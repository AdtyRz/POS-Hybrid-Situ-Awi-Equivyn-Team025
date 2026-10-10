<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Console') • Saung Situ Awi</title>

    {{-- Fonts: Plus Jakarta Sans, Playfair Display, JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite Assets with Fallback --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            background-color: #FCF9F2;
            color: #1C1C18;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #F0EEE7;
        }
        ::-webkit-scrollbar-thumb {
            background: #D4CEBF;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #B8B09D;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-[#FCF9F2] text-[#1C1C18] flex flex-col" x-data="{ mobileMenuOpen: false, clock: '14:32 WIB' }" x-init="
    setInterval(() => {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        clock = `${h}:${m} WIB`;
    }, 1000);
">

    <!-- DESKTOP & MOBILE WRAPPER -->
    <div class="flex min-h-screen relative">
        
        <!-- SIDEBAR OVERLAY (Mobile) -->
        <div 
            x-cloak
            x-show="mobileMenuOpen" 
            @click="mobileMenuOpen = false"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs lg:hidden transition-opacity">
        </div>

        <!-- ASIDE / SIDEBAR (288px width) -->
        <aside 
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:sticky top-0 left-0 z-50 h-screen w-72 bg-[#F6F3EC] border-r border-[#C0C9BF]/40 flex flex-col justify-between transition-transform duration-300 ease-in-out select-none overflow-y-auto">
            
            <!-- Top Logo & Navigation Section -->
            <div class="flex flex-col">
                <!-- Brand Header (Height ~129px) -->
                <div class="h-[110px] sm:h-[129px] border-b border-[#C0C9BF]/30 px-6 flex items-center gap-3.5">
                    <a href="{{ url('/admin/analitik-laporan') }}" class="flex items-center gap-3.5 group">
                        <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-10 w-auto object-contain flex-shrink-0 group-hover:scale-105 transition-transform">
                        <div class="flex flex-col">
                            <span class="font-display font-semibold text-2xl text-[#0B4D2B] leading-tight">Situ Awi</span>
                            <span class="text-[11px] font-bold tracking-wider text-[#707971] uppercase leading-tight mt-0.5">
                                MANAGEMENT CONSOLE • OWNER SUITE
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Main Menu Section -->
                <div class="p-4 flex flex-col gap-2">
                    <span class="px-2 text-[10px] font-bold text-[#707971] uppercase tracking-wider">MENU UTAMA</span>
                    
                    <nav class="flex flex-col gap-1 mt-1">
                        <!-- 1. Dasbor Analitik -->
                        <a 
                            href="{{ url('/admin/analitik-laporan') }}"
                            class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/analitik-laporan*') || request()->is('admin/dashboard*') ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'text-[#404941] hover:bg-[#EAE4D7] hover:text-[#1C1C18]' }}">
                            <!-- Grid / Dashboard Icon -->
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/analitik-laporan*') || request()->is('admin/dashboard*') ? 'text-white' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                            <span>Dasbor Analitik</span>
                        </a>

                        <!-- 2. Master Data Meja/Saung -->
                        <a 
                            href="{{ url('/admin/master-meja') }}"
                            class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/master-meja*') || request()->is('admin/meja*') ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'text-[#404941] hover:bg-[#EAE4D7] hover:text-[#1C1C18]' }}">
                            <!-- Gazebo / Table Icon -->
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/master-meja*') || request()->is('admin/meja*') ? 'text-white' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0v-4m14 4v-4M4 6h16a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z" />
                            </svg>
                            <span>Master Data Meja/Saung</span>
                        </a>

                        <!-- 3. Master Data Menu & Stok -->
                        <a 
                            href="{{ url('/admin/master-menu') }}"
                            class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/master-menu*') || request()->is('admin/menu*') ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'text-[#404941] hover:bg-[#EAE4D7] hover:text-[#1C1C18]' }}">
                            <!-- Fork / Knife Icon -->
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/master-menu*') || request()->is('admin/menu*') ? 'text-white' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Master Data Menu & Stok</span>
                        </a>

                        <!-- 4. Laporan & Ekspor -->
                        <a 
                            href="{{ url('/admin/laporan-penjualan') }}"
                            class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/laporan-penjualan*') || request()->is('admin/laporan*') ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'text-[#404941] hover:bg-[#EAE4D7] hover:text-[#1C1C18]' }}">
                            <!-- Report / File Icon -->
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/laporan-penjualan*') || request()->is('admin/laporan*') ? 'text-white' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Laporan & Ekspor</span>
                        </a>

                        <!-- 5. Manajemen Akun -->
                        <a 
                            href="{{ url('/admin/manajemen-akun') }}"
                            class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/manajemen-akun*') || request()->is('admin/akun*') ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'text-[#404941] hover:bg-[#EAE4D7] hover:text-[#1C1C18]' }}">
                            <!-- Users Icon -->
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/manajemen-akun*') || request()->is('admin/akun*') ? 'text-white' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Manajemen Akun</span>
                        </a>

                        <!-- 6. Pengaturan Sistem -->
                        <a 
                            href="{{ url('/admin/pengaturan-sistem') }}"
                            class="flex items-center gap-3.5 px-4 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/pengaturan-sistem*') || request()->is('admin/pengaturan*') ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'text-[#404941] hover:bg-[#EAE4D7] hover:text-[#1C1C18]' }}">
                            <!-- Sliders / Settings Icon -->
                            <svg class="w-4 h-4 flex-shrink-0 {{ request()->is('admin/pengaturan-sistem*') || request()->is('admin/pengaturan*') ? 'text-white' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span>Pengaturan Sistem</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Bottom Account Profile Card (Height ~87px) -->
            <div class="p-4 bg-[#F0EEE7]/50 border-t border-[#C0C9BF]/30">
                <div class="bg-white/80 border border-[#C0C9BF]/20 rounded-xl p-2.5 flex items-center justify-between gap-2 shadow-2xs">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                            PA
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#1C1C18] truncate leading-tight">Pak Rodiansyah Ariwibowo</span>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wide leading-tight mt-0.5">Owner / Admin Utama</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-lg hover:bg-gray-200/60 active:scale-95 flex items-center justify-center text-[#404941] transition-all cursor-pointer" title="Keluar / Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN VIEW CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#FCF9F2]">
            
            <!-- STICKY TOP NAVBAR (64px) -->
            <header class="sticky top-0 z-30 h-16 bg-[#FCF9F2]/90 backdrop-blur-md border-b border-[#C0C9BF]/30 shadow-[0_1px_8px_rgba(43,42,38,0.04)] px-4 sm:px-8 flex items-center justify-between gap-4">
                
                <!-- Left: Hamburger (Mobile) + Breadcrumbs Header -->
                <div class="flex items-center gap-3">
                    <button 
                        type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="p-2 rounded-lg text-[#404941] hover:bg-black/5 lg:hidden active:scale-95 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-[#707971] font-medium hidden sm:inline">Situ Awi Saung Lesehan</span>
                        <span class="text-[#C0C9BF] hidden sm:inline">/</span>
                        <span class="text-[#0B4D2B] font-semibold">@yield('page_category', 'Konsol Pemilik')</span>
                    </div>
                </div>

                <!-- Right: Telemetry & Controls -->
                <div class="flex items-center gap-2.5 sm:gap-4">
                    <!-- MQTT Online Pill -->
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#B1F1C2]/40 border border-[#B1F1C2] text-[10px] font-bold text-[#00341A]">
                        <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                        <span class="tracking-wide">MQTT Online</span>
                    </div>

                    <!-- 11/11 Nodes Synced Pill -->
                    <div class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FFDEA1]/50 border border-[#FFDEA1] text-[10px] font-bold text-[#261900]">
                        <svg class="w-3.5 h-3.5 text-[#261900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                        <span class="tracking-wide">11/11 Nodes Synced</span>
                    </div>

                    <!-- Live Digital Clock -->
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#F0EEE7] border border-[#C0C9BF]/20 text-xs font-semibold text-[#404941]">
                        <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                        </svg>
                        <span class="font-mono text-xs text-[#404941]" x-text="clock">14:32 WIB</span>
                    </div>

                    <!-- Notification Bell -->
                    <button type="button" class="relative p-2 rounded-full hover:bg-black/5 active:scale-95 transition-all text-[#404941]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#BA1A1A] ring-2 ring-[#FCF9F2]"></span>
                    </button>

                    <!-- Divider -->
                    <div class="w-[1px] h-6 bg-[#C0C9BF]/40"></div>

                    <!-- Small Avatar Profile -->
                    <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                        PA
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT SLOT -->
            <main class="flex-1 px-4 sm:px-8 py-6 sm:py-8 max-w-[1400px] w-full mx-auto">
                @yield('content')
            </main>

        </div>
    </div>

    @stack('scripts')
</body>
</html>

