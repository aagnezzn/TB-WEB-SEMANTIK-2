<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function nearest(Request $request)
    {
        $regionId = $request->query('region_id');

        if (!$regionId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter region_id wajib diisi.'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Endpoint rekomendasi berhasil diakses.',
            'region_id' => $regionId
        ]);
    }
}
