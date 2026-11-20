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

    /** Pemetaan nama negara ke kode ISO untuk fitur pencarian. */
    private const ISO_MAPPING = [
        'indonesia'         => 'id',
        'malaysia'          => 'my',
        'singapura'         => 'sg',
        'singapore'         => 'sg',
        'thailand'          => 'th',
        'filipina'          => 'ph',
        'philippines'       => 'ph',
        'vietnam'           => 'vn',
        'kamboja'           => 'kh',
        'cambodia'          => 'kh',
        'laos'              => 'la',
        'myanmar'           => 'mm',
        'brunei'            => 'bn',
        'brunei darussalam' => 'bn',
        'timor leste'       => 'tl',
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
        $search   = $request->input('search');
        $airports = $this->getAirportsData();

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

        if ($search) {
            $searchLower = strtolower(trim($search));
            $targetIso   = self::ISO_MAPPING[$searchLower] ?? $searchLower;

            $airports = array_filter($airports, function ($airport) use ($searchLower, $targetIso) {
                $name         = strtolower($airport['name'] ?? '');
                $municipality = strtolower($airport['municipality'] ?? '');
                $isoCountry   = strtolower($airport['iso_country'] ?? '');
                $ident        = strtolower($airport['ident'] ?? '');
                $iata         = strtolower($airport['iata_code'] ?? '');

                return str_contains($name, $searchLower)
                    || str_contains($municipality, $searchLower)
                    || str_contains($ident, $searchLower)
                    || str_contains($iata, $searchLower)
                    || $isoCountry === $searchLower
                    || $isoCountry === $targetIso;
            });
        }

        return view('welcome', compact('airports', 'search', 'mapAirports'));
    }

    // ------------------------------------------------------------------
    // Detail bandara
    // ------------------------------------------------------------------

    public function show($id)
    {
        $id      = (string) $id;
        $airport = null;
        $data    = $this->getAirportsData();

        // 1) Cari di JSON. Kode (ICAO/IATA) dicek duluan, ID angka terakhir
        //    supaya tidak tertukar antar bandara.
        foreach (['ident', 'icao_code', 'iata_code', 'airport_id', 'id'] as $key) {
            foreach ($data as $item) {
                if (isset($item[$key]) && (string) $item[$key] === $id) {
                    $airport = $item;
                    break 2;
                }
            }
        }

        // 2) Fallback ke database (untuk hasil rekomendasi terdekat)
        if (!$airport) {
            $query = DB::table('airports')
                ->where('icao_code', $id)
                ->orWhere('iata_code', $id);

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
            abort(404, 'Bandara tidak ditemukan');
        }

        return view('airports.show', compact('airport'));
    }

    // ------------------------------------------------------------------
    // Halaman kategori
    // ------------------------------------------------------------------

    public function locationCategory()
    {
        $aseanCountries = [
            ['code' => 'ID', 'name' => 'Indonesia',         'flag' => '🇮🇩'],
            ['code' => 'MY', 'name' => 'Malaysia',          'flag' => '🇲🇾'],
            ['code' => 'SG', 'name' => 'Singapura',         'flag' => '🇸🇬'],
            ['code' => 'TH', 'name' => 'Thailand',          'flag' => '🇹🇭'],
            ['code' => 'PH', 'name' => 'Filipina',          'flag' => '🇵🇭'],
            ['code' => 'VN', 'name' => 'Vietnam',           'flag' => '🇻🇳'],
            ['code' => 'KH', 'name' => 'Kamboja',           'flag' => '🇰🇭'],
            ['code' => 'LA', 'name' => 'Laos',              'flag' => '🇱🇦'],
            ['code' => 'MM', 'name' => 'Myanmar',           'flag' => '🇲🇲'],
            ['code' => 'BN', 'name' => 'Brunei Darussalam', 'flag' => '🇧🇳'],
            ['code' => 'TL', 'name' => 'Timor Leste',       'flag' => '🇹🇱'],
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
}