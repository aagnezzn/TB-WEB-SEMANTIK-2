<?php

namespace Tests\Unit;

use App\Services\ExternalSparqlService;
use App\Services\FusekiService;
use App\Services\LiveAirportService;
use PHPUnit\Framework\TestCase;

class LiveAirportServiceTest extends TestCase
{
    public function test_normalize_metar_converts_visibility_and_finds_ceiling(): void
    {
        $metar = LiveAirportService::normalizeMetar([
            'rawOb' => 'METAR WIII 221000Z 04008KT 8000 FEW020 BKN030 31/25 Q1010',
            'fltCat' => 'MVFR',
            'temp' => 31,
            'wdir' => 40,
            'wspd' => 8,
            'visib' => '6+',
            'altim' => 1010,
            'obsTime' => 1795341600,
            'clouds' => [['cover' => 'FEW', 'base' => 2000], ['cover' => 'BKN', 'base' => 3000]],
        ]);

        $this->assertSame('MVFR', $metar['flight_category']);
        $this->assertTrue($metar['visibility_is_min']);
        $this->assertEqualsWithDelta(9.7, $metar['visibility_km'], 0.1);
        $this->assertSame(3000, $metar['ceiling_ft']);
        $this->assertSame(1010, $metar['pressure_hpa']);
    }

    public function test_normalize_states_classifies_flight_phases(): void
    {
        // [icao24, callsign, country, ., ., lon, lat, baro_alt, on_ground, velocity, track, vertical_rate, ., geo_alt]
        $states = [
            ['a1', 'GIA001 ', 'Indonesia', 0, 0, 106.66, -6.13, 300, false, 70, 90, -4.0, null, 320],
            ['a2', 'LNI002 ', 'Indonesia', 0, 0, 106.67, -6.12, 500, false, 80, 270, 6.0, null, 520],
            ['a3', 'CTV003 ', 'Indonesia', 0, 0, 106.65, -6.12, 0, true, 0, 0, 0, null, 0],
            ['a4', 'SIA004 ', 'Singapore', 0, 0, 106.70, -6.10, 10000, false, 230, 45, 0, null, 10100],
            ['a5', 'FAR005 ', 'Indonesia', 0, 0, 120.0, -6.1, 5000, false, 200, 0, 0, null, 5000],
            ['a6', 'NOGPS ', 'Indonesia', 0, 0, null, null, 0, false, 0, 0, 0, null, 0],
        ];

        $traffic = LiveAirportService::normalizeStates($states, -6.125, 106.659, 30, 40);

        $this->assertSame(4, $traffic['counts']['total']);
        $this->assertSame(1, $traffic['counts']['arriving']);
        $this->assertSame(1, $traffic['counts']['departing']);
        $this->assertSame(1, $traffic['counts']['ground']);
        $this->assertSame(1, $traffic['counts']['overflight']);
        $this->assertSame('GIA001', collect($traffic['aircraft'])->firstWhere('phase', 'arriving')['callsign']);
    }

    public function test_distance_between_known_points(): void
    {
        // 0,0522 derajat lintang dan 0,2153 derajat bujur di dekat khatulistiwa sekitar 24,6 km
        $this->assertEqualsWithDelta(24.6, LiveAirportService::distanceKm(3.59, 98.67, 3.6422, 98.8853), 0.3);
    }

    public function test_fuseki_binding_maps_to_airport_array(): void
    {
        $airport = FusekiService::mapBindingToAirport([
            'airport_id' => ['value' => 'APT-0065'],
            'name' => ['value' => 'Kualanamu International Airport'],
            'latitude_deg' => ['value' => '3.6422'],
            'longitude_deg' => ['value' => '98.8853'],
            'elevation_ft' => ['value' => '12'],
            'country_code' => ['value' => 'ID'],
            'island' => ['value' => 'Sumatra'],
        ]);

        $this->assertSame('Indonesia', $airport['iso_country']);
        $this->assertSame(3.6422, $airport['latitude_deg']);
        $this->assertSame(12, $airport['elevation_ft']);
        $this->assertSame('Sumatra', $airport['island']);
        $this->assertNull($airport['iata_code']);
    }

    public function test_dbpedia_binding_is_normalized(): void
    {
        $this->assertNull(ExternalSparqlService::normalizeBinding(null));

        $result = ExternalSparqlService::normalizeBinding([
            'dbpedia' => ['value' => 'http://dbpedia.org/resource/Changi_Airport'],
            'operator' => ['value' => 'Changi Airport Group'],
        ]);

        $this->assertSame('Changi Airport Group', $result['operator']);
        $this->assertNull($result['abstract']);
    }
}
