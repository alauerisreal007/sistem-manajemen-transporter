<div id="createDeliveryModal"
    class="fixed flex items-center justify-center inset-0 z-50 bg-gray-600 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto">
        {{-- Close Button --}}
        <button type="button" onclick="closeCreateDeliveryModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Header --}}
        <h3 class="text-xl font-semibold mb-6 text-gray-900">Buat Delivery Baru</h3>

        {{-- Form --}}
        <form id="createDeliveryForm" method="POST" action="{{ route('admin.deliveryStore') }}">
            @csrf

            {{-- Pilih Rute --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rute</label>
                <select name="route_id" id="deliveryRouteSelect" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500" onchange="onDeliveryRouteChange(this)">
                    <option value="">Pilih Rute</option>
                    @foreach ($routes as $route)
                        <option value="{{ $route->id }}" data-pickup="{{ $route->pickupLocation->name }}"
                            data-locations="{{ $route->deliveryLocations->pluck('name')->implode(', ') }}">
                            {{ $route->route_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Lokasi Pickup & Tujuan (Auto generate) --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pickup</label>
                <input type="text" id="deliveryPickupDisplay"
                    class="w-full border border-gray-200 rounded px-3 py-2 bg-gray-100" placeholder="Pilih Rute Terlebih Dahulu" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery</label>
                <input type="text" id="deliveryLocationsDisplay"
                    class="w-full border border-gray-200 rounded px-3 py-2 bg-gray-100" placeholder="Pilih Rute Terlebih Dahulu" readonly>
            </div>

            {{-- Assign Drivers --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    👤 Driver <span class="text-red-500"></span>
                </label>
                <select name="driver_id" id="deliveryDriverSelect" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500">
                    <option value="">Pilih Driver</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }} ({{ $driver->email }})</option>
                    @endforeach
                </select>
                @if ($drivers->isEmpty())
                    <p class="text-xs text-red-500 mt-1">
                        ⚠ Tidak ada driver yang tersedia. Semua driver sedang memiliki driver aktif.
                    </p>
                @else
                    <p class="text-xs text-gray-500 mt-1">
                        Hanya menampilkan driver yang tidak sedang bertugas ({{ $drivers->count() }} tersedia)
                    </p>
                @endif
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan <span class="text-gray-400">(Opsional)</span>
                </label>
                <textarea name="notes" id="notes" rows="3" placeholder="Masukkan catatan tambahan jika ada..." class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 resize-none"></textarea>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeCreateDeliveryModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition">
                    Batal
                </button>

                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Delivery
                </button>
            </div>
        </form>
    </div>
</div>
