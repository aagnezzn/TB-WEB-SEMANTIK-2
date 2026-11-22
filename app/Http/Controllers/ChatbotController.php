<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Services\AirportRecommendationService;
use App\Services\ChatbotNlpService;
use App\Services\FusekiService;
use App\Services\IslandResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected AirportRecommendationService $recommendation;
    protected FusekiService $fuseki;
    protected ChatbotNlpService $nlp;

    public function __construct(
        AirportRecommendationService $recommendation,
        FusekiService $fuseki,
        ChatbotNlpService $nlp
    ) {
        $this->recommendation = $recommendation;
        $this->fuseki = $fuseki;
        $this->nlp = $nlp;
    }

    /**
     * Endpoint API komunikasi chatbot utama.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500']
        ]);

        $message = trim($request->input('message'));
        $lower = mb_strtolower($message);
        $normalized = $this->nlp->normalize($message);

        // Membantu memeriksa beberapa kata kunci sekaligus (mendukung bahasa baku dan santai)
        $containsAny = function (array $keywords) use ($lower, $normalized): bool {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword) || str_contains($normalized, $keyword)) {
                    return true;
                }
            }

            return false;
        };

        try {

            /*
            |--------------------------------------------------------------------------
            | 0. BANDARA BERDASARKAN PULAU (seluruh ASEAN)
            |--------------------------------------------------------------------------
            */
            $islandIndex = $this->islandIndex();
            $island = $this->nlp->extractIslandEntity($message, $normalized, array_keys($islandIndex));
            if ($island !== null && $this->isIslandQuestion($island, $lower, $normalized, $containsAny)) {
                return response()->json([
                    'reply' => $this->searchByIsland($island, $islandIndex)
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 1. REKOMENDASI BANDARA BERDASARKAN TIPE / FASILITAS (Large, Medium, Small)
            |--------------------------------------------------------------------------
            */
            $airportType = $this->nlp->extractAirportTypeEntity($normalized);
            if ($airportType !== null || $containsAny(['fasilitas', 'tipe bandara', 'ukuran bandara', 'besar', 'sedang', 'kecil'])) {
                if ($airportType) {
                    return response()->json([
                        'reply' => $this->searchAirportsByType($airportType)
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 2. REKOMENDASI BANDARA TERDEKAT
            |--------------------------------------------------------------------------
            */
            if ($containsAny([
                'terdekat', 'paling dekat', 'bandara terdekat', 'rekomendasi bandara',
                'bandara yang direkomendasikan', 'bandara terdekat dari', 'dekat',
                'dkat', 'dket', 'deket', 'plg dkt', 'yg dkat', 'yg deket',
                'yang dekat', 'paling dkt', 'sekitarnya'
            ])) {
                return response()->json([
                    'reply' => $this->nearestAirport($message)
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. PROFIL / INFORMASI BANDARA (Triple Store Jena Fuseki)
            |--------------------------------------------------------------------------
            */
            if ($containsAny([
                'profil', 'profile', 'jelaskan', 'ceritakan', 'informasi tentang',
                'informasi dari', 'detail bandara', 'data bandara', 'tentang bandara',
                'deskripsi bandara', 'spesifikasi bandara', 'berikan informasi mengenai',
                'apa itu bandara', 'berikan informasi tentang', 'kode iata',
                'kode icao', 'operator bandara', 'elevasi bandara'
            ]) || $this->nlp->extractAirportEntity($normalized) !== null) {
                return response()->json([
                    'reply' => $this->searchAirportProfile($message)
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 4. DAFTAR BANDARA & HITUNG JUMLAH (SPARQL Fuseki - Berdasarkan Negara ASEAN)
            |--------------------------------------------------------------------------
            */
            if ($containsAny([
                'daftar bandara', 'semua bandara', 'seluruh bandara',
                'bandara di indonesia', 'bandara indonesia', 'bandara malaysia',
                'bandara di malaysia', 'bandara thailand', 'bandara di thailand',
                'bandara singapura', 'bandara di singapura', 'bandara filipina',
                'bandara vietnam', 'bandara brunei', 'bandara kamboja',
                'bandara laos', 'bandara myanmar', 'berapa jumlah bandara',
                'berapa banyak bandara', 'total bandara', 'jumlah bandara', 'ada berapa bandara'
            ])) {
                return response()->json([
                    'reply' => $this->searchAirportsByCountry($message)
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 5. INFORMASI OPERASIONAL BANDARA
            |--------------------------------------------------------------------------
            */
            if ($containsAny([
                'operasional', 'layanan penerbangan', 'penerbangan terjadwal',
                'scheduled service', 'status layanan', 'jenis bandara',
                'tipe bandara', 'koordinat bandara', 'latitude', 'longitude'
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
                str_contains($lower, 'wilayah') ||
                str_contains($lower, 'daerah') ||
                str_contains($lower, 'di ') ||
                $this->extractRegionModel($message) !== null
            ) {
                $regionModel = $this->extractRegionModel($message);
                if ($regionModel) {
                    return response()->json([
                        'reply' => $this->searchByRegion($message)
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 7. SAPAAN DAN IDENTITAS CHATBOT
            |--------------------------------------------------------------------------
            */
            if ($containsAny([
                'halo', 'hai', 'hello', 'selamat pagi', 'selamat siang',
                'selamat sore', 'selamat malam', 'siapa kamu', 'kamu siapa',
                'apa itu aerofind', 'apa itu aerobot'
            ])) {
                return response()->json([
                    'reply' => 'Halo! Saya AeroBot, asisten virtual AeroFind. Saya dapat membantu kamu mencari profil bandara, daftar bandara, informasi lokasi, dan rekomendasi bandara terdekat berdasarkan kota atau kabupaten di Asia Tenggara. Ada yang ingin kamu tanyakan?'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 8. PERTANYAAN TENTANG FITUR AEROFIND
            |--------------------------------------------------------------------------
            */
            if ($containsAny([
                'fitur', 'fungsi aerofind', 'kegunaan aerofind',
                'cara menggunakan', 'cara kerja aerofind', 'bisa apa saja', 'bantuan'
            ])) {
                return response()->json([
                    'reply' => 'AeroFind merupakan aplikasi pencarian dan rekomendasi bandara berbasis Web Semantik. Kamu dapat menanyakan beberapa hal seperti mencari bandara terdekat berdasarkan nama kota, melihat profil bandara, memeriksa kode IATA/ICAO, serta melihat persebaran bandara melalui peta.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 9. PERTANYAAN YANG BELUM DIKENALI
            |--------------------------------------------------------------------------
            */
            return response()->json([
                'reply' => 'Maaf, saya belum memahami pertanyaan tersebut. Kamu dapat mencoba bertanya seperti: "Berikan profil Bandara Kualanamu", "Bandara apa saja di Deli Serdang?", "Apa bandara terdekat dari Medan?", atau "Berapa jumlah bandara di Indonesia?".'
            ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'Maaf, terjadi kendala ketika mengambil data bandara. Silakan coba kembali dengan pertanyaan nama kota atau bandara yang lebih spesifik.'
            ], 200);
        }
    }

    /**
     * Indeks bandara per pulau dari dataset JSON (dipakai untuk mengenali nama pulau
     * dan sebagai cadangan bila Fuseki tidak aktif).
     *
     * @return array<string, list<array{name: string, iata: ?string, icao: ?string, municipality: ?string}>>
     */
    private function islandIndex(): array
    {
        return Cache::remember('chatbot.island_index', now()->addHour(), function (): array {
            $jsonPath = database_path('data/dataset_bandara_tubes.json');
            if (!file_exists($jsonPath)) {
                return [];
            }

            $content = preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($jsonPath));
            $data = json_decode($content, true);
            $index = [];

            foreach (is_array($data) ? $data : [] as $item) {
                $island = IslandResolver::resolve($item);
                if ($island === null) {
                    continue;
                }

                $index[$island][] = [
                    'name'         => (string) ($item['name'] ?? 'Bandara'),
                    'iata'         => $item['iata_code'] ?? null,
                    'icao'         => $item['icao_code'] ?? null,
                    'municipality' => $item['municipality'] ?? null,
                ];
            }

            foreach ($index as &$airports) {
                usort($airports, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
            }
            unset($airports);

            return $index;
        });
    }

    /**
     * Menentukan apakah pesan benar-benar menanyakan bandara di sebuah pulau,
     * bukan nama negara, kota, atau bandara tertentu.
     */
    private function isIslandQuestion(string $island, string $lower, string $normalized, callable $containsAny): bool
    {
        if ($containsAny(['pulau', 'island', 'kepulauan'])) {
            return true;
        }

        if ($island === 'Singapura' || ($island === 'Timor' && str_contains($lower, 'timor leste'))) {
            return false;
        }

        return $containsAny(['bandara', 'airport', 'bandar udara'])
            && !$containsAny(['terdekat', 'dekat', 'deket', 'profil', 'profile'])
            && $this->nlp->extractAirportEntity($normalized) === null;
    }

    private function searchByIsland(string $island, array $islandIndex): string
    {
        $safeIsland = addcslashes($island, "\"\\");

        $query = "
            PREFIX af: <https://aerofind.example/id/>
            SELECT DISTINCT ?name ?iataCode ?icaoCode ?municipality WHERE {
                ?airport a af:Airport ;
                         af:name ?name ;
                         af:island ?island .
                FILTER(LCASE(STR(?island)) = LCASE(\"{$safeIsland}\"))
                OPTIONAL { ?airport af:iataCode ?iataCode }
                OPTIONAL { ?airport af:icaoCode ?icaoCode }
                OPTIONAL { ?airport af:municipality ?municipality }
            }
            ORDER BY ?name
        ";

        $airports = [];

        try {
            foreach ($this->fuseki->select($query)['results']['bindings'] ?? [] as $row) {
                $airports[] = [
                    'name'         => $row['name']['value'],
                    'iata'         => $row['iataCode']['value'] ?? null,
                    'icao'         => $row['icaoCode']['value'] ?? null,
                    'municipality' => $row['municipality']['value'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // Cadangan: dataset JSON bila Fuseki tidak aktif atau belum memuat af:island
        if ($airports === []) {
            $airports = $islandIndex[$island] ?? [];
        }

        return $this->nlp->formatIslandAirports($island, $airports);
    }

    /**
     * Dataset bandara dari berkas JSON, dipakai sebagai cadangan bila Fuseki tidak aktif.
     *
     * @return Collection<int, object>
     */
    private function airportsFromJson(): Collection
    {
        $jsonPath = database_path('data/dataset_bandara_tubes.json');

        if (!file_exists($jsonPath)) {
            return collect();
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($jsonPath));
        $data = json_decode($content, true);

        return collect(is_array($data) ? $data : [])
            ->filter(fn ($item) => !empty($item['name']))
            ->map(fn (array $item): object => (object) $item)
            ->values();
    }

    private function extractRegion(string $message): ?string
    {
        $region = $this->extractRegionModel($message);
        return $region ? $region->region_name : null;
    }

    private function extractRegionModel(string $message): ?Region
    {
        $normalized = $this->nlp->normalize($message);
        return $this->nlp->extractRegionEntity($message, $normalized);
    }

    /**
     * Pencarian bandara berdasarkan tipe fasilitas (large_airport, medium_airport, small_airport).
     */
    private function searchAirportsByType(string $type): string
    {
        $typeNameMap = [
            'large_airport'  => 'Large (Besar / Internasional)',
            'medium_airport' => 'Medium (Sedang)',
            'small_airport'  => 'Small (Kecil)',
        ];

        $displayTypeName = $typeNameMap[$type] ?? $type;

        $query = "
            PREFIX af: <https://aerofind.example/id/>
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

            SELECT DISTINCT ?name ?iataCode ?icaoCode ?municipality ?countryCode
            WHERE {
                GRAPH <https://aerofind.example/graph/airports> {
                    ?airport rdf:type af:Airport ;
                             rdfs:label ?name ;
                             af:airportType \"{$type}\" .

                    OPTIONAL { ?airport af:iataCode ?iataCode }
                    OPTIONAL { ?airport af:icaoCode ?icaoCode }
                    OPTIONAL { ?airport af:municipality ?municipality }
                    OPTIONAL { ?airport af:countryCode ?countryCode }
                }
            }
            ORDER BY ?name
            LIMIT 10
        ";

        try {
            $result = $this->fuseki->select($query);
            $bindings = $result['results']['bindings'] ?? [];
        } catch (\Throwable $e) {
            $bindings = [];
        }

        if (empty($bindings)) {
            try {
                $airports = $this->airportsFromJson()->where('type', $type)->take(10)->values();
                $bindings = $airports->map(fn ($a) => [
                    'name'         => ['value' => $a->name],
                    'iataCode'     => ['value' => $a->iata_code ?? '-'],
                    'icaoCode'     => ['value' => $a->icao_code ?? '-'],
                    'municipality' => ['value' => $a->municipality ?? '-'],
                    'countryCode'  => ['value' => strtoupper(substr((string) ($a->iso_region ?? 'ID'), 0, 2))],
                ])->toArray();
            } catch (\Throwable $e) {
                $bindings = [];
            }
        }

        if (empty($bindings)) {
            return "Belum ditemukan data bandara dengan tipe fasilitas {$displayTypeName} pada dataset RDF AeroFind.";
        }

        $reply = "Berikut beberapa rekomendasi bandara dengan fasilitas tipe {$displayTypeName}:\n\n";

        foreach ($bindings as $index => $item) {
            $name = $item['name']['value'] ?? '-';
            $iata = $item['iataCode']['value'] ?? '-';
            $icao = $item['icaoCode']['value'] ?? '-';
            $municipality = $item['municipality']['value'] ?? '-';
            $country = $item['countryCode']['value'] ?? '-';

            $reply .= ($index + 1) . ". {$name}\n";
            $reply .= "   Kode IATA / ICAO: {$iata} / {$icao}\n";
            $reply .= "   Lokasi: {$municipality} ({$country})\n\n";
        }

        $reply .= 'Menampilkan maksimal 10 data. Sumber data: Apache Jena Fuseki dan direktori AeroFind.';

        return $reply;
    }

    private function nearestAirport(string $message): string
    {
        $region = $this->extractRegionModel($message);

        if (!$region) {
            return 'Silakan sebutkan nama kota atau kabupaten yang kamu cari. Contoh: "Bandara terdekat dari Medan" atau "Bandara yang dekat Deli Serdang".';
        }

        $regionName = $region->region_name;
        $latitude = (float) $region->latitude;
        $longitude = (float) $region->longitude;

        $airports = $this->getNearestAirportsFromJena($latitude, $longitude, 5);

        if ($airports->isEmpty()) {
            return "Belum ditemukan data bandara dengan koordinat yang valid di sekitar wilayah {$regionName}.";
        }

        $reply = "Berikut 5 rekomendasi bandara terdekat dari {$regionName}:\n\n";

        foreach ($airports as $index => $airport) {
            $distance = number_format((float) $airport->distance_km, 2, ',', '.');
            $iata = $airport->iata_code ?: '-';
            $icao = $airport->icao_code ?: '-';
            $loc = $airport->municipality ?: '-';

            $reply .= ($index + 1) . ". {$airport->name}\n";
            $reply .= "   Kode IATA: {$iata}\n";
            $reply .= "   Kode ICAO: {$icao}\n";
            $reply .= "   Lokasi: {$loc}\n";
            $reply .= "   Jarak: {$distance} km\n\n";
        }

        $reply .= "Rekomendasi dihitung berdasarkan koordinat pusat wilayah {$regionName} ({$latitude}, {$longitude}) dan koordinat bandara menggunakan rumus Haversine.\n\n";
        $reply .= "Sumber data: Triple store Jena dan direktori bandara AeroFind.";

        return $reply;
    }

    private function searchByRegion(string $message): string
    {
        $normalizedMessage = $this->nlp->normalize($message);

        if (
            preg_match('/\bbali\b/u', $normalizedMessage) &&
            !preg_match('/\b(denpasar|badung|tabanan|jembrana|buleleng|bangli|gianyar|klungkung|karangasem)\b/u', $normalizedMessage)
        ) {
            return "Bali adalah nama provinsi, bukan kota atau kabupaten. Silakan sebutkan nama kota atau kabupaten di Bali, seperti Denpasar, Badung, Tabanan, Jembrana, Buleleng, Bangli, Gianyar, Klungkung, atau Karangasem.";
        }

        $matchedRegion = $this->extractRegionModel($message);

        if (!$matchedRegion) {
            return 'Maaf, saya belum dapat mengenali nama kota atau kabupaten yang kamu maksud. Silakan gunakan wilayah yang terdaftar di database AeroFind.';
        }

        $regionName = $matchedRegion->region_name;
        $latitude = (float) $matchedRegion->latitude;
        $longitude = (float) $matchedRegion->longitude;

        $airports = $this->getNearestAirportsFromJena($latitude, $longitude, 5);

        if ($airports->isEmpty()) {
            return "Maaf, belum ada data bandara dengan koordinat yang dapat digunakan untuk wilayah {$regionName}.";
        }

        $reply = "REKOMENDASI BANDARA UNTUK " . strtoupper($regionName) . "\n\n";
        $reply .= "Berikut 5 bandara terdekat berdasarkan koordinat wilayah:\n\n";

        foreach ($airports as $index => $airport) {
            $distance = number_format((float) $airport->distance_km, 2, ',', '.');
            $iata = $airport->iata_code ?: 'Tidak tersedia';
            $icao = $airport->icao_code ?: 'Tidak tersedia';
            $loc = $airport->municipality ?: 'Tidak tersedia';

            $reply .= ($index + 1) . ". {$airport->name}\n";
            $reply .= "   Jarak perkiraan: {$distance} km\n";
            $reply .= "   IATA: {$iata}\n";
            $reply .= "   ICAO: {$icao}\n";
            $reply .= "   Lokasi: {$loc}\n\n";
        }

        $reply .= "Catatan: Jarak dihitung dari titik koordinat wilayah {$regionName} ke titik koordinat bandara menggunakan rumus Haversine.\n\n";
        $reply .= "Data diperoleh dari triple store Apache Jena dan database AeroFind.";

        return $reply;
    }

    private function searchAirportProfile(string $message): string
    {
        $normalized = $this->nlp->normalize($message);
        $airportEntity = $this->nlp->extractAirportEntity($normalized);

        $searchName = $airportEntity['name'] ?? $airportEntity['iata'] ?? $this->cleanSearchName($message);

        if ($searchName === '') {
            return 'Silakan sebutkan nama atau kode bandara yang ingin kamu cari. Contoh: "Profil Bandara Kualanamu" atau "Info bandara CGK".';
        }

        $searchLiteral = json_encode(mb_strtolower($searchName), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $searchCode = strtoupper($searchName);
        $codeLiteral = json_encode($searchCode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $query = "
            PREFIX af: <https://aerofind.example/id/>
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

            SELECT DISTINCT ?name ?airportId ?ident ?airportType ?countryCode ?regionCode ?municipality ?scheduledService ?icaoCode ?iataCode ?latitude ?longitude ?elevationFt
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
                        CONTAINS(LCASE(STR(?name)), {$searchLiteral})
                        || STR(?iataCode) = {$codeLiteral}
                        || STR(?icaoCode) = {$codeLiteral}
                        || CONTAINS(LCASE(STR(?municipality)), {$searchLiteral})
                    )
                }
            }
            LIMIT 10
        ";

        try {
            $result = $this->fuseki->select($query);
            $bindings = $result['results']['bindings'] ?? [];
            
            $bindings = collect($bindings)->unique(function ($item) {
                return ($item['iataCode']['value'] ?? '') !== '-' && !empty($item['iataCode']['value']) 
                    ? $item['iataCode']['value'] 
                    : ($item['name']['value'] ?? '');
            })->values()->all();

        } catch (\Throwable $e) {
            Log::warning('SPARQL Fuseki query failed, attempting local fallback: ' . $e->getMessage());
            $bindings = [];
        }

        if (empty($bindings)) {
            $local = null;

            if (!$local) {
                $local = $this->findAirportFromDataset($searchName, $searchCode);
            }

            if ($local) {
                $name = $local->name ?? 'Bandara';
                $airportId = $local->airport_id ?? '-';
                $iata = $local->iata_code ?: 'Tidak tersedia';
                $icao = $local->icao_code ?: 'Tidak tersedia';
                $ident = $local->ident ?? $icao;
                $type = $local->type ?: 'Tidak tersedia';
                $city = $local->municipality ?: 'Tidak tersedia';
                $country = $local->iso_country ?? 'ID';
                $regionCode = $local->iso_region ?? 'Tidak tersedia';
                $lat = $local->latitude_deg ?? 'Tidak tersedia';
                $lng = $local->longitude_deg ?? 'Tidak tersedia';
                $elev = $local->elevation_ft ?? 'Tidak tersedia';
                $service = $local->scheduled_service ?? 'Tidak tersedia';

                return "Berikut adalah informasi tentang bandara {$name}:\n\n" .
                    "ID Bandara: {$airportId}\n" .
                    "Nama: {$name}\n" .
                    "Kode IATA: {$iata}\n" .
                    "Kode ICAO: {$icao}\n" .
                    "Identifikasi: {$ident}\n" .
                    "Tipe: {$type}\n" .
                    "Kota/Kabupaten: {$city}\n" .
                    "Negara: {$country}\n" .
                    "Kode Wilayah: {$regionCode}\n" .
                    "Latitude: {$lat}\n" .
                    "Longitude: {$lng}\n" .
                    "Elevasi (ft): {$elev}\n" .
                    "Layanan terjadwal: {$service}\n\n" .
                    "Sumber data: Direktori bandara AeroFind.";
            }

            return "Maaf, saya tidak menemukan bandara dengan kata kunci {$searchName} di dataset RDF AeroFind.";
        }

        if (count($bindings) > 1) {
            $reply = "Saya menemukan beberapa bandara yang cocok:\n\n";

            foreach ($bindings as $index => $item) {
                $name = $item['name']['value'] ?? '-';
                $iata = $item['iataCode']['value'] ?? '-';
                $municipality = $item['municipality']['value'] ?? '';

                $reply .= ($index + 1) . ". {$name}\n";
                $reply .= "   Kode IATA: {$iata}\n";
                if ($municipality) {
                    $reply .= "   Lokasi: {$municipality}\n";
                }
                $reply .= "\n";
            }

            $reply .= 'Silakan gunakan nama bandara atau kode IATA yang lebih spesifik.';
            return $reply;
        }

        $airport = $bindings[0];
        $getValue = function ($key) use ($airport) {
            return $airport[$key]['value'] ?? 'Tidak tersedia';
        };

        return "Berikut adalah informasi tentang bandara {$getValue('name')}:\n\n" .
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

    private function searchAirportsByCountry(string $message): string
    {
        $normalized = $this->nlp->normalize($message);
        
        $countryMap = [
            'indonesia'  => ['code' => 'ID', 'name' => 'Indonesia'],
            'malaysia'   => ['code' => 'MY', 'name' => 'Malaysia'],
            'thailand'   => ['code' => 'TH', 'name' => 'Thailand'],
            'singapura'  => ['code' => 'SG', 'name' => 'Singapura'],
            'singapore'  => ['code' => 'SG', 'name' => 'Singapura'],
            'filipina'   => ['code' => 'PH', 'name' => 'Filipina'],
            'philippines'=> ['code' => 'PH', 'name' => 'Filipina'],
            'vietnam'    => ['code' => 'VN', 'name' => 'Vietnam'],
            'brunei'     => ['code' => 'BN', 'name' => 'Brunei Darussalam'],
            'kamboja'    => ['code' => 'KH', 'name' => 'Kamboja'],
            'cambodia'   => ['code' => 'KH', 'name' => 'Kamboja'],
            'laos'       => ['code' => 'LA', 'name' => 'Laos'],
            'myanmar'    => ['code' => 'MM', 'name' => 'Myanmar'],
            'burma'      => ['code' => 'MM', 'name' => 'Myanmar'],
        ];

        $countryCode = 'ID';
        $countryName = 'Indonesia';

        foreach ($countryMap as $key => $info) {
            if (str_contains($normalized, $key)) {
                $countryCode = $info['code'];
                $countryName = $info['name'];
                break;
            }
        }

        $isCountQuestion = str_contains($normalized, 'berapa jumlah') ||
                           str_contains($normalized, 'berapa banyak') ||
                           str_contains($normalized, 'total bandara') ||
                           str_contains($normalized, 'jumlah bandara');

        if ($isCountQuestion) {
            $query = "
                PREFIX af: <https://aerofind.example/id/>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>

                SELECT (COUNT(DISTINCT ?airport) AS ?total)
                WHERE {
                    GRAPH <https://aerofind.example/graph/airports> {
                        ?airport rdf:type af:Airport ;
                                 af:countryCode \"{$countryCode}\" .
                    }
                }
            ";

            try {
                $result = $this->fuseki->select($query);
                $total = $result['results']['bindings'][0]['total']['value'] ?? null;
            } catch (\Throwable $e) {
                $total = null;
            }

            if (!$total) {
                try {
                    $total = $this->airportsFromJson()->filter(fn ($a) => str_starts_with((string) $a->iso_region, $countryCode . '-'))->count();
                } catch (\Throwable $e) {
                    $total = null;
                }
            }

            if (!$total) {
                return "Maaf, data bandara untuk {$countryName} belum tersedia di dataset RDF AeroFind.";
            }

            return "Berdasarkan dataset RDF AeroFind, terdapat {$total} data bandara di {$countryName}.";
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
                             af:countryCode \"{$countryCode}\" .

                    OPTIONAL { ?airport af:iataCode ?iataCode }
                    OPTIONAL { ?airport af:municipality ?municipality }
                }
            }
            ORDER BY ?name
            LIMIT 10
        ";

        try {
            $result = $this->fuseki->select($query);
            $bindings = $result['results']['bindings'] ?? [];
        } catch (\Throwable $e) {
            $bindings = [];
        }

        if (empty($bindings)) {
            try {
                $airports = $this->airportsFromJson()->filter(fn ($a) => str_starts_with((string) $a->iso_region, $countryCode . '-'))->take(10)->values();
                $bindings = $airports->map(fn ($a) => [
                    'name' => ['value' => $a->name],
                    'iataCode' => ['value' => $a->iata_code ?? '-'],
                    'municipality' => ['value' => $a->municipality ?? '-'],
                ])->toArray();
            } catch (\Throwable $e) {
                $bindings = [];
            }
        }

        if (empty($bindings)) {
            return "Belum ditemukan data bandara {$countryName} pada dataset RDF AeroFind.";
        }

        $reply = "Berikut beberapa bandara di {$countryName} berdasarkan dataset RDF AeroFind:\n\n";

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

    private function getNearestAirportsFromJena(float $lat, float $lng, int $limit = 5): Collection
    {
        $sparql = "
            PREFIX af: <https://aerofind.example/id/>
            PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

            SELECT DISTINCT ?name ?iataCode ?icaoCode ?municipality ?type ?latitude ?longitude
            WHERE {
                GRAPH <https://aerofind.example/graph/airports> {
                    ?airport rdf:type af:Airport ;
                             rdfs:label ?name ;
                             af:latitude ?latitude ;
                             af:longitude ?longitude .
                    OPTIONAL { ?airport af:iataCode ?iataCode }
                    OPTIONAL { ?airport af:icaoCode ?icaoCode }
                    OPTIONAL { ?airport af:municipality ?municipality }
                    OPTIONAL { ?airport af:airportType ?type }
                }
            }
        ";

        try {
            $result = $this->fuseki->select($sparql);
            $bindings = $result['results']['bindings'] ?? [];

            if (!empty($bindings)) {
                $airports = [];
                foreach ($bindings as $item) {
                    $aLat = (float) ($item['latitude']['value'] ?? null);
                    $aLng = (float) ($item['longitude']['value'] ?? null);

                    if (!$aLat || !$aLng) {
                        continue;
                    }

                    $dist = $this->calculateHaversineDistance($lat, $lng, $aLat, $aLng);

                    $airports[] = (object) [
                        'name' => $item['name']['value'] ?? 'Bandara',
                        'iata_code' => $item['iataCode']['value'] ?? null,
                        'icao_code' => $item['icaoCode']['value'] ?? null,
                        'municipality' => $item['municipality']['value'] ?? null,
                        'type' => $item['type']['value'] ?? 'medium_airport',
                        'distance_km' => $dist,
                    ];
                }

                if (!empty($airports)) {
                    usort($airports, fn ($a, $b) => $a->distance_km <=> $b->distance_km);
                    return collect(array_slice($airports, 0, $limit));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Fuseki nearest query failed, fallback to local service: ' . $e->getMessage());
        }

        try {
            $airports = $this->recommendation->nearestFromCoordinates($lat, $lng, $limit);
            if ($airports->isNotEmpty()) {
                return $airports;
            }
        } catch (\Throwable $e) {}

        return $this->calculateNearestFromJson($lat, $lng, $limit);
    }

    private function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function calculateNearestFromJson(float $lat, float $lng, int $limit = 5): Collection
    {
        $jsonPath = database_path('data/dataset_bandara_tubes.json');
        if (!file_exists($jsonPath)) {
            return collect();
        }

        $content = file_get_contents($jsonPath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return collect();
        }

        $results = [];
        foreach ($data as $item) {
            $aLat = (float) ($item['latitude_deg'] ?? null);
            $aLng = (float) ($item['longitude_deg'] ?? null);

            if (!$aLat || !$aLng) {
                continue;
            }

            $dist = $this->calculateHaversineDistance($lat, $lng, $aLat, $aLng);

            $results[] = (object) [
                'name' => $item['name'] ?? 'Bandara',
                'iata_code' => $item['iata_code'] ?? null,
                'icao_code' => $item['icao_code'] ?? null,
                'municipality' => $item['municipality'] ?? null,
                'type' => $item['type'] ?? 'medium_airport',
                'distance_km' => $dist,
            ];
        }

        usort($results, fn ($a, $b) => $a->distance_km <=> $b->distance_km);

        return collect(array_slice($results, 0, $limit));
    }

    private function cleanSearchName(string $message): string
    {
        $normalized = $this->nlp->normalize($message);

        $stopWords = [
            'berikan', 'beri', 'tolong', 'coba', 'saya',
            'ingin', 'minta', 'carikan', 'tampilkan',
            'jelaskan', 'ceritakan', 'profil', 'profile',
            'informasi', 'detail', 'data', 'tentang',
            'mengenai', 'dari', 'bandara', 'airport',
            'apa', 'itu', 'yang', 'ada', 'di', 'untuk',
            'dong', 'kamu', 'bisa', 'kah', 'saja', 'tuh', 'nih'
        ];

        foreach ($stopWords as $word) {
            $normalized = preg_replace(
                '/\b' . preg_quote($word, '/') . '\b/u',
                ' ',
                $normalized
            );
        }

        return trim(preg_replace('/\s+/', ' ', $normalized));
    }

    private function findAirportFromDataset(string $searchName, string $searchCode): ?object
    {
        $jsonPath = database_path('data/dataset_bandara_tubes.json');
        if (!file_exists($jsonPath)) {
            return null;
        }

        $content = file_get_contents($jsonPath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return null;
        }

        $searchLower = mb_strtolower($searchName);

        foreach ($data as $item) {
            $name = $item['name'] ?? '';
            $iata = strtoupper($item['iata_code'] ?? '');
            $icao = strtoupper($item['icao_code'] ?? '');

            if (
                str_contains(mb_strtolower($name), $searchLower) ||
                ($searchCode !== '' && ($iata === $searchCode || $icao === $searchCode))
            ) {
                return (object) [
                    'airport_id'        => $item['airport_id'] ?? '-',
                    'name'              => $item['name'] ?? 'Bandara',
                    'iata_code'         => $item['iata_code'] ?? null,
                    'icao_code'         => $item['icao_code'] ?? null,
                    'ident'             => $item['ident'] ?? null,
                    'type'              => $item['type'] ?? 'medium_airport',
                    'municipality'      => $item['municipality'] ?? '-',
                    'iso_country'       => $item['iso_country'] ?? 'ID',
                    'iso_region'        => $item['iso_region'] ?? '-',
                    'latitude_deg'      => $item['latitude_deg'] ?? null,
                    'longitude_deg'     => $item['longitude_deg'] ?? null,
                    'elevation_ft'      => $item['elevation_ft'] ?? null,
                    'scheduled_service' => $item['scheduled_service'] ?? 'yes',
                ];
            }
        }

        return null;
    }

    private function getAirportsListFromJson(int $limit = 10): array
    {
        $jsonPath = database_path('data/dataset_bandara_tubes.json');
        if (!file_exists($jsonPath)) {
            return [];
        }

        $content = file_get_contents($jsonPath);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return [];
        }

        $results = [];
        foreach ($data as $item) {
            if (($item['iso_country'] ?? '') === 'ID') {
                $results[] = [
                    'name'         => ['value' => $item['name'] ?? 'Bandara'],
                    'iataCode'     => ['value' => $item['iata_code'] ?? '-'],
                    'municipality' => ['value' => $item['municipality'] ?? '-'],
                ];
            }

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }
}