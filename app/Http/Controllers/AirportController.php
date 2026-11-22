<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Services\ExternalSparqlService;
use App\Services\FusekiService;

class AirportController extends Controller
{
    /** Cache data JSON supaya file tidak dibaca berulang dalam satu request. */
    private ?array $airportsCache = null;

    public function __construct(
        private FusekiService $fuseki,
        private ExternalSparqlService $external,
    ) {
    }

    /** Pemetaan nama negara ke kode ISO dan alias untuk fitur pencarian. */
    private const ISO_MAPPING = [
        // Kode ISO ke nama negara di dataset
        'id'                => ['indonesia'],
        'my'                => ['malaysia'],
        'sg'                => ['singapore', 'singapura'],
        'th'                => ['thailand'],
        'ph'                => ['filipina', 'philippines'],
        'vn'                => ['vietnam'],
        'kh'                => ['kamboja', 'cambodia'],
        'la'                => ['laos'],
        'mm'                => ['myanmar'],
        'bn'                => ['brunei darussalam', 'brunei'],
        'tl'                => ['timor leste', 'east timor'],

        // Nama negara dan variasinya ke kode ISO & nama di dataset
        'indonesia'         => ['id', 'indonesia'],
        'malaysia'          => ['my', 'malaysia'],
        'singapura'         => ['sg', 'singapore'],
        'singapore'         => ['sg', 'singapore'],
        'thailand'          => ['th', 'thailand'],
        'filipina'          => ['ph', 'filipina'],
        'philippines'       => ['ph', 'filipina'],
        'vietnam'           => ['vn', 'vietnam'],
        'kamboja'           => ['kh', 'kamboja'],
        'cambodia'          => ['kh', 'kamboja'],
        'laos'              => ['la', 'laos'],
        'myanmar'           => ['mm', 'myanmar'],
        'brunei'            => ['bn', 'brunei darussalam'],
        'brunei darussalam' => ['bn', 'brunei darussalam'],
        'timor leste'       => ['tl', 'timor leste'],
        'east timor'        => ['tl', 'timor leste'],
    ];

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function getAirportsData(): array
    {
        if ($this->airportsCache !== null) {
            return $this->airportsCache;
        }

        // Sumber utama: dataset RDF di Apache Jena Fuseki (via SPARQL)
        $fromFuseki = $this->fuseki->airports();
        if ($fromFuseki !== []) {
            return $this->airportsCache = $fromFuseki;
        }

        // Cadangan bila Fuseki tidak aktif: berkas JSON lokal
        $possiblePaths = [
            database_path('data/dataset_bandara_tubes.json'),
            database_path('data/dataset_bandara_tubes'),
            database_path('data/dataset_bandara_tubes.txt'),
            storage_path('app/dataset_bandara_tubes.json'),
            public_path('dataset_bandara_tubes.json'),
        ];

        $json = null;

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $json = file_get_contents($path);
                break;
            }
        }

        if (!$json) {
            return $this->airportsCache = [];
        }

        // Hapus BOM UTF-8 jika ada
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
        $data = json_decode($json, true);

        return $this->airportsCache = (is_array($data) ? $data : []);
    }

    private function paginate(Collection $items, int $perPage = 10): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path'  => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    // ------------------------------------------------------------------
    // Halaman utama + pencarian
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $rawSearch = $request->input('search');
        $search    = is_string($rawSearch) ? trim($rawSearch) : null;
        if ($search === '') {
            $search = null;
        }

        $airports = $this->getAirportsData();
        $all = collect($airports);

        $stats = [
            'total'     => $all->count(),
            'countries' => $all->pluck('iso_country')->filter()->unique()->count(),
            'large'     => $all->where('type', 'large_airport')->count(),
            'website'   => $all->filter(fn ($a) => !empty($a['home_link']))->count(),
        ];

        // Bandara unggulan: pilih berdasarkan kode ICAO
        $featuredCodes = ['WIII', 'WSSS', 'VTBS', 'WMKK', 'RPLL', 'VVTS'];
        $featured = $all->whereIn('icao_code', $featuredCodes)->values();

        // Bandara dengan koordinat valid untuk marker peta
        $mapAirports = array_values(array_filter($airports, function ($airport) {
            return isset($airport['latitude_deg'], $airport['longitude_deg'])
                && is_numeric($airport['latitude_deg'])
                && is_numeric($airport['longitude_deg'])
                && $airport['latitude_deg'] >= -90
                && $airport['latitude_deg'] <= 90
                && $airport['longitude_deg'] >= -180
                && $airport['longitude_deg'] <= 180;
        }));

        $suggestion = null;
        $suggestedAirports = [];

        if ($search !== null) {
            $searchLower = mb_strtolower($search);
            $mappedTerms = self::ISO_MAPPING[$searchLower] ?? [];

            $airports = array_values(array_filter(
                $airports,
                fn ($airport) => $this->matchesSearch($airport, $searchLower, $mappedTerms)
            ));

            // Deteksi saran typo jika pencarian persis/substring tidak menghasilkan data
            if (empty($airports)) {
                $suggestion = $this->findTypoSuggestion($search, $all->all());

                if ($suggestion !== null) {
                    $suggLower = mb_strtolower($suggestion['term']);
                    $suggTerms = self::ISO_MAPPING[$suggLower] ?? [];

                    $suggestedAirports = array_values(array_filter($all->all(), function ($airport) use ($suggLower, $suggTerms) {
                        $name         = mb_strtolower($airport['name'] ?? '');
                        $municipality = mb_strtolower($airport['municipality'] ?? '');
                        $isoCountry   = mb_strtolower($airport['iso_country'] ?? '');
                        $ident        = mb_strtolower($airport['ident'] ?? '');
                        $iata         = mb_strtolower($airport['iata_code'] ?? '');
                        $icao         = mb_strtolower($airport['icao_code'] ?? '');

                        if (
                            str_contains($name, $suggLower)
                            || str_contains($municipality, $suggLower)
                            || str_contains($ident, $suggLower)
                            || str_contains($iata, $suggLower)
                            || str_contains($icao, $suggLower)
                        ) {
                            return true;
                        }

                        foreach ($suggTerms as $term) {
                            if ($isoCountry === $term || str_contains($isoCountry, $term)) {
                                return true;
                            }
                        }

                        return false;
                    }));
                }
            }
        }

        return view('welcome', compact('airports', 'search', 'mapAirports', 'stats', 'featured', 'suggestion', 'suggestedAirports'));
    }

    /**
     * Apakah sebuah bandara cocok dengan kata kunci pencarian (nama, kota, kode, negara, alias).
     *
     * @param  array<string, mixed>  $airport
     * @param  list<string>  $mappedTerms
     */
    private function matchesSearch(array $airport, string $searchLower, array $mappedTerms): bool
    {
        $name         = mb_strtolower($airport['name'] ?? '');
        $municipality = mb_strtolower($airport['municipality'] ?? '');
        $isoCountry   = mb_strtolower($airport['iso_country'] ?? '');
        $isoRegion    = mb_strtolower($airport['iso_region'] ?? '');
        $ident        = mb_strtolower($airport['ident'] ?? '');
        $iata         = mb_strtolower($airport['iata_code'] ?? '');
        $icao         = mb_strtolower($airport['icao_code'] ?? '');
        $keywords     = mb_strtolower($airport['keywords'] ?? '');
        $airportId    = mb_strtolower($airport['airport_id'] ?? '');

        // Partial / substring match pada nama, kota, ident, IATA, ICAO, kata kunci, ID bandara
        if (
            str_contains($name, $searchLower)
            || str_contains($municipality, $searchLower)
            || str_contains($ident, $searchLower)
            || str_contains($iata, $searchLower)
            || str_contains($icao, $searchLower)
            || str_contains($keywords, $searchLower)
            || str_contains($airportId, $searchLower)
            || str_contains($isoCountry, $searchLower)
            || str_contains($isoRegion, $searchLower)
        ) {
            return true;
        }

        // Pencarian berdasarkan kode ISO atau alias negara
        foreach ($mappedTerms as $term) {
            if (
                $isoCountry === $term
                || str_contains($isoCountry, $term)
                || str_starts_with($isoRegion, $term . '-')
                || $isoRegion === $term
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Saran pencarian huruf per huruf untuk dashboard utama.
     */
    public function suggest(Request $request)
    {
        $rawQuery = $request->query('q');
        $query = is_string($rawQuery) ? trim($rawQuery) : '';

        if (mb_strlen($query) < 1) {
            return response()->json(['query' => '', 'results' => [], 'total' => 0, 'suggestion' => null]);
        }

        $queryLower = mb_strtolower($query);
        $mappedTerms = self::ISO_MAPPING[$queryLower] ?? [];
        $airports = $this->getAirportsData();

        $matches = array_values(array_filter(
            $airports,
            fn ($airport) => $this->matchesSearch($airport, $queryLower, $mappedTerms)
        ));

        // Urutkan: awalan nama/kode lebih dulu, lalu urut abjad
        $rank = function (array $airport) use ($queryLower): int {
            $name = mb_strtolower($airport['name'] ?? '');
            $codes = [
                mb_strtolower($airport['iata_code'] ?? ''),
                mb_strtolower($airport['icao_code'] ?? ''),
                mb_strtolower($airport['ident'] ?? ''),
            ];

            return match (true) {
                in_array($queryLower, $codes, true) => 0,
                str_starts_with($name, $queryLower) => 1,
                str_contains($name, $queryLower) => 2,
                default => 3,
            };
        };

        usort($matches, fn ($a, $b) => [$rank($a), mb_strtolower($a['name'] ?? '')]
            <=> [$rank($b), mb_strtolower($b['name'] ?? '')]);

        $suggestion = null;
        if ($matches === []) {
            $typo = $this->findTypoSuggestion($query, $airports);
            $suggestion = $typo['term'] ?? null;
        }

        $results = array_map(fn ($airport) => [
            'name' => $airport['name'] ?? '',
            'iata' => $airport['iata_code'] ?? null,
            'icao' => $airport['icao_code'] ?? $airport['ident'] ?? null,
            'municipality' => $airport['municipality'] ?? null,
            'country' => $airport['iso_country'] ?? null,
            'type' => $airport['type'] ?? null,
            'url' => route('airports.show', $airport['ident'] ?? $airport['icao_code'] ?? $airport['iata_code'] ?? $airport['airport_id'] ?? 0),
        ], array_slice($matches, 0, 8));

        return response()->json([
            'query' => $query,
            'results' => $results,
            'total' => count($matches),
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * Mendeteksi saran koreksi typo berdasarkan nama bandara, kota/municipality, atau negara.
     * Menggunakan algoritma levenshtein() dan similar_text().
     */
    private function findTypoSuggestion(string $search, array $allAirports): ?array
    {
        $query = mb_strtolower(trim($search));
        $queryLen = mb_strlen($query);

        if ($queryLen < 3) {
            return null;
        }

        // Kumpulan kandidat kata unik dari nama bandara, kota, dan negara
        $candidates = [];

        // 1. Nama negara dan variasinya
        foreach (self::ISO_MAPPING as $countryKey => $aliases) {
            if (is_string($countryKey) && strlen($countryKey) >= 3) {
                $candidates[$countryKey] = ucwords($countryKey);
            }
            foreach ($aliases as $alias) {
                if (strlen($alias) >= 3) {
                    $candidates[$alias] = ucwords($alias);
                }
            }
        }

        // 2. Kota / municipality dan kata signifikan dari nama bandara
        $stopWords = [
            'airport', 'international', 'bandara', 'internasional',
            'aerodrome', 'airfield', 'domestic', 'the', 'and', 'of',
            'dan', 'lapangan', 'terbang'
        ];

        foreach ($allAirports as $airport) {
            if (!empty($airport['municipality'])) {
                $muni = trim($airport['municipality']);
                $candidates[mb_strtolower($muni)] = $muni;
            }

            if (!empty($airport['name'])) {
                $words = preg_split('/[\s,\.\-\(\)\/]+/u', $airport['name'], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($words as $word) {
                    $wLower = mb_strtolower($word);
                    if (mb_strlen($wLower) >= 3 && !in_array($wLower, $stopWords, true)) {
                        $candidates[$wLower] = $word;
                    }
                }
            }
        }

        $bestDisplay = null;
        $bestDist    = 999;
        $bestSim     = 0;

        foreach ($candidates as $termLower => $display) {
            if ($termLower === $query) {
                continue;
            }

            $dist = levenshtein($query, $termLower);
            similar_text($query, $termLower, $percent);

            // Toleransi jarak edit berdasarkan panjang karakter
            $maxAllowedDist = match (true) {
                $queryLen <= 4 => 1,
                $queryLen <= 7 => 2,
                default        => 3,
            };

            $minAllowedSim = match (true) {
                $queryLen <= 4 => 75.0,
                $queryLen <= 7 => 68.0,
                default        => 65.0,
            };

            if ($dist <= $maxAllowedDist && $percent >= $minAllowedSim) {
                if ($dist < $bestDist || ($dist === $bestDist && $percent > $bestSim)) {
                    $bestDist    = $dist;
                    $bestSim     = $percent;
                    $bestDisplay = $display;
                }
            }
        }

        if ($bestDisplay !== null) {
            return [
                'term'       => $bestDisplay,
                'display'    => $bestDisplay,
                'distance'   => $bestDist,
                'similarity' => round($bestSim, 1),
            ];
        }

        return null;
    }

    // ------------------------------------------------------------------
    // Detail bandara
    // ------------------------------------------------------------------

    public function show($id)
    {
        $id      = trim((string) $id);
        $airport = null;
        $data    = $this->getAirportsData();

        // 1) Cari di JSON. Kode (ICAO/IATA) dicek duluan, ID angka terakhir secara case-insensitive
        foreach (['ident', 'icao_code', 'iata_code', 'airport_id', 'id'] as $key) {
            foreach ($data as $item) {
                if (isset($item[$key]) && strcasecmp((string) $item[$key], $id) === 0) {
                    $airport = $item;
                    break 2;
                }
            }
        }

        if (!$airport) {
            abort(404, 'Airport not found');
        }

        $semantic = $this->semanticEnrichment($airport);

        return view('airports.show', compact('airport', 'semantic'));
    }

    /**
     * Data tambahan dari DBpedia. Lebih dulu lewat kueri federasi Fuseki (SERVICE),
     * lalu langsung ke endpoint DBpedia bila federasi tidak tersedia.
     *
     * @param  array<string, mixed>  $airport
     * @return array<string, mixed>|null
     */
    private function semanticEnrichment(array $airport): ?array
    {
        $icao = (string) ($airport['icao_code'] ?? $airport['ident'] ?? '');
        $wikipedia = $airport['wikipedia_link'] ?? null;

        if ($wikipedia === null && $icao !== '') {
            foreach ($this->getAirportsData() as $item) {
                if (strcasecmp((string) ($item['icao_code'] ?? ''), $icao) === 0) {
                    $wikipedia = $item['wikipedia_link'] ?? null;
                    break;
                }
            }
        }

        if ($wikipedia === null) {
            return null;
        }

        $result = $icao !== '' ? $this->fuseki->federatedEnrichment($icao) : null;
        $source = 'Fuseki SERVICE → DBpedia';

        if ($result === null) {
            $result = $this->external->enrichmentForWikipediaLink($wikipedia, $icao !== '' ? $icao : null);
            $source = 'DBpedia';
        }

        return $result === null ? null : $result + ['source' => $source, 'wikipedia' => $wikipedia];
    }

    // ------------------------------------------------------------------
    // Halaman kategori
    // ------------------------------------------------------------------

    public function locationCategory()
    {
        $aseanCountries = [
            ['code' => 'ID', 'name' => __('messages.country_id')],
            ['code' => 'MY', 'name' => __('messages.country_my')],
            ['code' => 'SG', 'name' => __('messages.country_sg')],
            ['code' => 'TH', 'name' => __('messages.country_th')],
            ['code' => 'PH', 'name' => __('messages.country_ph')],
            ['code' => 'VN', 'name' => __('messages.country_vn')],
            ['code' => 'KH', 'name' => __('messages.country_kh')],
            ['code' => 'LA', 'name' => __('messages.country_la')],
            ['code' => 'MM', 'name' => __('messages.country_mm')],
            ['code' => 'BN', 'name' => __('messages.country_bn')],
            ['code' => 'TL', 'name' => __('messages.country_tl')],
        ];

        return view('airports.categories-location', compact('aseanCountries'));
    }

    public function phoneCategory()
    {
        $airports = $this->paginate(collect($this->getAirportsData()));

        return view('airports.categories-phone', compact('airports'));
    }

    public function operationalCategory()
    {
        $airports = $this->paginate(collect($this->getAirportsData()));

        return view('airports.categories-operational', compact('airports'));
    }

    public function directory(Request $request)
    {
        $type        = $request->query('type');
        $country     = $request->query('country');
        $rawQ        = $request->query('q');
        $q           = is_string($rawQ) ? mb_strtolower(trim($rawQ)) : '';
        $sort        = $request->query('sort', 'name');

        $all         = collect($this->getAirportsData());
        $countries   = $all->pluck('iso_country')->filter()->unique()->sort()->values();
        $mappedTerms = $q !== '' ? (self::ISO_MAPPING[$q] ?? []) : [];

        // Negara & pencarian diterapkan dulu, supaya angka di kartu jenis ikut berubah
        $base = $all
            ->when($country, fn ($c) => $c->where('iso_country', $country))
            ->when($q !== '', fn ($c) => $c->filter(function ($a) use ($q, $mappedTerms) {
                $text = implode(' ', [
                    $a['name'] ?? '',
                    $a['municipality'] ?? '',
                    $a['icao_code'] ?? '',
                    $a['iata_code'] ?? '',
                    $a['ident'] ?? '',
                    $a['keywords'] ?? '',
                    $a['airport_id'] ?? '',
                    $a['iso_country'] ?? '',
                    $a['iso_region'] ?? '',
                ]);

                if (str_contains(mb_strtolower($text), $q)) {
                    return true;
                }

                $isoCountry = mb_strtolower($a['iso_country'] ?? '');
                $isoRegion  = mb_strtolower($a['iso_region'] ?? '');
                foreach ($mappedTerms as $term) {
                    if (
                        $isoCountry === $term
                        || str_contains($isoCountry, $term)
                        || str_starts_with($isoRegion, $term . '-')
                    ) {
                        return true;
                    }
                }

                return false;
            }));

        $typeCounts = $base->countBy(fn ($a) => $a['type'] ?? 'unknown');

        $filtered = $base->when($type, fn ($c) => $c->where('type', $type));

        $sorted = match ($sort) {
            'elevation' => $filtered->sortByDesc(fn ($a) => $a['elevation_ft'] ?? -1),
            default     => $filtered->sortBy(fn ($a) => mb_strtolower($a['name'] ?? '')),
        };

        $airports = $this->paginate($sorted->values(), 12);

        return view('airports.directory', compact(
            'airports', 'typeCounts', 'countries', 'type', 'country', 'q', 'sort'
        ));
    }

}