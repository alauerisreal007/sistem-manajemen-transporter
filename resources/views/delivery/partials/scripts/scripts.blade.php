@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        let map;
        let driverMarker;
        let checkpointMarkers = [];
        let routePath;
        let gpsTrail;

        const deliveryId = {{ $delivery->id }};

        const checkpoints = {!! json_encode(
            $delivery->checkpoints->map(function ($checkpoint) {
                return [
                    'id' => $checkpoint->id,
                    'name' => $checkpoint->location->name,
                    'lat' => (float) $checkpoint->location->latitude,
                    'lng' => (float) $checkpoint->location->longitude,
                    'type' => $checkpoint->type,
                    'status' => $checkpoint->status,
                    'sequence' => $checkpoint->sequence,
                ];
            }),
        ) !!};

        // Initialize OpenStreetMap
        function initMap() {
            // Destroy existing map if any
            if (map) {
                try {
                    map.off();
                    map.remove();
                } catch (e) {
                    console.log('Error cleaning old map:', e);
                }
            }

            try {
                // Center on first checkpoint (pickup)
                const center = [checkpoints[0].lat, checkpoints[0].lng];

                // Create map
                map = L.map('map', {
                    center: center,
                    zoom: 13,
                    zoomControl: true,
                    scrollWheelZoom: true,
                    dragging: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true
                });

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                    minZoom: 3
                }).addTo(map);

                // Add checkpoint markers
                checkpoints.forEach((checkpoint, index) => {
                    const color = getMarkerColor(checkpoint);

                    const icon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="
                                background-color: ${color};
                                width: 35px;
                                height: 35px;
                                border-radius: 50%;
                                border: 3px solid white;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-weight: bold;
                                color: white;
                                font-size: 14px;
                            ">${index + 1}</div>`,
                        iconSize: [35, 35],
                        iconAnchor: [17, 17],
                        popupAnchor: [0, -20]
                    });

                    const marker = L.marker([checkpoint.lat, checkpoint.lng], {
                        icon: icon
                    }).addTo(map);

                    marker.bindPopup(`
                        <div style="font-size: 13px;">
                            <strong>${checkpoint.name}</strong><br>
                            ${checkpoint.type === 'pickup' ? '📦 Pickup' : '🚚 Delivery'}<br>
                            <small>Status: ${checkpoint.status}</small>
                        </div>
                    `);

                    checkpointMarkers.push(marker);
                });

                // Draw route
                const pathCoordinates = checkpoints.map(cp => [cp.lat, cp.lng]);
                routePath = L.polyline(pathCoordinates, {
                    color: '#3B82F6',
                    weight: 4,
                    opacity: 0.7
                }).addTo(map);

                // Fit bounds
                const bounds = L.latLngBounds(pathCoordinates);
                setTimeout(() => {
                    try {
                        map.invalidateSize(true);
                        map.fitBounds(bounds, {
                            padding: [50, 50],
                            maxZoom: 15
                        });
                    } catch (e) {
                        console.log('Bounds fit error:', e);
                    }
                }, 300);

                // Start tracking
                updateDriverLocation();
                setInterval(updateDriverLocation, 10000);

            } catch (error) {
                console.error('Map error:', error);
                throw error;
            }
        }

        // Get marker color based on checkpoint
        function getMarkerColor(checkpoint) {
            if (checkpoint.type === 'pickup') {
                return '#EF4444'; // Red
            } else if (checkpoint.status === 'completed') {
                return '#10B981'; // Green
            } else if (checkpoint.status === 'in_progress') {
                return '#3B82F6'; // Blue
            } else {
                return '#F59E0B'; // Yellow
            }
        }

        function toggleDetails(deliveryId) {
            const detailDiv = document.getElementById(`details-${deliveryId}`);
            const toggleText = document.getElementById(`toggle-text-${deliveryId}`);
            const toggleIcon = document.getElementById(`toggle-icon-${deliveryId}`);

            if (detailDiv.classList.contains('hidden')) {
                detailDiv.classList.remove('hidden');
                toggleText.textContent = 'Tutup Detail';
                toggleIcon.style.transform = 'rotate(180deg)';
            } else {
                detailDiv.classList.add('hidden');
                toggleText.textContent = 'Lihat Detail';
                toggleIcon.style.transform = 'rotate(0deg)';
            }
        }

        // Update driver location
        async function updateDriverLocation() {
            try {
                const response = await fetch(`/admin/deliveries/${deliveryId}/location`);
                const data = await response.json();

                if (data.latitude && data.longitude) {
                    const position = [parseFloat(data.latitude), parseFloat(data.longitude)];

                    // Create/update driver marker
                    if (!driverMarker) {
                        const driverIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div style="
                                    background-color: #3B82F6;
                                    width: 32px;
                                    height: 32px;
                                    border-radius: 50%;
                                    border: 3px solid white;
                                    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                ">
                                    <span style="font-size: 16px;">🚛</span>
                                </div>`,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16],
                            popupAnchor: [0, -20]
                        });

                        driverMarker = L.marker(position, {
                            icon: driverIcon
                        }).addTo(map);

                        driverMarker.bindPopup(`
                                <div style="font-family: sans-serif;">
                                    <strong>🚛 Driver Location</strong><br>
                                    <span style="font-size: 12px; color: #666;">Real-time position</span>
                                </div>
                            `);
                    } else {
                        driverMarker.setLatLng(position);
                    }

                    // Update UI
                    document.getElementById('lastUpdate').textContent = `Update: ${data.last_update}`;
                    document.getElementById('currentLocation').textContent = data.current_checkpoint ||
                        'Dalam perjalanan';
                }

                // Update GPS history trail
                updateGpsHistory();

            } catch (error) {
                console.error('Error updating driver location:', error);
            }
        }

        // Update GPS history trail
        async function updateGpsHistory() {
            try {
                const response = await fetch(`/admin/deliveries/${deliveryId}/gps-history`);
                const history = await response.json();

                if (history.length > 0 && history[0].speed !== null) {
                    document.getElementById('speedValue').textContent = Math.round(history[0].speed);
                }

                // Draw GPS trail
                const trailPath = history.map(point => [point.lat, point.lng]);

                if (gpsTrail) {
                    map.removeLayer(gpsTrail);
                }

                if (trailPath.length > 0) {
                    gpsTrail = L.polyline(trailPath, {
                        color: '#8B5CF6',
                        weight: 3,
                        opacity: 0.6,
                        dashArray: '10, 5'
                    }).addTo(map);
                }

            } catch (error) {
                console.error('Error updating GPS history:', error);
            }
        }

        // Auto-refresh page data
        setInterval(async () => {
            try {
                const response = await fetch(window.location.href);
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Update progress
                const progressBar = doc.getElementById('progressBar');
                const progressText = doc.getElementById('progressText');
                if (progressBar && progressText) {
                    document.getElementById('progressBar').style.width = progressBar.style.width;
                    document.getElementById('progressText').textContent = progressText.textContent;
                }

                // Update completed checkpoints
                const completed = doc.getElementById('completedCheckpoints');
                if (completed) {
                    document.getElementById('completedCheckpoints').textContent = completed.textContent;
                }

            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        }, 30000); // Refresh every 30 seconds

        // Initialize map when page loads
        function startMapInit() {
            const mapElement = document.getElementById('map');
            if (!mapElement) {
                setTimeout(startMapInit, 100);
                return;
            }
            
            try {
                initMap();
            } catch (error) {
                console.error('Map initialization error:', error);
                setTimeout(startMapInit, 500);
            }
        }

        // Simple initialization on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(startMapInit, 500);
            });
        } else {
            setTimeout(startMapInit, 500);
        }

        window.addEventListener('load', () => {
            setTimeout(() => {
                if (map && typeof map.invalidateSize === 'function') {
                    map.invalidateSize(true);
                }
            }, 800);
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (map) {
                    map.invalidateSize(true);
                }
            }, 250);
        });

        // Image Viewer Functions
        function viewFullImage(url) {
            document.getElementById('fullImage').src = url;
            document.getElementById('imageViewerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageViewer() {
            document.getElementById('imageViewerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Signature Viewer Functions
        function viewFullSignature(url) {
            document.getElementById('fullSignature').src = url;
            document.getElementById('signatureViewerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSignatureViewer() {
            document.getElementById('signatureViewerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Close modal when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageViewer();
                closeSignatureViewer();
            }
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const closeCreateModal = document.getElementById('createDeliveryModal');
            const imageModal = document.getElementById('imageViewerModal');
            const signatureModal = document.getElementById('signatureViewerModal');

            if (e.target === closeCreateModal) {
                closeCreateDeliveryModal();
            }
            if (e.target === imageModal) {
                closeImageViewer();
            }
            if (e.target === signatureModal) {
                closeSignatureViewer();
            }
        });
    </script>
@endpush
