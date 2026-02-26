@push('scripts')
    <script>
        function onDeliveryRouteChange(select) {
            const option = select.options[select.selectedIndex];
            document.getElementById('deliveryPickupDisplay').value = option.getAttribute('data-pickup') || '';
            document.getElementById('deliveryLocationsDisplay').value = option.getAttribute('data-locations') || '';
        }

        function openCreateDeliveryModal() {
            const modal = document.getElementById('createDeliveryModal');
            const form  = document.getElementById('createDeliveryForm');
            form.reset();
            document.getElementById('deliveryPickupDisplay').value      = '';
            document.getElementById('deliveryLocationsDisplay').value   = '';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCreateDeliveryModal() {
            document.getElementById('createDeliveryModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function confirmCancel(deliveryId) {
            document.getElementById('cancelForm').action = `/admin/deliveries/${deliveryId}/cancel`;
            document.getElementById('cancelModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }

        function confirmDelete(deliveryId) {
            if (confirm('Yakin ingin menghapus delivery ini?')) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/deliveries/${deliveryId}`;
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const routeSelect = document.getElementById('routeSelect');
            if (routeSelect) {
                routeSelect.addEventListener('change', function() {
                    const selected = routeSelect.options[routeSelect.selectedIndex];
                    document.getElementById('pickupLocation').value = selected.getAttribute('data-pickup') || '';
                    document.getElementById('deliveryLocations').value = selected.getAttribute('data-location') || '';
                });
            }
        });
    </script>
@endpush