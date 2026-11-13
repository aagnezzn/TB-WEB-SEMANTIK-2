<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AirportController extends Controller
{
    private function getAirportsData()
    {
        // Daftar kemungkinan lokasi file dataset (disesuaikan ke database/data/)
        $possiblePaths = [
            database_path('data/dataset_bandara_tubes.json'),
            database_path('data/dataset_bandara_tubes'),
            database_path('data/dataset_bandara_tubes.txt'),
            storage_path('app/dataset_bandara_tubes.json'),
            public_path('dataset_bandara_tubes.json'),
        ];

        $json = null;

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $json = file_get_contents($path);
                break;
            }
        }

        if (!$json) {
            return [];
        }

        // Hapus BOM UTF-8 jika ada
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        // Menggunakan path baru di database/data/
        $jsonPath = database_path('data/dataset_bandara_tubes.json');

        $airports = [];

        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);

            // Hapus BOM UTF-8 jika ada
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                $airports = $decoded;
            }
        }

        // Ambil data bandara yang memiliki koordinat valid
        $mapAirports = array_values(array_filter($airports, function ($airport) {
            return isset($airport['latitude_deg'], $airport['longitude_deg'])
                && is_numeric($airport['latitude_deg'])
                && is_numeric($airport['longitude_deg'])
                && $airport['latitude_deg'] >= -90
                && $airport['latitude_deg'] <= 90
                && $airport['longitude_deg'] >= -180
                && $airport['longitude_deg'] <= 180;
        }));

        // Fitur pencarian bandara
        if ($search) {
            $searchLower = strtolower(trim($search));

            // Mapping nama negara ke kode ISO
            $isoMapping = [
                'indonesia' => 'id',
                'malaysia' => 'my',
                'singapura' => 'sg',
                'singapore' => 'sg',
                'thailand' => 'th',
                'filipina' => 'ph',
                'philippines' => 'ph',
                'vietnam' => 'vn',
                'kamboja' => 'kh',
                'cambodia' => 'kh',
                'laos' => 'la',
                'myanmar' => 'mm',
                'brunei' => 'bn',
                'brunei darussalam' => 'bn',
                'timor leste' => 'tl',
            ];

            $targetIso = $isoMapping[$searchLower] ?? $searchLower;

            $airports = array_filter($airports, function ($airport) use ($searchLower, $targetIso) {
                $name = strtolower($airport['name'] ?? '');
                $municipality = strtolower($airport['municipality'] ?? '');
                $isoCountry = strtolower($airport['iso_country'] ?? '');
                $ident = strtolower($airport['ident'] ?? '');
                $iata = strtolower($airport['iata_code'] ?? '');

                return str_contains($name, $searchLower)
                    || str_contains($municipality, $searchLower)
                    || str_contains($ident, $searchLower)
                    || str_contains($iata, $searchLower)
                    || $isoCountry === $searchLower
                    || $isoCountry === $targetIso;
            });
        }

        return view('welcome', compact(
            'airports',
            'search',
            'mapAirports'
        ));
    }

    public function show($id)
    {
        $allAirports = $this->getAirportsData();

        $airport = null;

        foreach ($allAirports as $item) {
            $itemId = $item['airport_id']
                ?? $item['id']
                ?? $item['ident']
                ?? $item['iata_code']
                ?? null;

            if ($itemId == $id || (string) $itemId === (string) $id) {
                $airport = $item;
                break;
            }
        }

        if (!$airport) {
            abort(404, 'Bandara tidak ditemukan');
        }

        return view('airports.show', compact('airport'));
    }

    public function locationCategory()
    {
        // Daftar negara ASEAN
        $aseanCountries = [
            ['code' => 'ID', 'name' => 'Indonesia', 'flag' => '🇮🇩'],
            ['code' => 'MY', 'name' => 'Malaysia', 'flag' => '🇲🇾'],
            ['code' => 'SG', 'name' => 'Singapura', 'flag' => '🇸🇬'],
            ['code' => 'TH', 'name' => 'Thailand', 'flag' => '🇹🇭'],
            ['code' => 'PH', 'name' => 'Filipina', 'flag' => '🇵🇭'],
            ['code' => 'VN', 'name' => 'Vietnam', 'flag' => '🇻🇳'],
            ['code' => 'KH', 'name' => 'Kamboja', 'flag' => '🇰🇭'],
            ['code' => 'LA', 'name' => 'Laos', 'flag' => '🇱🇦'],
            ['code' => 'MM', 'name' => 'Myanmar', 'flag' => '🇲🇲'],
            ['code' => 'BN', 'name' => 'Brunei Darussalam', 'flag' => '🇧🇳'],
            ['code' => 'TL', 'name' => 'Timor Leste', 'flag' => '🇹🇱'],
        ];

        return view('airports.categories-location', compact('aseanCountries'));
    }

    public function phoneCategory()
    {
        $jsonPath = database_path('data/dataset_bandara_tubes.json');

        $airports = [];

        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                $airports = array_filter($decoded, function ($airport) {
                    return !empty($airport['phone'])
                        || !empty($airport['home_link'])
                        || !empty($airport['wikipedia_link']);
                });
            }
        }

        return view('airports.categories-phone', compact('airports'));
    }

    public function operationalCategory()
    {
        $jsonPath = database_path('data/dataset_bandara_tubes.json');

        $airports = [];

        if (file_exists($jsonPath)) {
            $content = file_get_contents($jsonPath);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                $airports = array_filter($decoded, function ($airport) {
                    return !empty($airport['scheduled_service'])
                        || !empty($airport['type']);
                });
            }
        }

        return view('airports.categories-operational', compact('airports'));
    }
}