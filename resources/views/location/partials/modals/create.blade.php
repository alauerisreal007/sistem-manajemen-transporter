<!-- Modal Create Route -->
<div id="createModal" class="hidden fixed inset-0 flex items-center bg-gray-600 bg-opacity-50 z-50">
    <div class="relative mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Tambah Lokasi Baru</h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="createLocationForm" method="POST" action="{{ route('admin.locationStore') }}">
                @csrf
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p class="font-bold mb-2">Terjadi kesalahan:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                    <!-- Location Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            placeholder="Contoh: Pabrik KBU" required>
                    </div>

                    <!-- Location Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Lokasi <span
                                class="text-red-500">*</span></label>
                        <select name="type"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="pickup">Pickup</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>

                    <!-- Location Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat <span
                                class="text-red-500">*</span></label>
                        <textarea name="address" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            placeholder="Masukkan alamat lengkap" rows="3" required></textarea>
                    </div>

                    <!-- City and Province -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kota <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="city"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 focus:ring-2 focus:ring-teal-500"
                                placeholder="Contoh: Semarang" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="province"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 focus:ring-2 focus:ring-teal-500"
                                placeholder="Contoh: Jawa Tengah" required>
                        </div>
                    </div>

                    <!-- Postal Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Pos <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="postal_code"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 focus:ring-2 focus:ring-teal-500"
                            placeholder="Contoh: 59322" required>
                    </div>

                    <!-- Coordinates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="latitude"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                                placeholder="Latitude" required>
                            <p class="text-xs text-gray-500 mt-1">Koordinat GPS</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="longitude"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                                placeholder="Longitude" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span
                                class="text-red-500">*</span></label>
                        <select name="status" id="status"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-teal-500"
                            required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-2 justify-end mt-6 pt-4 border-t">
                    <button type="button" onclick="closeCreateModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition duration-150">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition duration-150">
                        Simpan Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
