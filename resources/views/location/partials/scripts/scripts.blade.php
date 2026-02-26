@push('scripts')
<script>
    // ========================================
    // LOCATIONS DATA FROM BACKEND
    // ========================================
    const locations = @json($location->items());
    
    console.log('✅ Location scripts loaded');
    console.log('Total locations:', locations.length);

    // ========================================
    // DETAIL MODAL FUNCTIONS
    // ========================================
    function showDetailModal(locationId) {
        console.log('Opening detail modal for location:', locationId);
        
        const selectedLocation = locations.find(loc => loc.id === locationId);
        
        if (!selectedLocation) {
            console.error('Location not found:', locationId);
            alert('Lokasi tidak ditemukan!');
            return;
        }

        console.log('Selected location:', selectedLocation);

        // Build detail content
        const detailHTML = `
            <div class="space-y-4">
                <!-- Location Name -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm font-medium text-gray-500 block mb-1">Nama Lokasi</label>
                    <p class="text-lg font-semibold text-gray-900">${selectedLocation.name}</p>
                </div>

                <!-- Type Badge -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm font-medium text-gray-500 block mb-1">Tipe</label>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full ${selectedLocation.type === 'pickup' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                        ${selectedLocation.type === 'pickup' ? '📦 Pickup' : '🚚 Delivery'}
                    </span>
                </div>

                <!-- Address -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm font-medium text-gray-500 block mb-1">Alamat Lengkap</label>
                    <p class="text-gray-900">${selectedLocation.address}</p>
                    ${selectedLocation.postal_code ? `
                        <p class="text-sm text-gray-500 mt-2">
                            📮 Kode Pos: <span class="font-medium">${selectedLocation.postal_code}</span>
                        </p>
                    ` : ''}
                </div>

                <!-- City & Province -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500 block mb-1">Kota</label>
                        <p class="text-gray-900 font-medium">${selectedLocation.city}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500 block mb-1">Provinsi</label>
                        <p class="text-gray-900 font-medium">${selectedLocation.province}</p>
                    </div>
                </div>

                <!-- Coordinates -->
                <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <label class="text-sm font-medium text-blue-700 block mb-2">📍 Koordinat GPS</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-blue-600 mb-1">Latitude</p>
                            <p class="text-blue-900 font-mono font-medium">${selectedLocation.latitude}</p>
                        </div>
                        <div>
                            <p class="text-xs text-blue-600 mb-1">Longitude</p>
                            <p class="text-blue-900 font-mono font-medium">${selectedLocation.longitude}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button onclick="copyCoordinates('${selectedLocation.latitude}', '${selectedLocation.longitude}')" 
                            class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition">
                            📋 Copy Coordinates
                        </button>
                        <button onclick="openInMaps('${selectedLocation.latitude}', '${selectedLocation.longitude}')" 
                            class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition">
                            🗺️ Open in Google Maps
                        </button>
                    </div>
                </div>

                <!-- Contact Info -->
                ${selectedLocation.contact_person || selectedLocation.phone_number ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="text-sm font-medium text-gray-500 block mb-2">Kontak</label>
                        ${selectedLocation.contact_person ? `
                            <p class="text-gray-900 mb-1">
                                👤 <span class="font-medium">${selectedLocation.contact_person}</span>
                            </p>
                        ` : ''}
                        ${selectedLocation.phone_number ? `
                            <p class="text-gray-900">
                                📞 <span class="font-medium">${selectedLocation.phone_number}</span>
                            </p>
                        ` : ''}
                    </div>
                ` : ''}

                <!-- Status -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="text-sm font-medium text-gray-500 block mb-2">Status</label>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full ${selectedLocation.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${selectedLocation.status === 'active' ? '✅ Aktif' : '❌ Nonaktif'}
                    </span>
                </div>

                <!-- Notes if any -->
                ${selectedLocation.notes ? `
                    <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                        <label class="text-sm font-medium text-yellow-800 block mb-2">📝 Catatan</label>
                        <p class="text-yellow-900 text-sm">${selectedLocation.notes}</p>
                    </div>
                ` : ''}
            </div>
        `;

        // Insert content and show modal
        document.getElementById('detailContent').innerHTML = detailHTML;
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================
    
    function copyCoordinates(lat, lng) {
        const coords = `${lat}, ${lng}`;
        navigator.clipboard.writeText(coords).then(() => {
            alert('✅ Koordinat berhasil dicopy: ' + coords);
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('❌ Gagal copy koordinat');
        });
    }

    function openInMaps(lat, lng) {
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, '_blank');
    }

    // ========================================
    // CREATE MODAL FUNCTIONS
    // ========================================
    
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('createLocationForm').reset();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    // ========================================
    // EDIT MODAL FUNCTIONS
    // ========================================
    
    function openEditModal(locationId) {
        const selectedLocation = locations.find(loc => loc.id === locationId);
        
        if (!selectedLocation) {
            console.error('Location not found:', locationId);
            alert('Lokasi tidak ditemukan!');
            return;
        }

        // Populate form fields
        document.getElementById('edit_name').value = selectedLocation.name;
        document.getElementById('edit_type').value = selectedLocation.type;
        document.getElementById('edit_address').value = selectedLocation.address;
        document.getElementById('edit_city').value = selectedLocation.city;
        document.getElementById('edit_province').value = selectedLocation.province;
        document.getElementById('edit_postal_code').value = selectedLocation.postal_code || '';
        document.getElementById('edit_latitude').value = selectedLocation.latitude;
        document.getElementById('edit_longitude').value = selectedLocation.longitude;
        document.getElementById('edit_status').value = selectedLocation.status;
        
        // if (document.getElementById('edit_notes')) {
        //     document.getElementById('edit_notes').value = selectedLocation.notes || '';
        // }

        // Set form action
        document.getElementById('editLocationForm').action = `/admin/locations/update/${locationId}`;
        
        // Show modal
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // ========================================
    // DELETE MODAL FUNCTIONS
    // ========================================
    
    let deleteLocationId = null;

    function confirmDelete(locationId) {
        const selectedLocation = locations.find(loc => loc.id === locationId);
        
        if (!selectedLocation) {
            console.error('Location not found:', locationId);
            alert('Lokasi tidak ditemukan!');
            return;
        }

        // Store location ID
        deleteLocationId = locationId;

        // Populate delete modal info
        const deleteInfo = `
            <div class="text-center">
                <div class="mb-4">
                    <p class="text-lg font-semibold text-gray-900">${selectedLocation.name}</p>
                    <span class="text-sm px-2 py-1 rounded-full ${selectedLocation.type === 'pickup' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                        ${selectedLocation.type === 'pickup' ? 'Pickup' : 'Delivery'}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mb-2">${selectedLocation.address}</p>
                <p class="text-sm text-gray-500">${selectedLocation.city}, ${selectedLocation.province}</p>
            </div>
        `;

        document.getElementById('deleteLocationInfo').innerHTML = deleteInfo;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteLocationId = null;
    }

    function submitDelete() {
        if (!deleteLocationId) {
            alert('Terjadi kesalahan. Silakan coba lagi.');
            return;
        }

        const form = document.getElementById('deleteForm');
        form.action = `/admin/locations/destroy/${deleteLocationId}`;
        form.submit();
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================
    
    // Close modals when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.id === 'createModal') closeCreateModal();
        if (event.target.id === 'editModal') closeEditModal();
        if (event.target.id === 'deleteModal') closeDeleteModal();
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

    console.log('✅ All location scripts initialized successfully');
</script>
@endpush