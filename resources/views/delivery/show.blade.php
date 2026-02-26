<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Live Tracking - {{ $delivery->delivery_code }}</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    <div class="p-4 md:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 mb-6">

            {{-- Driver info card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <h3 class="text-base md:text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd" />
                    </svg>
                    Informasi Driver
                </h3>

                <div class="space-y-4">
                    {{-- Driver Profile --}}
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-200">
                        <div
                            class="w-12 h-12 md:w-16 md:h-16 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center shadow-lg flex-shrink-0">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-base md:text-lg truncate">
                                {{ $delivery->driver->name }}</p>
                            <p class="text-xs md:text-sm text-gray-500 truncate">{{ $delivery->driver->email }}</p>
                        </div>
                    </div>

                    {{-- Driver Performance Badge --}}
                    @php
                        $completedCount = $delivery->driver->deliveries()->where('status', 'completed')->count();
                        $badge =
                            $completedCount >= 100
                                ? [
                                    'text' => '⭐ Expert Driver',
                                    'class' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                ]
                                : ($completedCount >= 50
                                    ? [
                                        'text' => '🏆 Senior Driver',
                                        'class' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    ]
                                    : ($completedCount >= 10
                                        ? [
                                            'text' => '✅ Experienced',
                                            'class' => 'bg-green-100 text-green-800 border-green-300',
                                        ]
                                        : [
                                            'text' => '🚀 New Driver',
                                            'class' => 'bg-gray-100 text-gray-800 border-gray-300',
                                        ]));
                    @endphp
                    <div class="text-center">
                        <span
                            class="inline-block px-3 py-1 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                            {{ $badge['text'] }}
                        </span>
                    </div>

                    {{-- Delivery Assignment Info --}}
                    <div class="pt-3 border-t border-gray-200">
                        <div class="text-xs text-gray-500 space-y-1.5">
                            <div class="flex justify-between gap-2">
                                <span>Ditugaskan:</span>
                                <span class="font-medium text-gray-700">
                                    {{ $delivery->created_at->format('d M Y, H:i:s') }}
                                </span>
                            </div>
                            @if ($delivery->started_at)
                                <div class="flex justify-between gap-2">
                                    <span>Dimulai:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ $delivery->started_at->format('d M Y, H:i:s') }}
                                    </span>
                                </div>
                            @endif
                            @if ($delivery->completed_at)
                                <div class="flex justify-between gap-2">
                                    <span>Selesai:</span>
                                    <span class="font-medium text-gray-700">
                                        {{ $delivery->completed_at->format('d M Y, H:i:s') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <h3 class="text-base md:text-lg font-semibold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="truncate">Status Delivery</span>
                </h3>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 font-medium">Status</label>
                        <div class="mt-1">{!! $delivery->status_badge !!}</div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Progress</label>
                        <div class="mt-1">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-3">
                                    <div class="bg-blue-600 h-3 rounded-full transition-all" id="progressBar"
                                        style="width: {{ $delivery->progress_percentage }}%"></div>
                                </div>
                                <span class="text-sm font-semibold"
                                    id="progressText">{{ $delivery->progress_percentage }}%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <span
                                    id="completedCheckpoints">{{ $delivery->checkpoints->where('status', 'completed')->count() }}</span>
                                /
                                <span id="totalCheckpoints">{{ $delivery->checkpoints->count() }}</span> checkpoint
                                selesai
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Lokasi Saat Ini</label>
                        <p class="text-sm font-medium mt-1" id="currentLocation">
                            {{ $delivery->getCurrentCheckpoint()?->location->name ?? 'Belum dimulai' }}
                        </p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Durasi Perjalanan</label>
                        <p class="text-sm font-medium mt-1" id="journeyDuration">
                            {{ $delivery->formatted_duration }}
                        </p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Kecepatan</label>
                        <p class="text-sm font-medium mt-1" id="currentSpeed">
                            <span id="speedValue">-</span> km/h
                        </p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Baterai Driver</label>
                        <div class="mt-1 flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" id="batteryBar" style="width: 0%"></div>
                            </div>
                            <span class="text-sm font-medium" id="batteryText">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <h3 class="text-base md:text-lg font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="truncate">Informasi Rute</span>
                </h3>

                <div class="space-y-3 text-sm">
                    <div>
                        <label class="text-xs text-gray-500 font-medium">Nama Rute</label>
                        <p class="font-medium mt-1">{{ $delivery->route->route_name }}</p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Jarak Total</label>
                        <p class="font-medium mt-1">{{ $delivery->route->distance_km }} km</p>
                    </div>

                    <div>
                        <label class="text-xs text-gray-500 font-medium">Estimasi Waktu</label>
                        <p class="font-medium mt-1">{{ $delivery->route->estimated_time }} menit</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkpoint Timeline -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <h3 class="text-base md:text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd" />
                </svg>
                Timeline Checkpoint
            </h3>

            <div class="space-y-4">
                @foreach ($delivery->checkpoints as $checkpoint)
                    <div
                        class="flex items-start gap-3 md:gap-4 p-3 md:p-4 rounded-lg {{ $checkpoint->status === 'completed' ? 'bg-green-50 border border-green-200' : ($checkpoint->status === 'in_progress' ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200') }}">
                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                            {{ $checkpoint->status === 'completed' ? 'bg-green-500' : ($checkpoint->status === 'in_progress' ? 'bg-blue-500' : 'bg-gray-300') }}">
                            @if ($checkpoint->status === 'completed')
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            @elseif($checkpoint->status === 'in_progress')
                                <svg class="w-5 h-5 text-white animate-spin" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            @else
                                <span class="text-white font-semibold">{{ $checkpoint->sequence + 1 }}</span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-lg flex-shrink-0">{{ $checkpoint->type_icon }}</span>
                                <h4 class="font-semibold text-gray-900">{{ $checkpoint->location->name }}</h4>
                                <span
                                    class="text-xs px-2 py-1 rounded-full flex-shrink-0 {{ $checkpoint->type === 'pickup' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $checkpoint->type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mb-2">{{ $checkpoint->location->address }}</p>

                            @if ($checkpoint->status === 'completed')
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs mb-3">
                                    <div>
                                        <span class="text-gray-500">Tiba:</span>
                                        <span
                                            class="font-medium">{{ $checkpoint->arrived_at?->format('H:i:s') ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Mulai:</span>
                                        <span
                                            class="font-medium">{{ $checkpoint->load_start_at?->format('H:i:s') ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Selesai:</span>
                                        <span
                                            class="font-medium">{{ $checkpoint->load_end_at?->format('H:i:s') ?? '-' }}</span>
                                    </div>
                                    <div>
                                        @if ($checkpoint->type === 'pickup')
                                            <span class="text-gray-500">Durasi:</span>
                                            <span
                                                class="font-medium">{{ $checkpoint->formatted_load_duration }}</span>
                                        @elseif ($checkpoint->type === 'delivery')
                                            <span class="text-gray-500">Durasi:</span>
                                            <span
                                                class="font-medium">{{ $checkpoint->formatted_load_duration }}</span>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-2 rounded mb-3">
                                    <span
                                        class="text-xs text-gray-500">{{ $checkpoint->type === 'pickup' ? 'Petugas' : 'Penerima' }}:</span>
                                    <span
                                        class="text-xs font-semibold text-gray-900 ml-1">{{ $checkpoint->recipient_name ?? '-' }}</span>
                                </div>

                                {{-- Tanda Tangan --}}
                                @if ($checkpoint->signature_url)
                                    <div class="mb-3">
                                        <p class="text-xs text-gray-600 font-semibold mb-2">Tanda Tangan:</p>

                                        <img src="{{ $checkpoint->signature_url }}"
                                            alt="Tanda Tangan"
                                            onclick="viewFullSignature('{{ $checkpoint->signature_url }}')"
                                            class="h-20 border border-gray-300 rounded cursor-pointer hover:border-blue-400 transition">
                                    </div>
                                @endif

                                {{-- Foto Checkpoint --}}
                                @if ($checkpoint->checkpointPhotos && $checkpoint->checkpointPhotos->count() > 0)
                                    <div class="mt-3">
                                        <p class="text-xs text-gray-600 font-semibold mb-2">Foto
                                            ({{ $checkpoint->checkpointPhotos->count() }})</p>
                                        <div class="grid grid-cols-4 gap-1">
                                            @foreach ($checkpoint->checkpointPhotos->take(8) as $photo)
                                                <img src="{{ $photo->photo_url }}" alt="Foto"
                                                    onclick="viewFullImage('{{ $photo->photo_url }}')"
                                                    class="w-full aspect-square object-cover rounded border border-gray-300 cursor-pointer hover:border-blue-400 transition">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @elseif($checkpoint->status === 'in_progress')
                                <p class="text-sm text-blue-600 font-medium">🚛 Sedang proses...</p>
                            @else
                                <p class="text-sm text-gray-400">⏳ Menunggu...</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @include('delivery.partials.modals.signature-viewer')
    @include('delivery.partials.modals.image-viewer')
    @include('delivery.partials.scripts.scripts')
</x-app-layout>
