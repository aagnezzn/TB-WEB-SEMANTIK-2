<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AirportRecommendationController extends Controller
{
    public function nearest(Request $request)
    {
        $validated = $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
        ]);

        $region = Region::findOrFail($validated['region_id']);

        $lat = (float) $region->latitude;
        $lng = (float) $region->longitude;

        $distanceFormula = '
            6371 * 2 * ASIN(
                SQRT(
                    POWER(SIN(RADIANS(latitude_deg - ?) / 2), 2)
                    +
                    COS(RADIANS(?))
                    * COS(RADIANS(latitude_deg))
                    * POWER(SIN(RADIANS(longitude_deg - ?) / 2), 2)
                )
            )
        ';

        $airports = Airport::query()
            ->whereNotNull('latitude_deg')
            ->whereNotNull('longitude_deg')
            ->where('scheduled_service', 'yes')
            ->select([
                'airport_id',
                'name',
                'iata_code',
                'icao_code',
                'municipality',
                'latitude_deg',
                'longitude_deg',
            ])
            ->selectRaw(
                "($distanceFormula) AS distance_km",
                [$lat, $lat, $lng]
            )
            ->orderBy('distance_km')
            ->limit(5)
            ->get();

        return response()->json([
            'region' => $region->region_name,
            'country' => $region->country_code,
            'recommendations' => $airports,
        ]);
    }
}
