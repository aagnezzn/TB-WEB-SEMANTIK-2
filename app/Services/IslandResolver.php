<?php

namespace App\Services;

/**
 * Menentukan pulau tempat sebuah bandara berada. Kelas ini tidak bergantung
 * pada framework supaya bisa dipakai juga oleh skrip ekspor RDF.
 */
class IslandResolver
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /**
     * @param  array<string, mixed>  $airport  Kunci: iso_region, municipality, latitude_deg, longitude_deg
     */
    public static function resolve(array $airport): ?string
    {
        $config = self::config();
        $region = (string) ($airport['iso_region'] ?? '');
        $country = strtoupper(substr($region, 0, 2));

        $fromName = self::islandFromMunicipality((string) ($airport['municipality'] ?? ''), $config);
        if ($fromName !== null) {
            return $fromName;
        }

        $lat = $airport['latitude_deg'] ?? null;
        $lon = $airport['longitude_deg'] ?? null;

        if (is_numeric($lat) && is_numeric($lon)) {
            foreach ($config['bounds'] as [$island, $minLat, $maxLat, $minLon, $maxLon, $boundCountry]) {
                if (
                    $country === $boundCountry
                    && $lat >= $minLat && $lat <= $maxLat
                    && $lon >= $minLon && $lon <= $maxLon
                ) {
                    return $island;
                }
            }
        }

        if (isset($config['regions'][$region])) {
            return $config['regions'][$region];
        }

        return in_array($country, $config['mainland_countries'], true) ? $config['mainland_name'] : null;
    }

    /**
     * Daftar alias pencarian: alias huruf kecil => nama pulau baku.
     * Nama pulau yang muncul di data (mis. "Tablas") ikut ditambahkan.
     *
     * @param  list<string>  $dataIslands
     * @return array<string, string>
     */
    public static function aliasMap(array $dataIslands = []): array
    {
        $map = [];

        foreach (self::config()['aliases'] as $island => $aliases) {
            $map[mb_strtolower($island)] = $island;
            foreach ($aliases as $alias) {
                $map[mb_strtolower($alias)] = $island;
            }
        }

        foreach ($dataIslands as $island) {
            $map[mb_strtolower($island)] ??= $island;
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private static function islandFromMunicipality(string $municipality, array $config): ?string
    {
        if (! preg_match('/([\p{L}\'\.\- ]+?)\s+Islands?\b/u', $municipality, $match)) {
            return null;
        }

        $parts = preg_split('/\s+[-–]\s+|\s*\(/u', trim($match[1]));
        $name = trim((string) end($parts));

        // "Talang Gudang-Sumatra" -> "Sumatra" bila bagian akhir adalah pulau yang dikenal
        if (preg_match('/-([\p{L} ]+)$/u', $name, $suffix)) {
            $known = self::aliasMap();
            if (isset($known[mb_strtolower(trim($suffix[1]))])) {
                $name = trim($suffix[1]);
            }
        }

        if ($name === '') {
            return null;
        }

        return $config['municipality_names'][mb_strtolower($name)] ?? self::aliasMap()[mb_strtolower($name)] ?? $name;
    }

    /**
     * @return array<string, mixed>
     */
    private static function config(): array
    {
        return self::$config ??= require dirname(__DIR__, 2).'/config/islands.php';
    }
}
