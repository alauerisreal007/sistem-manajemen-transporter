{{-- Camera Modal --}}
<div id="cameraModal" class="hidden fixed inset-0 bg-black z-50">
    <div class="relative h-full flex flex-col">
        {{-- Camera Header --}}
        <div class="bg-black bg-opacity-50 text-white p-4 flex items-center justify-between">
            <h3 class="font-semibold">Ambil Foto</h3>
            <button onclick="closeCamera()" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Camera Preview --}}
        <div class="flex-1 relative">
            <video id="cameraPreview" class="w-full h-full object-cover" autoplay playsinline></video>
            <canvas id="canvas" class="hidden"></canvas>
        </div>

        {{-- Camera Controls --}}
        <div class="bg-black bg-opacity-50 p-6">
            <div class="flex items-center justify-center gap-4">
                <button onclick="switchCamera()" class="text-white p-3 bg-white bg-opacity-20 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
                
                <button onclick="capturePhoto()" class="w-16 h-16 bg-white rounded-full border-4 border-gray-300 flex items-center justify-center">
                    <div class="w-12 h-12 bg-white rounded-full"></div>
                </button>

                <div class="w-12"></div> {{-- Spacer --}}
            </div>
        </div>
    </div>
</div>