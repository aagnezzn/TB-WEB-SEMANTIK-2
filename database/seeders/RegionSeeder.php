<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/regions_southeast_asia.csv');

        if (!file_exists($path)) {
            $this->command->error("File CSV tidak ditemukan: $path");
            return;
        }

        // Pemetaan ISO alpha-3 ke ISO alpha-2
        $countryCodes = [
            'IDN' => 'ID',
            'MYS' => 'MY',
            'SGP' => 'SG',
            'THA' => 'TH',
            'VNM' => 'VN',
            'PHL' => 'PH',
            'BRN' => 'BN',
            'KHM' => 'KH',
            'LAO' => 'LA',
            'MMR' => 'MM',
            'TLS' => 'TL',
        ];

        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->command->error('File CSV gagal dibuka.');
            return;
        }

        $header = fgetcsv($handle, 0, ',', '"', '');

        if (!$header) {
            fclose($handle);
            $this->command->error('Header CSV tidak ditemukan.');
            return;
        }

        // Menghapus BOM
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

        $rows = [];
        $total = 0;
        $skipped = 0;

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {

            if (count($line) !== count($header)) {
                $skipped++;
                continue;
            }

            $r = array_combine($header, $line);

            $alpha3 = strtoupper(trim($r['country_code'] ?? ''));

            // Konversi kode negara
            if (!isset($countryCodes[$alpha3])) {
                $skipped++;
                continue;
            }

            if (
                empty($r['region_name']) ||
                !is_numeric($r['latitude'] ?? null) ||
                !is_numeric($r['longitude'] ?? null)
            ) {
                $skipped++;
                continue;
            }

            $rows[] = [
                'region_name'  => trim($r['region_name']),
                'region_type'  => $r['region_type'] ?? null,
                'country_code' => $countryCodes[$alpha3],
                'latitude'     => (float) $r['latitude'],
                'longitude'    => (float) $r['longitude'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            if (count($rows) >= 500) {
                DB::table('regions')->insert($rows);

                $total += count($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('regions')->insert($rows);
            $total += count($rows);
        }

        fclose($handle);

        $this->command->info("Berhasil memasukkan {$total} wilayah.");
        $this->command->info("Baris dilewati: {$skipped}.");
    }
}
