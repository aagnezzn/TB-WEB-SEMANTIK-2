<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Region;
use Illuminate\Support\Collection;

class AirportRecommendationService
{
    /**
     * Mengambil rekomendasi bandara berdasarkan koordinat pengguna.
     */
    public function nearestFromCoordinates(
        float $lat,
        float $lng,
        int $limit = 5
    ): Collection {

        $distanceFormula = '
            (
                6371 * 2 * ASIN(
                    SQRT(
                        POWER(
                            SIN(RADIANS(latitude_deg - ?) / 2),
                            2
                        )
                        +
                        COS(RADIANS(?))
                        * COS(RADIANS(latitude_deg))
                        * POWER(
                            SIN(RADIANS(longitude_deg - ?) / 2),
                            2
                        )
                    )
                )
            )
        ';

        return Airport::query()
            ->whereNotNull('latitude_deg')
            ->whereNotNull('longitude_deg')
            ->where('scheduled_service', 'yes')

            ->select('airports.*')

            ->selectRaw(
                "$distanceFormula AS distance_km",
                [$lat, $lat, $lng]
            )

            // Prioritas ukuran bandara
            ->orderBy('distance_km', 'asc')
            ->orderByRaw("
                CASE type
                    WHEN 'large_airport' THEN 1
                    WHEN 'medium_airport' THEN 2
                    WHEN 'small_airport' THEN 3
                    ELSE 4
                END
            ")
            ->limit($limit)
            ->get();
            }

    /**
     * Tetap mendukung pencarian berdasarkan wilayah.
     */
    public function nearest(
        Region $region,
        int $limit = 5
    ): Collection {

        return $this->nearestFromCoordinates(
            (float) $region->latitude,
            (float) $region->longitude,
            $limit
        );
    }
}
