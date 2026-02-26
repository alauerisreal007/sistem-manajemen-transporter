<div id="imageViewerModal" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">
    <div class="relative mx-auto p-5 w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-black max-h-[90vh] overflow-y-auto">
        <button onclick="closeImageViewer()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition hover:scale-110">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img id="fullImage" src="" alt="Full Size" class="max-h-screen rounded-lg shadow-2xl">
    </div>
</div>
