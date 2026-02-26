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
            
            <!-- Backdrop Overlay (Mobile Only) -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-20 lg-hidden"
                 style="display: none;">
            </div>

            <!-- Sidebar -->
            <div x-show="sidebarOpen || window.innerWidth >= 1024"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-30 w-64 h-full lg:static lg:translate-x-0 lg:flex-shrink-0 lg:h-full">
                <x-sidebar />
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden min-w-0">
                
                <!-- Header -->
                <header class="bg-white shadow-sm px-4 sm:px-6 lg:px-8 py-4 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        
                        <!-- Left: Hamburger + Title -->
                        <div class="flex items-center gap-4 min-w-0">
                            <!-- Hamburger Button (Mobile Only) -->
                            <button @click="sidebarOpen = !sidebarOpen" 
                                    class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <!-- Title -->
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

                        <!-- Right: User Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                    <div class="inline-flex items-center gap-2 sm:gap-3">
                                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=4F46E5&color=fff" 
                                             alt="{{ Auth::user()->name }}" 
                                             class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-teal-500">
                                        <div class="text-left hidden sm:block">
                                            <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            {{ __('Log Out') }}
                                        </div>
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <!-- Content Area -->
                <main class="flex-1 overflow-y-auto bg-gray-100">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Script Stack -->
        @stack('scripts')
        
        <script>
            function updateClock() {
                const now = new Date();
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const dayName = days[now.getDay()];

                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const monthName = months[now.getMonth()];

                const day = String(now.getDate());
                const year = now.getFullYear();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                const offset = -now.getTimezoneOffset() / 60;
                const timezone = `WIB (GMT+${offset})`;

                const formattedDateTime = `${dayName}, ${day} ${monthName} ${year}, ${hours}:${minutes}:${seconds} ${timezone}`;

                const clockElement = document.getElementById('realtimeClock');
                if (clockElement) {
                    clockElement.textContent = formattedDateTime;
                }
            }

            updateClock();
            setInterval(updateClock, 1000);
        </script>
    </body>
</html>