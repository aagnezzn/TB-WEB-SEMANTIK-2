<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Airport;
use App\Services\AirportRecommendationService;
use App\Services\FusekiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    protected AirportRecommendationService $recommendation;
    protected FusekiService $fuseki;

    public function __construct(
        AirportRecommendationService $recommendation,
        FusekiService $fuseki
    ) {
        $this->recommendation = $recommendation;
        $this->fuseki = $fuseki;
    }

    
    public function chat(Request $request): JsonResponse
        {
            $request->validate([
                'message' => ['required', 'string', 'max:500']
            ]);

            $message = trim($request->input('message'));
            $lower = mb_strtolower($message);

            // Membantu memeriksa beberapa kata kunci sekaligus.
            $containsAny = function (array $keywords) use ($lower): bool {
                foreach ($keywords as $keyword) {
                    if (str_contains($lower, $keyword)) {
                        return true;
                    }
                }

                return false;
            };

            try {

                /*
                |--------------------------------------------------------------------------
                | 1. SAPAAN DAN IDENTITAS CHATBOT
                |--------------------------------------------------------------------------
                */

                if ($containsAny([
                    'halo',
                    'hai',
                    'hello',
                    'selamat pagi',
                    'selamat siang',
                    'selamat sore',
                    'selamat malam',
                    'siapa kamu',
                    'apa itu aerofind',
                    'apa itu aerobot'
                ])) {
                    return response()->json([
                        'reply' => 'Halo! Saya AeroBot, asisten virtual AeroFind. Saya dapat membantu mencari profil bandara, daftar bandara, informasi lokasi, dan rekomendasi bandara terdekat berdasarkan kota atau kabupaten.'
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 2. REKOMENDASI BANDARA TERDEKAT
                |--------------------------------------------------------------------------
                */

                if ($containsAny([
                    'terdekat',
                    'paling dekat',
                    'bandara terdekat',
                    'rekomendasi bandara',
                    'bandara yang direkomendasikan',
                    'bandara terdekat dari'
                ])) {
                    return response()->json([
                        'reply' => $this->nearestAirport($message)
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 3. PROFIL / INFORMASI BANDARA
                |--------------------------------------------------------------------------
                */

                if ($containsAny([
                    'profil',
                    'profile',
                    'jelaskan',
                    'ceritakan',
                    'informasi tentang',
                    'informasi dari',
                    'detail bandara',
                    'data bandara',
                    'tentang bandara',
                    'deskripsi bandara',
                    'spesifikasi bandara',
                    'berikan informasi mengenai',
                    'apa itu bandara',
                    'berikan informasi tentang'
                ])) {
                    return response()->json([
                        'reply' => $this->searchAirportProfile($message)
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 4. DAFTAR BANDARA INDONESIA
                |--------------------------------------------------------------------------
                */

                if (
                    $containsAny([
                        'daftar bandara',
                        'semua bandara',
                        'seluruh bandara',
                        'bandara di indonesia',
                        'bandara indonesia',
                        'berapa jumlah bandara'
                    ])
                ) {
                    return response()->json([
                        'reply' => $this->searchIndonesiaAirports($message)
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 5. INFORMASI OPERASIONAL BANDARA
                |--------------------------------------------------------------------------
                */

                if ($containsAny([
                    'operasional',
                    'layanan penerbangan',
                    'penerbangan terjadwal',
                    'scheduled service',
                    'status layanan',
                    'jenis bandara',
                    'tipe bandara',
                    'kode iata',
                    'kode icao',
                    'koordinat bandara',
                    'latitude',
                    'longitude'
                ])) {
                    return response()->json([
                        'reply' => $this->searchAirportProfile($message)
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 6. PENCARIAN BANDARA BERDASARKAN WILAYAH
                |--------------------------------------------------------------------------
                */

                if (
                    str_contains($lower, 'bandara') ||
                    str_contains($lower, 'kabupaten') ||
                    str_contains($lower, 'kota') ||
                    str_contains($lower, 'wilayah')
                ) {
                    return response()->json([
                        'reply' => $this->searchByRegion($message)
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 7. PERTANYAAN TENTANG FITUR AEROFIND
                |--------------------------------------------------------------------------
                */

                if ($containsAny([
                    'fitur',
                    'fungsi aerofind',
                    'kegunaan aerofind',
                    'cara menggunakan',
                    'cara kerja aerofind'
                ])) {
                    return response()->json([
                        'reply' => 'AeroFind merupakan aplikasi pencarian dan rekomendasi bandara. Kamu dapat menggunakan beberapa fitur, seperti pencarian bandara berdasarkan wilayah, melihat profil bandara, melihat lokasi bandara melalui peta, dan mendapatkan rekomendasi bandara terdekat.'
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 8. PERTANYAAN YANG BELUM DIKENALI
                |--------------------------------------------------------------------------
                */

                return response()->json([
                    'reply' => 'Maaf, saya belum memahami pertanyaan tersebut. Kamu dapat mencoba bertanya seperti: "Berikan profil Bandara Kualanamu", "Bandara apa saja di Kabupaten Deli Serdang?", atau "Apa bandara terdekat dari Kota Medan?".'
                ]);

            } catch (\Throwable $e) {

                report($e);

                return response()->json([
                    'reply' => 'Maaf, terjadi kendala ketika mengambil data bandara. Silakan coba kembali dengan pertanyaan yang lebih spesifik.'
                ], 500);
            }
        }


    /**
     * Mengambil nama wilayah dari pertanyaan.
     */
    private function extractRegion(string $message): ?string
    {
        $message = trim($message);

        // Hilangkan kata-kata pembuka pertanyaan.
        $message = preg_replace(
            '/\b(bandara|terdekat|paling dekat|rekomendasi|apa|saja|yang|ada|dari|di|sekitar|wilayah|kabupaten|kab\.|kota|tolong|carikan|cari|untuk|jelaskan|saya)\b/iu',
            ' ',
            $message
        );

        // Bersihkan karakter dan spasi.
        $message = trim(
            preg_replace('/\s+/', ' ', $message),
            " \t\n\r\0\x0B?.!,"
        );

        return $message !== '' ? $message : null;
    }

    /**
     * Rekomendasi bandara terdekat berdasarkan nama wilayah.
     */
    private function nearestAirport(string $message): string
    {
        $regionName = $this->extractRegion($message);

        if (!$regionName) {
            return 'Silakan sebutkan nama kota atau kabupaten. Contoh: Bandara terdekat dari Kabupaten Deli Serdang.';
        }

        // Cari nama wilayah di database.
        $region = Region::whereRaw(
            'LOWER(region_name) = ?',
            [mb_strtolower($regionName)]
        )->first();

        // Jika tidak ditemukan, coba pencarian sebagian.
        if (!$region) {
            $region = Region::where(
                'region_name',
                'LIKE',
                '%' . $regionName . '%'
            )->first();
        }

        if (!$region) {
            return "Maaf, wilayah {$regionName} belum ditemukan dalam database AeroSearch.";
        }

        // Gunakan Service rekomendasi yang sama dengan API peta.
        $airports = $this->recommendation->nearest(
            $region,
            5
        );

        if ($airports->isEmpty()) {
            return "Belum ditemukan bandara dengan koordinat yang valid di negara wilayah {$region->region_name}.";
        }

        $reply = "Berikut rekomendasi 5 bandara terdekat dari {$region->region_name}:\n\n";

        foreach ($airports as $index => $airport) {
            $distance = number_format(
                (float) $airport->distance_km,
                2,
                ',',
                '.'
            );

            $reply .= ($index + 1) . ". {$airport->name}\n";

            $reply .= "   Kode IATA: "
                . ($airport->iata_code ?: '-') . "\n";

            $reply .= "   Kode ICAO: "
                . ($airport->icao_code ?: '-') . "\n";

            $reply .= "   Lokasi: "
                . ($airport->municipality ?: '-') . "\n";

            $reply .= "   Jarak: {$distance} km\n\n";
        }

        $reply .= "Rekomendasi dihitung berdasarkan koordinat pusat wilayah dan koordinat bandara menggunakan rumus Haversine.\n\n";

        $reply .= "Sumber data: Direktori bandara AeroSearch.";

        return $reply;
    }

    /**
     * Pencarian bandara berdasarkan wilayah.
     */
    
    
    private function searchByRegion(string $message): string
    {
        $lowerMessage = mb_strtolower($message);

        // Mengambil daftar wilayah
        $regions = Region::query()->get([
            'id',
            'region_name',
            'country_code',
            'latitude',
            'longitude'
        ]);

        // Mencocokkan nama wilayah dalam pertanyaan
        // Normalisasi teks pertanyaan
$normalizedMessage = mb_strtolower($message);

// Jangan menganggap Bali sebagai nama kota
if (
    preg_match('/\bbali\b/u', $normalizedMessage) &&
    !preg_match('/\b(denpasar|badung|tabanan|jembrana|buleleng|bangli|gianyar|klungkung|karangasem)\b/u', $normalizedMessage)
) {
    return "Bali adalah nama provinsi, bukan kota atau kabupaten. Silakan sebutkan nama kota atau kabupaten di Bali, seperti Denpasar, Badung, Tabanan, Jembrana, Buleleng, Bangli, Gianyar, Klungkung, atau Karangasem.";
}

// Cocokkan nama wilayah secara utuh, bukan potongan kata
    $matchedRegion = $regions
        ->filter(function ($region) use ($normalizedMessage) {

            $regionName = mb_strtolower(trim($region->region_name));

            // Abaikan nama wilayah yang terlalu pendek
            if (mb_strlen($regionName) < 3) {
                return false;
            }

            $pattern = '/(?<![\p{L}\p{N}])'
                . preg_quote($regionName, '/')
                . '(?![\p{L}\p{N}])/u';

            return preg_match($pattern, $normalizedMessage) === 1;
        })
        ->sortByDesc(function ($region) {
            return mb_strlen($region->region_name);
        })
        ->first();

        if (!$matchedRegion) {
            return 'Maaf, saya belum dapat mengenali nama kota atau kabupaten yang kamu maksud. Silakan gunakan nama wilayah yang terdaftar di database AeroFind.';
        }

        $regionName = $matchedRegion->region_name;
        $latitude = (float) $matchedRegion->latitude;
        $longitude = (float) $matchedRegion->longitude;
        $countryCode = $matchedRegion->country_code;

        // Menghitung jarak wilayah ke setiap bandara
        $airports = Airport::query()
            ->select([
                'airport_id',
                'name',
                'iata_code',
                'icao_code',
                'type',
                'municipality',
                'iso_country',
                'latitude_deg',
                'longitude_deg',
                'scheduled_service'
            ])
            ->selectRaw(
                '(
                    6371 * ACOS(
                        LEAST(1, GREATEST(-1,
                            COS(RADIANS(?))
                            * COS(RADIANS(latitude_deg))
                            * COS(RADIANS(longitude_deg) - RADIANS(?))
                            + SIN(RADIANS(?))
                            * SIN(RADIANS(latitude_deg))
                        ))
                    )
                ) AS distance_km',
                [$latitude, $longitude, $latitude]
            )
            ->where('iso_country', $countryCode)
            ->whereNotNull('latitude_deg')
            ->whereNotNull('longitude_deg')
            ->orderBy('distance_km')
            ->limit(5)
            ->get();

        if ($airports->isEmpty()) {
            return "Maaf, belum ada data bandara dengan koordinat yang dapat digunakan untuk wilayah {$regionName}.";
        }

        // Menyusun jawaban chatbot
        $reply = "✈️ REKOMENDASI BANDARA UNTUK {$regionName}\n\n";

        $reply .= "Berikut 5 bandara terdekat berdasarkan koordinat wilayah:\n\n";

        foreach ($airports as $index => $airport) {
            $distance = number_format($airport->distance_km, 2);

            $reply .= ($index + 1) . ". {$airport->name}\n";

            $reply .= "   Jarak perkiraan: {$distance} km\n";

            $reply .= "   IATA: " .
                ($airport->iata_code ?: 'Tidak tersedia') . "\n";

            $reply .= "   ICAO: " .
                ($airport->icao_code ?: 'Tidak tersedia') . "\n";

            $reply .= "   Lokasi: " .
                ($airport->municipality ?: 'Tidak tersedia') . "\n\n";
        }

        $reply .= "Catatan: Jarak dihitung dari titik koordinat wilayah ke titik koordinat bandara, bukan jarak perjalanan melalui jalan raya.\n\n";

        $reply .= "Data diperoleh dari database AeroFind.";

        return $reply;
    }


    /**
     * Placeholder pencarian profil melalui Fuseki.
     * Implementasi query mengikuti struktur RDF proyek.
     */
    
    
    
    private function searchAirportProfile(string $message): string
    {
        // Bersihkan kata-kata instruksi dari pertanyaan.
        $searchName = mb_strtolower($message);

        $stopWords = [
            'berikan', 'beri', 'tolong', 'coba', 'saya',
            'ingin', 'minta', 'carikan', 'tampilkan',
            'jelaskan', 'ceritakan', 'profil', 'profile',
            'informasi', 'detail', 'data', 'tentang',
            'mengenai', 'dari', 'bandara', 'airport',
            'apa', 'itu', 'yang', 'ada', 'di', 'untuk'
        ];

        foreach ($stopWords as $word) {
            $searchName = preg_replace(
                '/\b' . preg_quote($word, '/') . '\b/u',
                ' ',
                $searchName
            );
        }

        $searchName = trim(preg_replace('/\s+/', ' ', $searchName));

        if ($searchName === '') {
            return 'Silakan sebutkan nama bandara yang ingin dicari.';
        }

        // Ubah teks pencarian menjadi literal SPARQL yang aman.
        $searchLiteral = json_encode(
            $searchName,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $searchCode = strtoupper($searchName);

        $codeLiteral = json_encode(
            $searchCode,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $query = "
            PREFIX af: <https://aerofind.example/id/>
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

            SELECT DISTINCT
                ?name
                ?airportId
                ?ident
                ?airportType
                ?countryCode
                ?regionCode
                ?municipality
                ?scheduledService
                ?icaoCode
                ?iataCode
                ?latitude
                ?longitude
                ?elevationFt

            WHERE {
                GRAPH <https://aerofind.example/graph/airports> {

                    ?airport rdf:type af:Airport ;
                            rdfs:label ?name .

                    OPTIONAL { ?airport af:airportId ?airportId }
                    OPTIONAL { ?airport af:ident ?ident }
                    OPTIONAL { ?airport af:airportType ?airportType }
                    OPTIONAL { ?airport af:countryCode ?countryCode }
                    OPTIONAL { ?airport af:regionCode ?regionCode }
                    OPTIONAL { ?airport af:municipality ?municipality }
                    OPTIONAL { ?airport af:scheduledService ?scheduledService }
                    OPTIONAL { ?airport af:icaoCode ?icaoCode }
                    OPTIONAL { ?airport af:iataCode ?iataCode }
                    OPTIONAL { ?airport af:latitude ?latitude }
                    OPTIONAL { ?airport af:longitude ?longitude }
                    OPTIONAL { ?airport af:elevationFt ?elevationFt }

                    FILTER (
                        CONTAINS(LCASE(STR(?name)), $searchLiteral)
                        ||
                        STR(?iataCode) = $codeLiteral
                        ||
                        STR(?icaoCode) = $codeLiteral
                    )
                }
            }

            LIMIT 5
        ";

        // Ambil hasil dari Fuseki.
        $result = $this->fuseki->select($query);

        $bindings = $result['results']['bindings'] ?? [];

        if (empty($bindings)) {
            return "Maaf, saya tidak menemukan bandara dengan kata kunci {$searchName} di dataset RDF AeroFind.";
        }

        // Jika ada beberapa hasil, tampilkan daftar agar tidak salah memilih.
        if (count($bindings) > 1) {
            $reply = "Saya menemukan beberapa bandara yang cocok:\n\n";

            foreach ($bindings as $index => $item) {
                $name = $item['name']['value'] ?? '-';
                $iata = $item['iataCode']['value'] ?? '-';

                $reply .= ($index + 1) . ". {$name}\n";
                $reply .= "   Kode IATA: {$iata}\n\n";
            }

            $reply .= 'Silakan gunakan nama bandara atau kode IATA yang lebih spesifik.';

            return $reply;
        }

        $airport = $bindings[0];

        // Mengambil nilai dari hasil SPARQL.
        $getValue = function ($key) use ($airport) {
            return $airport[$key]['value'] ?? 'Tidak tersedia';
        };

        return
            "Berikut adalah informasi tentang bandara {$getValue('name')}:\n\n" .
            "ID Bandara: {$getValue('airportId')}\n" .
            "Nama: {$getValue('name')}\n" .
            "Kode IATA: {$getValue('iataCode')}\n" .
            "Kode ICAO: {$getValue('icaoCode')}\n" .
            "Identifikasi: {$getValue('ident')}\n" .
            "Tipe: {$getValue('airportType')}\n" .
            "Kota/Kabupaten: {$getValue('municipality')}\n" .
            "Negara: {$getValue('countryCode')}\n" .
            "Kode Wilayah: {$getValue('regionCode')}\n" .
            "Latitude: {$getValue('latitude')}\n" .
            "Longitude: {$getValue('longitude')}\n" .
            "Elevasi (ft): {$getValue('elevationFt')}\n" .
            "Layanan terjadwal: {$getValue('scheduledService')}\n\n" .
            "Sumber data: Dataset RDF AeroFind melalui Apache Jena Fuseki.";
    }


    /**
     * Placeholder pencarian bandara Indonesia.
     */
    
    private function searchIndonesiaAirports(string $message): string
    {
        $isCountQuestion = str_contains(
            mb_strtolower($message),
            'berapa jumlah'
        );

        if ($isCountQuestion) {
            $query = "
                PREFIX af: <https://aerofind.example/id/>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>

                SELECT (COUNT(DISTINCT ?airport) AS ?total)

                WHERE {
                    GRAPH <https://aerofind.example/graph/airports> {
                        ?airport rdf:type af:Airport ;
                                af:countryCode \"ID\" .
                    }
                }
            ";

            $result = $this->fuseki->select($query);

            $total = $result['results']['bindings'][0]['total']['value'] ?? '0';

            return "Berdasarkan dataset RDF AeroFind, terdapat {$total} data bandara di Indonesia.";
        }

        $query = "
            PREFIX af: <https://aerofind.example/id/>
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

            SELECT DISTINCT ?name ?iataCode ?municipality

            WHERE {
                GRAPH <https://aerofind.example/graph/airports> {
                    ?airport rdf:type af:Airport ;
                            rdfs:label ?name ;
                            af:countryCode \"ID\" .

                    OPTIONAL { ?airport af:iataCode ?iataCode }
                    OPTIONAL { ?airport af:municipality ?municipality }
                }
            }

            ORDER BY ?name
            LIMIT 10
        ";

        $result = $this->fuseki->select($query);

        $bindings = $result['results']['bindings'] ?? [];

        if (empty($bindings)) {
            return 'Belum ditemukan data bandara Indonesia pada dataset RDF AeroFind.';
        }

        $reply = "Berikut beberapa bandara di Indonesia berdasarkan dataset RDF AeroFind:\n\n";

        foreach ($bindings as $index => $airport) {
            $name = $airport['name']['value'] ?? '-';
            $iata = $airport['iataCode']['value'] ?? '-';
            $municipality = $airport['municipality']['value'] ?? '-';

            $reply .= ($index + 1) . ". {$name}\n";
            $reply .= "   Kode IATA: {$iata}\n";
            $reply .= "   Lokasi: {$municipality}\n\n";
        }

        $reply .= 'Menampilkan maksimal 10 bandara. Data diperoleh dari Apache Jena Fuseki.';

        return $reply;
    }

}
