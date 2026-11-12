<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/dataset_bandara_tubes.json');

        if (!file_exists($file)) {
            throw new RuntimeException(
                "Dataset tidak ditemukan: {$file}"
            );
        }

        $json = file_get_contents($file);

        // Hilangkan BOM jika tersedia.
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'JSON tidak valid: ' . json_last_error_msg()
            );
        }

        // Mendukung JSON berbentuk array langsung
        // maupun object dengan key airports/data.
        if (isset($data['airports']) && is_array($data['airports'])) {
            $airports = $data['airports'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $airports = $data['data'];
        } else {
            $airports = $data;
        }

        if (!is_array($airports) || empty($airports)) {
            throw new RuntimeException(
                'Data bandara tidak ditemukan dalam JSON.'
            );
        }

        if (DB::table('airports')->exists()) {
            throw new RuntimeException(
                'Tabel airports sudah berisi data. Import dibatalkan.'
            );
        }

        $allowedTypes = [
            'large_airport',
            'medium_airport',
            'small_airport'
        ];

        $batch = [];
        $total = 0;

        foreach ($airports as $airport) {
            if (!is_array($airport)) {
                continue;
            }

            $type = $airport['type'] ?? null;
            $scheduled = strtolower(
                trim((string) ($airport['scheduled_service'] ?? ''))
            );

            // Filter sesuai dataset AeroFind.
            if (!in_array($type, $allowedTypes, true)) {
                continue;
            }

            if ($scheduled !== 'yes') {
                continue;
            }

            $name = trim($airport['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $batch[] = [
                'name' => $name,
                'iata_code' => $airport['iata_code'] ?? null,
                'icao_code' => $airport['icao_code']
                    ?? $airport['ident']
                    ?? null,
                'type' => $type,
                'municipality' => $airport['municipality'] ?? null,
                'iso_country' => $airport['iso_country'] ?? null,
                'latitude_deg' => is_numeric($airport['latitude_deg'] ?? null)
                    ? $airport['latitude_deg'] : null,
                'longitude_deg' => is_numeric($airport['longitude_deg'] ?? null)
                    ? $airport['longitude_deg'] : null,
                'scheduled_service' => $scheduled,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('airports')->insert($batch);
                $total += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('airports')->insert($batch);
            $total += count($batch);
        }

        echo "\nBerhasil mengimpor {$total} data bandara.\n";
    }
}
