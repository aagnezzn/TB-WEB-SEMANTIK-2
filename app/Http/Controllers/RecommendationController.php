<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Services\AirportRecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    protected AirportRecommendationService $recommendation;

    public function __construct(
        AirportRecommendationService $recommendation
    ) {
        $this->recommendation = $recommendation;
    }

    public function nearest(Request $request)
    {
        $request->validate([
            'region_id' => ['required', 'string', 'max:50'],
        ]);

        $region = Region::where(
            'region_id',
            $request->query('region_id')
        )->first();

        if (!$region) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kota/Kabupaten tidak ditemukan.'
            ], 404);
        }

        $airports = $this->recommendation->nearest(
            $region,
            5
        );

        return response()->json([
            'status' => 'success',
            'region' => $region->region_name,
            'recommendations' => $airports
        ]);
    }
}
