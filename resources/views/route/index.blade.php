<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-800">Master Rute</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    {{-- Alert --}}
    <div class="p-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-lg font-semibold text-gray-700">Daftar Rute</h3>

                    <div class="flex gap-2 w-full md:w-auto">
                        <form method="GET" action="{{ route('admin.routeIndex') }}"
                            class="flex-2 gap-2 flex 1 md:flex-initial">
                            <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}"
                                class="border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-teal-500 focus:border-transparent w-full md:w-64">
                            <button type="submit"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md transition duration-150">Cari</button>
                        </form>

                        <button onclick="openCreateModal()"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md flex items-center gap-2 transition duration-150 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No.</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Rute</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Titik Pickup</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Titik Delivery</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jarak</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estimasi</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($route as $index => $routes)
                                <tr class="hover:bg-gray-50 transititon">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $route->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ $routes->route_name }}</div>
                                        {{-- Show Delivery Count Badge --}}
                                        @if ($routes->deliveryLocations->count() > 0)
                                            <span
                                                class="flex items-center justify-center mt-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                {{ $routes->deliveryLocations->count() }} Titik Delivery
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Pickup Location --}}
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <div class="font-medium">{{ $routes->pickupLocation->name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ Str::limit($routes->pickupLocation->address, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Delivery Location Multiple --}}
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        @if ($routes->deliveryLocations->count() > 0)
                                            <div class="space-y-2">
                                                @foreach ($routes->deliveryLocations as $delivery)
                                                    <div class="flex items-start gap-2">
                                                        {{-- Sequence Number Badge --}}
                                                        <div class="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                                            <span class="text-xs font-semibold text-green-700">{{ $index + 1 }}</span>
                                                        </div>

                                                        {{-- Location Icon --}}
                                                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                                clip-rule="evenodd" />
                                                        </svg>

                                                        {{-- Location Details --}}
                                                        <div>
                                                            <div class="font-medium">
                                                                {{ $delivery->name }}</div>
                                                            <div class="text-xs text-gray-500">
                                                                {{ Str::limit($delivery->address, 50) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif ($routes->deliveryLocation)
                                            {{-- Fallback to single Delivery --}}
                                            <div class="flex items-start gap-2">
                                                <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $routes->deliveryLocation->name }}</div>
                                                <div class="text-xs text-gray-500">{{ Str::limit($routes->deliveryLocation->address, 50) }}</div>
                                            </div>
                                        @else
                                            <span clas="=text-gray-400 italic text-sm">Belum ada delivery</span>
                                        @endif
                                    </td>

                                    {{-- Distance & Time --}}
                                    <td class="px-4 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ $routes->formatted_distance }}
                                    </td>
                                    <td class="px-4 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ $routes->formatted_duration }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if ($routes->status === 'active')
                                            {!! $routes->status_badge !!}
                                        @else
                                            {!! $routes->status_badge !!}
                                        @endif
                                    </td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-col gap-2">
                                            <button onclick="showDetailModal({{ $routes->id }})"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition duration-150 flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Detail
                                            </button>

                                            <!-- Edit Button -->
                                            @if ($routes->status !== 'completed')
                                                <button onclick="openEditModal({{ $routes->id }})"
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs transition duration-150 flex items-center justify-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data rute yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $route->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Include All Modal --}}
    @include('route.partials.modals.create')
    @include('route.partials.modals.assign')
    @include('route.partials.modals.detail')
    @include('route.partials.modals.edit')

    <!-- Form Unassign Driver -->
    <form id="unassignForm" method="POST" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    {{-- Include Javascript --}}
    @include('route.partials.scripts.scripts')

</x-app-layout>
