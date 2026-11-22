<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AirportController extends Controller
{
    /** Cache data JSON supaya file tidak dibaca berulang dalam satu request. */
    private ?array $airportsCache = null;

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

        if ($search !== null) {
            $searchLower = mb_strtolower($search);
            $mappedTerms = self::ISO_MAPPING[$searchLower] ?? [];

            $airports = array_values(array_filter($airports, function ($airport) use ($searchLower, $mappedTerms) {
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
            }));
        }

        return view('welcome', compact('airports', 'search', 'mapAirports', 'stats', 'featured'));
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

        // 2) Fallback ke database (untuk hasil rekomendasi terdekat)
        if (!$airport) {
            $query = DB::table('airports')
                ->whereRaw('LOWER(icao_code) = ?', [strtolower($id)])
                ->orWhereRaw('LOWER(iata_code) = ?', [strtolower($id)]);

            if (ctype_digit($id)) {
                $query->orWhere('airport_id', (int) $id);
            }

            $row = $query->first();

            if ($row) {
                $airport = (array) $row;
                // show.blade.php membaca kode ICAO dari field 'ident'
                $airport['ident'] = $airport['icao_code'] ?? null;
            }
        }

        if (!$airport) {
            abort(404, 'Airport not found');
        }

        return view('airports.show', compact('airport'));
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