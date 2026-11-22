<?php

// ==================================================
// Ekspor data kota/kabupaten (CSV) menjadi RDF Turtle
// Jalankan: php export_regions_to_ttl.php
// Hasil: semantic-data/regions.ttl (muat ke dataset Fuseki "airports")
// ==================================================

$inputFile = __DIR__ . '/database/seeders/data/regions_southeast_asia.csv';
$outputDirectory = __DIR__ . '/semantic-data';
$outputFile = $outputDirectory . '/regions.ttl';

// Pemetaan ISO alpha-3 ke alpha-2 (sama dengan RegionSeeder)
$countryCodes = [
    'IDN' => 'ID', 'MYS' => 'MY', 'SGP' => 'SG', 'THA' => 'TH', 'VNM' => 'VN',
    'PHL' => 'PH', 'BRN' => 'BN', 'KHM' => 'KH', 'LAO' => 'LA', 'MMR' => 'MM', 'TLS' => 'TL',
];

if (!file_exists($inputFile)) {
    die("ERROR: File CSV tidak ditemukan: {$inputFile}\n");
}

$handle = fopen($inputFile, 'r');
$header = fgetcsv($handle, 0, ',', '"', '');
$header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

function ttlString(string $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$ttl = <<<'TTL'
@prefix af: <https://aerofind.example/id/> .
@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .

TTL;

$total = 0;
$skipped = 0;
$used = [];

while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
    if (count($line) !== count($header)) {
        $skipped++;
        continue;
    }

    $row = array_combine($header, $line);
    $alpha3 = strtoupper(trim($row['country_code'] ?? ''));
    $regionId = trim($row['region_id'] ?? '');

    if (
        !isset($countryCodes[$alpha3])
        || $regionId === ''
        || empty($row['region_name'])
        || !is_numeric($row['latitude'] ?? null)
        || !is_numeric($row['longitude'] ?? null)
        || isset($used[$regionId])
    ) {
        $skipped++;
        continue;
    }

    $used[$regionId] = true;
    $resource = 'region-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $regionId);

    $ttl .= "af:{$resource}\n"
        . "    rdf:type af:Region ;\n"
        . '    rdfs:label ' . ttlString($row['region_name']) . " ;\n"
        . '    af:regionId ' . ttlString($regionId) . " ;\n"
        . '    af:regionType ' . ttlString($row['region_type'] ?? 'ADM2') . " ;\n"
        . '    af:countryCode ' . ttlString($countryCodes[$alpha3]) . " ;\n"
        . '    af:latitude ' . (float) $row['latitude'] . " ;\n"
        . '    af:longitude ' . (float) $row['longitude'] . " .\n\n";

    $total++;
}

fclose($handle);

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0777, true)) {
    die("ERROR: Gagal membuat folder semantic-data.\n");
}

file_put_contents($outputFile, $ttl);

echo "Selesai. {$total} wilayah ditulis, {$skipped} dilewati.\n{$outputFile}\n";
