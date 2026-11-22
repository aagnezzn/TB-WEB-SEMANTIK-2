<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Data operasi bandara real-time dari layanan publik tanpa API key:
 * - Cuaca bandara (METAR) dari NOAA Aviation Weather Center.
 * - Pesawat di sekitar bandara dari OpenSky Network.
 */
class LiveAirportService
{
    private const METAR_URL = 'https://aviationweather.gov/api/data/metar';

    private const OPENSKY_URL = 'https://opensky-network.org/api/states/all';

    /**
     * Mencari bandara berdasarkan kode ICAO dari Fuseki, lalu dataset JSON sebagai cadangan.
     *
     * @return array<string, mixed>|null
     */
    public function locate(string $icao): ?array
    {
        foreach (app(FusekiService::class)->airports() ?: $this->airportsFromJson() as $airport) {
            $code = $airport['icao_code'] ?? $airport['ident'] ?? '';

            if (strcasecmp((string) $code, $icao) === 0
                && is_numeric($airport['latitude_deg'] ?? null)
                && is_numeric($airport['longitude_deg'] ?? null)) {
                return $airport;
            }
        }

        return null;
    }

    /**
     * Cuaca terkini bandara dari laporan METAR terbaru.
     *
     * @return array<string, mixed>|null
     */
    public function weather(string $icao): ?array
    {
        return $this->cached('live.metar.'.strtoupper($icao), 300, function () use ($icao): ?array {
            $response = Http::timeout(8)->acceptJson()->get(self::METAR_URL, [
                'ids' => strtoupper($icao),
                'format' => 'json',
            ]);

            $report = $response->successful() ? ($response->json()[0] ?? null) : null;

            return $report ? self::normalizeMetar($report) : null;
        });
    }

