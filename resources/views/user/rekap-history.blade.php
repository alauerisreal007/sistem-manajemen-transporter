<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Rekap Delivery</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    <div class="p-4 md:p-8 pb-24">

        {{-- ============================= --}}
        {{-- STATS SUMMARY                 --}}
        {{-- ============================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Total Selesai</p>
                <p class="text-2xl font-bold text-green-600">{{ $summary->total_completed }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Dibatalkan</p>
                <p class="text-2xl font-bold text-red-600">{{ $summary->total_cancelled }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Rata-rata Durasi</p>
                @php
                    $avgH = floor($averageDuration / 60);
                    $avgM = $averageDuration % 60;
                @endphp
                <p class="text-2xl font-bold text-blue-600">
                    {{ $averageDuration > 0 ? ($avgH > 0 ? "{$avgH}j {$avgM}m" : "{$avgM}m") : '-' }}
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Total Checkpoint</p>
                {{-- withCount sudah eager load, tidak lazy load --}}
                <p class="text-2xl font-bold text-purple-600">{{ $deliveries->sum('checkpoints_count') }}</p>
            </div>
        </div>

        {{-- ============================= --}}
        {{-- FILTER                        --}}
        {{-- ============================= --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('user.history') }}" class="flex gap-3 flex-wrap items-center">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300 text-sm">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                    Filter
                </button>
                @if (request()->hasAny(['status', 'date_from', 'date_to']))
                    <a href="{{ route('user.history') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- ============================= --}}
        {{-- HISTORY LIST                  --}}
        {{-- ============================= --}}
        <div class="space-y-4">
            @forelse ($deliveries as $delivery)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">

                    {{-- Card Header --}}
                    <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2 flex-wrap">
                                    <span>{{ $delivery->delivery_code }}</span>
                                    @if ($delivery->status === 'completed')
                                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">✔ Selesai</span>
                                    @elseif ($delivery->status === 'cancelled')
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-medium">❌ Dibatalkan</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-medium">⏳ Menunggu</span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 truncate">{{ $delivery->route->route_name }}</p>
                            </div>
                            <button onclick="toggleDetails({{ $delivery->id }})"
                                class="flex-shrink-0 flex items-center gap-1 text-white text-sm font-medium bg-blue-600 hover:bg-blue-700 rounded-lg px-3 py-2 transition-colors">
                                <span id="toggle-text-{{ $delivery->id }}">Detail</span>
                                <svg id="toggle-icon-{{ $delivery->id }}" class="w-4 h-4 transition-transform duration-200"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Quick Info --}}
                    <div class="p-4 bg-gray-50 grid grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">📅 Tanggal:</p>
                            {{-- FIX: started_at bisa null, gunakan ?-> --}}
                            <p class="font-semibold text-gray-900">
                                {{ $delivery->started_at?->format('d M Y') ?? $delivery->created_at->format('d M Y') }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">🕒 Durasi:</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->formatted_duration }}</p>
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">📍 Checkpoint:</p>
                            {{-- withCount → tidak lazy load --}}
                            <p class="font-semibold text-gray-900">{{ $delivery->checkpoints_count }}</p>
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">📊 Progress:</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->progress_percentage }}%</p>
                        </div>
                    </div>

                    {{-- Detail Panel --}}
                    <div id="details-{{ $delivery->id }}" class="hidden">

                        {{-- Timeline --}}
                        <div class="p-4 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Timeline Perjalanan
                            </h4>

                            <div class="space-y-4">
                                @foreach ($delivery->checkpoints->sortBy('sequence') as $checkpoint)
                                    <div class="flex gap-3 {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">

                                        <div class="flex-shrink-0">
                                            @if ($checkpoint->status === 'completed')
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            @elseif ($checkpoint->status === 'in_progress')
                                                <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">🚚</span>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">{{ $checkpoint->sequence + 1 }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 flex items-center gap-2 flex-wrap">
                                                <span>{{ $checkpoint->type_icon }} {{ $checkpoint->location->name }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded-full {{ $checkpoint->type === 'pickup' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $checkpoint->type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                                </span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $checkpoint->location->address }}</p>

                                            @if ($checkpoint->status === 'completed')
                                                <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">Tiba:</span>
                                                        <span class="font-semibold text-gray-900 ml-1">{{ $checkpoint->arrived_at?->format('H:i') ?? '-' }}</span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">Selesai:</span>
                                                        <span class="font-semibold text-gray-900 ml-1">{{ $checkpoint->departed_at?->format('H:i') ?? '-' }}</span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">Durasi:</span>
                                                        <span class="font-semibold text-gray-900 ml-1">{{ $checkpoint->formatted_load_duration ?? '-' }}</span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">{{ $checkpoint->type === 'pickup' ? 'Petugas' : 'Penerima' }}:</span>
                                                        <span class="font-semibold text-gray-900 ml-1">{{ $checkpoint->recipient_name ?? '-' }}</span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded col-span-2">
                                                        <span class="text-gray-500">Tanda Tangan:</span>
                                                        @if ($checkpoint->signature_url)
                                                            <img src="{{ $checkpoint->signature_url }}"
                                                                alt="Tanda Tangan"
                                                                onclick="viewFullSignature('{{ $checkpoint->signature_url }}')"
                                                                class="mt-1 h-16 border border-gray-300 rounded cursor-pointer hover:border-blue-400">
                                                        @else
                                                            <span class="font-semibold text-gray-400 ml-1">Tidak ada</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if ($checkpoint->checkpointPhotos->count() > 0)
                                                    <div class="mt-2">
                                                        <p class="text-xs text-gray-600 mb-1">📷 Foto ({{ $checkpoint->checkpointPhotos->count() }})</p>
                                                        <div class="grid grid-cols-5 gap-1">
                                                            @foreach ($checkpoint->checkpointPhotos->take(5) as $photo)
                                                                <img src="{{ $photo->photo_url }}"
                                                                    alt="Foto"
                                                                    onclick="viewFullImage('{{ $photo->photo_url }}')"
                                                                    class="w-full aspect-square object-cover rounded border border-gray-200 cursor-pointer hover:border-blue-400">
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{--
                            ╔══════════════════════════════════════════════════════════════╗
                            ║  GPS SUMMARY — pakai withAvg dari controller, BUKAN          ║
                            ║  $delivery->gpsTracking->... (itu lazy load semua records!)  ║
                            ╚══════════════════════════════════════════════════════════════╝
                        --}}
                        @if ($delivery->avg_speed !== null || $delivery->avg_accuracy !== null)
                            <div class="p-4 bg-blue-50 border-t border-blue-100">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    Data GPS Tracking
                                </h4>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs text-gray-500 mb-1">Kecepatan Rata-rata</p>
                                        <p class="text-lg font-bold text-blue-600">
                                            {{ $delivery->avg_speed ? number_format($delivery->avg_speed, 1) : '-' }} km/jam
                                        </p>
                                    </div>
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs text-gray-500 mb-1">Akurasi GPS</p>
                                        <p class="text-lg font-bold text-blue-600">
                                            {{ $delivery->avg_accuracy ? number_format($delivery->avg_accuracy, 1) : '-' }} m
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Alasan Pembatalan --}}
                        @if ($delivery->status === 'cancelled' && $delivery->cancellation_reason)
                            <div class="p-4 bg-red-50 border-t border-red-100">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-red-900 text-sm">Alasan Pembatalan</p>
                                        <p class="text-sm text-red-700 mt-1">{{ $delivery->cancellation_reason }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Completion Summary --}}
                        @if ($delivery->status === 'completed')
                            <div class="p-4 bg-green-50 border-t border-green-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-900">Delivery Berhasil Diselesaikan</p>
                                        {{-- FIX: completed_at bisa null --}}
                                        <p class="text-sm text-green-700">{{ $delivery->completed_at?->format('d M Y, H:i') ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>{{-- end details --}}
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Belum ada riwayat delivery</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $deliveries->links() }}
        </div>
    </div>

    @include('user.partials.modals.image-viewer')
    @include('user.partials.modals.signature-viewer')
    @include('user.partials.scripts.scripts')

</x-app-layout>