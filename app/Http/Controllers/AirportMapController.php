<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Region;

class AirportMapController extends Controller
{
    public function index()
    {
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

   public function regions()
    {
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
