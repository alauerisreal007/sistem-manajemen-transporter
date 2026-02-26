<!-- Modal Detail Location -->
<div id="detailModal" class="hidden fixed inset-0 flex items-center bg-gray-600 bg-opacity-50 z-50">
    <div class="relative mx-auto p-5 w-[600px] shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Lokasi</h3>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div id="detailContent" class="space-y-4">
                <!-- Detail content will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
</div>
