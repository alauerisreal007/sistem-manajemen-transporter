<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-gray-800">Master Lokasi</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    {{-- Alert --}}
    <div class="p-8">
        <x-flash-alerts />

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
            <div class="p-6">
                <!-- Header Tombol Search dan Tambah -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h3 class="text-lg font-semibold text-gray-700">Daftar Lokasi</h3>

                    <div class="flex gap-2 w-full md:w-auto">
                        <form method="GET" action="{{ route('admin.locationIndex') }}"
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
                                    Nama Lokasi</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Alamat</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Latitude</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Longitude</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kota</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Provinsi</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($location as $index => $locations)
                                <tr class="hover:bg-gray-50 transititon">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $location->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ $locations->name }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{-- <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"></span> --}}
                                            {!! $locations->type_badge !!}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        <div class="max-w-xs">
                                            <div class="text-sm">{{ $locations->address }}</div>
                                            @if ($locations->postal_code)
                                                <div class="text-xs text-gray-500 mt-1">{{ $locations->postal_code }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $locations->latitude }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600">
                                        {{ $locations->longitude }}
                                    </td>
                                    <td class="px-4 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ $locations->city }}
                                    </td>
                                    <td class="px-4 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {{ $locations->province }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if ($locations->status === 'active')
                                            {{-- <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Aktif
                                            </span> --}}
                                            {!! $locations->status_badge !!}
                                        @else
                                            {{-- <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Nonaktif
                                            </span> --}}
                                            {!! $locations->status_badge !!}
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-col gap-2">
                                            {{-- Detail Button --}}
                                            <button onclick="showDetailModal({{ $locations->id }})"
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
                                            <button onclick="openEditModal({{ $locations->id }})"
                                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs transition duration-150 flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>

                                            <!-- Delete Button -->
                                            <button onclick="confirmDelete({{ $locations->id }})"
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transititon duration-150 flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data lokasi yang ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $location->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Include All Modal --}}
    @include('location.partials.modals.create')
    @include('location.partials.modals.edit')
    @include('location.partials.modals.delete')
    @include('location.partials.modals.detail')

    {{-- Hide Delete Button --}}
    <form method="POST" id="deleteForm" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    {{-- Include Scripts --}}
    @include('location.partials.scripts.scripts')
</x-app-layout>
