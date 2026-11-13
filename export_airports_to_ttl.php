<?php

// ==================================================
// 1. KONFIGURASI LOKASI FILE
// ==================================================

// File JSON sumber dataset AeroFind
$inputFile = __DIR__ . '/storage/app/dataset_bandara_tubes.json';

// Folder dan file hasil RDF Turtle
$outputDirectory = __DIR__ . '/semantic-data';
$outputFile = $outputDirectory . '/airports.ttl';

// ==================================================
// 2. VALIDASI FILE JSON
// ==================================================

if (!file_exists($inputFile)) {
    die("ERROR: File JSON tidak ditemukan!\nLokasi: {$inputFile}\n");
}

$jsonContent = file_get_contents($inputFile);

if ($jsonContent === false) {
    die("ERROR: Gagal membaca file JSON.\n");
}

// Menghapus BOM jika ada
$jsonContent = preg_replace('/^\xEF\xBB\xBF/', '', $jsonContent);

$data = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("ERROR: JSON tidak valid: " . json_last_error_msg() . "\n");
}

// ==================================================
// 3. MEMBACA STRUKTUR DATA
// ==================================================

// Mendukung beberapa bentuk JSON:
// A. Array langsung berisi bandara
// B. Object dengan properti airports
// C. Object dengan properti data
// D. Satu object bandara

if (isset($data['airports']) && is_array($data['airports'])) {
    $airports = $data['airports'];

} elseif (isset($data['data']) && is_array($data['data'])) {
    $airports = $data['data'];

} elseif (isset($data['airport_id'])) {
    $airports = [$data];

} elseif (is_array($data) && array_is_list($data)) {
    $airports = $data;

} else {
    die("ERROR: Struktur JSON tidak dikenali.\n");
}

if (count($airports) === 0) {
    die("ERROR: Dataset tidak memiliki data bandara.\n");
}

// ==================================================
// 4. FUNGSI PEMBANTU RDF TURTLE
// ==================================================

/**
 * Mengubah teks menjadi literal RDF Turtle.
 */
function ttlString($value): string
{
    return json_encode(
        (string) $value,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}

/**
 * Mengubah nilai angka menjadi literal numerik.
 */
function ttlNumber($value): ?string
{
    if ($value === null || $value === '' || !is_numeric($value)) {
        return null;
    }

    $number = (float) $value;

    if (!is_finite($number)) {
        return null;
    }

    return rtrim(
        rtrim(sprintf('%.10F', $number), '0'),
        '.'
    );
}

/**
 * Membuat ID resource RDF yang aman.
 */
function ttlResourceId($value): string
{
    $value = trim((string) $value);

    return preg_replace('/[^a-zA-Z0-9_-]/', '-', $value);
}

// ==================================================
// 5. HEADER NAMESPACE RDF
// ==================================================

$ttl = <<<'TTL'
@prefix af: <https://aerofind.example/id/> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
@prefix xsd: <http://www.w3.org/2001/XMLSchema#> .

TTL;

// ==================================================
// 6. DAFTAR PROPERTI RDF
// ==================================================

// Kolom JSON => Properti RDF
$stringProperties = [
    'airport_id'       => 'airportId',
    'ident'            => 'ident',
    'type'             => 'airportType',
    'name'             => 'name',
    'continent'        => 'continent',
    'iso_country'      => 'countryCode',
    'iso_region'       => 'regionCode',
    'municipality'     => 'municipality',
    'scheduled_service'=> 'scheduledService',
    'icao_code'        => 'icaoCode',
    'iata_code'        => 'iataCode',
    'gps_code'         => 'gpsCode',
    'local_code'       => 'localCode',
    'home_link'        => 'homeLink',
    'wikipedia_link'   => 'wikipediaLink',
    'keywords'         => 'keywords',
    'data_source'      => 'dataSource',
    'last_verified'    => 'lastVerified',
];

// Kolom numerik JSON => Properti RDF
$numericProperties = [
    'id'            => 'sourceId',
    'latitude_deg'  => 'latitude',
    'longitude_deg' => 'longitude',
    'elevation_ft'  => 'elevationFt',
];

// ==================================================
// 7. KONVERSI DATA BANDARA MENJADI RDF
// ==================================================

$total = 0;
$skipped = 0;
$usedResources = [];

foreach ($airports as $airport) {

    if (!is_array($airport)) {
        $skipped++;
        continue;
    }

    // Mengambil ID dan nama bandara
    $airportId = $airport['airport_id'] ?? null;
    $airportName = $airport['name'] ?? null;

    if (!$airportId || !$airportName) {
        $skipped++;
        continue;
    }

    $resourceId = ttlResourceId($airportId);

    if ($resourceId === '') {
        $skipped++;
        continue;
    }

    // Mencegah resource RDF duplikat
    if (isset($usedResources[$resourceId])) {
        $skipped++;
        continue;
    }

    $usedResources[$resourceId] = true;

    // Setiap bandara merupakan instance af:Airport
    $triples = [];

    $triples[] = 'rdf:type af:Airport';
    $triples[] = 'rdfs:label ' . ttlString($airportName);

    // ----------------------------------------------
    // Properti berbentuk teks
    // ----------------------------------------------

    foreach ($stringProperties as $jsonKey => $rdfProperty) {

        $value = $airport[$jsonKey] ?? null;

        if ($value !== null && $value !== '') {

            $triples[] = 'af:' . $rdfProperty . ' '
                . ttlString($value);
        }
    }

    // ----------------------------------------------
    // Properti berbentuk angka
    // ----------------------------------------------

    foreach ($numericProperties as $jsonKey => $rdfProperty) {

        $value = ttlNumber($airport[$jsonKey] ?? null);

        if ($value !== null) {

            $triples[] = 'af:' . $rdfProperty . ' ' . $value;
        }
    }

    // ----------------------------------------------
    // Susun triple menjadi format Turtle
    // ----------------------------------------------

    $ttl .= 'af:' . $resourceId . "\n";
    $ttl .= '    ' . implode(" ;\n    ", $triples) . " .\n\n";

    $total++;
}

// ==================================================
// 8. SIMPAN FILE RDF TURTLE
// ==================================================

if (!is_dir($outputDirectory)) {

    if (!mkdir($outputDirectory, 0777, true)) {
        die("ERROR: Gagal membuat folder semantic-data.\n");
    }
}

$result = file_put_contents($outputFile, $ttl);

if ($result === false) {
    die("ERROR: Gagal menyimpan file airports.ttl.\n");
}

// ==================================================
// 9. INFORMASI HASIL KONVERSI
// ==================================================

echo "\n";
echo "========================================\n";
echo "     KONVERSI RDF AEROFIND BERHASIL\n";
echo "========================================\n";

echo "Sumber JSON     : {$inputFile}\n";
echo "File RDF        : {$outputFile}\n";
echo "Bandara berhasil: {$total}\n";
echo "Data dilewati   : {$skipped}\n";
echo "Ukuran file     : " . number_format($result) . " bytes\n";

echo "========================================\n";
echo "File airports.ttl siap diunggah ke Fuseki.\n";
echo "========================================\n\n";
