<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Services\FusekiService;

class NavigationController extends Controller
{
    public function index(FusekiService $fuseki)
    {
        $airportData = $this->airportsFromFuseki($fuseki);

        // Cadangan bila Fuseki tidak aktif: tabel airports di database
        if ($airportData->isEmpty()) {
            $airportData = Airport::query()
                ->whereNotNull('latitude_deg')
                ->whereNotNull('longitude_deg')
                ->orderBy('name')
                ->get([
                    'airport_id',
                    'name',
                    'iata_code',
                    'icao_code',
                    'municipality',
                    'iso_country',
                    'latitude_deg',
                    'longitude_deg',
                ])
                ->map(fn (Airport $airport): array => [
                    'id' => $airport->airport_id,
                    'name' => $airport->name,
                    'iata' => $airport->iata_code,
                    'icao' => $airport->icao_code,
                    'municipality' => $airport->municipality,
                    'country' => $airport->iso_country,
                    'lat' => $airport->latitude_deg,
                    'lng' => $airport->longitude_deg,
                ])
                ->values();
        }

        return view('navigation.index', compact('airportData'));
    }

    /**
     * Daftar bandara dari dataset RDF di Fuseki (hasil kueri SPARQL).
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function airportsFromFuseki(FusekiService $fuseki)
    {
        return collect($fuseki->airports())
            ->filter(fn (array $airport): bool => is_numeric($airport['latitude_deg'] ?? null)
                && is_numeric($airport['longitude_deg'] ?? null))
            ->sortBy(fn (array $airport): string => mb_strtolower((string) $airport['name']))
            ->map(fn (array $airport): array => [
                'id' => $airport['airport_id'],
                'name' => $airport['name'],
                'iata' => $airport['iata_code'],
                'icao' => $airport['icao_code'],
                'municipality' => $airport['municipality'],
                'country' => $airport['iso_country'],
                'lat' => $airport['latitude_deg'],
                'lng' => $airport['longitude_deg'],
            ])
            ->values();
    }
}
