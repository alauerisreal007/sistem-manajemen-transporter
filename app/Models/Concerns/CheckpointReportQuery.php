<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait CheckpointReportQuery
{
    // =======================================
    // BASE QUERY
    // =======================================
    public static function reportCheckpointStats(array $filters)
    {
        $stats = static::completed()
                ->whereBetween(DB::raw('DATE(departed_at)'), [
                    $filters['start_date'],
                    $filters['end_date']
                ])
                ->selectRaw('
                    COUNT(*) as total,
                    AVG(load_durationMinutes) as avg_load,
                    AVG(CASE WHEN type = "pickup" THEN load_duration_minutes END) as avg_pickup,
                    AVG(CASE WHEn type = "delivery" THEN load_duration_minutes END) as avg_delivery,
                    MIN(load_duration_minutes) as min_load,
                    MAX(load_duration_minutes) as max_load
                ')
                ->first();

            return (object) [
                'total'         => $stats->total ?? 0,
                'avg_load'      => round($stats->avg_load       ?? 0, 1),
                'avg_pickup'    => round($stats->avg_pickup     ?? 0, 1),
                'avg_delivery'  => round($stats->avg_delivery   ?? 0, 1),
                'min_load'      => round($stats->min_load       ?? 0, 1),
                'max_load'      => round($stats->max_load       ?? 0, 1),
            ];
    }

    // ===============================
    // SUMMARY
    // ===============================
    public static function reportAvgLoadTime(array $filters): float
    {
        return round(
            static::completed()
                ->whereBetween(DB::raw('DATE(departed_at)'), [
                    $filters['start_date'],
                    $filters['end_date'],
                ])
                ->avg('load_duration_minutes') ?? 0,
            1
        );
    }
}