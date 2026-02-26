<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryCheckpoint;
use App\Models\User;
use App\Models\Route;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Halaman utama laporan
     */
    public function index(Request $request)
    {
        $filters = $this->getFilters($request);

        return view('admin.report.index', [
            'filters'          => $filters,
            'summary'          => Delivery::reportSummary($filters),
            'deliveryTrend'    => Delivery::reportTrend($filters),
            'driverStats'      => Delivery::reportDriverStats($filters),
            'routeStats'       => Delivery::reportRouteStats($filters),
            'checkpointStats'  => DeliveryCheckpoint::reportCheckpointStats($filters),
            'recentDeliveries' => Delivery::reportPaginated($filters),
            'drivers'          => User::where('role', 'user')->where('status', 'active')->orderBy('name')->get(),
            'routes'           => Route::where('status', 'active')->orderBy('route_name')->get(),
        ]);
    }

    /**
     * Export laporan ke PDF (print browser)
     * Opsional: ganti dengan DomPDF untuk file .pdf asli
     */
    public function exportPdf(Request $request)
    {
        $filters = $this->getFilters($request);

        $html = view('admin.report.export.pdf', [
            'filters'      => $filters,
            'summary'      => Delivery::reportSummary($filters),
            'driverStats'  => Delivery::reportDriverStats($filters),
            'routeStats'   => Delivery::reportRouteStats($filters),
            'deliveries'   => Delivery::reportAllForExport($filters),
        ])->render();

        // Uncomment jika sudah install barryvdh/laravel-dompdf:
        // $pdf = \PDF::loadHTML($html)->setPaper('a4', 'landscape');
        // return $pdf->download("laporan-delivery-{$filters['start_date']}-{$filters['end_date']}.pdf");

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Export laporan ke CSV (bisa dibuka di Excel)
     */
    public function exportExcel(Request $request)
    {
        $filters    = $this->getFilters($request);
        $deliveries = Delivery::reportAllForExport($filters);
        $filename   = "laporan-delivery-{$filters['start_date']}-{$filters['end_date']}.csv";

        return response()->stream(function () use ($deliveries, $filters) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM UTF-8 agar Excel baca karakter Indonesia

            // Info laporan
            fputcsv($file, ['LAPORAN DELIVERY']);
            fputcsv($file, ['Periode', $filters['start_date'] . ' s/d ' . $filters['end_date']]);
            fputcsv($file, ['Dibuat', now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []);

            // Header kolom
            fputcsv($file, [
                'No', 'Kode Delivery', 'Tanggal Dibuat', 'Driver', 'Rute', 'Status',
                'Mulai', 'Selesai', 'Durasi (menit)',
                'Total Checkpoint', 'Checkpoint Selesai', 'Avg Load Time (menit)', 'Catatan',
            ]);

            foreach ($deliveries as $idx => $d) {
                $totalCp     = $d->checkpoints->count();
                $completedCp = $d->checkpoints->where('status', 'completed')->count();
                $avgLoad     = $completedCp > 0
                    ? round($d->checkpoints->where('status', 'completed')->avg('load_duration_minutes'), 1)
                    : '-';

                fputcsv($file, [
                    $idx + 1,
                    $d->delivery_code,
                    $d->created_at->format('d/m/Y'),
                    $d->driver->name       ?? '-',
                    $d->route->route_name  ?? '-',
                    ucfirst(str_replace('_', ' ', $d->status)),
                    $d->started_at   ? $d->started_at->format('d/m/Y H:i')   : '-',
                    $d->completed_at ? $d->completed_at->format('d/m/Y H:i') : '-',
                    $d->total_duration_minutes ?? '-',
                    $totalCp,
                    $completedCp,
                    $avgLoad,
                    $d->notes ?? '-',
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Endpoint AJAX untuk refresh data chart
     */
    public function chartData(Request $request)
    {
        $filters = $this->getFilters($request);

        return response()->json([
            'trend'   => Delivery::reportTrend($filters),
            'summary' => Delivery::reportSummary($filters),
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Mengambil dan memvalidasi filter dari request.
     * Jika start_date/end_date tidak ada, gunakan preset.
     */
    private function getFilters(Request $request): array
    {
        $preset    = $request->input('preset', 'this_month');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = $this->presetToRange($preset);
        }

        return [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'preset'     => $preset,
            'driver_id'  => $request->input('driver_id'),
            'route_id'   => $request->input('route_id'),
            'status'     => $request->input('status'),
        ];
    }

    /**
     * Mengkonversi preset string menjadi rentang tanggal [start, end].
     */
    private function presetToRange(string $preset): array
    {
        return match ($preset) {
            'today'        => [today()->format('Y-m-d'),                               today()->format('Y-m-d')],
            'yesterday'    => [today()->subDay()->format('Y-m-d'),                     today()->subDay()->format('Y-m-d')],
            'this_week'    => [now()->startOfWeek()->format('Y-m-d'),                  now()->endOfWeek()->format('Y-m-d')],
            'last_week'    => [now()->subWeek()->startOfWeek()->format('Y-m-d'),       now()->subWeek()->endOfWeek()->format('Y-m-d')],
            'this_month'   => [now()->startOfMonth()->format('Y-m-d'),                 now()->endOfMonth()->format('Y-m-d')],
            'last_month'   => [now()->subMonth()->startOfMonth()->format('Y-m-d'),     now()->subMonth()->endOfMonth()->format('Y-m-d')],
            'last_7_days'  => [today()->subDays(6)->format('Y-m-d'),                   today()->format('Y-m-d')],
            'last_30_days' => [today()->subDays(29)->format('Y-m-d'),                  today()->format('Y-m-d')],
            default        => [now()->startOfMonth()->format('Y-m-d'),                 now()->endOfMonth()->format('Y-m-d')],
        };
    }
}