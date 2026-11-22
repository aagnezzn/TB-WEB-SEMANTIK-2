<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class FusekiService
{
    /** Pemetaan kode negara ISO ke nama negara yang dipakai aplikasi. */
    private const COUNTRY_NAMES = [
        'ID' => 'Indonesia',
        'MY' => 'Malaysia',
        'SG' => 'Singapore',
        'TH' => 'Thailand',
        'PH' => 'Filipina',
        'VN' => 'Vietnam',
        'KH' => 'Kamboja',
        'LA' => 'Laos',
        'MM' => 'Myanmar',
        'BN' => 'Brunei Darussalam',
        'TL' => 'Timor Leste',
    ];

    public function select(string $query): array
    {
        $baseUrl = rtrim(
            config('services.fuseki.base_url'),
            '/'
        );

        $dataset = config('services.fuseki.dataset');
        $queryPath = config('services.fuseki.query_path', '/query');

        $url = "{$baseUrl}/{$dataset}{$queryPath}";

        $response = Http::timeout(15)
            ->connectTimeout(3)
            ->accept('application/sparql-results+json')
            ->asForm()
            ->post($url, [
                'query' => $query,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Kueri SPARQL ke Fuseki gagal.'
            );
        }

        return $response->json();
    }

    /**
     * Mengambil seluruh bandara dari dataset RDF di Fuseki dengan bentuk
     * array yang sama seperti dataset JSON. Hasil disimpan sementara di cache.
     * Mengembalikan array kosong bila Fuseki tidak dapat dijangkau.
     *
     * @return list<array<string, mixed>>
     */
    public function airports(): array
    {
        if (Cache::has('fuseki.down')) {
            return [];
        }

        $cached = Cache::get('fuseki.airports');
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        try {
            $bindings = $this->select($this->allAirportsQuery())['results']['bindings'] ?? [];
        } catch (Throwable $e) {
            Cache::put('fuseki.down', true, 60);

            return [];
        }

        $airports = array_map(fn (array $row): array => self::mapBindingToAirport($row), $bindings);

        if ($airports !== []) {
            Cache::put('fuseki.airports', $airports, now()->addMinutes(10));
        }

        return $airports;
    }

    /**
     * Kueri federasi: Fuseki mengambil bandara lokal lalu memanggil DBpedia
     * lewat SERVICE untuk data tambahan, dalam satu kueri SPARQL.
     *
     * @return array{dbpedia: ?string, abstract: ?string, operator: ?string, opened: ?string, thumbnail: ?string}|null
     */
    public function federatedEnrichment(string $icaoCode): ?array
    {
        if (! preg_match('/^[A-Za-z0-9]{3,8}$/', $icaoCode) || Cache::has('fuseki.down')) {
            return null;
        }

        $endpoint = ExternalSparqlService::DBPEDIA_ENDPOINT;
        $pattern = ExternalSparqlService::enrichmentPattern();
        $prefixes = ExternalSparqlService::PREFIXES;

        $query = <<<SPARQL
PREFIX af: <https://aerofind.example/id/>
{$prefixes}
SELECT ?dbpedia ?abstract ?operator ?opened ?thumbnail WHERE {
    ?airport af:icaoCode "{$icaoCode}" ;
             af:wikipediaLink ?wikipedia .
    BIND(IRI(REPLACE(STR(?wikipedia), "^https://", "http://")) AS ?wikiIri)
    SERVICE <{$endpoint}> {
        {$pattern}
    }
} LIMIT 1
SPARQL;

        try {
            $bindings = $this->select($query)['results']['bindings'] ?? [];
        } catch (Throwable $e) {
            return null;
        }

        return ExternalSparqlService::normalizeBinding($bindings[0] ?? null);
    }

    /**
     * Pola graf dasar untuk satu bandara. Nama variabel sama dengan kunci array dataset JSON.
     */
    private function airportPatterns(bool $scheduledOnly = false): string
    {
        $scheduled = $scheduledOnly
            ? '?a af:scheduledService ?scheduled_service . FILTER(LCASE(STR(?scheduled_service)) = "yes")'
            : 'OPTIONAL { ?a af:scheduledService ?scheduled_service }';

        return <<<SPARQL
    ?a a af:Airport ;
       af:airportId ?airport_id ;
       af:ident ?ident ;
       af:airportType ?type ;
       af:name ?name ;
       af:latitude ?latitude_deg ;
       af:longitude ?longitude_deg ;
       af:countryCode ?country_code ;
       af:regionCode ?iso_region .
    {$scheduled}
    OPTIONAL { ?a af:sourceId ?id }
    OPTIONAL { ?a af:elevationFt ?elevation_ft }
    OPTIONAL { ?a af:continent ?continent }
    OPTIONAL { ?a af:municipality ?municipality }
    OPTIONAL { ?a af:icaoCode ?icao_code }
    OPTIONAL { ?a af:iataCode ?iata_code }
    OPTIONAL { ?a af:gpsCode ?gps_code }
    OPTIONAL { ?a af:localCode ?local_code }
    OPTIONAL { ?a af:homeLink ?home_link }
    OPTIONAL { ?a af:keywords ?keywords }
    OPTIONAL { ?a af:wikipediaLink ?wikipedia_link }
    OPTIONAL { ?a af:dataSource ?data_source }
    OPTIONAL { ?a af:island ?island }
SPARQL;
    }

    private function allAirportsQuery(): string
    {
        $patterns = $this->airportPatterns();

        return <<<SPARQL
PREFIX af: <https://aerofind.example/id/>
SELECT * WHERE {
{$patterns}
}
ORDER BY ?airport_id
SPARQL;
    }

    /**
     * Bandara berjadwal terdekat. Jarak Haversine dihitung langsung di SPARQL
     * (fungsi math:), lalu diurutkan dan dibatasi oleh Fuseki.
     *
     * @return list<array<string, mixed>>
     */
    public function nearestAirports(float $lat, float $lng, int $limit = 5): array
    {
        if (Cache::has('fuseki.down')) {
            return [];
        }

        $latitude = sprintf('%.6F', $lat);
        $longitude = sprintf('%.6F', $lng);
        $limit = max(1, min(50, $limit));
        $patterns = $this->airportPatterns(scheduledOnly: true);

        $query = <<<SPARQL
PREFIX af: <https://aerofind.example/id/>
PREFIX math: <http://www.w3.org/2005/xpath-functions/math#>
SELECT * WHERE {
{$patterns}
    BIND(math:sin((?latitude_deg - {$latitude}) * math:pi() / 360) AS ?sin_lat)
    BIND(math:sin((?longitude_deg - {$longitude}) * math:pi() / 360) AS ?sin_lng)
    BIND(?sin_lat * ?sin_lat + math:cos({$latitude} * math:pi() / 180) * math:cos(?latitude_deg * math:pi() / 180) * ?sin_lng * ?sin_lng AS ?haversine)
    BIND(6371 * 2 * math:asin(math:sqrt(?haversine)) AS ?distance_km)
}
ORDER BY ?distance_km
LIMIT {$limit}
SPARQL;

        try {
            $bindings = $this->select($query)['results']['bindings'] ?? [];
        } catch (Throwable $e) {
            return [];
        }

        return array_map(function (array $row): array {
            $airport = self::mapBindingToAirport($row);
            $airport['distance_km'] = round((float) $row['distance_km']['value'], 2);

            return $airport;
        }, $bindings);
    }

    /**
     * Daftar kota/kabupaten (af:Region) dari Fuseki.
     *
     * @return list<array{region_id: string, region_name: string, country_code: string, latitude: float, longitude: float}>
     */
    public function regions(): array
    {
        if (Cache::has('fuseki.down')) {
            return [];
        }

        $cached = Cache::get('fuseki.regions');
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $query = <<<'SPARQL'
PREFIX af: <https://aerofind.example/id/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT ?region_id ?region_name ?country_code ?latitude ?longitude WHERE {
    ?r a af:Region ;
       af:regionId ?region_id ;
       rdfs:label ?region_name ;
       af:countryCode ?country_code ;
       af:latitude ?latitude ;
       af:longitude ?longitude .
}
ORDER BY ?region_name
SPARQL;

        try {
            $bindings = $this->select($query)['results']['bindings'] ?? [];
        } catch (Throwable $e) {
            Cache::put('fuseki.down', true, 60);

            return [];
        }

        $regions = array_map(fn (array $row): array => [
            'region_id' => $row['region_id']['value'],
            'region_name' => $row['region_name']['value'],
            'country_code' => $row['country_code']['value'],
            'latitude' => (float) $row['latitude']['value'],
            'longitude' => (float) $row['longitude']['value'],
        ], $bindings);

        if ($regions !== []) {
            Cache::put('fuseki.regions', $regions, now()->addHour());
        }

        return $regions;
    }

    /**
     * @return array{region_id: string, region_name: string, country_code: string, latitude: float, longitude: float}|null
     */
    public function region(string $regionId): ?array
    {
        foreach ($this->regions() as $region) {
            if ($region['region_id'] === $regionId) {
                return $region;
            }
        }

        return null;
    }

    /**
     * @param  array<string, array{value: string}>  $row
     * @return array<string, mixed>
     */
    public static function mapBindingToAirport(array $row): array
    {
        $value = fn (string $key): ?string => $row[$key]['value'] ?? null;
        $number = fn (string $key): int|float|null => isset($row[$key]) ? $row[$key]['value'] + 0 : null;

        $countryCode = strtoupper((string) $value('country_code'));

        return [
            'airport_id' => $value('airport_id'),
            'id' => $number('id'),
            'ident' => $value('ident'),
            'type' => $value('type'),
            'name' => $value('name'),
            'latitude_deg' => $number('latitude_deg'),
            'longitude_deg' => $number('longitude_deg'),
            'elevation_ft' => $number('elevation_ft'),
            'continent' => $value('continent'),
            'iso_country' => self::COUNTRY_NAMES[$countryCode] ?? $countryCode,
            'iso_region' => $value('iso_region'),
            'municipality' => $value('municipality'),
            'scheduled_service' => $value('scheduled_service'),
            'icao_code' => $value('icao_code'),
            'iata_code' => $value('iata_code'),
            'gps_code' => $value('gps_code'),
            'local_code' => $value('local_code'),
            'home_link' => $value('home_link'),
            'keywords' => $value('keywords'),
            'wikipedia_link' => $value('wikipedia_link'),
            'data_source' => $value('data_source'),
            'island' => $value('island'),
        ];
    }
}
