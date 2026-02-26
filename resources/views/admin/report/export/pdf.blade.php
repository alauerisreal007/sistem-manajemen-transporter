<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Delivery - {{ $filters['start_date'] }} s/d {{ $filters['end_date'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

        .page { padding: 30px 35px; }

        /* HEADER */
        .report-header { border-bottom: 3px solid #1d4ed8; padding-bottom: 14px; margin-bottom: 20px; }
        .report-header h1 { font-size: 20px; font-weight: bold; color: #1d4ed8; }
        .report-header .meta { margin-top: 4px; color: #555; font-size: 10px; }
        .report-header .meta span { margin-right: 16px; }

        /* SUMMARY CARDS */
        .cards { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .card { flex: 1; min-width: 100px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; }
        .card .label { font-size: 9px; text-transform: uppercase; color: #6b7280; font-weight: 600; margin-bottom: 4px; }
        .card .value { font-size: 22px; font-weight: bold; }
        .card.green  .value { color: #16a34a; }
        .card.blue   .value { color: #2563eb; }
        .card.yellow .value { color: #ca8a04; }
        .card.red    .value { color: #dc2626; }
        .card.purple .value { color: #7c3aed; }
        .card.gray   .value { color: #374151; }
        .card .sub   { font-size: 9px; color: #9ca3af; margin-top: 2px; }

        /* SECTION */
        .section-title { font-size: 12px; font-weight: bold; color: #374151; margin-bottom: 8px; margin-top: 18px; border-left: 3px solid #1d4ed8; padding-left: 8px; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 10px; }
        thead th { background: #eff6ff; color: #1e40af; text-align: left; padding: 6px 8px; font-weight: 600; border-bottom: 2px solid #bfdbfe; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tbody tr:nth-child(even) td { background: #f9fafb; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        /* BADGES */
        .badge { padding: 2px 7px; border-radius: 999px; font-size: 9px; font-weight: 600; }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-yellow { background: #fef9c3; color: #a16207; }
        .badge-red    { background: #fee2e2; color: #dc2626; }

        /* FOOTER */
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }

        /* PRINT */
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }

        /* Progress bar */
        .progress-bar { width: 100%; background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden; }
        .progress-fill { height: 6px; border-radius: 4px; background: #3b82f6; }
        .progress-fill.done { background: #22c55e; }
    </style>
</head>
<body>
    <div class="page">

        {{-- PRINT BUTTON (hidden on print) --}}
        <div class="no-print" style="text-align:right; margin-bottom: 16px;">
            <button onclick="window.print()"
                style="background:#1d4ed8;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600;">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>

        {{-- HEADER --}}
        <div class="report-header">
            <h1>📊 Laporan Delivery</h1>
            <div class="meta">
                <span>Periode: <strong>{{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</strong></span>
                <span>Dibuat: <strong>{{ now()->format('d/m/Y H:i') }}</strong></span>
                @if(!empty($filters['driver_id']))
                    <span>Driver: <strong>Filter Aktif</strong></span>
                @endif
            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="section-title">Ringkasan Eksekutif</div>
        <div class="cards">
            <div class="card gray">
                <div class="label">Total</div>
                <div class="value">{{ $summary['total'] }}</div>
                <div class="sub">Semua delivery</div>
            </div>
            <div class="card green">
                <div class="label">Selesai</div>
                <div class="value">{{ $summary['completed'] }}</div>
                <div class="sub">{{ $summary['completion_rate'] }}% dari total</div>
            </div>
            <div class="card blue">
                <div class="label">Dalam Perjalanan</div>
                <div class="value">{{ $summary['in_progress'] }}</div>
                <div class="sub">Aktif</div>
            </div>
            <div class="card yellow">
                <div class="label">Menunggu</div>
                <div class="value">{{ $summary['pending'] }}</div>
                <div class="sub">Belum mulai</div>
            </div>
            <div class="card red">
                <div class="label">Dibatalkan</div>
                <div class="value">{{ $summary['cancelled'] }}</div>
                <div class="sub">Tidak terselesaikan</div>
            </div>
            <div class="card purple">
                <div class="label">Avg Durasi</div>
                <div class="value">{{ $summary['avg_duration_h'] }}j{{ $summary['avg_duration_m'] }}m</div>
                <div class="sub">Per delivery selesai</div>
            </div>
        </div>

        {{-- DRIVER STATS --}}
        <div class="section-title">Performa Driver</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Driver</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Selesai</th>
                    <th class="text-center">Dibatalkan</th>
                    <th class="text-center">Completion Rate</th>
                    <th class="text-center">Avg Durasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($driverStats as $idx => $stat)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $stat->driver->name ?? '-' }}</strong></td>
                        <td class="text-center">{{ $stat->total }}</td>
                        <td class="text-center" style="color:#16a34a;font-weight:600;">{{ $stat->completed }}</td>
                        <td class="text-center" style="color:#dc2626;">{{ $stat->cancelled }}</td>
                        <td class="text-center">
                            <span class="badge {{ $stat->completion_rate >= 90 ? 'badge-green' : ($stat->completion_rate >= 70 ? 'badge-yellow' : 'badge-red') }}">
                                {{ $stat->completion_rate }}%
                            </span>
                        </td>
                        <td class="text-center">{{ $stat->avg_duration_fmt }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center" style="color:#9ca3af;padding:12px;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- ROUTE STATS --}}
        <div class="section-title">Performa Rute</div>
        <table>
            <thead>
                <tr>
                    <th>Nama Rute</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Selesai</th>
                    <th class="text-center">Avg Durasi</th>
                    <th class="text-center">Terbaik</th>
                    <th class="text-center">Terburuk</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routeStats as $stat)
                    <tr>
                        <td><strong>{{ $stat->route->route_name ?? '-' }}</strong></td>
                        <td class="text-center">{{ $stat->total }}</td>
                        <td class="text-center" style="color:#16a34a;font-weight:600;">{{ $stat->completed }}</td>
                        <td class="text-center" style="color:#7c3aed;font-weight:600;">{{ $stat->avg_duration_fmt }}</td>
                        <td class="text-center" style="color:#16a34a;">{{ $stat->best_duration_fmt }}</td>
                        <td class="text-center" style="color:#dc2626;">{{ $stat->worst_duration_fmt }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center" style="color:#9ca3af;padding:12px;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- DETAIL TABLE --}}
        <div class="section-title">Detail Seluruh Delivery</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Tanggal</th>
                    <th>Driver</th>
                    <th>Rute</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Mulai</th>
                    <th class="text-center">Selesai</th>
                    <th class="text-center">Durasi</th>
                    <th class="text-center">Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $idx => $delivery)
                    @php
                        $total     = $delivery->checkpoints->count();
                        $done      = $delivery->checkpoints->where('status', 'completed')->count();
                        $pct       = $total > 0 ? round($done / $total * 100) : 0;
                        $statusMap = [
                            'completed'   => ['label' => 'Selesai',           'class' => 'badge-green'],
                            'in_progress' => ['label' => 'Dalam Perjalanan',  'class' => 'badge-blue'],
                            'pending'     => ['label' => 'Menunggu',          'class' => 'badge-yellow'],
                            'cancelled'   => ['label' => 'Dibatalkan',        'class' => 'badge-red'],
                        ];
                        $st = $statusMap[$delivery->status] ?? ['label' => $delivery->status, 'class' => ''];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $delivery->delivery_code }}</strong></td>
                        <td>{{ $delivery->created_at->format('d/m/Y') }}</td>
                        <td>{{ $delivery->driver->name ?? '-' }}</td>
                        <td>{{ $delivery->route->route_name ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td class="text-center">{{ $delivery->started_at ? $delivery->started_at->format('H:i') : '-' }}</td>
                        <td class="text-center">{{ $delivery->completed_at ? $delivery->completed_at->format('H:i') : '-' }}</td>
                        <td class="text-center">{{ $delivery->formatted_duration }}</td>
                        <td class="text-center" style="min-width:70px;">
                            <div class="progress-bar">
                                <div class="progress-fill {{ $pct === 100 ? 'done' : '' }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <div style="font-size:8px;color:#6b7280;margin-top:2px;">{{ $done }}/{{ $total }}</div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center" style="color:#9ca3af;padding:12px;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- FOOTER --}}
        <div class="footer">
            <span>Sistem Manajemen Transporter</span>
            <span>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog if opened via export button
        if (window.location.search.includes('print=1')) {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>