{{-- Complete Checkpoint Modal --}}
<div id="completeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-40 flex items-end">
    <div class="bg-white rounded-t-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">Selesaikan Checkpoint</h3>
                <button onclick="closeCompleteModal()" class="text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Recipient Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penerima (Opsional)</label>
                <input type="text" id="recipientName" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500"
                    placeholder="Masukkan nama penerima">
            </div>

            {{-- Signature Pad --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan Penerima</label>
                <div class="border-2 border-gray-300 rounded-lg bg-white">
                    <canvas id="signaturePad" width="350" height="200" class="touch-none w-full"></canvas>
                </div>
                <button onclick="clearSignature()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                    Hapus Tanda Tangan
                </button>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                <textarea id="checkpointNotes" rows="3" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500"
                    placeholder="Tambahkan catatan jika diperlukan"></textarea>
            </div>

            {{-- Submit Button --}}
            <button onclick="submitComplete()" 
                class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-bold text-lg">
                ✓ Selesaikan Checkpoint
            </button>
        </div>
    </div>
</div>