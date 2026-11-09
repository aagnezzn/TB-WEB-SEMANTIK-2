<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Ambil data region berdasarkan ID
        $region = Region::where('region_id', $regionId)->first() ?? Region::find($regionId);

        if (!$region) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kota/Kabupaten tidak ditemukan.'
            ], 404);
        }

        // Mengambil koordinat dari kolom latitude & longitude yang ada di tabel regions
        $lat = $region->latitude;
        $lng = $region->longitude;

        // Hitung jarak terdekat menggunakan Haversine Formula langsung di database
        $airports = Airport::select('airports.*', DB::raw("(
            6371 * acos(
                cos(radians(?)) * cos(radians(latitude_deg)) * 
                cos(radians(longitude_deg) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude_deg))
            )
        ) AS distance_km"))
        ->setBindings([$lat, $lng, $lat])
        ->whereNotNull('latitude_deg')
        ->whereNotNull('longitude_deg')
        ->orderBy('distance_km', 'asc')
        ->limit(5) // Ambil 5 bandara terdekat
        ->get();

        return response()->json([
            'status' => 'success',
            'region' => $region->region_name ?? 'Wilayah Terpilih',
            'recommendations' => $airports
        ]);
    }
}