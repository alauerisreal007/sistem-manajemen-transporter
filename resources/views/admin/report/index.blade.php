<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">📊 Laporan Delivery</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Periode: <strong>{{ \Carbon\Carbon::parse($filters['start_date'])->locale('id')->translatedFormat('d F Y') }}</strong>
                    s/d <strong>{{ \Carbon\Carbon::parse($filters['end_date'])->locale('id')->translatedFormat('d F Y') }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reportExportExcel', request()->query()) }}"
                    class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    Export Excel
                </a>
                <a href="{{ route('admin.reportExportPdf', request()->query()) }}" target="_blank"
                    class="flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/>
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 md:px-8 space-y-6">

        {{-- FILTER PANEL --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="GET" action="{{ route('admin.report') }}" id="filterForm">
                <div class="flex flex-col lg:flex-row gap-4 items-end">

                    {{-- PRESET SHORTCUTS --}}
                    <div class="flex-shrink-0">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Periode Cepat</label>
                        <div class="flex flex-wrap gap-1">
                            @foreach([
                                'today'        => 'Hari Ini',
                                'yesterday'    => 'Kemarin',
                                'last_7_days'  => '7 Hari',
                                'this_week'    => 'Minggu Ini',
                                'last_week'    => 'Minggu Lalu',
                                'this_month'   => 'Bulan Ini',
                                'last_month'   => 'Bulan Lalu',
                                'last_30_days' => '30 Hari',
                            ] as $key => $label)
                                <button type="button" onclick="setPreset('{{ $key }}')"
                                    class="preset-btn px-3 py-1.5 rounded-lg text-xs font-medium border transition
                                    {{ $filters['preset'] === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400 hover:text-blue-600' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="preset" id="presetInput" value="{{ $filters['preset'] }}">
                    </div>

                    {{-- CUSTOM DATE RANGE --}}
                    <div class="flex items-end gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Dari</label>
                            <input type="date" name="start_date" id="startDate"
                                value="{{ $filters['start_date'] }}" max="{{ today()->format('Y-m-d') }}"
                                onchange="clearPreset()"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Sampai</label>
                            <input type="date" name="end_date" id="endDate"
                                value="{{ $filters['end_date'] }}" max="{{ today()->format('Y-m-d') }}"
                                onchange="clearPreset()"
                                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- DRIVER FILTER --}}
                    <div class="min-w-[160px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Driver</label>
                        <select name="driver_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Driver</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ $filters['driver_id'] == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ROUTE FILTER --}}
                    <div class="min-w-[160px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Rute</label>
                        <select name="route_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Rute</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ $filters['route_id'] == $route->id ? 'selected' : '' }}>
                                    {{ $route->route_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- STATUS FILTER --}}
                    <div class="min-w-[140px]">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Status</label>
                        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="completed"  {{ $filters['status'] === 'completed'  ? 'selected' : '' }}>Selesai</option>
                            <option value="in_progress" {{ $filters['status'] === 'in_progress' ? 'selected' : '' }}>Dalam Perjalanan</option>
                            <option value="pending"    {{ $filters['status'] === 'pending'    ? 'selected' : '' }}>Menunggu</option>
                            <option value="cancelled"  {{ $filters['status'] === 'cancelled'  ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                            Terapkan
                        </button>
                        <a href="{{ route('admin.report') }}"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            {{-- Total --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Total Delivery</p>
                <p class="text-3xl font-bold text-gray-800">{{ $summary['total'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Semua status</p>
            </div>

            {{-- Completed --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Selesai</p>
                <p class="text-3xl font-bold text-green-600">{{ $summary['completed'] }}</p>
                <p class="text-xs text-green-600 mt-1">{{ $summary['completion_rate'] }}% dari total</p>
            </div>

            {{-- In Progress --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Dalam Perjalanan</p>
                <p class="text-3xl font-bold text-blue-600">{{ $summary['in_progress'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Aktif saat ini</p>
            </div>

            {{-- Pending --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-400 p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Menunggu</p>
                <p class="text-3xl font-bold text-yellow-500">{{ $summary['pending'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Belum dimulai</p>
            </div>

            {{-- Cancelled --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-red-400 p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Dibatalkan</p>
                <p class="text-3xl font-bold text-red-500">{{ $summary['cancelled'] }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $summary['total'] > 0 ? round(($summary['cancelled'] / $summary['total']) * 100, 1) : 0 }}% dari total
                </p>
            </div>

            {{-- Avg Duration --}}
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-5">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Avg Durasi</p>
                <p class="text-3xl font-bold text-purple-600">
                    {{ $summary['avg_duration_h'] }}<span class="text-lg">j</span>
                    {{ $summary['avg_duration_m'] }}<span class="text-lg">m</span>
                </p>
                <p class="text-xs text-gray-500 mt-1">Per delivery selesai</p>
            </div>
        </div>

        {{-- CHARTS ROW --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Trend Chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-800">Tren Delivery</h3>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Total</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Selesai</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span> Batal</span>
                    </div>
                </div>
                <canvas id="trendChart" style="height: 280px;"></canvas>
            </div>

            {{-- Donut Summary --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Distribusi Status</h3>
                <canvas id="statusDonut" style="height: 200px;"></canvas>
                <div class="mt-4 space-y-2">
                    @foreach([
                        ['label' => 'Selesai',           'val' => $summary['completed'],   'color' => 'bg-green-500'],
                        ['label' => 'Dalam Perjalanan',  'val' => $summary['in_progress'], 'color' => 'bg-blue-500'],
                        ['label' => 'Menunggu',          'val' => $summary['pending'],     'color' => 'bg-yellow-400'],
                        ['label' => 'Dibatalkan',        'val' => $summary['cancelled'],   'color' => 'bg-red-400'],
                    ] as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $item['color'] }} flex-shrink-0"></span>
                                <span class="text-gray-600">{{ $item['label'] }}</span>
                            </div>
                            <span class="font-semibold text-gray-800">{{ $item['val'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CHECKPOINT STATS --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="col-span-2 md:col-span-3 lg:col-span-6 flex items-center gap-2 mb-0">
                <h3 class="text-base font-semibold text-gray-800">📦 Statistik Loading/Unloading</h3>
                <span class="text-xs text-gray-400">(checkpoint yang selesai)</span>
            </div>
            @foreach([
                ['label' => 'Total Checkpoint', 'value' => $checkpointStats->total, 'suffix' => '', 'color' => 'text-gray-700'],
                ['label' => 'Avg Load Time',    'value' => $checkpointStats->avg_load,     'suffix' => ' min', 'color' => 'text-yellow-600'],
                ['label' => 'Avg Pickup',        'value' => $checkpointStats->avg_pickup,   'suffix' => ' min', 'color' => 'text-blue-600'],
                ['label' => 'Avg Delivery',      'value' => $checkpointStats->avg_delivery, 'suffix' => ' min', 'color' => 'text-purple-600'],
                ['label' => 'Tercepat',          'value' => $checkpointStats->min_load,     'suffix' => ' min', 'color' => 'text-green-600'],
                ['label' => 'Terlambat',         'value' => $checkpointStats->max_load,     'suffix' => ' min', 'color' => 'text-red-600'],
            ] as $stat)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs text-gray-400 font-medium uppercase mb-1">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold {{ $stat['color'] }}">{{ $stat['value'] }}<span class="text-base font-normal">{{ $stat['suffix'] }}</span></p>
                </div>
            @endforeach
        </div>

        {{-- DRIVER & ROUTE STATS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- DRIVER STATS --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Performa Driver</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Driver</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Selesai</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Rate</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Avg Durasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($driverStats as $idx => $stat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                                {{ $idx === 0 ? 'bg-yellow-100 text-yellow-700' : ($idx === 1 ? 'bg-gray-100 text-gray-600' : ($idx === 2 ? 'bg-orange-100 text-orange-600' : 'bg-gray-50 text-gray-500')) }}">
                                                {{ $idx + 1 }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-800">{{ $stat->driver->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $stat->total }}</td>
                                    <td class="px-4 py-3 text-center text-sm font-semibold text-green-600">{{ $stat->completed }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                            {{ $stat->completion_rate >= 90 ? 'bg-green-100 text-green-700' : ($stat->completion_rate >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $stat->completion_rate }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $stat->avg_duration_fmt }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ROUTE STATS --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Performa Rute</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rute</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Avg</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Best</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Worst</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($routeStats as $stat)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $stat->route->route_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-700">{{ $stat->total }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-purple-600 font-semibold">{{ $stat->avg_duration_fmt }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-green-600">{{ $stat->best_duration_fmt }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-red-500">{{ $stat->worst_duration_fmt }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- DETAIL TABLE --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Detail Delivery</h3>
                <span class="text-xs text-gray-400">{{ $recentDeliveries->total() }} data ditemukan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Driver</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rute</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Durasi</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Progress</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentDeliveries as $delivery)
                            @php
                                $totalCp     = $delivery->checkpoints->count();
                                $completedCp = $delivery->checkpoints->where('status', 'completed')->count();
                                $progress    = $totalCp > 0 ? round(($completedCp / $totalCp) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.deliveryShow', $delivery->id) }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                        {{ $delivery->delivery_code }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $delivery->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $delivery->driver->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $delivery->route->route_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{!! $delivery->status_badge !!}</td>
                                <td class="px-4 py-3 text-center text-sm text-gray-700">
                                    {{ $delivery->formatted_duration }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-center">
                                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full {{ $progress === 100 ? 'bg-green-500' : 'bg-blue-500' }}"
                                                style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $progress }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.deliveryShow', $delivery->id) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">Detail →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-sm">Tidak ada data delivery untuk filter yang dipilih</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if($recentDeliveries->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $recentDeliveries->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        // ============================
        // FILTER LOGIC
        // ============================
        const presets = {
            today:        [formatDate(new Date()), formatDate(new Date())],
            yesterday:    [formatDate(addDays(new Date(), -1)), formatDate(addDays(new Date(), -1))],
            last_7_days:  [formatDate(addDays(new Date(), -6)), formatDate(new Date())],
            this_week:    [formatDate(startOfWeek(new Date())), formatDate(new Date())],
            last_week:    [formatDate(startOfWeek(addDays(new Date(), -7))), formatDate(addDays(startOfWeek(new Date()), -1))],
            this_month:   [formatDate(startOfMonth(new Date())), formatDate(new Date())],
            last_month:   [formatDate(startOfMonth(addMonths(new Date(), -1))), formatDate(endOfMonth(addMonths(new Date(), -1)))],
            last_30_days: [formatDate(addDays(new Date(), -29)), formatDate(new Date())],
        };

        function setPreset(key) {
            document.getElementById('presetInput').value = key;
            document.getElementById('startDate').value   = presets[key][0];
            document.getElementById('endDate').value     = presets[key][1];
            document.querySelectorAll('.preset-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-600');
                btn.classList.add('bg-white', 'text-gray-600', 'border-gray-300');
            });
            event.target.classList.add('bg-blue-600', 'text-white', 'border-blue-600');
            event.target.classList.remove('bg-white', 'text-gray-600', 'border-gray-300');
            document.getElementById('filterForm').submit();
        }

        function clearPreset() {
            document.getElementById('presetInput').value = 'custom';
        }

        function formatDate(d) {
            return d.toISOString().split('T')[0];
        }
        function addDays(d, n) { const r = new Date(d); r.setDate(r.getDate() + n); return r; }
        function addMonths(d, n) { const r = new Date(d); r.setMonth(r.getMonth() + n); return r; }
        function startOfWeek(d) { const r = new Date(d); const day = r.getDay(); r.setDate(r.getDate() - (day === 0 ? 6 : day - 1)); return r; }
        function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
        function endOfMonth(d)   { return new Date(d.getFullYear(), d.getMonth() + 1, 0); }

        // ============================
        // TREND CHART
        // ============================
        const trendData = @json($deliveryTrend);

        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trendData.map(d => d.label),
                datasets: [
                    {
                        label: 'Selesai',
                        data: trendData.map(d => d.completed),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Pending/Progress',
                        data: trendData.map(d => d.pending + (d.in_progress || 0)),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Dibatalkan',
                        data: trendData.map(d => d.cancelled),
                        backgroundColor: 'rgba(248, 113, 113, 0.7)',
                        borderRadius: 4,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 11 } } },
                    tooltip: { mode: 'index' }
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // ============================
        // STATUS DONUT CHART
        // ============================
        const donutCtx = document.getElementById('statusDonut').getContext('2d');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Dalam Perjalanan', 'Menunggu', 'Dibatalkan'],
                datasets: [{
                    data: [
                        {{ $summary['completed'] }},
                        {{ $summary['in_progress'] }},
                        {{ $summary['pending'] }},
                        {{ $summary['cancelled'] }},
                    ],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.85)',
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(250, 204, 21, 0.85)',
                        'rgba(248, 113, 113, 0.85)',
                    ],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} (${Math.round(ctx.parsed / {{ $summary['total'] ?: 1 }} * 100)}%)`
                    }}
                }
            }
        });
    </script>
    @endpush
</x-app-layout>