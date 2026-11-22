<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Region;
use App\Services\FusekiService;

class AirportMapController extends Controller
{
    public function index(FusekiService $fuseki)
    {
        $fromFuseki = collect($fuseki->airports())
            ->filter(fn (array $airport): bool => is_numeric($airport['latitude_deg'] ?? null)
                && is_numeric($airport['longitude_deg'] ?? null))
            ->map(fn (array $airport): array => collect($airport)->only([
                'airport_id', 'name', 'latitude_deg', 'longitude_deg', 'iata_code',
                'icao_code', 'type', 'municipality', 'iso_country',
            ])->all())
            ->values();

        if ($fromFuseki->isNotEmpty()) {
            return response()->json($fromFuseki);
        }

        $airports = Airport::query()
            ->whereNotNull('latitude_deg')
            ->whereNotNull('longitude_deg')
            ->get([
                'airport_id',
                'name',
                'latitude_deg',
                'longitude_deg',
                'iata_code',
                'icao_code',
                'type',
                'municipality',
                'iso_country',
            ]);

        return response()->json($airports);
    }

    public function map()
    {
        return view('airports.map');
    }

    public function regions(FusekiService $fuseki)
    {
        $fromFuseki = collect($fuseki->regions())
            ->map(fn (array $region): array => [
                'region_id' => $region['region_id'],
                'region_name' => $region['region_name'],
                'country_code' => $region['country_code'],
            ])
            ->values();

        if ($fromFuseki->isNotEmpty()) {
            return response()->json($fromFuseki);
        }

        // Cadangan bila Fuseki tidak aktif
        return response()->json(
            Region::orderBy('region_name')
                ->get([
                    'region_id',
                    'region_name',
                    'country_code',
                ])
        );
    }
}
