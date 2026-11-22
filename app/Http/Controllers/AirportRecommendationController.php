<?php

namespace App\Http\Controllers;

use App\Services\AirportRecommendationService;
use App\Services\FusekiService;
use Illuminate\Http\Request;

class AirportRecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(
        AirportRecommendationService $recommendationService
    ) {
        $this->recommendationService = $recommendationService;
    }

    public function nearest(Request $request)
    {
        // Peta bandara mengirim region_id (kota/kabupaten), bukan koordinat
        if ($request->filled('region_id')) {
            return app(RecommendationController::class)->nearest($request, app(FusekiService::class));
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $validated['latitude'];
        $lng = (float) $validated['longitude'];

        $airports = $this->recommendationService
            ->nearestFromCoordinates($lat, $lng, 5);

        return response()->json([
            'success' => true,

            'user_location' => [
                'latitude' => $lat,
                'longitude' => $lng,
            ],

            'total' => $airports->count(),

            'recommendations' => $airports,
        ]);
    }
}
