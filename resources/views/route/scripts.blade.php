<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-800">Live Tracking - {{ $delivery->delivery_code }}</h2>
        <p class="text-sm text-gray-500 mt-1">
            Real-time monitoring pengiriman
        </p>
    </x-slot>

    <div class="p-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Map Section (Left - 2/3) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Map Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                        <div class="flex items-center justify-between text-white">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg">Live GPS Tracking</h3>
                                    <p class="text-sm text-blue-100" id="lastUpdate">Updating...</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1 bg-white/20 px-3 py-1 rounded-full">
                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-medium">Live</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OpenStreetMap -->
                    <div id="map" style="height: 600px;" class="w-full"></div>

                    <!-- Map Legend -->
                    <div class="p-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-red-500 rounded-full border-2 border-white shadow"></div>
                                <span>Pickup</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-500 rounded-full border-2 border-white shadow"></div>
                                <span>Delivery (Selesai)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-yellow-500 rounded-full border-2 border-white shadow"></div>
                                <span>Delivery (Pending)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow"></div>
                                <span>Driver (Current)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Panel (Right - 1/3) -->
            <div class="space-y-6">

                <!-- Status Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                        Status Delivery
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-gray-500 font-medium">Status</label>
                            <div class="mt-1">{!! $delivery->status_badge !!}</div>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Progress</label>
                            <div class="mt-1">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-3">
                                        <div class="bg-blue-600 h-3 rounded-full transition-all" id="progressBar"
                                            style="width: {{ $delivery->progress_percentage }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold"
                                        id="progressText">{{ $delivery->progress_percentage }}%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span
                                        id="completedCheckpoints">{{ $delivery->checkpoints->where('status', 'completed')->count() }}</span>
                                    /
                                    <span id="totalCheckpoints">{{ $delivery->checkpoints->count() }}</span> checkpoint
                                    selesai
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Lokasi Saat Ini</label>
                            <p class="text-sm font-medium mt-1" id="currentLocation">
                                {{ $delivery->getCurrentCheckpoint()?->location->name ?? 'Belum dimulai' }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Durasi Perjalanan</label>
                            <p class="text-sm font-medium mt-1" id="journeyDuration">
                                {{ $delivery->formatted_duration }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Kecepatan</label>
                            <p class="text-sm font-medium mt-1" id="currentSpeed">
                                <span id="speedValue">-</span> km/h
                            </p>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Baterai Driver</label>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" id="batteryBar" style="width: 0%"></div>
                                </div>
                                <span class="text-sm font-medium" id="batteryText">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        Driver
                    </h3>

                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $delivery->driver->name }}</p>
                            <p class="text-sm text-gray-500">{{ $delivery->driver->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Route Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                        Informasi Rute
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="text-xs text-gray-500 font-medium">Nama Rute</label>
                            <p class="font-medium mt-1">{{ $delivery->route->route_name }}</p>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Jarak Total</label>
                            <p class="font-medium mt-1">{{ $delivery->route->distance_km }} km</p>
                        </div>

                        <div>
                            <label class="text-xs text-gray-500 font-medium">Estimasi Waktu</label>
                            <p class="font-medium mt-1">{{ $delivery->route->estimated_time }} menit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkpoint Timeline -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd" />
                </svg>
                Timeline Checkpoint
            </h3>

            <div class="space-y-4">
                @foreach ($delivery->checkpoints as $checkpoint)
                    <div
                        class="flex items-start gap-4 p-4 rounded-lg {{ $checkpoint->status === 'completed' ? 'bg-green-50 border border-green-200' : ($checkpoint->status === 'in_progress' ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 border border-gray-200') }}">
                        <!-- Icon -->
                        <div
                            class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                            {{ $checkpoint->status === 'completed' ? 'bg-green-500' : ($checkpoint->status === 'in_progress' ? 'bg-blue-500' : 'bg-gray-300') }}">
                            @if ($checkpoint->status === 'completed')
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            @elseif($checkpoint->status === 'in_progress')
                                <svg class="w-5 h-5 text-white animate-spin" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            @else
                                <span class="text-white font-semibold">{{ $checkpoint->sequence + 1 }}</span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">{{ $checkpoint->type_icon }}</span>
                                <h4 class="font-semibold text-gray-900">{{ $checkpoint->location->name }}</h4>
                                <span
                                    class="text-xs px-2 py-1 rounded-full {{ $checkpoint->type === 'pickup' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $checkpoint->type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mb-2">{{ $checkpoint->location->address }}</p>

                            @if ($checkpoint->status === 'completed')
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                    <div>
                                        <span class="text-gray-500">Tiba:</span>
                                        <span
                                            class="font-medium">{{ $checkpoint->arrived_at?->format('H:i') ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Mulai:</span>
                                        <span
                                            class="font-medium">{{ $checkpoint->load_start_at?->format('H:i') ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Selesai:</span>
                                        <span
                                            class="font-medium">{{ $checkpoint->load_end_at?->format('H:i') ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Durasi:</span>
                                        <span class="font-medium">{{ $checkpoint->formatted_load_duration }}</span>
                                    </div>
                                </div>
                            @elseif($checkpoint->status === 'in_progress')
                                <p class="text-sm text-blue-600 font-medium">🚛 Sedang proses...</p>
                            @else
                                <p class="text-sm text-gray-400">⏳ Menunggu</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpush

    @push('scripts')
        <!-- Leaflet JS -->
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
                // Center on first checkpoint (pickup)
                const center = [checkpoints[0].lat, checkpoints[0].lng];

                // Create map with better controls
                map = L.map('map', {
                    center: center,
                    zoom: 12,
                    zoomControl: true,
                    scrollWheelZoom: true
                });

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);

                // Add checkpoint markers dengan numbering
                checkpoints.forEach((checkpoint, index) => {
                    const color = getMarkerColor(checkpoint);
                    
                    // Create custom icon dengan numbering
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
                            font-family: sans-serif;
                        ">${index + 1}</div>`,
                        iconSize: [35, 35],
                        iconAnchor: [17, 17],
                        popupAnchor: [0, -20]
                    });

                    const marker = L.marker([checkpoint.lat, checkpoint.lng], {
                        icon: icon
                    }).addTo(map);

                    // Popup info
                    marker.bindPopup(`
                        <div style="font-family: sans-serif;">
                            <strong style="font-size: 14px;">${checkpoint.name}</strong><br>
                            <span style="font-size: 12px; color: #666;">
                                ${checkpoint.type === 'pickup' ? '📦 Pickup' : '🚚 Delivery'}
                            </span><br>
                            <span style="font-size: 11px; color: #999;">
                                Status: ${checkpoint.status}
                            </span>
                        </div>
                    `);

                    checkpointMarkers.push(marker);
                });

                // Draw route path
                const pathCoordinates = checkpoints.map(cp => [cp.lat, cp.lng]);
                routePath = L.polyline(pathCoordinates, {
                    color: '#3B82F6',
                    weight: 4,
                    opacity: 0.7
                }).addTo(map);

                // Fit map to show all checkpoints dengan padding
                const bounds = L.latLngBounds(pathCoordinates);
                map.fitBounds(bounds, {
                    padding: [50, 50]
                });

                // Start live tracking
                updateDriverLocation();
                setInterval(updateDriverLocation, 10000); // Update every 10 seconds
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
            document.addEventListener('DOMContentLoaded', function() {
                initMap();
            });
        </script>
    @endpush
</x-app-layout>