<?php

namespace Tests\Unit;

use App\Services\IslandResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IslandResolverTest extends TestCase
{
    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function airportProvider(): array
    {
        return [
            'bali dari kode wilayah' => [['iso_region' => 'ID-BA', 'municipality' => 'Kuta, Badung', 'latitude_deg' => -8.7, 'longitude_deg' => 115.2], 'Bali'],
            'rote dari nama pulau' => [['iso_region' => 'ID-NT', 'municipality' => "Ba'a - Rote Island", 'latitude_deg' => -10.8, 'longitude_deg' => 123.1], 'Rote'],
            'borneo dari akhiran nama' => [['iso_region' => 'ID-KB', 'municipality' => 'Nanga Pinoh-Borneo Island', 'latitude_deg' => -0.3, 'longitude_deg' => 111.7], 'Borneo'],
            'lombok dari koordinat' => [['iso_region' => 'ID-NB', 'municipality' => 'Mataram', 'latitude_deg' => -8.8, 'longitude_deg' => 116.3], 'Lombok'],
            'sabah termasuk borneo' => [['iso_region' => 'MY-12', 'municipality' => 'Kota Kinabalu', 'latitude_deg' => 5.9, 'longitude_deg' => 116.0], 'Borneo'],
            'luzon dari wilayah filipina' => [['iso_region' => 'PH-ILN', 'municipality' => 'Laoag City', 'latitude_deg' => 18.2, 'longitude_deg' => 120.5], 'Luzon'],
            'phuket dari koordinat' => [['iso_region' => 'TH-83', 'municipality' => 'Phuket', 'latitude_deg' => 8.1, 'longitude_deg' => 98.3], 'Phuket'],
            'thailand daratan' => [['iso_region' => 'TH-10', 'municipality' => 'Bangkok', 'latitude_deg' => 13.9, 'longitude_deg' => 100.6], 'Daratan Asia Tenggara'],
            'sumatra dari kode wilayah' => [['iso_region' => 'ID-SU', 'municipality' => 'Beringin', 'latitude_deg' => 3.6, 'longitude_deg' => 98.9], 'Sumatra'],
        ];
    }

    /**
     * @param  array<string, mixed>  $airport
     */
    #[DataProvider('airportProvider')]
    public function test_resolves_island(array $airport, string $expected): void
    {
        $this->assertSame($expected, IslandResolver::resolve($airport));
    }

    public function test_unknown_region_has_no_island(): void
    {
        $this->assertNull(IslandResolver::resolve(['iso_region' => 'ZZ-01', 'municipality' => 'Nowhere']));
    }

    public function test_alias_map_contains_common_names(): void
    {
        $aliases = IslandResolver::aliasMap(['Tablas']);

        $this->assertSame('Borneo', $aliases['kalimantan']);
        $this->assertSame('Sumatra', $aliases['sumatera']);
        $this->assertSame('Tablas', $aliases['tablas']);
    }
}
