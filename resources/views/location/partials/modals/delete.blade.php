<!-- Modal Create Route -->
<div id="deleteModal" class="hidden fixed inset-0 flex items-center bg-gray-600 bg-opacity-50 z-50">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex justify-center items-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h3 class="text-lg font-medium text-gray-900 text-center mt-4mb-2">
                Konfirmasi Hapus Lokasi
            </h3>

            <p class="text-sm text-gray-500 text-center mb-2">
                Apakah Anda yakin ingin menghapus lokasi ini?
            </p>

            <div id="deleteLocationInfo" class="bg-gray-50 p-3 rounded-lg mb-4">
                <!-- Location info will be populated here -->
            </div>

            <div class="bg-yellow-50 border-1-4 border-yellow-400 p-3 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Data yang dihapus tidak dapat dikembalikan!
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition duration-150">
                    Batal
                </button>
                <button type="button" onclick="submitDelete()" class="bg-red-500 hover:bg-red-600 text-white rounded px-4 transition duration-150">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>
