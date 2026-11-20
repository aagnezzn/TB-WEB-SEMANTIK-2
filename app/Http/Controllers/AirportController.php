<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AirportController extends Controller
{
    private function getAirportsData(): array
    {
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
            return [];
        }

        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $airports = $this->getAirportsData();

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

            $isoMapping = [
                'indonesia' => 'id',
                'malaysia' => 'my',
                'singapura' => 'sg',
                'singapore' => 'sg',
                'thailand' => 'th',
                'filipina' => 'ph',
                'philippines' => 'ph',
                'vietnam' => 'vn',
                'kamboja' => 'kh',
                'cambodia' => 'kh',
                'laos' => 'la',
                'myanmar' => 'mm',
                'brunei' => 'bn',
                'brunei darussalam' => 'bn',
                'timor leste' => 'tl',
            ];

            $targetIso = $isoMapping[$searchLower] ?? $searchLower;

            $airports = array_filter($airports, function ($airport) use ($searchLower, $targetIso) {
                $name = strtolower($airport['name'] ?? '');
                $municipality = strtolower($airport['municipality'] ?? '');
                $isoCountry = strtolower($airport['iso_country'] ?? '');
                $ident = strtolower($airport['ident'] ?? '');
                $iata = strtolower($airport['iata_code'] ?? '');

                return str_contains($name, $searchLower)
                    || str_contains($municipality, $searchLower)
                    || str_contains($ident, $searchLower)
                    || str_contains($iata, $searchLower)
                    || $isoCountry === $searchLower
                    || $isoCountry === $targetIso;
            });
        }

        return view('welcome', compact(
            'airports',
            'search',
            'mapAirports'
        ));
    }

    public function show($id)
    {
        $allAirports = $this->getAirportsData();
        $airport = null;
        $target = strtolower(trim((string) $id));

        foreach ($allAirports as $item) {
            $candidates = array_filter([
                $item['airport_id'] ?? null,
                $item['ident'] ?? null,
                $item['iata_code'] ?? null,
                $item['icao_code'] ?? null,
                $item['id'] ?? null,
            ], fn ($val) => $val !== null && $val !== '');

            foreach ($candidates as $candidate) {
                if (strtolower(trim((string) $candidate)) === $target) {
                    $airport = $item;
                    break 2;
                }
            }
        }

        if (!$airport) {
            $model = Airport::where('airport_id', $id)
                ->orWhere('iata_code', strtoupper((string) $id))
                ->orWhere('icao_code', strtoupper((string) $id))
                ->first();

            if ($model) {
                $airport = $model->toArray();
            }
        }

        if (!$airport) {
            abort(404, 'Bandara tidak ditemukan');
        }

        $nearestRegion = $this->findNearestRegion(
            $airport['latitude_deg'] ?? null,
            $airport['longitude_deg'] ?? null,
            $airport['iso_country'] ?? null
        );

        $fusekiId = (string) ($airport['airport_id'] ?? $id);
        $sparqlInfo = $this->fetchFusekiAirportInfo($fusekiId);
        $dbpediaAbstract = $this->fetchDbpediaAbstract($airport['wikipedia_link'] ?? null);

        return view('airports.show', compact(
            'airport',
            'nearestRegion',
            'sparqlInfo',
            'dbpediaAbstract'
        ));
    }

    private function findNearestRegion($lat, $lon, $isoCountry): ?array
    {
        if (!is_numeric($lat) || !is_numeric($lon)) {
            return null;
        }

        $regionPaths = [
            database_path('data/regions_southeast_asia.csv'),
            storage_path('app/regions_southeast_asia.csv'),
        ];

        $csvPath = null;
        foreach ($regionPaths as $path) {
            if (file_exists($path)) {
                $csvPath = $path;
                break;
            }
        }

        if (!$csvPath) {
            return null;
        }

        $countryMap = [
            'indonesia' => 'Indonesia',
            'filipina' => 'Philippines',
            'philippines' => 'Philippines',
            'malaysia' => 'Malaysia',
            'thailand' => 'Thailand',
            'myanmar' => 'Myanmar',
            'vietnam' => 'Vietnam',
            'kamboja' => 'Cambodia',
            'cambodia' => 'Cambodia',
            'laos' => 'Laos',
            'timor leste' => 'Timor-Leste',
            'timor-leste' => 'Timor-Leste',
            'singapore' => 'Singapore',
            'singapura' => 'Singapore',
            'brunei darussalam' => 'Brunei Darussalam',
            'brunei' => 'Brunei Darussalam',
        ];

        $targetCountry = $countryMap[strtolower(trim((string) $isoCountry))] ?? null;

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return null;
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return null;
        }

        $best = null;
        $minDist = INF;
        $earthRadius = 6371.0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $rec = array_combine($header, $row);
            if ($targetCountry && strcasecmp($rec['country_name'] ?? '', $targetCountry) !== 0) {
                continue;
            }

            $rLat = (float) ($rec['latitude'] ?? 0);
            $rLon = (float) ($rec['longitude'] ?? 0);

            $dLat = deg2rad($rLat - (float) $lat);
            $dLon = deg2rad($rLon - (float) $lon);
            $a = sin($dLat / 2) ** 2
                + cos(deg2rad((float) $lat)) * cos(deg2rad($rLat)) * sin($dLon / 2) ** 2;
            $dist = 2 * $earthRadius * asin(min(1, sqrt($a)));

            if ($dist < $minDist) {
                $minDist = $dist;
                $best = [
                    'region_name' => $rec['region_name'] ?? '-',
                    'region_type' => $rec['region_type'] ?? 'ADM2',
                    'country_name' => $rec['country_name'] ?? '-',
                    'distance_km' => round($dist, 2),
                ];
            }
        }

        fclose($handle);

        return $best;
    }

    private function fetchFusekiAirportInfo(string $airportId): array
    {
        $endpoint = env('FUSEKI_ENDPOINT', 'http://localhost:3030/aerofind/sparql');
        $cacheKey = "fuseki_airport_{$airportId}";

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $query = <<<SPARQL
PREFIX af: <https://aerofind.example/id/>
SELECT ?icaoCode ?iataCode ?continent ?regionCode ?elevationFt ?homeLink ?wikipediaLink ?keywords
WHERE {
  ?s a af:Airport ;
     af:airportId "{$airportId}" .
  OPTIONAL { ?s af:icaoCode ?icaoCode . }
  OPTIONAL { ?s af:iataCode ?iataCode . }
  OPTIONAL { ?s af:continent ?continent . }
  OPTIONAL { ?s af:regionCode ?regionCode . }
  OPTIONAL { ?s af:elevationFt ?elevationFt . }
  OPTIONAL { ?s af:homeLink ?homeLink . }
  OPTIONAL { ?s af:wikipediaLink ?wikipediaLink . }
  OPTIONAL { ?s af:keywords ?keywords . }
}
LIMIT 1
SPARQL;

        try {
            $res = Http::timeout(2)
                ->accept('application/sparql-results+json')
                ->get($endpoint, ['query' => $query]);

            if (!$res->successful()) {
                return [];
            }

            $binding = $res->json('results.bindings.0', []);
            $out = [];
            foreach ($binding as $k => $v) {
                $out[$k] = $v['value'] ?? null;
            }

            if (!empty($out)) {
                Cache::put($cacheKey, $out, 300);
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function fetchDbpediaAbstract(?string $wikiUrl): ?string
    {
        if (!$wikiUrl) {
            return null;
        }

        $slug = basename(parse_url($wikiUrl, PHP_URL_PATH) ?? '');
        if (!$slug) {
            return null;
        }

        $cacheKey = "dbpedia_abstract_{$slug}";
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $resource = "http://dbpedia.org/resource/{$slug}";
        $query = <<<SPARQL
SELECT ?abstract WHERE {
  <{$resource}> <http://dbpedia.org/ontology/abstract> ?abstract .
  FILTER (LANG(?abstract) = 'en' || LANG(?abstract) = 'id')
}
LIMIT 1
SPARQL;

        try {
            $res = Http::timeout(3)
                ->accept('application/sparql-results+json')
                ->get('https://dbpedia.org/sparql', ['query' => $query]);

            if (!$res->successful()) {
                return null;
            }

            $abstract = $res->json('results.bindings.0.abstract.value');
            if (is_string($abstract) && $abstract !== '') {
                Cache::put($cacheKey, $abstract, 86400);
                return $abstract;
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function locationCategory()
    {
        $aseanCountries = [
            ['code' => 'ID', 'name' => 'Indonesia', 'flag' => '🇮🇩'],
            ['code' => 'MY', 'name' => 'Malaysia', 'flag' => '🇲🇾'],
            ['code' => 'SG', 'name' => 'Singapura', 'flag' => '🇸🇬'],
            ['code' => 'TH', 'name' => 'Thailand', 'flag' => '🇹🇭'],
            ['code' => 'PH', 'name' => 'Filipina', 'flag' => '🇵🇭'],
            ['code' => 'VN', 'name' => 'Vietnam', 'flag' => '🇻🇳'],
            ['code' => 'KH', 'name' => 'Kamboja', 'flag' => '🇰🇭'],
            ['code' => 'LA', 'name' => 'Laos', 'flag' => '🇱🇦'],
            ['code' => 'MM', 'name' => 'Myanmar', 'flag' => '🇲🇲'],
            ['code' => 'BN', 'name' => 'Brunei Darussalam', 'flag' => '🇧🇳'],
            ['code' => 'TL', 'name' => 'Timor Leste', 'flag' => '🇹🇱'],
        ];

        return view('airports.categories-location', compact('aseanCountries'));
    }

    public function phoneCategory()
    {
        $airports = collect($this->getAirportsData());

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $airports
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $paginatedAirports = new LengthAwarePaginator(
            $currentItems,
            $airports->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        return view('airports.categories-phone', [
            'airports' => $paginatedAirports,
        ]);
    }

    public function operationalCategory()
    {
        $airports = collect($this->getAirportsData());

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $airports
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $paginatedAirports = new LengthAwarePaginator(
            $currentItems,
            $airports->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        return view('airports.categories-operational', [
            'airports' => $paginatedAirports,
        ]);
    }
}