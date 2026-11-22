<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Services\AirportRecommendationService;
use App\Services\FusekiService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    protected AirportRecommendationService $recommendation;

    public function __construct(
        AirportRecommendationService $recommendation
    ) {
        $this->recommendation = $recommendation;
    }

    public function nearest(Request $request, FusekiService $fuseki)
    {
        $request->validate([
            'region_id' => ['required', 'string', 'max:50'],
        ]);

        $regionId = (string) $request->query('region_id');

        // Sumber utama: af:Region di Fuseki. Cadangan: tabel regions.
        $region = $fuseki->region($regionId);

        if ($region) {
            $regionName = $region['region_name'];
            $airports = $this->recommendation->nearestFromCoordinates(
                $region['latitude'],
                $region['longitude'],
                5
            );
        } else {
            try {
                $model = Region::where('region_id', $regionId)->first();
            } catch (\Throwable $e) {
                $model = null;
            }

            if (!$model) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kota/Kabupaten tidak ditemukan.'
                ], 404);
            }

            $regionName = $model->region_name;
            $airports = $this->recommendation->nearest($model, 5);
        }

        return response()->json([
            'status' => 'success',
            'region' => $regionName,
            'recommendations' => $airports
        ]);
    }
}
