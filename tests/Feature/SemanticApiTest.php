<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Menguji API yang bergantung pada Fuseki dan layanan eksternal tanpa jaringan nyata.
 */
class SemanticApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function fakeServices(bool $fusekiDown = true): void
    {
        Http::fake([
            'aviationweather.gov/*' => Http::response([[
                'rawOb' => 'METAR WIII 221000Z 04008KT 8000 FEW020 31/25 Q1010',
                'fltCat' => 'VFR',
                'temp' => 31,
                'wdir' => 40,
                'wspd' => 8,
                'visib' => 5,
                'altim' => 1010,
                'obsTime' => 1795341600,
                'clouds' => [['cover' => 'FEW', 'base' => 2000]],
            ]]),
            'opensky-network.org/*' => Http::response(['states' => [
                ['a1', 'GIA001 ', 'Indonesia', 0, 0, 106.66, -6.13, 300, false, 70, 90, -4.0, null, 320],
            ]]),
            '*' => $fusekiDown ? Http::response('down', 500) : Http::response([], 200),
        ]);
    }

    public function test_suggest_returns_matches_per_letter_with_json_fallback(): void
    {
        $this->fakeServices();

        $this->getJson('/api/airports/suggest?q=kno')
            ->assertOk()
            ->assertJsonPath('results.0.name', 'Kualanamu International Airport')
            ->assertJsonPath('results.0.iata', 'KNO');
    }

    public function test_suggest_empty_query_returns_no_results(): void
    {
        $this->fakeServices();

        $this->getJson('/api/airports/suggest?q=')
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    public function test_suggest_offers_typo_correction(): void
    {
        $this->fakeServices();

        $this->getJson('/api/airports/suggest?q=kualanmu')
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('suggestion', 'Kualanamu');
    }

    public function test_live_endpoint_returns_weather_and_traffic(): void
    {
        $this->fakeServices();

        $this->getJson('/api/airports/WIII/live')
            ->assertOk()
            ->assertJsonPath('airport.icao', 'WIII')
            ->assertJsonPath('weather.flight_category', 'VFR')
            ->assertJsonPath('traffic.counts.total', 1)
            ->assertJsonPath('traffic.counts.arriving', 1);
    }

    public function test_live_endpoint_rejects_unknown_or_invalid_codes(): void
    {
        $this->fakeServices();

        $this->getJson('/api/airports/ZZZZ/live')->assertNotFound();
        $this->getJson('/api/airports/not-valid!/live')->assertNotFound();
    }

    public function test_nearest_airports_come_from_fuseki_sparql(): void
    {
        $binding = fn (string $name, string $distance): array => [
            'airport_id' => ['value' => 'APT-0065'],
            'ident' => ['value' => 'WIMM'],
            'type' => ['value' => 'large_airport'],
            'name' => ['value' => $name],
            'latitude_deg' => ['value' => '3.6422'],
            'longitude_deg' => ['value' => '98.8853'],
            'country_code' => ['value' => 'ID'],
            'iso_region' => ['value' => 'ID-SU'],
            'distance_km' => ['value' => $distance],
        ];

        Http::fake([
            '*' => Http::response(['results' => ['bindings' => [
                $binding('Kualanamu International Airport', '22.88'),
                $binding('Alas Leuser Airport', '92.17'),
            ]]]),
        ]);

        $this->getJson('/api/recommendations/nearest?latitude=3.59&longitude=98.67')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('recommendations.0.name', 'Kualanamu International Airport')
            ->assertJsonPath('recommendations.0.distance_km', 22.88);

        Http::assertSent(fn ($request) => str_contains((string) $request['query'], 'math:asin'));
    }

    public function test_region_recommendation_uses_fuseki_regions(): void
    {
        $region = [
            'region_id' => ['value' => 'R1'],
            'region_name' => ['value' => 'Medan'],
            'country_code' => ['value' => 'ID'],
            'latitude' => ['value' => '3.59'],
            'longitude' => ['value' => '98.67'],
        ];

        $airport = [
            'airport_id' => ['value' => 'APT-0065'],
            'name' => ['value' => 'Kualanamu International Airport'],
            'latitude_deg' => ['value' => '3.6422'],
            'longitude_deg' => ['value' => '98.8853'],
            'country_code' => ['value' => 'ID'],
            'distance_km' => ['value' => '22.88'],
        ];

        Http::fake(function ($request) use ($region, $airport) {
            $isRegionQuery = str_contains((string) $request['query'], 'af:Region');

            return Http::response(['results' => ['bindings' => [$isRegionQuery ? $region : $airport]]]);
        });

        $this->getJson('/api/recommendations/nearest?region_id=R1')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('region', 'Medan')
            ->assertJsonPath('recommendations.0.name', 'Kualanamu International Airport');
    }
}