    /**
     * Pesawat yang sedang terdeteksi di sekitar bandara.
     *
     * @return array{aircraft: list<array<string, mixed>>, counts: array<string, int>}|null
     */
    public function traffic(float $lat, float $lng, float $elevationFt = 0.0, float $radiusKm = 40.0): ?array
    {
        $key = sprintf('live.traffic.%.2f.%.2f', $lat, $lng);

        return $this->cached($key, 30, function () use ($lat, $lng, $elevationFt, $radiusKm): ?array {
            $latDelta = $radiusKm / 111.0;
            $lngDelta = $radiusKm / (111.0 * max(0.2, cos(deg2rad($lat))));

            $response = Http::timeout(10)->acceptJson()->get(self::OPENSKY_URL, [
                'lamin' => $lat - $latDelta,
                'lamax' => $lat + $latDelta,
                'lomin' => $lng - $lngDelta,
                'lomax' => $lng + $lngDelta,
            ]);

            if (! $response->successful()) {
                return null;
            }

            return self::normalizeStates($response->json('states') ?? [], $lat, $lng, $elevationFt, $radiusKm);
        });
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    public static function normalizeMetar(array $report): array
    {
        $visibility = $report['visib'] ?? null;
        $visibilityKm = match (true) {
            is_numeric($visibility) => round($visibility * 1.609, 1),
            is_string($visibility) && str_ends_with($visibility, '+') => (float) rtrim($visibility, '+') * 1.609,
            default => null,
        };

        $ceilingFt = null;
        foreach ($report['clouds'] ?? [] as $layer) {
            if (in_array($layer['cover'] ?? '', ['BKN', 'OVC', 'OVX', 'VV'], true) && isset($layer['base'])) {
                $ceilingFt = $ceilingFt === null ? $layer['base'] : min($ceilingFt, $layer['base']);
            }
        }

        return [
            'raw' => $report['rawOb'] ?? null,
            'flight_category' => $report['fltCat'] ?? null,
            'temperature_c' => $report['temp'] ?? null,
            'dewpoint_c' => $report['dewp'] ?? null,
            'wind_direction' => $report['wdir'] ?? null,
            'wind_speed_kt' => $report['wspd'] ?? null,
            'wind_gust_kt' => $report['wgst'] ?? null,
            'visibility_km' => $visibilityKm !== null ? round($visibilityKm, 1) : null,
            'visibility_is_min' => is_string($visibility) && str_ends_with($visibility, '+'),
            'pressure_hpa' => $report['altim'] ?? null,
            'clouds' => array_map(fn (array $layer): array => [
                'cover' => $layer['cover'] ?? null,
                'base_ft' => $layer['base'] ?? null,
            ], $report['clouds'] ?? []),
            'ceiling_ft' => $ceilingFt,
            'observed_at' => isset($report['obsTime']) ? gmdate('c', (int) $report['obsTime']) : null,
        ];
    }

    /**
     * Mengubah data state vector OpenSky menjadi daftar pesawat dengan fase penerbangan.
     *
     * @param  list<list<mixed>>  $states
     * @return array{aircraft: list<array<string, mixed>>, counts: array<string, int>}
     */
    public static function normalizeStates(array $states, float $lat, float $lng, float $elevationFt, float $radiusKm): array
    {
        $groundLevelM = $elevationFt * 0.3048;
        $aircraft = [];

        foreach ($states as $state) {
            if (! is_numeric($state[5] ?? null) || ! is_numeric($state[6] ?? null)) {
                continue;
            }

            $distanceKm = self::distanceKm($lat, $lng, (float) $state[6], (float) $state[5]);

            if ($distanceKm > $radiusKm) {
                continue;
            }

            $altitudeM = $state[13] ?? $state[7] ?? null;
            $verticalRate = (float) ($state[11] ?? 0);
            $aboveGround = $altitudeM !== null ? (float) $altitudeM - $groundLevelM : null;

            $phase = match (true) {
                (bool) ($state[8] ?? false) => 'ground',
                $aboveGround !== null && $aboveGround < 3000 && $verticalRate <= -1.5 => 'arriving',
                $aboveGround !== null && $aboveGround < 3000 && $verticalRate >= 1.5 => 'departing',
                default => 'overflight',
            };

            $aircraft[] = [
                'callsign' => trim((string) ($state[1] ?? '')) ?: strtoupper((string) $state[0]),
                'country' => $state[2] ?? null,
                'lat' => (float) $state[6],
                'lng' => (float) $state[5],
                'altitude_m' => $altitudeM !== null ? (int) round((float) $altitudeM) : null,
                'speed_kmh' => isset($state[9]) ? (int) round($state[9] * 3.6) : null,
                'heading' => isset($state[10]) ? (int) round($state[10]) : null,
                'vertical_rate' => round($verticalRate, 1),
                'phase' => $phase,
                'distance_km' => round($distanceKm, 1),
            ];
        }

        usort($aircraft, fn (array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);

        $counts = ['total' => count($aircraft), 'ground' => 0, 'arriving' => 0, 'departing' => 0, 'overflight' => 0];
        foreach ($aircraft as $plane) {
            $counts[$plane['phase']]++;
        }

        return ['aircraft' => array_slice($aircraft, 0, 40), 'counts' => $counts];
    }

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($a)));
    }

    /**
     * Cache dengan hasil gagal disimpan singkat agar layanan eksternal tidak dibanjiri.
     *
     * @param  callable(): mixed  $resolver
     */
    private function cached(string $key, int $ttlSeconds, callable $resolver): mixed
    {
        $hit = Cache::get($key);

        if (is_array($hit)) {
            return $hit['failed'] ?? false ? null : $hit['value'];
        }

        try {
            $value = $resolver();
        } catch (Throwable $e) {
            report($e);
            $value = null;
        }

        Cache::put(
            $key,
            ['failed' => $value === null, 'value' => $value],
            $value === null ? 60 : $ttlSeconds
        );

        return $value;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function airportsFromJson(): array
    {
        $path = database_path('data/dataset_bandara_tubes.json');

        if (! file_exists($path)) {
            return [];
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($path));
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }
}
