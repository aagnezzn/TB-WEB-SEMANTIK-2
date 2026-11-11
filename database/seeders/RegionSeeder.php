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

        $handle = fopen($path, 'r');

        $header = fgetcsv($handle, 0, ',', '"', '');
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); // hapus BOM

        $rows = [];
        $total = 0;

        while (($line = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (count($line) !== count($header)) {
                continue; // lewati baris rusak/kosong
            }

            $r = array_combine($header, $line);

            $rows[] = [
                'region_id'    => $r['region_id'],
                'region_name'  => $r['region_name'],
                'region_type'  => $r['region_type'],
                'country_code' => $r['country_code'],
                'country_name' => $r['country_name'],
                'latitude'     => (float) $r['latitude'],
                'longitude'    => (float) $r['longitude'],
                'source'       => $r['source'],
                'source_year'  => (int) $r['source_year'],
                'license'      => $r['license'],
            ];

            if (count($rows) === 500) {
                DB::table('regions')->insert($rows);
                $total += count($rows);
                $rows = [];
            }
        }

        if ($rows) {
            DB::table('regions')->insert($rows);
            $total += count($rows);
        }

        fclose($handle);

        $this->command->info("Selesai: $total wilayah dimasukkan.");
    }
}