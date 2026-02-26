<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>

    <body class="font-sans antialiased" x-data="{ sidebarOpen: false }">
        <div class="flex h-screen bg-gray-100 overflow-hidden">

            {{-- ===================== --}}
            {{-- BACKDROP (mobile)     --}}
            {{-- ===================== --}}
            <div x-show="sidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-20 lg:hidden"
                 style="display: none;">
            </div>

            {{-- ===================== --}}
            {{-- SIDEBAR WRAPPER       --}}
            {{-- ===================== --}}
            {{--
                PENTING — tiga perubahan kunci di sini:
                1. `h-full`          → wrapper mengikuti tinggi flex parent (h-screen)
                2. `flex-shrink-0`   → sidebar tidak mengecil
                3. Pada mobile: `fixed inset-y-0` agar menutupi layar penuh secara vertikal
            --}}
            <div x-show="sidebarOpen || window.innerWidth >= 1024"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-30 w-64 h-full
                        lg:static lg:translate-x-0 lg:flex-shrink-0 lg:h-full"
                 style="display: none;">
                <x-sidebar />
            </div>

            {{-- ===================== --}}
            {{-- MAIN CONTENT          --}}
            {{-- ===================== --}}
            <div class="flex-1 flex flex-col overflow-hidden min-w-0">

                {{-- Header --}}
                <header class="bg-white shadow-sm px-4 sm:px-6 lg:px-8 py-4 flex-shrink-0">
                    <div class="flex items-center justify-between">

                        {{-- Kiri: Hamburger + Judul --}}
                        <div class="flex items-center gap-4 min-w-0">
                            {{-- Hamburger (mobile only) --}}
                            <button @click="sidebarOpen = !sidebarOpen"
                                    class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400
                                           hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            {{-- Judul halaman --}}
                            <div class="min-w-0">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 truncate">
                                        @if(Auth::user()->isSuperAdmin())
                                            Super Admin Dashboard
                                        @elseif(Auth::user()->isAdmin())
                                            Admin Dashboard
                                        @else
                                            Driver Dashboard
                                        @endif
                                    </h2>
                                    <p class="text-xs sm:text-sm text-gray-500 mt-1" id="realtimeClock">
                                        {{ now()->locale('id')->translatedFormat('l, d F Y H:i:s') }}
                                    </p>
                                @endisset
                            </div>
                        </div>

                        {{-- Kanan: Dropdown User --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm
                                               leading-4 font-medium rounded-md text-gray-500 bg-white
                                               hover:text-gray-700 focus:outline-none transition">
                                    <div class="inline-flex items-center gap-2 sm:gap-3">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse flex-shrink-0"></div>
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4F46E5&color=fff"
                                             alt="{{ Auth::user()->name }}"
                                             class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-teal-500 flex-shrink-0">
                                        <div class="text-left hidden sm:block">
                                            <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 hidden sm:block flex-shrink-0"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ __('Profile') }}
                                    </div>
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            {{ __('Log Out') }}
                                        </div>
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                {{-- Konten Halaman --}}
                <main class="flex-1 overflow-y-auto bg-gray-100">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Script stack dari child views --}}
        @stack('scripts')

        <script>
            // ========================================
            // REALTIME CLOCK
            // ========================================
            function updateClock() {
                const now       = new Date();
                const days      = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                const months    = ['Januari','Februari','Maret','April','Mei','Juni',
                                   'Juli','Agustus','September','Oktober','November','Desember'];
                const offset    = -now.getTimezoneOffset() / 60;
                const formatted = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}, `
                                + `${String(now.getHours()).padStart(2,'0')}:`
                                + `${String(now.getMinutes()).padStart(2,'0')}:`
                                + `${String(now.getSeconds()).padStart(2,'0')} `
                                + `WIB (GMT+${offset})`;

                const el = document.getElementById('realtimeClock');
                if (el) el.textContent = formatted;
            }
            updateClock();
            setInterval(updateClock, 1000);

            // ========================================
            // SIDEBAR: Pastikan tampil di desktop saat load
            // Alpine x-show dengan window.innerWidth perlu
            // di-init ulang saat resize supaya tidak hilang
            // ========================================
            window.addEventListener('resize', () => {
                // Alpine akan re-evaluate ekspresi x-show secara otomatis
                // tapi kita perlu trigger Alpine untuk re-render
                document.dispatchEvent(new CustomEvent('alpine:resize'));
            });
        </script>
    </body>
</html>





<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Rekap Delivery</h2>
        <p class="text-sm text-gray-500 mt-1" id="realtimeClock">
            {{ now()->locale('id')->translatedFormat('l, d F Y, H:i:s') }}
        </p>
    </x-slot>

    <div class="p-4 md:p-8 pb-24">

        {{-- ============================= --}}
        {{-- STATS SUMMARY                 --}}
        {{-- ============================= --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            {{-- Total Selesai --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Total Selesai</p>
                <p class="text-2xl font-bold text-green-600">
                    {{ $deliveries->where('status', 'completed')->count() }}
                </p>
            </div>

            {{-- Dibatalkan --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Dibatalkan</p>
                <p class="text-2xl font-bold text-red-600">
                    {{ $deliveries->where('status', 'cancelled')->count() }}
                </p>
            </div>

            {{-- Rata-rata Waktu --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Rata-rata Waktu</p>
                <p class="text-2xl font-bold text-blue-600">
                    {{ App\Models\Delivery::calculateAverageCompletedDuration($deliveries) }}
                </p>
            </div>

            {{-- Total Checkpoint --}}
            <div class="bg-white rounded-lg shadow p-4">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500">Total Checkpoint</p>
                <p class="text-2xl font-bold text-purple-600">
                    {{ $deliveries->sum(fn($d) => $d->checkpoints->count()) }}
                </p>
            </div>
        </div>

        {{-- ============================= --}}
        {{-- FILTER SECTION                --}}
        {{-- ============================= --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('user.history') }}" class="flex gap-3 flex-wrap items-center">

                {{-- FIX: 'selected' harus sebagai atribut HTML, bukan bagian dari value --}}
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="rounded-lg border-gray-300 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="rounded-lg border-gray-300 text-sm">

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Filter
                </button>

                @if (request()->hasAny(['status', 'date_from', 'date_to']))
                    <a href="{{ route('user.history') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- ============================= --}}
        {{-- HISTORY LIST                  --}}
        {{-- ============================= --}}
        <div class="space-y-4">
            @forelse ($deliveries as $delivery)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">

                    {{-- Card Header --}}
                    <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 flex items-center gap-2 flex-wrap">
                                    <span>{{ $delivery->delivery_code }}</span>

                                    @if ($delivery->status === 'completed')
                                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium whitespace-nowrap">
                                            ✔ Selesai
                                        </span>
                                    @elseif ($delivery->status === 'cancelled')
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-medium whitespace-nowrap">
                                            ❌ Dibatalkan
                                        </span>
                                    @elseif ($delivery->status === 'in_progress')
                                        <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium whitespace-nowrap">
                                            🚚 Dalam Perjalanan
                                        </span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-medium whitespace-nowrap">
                                            ⏳ Menunggu
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 truncate">{{ $delivery->route->route_name }}</p>
                            </div>

                            <button onclick="toggleDetails({{ $delivery->id }})"
                                class="flex-shrink-0 text-white text-sm font-medium bg-blue-600 hover:bg-blue-700 rounded-lg px-3 py-2 transition-colors flex items-center gap-1">
                                <span id="toggle-text-{{ $delivery->id }}">Detail</span>
                                <svg id="toggle-icon-{{ $delivery->id }}"
                                    class="w-4 h-4 transition-transform duration-200"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Quick Info --}}
                    <div class="p-4 bg-gray-50 grid grid-cols-2 lg:grid-cols-4 gap-3 text-sm">

                        {{-- FIX 1: started_at bisa null jika delivery belum di-start (pending/cancelled) --}}
                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">📅 Tanggal:</p>
                            <p class="font-semibold text-gray-900">
                                {{ $delivery->started_at?->format('d M Y') ?? $delivery->created_at->format('d M Y') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">🕒 Durasi:</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->formatted_duration }}</p>
                        </div>

                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">📍 Checkpoint:</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->checkpoints->count() }}</p>
                        </div>

                        <div class="flex items-center justify-center gap-2">
                            <p class="text-xs text-gray-500">📊 Progress:</p>
                            <p class="font-semibold text-gray-900">{{ $delivery->progress_percentage }}%</p>
                        </div>
                    </div>

                    {{-- ============================= --}}
                    {{-- DETAIL (hidden by default)    --}}
                    {{-- ============================= --}}
                    <div id="details-{{ $delivery->id }}" class="hidden">

                        {{-- Timeline Perjalanan --}}
                        <div class="p-4 border-t border-gray-200">
                            <h4 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"/>
                                </svg>
                                Timeline Perjalanan
                            </h4>

                            <div class="space-y-4">
                                @foreach ($delivery->checkpoints->sortBy('sequence') as $checkpoint)
                                    <div class="flex gap-3 {{ !$loop->last ? 'pb-4 border-b border-gray-100' : '' }}">

                                        {{-- Status Icon --}}
                                        <div class="flex-shrink-0">
                                            @if ($checkpoint->status === 'completed')
                                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            @elseif ($checkpoint->status === 'in_progress')
                                                <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">{{ $checkpoint->sequence + 1 }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Checkpoint Detail --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <p class="font-medium text-gray-900 flex items-center gap-2 flex-wrap">
                                                        <span>{{ $checkpoint->type_icon }} {{ $checkpoint->location->name }}</span>
                                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $checkpoint->type === 'pickup' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                            {{ $checkpoint->type === 'pickup' ? 'Pickup' : 'Delivery' }}
                                                        </span>
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $checkpoint->location->address }}</p>
                                                </div>
                                            </div>

                                            {{-- Info detail hanya jika checkpoint completed --}}
                                            @if ($checkpoint->status === 'completed')
                                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">Tiba:</span>
                                                        {{-- FIX 2: arrived_at bisa null, gunakan optional chaining --}}
                                                        <span class="font-semibold text-gray-900 ml-1">
                                                            {{ $checkpoint->arrived_at?->format('H:i') ?? '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">Selesai:</span>
                                                        {{-- FIX 3: departed_at bisa null --}}
                                                        <span class="font-semibold text-gray-900 ml-1">
                                                            {{ $checkpoint->departed_at?->format('H:i') ?? '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">Durasi:</span>
                                                        <span class="font-semibold text-gray-900 ml-1">
                                                            {{ $checkpoint->formatted_load_duration ?? '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded">
                                                        <span class="text-gray-500">
                                                            {{ $checkpoint->type === 'pickup' ? 'Petugas' : 'Penerima' }}:
                                                        </span>
                                                        <span class="font-semibold text-gray-900 ml-1">
                                                            {{ $checkpoint->recipient_name ?? '-' }}
                                                        </span>
                                                    </div>
                                                    <div class="bg-gray-50 p-2 rounded col-span-2">
                                                        <span class="text-gray-500">Tanda Tangan:</span>
                                                        @if ($checkpoint->signature_url)
                                                            <img src="{{ $checkpoint->signature_url }}"
                                                                 alt="Tanda Tangan"
                                                                 onclick="viewFullSignature('{{ $checkpoint->signature_url }}')"
                                                                 class="mt-1 h-16 border border-gray-300 rounded cursor-pointer hover:border-blue-400">
                                                        @else
                                                            <span class="font-semibold text-gray-400 ml-1">Tidak ada</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Foto --}}
                                                @if ($checkpoint->checkpointPhotos->count() > 0)
                                                    <div class="mt-3">
                                                        <p class="text-xs text-gray-600 mb-1">
                                                            📷 Foto ({{ $checkpoint->checkpointPhotos->count() }})
                                                        </p>
                                                        <div class="grid grid-cols-5 gap-1">
                                                            @foreach ($checkpoint->checkpointPhotos->take(5) as $photo)
                                                                <img src="{{ $photo->photo_url }}"
                                                                    alt="Foto"
                                                                    onclick="viewFullImage('{{ $photo->photo_url }}')"
                                                                    class="w-full aspect-square object-cover rounded border border-gray-200 cursor-pointer hover:border-blue-400 transition-colors">
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- GPS Tracking Summary --}}
                        @if ($delivery->gpsTracking->count() > 0)
                            <div class="p-4 bg-blue-50 border-t border-blue-100">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"/>
                                    </svg>
                                    Data GPS Tracking
                                </h4>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs text-gray-500 mb-1">Kecepatan Rata-rata</p>
                                        <p class="text-lg font-bold text-blue-600">
                                            {{ number_format($delivery->gpsTracking->whereNotNull('speed')->avg('speed') ?? 0, 1) }} km/jam
                                        </p>
                                    </div>
                                    <div class="bg-white p-3 rounded-lg">
                                        <p class="text-xs text-gray-500 mb-1">Akurasi GPS</p>
                                        <p class="text-lg font-bold text-blue-600">
                                            {{ number_format($delivery->gpsTracking->whereNotNull('accuracy')->avg('accuracy') ?? 0, 1) }} m
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Alasan Pembatalan --}}
                        @if ($delivery->status === 'cancelled' && $delivery->cancellation_reason)
                            <div class="p-4 bg-red-50 border-t border-red-100">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-red-900">Alasan Pembatalan</p>
                                        <p class="text-sm text-red-700 mt-1">{{ $delivery->cancellation_reason }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Completion Summary --}}
                        {{-- FIX 4: completed_at bisa null, gunakan optional chaining --}}
                        @if ($delivery->status === 'completed')
                            <div class="p-4 bg-green-50 border-t border-green-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-green-900">Delivery Berhasil Diselesaikan</p>
                                        <p class="text-sm text-green-700">
                                            {{ $delivery->completed_at?->format('d M Y, H:i') ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>{{-- end #details --}}
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Belum ada riwayat delivery</p>
                    <p class="text-gray-400 text-sm mt-1">Data akan muncul setelah delivery selesai atau dibatalkan</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $deliveries->links() }}
        </div>
    </div>

    @include('user.partials.modals.image-viewer')
    @include('user.partials.modals.signature-viewer')
    @include('user.partials.scripts.scripts')

    @push('scripts')
    <script>
        function toggleDetails(id) {
            const panel = document.getElementById('details-' + id);
            const icon  = document.getElementById('toggle-icon-' + id);
            const text  = document.getElementById('toggle-text-' + id);

            const isHidden = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !isHidden);
            icon.style.transform  = isHidden ? 'rotate(180deg)' : '';
            text.textContent      = isHidden ? 'Sembunyikan' : 'Detail';
        }
    </script>
    @endpush

</x-app-layout>


<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">
            Rekap History Delivery
        </h2>
    </x-slot>

    <div class="p-6 space-y-6">

        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="bg-white shadow rounded-xl p-4">
                <p class="text-sm text-gray-500">Total Selesai</p>
                <p class="text-2xl font-bold text-green-600">
                    {{ $summary->total_completed }}
                </p>
            </div>

            <div class="bg-white shadow rounded-xl p-4">
                <p class="text-sm text-gray-500">Total Cancel</p>
                <p class="text-2xl font-bold text-red-600">
                    {{ $summary->total_cancelled }}
                </p>
            </div>

            <div class="bg-white shadow rounded-xl p-4">
                <p class="text-sm text-gray-500">Rata-rata Durasi</p>
                <p class="text-2xl font-bold text-blue-600">
                    @php
                        $hours = floor($averageDuration / 60);
                        $minutes = $averageDuration % 60;
                    @endphp

                    {{ $averageDuration 
                        ? ($hours > 0 ? $hours.'j '.$minutes.'m' : $minutes.' menit') 
                        : '-' }}
                </p>
            </div>

            <div class="bg-white shadow rounded-xl p-4">
                <p class="text-sm text-gray-500">Total Checkpoint</p>
                <p class="text-2xl font-bold text-indigo-600">
                    {{ $deliveries->sum('checkpoints_count') }}
                </p>
            </div>
        </div>

        {{-- LIST DELIVERY --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Kode</th>
                        <th class="p-3 text-left">Route</th>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Progress</th>
                        <th class="p-3 text-left">Durasi</th>
                        <th class="p-3 text-left">Avg Speed</th>
                        <th class="p-3 text-left">Avg Accuracy</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deliveries as $delivery)
                        <tr class="border-t">
                            <td class="p-3 font-semibold">
                                {{ $delivery->delivery_code }}
                            </td>

                            <td class="p-3">
                                {{ $delivery->route->route_name ?? '-' }}
                            </td>

                            <td class="p-3">
                                {{ $delivery->started_at?->format('d M Y') 
                                    ?? $delivery->created_at->format('d M Y') }}
                            </td>

                            <td class="p-3">
                                {{ $delivery->progress_percentage }}%
                            </td>

                            <td class="p-3">
                                {{ $delivery->formatted_duration }}
                            </td>

                            <td class="p-3">
                                {{ number_format($delivery->avg_speed ?? 0, 1) }} km/h
                            </td>

                            <td class="p-3">
                                {{ number_format($delivery->avg_accuracy ?? 0, 1) }} m
                            </td>

                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs
                                    @if($delivery->status == 'completed') bg-green-100 text-green-700
                                    @elseif($delivery->status == 'cancelled') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700
                                    @endif
                                ">
                                    {{ ucfirst($delivery->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-4 text-center text-gray-500">
                                Tidak ada data delivery
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4">
                {{ $deliveries->links() }}
            </div>
        </div>

    </div>
</x-app-layout>