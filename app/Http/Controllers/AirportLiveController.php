<?php

namespace App\Http\Controllers;

use App\Services\LiveAirportService;
use Illuminate\Http\JsonResponse;

class AirportLiveController extends Controller
{
    /**
     * Data operasi real-time bandara: cuaca (METAR) dan pesawat di sekitarnya.
     */
    public function show(string $icao, LiveAirportService $live): JsonResponse
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{3,8}$/', $icao) === 1, 404);

        $airport = $live->locate($icao);

        abort_if($airport === null, 404, 'Airport not found');

        $lat = (float) $airport['latitude_deg'];
        $lng = (float) $airport['longitude_deg'];

        return response()->json([
            'airport' => [
                'name' => $airport['name'] ?? null,
                'icao' => strtoupper($icao),
                'lat' => $lat,
                'lng' => $lng,
            ],
            'weather' => $live->weather($icao),
            'traffic' => $live->traffic($lat, $lng, (float) ($airport['elevation_ft'] ?? 0)),
            'updated_at' => now()->toIso8601String(),
        ]);
    }
}
