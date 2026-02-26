<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-800">Menu Delivery</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    <div class="p-4 md:8">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Active</p>
                        <p class="text-2xl text-blue-600 fon-bold">
                            {{ $deliveries->where('status', 'in_progress')->count() }}</p>
                    </div>
                    <svg class="w-10 h-10 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        <path
                            d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl text-yellow-600 font-bold">
                            {{ $deliveries->where('status', 'pending')->count() }}</p>
                    </div>
                    <svg class="w-10 h-10 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <h3 class="text-lg font-semibold mb-2">📍 Quick Actions</h3>
            <ul class="text-sm space-y-1 opactiry-90">
                <li>▫ Pastikan GPS aktif untuk tracking real-time</li>
                <li>▫ Upload foto di setiap checkpoint</li>
                <li>▫ Ambil tanda tangan penerima saat delivery</li>
            </ul>
        </div>

        {{-- Active Deliveries --}}
        <div class="space-y-4">
            @forelse ($deliveries as $delivery)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $delivery->delivery_code }}</h3>
                                <p class="text-xs text-gray-50">{{ $delivery->route->route_name }}</p>
                            </div>
                            {!! $delivery->status_badge !!}
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-4">
                        {{-- Progress Bar --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-1">
                                <div class="gap-2">
                                    <span class="text-sm font-medium text-gray-700">Progress</span>
                                    <span
                                        class="text-sm font-semibold text-blue-600">{{ $delivery->progress_percentage }}%</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $delivery->progress_percentage }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $delivery->checkpoints->where('status', 'completed')->count() }} / {{ $delivery->checkpoints->count() }} Checkpoint Selesai
                            </p>
                        </div>

                        {{-- Route Info --}}
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex items-start gap-2">
                                <span class="text-red-500 mt-0.5">📦</span>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{$delivery->route->pickupLocation->name}}</p>
                                    <p class="text-xs text-gray-500">Pickup</p>
                                </div>
                            </div>
                            <div class="ml-3 border-l-2 border-gray-100 pl-3 space-y-2">
                                @foreach ($delivery->route->deliveryLocations as $location)
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-500">🚚</span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $location->name }}</p>
                                            <p class="text-xs text-gray-500">Delivery {{ $loop->iteration }}</p>
                                        </div>
                                        @php
                                            $checkpoint = $delivery->checkpoints->where('location_id', $location->id)->first();
                                        @endphp
                                        @if($checkpoint && $checkpoint->status === 'completed')
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <a href="{{ route('user.deliveryShow', $delivery) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-lg font-semibold transition">
                            @if($delivery->status === 'pending')
                                🚀 Mulai Delivery
                            @else
                                📍 Lihat Detail
                            @endif
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">Tidak ada delivery aktif</p>
                    <p class="text-gray-400 text-sm mt-1">Delivery baru akan muncul di sini</p>
                </div>
            @endforelse
        </div>
    </div>

    @include('user.partials.modals.camera')
    @include('user.partials.modals.complete-checkpoint')
    @include('user.partials.modals.image-viewer')

    @include('user.partials.scripts.scripts')
</x-app-layout>
