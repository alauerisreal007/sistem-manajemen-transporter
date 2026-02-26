<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryCheckpoint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index(Request $request)
    {
        $date       = $request->filled('date') ? Carbon::parse($request->date) : today();
        $isToday    = $date->isToday();

        // Metrics berdasarkan tanggal yang dipilih
        $activeDeliveries           = Delivery::countActiveDeliveries();
        $completedDeliveriesToday   = Delivery::completedCount($date);
        $targetDaily                = 30;
        
        $loadStats          = DeliveryCheckpoint::averageLoadTimeByDate($date);
        $avgPickupTime      = round($loadStats->avg_pickup ?? 0, 1);
        $avgDeliveryTime    = round($loadStats->avg_delivery ?? 0, 1);
        $avgLoadTime        = round($loadStats->avg_overall ?? 0, 1);

        // Trend berdasarkan 7 hari sebelum tanggal dipilih
        $lastWeekAvg    = DeliveryCheckpoint::averageLoadByDate($date->copy()->subDays(7));
        $loadTimeTrend  = $avgLoadTime - round($lastWeekAvg ?? 0, 1);

        $durationStats      = Delivery::durationStatsByDate($date);
        $avgTotalDuration   = round($durationStats->avg_duration ?? 0, 1);
        $bestDuration       = round($durationStats->best_duration ?? 0, 1);
        $worstDuration      = round($durationStats->worst_duration ?? 0, 1);

        $onTimeRate = Delivery::onTimeRateByDate($date);

        $totalDrivers = User::totalDrivers();
        $activeDrivers = Delivery::activeDriversCount();
        $availableDrivers = $totalDrivers - $activeDrivers;

        $trendData = $this->getPerformanceTrends($date);
        $loadingByLocation = DeliveryCheckpoint::loadingByLocation();
        $durationByRoute = Delivery::durationByRouteLast7Days();

        // Delivery aktif/pending
        $activeDeliveriesList = Delivery::activeDeliveriesList();
        $pendingDeliveriesList = Delivery::pendingDeliveriesList();

        $topDrivers = User::topDriversByDate($date);

        if ($request->ajax()) {
            return response()->json([
                'date_label'            => $isToday ? 'Hari Ini' : $date->locale('id')->translatedFormat('d F Y'),
                'is_today'              =>$isToday,
                'completed_deliveries'  => $completedDeliveriesToday,
                'target_percentage'     => $targetDaily > 0 ? min(round($completedDeliveriesToday / $targetDaily) * 100, 100) : 0,
                'avg_pickup_time'       => $avgPickupTime,
                'avg_delivery_time'     => $avgDeliveryTime,
                'avg_load_time'         => $avgLoadTime,
                'load_time_trend'       => $loadTimeTrend,
                'avg_total_duration'    => $avgTotalDuration,
                'avg_duration_h'        => floor($avgTotalDuration / 60),
                'avg_duration_m'        => (int) ($avgTotalDuration % 60),
                'best_duration_h'       => floor($bestDuration / 60),
                'best_duration_m'       => (int) ($bestDuration % 60),
                'worst_duration_h'      => floor($worstDuration / 60),
                'worst_duration_m'      => (int) ($worstDuration % 60),
                'on_time_rate'          => $onTimeRate,
                'trend_data'            => $trendData
            ]);
        }

        return view('admin.dashboard', compact(
            'date', 'isToday', 'activeDeliveries', 
            'completedDeliveriesToday', 
            'targetDaily', 
            'avgPickupTime', 
            'avgDeliveryTime', 
            'avgLoadTime',
            'loadTimeTrend',
            'avgTotalDuration',
            'bestDuration',
            'worstDuration',
            'onTimeRate',
            'totalDrivers',
            'activeDrivers',
            'availableDrivers',
            'loadingByLocation',
            'durationByRoute',
            'activeDeliveriesList',
            'pendingDeliveriesList',
            'topDrivers',
            'trendData'
        ));
    }

    public function refresh()
    {
        return response()->json([
            'active_deliveries' => Delivery::countActiveDeliveries(),
            'completed_today' => Delivery::countCompletedToday(),
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    private function getPerformanceTrends(Carbon $endDate)
    {
        $trends = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $endDate->copy()->subDays($i);

            $trends[] = [
                'date'              => $date->format('D'),
                'avg_load'          => round(DeliveryCheckpoint::averageLoadByDate($date) ?? 0, 1),
                'avg_duration'      => round(Delivery::totalAverageDuration($date) ?? 0, 1),
                'count_completed'   => Delivery::completedCount($date)
            ];
        }

        return $trends;
    }
}