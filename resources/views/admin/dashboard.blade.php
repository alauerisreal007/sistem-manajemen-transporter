<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Dashboard Admin</h2>
                <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
                    {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
                </p>
                <p class="text-xs text-gray-500">Last Update: <span class="text-sm font-semibold text-gray-700"
                        id="lastUpdate">{{ now()->format('H:i:s') }} WIB</span></p>
            </div>
        </div>
    </x-slot>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay"
        class="hidden fixed inset-0 bg-white/60 backdrop-blur-sm z-40 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg px-6 py-4 flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Memuat Data...</span>
        </div>
    </div>

    <div class="py-6 px-4 md:px-8">
        {{-- NON_TODAY BANNER --}}
        @if (!$isToday)
            <div class="mb-4 bg-orange-50 border border-orange-200 rounded-lg px-4 py-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-orange-700">
                    Menampilkan data untuk <strong>{{ $date->locale('id')->translatedFormat('l, d F Y') }}</strong>
                    <button onclick="setDate('today')" class="underline font-semibold hover:text-orange-900">Kembali ke
                        hari ini</button>
                </p>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-lg px-6 py-4 flex items-center justify-center gap-3 mb-6">
            {{-- DATE FILTER --}}
            <div class="flex items-center gap-2 flex-wrap">
                {{-- QUICK DATE SHORTCUTS --}}
                <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                    <button onclick="setDate('today')" id="btn-today"
                        class="date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition {{ $isToday ? 'bg-white shadow text-blue-600' : 'text-gray-600 hover:bg-white hover:shadow' }}">
                        Hari Ini
                    </button>
                    <button onclick="setDate('yesterday')" id="btn-yesterday"
                        class="date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:bg-white hover:shadow">
                        Kemarin
                    </button>
                    <button onclick="setDate('7days')" id="btn-7days"
                        class="date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:bg-white hover:shadow">
                        -7 Hari
                    </button>
                    <button onclick="setDate('30days')" id="btn-30days"
                        class="date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition text-gray-600 hover:bg-white hover:shadow">
                        -30 Hari
                    </button>
                </div>

                {{-- Manual Date Picker --}}
                <div class="relative">
                    <input type="date" id="datePicker" value="{{ $date->format('Y-m-d') }}"
                        max="{{ today()->format('Y-m-d') }}" onchange="applyDateFilter(this.value)"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                </div>

                {{-- Date Manual Badge --}}
                <span id="dateBadge"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $isToday ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                    📅 {{ $isToday ? 'Hari Ini' : $date->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
        </div>

        {{-- KEY METRICS CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-6">
            {{-- Active Deliveries --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            <path
                                d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-1">Active Deliveries</p>
                <p class="text-3xl font-bold text-blue-600" id="activeCount">{{ $activeDeliveries }}</p>
                <p class="text-xs text-gray-500 mt-2">🚛 In Progress</p>
            </div>

            {{-- Completed Today --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500 filterable-card">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 mb-1">Completed
                    <span id="completedLabel" class="font-medium text-gray-700">
                        {{ $isToday ? 'Hari Ini' : $date->format('d/m') }}
                    </span>
                </p>
                <p class="text-3xl font-bold text-green-600" id="completedCount">{{ $completedDeliveriesToday }}</p>
                <div class="mt-2">
                    <p class="text-xs text-gray-500">🎯 Target: {{ $targetDaily }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-500" id="targetBar"
                            style="width: {{ min(($completedDeliveriesToday / $targetDaily) * 100, 100) }}%">
                        </div>
                    </div>
                    <p class="text-xs text-green-600 mt-1" id="targetPct">
                        {{ min(round(($completedDeliveriesToday / $targetDaily) * 100), 100) }}% dari target
                    </p>
                </div>
            </div>

            {{-- Average Load Time --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500 filterable-card">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 mb-1">Avg Load Time</p>
                <p class="text-3xl font-bold text-yellow-600">
                    <span id="avgLoadTime">{{ round($avgLoadTime) }}</span>
                    <span class="text-lg">min</span>
                </p>
                <div class="mt-2 text-xs space-y-1">
                    <p class="text-gray-600">📦 Pickup: <span class="font-semibold"
                            id="avgPickup">{{ round($avgPickupTime) }}
                        </span> min</p>
                    <p class="text-gray-600">🚚 Delivery: <span class="font-semibold"
                            id="avgDelivery">{{ round($avgDeliveryTime) }}
                        </span> min</p>
                </div>
                <p class="text-xs mt-2" id="loadTrend">
                    @if ($loadTimeTrend < 0)
                        <p class="text-xs text-green-600 mt-2">📉 {{ abs($loadTimeTrend) }} min lebih cepat</p>
                    @elseif($loadTimeTrend > 0)
                        <p class="text-xs text-red-600 mt-2">📈 {{ $loadTimeTrend }} min lebih lambat</p>
                    @else
                        <p class="text-xs text-gray-500 mt-2">→ Sama dengan minggu lalu</p>
                    @endif
                </p>
            </div>

            {{-- Average Total Duration --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500 filterable-card">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 mb-1">Avg Total Duration</p>
                <p class="text-3xl font-bold text-purple-600">
                    <span id="avgDurationH">{{ floor($avgTotalDuration / 60) }}</span>h
                    <span id="avgDurationM">{{ $avgTotalDuration % 60 }}</span>m
                </p>
                <div class="mt-2 text-xs space-y-1">
                    <p class="text-gray-600">🏆 Best: <span class="font-semibold">
                            <span id="bestH">{{ floor($bestDuration / 60) }}</span>h
                            <span id="bestM">{{ $bestDuration % 60 }}</span>m
                        </span></p>
                    <p class="text-gray-600">⚠️ Worst: <span class="font-semibold">
                            <span id="worstH">{{ floor($worstDuration / 60) }}</span>h
                            <span id="worstM">{{ $worstDuration % 60 }}</span>m
                        </span></p>
                </div>
            </div>

            {{-- On-Time Rate --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500 filterable-card">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 mb-1">On-Time Rate</p>
                <p class="text-3xl font-bold text-indigo-600"><span id="onTimeRate">{{ $onTimeRate }}</span>%</p>
                <p class="text-xs mt-2" id="onTimeBadge">
                    @if ($onTimeRate >= 90)
                        <span class="text-green-600">✅ Diatas Target (90%)</span>
                    @else
                        <span class="text-orange-600">⚠ Dibawah Target (90%)</span>
                    @endif
                </p>
            </div>

            {{-- Active Drivers --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-teal-500">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-1">Active Drivers</p>
                <p class="text-3xl font-bold text-teal-600">{{ $activeDrivers }} <span class="text-lg">/
                        {{ $totalDrivers }}</span></p>
                <p class="text-xs text-gray-600 mt-2">{{ $availableDrivers }} available</p>
            </div>
        </div>

        {{-- MAIN CONTENT - 2 COLUMNS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- LEFT COLUMN: ACTIVE DELIVERIES --}}
            <div class="lg:col-span-2 bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            <path
                                d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                        </svg>
                        Active & Pending Deliveries
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-center">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Code
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Driver
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Route
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">
                                        Progress</th>
                                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($activeDeliveriesList as $delivery)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('admin.deliveryShow', $delivery->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium">
                                                {{ $delivery->delivery_code }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $delivery->driver->name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $delivery->route->route_name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full"
                                                        style="width: {{ $delivery->progress_percentage }}%"></div>
                                                </div>
                                                <span
                                                    class="text-xs font-medium text-gray-700">{{ $delivery->progress_percentage }}%</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $delivery->checkpoints->where('status', 'completed')->count() }} /
                                                {{ $delivery->checkpoints->count() }} selesai
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {!! $delivery->status_badge !!}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('admin.deliveryShow', $delivery->id) }}"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No active deliveries at the moment
                                        </td>
                                    </tr>
                                @endforelse

                                @foreach ($pendingDeliveriesList as $delivery)
                                    <tr class="hover:bg-gray-50 bg-yellow-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('admin.deliveryShow', $delivery->id) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium">
                                                {{ $delivery->delivery_code }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $delivery->driver->name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $delivery->route->route_name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full"
                                                        style="width: {{ $delivery->progress_percentage }}%"></div>
                                                </div>
                                                <span
                                                    class="text-xs font-medium text-gray-700">{{ $delivery->progress_percentage }}%</span>
                                            </div>
                                            <p class=" text-xs text-gray-500 mt-1">
                                                {{ $delivery->checkpoints->where('status', 'completed')->count() }} /
                                                {{ $delivery->checkpoints->count() }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {!! $delivery->status_badge !!}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('admin.deliveryShow', $delivery->id) }}"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: ANALYTICS --}}
            <div class="space-y-6">
                {{-- Loading Time Analysis --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd" />
                            </svg>
                            Loading Time by Location
                        </h3>
                        <span
                            class="text-xs px-2 py-1 rounded font-semibold {{ $isToday ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' }}">
                            {{ $isToday ? 'Hari Ini' : $date->format('d/m') }}
                        </span>
                    </div>
                    <div class="p-6 space-y-3" id="loadingByLocationContainer">
                        @forelse($loadingByLocation->take(5) as $item)
                            @php
                                $avgTime = round($item->avg_time, 1);
                                $percentage = min(($avgTime / 40) * 100, 100);
                                $colorClass =
                                    $avgTime < 20 ? 'bg-green-500' : ($avgTime < 30 ? 'bg-yellow-500' : 'bg-red-500');
                                $textClass =
                                    $avgTime < 20
                                        ? 'text-green-600'
                                        : ($avgTime < 30
                                            ? 'text-yellow-600'
                                            : 'text-red-600');
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $item->location->name }}</span>
                                    <span class="text-sm font-bold {{ $textClass }}">{{ $avgTime }}
                                        min</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $colorClass }} h-2 rounded-full"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $item->count }} checkpoints</p>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-4">No data available</p>
                        @endforelse
                        <a href="{{ route('admin.locationIndex') }}"
                            class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium mt-4">
                            View All Locations →
                        </a>
                    </div>
                </div>

                {{-- Duration by Route --}}
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z"
                                    clip-rule="evenodd" />
                            </svg>
                            Total Duration by Route
                        </h3>
                        <span
                            class="text-xs px-2 py-1 rounded {{ $isToday ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' }}">
                            7 Hari sebelum {{ $date->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="p-6 space-y-3">
                        @forelse($durationByRoute->take(5) as $item)
                            @php
                                $avgMinutes = round($item->avg_duration, 0);
                                $hours = floor($avgMinutes / 60);
                                $minutes = $avgMinutes % 60;
                                $percentage = min(($avgMinutes / 480) * 100, 100); // Max 8 hours
                                $colorClass =
                                    $avgMinutes < 240
                                        ? 'bg-green-500'
                                        : ($avgMinutes < 360
                                            ? 'bg-yellow-500'
                                            : 'bg-orange-500');
                                $textClass =
                                    $avgMinutes < 240
                                        ? 'text-green-600'
                                        : ($avgMinutes < 360
                                            ? 'text-yellow-600'
                                            : 'text-orange-600');
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span
                                        class="text-sm font-medium text-gray-700">{{ $item->route->route_name }}</span>
                                    <span class="text-sm font-bold {{ $textClass }}">{{ $hours }}h
                                        {{ $minutes }}m</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="{{ $colorClass }} h-2 rounded-full"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">{{ $item->count }} deliveries</p>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-4">Tidak ada data</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- PERFORMANCE TRENDS --}}
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                    Performance Trends - 7 Hari Sebelum
                    <span id="chartDateLabel" class="text-sm font-normal text-gray-500 mt-1">
                        ({{ $date->locale('id')->translatedFormat('d F Y') }})
                    </span>
                </h3>
            </div>
            <div class="p-6">
                <canvas id="performanceChart" class="w-full" style="height: 300px;"></canvas>
            </div>
        </div>

        {{-- TOP PERFORMERS --}}

        <div class="bg-white rounded-lg shadow" id="topDriversSection">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Top Performing Drivers
                </h3>
                <span id="topDriversLabel"
                    class="text-xs px-2 py-1 rounded {{ $isToday ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600' }}">
                    {{ $isToday ? 'Hari Ini' : $date->locale('id')->translatedFormat('d F Y') }}
                </span>
            </div>
            <div class="p-6" id="topDriversContent">
                @if ($topDrivers->count() > 0)
                    <div class="space-y-4">
                        @foreach ($topDrivers as $index => $driver)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white font-bold text-lg">
                                        #{{ $index + 1 }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $driver->name }}</h4>
                                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-600">
                                        <span>✅ {{ $driver->completed_today }} deliveries</span>
                                        <span>⭐ 100% on-time</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-6">Tidak ada data driver untuk tanggal ini</p>
                @endif
            </div>
        </div>

    </div>

    @include('admin.partials.scripts.scripts')
</x-app-layout>
