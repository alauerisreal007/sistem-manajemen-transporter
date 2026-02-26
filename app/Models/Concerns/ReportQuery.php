<?php

namespace App\Models\Concerns;

use App\Models\DeliveryCheckpoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait ReportQuery
{
    // =======================================
    // BASE QUERY
    // =======================================
    public static function reportBaseQuery(array $filters)
    {
        $query = static::with(['driver', 'route', 'checkpoints'])
                ->whereBetween(DB::raw('DATE(created_at)'), [
                    $filters['start_date'],
                    $filters['end_date']
                ]);

        if (!empty($filters['driver_id'])) {
            $query->where('driver_id', $filters['driver_id']);
        }

        if (!empty($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    // ===============================
    // SUMMARY
    // ===============================
    public static function reportSummary(array $filters): array
    {
        $all = static::reportBaseQuery($filters)->get();

        $total      = $all->count();
        $completed  = $all->where('status', 'completed');
        $completedCount = $completed->count();

        $avgDuration = $completedCount > 0
            ? round($completed->avg('total_duration_minutes'), 1)
            : 0;

        $avgLoadTime = DeliveryCheckpoint::completed()
            ->whereBetween(DB::raw('DATE(departed_at)'), [
                $filters['start_date'],
                $filters['end_date'],
            ])
            ->avg('load_duration_minutes');

        return [
            'total'           => $total,
            'completed'       => $completedCount,
            'cancelled'       => $all->where('status', 'cancelled')->count(),
            'pending'         => $all->where('status', 'pending')->count(),
            'in_progress'     => $all->where('status', 'in_progress')->count(),
            'completion_rate' => $total > 0 ? round(($completedCount / $total) * 100, 1) : 0,
            'avg_duration'    => $avgDuration,
            'avg_duration_h'  => floor($avgDuration / 60),
            'avg_duration_m'  => (int) ($avgDuration % 60),
            'on_time_rate'    => $completedCount > 0 ? 100.0 : 0, // Sesuaikan jika ada logika deadline
            'avg_load_time'   => round($avgLoadTime ?? 0, 1),
        ];
    }

    // =============================
    // TREND
    // =============================
    public static function reportTrend(array $filters)
    {
        $diff = Carbon::parse($filters['start_date'])
                ->diffInDays(Carbon::parse($filters['end_date']));

        return $diff > 60 ? static::reportTrendByWeek($filters) : static::reportTrendByDay($filters);
    }

    public static function reportTrendByDay(array $filters)
    {
        $results = static::selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
                ->whereBetween(DB::raw('DATE(created_at'), [$filters['start_date'], $filters['end_date']])
                ->when(!empty($filters['driver_id']), fn($q) => $q->where('driver_id', $filters['driver_id']))
                ->when(!empty($filters['route_id']), fn($q) => $q->where('route_id', $filters['route_id']))
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get()
                ->groupBy('date');

        $trends = [];
        $cursor = Carbon::parse($filters['start_date']);
        $end    = Carbon::parse($filters['end_date']);

        while ($cursor->lte($end)) {
            $key    = $cursor->format('Y-m-d');
            $group  = $results->get($key, collect());

            $trends[]   = [
                'label'     => $cursor->format('d M'),
                'total'     => $group->sum('count'),
                'completed' => $group->where('status', 'completed')->sum('count'),
                'cancelled' => $group->where('status', 'cancelled')->sum('count'),
                'pending'   => $group->where('status', 'pending')->sum('count')
            ];

            $cursor->addDay();
        }

        return $trends;
    }

    public static function reportTrendByWeek(array $filters)
    {
        $results = static::selectRaw('
                YEARWEEK(created_at, 1) as week_key,
                MIN(DATE(created_at)) as week_start,
                status,
                COUNT(*) as count
            ')
            ->whereBetween(DB::raw('DATE(created_at)'), [$filters['start_date'], $filters['end_date']])
            ->when(!empty($filters['driver_id']), fn($q) => $q->where('driver_id', $filters['driver_id']))
            ->when(!empty($filters['route_id']), fn($q) => $q->where('route_id', $filters['route_id']))
            ->groupBy('week_key', 'status')
            ->orderBy('week_key')
            ->get()
            ->groupBy('week_key');

        $trends = [];
        foreach ($results as $group) {
            $weekStart = Carbon::parse($group->first()->week_start);
            $trends[] = [
                'label'     => 'W' . $weekStart->weekOfYear . ' ' . $weekStart->format('M'),
                'total'     => $group->sum('count'),
                'completed' => $group->where('status', 'completed')->sum('count'),
                'cancelled' => $group->where('status', 'cancelled')->sum('count'),
                'pending'   => $group->where('status', 'pending')->sum('count')
            ];
        }

        return $trends;
    }

    // =========================
    // DRIVER STATS
    // =========================
    public static function reportDriverStats(array $filters)
    {
        return static::with('driver')
                ->selectRaw('
                    driver_id
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                    AVG(CASE WHEN status = "completed" THEN total_duration_minutes END) as av_duration    
                ')
                ->whereBetween(DB::raw('DATE(created_at)'), [$filters['start_date'], $filters['end_date']])
                ->when(!empty($filters['driver_id']), fn($q) => $q->where('driver_id', $filters['driver_id']))
                ->when(!empty($filters['route_id']), fn($q) => $q->where('route_id', $filters['route_id']))
                ->groupBy('driver_id')
                ->orderByDesc('completed')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $item->completion_rate  = $item->total > 0 ? round(($item->completed / $item->total) * 100, 1) : 0;
                    $item->avg_duration_fmt = $item->avg_duration ? floor($item->avg_duration / 60) . 'j ' . ((int) $item->avg_duration % 60) . 'm' : '-';
                    return $item;
                });
    }

    public static function reportRouteStats(array $filters)
    {
        $fmt = fn($min) => $min ? floor($min / 60) . 'j ' > ((int) $min % 60) . 'm' : '-';
        
        return static::with('route')
                ->selectRaw('
                    route_id
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                    AVG(CASE WHEN status = "completed" THEN total_duration_minutes END) as avg_duration,
                    MIN(CASE WHEN status = "completed" THEN total_duration_minutes END) as best_duration,
                    MAX(CASE WHEN status = "completed" THEN total_duration_minutes END) as worst_duration,    
                ')
                ->whereBetween(DB::raw('DATE(created_at)'), [$filters['start_date'], $filters['end_date']])
                ->when(!empty($filters['driver_id']), fn($q) => $q->where('driver_id', $filters['driver_id']))
                ->when(!empty($filters['route_id']), fn($q) => $q->where('route_id', $filters['route_id']))
                ->groupBy('route_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) use ($fmt) {
                    $item->avg_duration_fmt  = $fmt($item->avg_duration);
                    $item->best_duration_fmt = $fmt($item->best_duration);
                    $item->worst_duration_fmt = $fmt($item->worst_duration);
                    return $item;
                });
    }

    // ====================================
    // FOR EXPORT
    // ====================================
    /**
     * get seluruh delivery tanpa paginasi
     * @param array $filters
     */
    public static function reportAllForExport(array $filters)
    {
        return static::reportBaseQuery($filters)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get dleivery dengan paginasi
     */
    public static function reportPaginated(array $filters, int $perPage = 15)
    {
        return static::reportBaseQuery($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}