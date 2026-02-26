<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-800">Master Delivery</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    <div class="p-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
            <div class="p-6">
                <!-- Header & Filters -->
                <div class="flex flex-col lg:flex-row md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-lg font-semibold text-gray-700">Daftar Delivery</h3>

                    <div class="flex gap-2 w-full md:w-auto flex-wrap">
                        <!-- Search -->
                        <form method="GET" action="{{ route('admin.deliveryIndex') }}" class="flex gap-2">
                            <input type="text" name="search" placeholder="Cari delivery..."
                                value="{{ request('search') }}"
                                class="border border-gray-300 px-4 py-2 rounded focus:ring-2 focus:ring-teal-500 w-full md:w-64">

                            <!-- Status Filter -->
                            <select name="status"
                                class="border border-gray-300 px-4 py-2 rounded focus:ring-2 focus:ring-teal-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu
                                </option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                    Dalam Perjalanan</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    Selesai</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    Dibatalkan</option>
                            </select>

                            <button type="submit"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </form>

                        {{-- Button Buat Delivery --}}
                        <button onclick="openCreateDeliveryModal()"
                            class="bg-green-600 hover:bg-green-800 text-white tex-sm rounded px-3 py-2 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Delivery
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-center">
                            <tr>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">No.</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Kode Delivery</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Rute</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Driver</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Progress</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Durasi</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($deliveries as $index => $delivery)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        {{ $deliveries->firstItem() + $index }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                <path fill-rule="evenodd"
                                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $delivery->delivery_code }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $delivery->created_at->format('d M Y, H:i') }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="text-sm">
                                            <div class="font-medium text-gray-900">{{ $delivery->route->route_name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                📦 {{ $delivery->route->pickupLocation->name }}
                                                →
                                                🚚 {{ $delivery->total_delivery_locations }} lokasi
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-purple-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 text-sm">
                                                    {{ $delivery->driver->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $delivery->driver->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    style="width: {{ $delivery->progress_percentage }}%"></div>
                                            </div>
                                            <span
                                                class="text-xs font-semibold text-gray-700">{{ $delivery->progress_percentage }}%</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $delivery->checkpoints->where('status', 'completed')->count() }} /
                                            {{ $delivery->checkpoints->count() }} selesai
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {!! $delivery->status_badge !!}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        {{ $delivery->formatted_duration }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('admin.deliveryShow', $delivery) }}"
                                                class="text-center bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition">
                                                Detail
                                            </a>

                                            @if ($delivery->status === 'pending')
                                                <button onclick="confirmCancel({{ $delivery->id }})"
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs transition">
                                                    Batalkan
                                                </button>

                                                <button onclick="confirmDelete({{ $delivery->id }})"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data delivery.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $deliveries->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold mb-4">Batalkan Delivery</h3>

            <form id="cancelForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Pembatalan</label>
                    <textarea name="cancellation_reason" rows="3" required
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-teal-500"
                        placeholder="Masukkan alasan pembatalan..."></textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeCancelModal()"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded transition">
                        Ya, Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    @include('delivery.partials.modals.create')
    @include('delivery.partials.modals.signature-viewer')
    @include('delivery.partials.modals.image-viewer')
    @include('delivery.partials.scripts.index-scripts')
</x-app-layout>
