@push('scripts')
    <script>
        // ========================================
        // DATA FROM BACKEND
        // ========================================
        const routes = @json($route->items());
        const locations = {!! json_encode(
            $locations->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'name' => $loc->name,
                    'city' => $loc->city,
                    'latitude' => $loc->latitude,
                    'longitude' => $loc->longitude,
                ];
            }),
        ) !!};

        let deliveryLocationCount = 1;
        let isRouteNameAuto = true;

        console.log('Route scripts loaded');
        console.log('Routes:', routes.length);
        console.log('Locations:', locations.length);

        // ========================================
        // FUNCTION PERHITUNGAN RUTE NO-TOL
        // ========================================
        function getDetourFactor(straightKm) {
            if (straightKm  < 3)    return 1.6;
            if (straightKm  < 8)    return 1.5;
            if (straightKm  < 20)   return 1.4;
            if (straightKm  < 50)   return 1.35;
            return 1.3;
        }

        /**
         * Kecepatan rata-rata (km/h)
         */
        function getAvgSpeed(actualKm) {
            if (actualKm    < 5)    return 20;
            if (actualKm    < 15)   return 28;
            if (actualKm    <35)    return 35;
            if (actualKm    < 70)   return 42;
            return 48;
        }

        /**
         * Konversi derajat ke radian
         */
        function toRad(deg) {
            return deg * (Math.PI / 180);
        }

        /**
         * Calculate distance using Haversine formula
         * Returns distance in kilometers
         */
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Earth radius in km
            const dLat = toRad(lat2 - lat1);
            const dLon = toRad(lon2 - lon1);

            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);

            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        /**
         * Estimate travel time based on distance
         * More accurate calculation with distance-based speed tiers
         * 
         * @param {number} distanceKm - Distance in kilometers
         * @returns {number} Time in minutes (excluding loading/unloading)
         */
        function estimateTravelTime(actualKm) {
            const speed = getAvgSpeed(actualKm);

            // Waktu berkendara (jam -> menit)
            let minutes = (actualKm / speed) * 60;

            // Estimasi berhenti lampu merah
            const stopCount = Math.floor(actualKm / 1.5);
            const stopTime = actualKm < 15 ? stopCount * 2.5 : stopCount * 1.5;

            return Math.round(minutes + stopTime);
        }

        /**
         * Calculate multi-point route with accurate time estimation
         * 
         * @param {Object} pickup - {lat, lng, name}
         * @param {Array} deliveryPoints - [{lat, lng, name}, ...]
         * @returns {Object} {totalDistance, totalTime, segments, breakdown}
         */
        function calculateMultiPointRoute(pickup, deliveryPoints) {
            if (!pickup || !deliveryPoints || deliveryPoints.length === 0) {
                return {
                    totalDistance: 0,
                    totalTime: 0,
                    segments: [],
                    breakdown: {}
                };
            }

            // Penambahan 20 menit untuk per titik (pickup & delivery)
            const LOADING_TIME_PER_CHECKPOINT = 20;

            let segments                = [];
            let totalStraightKm         = 0;
            let totalActualKm           = 0;
            let totalDrivingTime        = 0;
            let prevPoint               = pickup;

            // Calculate each segment
            deliveryPoints.forEach((point, index) => {
                const straightKm = calculateDistance(
                    prevPoint.lat,
                    prevPoint.lng,
                    point.lat,
                    point.lng
                );

                const detour    = getDetourFactor(straightKm);
                const actualKm  = straightKm * detour;
                const driveTime = estimateTravelTime(actualKm);

                segments.push({
                    from:       prevPoint.name,
                    to:         point.name,
                    straightKm: Math.round(straightKm * 10) / 10,
                    actualKm:   Math.round(actualKm * 10) / 10,
                    driveTime,
                    detour:     detour.toFixed(2),
                    sequence: index + 1
                });

                totalStraightKm     += straightKm;
                totalActualKm       += actualKm;
                totalDrivingTime    += driveTime;
                prevPoint = point;
            });

            // Jumlah checkpoint = 1 pickup + semua titik delivery
            const totalCheckpoints  = 1 + deliveryPoints.length;
            const totalLoadingTime  = totalCheckpoints * LOADING_TIME_PER_CHECKPOINT;

            // Total time = driving + loading/unloading
            const totalTime = Math.round(totalDrivingTime + totalLoadingTime);

            return {
                totalDistance: Math.round(totalActualKm * 100) / 100,
                straightDistance: Math.round(totalStraightKm),
                totalTime,
                segments,
                breakdown: {
                    drivingTime: Math.round(totalDrivingTime),
                    loadingTime: totalLoadingTime,
                    checkpoints: totalCheckpoints,
                    perCheckpoint: LOADING_TIME_PER_CHECKPOINT
                }
            };
        }

        // ========================================
        // CREATE MODAL FUNCTIONS
        // ========================================
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            document.getElementById('createRouteForm').reset();

            const container = document.getElementById('deliveryLocationsContainer');
            const items = container.querySelectorAll('.delivery-location-item');
            items.forEach((item, idx) => {
                if (idx > 0) item.remove();
            });

            deliveryLocationCount = 1;
            clearRouteCalculation();
            updateRemoveButtons();
            document.getElementById('pickup_info').textContent = '';
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        // ========================================
        // MULTI-DELIVERY FUNCTIONS
        // ========================================
        function addDeliveryLocation() {
            const container = document.getElementById('deliveryLocationsContainer');
            const index = deliveryLocationCount;

            let optionsHTML = '<option value="">-- Pilih Lokasi Delivery --</option>';
            locations.forEach(location => {
                optionsHTML += `<option value="${location.id}" 
                data-lat="${location.latitude}" 
                data-lng="${location.longitude}" 
                data-name="${location.name}">
                ${location.name} (${location.city})
            </option>`;
            });

            container.insertAdjacentHTML('beforeend', `
                <div class="delivery-location-item bg-white p-3 rounded border border-gray-200" data-index="${index}">
                    <div class="flex items-start gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-semibold text-green-700 delivery-number">${index + 1}</span>
                        </div>
                        <div class="flex-1">
                            <select name="delivery_location_ids[]" 
                                class="delivery-location-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" 
                                onchange="calculateTotalRoute()" required>
                                ${optionsHTML}
                            </select>
                            <p class="text-xs text-gray-500 mt-1 delivery-info"></p>
                        </div>
                        <button type="button" onclick="removeDeliveryLocation(${index})" 
                            class="remove-delivery-btn flex-shrink-0 text-red-500 hover:text-red-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `);

            deliveryLocationCount++;
            updateRemoveButtons();
            console.log(`Delivery location ${index + 1} added`);
        }

        function removeDeliveryLocation(index) {
            const item = document.querySelector(`.delivery-location-item[data-index="${index}"]`);
            if (item) {
                item.remove();
                updateDeliveryNumbers();
                updateRemoveButtons();
                calculateTotalRoute();
                console.log(`Delivery location removed`);
            }
        }

        function updateDeliveryNumbers() {
            const items = document.querySelectorAll('.delivery-location-item');
            items.forEach((item, idx) => {
                const numberSpan = item.querySelector('.delivery-number');
                if (numberSpan) {
                    numberSpan.textContent = idx + 1;
                }
            });
        }

        function updateRemoveButtons() {
            const items = document.querySelectorAll('.delivery-location-item');
            const removeButtons = document.querySelectorAll('.remove-delivery-btn');

            if (items.length > 1) {
                removeButtons.forEach(btn => btn.classList.remove('hidden'));
            } else {
                removeButtons.forEach(btn => btn.classList.add('hidden'));
            }
        }

        // ========================================
        // ROUTE CALCULATION (CREATE)
        // ========================================
        function calculateTotalRoute() {
            const pickupSelect = document.getElementById('pickup_location_id');
            const deliverySelects = document.querySelectorAll('.delivery-location-select');

            if (!pickupSelect || !pickupSelect.value) {
                console.log('⚠️ Pickup location not selected');
                return;
            }

            const pickupOption = pickupSelect.options[pickupSelect.selectedIndex];
            const pickup = {
                lat: parseFloat(pickupOption.dataset.lat),
                lng: parseFloat(pickupOption.dataset.lng),
                name: pickupOption.dataset.name
            };

            document.getElementById('pickup_info').textContent = `📍 ${pickup.name}`;

            // Collect delivery points
            const deliveryPoints = [];
            deliverySelects.forEach((select) => {
                if (select.value) {
                    const option = select.options[select.selectedIndex];
                    const lat = parseFloat(option.dataset.lat);
                    const lng = parseFloat(option.dataset.lng);
                    const name = option.dataset.name;

                    if (lat && lng) {
                        deliveryPoints.push({
                            lat,
                            lng,
                            name
                        });
                        const infoElement = select.parentElement.querySelector('.delivery-info');
                        if (infoElement) {
                            infoElement.textContent = `📍 ${name}`;
                        }
                    }
                }
            });

            if (deliveryPoints.length === 0) {
                console.log('⚠️ No delivery locations selected');
                clearRouteCalculation();
                return;
            }

            // Validation
            const selectedIds = Array.from(deliverySelects).map(s => s.value).filter(v => v);
            const uniqueIds = new Set(selectedIds);

            if (selectedIds.length !== uniqueIds.size) {
                alert('⚠️ Tidak boleh memilih lokasi delivery yang sama lebih dari sekali!');
                clearRouteCalculation();
                return;
            }

            if (selectedIds.includes(pickupSelect.value)) {
                alert('⚠️ Lokasi delivery tidak boleh sama dengan lokasi pickup!');
                clearRouteCalculation();
                return;
            }

            // Calculate route
            const routeData = calculateMultiPointRoute(pickup, deliveryPoints);

            // Update form fields
            document.getElementById('distance_km').value = routeData.totalDistance;
            document.getElementById('estimated_time').value = routeData.totalTime;

            // Auto-generate route name (pickup -> LAST delivery)
            const routeNameInput = document.getElementById('route_name');

            if (isRouteNameAuto && deliveryPoints.length > 0) {
                const lastDelivery = deliveryPoints[deliveryPoints.length - 1];
                routeNameInput.value = `${pickup.name} → ${lastDelivery.name}`;
            }

            // Build route path
            const routePath = [pickup.name, ...deliveryPoints.map(p => p.name)];

            // Show summary
            showRouteSummary(routePath, routeData);
            console.log('Route calculated:', routeData);
        }

        function showRouteSummary(routePath, routeData) {
            const summaryDiv = document.getElementById('routeSummary');
            const summaryContent = document.getElementById('routeSummaryContent');

            let summaryHTML = '<div class="space-y-3">';

            // Route visualization
            summaryHTML += '<div class="flex items-start gap-2">';
            summaryHTML +=
                '<svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">';
            summaryHTML +=
                '<path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>';
            summaryHTML += '</svg><div class="space-y-1 flex-1">';

            routePath.forEach((location, idx) => {
                const icon = idx === 0 ? '🔴' : (idx === routePath.length - 1 ? '🏁' : '🟢');
                const label = idx === 0 ? 'Pickup' : `Delivery ${idx}`;
                summaryHTML +=
                    `<div class="flex items-center gap-1">${icon} <span class="font-medium">${location}</span> <span class="text-xs text-gray-400">(${label})</span></div>`;

                if (idx < routePath.length - 1) {
                    const segment = routeData.segments[idx];
                    if (segment) {
                        summaryHTML += `<div class="ml-5 text-xs text-gray-500 bg-gray-50 rounded p-1 mt-0.5">
                            ↓ Garis lurus: ${segment.straightKm} km
                            → Jarak jalan non-tol: <strong>${segment.actualKm} km</strong>
                            (×${segment.detour}) — ~${segment.driveTime} menit berkendara
                        </div>`;
                    }
                }
            });

            summaryHTML += '</div></div>';

            // Summary stats with breakdown
            summaryHTML += `
                <div class="mt-3 pt-3 border-t border-gray-300 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm"><strong>🥖 Total Jarak:</strong></span>
                        <span class="text-sm font-semibold">${routeData.totalDistance} km</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm"><strong>🕓 Waktu Berkendara:</strong></span>
                        <span class="text-sm text-gray-600">${routeData.breakdown.drivingTime} menit</span></div>
                    <div class="flex justify-between">
                        <span class="text-sm"><strong>📦 Waktu Loading/Unloading:</strong></span>
                        <span class="text-sm text-gray-600">${routeData.breakdown.loadingTime} menit (${routeData.breakdown.checkpoints} × 20 menit)</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm"><strong>🕓 Total Estimasi:</strong></span>
                        <span class="text-base font-bold text-blue-600">
                            ${routeData.totalTime} menit (≈ ${(routeData.totalTime / 60).toFixed(1)} jam)
                        </span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-200 bg-yellow-50 rounded p-2">
                        <p class="text-xs text-yellow-700">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Estimasi termasuk waktu berkendara (dengan faktor traffic) + 20 menit per checkpoint untuk loading/unloading
                        </p>
                    </div>
                </div>
            </div>`;

            summaryContent.innerHTML = summaryHTML;
            summaryDiv.classList.remove('hidden');
        }

        function clearRouteCalculation() {
            const distanceInput = document.getElementById('distance_km');
            const timeInput = document.getElementById('estimated_time');
            const summaryDiv = document.getElementById('routeSummary');

            if (summaryDiv) summaryDiv.classList.add('hidden');
            if (distanceInput) distanceInput.value = '';
            if (timeInput) timeInput.value = '';
        }

        // ========================================
        // EDIT MODAL FUNCTIONS
        // ========================================
        let editDeliveryLocationCount = 0;

        function openEditModal(routeId) {
            const selectedRoute = routes.find(r => r.id === routeId);
            if (!selectedRoute) {
                console.error('Route not found:', routeId);
                return;
            }

            document.getElementById('editRouteForm').reset();
            document.getElementById('edit_route_name').value = selectedRoute.route_name;
            document.getElementById('edit_pickup_location_id').value = selectedRoute.pickup_location_id;
            document.getElementById('edit_distance_km').value = selectedRoute.distance_km;
            document.getElementById('edit_estimated_time').value = selectedRoute.estimated_time;
            document.getElementById('edit_status').value = selectedRoute.status;

            const pickupOption = document.querySelector(
                `#edit_pickup_location_id option[value="${selectedRoute.pickup_location_id}"]`);
            if (pickupOption) {
                document.getElementById('edit_pickup_info').textContent = `📍 ${pickupOption.dataset.name}`;
            }

            const container = document.getElementById('editDeliveryLocationsContainer');
            container.innerHTML = '';
            editDeliveryLocationCount = 0;

            if (selectedRoute.delivery_locations && selectedRoute.delivery_locations.length > 0) {
                selectedRoute.delivery_locations.forEach((delivery, index) => {
                    addEditDeliveryLocation(delivery.id, index);
                });
            } else if (selectedRoute.delivery_location_id) {
                addEditDeliveryLocation(selectedRoute.delivery_location_id, 0);
            } else {
                addEditDeliveryLocation(null, 0);
            }

            document.getElementById('editRouteForm').action = `/admin/routes/update/${routeId}`;
            document.getElementById('editModal').classList.remove('hidden');
            updateEditRemoveButtons();
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            clearEditRouteCalculation();
        }

        function addEditDeliveryLocation(selectedId = null, index = null) {
            const container = document.getElementById('editDeliveryLocationsContainer');
            const currentIndex = index !== null ? index : editDeliveryLocationCount;

            let optionsHTML = '<option value="">-- Pilih Lokasi Delivery --</option>';
            locations.forEach(location => {
                const selected = selectedId && location.id == selectedId ? 'selected' : '';
                optionsHTML += `<option value="${location.id}" data-lat="${location.latitude}" data-lng="${location.longitude}" data-name="${location.name}" ${selected}>
                    ${location.name} (${location.city})
                </option>`;
            });

            const newDeliveryHTML = `
                <div class="edit-delivery-location-item bg-white p-3 rounded border border-gray-200" data-index="${currentIndex}">
                    <div class="flex items-start gap-2">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-semibold text-green-700 edit-delivery-number">${currentIndex + 1}</span>
                        </div>
                        <div class="flex-1">
                            <select name="delivery_location_ids[]" 
                                class="edit-delivery-location-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500" 
                                onchange="calculateEditTotalRoute()" required>
                                ${optionsHTML}
                            </select>
                            <p class="text-xs text-gray-500 mt-1 edit-delivery-info"></p>
                        </div>
                        <button type="button" onclick="removeEditDeliveryLocation(${currentIndex})" 
                            class="remove-edit-delivery-btn flex-shrink-0 text-red-500 hover:text-red-700 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', newDeliveryHTML);
            editDeliveryLocationCount++;
            updateEditRemoveButtons();

            if (selectedId) {
                const select = container.querySelector(`.edit-delivery-location-item[data-index="${currentIndex}"] select`);
                const option = select.options[select.selectedIndex];
                if (option && option.dataset.name) {
                    const infoElement = select.parentElement.querySelector('.edit-delivery-info');
                    if (infoElement) {
                        infoElement.textContent = `📍 ${option.dataset.name}`;
                    }
                }
            }
        }

        function removeEditDeliveryLocation(index) {
            const item = document.querySelector(`.edit-delivery-location-item[data-index="${index}"]`);
            if (item) {
                item.remove();
                updateEditDeliveryNumbers();
                updateEditRemoveButtons();
                calculateEditTotalRoute();
            }
        }

        function updateEditDeliveryNumbers() {
            const items = document.querySelectorAll('.edit-delivery-location-item');
            items.forEach((item, idx) => {
                const numberSpan = item.querySelector('.edit-delivery-number');
                if (numberSpan) {
                    numberSpan.textContent = idx + 1;
                }
                item.setAttribute('data-index', idx);
            });
        }

        function updateEditRemoveButtons() {
            const items = document.querySelectorAll('.edit-delivery-location-item');
            const removeButtons = document.querySelectorAll('.remove-edit-delivery-btn');

            if (items.length > 1) {
                removeButtons.forEach(btn => btn.classList.remove('hidden'));
            } else {
                removeButtons.forEach(btn => btn.classList.add('hidden'));
            }
        }

        function calculateEditTotalRoute() {
            const pickupSelect = document.getElementById('edit_pickup_location_id');
            const deliverySelects = document.querySelectorAll('.edit-delivery-location-select');

            if (!pickupSelect || !pickupSelect.value) {
                console.log('⚠️ Edit: Pickup location not selected');
                return;
            }

            const pickupOption = pickupSelect.options[pickupSelect.selectedIndex];
            const pickup = {
                lat: parseFloat(pickupOption.dataset.lat),
                lng: parseFloat(pickupOption.dataset.lng),
                name: pickupOption.dataset.name
            };

            document.getElementById('edit_pickup_info').textContent = `📍 ${pickup.name}`;

            const deliveryPoints = [];
            deliverySelects.forEach((select) => {
                if (select.value) {
                    const option = select.options[select.selectedIndex];
                    const lat = parseFloat(option.dataset.lat);
                    const lng = parseFloat(option.dataset.lng);
                    const name = option.dataset.name;

                    if (lat && lng) {
                        deliveryPoints.push({
                            lat,
                            lng,
                            name
                        });
                        const infoElement = select.parentElement.querySelector('.edit-delivery-info');
                        if (infoElement) {
                            infoElement.textContent = `📍 ${name}`;
                        }
                    }
                }
            });

            if (deliveryPoints.length === 0) {
                console.log('⚠️ No delivery locations selected');
                clearEditRouteCalculation();
                return;
            }

            const selectedIds = Array.from(deliverySelects).map(s => s.value).filter(v => v);
            const uniqueIds = new Set(selectedIds);

            if (selectedIds.length !== uniqueIds.size) {
                alert('⚠️ Tidak boleh memilih lokasi delivery yang sama lebih dari sekali!');
                clearEditRouteCalculation();
                return;
            }

            if (selectedIds.includes(pickupSelect.value)) {
                alert('⚠️ Lokasi delivery tidak boleh sama dengan lokasi pickup!');
                clearEditRouteCalculation();
                return;
            }

            const routeData = calculateMultiPointRoute(pickup, deliveryPoints);

            document.getElementById('edit_distance_km').value = routeData.totalDistance;
            document.getElementById('edit_estimated_time').value = routeData.totalTime;

            const routePath = [pickup.name, ...deliveryPoints.map(p => p.name)];
            showEditRouteSummary(routePath, routeData);

            console.log('✅ Edit route calculated:', routeData);
        }

        function showEditRouteSummary(routePath, routeData) {
            const summaryDiv = document.getElementById('editRouteSummary');
            const summaryContent = document.getElementById('editRouteSummaryContent');

            let summaryHTML = '<div class="space-y-3">';

            summaryHTML += '<div class="flex items-start gap-2">';
            summaryHTML +=
                '<svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">';
            summaryHTML +=
                '<path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>';
            summaryHTML += '</svg><div class="space-y-1 flex-1">';

            routePath.forEach((location, idx) => {
                const icon = idx === 0 ? '🔴' : (idx === routePath.length - 1 ? '🏁' : '🟢');
                const label = idx === 0 ? 'Pickup' : `Delivery ${idx}`;
                summaryHTML +=
                    `<div class="flex items-center gap-1">${icon} <span class="font-medium">${location}</span> <span class="text-xs text-gray-400">(${label})</span></div>`;
                if (idx < routePath.length - 1) {
                    const segment = routeData.segments[idx];
                    if (segment) {
                        summaryHTML +=
                            `<div class="ml-5 text-xs text-gray-500">
                                ⬇ ${segment.straightKm} km → <strong>${segment.actualKm} km non-tol</strong> (~${segment.driveTime} menit)</div>`;
                    }
                }
            });

            summaryHTML += '</div></div>';

            // Summary stats with breakdown
            summaryHTML += `
                <div class="mt-3 pt-3 border-t border-gray-300 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm"><strong>🥖 Total Jarak:</strong></span>
                        <span class="text-sm font-semibold">${routeData.totalDistance} km</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm"><strong>🕓 Waktu Berkendara:</strong></span>
                        <span class="text-sm text-gray-600">${routeData.breakdown.drivingTime} menit</span></div>
                    <div class="flex justify-between">
                        <span class="text-sm"><strong>📦 Waktu Loading/Unloading:</strong></span>
                        <span class="text-sm text-gray-600">${routeData.breakdown.loadingTime} menit (${routeData.breakdown.checkpoints} × 20 menit)</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-sm"><strong>🕓 Total Estimasi:</strong></span>
                        <span class="text-base font-bold text-blue-600">
                            ${routeData.totalTime} menit (≈ ${(routeData.totalTime / 60).toFixed(1)} jam)
                        </span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-200 bg-yellow-50 rounded p-2">
                        <p class="text-xs text-yellow-700">
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Estimasi termasuk waktu berkendara (dengan faktor traffic) + 20 menit per checkpoint untuk loading/unloading
                        </p>
                    </div>
                </div>
            </div>`;

            summaryContent.innerHTML = summaryHTML;
            summaryDiv.classList.remove('hidden');
        }

        function clearEditRouteCalculation() {
            const distanceInput = document.getElementById('edit_distance_km');
            const timeInput = document.getElementById('edit_estimated_time');
            const summaryDiv = document.getElementById('editRouteSummary');

            if (summaryDiv) summaryDiv.classList.add('hidden');
            if (distanceInput) distanceInput.value = '';
            if (timeInput) timeInput.value = '';
        }

        function openModalAssign(routeId) {
            loadDrivers();
            document.getElementById('assignForm').action = `/admin/routes/${routeId}/assign-driver`;
            document.getElementById('assignModal').classList.remove('hidden');
        }

        function closeAssignModal() {
            document.getElementById('assignModal').classList.add('hidden');
            document.getElementById('driver_select').innerHTML = '<option value="">-- Pilih Driver --</option>';
        }

        function confirmUnassign(routeId) {
            if (confirm('Apakah Anda yakin ingin membatalkan penugasan driver dari rute ini?')) {
                const form = document.getElementById('unassignForm');
                form.action = `/admin/routes/${routeId}/unassign-driver`;
                form.submit();
            }
        }

        // ========================================
        // DETAIL MODAL
        // ========================================
        function showDetailModal(routeId) {
            const selectedRoute = routes.find(r => r.id === routeId);
            if (!selectedRoute) {
                console.error('Route not found:', routeId);
                return;
            }

            let deliveryHTML = '';

            if (selectedRoute.delivery_locations && selectedRoute.delivery_locations.length > 0) {
                deliveryHTML = '<div class="bg-gray-50 p-4 rounded-lg">';
                deliveryHTML += '<label class="text-sm font-medium text-gray-500 mb-3 block">Titik Delivery</label>';
                deliveryHTML += '<div class="space-y-3">';

                selectedRoute.delivery_locations.forEach((delivery, index) => {
                    deliveryHTML += `
                        <div class="flex items-start gap-2 bg-white p-3 rounded border border-gray-200">
                            <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-green-700">${index + 1}</span>
                            </div>
                            <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-gray-900 font-medium">${delivery.name}</p>
                                <p class="text-xs text-gray-500">${delivery.type}</p>
                                <p class="text-xs text-gray-600 mt-1">${delivery.address}</p>
                            </div>
                        </div>
                    `;
                });

                deliveryHTML += '</div>';
                deliveryHTML += `<div class="mt-3 pt-3 border-t border-gray-200">
                    <p class="text-sm text-blue-600">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Total ${selectedRoute.delivery_locations.length} titik delivery
                    </p>
                </div>`;
                deliveryHTML += '</div>';
            } else if (selectedRoute.delivery_location) {
                deliveryHTML = `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500">Titik Delivery</label>
                        <p class="text-gray-900 font-medium">${selectedRoute.delivery_location.name}</p>
                        <p class="text-xs text-gray-500">${selectedRoute.delivery_location.type}</p>
                        <p class="text-sm text-gray-600 mt-1">${selectedRoute.delivery_location.address}</p>
                    </div>
                `;
            }

            const content = `
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500">Nama Rute</label>
                        <p class="text-gray-900 font-semibold text-lg">${selectedRoute.route_name}</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500 mb-2">Titik Pickup</label>
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-gray-900 font-medium">${selectedRoute.pickup_location.name}</p>
                                <p class="text-xs text-gray-500">${selectedRoute.pickup_location.type}</p>
                                <p class="text-xs text-gray-600 mt-1">${selectedRoute.pickup_location.address}</p>
                            </div>
                        </div>
                    </div>

                    ${deliveryHTML}

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-500">Jarak</label>
                            <p class="text-gray-900 text-2xl font-bold">${selectedRoute.distance_km} km</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="text-sm font-medium text-gray-500">Estimasi Waktu</label>
                            <p class="text-gray-900 text-2xl font-bold">${selectedRoute.estimated_time} menit</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <p class="mt-2">${getStatusBadge(selectedRoute.status)}</p>
                    </div>

                    ${selectedRoute.driver ? getDriverInfo(selectedRoute) : '<div class="text-gray-500 text-sm">Belum ada driver yang di-assign</div>'}
                </div>
            `;

            document.getElementById('detailContent').innerHTML = content;
            document.getElementById('detailModal').classList.remove('hidden');
        }

        function getStatusBadge(status) {
            const badges = {
                'active': '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Aktif</span>',
                'inactive': '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">Nonaktif</span>',
                'completed': '<span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">Selesai</span>'
            };
            return badges[status] || '';
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        // ========================================
        // EVENT LISTENERS
        // ========================================
        document.addEventListener('click', function(event) {
            if (event.target.id === 'createModal') closeCreateModal();
            if (event.target.id === 'editModal') closeEditModal();
            if (event.target.id === 'assignModal') closeAssignModal();
            if (event.target.id === 'detailModal') closeDetailModal();
        });

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('[role="alert"]').forEach(el => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 3000);

        console.log('✅ All route functions loaded successfully');
    </script>
@endpush
