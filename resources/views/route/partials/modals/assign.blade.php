<!-- Modal Assign Driver -->
<div id="assignModal" class="hidden fixed inset-0 flex items-center justify-center bg-gray-600 bg-opacity-50 z-50">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Driver ke Rute</h3>

            <form id="assignForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="driver_select" class="block text-sm font-medium text-gray-700 mb-2">Pilih
                        Driver</label>
                    <select id="driver_select" name="driver_id"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        required>
                        <option value="">-- Pilih Driver --</option>
                    </select>
                    <p id="no_drivers_msg" class="hidden text-sm text-red-600 mt-2">
                        Tidak ada driver yang tersedia saat ini.
                    </p>
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeAssignModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-2 rounded transition duration-150">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded transititon duration-150">
                        Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
