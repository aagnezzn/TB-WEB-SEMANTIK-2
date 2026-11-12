<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AirportRecommendationService
{
    /**
     * Mengambil bandara terdekat dari koordinat suatu wilayah.
     */
    public function nearest(Region $region, int $limit = 5): Collection
    {
        $lat = (float) $region->latitude;
        $lng = (float) $region->longitude;

        $query = Airport::query()
            ->select('airports.*')
            ->selectRaw(
                "(
                    6371 * ACOS(
                        LEAST(1, GREATEST(-1,
                            COS(RADIANS(?))
                            * COS(RADIANS(latitude_deg))
                            * COS(RADIANS(longitude_deg) - RADIANS(?))
                            + SIN(RADIANS(?))
                            * SIN(RADIANS(latitude_deg))
                        ))
                    )
                ) AS distance_km",
                [$lat, $lng, $lat]
            )
            ->whereNotNull('latitude_deg')
            ->whereNotNull('longitude_deg');

        // Batasi pencarian ke negara yang sama dengan wilayah.
        if (!empty($region->country_code)) {
            $query->where(
                'iso_country',
                $region->country_code
            );
        }

        return $query
            ->orderBy('distance_km', 'asc')
            ->limit($limit)
            ->get();
    }
}
