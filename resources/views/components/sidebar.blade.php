@props(['active' => ''])

{{--
    ╔══════════════════════════════════════════════════╗
    ║  ACTIVE STATE  → bg putih solid + teks biru       ║
    ║                  (seperti "pill" yang dipilih)     ║
    ║  HOVER  STATE  → bg putih transparan 15%          ║
    ║  DEFAULT STATE → teks putih 70%                   ║
    ╚══════════════════════════════════════════════════╝

    Kunci: active pakai `bg-white text-blue-700 font-semibold shadow`
           bukan `bg-white bg-opacity-10` yang sama dengan hover.
--}}

<aside class="w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white flex flex-col h-full min-h-screen">
    <div class="flex flex-col flex-1 px-4 py-6 overflow-y-auto">

        {{-- ===== LOGO ===== --}}
        <a href="#" class="flex items-center gap-3 mb-8 px-2 flex-shrink-0 group">
            <div class="bg-white bg-opacity-20 group-hover:bg-opacity-30 p-2 rounded-xl transition-all flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-sm leading-tight text-white">Sistem Manajemen</p>
                <p class="font-semibold text-sm leading-tight text-teal-300">Transporter</p>
            </div>
        </a>

        {{-- ===== NAVIGATION ===== --}}
        <nav class="space-y-1 flex-1">

            {{-- ──────────────────── SUPERADMIN ──────────────────── --}}
            @if (Auth::user()->role === 'superadmin')

                @php
                    $items = [
                        [
                            'href'  => route('superadmin.dashboard'),
                            'match' => 'superadmin.dashboard',
                            'label' => 'Dashboard',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>',
                        ],
                        [
                            'href'  => route('superadmin.users.index'),
                            'match' => 'superadmin.users.*',
                            'label' => 'User Management',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
                        ],
                        [
                            'href'  => route('superadmin.deliveries.index'),
                            'match' => 'superadmin.deliveries.*',
                            'label' => 'Master Delivery',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        ],
                        [
                            'href'  => route('superadmin.locations.index'),
                            'match' => 'superadmin.locations.*',
                            'label' => 'Master Location',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
                        ],
                        [
                            'href'  => route('superadmin.routes.index'),
                            'match' => 'superadmin.routes.*',
                            'label' => 'Master Route',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
                        ],
                    ];
                @endphp

            {{-- ──────────────────── ADMIN ──────────────────── --}}
            @elseif (Auth::user()->role === 'admin')

                @php
                    $items = [
                        [
                            'href'  => route('admin.dashboard'),
                            'match' => 'admin.dashboard',
                            'label' => 'Dashboard',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>',
                        ],
                        [
                            'href'  => route('admin.locationIndex'),
                            'match' => 'admin.location*',
                            'label' => 'Master Lokasi',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
                        ],
                        [
                            'href'  => route('admin.routeIndex'),
                            'match' => 'admin.route*',
                            'label' => 'Master Rute',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
                        ],
                        [
                            'href'  => route('admin.deliveryIndex'),
                            'match' => 'admin.delivery*',
                            'label' => 'Master Delivery',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        ],
                    ];
                @endphp

            {{-- ──────────────────── DRIVER ──────────────────── --}}
            @elseif (Auth::user()->role === 'user')

                @php
                    $items = [
                        [
                            'href'  => route('user.deliveryIndex'),
                            'match' => 'user.deliver*',
                            'label' => 'Menu Delivery',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        ],
                        [
                            'href'  => route('user.history'),
                            'match' => 'user.history*',
                            'label' => 'Rekap Delivery',
                            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
                        ],
                    ];
                @endphp

            @endif

            {{-- ===== RENDER MENU ITEMS ===== --}}
            @isset($items)
                @foreach ($items as $item)
                    @php $isActive = request()->routeIs($item['match']); @endphp

                    <a href="{{ $item['href'] }}"
                        @class([
                            // BASE — selalu ada
                            'flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-200',

                            // ACTIVE — bg putih solid, teks biru gelap
                            // Ini SANGAT berbeda dari hover sehingga selalu terlihat jelas
                            'bg-white text-blue-700 shadow-md font-semibold' => $isActive,

                            // DEFAULT + HOVER — hanya saat tidak aktif
                            'text-white/70 hover:bg-white/15 hover:text-white' => ! $isActive,
                        ])>

                        {{-- Garis aksen kiri (hanya saat aktif) --}}
                        <span @class([
                            'absolute left-0 w-1 h-8 rounded-r-full bg-teal-400 transition-all',
                            'opacity-100' => $isActive,
                            'opacity-0'   => ! $isActive,
                        ])></span>

                        {{-- Icon: biru saat aktif, putih saat tidak aktif --}}
                        <svg @class([
                            'w-5 h-5 flex-shrink-0',
                            'text-blue-600' => $isActive,
                            'text-white/70' => ! $isActive,
                        ]) fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $item['icon'] !!}
                        </svg>

                        {{-- Label --}}
                        <span class="flex-1">{{ $item['label'] }}</span>

                        {{-- Dot indikator (hanya saat aktif) --}}
                        @if ($isActive)
                            <span class="w-2 h-2 rounded-full bg-teal-500 flex-shrink-0"></span>
                        @endif
                    </a>
                @endforeach
            @endisset

        </nav>

        {{-- ===== USER INFO (BAWAH) ===== --}}
        <div class="mt-auto pt-4 flex-shrink-0">
            <div class="border-t border-white/20 pt-4">
                <div class="flex items-center gap-3 px-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d9488&color=fff&size=64"
                         alt="{{ Auth::user()->name }}"
                         class="w-9 h-9 rounded-full border-2 border-teal-400 flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-teal-300 capitalize">{{ Auth::user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                            class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</aside>