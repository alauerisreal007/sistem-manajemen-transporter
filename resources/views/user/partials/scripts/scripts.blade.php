@push('scripts')
    <script>
        let currentCheckpointId = null;
        let cameraStream = null;
        let currentCamera = 'environment';
        let signaturePad = null;
        let gpsWatchId = null;
        let lastGpsUdapte = 0;

        const DELIVERY_ID = document.getElementById('deliveryPageData')?.dataset?.deliveryId ?? null;
        const DELIVERY_STATUS = document.getElementById('deliveryPageData')?.dataset?.deliveryStatus ?? null;

        // ========================================
        // TOAST NOTIFICATION (ALERTS)
        // ========================================
        function showLoadingToast(message) {
            const toast = document.createElement('div');
            toast.id = 'loadingToast';
            toast.className =
                'fixed bottom-20 left-4 right-4 bg-blue-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3';
            toast.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            return toast;
        }

        function closeLoadingToast() {
            const toast = document.getElementById('loadingToast');
            if (toast) toast.remove();
        }

        function showSuccessToast(message) {
            const toast = document.createElement('div');
            toast.className =
                'fixed bottom-20 left-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            toast.innerHTML = `
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function showErrorToast(message) {
            const toast = document.createElement('div');
            toast.className =
                'fixed bottom-20 left-4 right-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // ========================================
        // HELPER FUNCTION: Better Fetch with Error Handling
        // ========================================
        async function apiRequest(url, options = {}) {
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        ...options.headers
                    }
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Response is not JSON:', await response.text());
                    throw new Error('Server returned non-JSON response. Check console for details.');
                }

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Request failed');
                }

                return data;
            } catch (error) {
                console.error('API Request Error:', error);
                throw error;
            }
        }

        // ========================================
        // GPS TRACKING
        // ========================================
        function startGpsTracking() {
            if (!DELIVERY_ID) {
                console.warn('GPS: DELIVERY_ID tidak ada, GPS tidak dijalankan.');
                return;
            }

            if (!("geolocation" in navigator)) {
                console.warn('GPS: Geolocation tidak didukung browser ini.');
                return;
            }

            gpsWatchId = navigator.geolocation.watchPosition(
                updateGpsLocation,
                (error) => console.error('GPS Error:', error), {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 5000
                }
            );
            console.log('GPS tracking started, delivery:', DELIVERY_ID);
        }

        async function updateGpsLocation(position) {
            if (!DELIVERY_ID) return;

            const now = Date.now();
            if (now - lastGpsUdapte < 10000) return;
            lastGpsUpdate = now;

            const data = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                speed: position.coords.speed,
                accuracy: position.coords.accuracy,
                heading: position.coords.heading,
                battery_level: await getBatteryLevel()
            };

            try {
                await apiRequest(`/user/deliveries/${DELIVERY_ID}/update-gps`, {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
            } catch (err) {
                console.error('GPS update failed:', err);
            }
        }

        async function getBatteryLevel() {
            if ('getBattery' in navigator) {
                try {
                    const battery = await navigator.getBattery();
                    return Math.round(battery.level * 100);
                } catch (e) {
                    return null;
                }
            }
            return null;
        }

        // ========================================
        // DELIVERY ACTIONS
        // ========================================
        async function startDelivery() {
            if (!DELIVERY_ID) return;
            if (!confirm('Mulai delivery sekarang?')) return;

            try {
                // Disable button sementara (anti double click)
                const btn = event?.target?.closest('button');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-60', 'cursor-not-allowed');
                }

                const data = await apiRequest(`/user/deliveries/${DELIVERY_ID}/start`, {
                    method: 'POST'
                });

                if (data.success) {
                    showLoadingToast(data.message);

                    // Start GPS tracking immediately
                    startGpsTracking();

                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                showErrorToast('Error: ' + err.message);
            }
        }

        async function arriveAtCheckpoint(checkpointId) {
            try {
                showLoadingToast('Mengambil lokasi GPS...');

                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 10000
                    });
                });

                closeLoadingToast();

                const data = await apiRequest(`/user/checkpoints/${checkpointId}/arrive`, {
                    method: 'POST',
                    body: JSON.stringify({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    })
                });

                if (data.success) {
                    showSuccessToast(data.message);
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                closeLoadingToast();
                showErrorToast('Error: ' + (err.message || 'Tidak dapat mendapatkan lokasi GPS'));
            }
        }

        async function startLoading(checkpointId) {
            try {
                const data = await apiRequest(`/user/checkpoints/${checkpointId}/start-loading`, {
                    method: 'POST'
                });

                if (data.success) {
                    showSuccessToast(data.message);
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                showErrorToast('Error: ' + err.message);
            }
        }

        async function endLoading(checkpointId) {
            try {
                const data = await apiRequest(`/user/checkpoints/${checkpointId}/end-loading`, {
                    method: 'POST'
                });

                if (data.success) {
                    showSuccessToast(data.message);
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (err) {
                showErrorToast('Error: ' + err.message);
            }
        }

        // ========================================
        // CAMERA FUNCTIONS
        // ========================================
        async function openCamera(checkpointId) {
            currentCheckpointId = checkpointId;
            document.getElementById('cameraModal').classList.remove('hidden');

            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: currentCamera,
                        width: {
                            ideal: 1020
                        },
                        height: {
                            ideal: 1000
                        }
                    }
                });
                document.getElementById('cameraPreview').srcObject = cameraStream;
            } catch (err) {
                showErrorToast('Tidak dapat mengakses kamera: ' + err.message);
                closeCamera();
            }
        }

        function closeCamera() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(track => track.stop());
                cameraStream = null;
            }
            document.getElementById('cameraModal').classList.add('hidden');
        }

        async function switchCamera() {
            currentCamera = currentCamera === 'environment' ? 'user' : 'environment';
            closeCamera();
            await openCamera(currentCheckpointId);
        }

        async function capturePhoto() {
            const video = document.getElementById('cameraPreview');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            // Compression
            const MAX_WIDTH = 1024;
            const MAX_HEIGHT = 1024;
            const QUALITY = 0.7;

            let width = video.videoWidth;
            let height = video.videoHeight;

            // Resize
            if (width > height) {
                if (width > MAX_WIDTH) {
                    height = height * (MAX_WIDTH / width);
                    width = MAX_WIDTH;
                }
            } else {
                if (height > MAX_HEIGHT) {
                    width = width * (MAX_HEIGHT / height);
                    height = MAX_HEIGHT;
                }
            }

            canvas.width = width;
            canvas.height = height;
            context.drawImage(video, 0, 0, width, height);

            const imageData = canvas.toDataURL('image/jpeg', QUALITY);

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(
                        resolve,
                        () => resolve(null), {
                            timeout: 5000
                        }
                    );
                });

                await uploadPhoto(
                    currentCheckpointId,
                    imageData,
                    position?.coords.latitude,
                    position?.coords.longitude
                );
            } catch (err) {
                showErrorToast('Error capturing photo: ' + err.message);
            }
        }

        async function uploadPhoto(checkpointId, photoData, latitude, longitude) {
            try {
                // Show loading
                showLoadingToast('Mengupload foto...');

                const data = await apiRequest(`/user/checkpoints/${checkpointId}/upload-photo`, {
                    method: 'POST',
                    body: JSON.stringify({
                        photo: photoData,
                        type: 'proof',
                        latitude: latitude,
                        longitude: longitude
                    })
                });

                closeLoadingToast();

                if (data.success) {
                    showSuccessToast('Foto berhasil diupload!');
                    closeCamera();

                    const photoContainer = document.getElementById(`photos-${checkpointId}`);
                    if (photoContainer) {
                        const placeholder = photoContainer.querySelector('.border-dashed');
                        if (placeholder) {
                            photoContainer.innerHTML = '';
                            photoContainer.classList.remove('border-2', 'border-dashed', 'text-center', 'py-4',
                                'bg-gray-100');
                            photoContainer.classList.add('grid', 'grid-cols-3', 'gap-2', 'mt-3');

                            const label = photoContainer.previousElementSibling;
                            if (label && label.classList.contains('text-xs')) {
                                label.innerHTML =
                                    '<p class="text-xs text-gray-600 mb-2 font-medium">Foto Terupload (1)</p>';
                            }
                        }

                        const photoDiv = document.createElement('div');
                        photoDiv.className = 'relative group';
                        photoDiv.innerHTML = `
                            <img src="${data.photo_url}" alt="Foto" onclick="viewFullImage('${data.photo_url}')" class="w-full aspect-square object-cover rounded-lg border-2 border-gray-200 cursor-pointer hover:border-purple-400 transition-all shadow-sm" loading="lazy">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all"></div>
                    `;
                        photoContainer.appendChild(photoDiv);

                        // Update count
                        const label = photoContainer.previousElementSibling;
                        if (label) {
                            const currentCount = photoContainer.querySelectorAll('img').length;
                            label.textContent = `Foto Terupload (${currentCount})`;
                        }
                    }
                }
            } catch (err) {
                closeLoadingToast();
                showErrorToast('Upload gagal: ' + err.message);
            }
        }

        // ========================================
        // COMPLETE CHECKPOINT
        // ========================================
        function openCompleteModal(checkpointId) {
            currentCheckpointId = checkpointId;
            document.getElementById('completeModal').classList.remove('hidden');

            // Initialize signature pad
            setTimeout(() => {
                const canvas = document.getElementById('signaturePad');
                if (canvas && typeof SignaturePad !== 'undefined') {
                    signaturePad = new SignaturePad(canvas);
                }
            }, 1000);
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').classList.add('hidden');
            signaturePad = null;
        }

        function clearSignature() {
            if (signaturePad) {
                signaturePad.clear();
            }
        }

        async function submitComplete() {
            const recipientName = document.getElementById('recipientName').value;
            const notes = document.getElementById('checkpointNotes').value;
            const signature = signaturePad && !signaturePad.isEmpty() ? signaturePad.toDataURL() : null;

            try {
                showLoadingToast('Menyelesaikan checkpoint...');
                const data = await apiRequest(`/user/checkpoints/${currentCheckpointId}/complete`, {
                    method: 'POST',
                    body: JSON.stringify({
                        recipient_name: recipientName,
                        signature: signature,
                        notes: notes
                    })
                });

                closeLoadingToast();

                if (data.success) {
                    alert(data.message);
                    if (data.all_completed) {
                        alert('🎉 Semua checkpoint selesai! Delivery completed!');
                    }
                    location.reload();
                }
            } catch (err) {
                closeLoadingToast();
                showErrorToast('Error: ' + err.message)
            }
        }

        function toggleDetails(deliveryId) {
            const detailsDiv = document.getElementById(`details-${deliveryId}`);
            const toggleText = document.getElementById(`toggle-text-${deliveryId}`);
            const toggleIcon = document.getElementById(`toggle-icon-${deliveryId}`);

            if (detailsDiv.classList.contains('hidden')) {
                detailsDiv.classList.remove('hidden');
                toggleText.textContent = 'Tutup Detail';
                toggleIcon.style.transform = 'rotate(180deg)';
            } else {
                detailsDiv.classList.add('hidden');
                toggleText.textContent = 'Lihat Detail';
                toggleIcon.style.transform = 'rotate(0deg)';
            }
        }

        function viewFullImage(url) {
            document.getElementById('fullImage').src = url;
            document.getElementById('imageViewerModal').classList.remove('hidden');
        }

        function closeImageViewer() {
            document.getElementById('imageViewerModal').classList.add('hidden');
        }

        function viewFullSignature(url) {
            document.getElementById('fullSignature').src = url;
            document.getElementById('signatureViewerModal').classList.remove('hidden')
        }

        function closeSignatureViewer() {
            document.getElementById('signatureViewerModal').classList.add('hidden');
        }

        // ========================================
        // INITIALIZATION
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Driver scripts loaded');
            console.log('Delivery ID:', DELIVERY_ID);
            console.log('Delivery Status:', DELIVERY_STATUS);

            // Image Viewer Modal - Click on background to close
            const imageViewerModal = document.getElementById('imageViewerModal');
            if (imageViewerModal) {
                imageViewerModal.addEventListener('click', function(e) {
                    // Close only if clicking on the modal background, not on the image or content
                    if (e.target === imageViewerModal) {
                        closeImageViewer();
                    }
                });

                // Also allow ESC key to close
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !imageViewerModal.classList.contains('hidden')) {
                        closeImageViewer();
                    }
                });
            }

            // Signature Viewer Modal - Click on background to close
            const signatureViewerModal = document.getElementById('signatureViewerModal');
            if (signatureViewerModal) {
                signatureViewerModal.addEventListener('click', function(e) {
                    // Close only if clicking on the the modal background, not on the image or content
                    if (e.target === signatureViewerModal) {
                        closeSignatureViewer();
                    }
                });

                // Also allow ESC key to close
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !signatureViewerModal.classList.contains('hidden')) {
                        closeSignatureViewer();
                    }
                });
            }

            // Start GPS tracking if delivery is in progress
            if (DELIVERY_ID && DELIVERY_STATUS === 'in_progress') {
                startGpsTracking();
            }

            // Load Signature Pad library
            if (typeof SignaturePad === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js';
                script.onload = () => console.log('SignaturePad loaded');
                script.onerror = () => console.error('Failed to load SignaturePad');
                document.head.appendChild(script);
            }
        });

        // Debug: Log current route
        console.log('Current page:', window.location.pathname);
        console.log('Delivery ID:', DELIVERY_ID);
    </script>
@endpush
