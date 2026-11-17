<?php

namespace App\Http\Controllers;

use App\Models\Airport;

class NavigationController extends Controller
{
    public function index()
    {
        $airports = Airport::query()
            ->whereNotNull('latitude_deg')
            ->whereNotNull('longitude_deg')
            ->select([
                'airport_id',
                'name',
                'iata_code',
                'icao_code',
                'municipality',
                'iso_country',
                'latitude_deg',
                'longitude_deg',
            ])
            ->orderBy('name')
            ->get();

        $airportData = $airports->map(function ($airport) {
            return [
                'id' => $airport->airport_id,
                'name' => $airport->name,
                'iata' => $airport->iata_code,
                'icao' => $airport->icao_code,
                'municipality' => $airport->municipality,
                'country' => $airport->iso_country,
                'lat' => $airport->latitude_deg,
                'lng' => $airport->longitude_deg,
            ];
        })->values();

        return view('navigation.index', compact(
            'airports',
            'airportData'
        ));
    }
}