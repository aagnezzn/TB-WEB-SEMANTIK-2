<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AirportController extends Controller
{
    private function getAirportsData()
    {
        // Daftar kemungkinan nama file di storage/app/
        $possiblePaths = [
            storage_path('app/dataset_bandara_tubes'),
            storage_path('app/dataset_bandara_tubes.json'),
            storage_path('app/dataset_bandara_tubes.txt'),
            storage_path('app/airports.json'),
            public_path('dataset_bandara_tubes'),
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

    $jsonPath = storage_path('app/dataset_bandara_tubes.json');

    $airports = [];
    if (file_exists($jsonPath)) {
        $content = file_get_contents($jsonPath);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $airports = $decoded;
        }
    }

    if ($search) {
        $searchLower = strtolower(trim($search));

        // Mapping nama negara/wilayah ke kode ISO yang ada di dalam dataset
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

        // Ambil kode ISO jika pencarian cocok dengan nama negara di atas
        $targetIso = $isoMapping[$searchLower] ?? $searchLower;

        $airports = array_filter($airports, function ($airport) use ($searchLower, $targetIso) {
            $name = strtolower($airport['name'] ?? '');
            $municipality = strtolower($airport['municipality'] ?? '');
            $isoCountry = strtolower($airport['iso_country'] ?? '');
            $ident = strtolower($airport['ident'] ?? '');
            $iata = strtolower($airport['iata_code'] ?? '');

            return str_contains($name, $searchLower) ||
                   str_contains($municipality, $searchLower) ||
                   str_contains($ident, $searchLower) ||
                   str_contains($iata, $searchLower) ||
                   $isoCountry === $searchLower ||
                   $isoCountry === $targetIso;
        });
    }

    return view('welcome', compact('airports', 'search'));
}
    

    public function show($id)
    {
        $allAirports = $this->getAirportsData();
        
        $airport = null;
        foreach ($allAirports as $item) {
            // Cek berbagai kemungkinan key ID di dalam dataset
            $itemId = $item['airport_id'] ?? $item['id'] ?? $item['ident'] ?? $item['iata_code'] ?? null;
            
            if ($itemId == $id || (string)$itemId === (string)$id) {
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
    // Daftar negara ASEAN yang didukung dalam dataset
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
    $jsonPath = storage_path('app/dataset_bandara_tubes.json');
    $airports = [];

    if (file_exists($jsonPath)) {
        $content = file_get_contents($jsonPath);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            // Menyaring bandara yang memiliki data kontak, website, atau wikipedia
            $airports = array_filter($decoded, function ($airport) {
                return !empty($airport['phone']) || !empty($airport['home_link']) || !empty($airport['wikipedia_link']);
            });
        }
    }

    return view('airports.categories-phone', compact('airports'));
}

public function operationalCategory()
{
    $jsonPath = storage_path('app/dataset_bandara_tubes.json');
    $airports = [];

    if (file_exists($jsonPath)) {
        $content = file_get_contents($jsonPath);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            // Menyaring bandara berdasarkan status operasional (misalnya scheduled_service bernilai yes/tersedia)
            $airports = array_filter($decoded, function ($airport) {
                return !empty($airport['scheduled_service']) || !empty($airport['type']);
            });
        }
    }

    return view('airports.categories-operational', compact('airports'));
}

}