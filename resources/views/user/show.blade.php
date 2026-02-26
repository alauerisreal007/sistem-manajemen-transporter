<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-900">Detail Delivery</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    <div id="deliveryPageData" data-delivery-id="{{ $delivery->id }}" data-delivery-status="{{ $delivery->status }}" class="hidden">
        
    </div>

    <div class="pb-24">
        {{-- Current Location Banner --}}
        @if ($delivery->status === 'in_progress' && $currentCheckpoint)
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center animate-pulse">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm opacity-90">Checkpoint Saat Ini:</p>
                        <p class="font-semibold text-lg">{{ $currentCheckpoint->location->name }}</p>
                        <p class="text-xs opacity-75">
                            {{ $currentCheckpoint->type === 'pickup' ? '📦 Pickup' : '🚚 Delivery' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Progress Overview --}}
        <div class="bg-white shadow p-4 m-4 rounded-lg">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                <span class="text-sm font-bold text-blue-600">{{ $delivery->progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-blue-600 h-3 rounded-full transition-all" id="progressBar"
                    style="width: {{ $delivery->progress_percentage }}%"></div>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                <span id="completedCount">{{ $delivery->checkpoints->where('status', 'completed')->count() }}</span> /
                <span class="totalCount">{{ $delivery->checkpoints->count() }}</span> checkpoint selesai
            </p>
        </div>

        {{-- Checkpoints Timeline --}}
        <div class="p-4 space-y-4">
            @foreach ($delivery->checkpoints->sortBy('sequence') as $checkpoint)
                <div class="bg-white rounded-lg shadow-md overflow-hidden checkpoint-card"
                    data-checkpoint-id="{{ $checkpoint->id }}">

                    {{-- Checkpoint Header --}}
                    <div
                        class="p-4 {{ $checkpoint->status === 'completed' ? 'bg-green-50 border-l-4 border-green-500' : ($checkpoint->status === 'in_progress' ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-gray-50 border-l-4 border-gray-300') }}">
                        <div class="flex items-start gap-3">
                            {{-- Status Icon --}}
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $checkpoint->status === 'completed' ? 'bg-green-500' : ($checkpoint->status === 'in_progress' ? 'bg-blue-500 animate-pulse' : 'bg-gray-300') }}">
                                @if ($checkpoint->status === 'completed')
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @elseif ($checkpoint->status === 'in_progress')
                                    <svg class="w-6 h-6 text-white animate-spin" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                @else
                                    <span class="text-white font-bold">{{ $checkpoint->sequence + 1 }}</span>
                                @endif
                            </div>

                            {{-- Checkpoint Info --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-lg">{{ $checkpoint->type_icon }}</span>
                                    <h3 class="font-semibold text-gray-900">{{ $checkpoint->location->name }}</h3>
                                </div>
                                <p class="text-sm text-gray-600">{{ $checkpoint->location->address }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full {{ $checkpoint->type === 'pickup' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $checkpoint->type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                    </span>
                                    @if ($checkpoint->status === 'completed')
                                        <span class="text-xs text-gray-500">
                                            ✔ {{ $checkpoint->departed_at->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Checkpoint Actions (In Progress) --}}
                    @if ($checkpoint->status === 'in_progress')
                        <div class="p-4 space-y-3">

                            {{-- Arrival Button --}}
                            @if (!$checkpoint->arrived_at)
                                <button onclick="arriveAtCheckpoint({{ $checkpoint->id }})"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg flex items-center justify-center gap-2 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Tiba di Lokasi
                                </button>
                            @else
                                {{-- Loading/Unloading Process --}}
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500 mb-2">
                                        ✓ Tiba: {{ $checkpoint->arrived_at->format('H:i') }}
                                    </p>

                                    @if (!$checkpoint->load_start_at)
                                        {{-- Start Loading/Unloading --}}
                                        <button onclick="startLoading({{ $checkpoint->id }})"
                                            class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2">
                                            @if ($checkpoint->type === 'pickup')
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                                </svg>
                                                Mulai Loading Barang
                                            @else
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L5 10.274zm10 0l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L15 10.274z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Mulai Unloading Barang
                                            @endif
                                        </button>
                                    @elseif (!$checkpoint->load_end_at)
                                        {{-- Loading in Progress --}}
                                        <div class="space-y-2">
                                            <div class="bg-blue-50 border border-blue-200 rounded p-2">
                                                <p class="text-sm text-blue-700 font-medium">
                                                    🕒 {{ $checkpoint->type === 'pickup' ? 'Loading' : 'Unloading' }}
                                                    dimulai:
                                                    {{ $checkpoint->load_start_at->format('H:i:s') }}
                                                </p>
                                            </div>
                                            <button onclick="endLoading({{ $checkpoint->id }})"
                                                class="w-full bg-green-500 hover:bg-green-600 text-white py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                @if ($checkpoint->type === 'pickup')
                                                    Selesai Loading
                                                @else
                                                    Selesai Unloading
                                                @endif
                                            </button>
                                        </div>
                                    @else
                                        {{-- Loading Completed --}}
                                        <div class="bg-green-50 border border-green-200 rounded p-2">
                                            <p class="text-sm text-green-700 font-medium">
                                                ✔ {{ $checkpoint->type === 'pickup' ? 'Loading' : 'Unloading' }}
                                                selesai:
                                                {{ $checkpoint->load_end_at->format('H:i:s') }}
                                                <span
                                                    class="text-xs">({{ $checkpoint->formatted_load_duration }})</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Photo Upload --}}
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        @if ($checkpoint->type === 'pickup')
                                            📷 Foto Barang yang Diambil
                                        @else
                                            📷 Foto Bukti Pengiriman
                                        @endif
                                    </label>
                                    <button onclick="openCamera({{ $checkpoint->id }})"
                                        class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Ambil Foto
                                    </button>

                                    {{-- Photo Preview Grid --}}
                                    @if ($checkpoint->checkpointPhotos->count() > 0)
                                        <div class="mt-3">
                                            <p class="text-xs text-gray-600 mb-2 font-medium">
                                                Foto Terupload ({{ $checkpoint->checkpointPhotos->count() }})
                                            </p>
                                            <div id="photos-{{ $checkpoint->id }}" class="grid grid-cols-3 gap-2">
                                                @foreach ($checkpoint->checkpointPhotos as $photo)
                                                    <div class="relative group">
                                                        <img src="{{ $photo->photo_url }}"
                                                            alt="Foto {{ $loop->iteration }}"
                                                            onclick="viewFullImage('{{ $photo->photo_url }}')"
                                                            class="w-full aspect-square object-cover rounded-lg border-2 border-gray-200 cursor-pointer hover:border-purple-400 transition-all shadow-sm"
                                                            loading="lazy">
                                                        <div
                                                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div id="photos-{{ $checkpoint->id }}"
                                            class="mt-3 text-center py-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300">
                                            <svg class="w-8 h-8 mx-auto text-gray-400 mb-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-xs text-gray-500">Belum ada foto</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Complete Checkpoint Button --}}
                                @if ($checkpoint->arrived_at && $checkpoint->load_end_at)
                                    <button onclick="openCompleteModal({{ $checkpoint->id }})"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-bold flex items-center justify-center gap-2 shadow-md">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Selesaikan Checkpoint
                                    </button>
                                @endif
                            @endif
                        </div>

                        {{-- Checkpoint Details (Completed) --}}
                    @elseif($checkpoint->status === 'completed')
                        <div class="p-4 bg-green-50 space-y-2">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-gray-500">Tiba:</span>
                                    <span class="font-medium">{{ $checkpoint->arrived_at?->format('H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Berangkat:</span>
                                    <span class="font-medium">{{ $checkpoint->departed_at?->format('H:i') }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Durasi
                                        {{ $checkpoint->type === 'pickup' ? 'Loading' : 'Unloading' }}:</span>
                                    <span class="font-medium">{{ $checkpoint->formatted_load_duration }}</span>
                                </div>
                                <div>
                                    <span
                                        class="text-gray-500">{{ $checkpoint->type === 'pickup' ? 'Petugas' : 'Penerima' }}:</span>
                                    <span class="font-medium">{{ $checkpoint->recipient_name ?? '-' }}</span>
                                </div>
                            </div>

                            {{-- Photos Grid --}}
                            @if ($checkpoint->checkpointPhotos->count() > 0)
                                <div class="bg-white p-3 rounded-lg">
                                    <p class="text-xs text-gray-600 mb-2 font-medium flex items-center gap-2">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Foto Bukti ({{ $checkpoint->checkpointPhotos->count() }}):
                                    </p>
                                    <div class="grid grid-cols-4 gap-2">
                                        @foreach ($checkpoint->checkpointPhotos as $photo)
                                            <div class="relative group">
                                                <img src="{{ $photo->photo_url }}"
                                                    alt="Foto {{ $loop->iteration }}"
                                                    onclick="viewFullImage('{{ $photo->photo_url }}')"
                                                    class="w-full aspect-square object-cover rounded-lg border-2 border-green-200 cursor-pointer hover:border-green-400 transition-all shadow-sm"
                                                    loading="lazy">
                                                <div
                                                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Start Delivery Button (Pending Status) --}}
        @if ($delivery->status === 'pending')
            <div class="max-w-3xl mx-auto mt-6 p-4">
                <div class="bg-white border rounded-xl p-5 shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-gray-500">Status Pengiriman</p>
                            <p class="font-semibold text-gray-900">Siap Dimulai</p>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">
                            Menunggu
                        </span>
                    </div>

                    <button onclick="startDelivery()"
                        class="w-full bg-green-600 hover:bg-green-700 active:scale-[0.98]
                       text-white py-3 rounded-xl font-bold
                       flex items-center justify-center gap-2
                       transition-all duration-150 shadow-md">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                clip-rule="evenodd" />
                        </svg>
                        Mulai Delivery
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- Modals --}}
    @include('user.partials.modals.camera')
    @include('user.partials.modals.complete-checkpoint')
    @include('user.partials.modals.image-viewer')

    {{-- Scripts --}}
    @include('user.partials.scripts.scripts')
</x-app-layout>
