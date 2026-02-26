<!-- Modal Edit Route -->
<div id="editModal" class="hidden fixed inset-0 flex items-center justify-center bg-gray-600 bg-opacity-50 z-50">
    <div
        class="relative mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4 pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Edit Rute</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editRouteForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Rute <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="route_name" id="edit_route_name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            placeholder="Contoh: Karanganyar - Semarang" required>
                    </div>

                    {{-- Pickup Location --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lokasi Pickup <span class="text-red-500">*</span>
                        </label>
                        <select name="pickup_location_id" id="edit_pickup_location_id"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            onchange="calculateEditDistanceAndTime()" required>
                            <option value="">-- Pilih Lokasi Pickup --</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" data-lat="{{ $location->latitude }}"
                                    data-lng="{{ $location->longitude }}" data-name="{{ $location->name }}">
                                    {{ $location->name }} - {{ $location->city }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1" id="edit_pickup_info"></p>
                    </div>

                    {{-- Multiple Delivery Locations --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Lokasi Delivery <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="addEditDeliveryLocation()"
                                class="bg-gradient-to-r from-blue-500 to-blue-600 hover:to-blue-800 text-white px-3 py-1 rounded text-xs transition duration-150 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Delivery
                            </button>
                        </div>

                        {{-- Delivery Locations Container --}}
                        <div id="editDeliveryLocationsContainer" class="space-y-3">
                            {{-- Delivery locations will be loaded here --}}
                        </div>

                        {{-- Info --}}
                        <div class="mt-3 bg-blue-100 border-l-4 border-blue-500 p-2">
                            <p class="text-xs text-blue-700">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <strong>Tips:</strong> Ubah urutan atau tambah/hapus titik delivery sesuai kebutuhan.
                            </p>
                        </div>
                    </div>

                    {{-- Distance & Time --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Total Jarak (km) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="distance_km" id="edit_distance_km"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 focus:ring-2 focus:ring-teal-500"
                                placeholder="Otomatis terisi" readonly required>
                            <p class="text-sm text-blue-600 mt-1">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Total Jarak Semua titik
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Estimasi Waktu (menit) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="estimated_time" id="edit_estimated_time"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 focus:ring-2 focus:ring-teal-500"
                                placeholder="Otomatis terisi" readonly required>
                            <p class="text-sm text-blue-600 mt-1">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Total Estimasi Waktu Berdasarkan Kecepatan Rata-Rata 60 km/jam
                            </p>
                        </div>
                    </div>

                    {{-- Route Summary --}}
                    <div id="editRouteSummary" class="hidden bg-gray-300 border border-gray-200 rounded-lg p-3">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Ringkasan Rute:</h4>
                        <div id="editRouteSummaryContent" class="text-xs text-gray-600 space-y-1"></div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="edit_status"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2 justify-end mt-6 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition duration-150">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:to-blue-800 text-white px-4 py-2 rounded transition duration-150">
                        Simpan Rute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
