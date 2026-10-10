<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KDS Bar (Beverage Display System) • Saung Situ Awi</title>

    {{-- Fonts: Plus Jakarta Sans, Playfair Display, JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite Assets with CDN Fallbacks --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            background-color: #FBF8F1;
            color: #1F2937;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            touch-action: manipulation;
            user-select: none;
        }

        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Custom scrollbar for horizontal cards */
        .kds-scroll::-webkit-scrollbar {
            height: 10px;
        }
        .kds-scroll::-webkit-scrollbar-track {
            background: #EAE4D7;
            border-radius: 9999px;
            margin: 0 24px;
        }
        .kds-scroll::-webkit-scrollbar-thumb {
            background: #C4B89B;
            border-radius: 9999px;
            border: 2px solid #EAE4D7;
        }
        .kds-scroll::-webkit-scrollbar-thumb:hover {
            background: #A89B7B;
        }

        /* Pulse glow for overdue cards */
        @keyframes overdue-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(193, 68, 44, 0.45);
            }
            50% {
                box-shadow: 0 0 16px 3px rgba(193, 68, 44, 0.3);
            }
        }
        .animate-overdue-glow {
            animation: overdue-glow 2s infinite ease-in-out;
        }
    </style>
</head>
<body class="min-h-screen bg-[#FBF8F1] flex flex-col justify-between overflow-x-hidden" x-data="kdsBar()" x-init="initKds()">

    <!-- 01. STICKY TOP HEADER (81px) -->
    <header class="sticky top-0 z-40 w-full bg-[#FBF8F1] border-b border-[#E5E2DB] shadow-[0_1px_2px_rgba(0,0,0,0.05)] px-4 sm:px-8 py-3.5 flex flex-wrap items-center justify-between gap-4">
        <!-- Left: Logo & Station Title -->
        <div class="flex items-center gap-3.5">
            <div class="flex-shrink-0">
                <img src="{{ asset('asset/Logo.png') }}" alt="Logo Saung Situ Awi" class="h-11 sm:h-12 w-auto object-contain">
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <h1 class="font-display font-bold text-2xl sm:text-[32px] text-[#00341A] tracking-tight leading-none">
                        Saung Situ Awi
                    </h1>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#EBF6EE] border border-[#C8E6D0] text-[#0B4D2B]">
                        KDS BAR — MINUMAN & KOPI
                    </span>
                </div>
                <p class="text-[10px] font-semibold tracking-wider text-[#707971] uppercase mt-1 leading-none">
                    ALUR TIKET MASUK BERDASARKAN WAKTU • BARISTA VIEW
                </p>
            </div>
        </div>

        <!-- Center: WebSocket Connection Badge -->
        <div class="hidden md:flex items-center">
            <div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-[#EBF4DC] border border-[#D5E8B3] shadow-2xs">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#7CA82B] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#4E750E]"></span>
                </span>
                <span class="text-[10px] font-bold text-[#364E00] tracking-wide">
                    WebSocket Terhubung <span class="font-mono text-[11px] font-semibold text-[#364E00]/90">(12ms)</span>
                </span>
            </div>
        </div>

        <!-- Right: Digital Clock & Ergonomic Controls -->
        <div class="flex items-center gap-4 sm:gap-6">
            <!-- Digital Clock -->
            <div class="text-right">
                <div class="font-mono font-bold text-lg sm:text-[20px] text-[#00341A] tracking-tight leading-none" x-text="clock">
                    12:08:45
                </div>
                <div class="text-[9px] sm:text-[10px] font-semibold tracking-wider text-[#707971] uppercase mt-1 leading-none">
                    WIB • SERVER SYNCHRONIZED
                </div>
            </div>

            <!-- Action Controls -->
            <div class="flex items-center gap-2">
                <!-- Buzzer Volume / Sound Toggle -->
                <button 
                    type="button"
                    @click="toggleSound()"
                    class="h-11 w-11 sm:h-12 sm:w-12 rounded-xl bg-[#F0EEE7] border border-[#E5E2DB] hover:border-[#0B4D2B]/30 active:scale-95 flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                    :title="soundEnabled ? 'Matikan Suara Buzzer' : 'Nyalakan Suara Buzzer'">
                    <template x-if="soundEnabled">
                        <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </template>
                    <template x-if="!soundEnabled">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                    </template>
                </button>

                <!-- Fullscreen Toggle Button -->
                <button 
                    type="button"
                    @click="toggleFullscreen()"
                    class="h-11 w-11 sm:h-12 sm:w-12 rounded-xl bg-[#F0EEE7] border border-[#E5E2DB] hover:border-[#0B4D2B]/30 active:scale-95 flex items-center justify-center transition-all shadow-2xs cursor-pointer"
                    title="Layar Penuh (Kios Mode)">
                    <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- 02. SUB-HEADER QUICK ACTION BAR & TARGET INDICATORS (78px) -->
    <section class="w-full bg-[#FBF8F1] px-4 sm:px-8 py-3 border-b border-[#0B4D2B]/10 flex flex-wrap items-center justify-between gap-4">
        <!-- Left: Station Pill & Status Filter Pills -->
        <div class="flex items-center flex-wrap gap-3 sm:gap-4">
            <!-- Station Pill Badge -->
            <div class="h-[54px] bg-white border border-[#0B4D2B]/10 shadow-xs rounded-xl px-3.5 py-2 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#EBF5EE] flex items-center justify-center text-[#0B4D2B] flex-shrink-0">
                    <!-- Drink / Cup / Glass Icon -->
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-[15px] text-[#0B4D2B] leading-tight">Bar & Minuman</span>
                    <span class="text-[10px] font-bold text-[#7A5900] uppercase tracking-wide leading-tight">STATION POS 02 • KHUSUS MINUMAN</span>
                </div>
            </div>

            <!-- Vertical Divider -->
            <div class="hidden sm:block w-[1px] h-7 bg-[#0B4D2B]/15"></div>

            <!-- Filter Tabs Container -->
            <div class="bg-[#EAE4D7] border border-[#0B4D2B]/10 rounded-full p-1 flex items-center gap-1 shadow-2xs">
                <!-- Semua -->
                <button 
                    type="button" 
                    @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-[#E0B24E] text-[#261900] shadow-xs' : 'text-[#404941] hover:text-black'"
                    class="rounded-full px-3 py-1 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>Semua</span>
                    <span 
                        :class="filter === 'all' ? 'bg-[#261900]/15 text-[#261900]' : 'bg-[#0B4D2B]/10 text-[#0B4D2B]'"
                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                        x-text="tickets.length">4</span>
                </button>

                <!-- Menunggu -->
                <button 
                    type="button" 
                    @click="filter = 'menunggu'"
                    :class="filter === 'menunggu' ? 'bg-[#E0B24E] text-[#261900] shadow-xs' : 'text-[#404941] hover:text-black'"
                    class="rounded-full px-3 py-1 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>Menunggu</span>
                    <span 
                        :class="filter === 'menunggu' ? 'bg-[#261900]/15 text-[#261900]' : 'bg-[#0B4D2B]/10 text-[#0B4D2B]'"
                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                        x-text="tickets.filter(t => t.status === 'menunggu').length">1</span>
                </button>

                <!-- Diproses -->
                <button 
                    type="button" 
                    @click="filter = 'diproses'"
                    :class="filter === 'diproses' ? 'bg-[#E0B24E] text-[#261900] shadow-xs' : 'text-[#404941] hover:text-black'"
                    class="rounded-full px-3 py-1 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>Diproses</span>
                    <span 
                        :class="filter === 'diproses' ? 'bg-[#261900]/15 text-[#261900]' : 'bg-[#E0B24E]/30 text-[#765600]'"
                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                        x-text="tickets.filter(t => t.status === 'diproses').length">2</span>
                </button>

                <!-- Siap -->
                <button 
                    type="button" 
                    @click="filter = 'siap'"
                    :class="filter === 'siap' ? 'bg-[#E0B24E] text-[#261900] shadow-xs' : 'text-[#404941] hover:text-black'"
                    class="rounded-full px-3 py-1 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>Siap</span>
                    <span 
                        :class="filter === 'siap' ? 'bg-[#261900]/15 text-[#261900]' : 'bg-[#2E6A45]/20 text-[#0B4D2B]'"
                        class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                        x-text="tickets.filter(t => t.status === 'siap').length">1</span>
                </button>
            </div>
        </div>

        <!-- Right: Target Metric & Global Runner Call -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Rata-Rata Seduh Pill -->
            <div class="flex items-center gap-2.5 px-3.5 py-2 bg-white rounded-xl border border-[#0B4D2B]/10 shadow-xs">
                <div class="w-5 h-5 rounded-full bg-[#EBF5EE] flex items-center justify-center text-[#7A5900]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold tracking-wider text-[#707971] uppercase leading-none">RATA–RATA SEDUH</span>
                    <div class="flex items-baseline gap-1 mt-0.5 leading-tight">
                        <span class="font-bold text-[16px] text-[#0B4D2B]">3.8</span>
                        <span class="text-xs font-semibold text-[#0B4D2B]">Mnt</span>
                    </div>
                </div>
            </div>

            <!-- Panggil Runner CTA Button -->
            <button 
                type="button"
                @click="openGlobalRunnerModal()"
                class="h-11 px-4 rounded-xl bg-[#E0B24E] hover:bg-[#d4a33f] active:scale-95 border border-[#C49A3C] shadow-xs text-[#261900] font-bold text-sm flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Panggil Runner</span>
            </button>

            <!-- Notification Bell Icon Button -->
            <button 
                type="button"
                @click="testKitchenBell()"
                class="h-11 w-11 rounded-xl bg-white border border-[#0B4D2B]/15 hover:border-[#0B4D2B]/30 active:scale-95 shadow-xs flex items-center justify-center text-[#0B4D2B] transition-all cursor-pointer"
                title="Cek Notifikasi / Pager">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </button>
        </div>
    </section>

    <!-- 03. HORIZONTAL STREAMLINE ORDER CARDS ROW -->
    <main class="flex-1 w-full px-4 sm:px-8 py-5 overflow-x-auto kds-scroll">
        <div class="flex items-start gap-6 min-w-max pb-3">
            
            <template x-for="(ticket, tIndex) in filteredTickets" :key="ticket.id">
                <article 
                    class="w-[370px] min-h-[515px] bg-white rounded-2xl flex flex-col justify-between transition-all duration-200"
                    :class="{
                        'border-2 border-[#C1442C] shadow-[0px_10px_15px_-3px_rgba(193,68,44,0.1),0px_4px_6px_-4px_rgba(193,68,44,0.1)]': ticket.isOverdue && ticket.status !== 'siap',
                        'border-2 border-[#2E6A45] shadow-md': ticket.status === 'siap',
                        'border-2 border-[#0B4D2B] shadow-md': !ticket.isOverdue && ticket.status === 'diproses',
                        'border-2 border-[#0B4D2B]/40 shadow-md': ticket.status === 'menunggu'
                    }">
                    
                    <!-- Card Upper Section: Header + Items List -->
                    <div class="flex flex-col">
                        <!-- Card Header -->
                        <div 
                            class="p-4 rounded-t-2xl border-b flex items-start justify-between gap-2"
                            :class="{
                                'bg-[#FDF4F2] border-[#C1442C]/20': ticket.isOverdue && ticket.status !== 'siap',
                                'bg-[#F0FDF4] border-[#2E6A45]/20': ticket.status === 'siap',
                                'bg-[#F8F5EE] border-[#E6E1D5]': ticket.status === 'menunggu' || (!ticket.isOverdue && ticket.status === 'diproses')
                            }">
                            <!-- Left: Saung & Customer Info -->
                            <div class="flex flex-col">
                                <h3 
                                    class="font-display font-bold text-2xl tracking-tight leading-tight"
                                    :class="ticket.status === 'siap' || ticket.status === 'diproses' && !ticket.isOverdue ? 'text-[#0B4D2B]' : 'text-[#1F2937]'"
                                    x-text="ticket.saung">
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1 text-xs">
                                    <span class="font-bold text-[#0B4D2B]" x-text="ticket.id"></span>
                                    <span class="text-gray-400">•</span>
                                    <span class="font-medium text-[#1F2937]" x-text="ticket.customer"></span>
                                </div>
                            </div>

                            <!-- Right: Status Badge & Timer Box -->
                            <div class="flex flex-col items-end gap-1.5">
                                <!-- Status Badge -->
                                <template x-if="ticket.status === 'diproses'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#FEF3C7] border border-[#F59E0B] text-[#92400E] flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#92400E] inline-block"></span>
                                        DIPROSES
                                    </span>
                                </template>
                                <template x-if="ticket.status === 'menunggu'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#E5E7EB] text-[#4B5563]">
                                        MENUNGGU
                                    </span>
                                </template>
                                <template x-if="ticket.status === 'siap'">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#2E6A45] text-white flex items-center gap-1">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        SIAP DIANTAR
                                    </span>
                                </template>

                                <!-- Timer Box -->
                                <!-- 1. Overdue Timer Box (Red Alert for Bar > 5m) -->
                                <template x-if="ticket.isOverdue && ticket.status !== 'siap'">
                                    <div class="h-10 px-2.5 rounded-lg bg-[#C1442C] text-white flex items-center gap-1.5 shadow-2xs">
                                        <svg class="w-4 h-4 text-white flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div class="flex flex-col text-right leading-none">
                                            <span class="font-mono font-bold text-sm tracking-tight" x-text="formatTimer(ticket.seconds)">06:20</span>
                                            <span class="text-[9px] uppercase tracking-wider font-semibold opacity-90">mnt</span>
                                        </div>
                                    </div>
                                </template>

                                <!-- 2. Siap Diantar Timer Box -->
                                <template x-if="ticket.status === 'siap'">
                                    <div class="h-8 px-2.5 rounded-lg bg-[#EBF5EE] border border-[#2E6A45]/20 text-[#0B4D2B] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#0B4D2B] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                        </svg>
                                        <span class="font-mono font-bold text-sm tracking-tight" x-text="formatTimer(ticket.seconds) + ' mnt'">04:15 mnt</span>
                                    </div>
                                </template>

                                <!-- 3. Diproses Normal Timer Box -->
                                <template x-if="!ticket.isOverdue && ticket.status === 'diproses'">
                                    <div class="h-8 px-2.5 rounded-lg bg-[#EBF5EE] border border-[#0B4D2B]/20 text-[#0B4D2B] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#0B4D2B] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                        </svg>
                                        <span class="font-mono font-bold text-sm tracking-tight" x-text="formatTimer(ticket.seconds) + ' mnt'">02:10 mnt</span>
                                    </div>
                                </template>

                                <!-- 4. Menunggu Timer Box -->
                                <template x-if="ticket.status === 'menunggu'">
                                    <div class="h-8 px-2.5 rounded-lg bg-[#F3F4F6] border border-[#E5E7EB] text-[#374151] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#374151] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                        </svg>
                                        <span class="font-mono font-bold text-sm tracking-tight" x-text="formatTimer(ticket.seconds) + ' mnt'">00:50 mnt</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Counter Bar Pickup Banner (For Ready Tickets) -->
                        <template x-if="ticket.status === 'siap'">
                            <div class="m-3 p-2.5 rounded-xl bg-[#EBF5EE] border border-[#2E6A45]/30 flex items-center gap-2.5 text-[#0B4D2B]">
                                <svg class="w-4 h-4 text-[#0B4D2B] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs font-bold leading-tight" x-text="ticket.passBarMessage">
                                    Di Counter Bar: Menunggu pelayan mengantar ke Saung LB-05.
                                </span>
                            </div>
                        </template>

                        <!-- Beverage Items Stream -->
                        <div class="p-4 flex flex-col gap-3">
                            <template x-for="(item, iIndex) in ticket.items" :key="iIndex">
                                <div 
                                    class="p-3 rounded-xl border transition-all cursor-pointer"
                                    :class="item.checked ? 'bg-[#FBF9F2] border-[#E6E1D5]' : 'bg-[#FBF9F2] border-[#E6E1D5] hover:border-gray-400'"
                                    @click="toggleItem(ticket, iIndex)">
                                    
                                    <div class="flex items-start gap-2.5">
                                        <!-- Checkbox Box -->
                                        <div 
                                            class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0 mt-0.5 transition-all shadow-2xs"
                                            :class="item.checked ? 'bg-[#0B4D2B] text-white' : 'border-2 border-[#A3A8A2] bg-white'">
                                            <template x-if="item.checked">
                                                <svg class="w-4 h-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </template>
                                        </div>

                                        <!-- Item Details & Notes -->
                                        <div class="flex-1 flex flex-col gap-1.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <span 
                                                    class="font-bold text-[16px] leading-snug text-[#1F2937]"
                                                    :class="ticket.status === 'siap' ? 'line-through text-[#6B7280]' : ''"
                                                    x-text="item.name">
                                                </span>
                                                <!-- Quantity Pill -->
                                                <span 
                                                    class="px-2.5 py-0.5 rounded-lg text-sm font-bold flex-shrink-0"
                                                    :class="ticket.status === 'siap' ? 'bg-[#F3F4F6] text-[#4B5563]' : 'bg-[#FDF6E2] border border-[#F3E27A] text-[#B45309]'"
                                                    x-text="item.qty">
                                                    2x
                                                </span>
                                            </div>

                                            <!-- Subtitle text (for completed items like in Ticket 2) -->
                                            <template x-if="item.sub">
                                                <span class="text-xs text-[#9CA3AF] font-normal" x-text="item.sub"></span>
                                            </template>

                                            <!-- Special Note Box -->
                                            <template x-if="item.note">
                                                <div 
                                                    class="p-2 rounded-lg text-xs flex items-center gap-1.5 leading-snug"
                                                    :class="{
                                                        'bg-[#FEF9C3] border border-[#FCD34D] text-[#92400E] font-semibold': item.noteType === 'warning' || item.noteType === 'v60' || item.noteType === 'kelapa',
                                                        'bg-[#FFFBEB] border border-[#FCD34D] text-[#92400E] font-semibold': item.noteType === 'info' || item.noteType === 'vietnam',
                                                        'text-[#707971] text-xs font-normal': item.noteType === 'sub'
                                                    }">
                                                    
                                                    <!-- Icon by Note Type -->
                                                    <template x-if="item.noteType === 'warning'">
                                                        <span class="font-bold text-[#C1442C] flex-shrink-0 text-sm">!</span>
                                                    </template>
                                                    <template x-if="item.noteType === 'v60'">
                                                        <span class="flex-shrink-0 text-sm">☕</span>
                                                    </template>
                                                    <template x-if="item.noteType === 'info' || item.noteType === 'vietnam' || item.noteType === 'kelapa'">
                                                        <svg class="w-3.5 h-3.5 text-[#92400E] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    </template>

                                                    <span x-text="item.note"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Card Lower Section: Ergonomic CTA Buttons -->
                    <div class="p-4 pt-0 flex flex-col gap-2">
                        <!-- Mode 1: Status DIPROSES (Tandai Minuman Siap + Panggil Runner) -->
                        <template x-if="ticket.status === 'diproses'">
                            <div class="flex flex-col gap-2">
                                <button 
                                    type="button"
                                    @click="markReady(ticket)"
                                    class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-[0.98] text-white font-bold text-base flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer">
                                    <svg class="w-5 h-5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Tandai Minuman Siap</span>
                                </button>
                                <button 
                                    type="button"
                                    @click="callRunnerForTicket(ticket)"
                                    class="w-full h-[42px] rounded-xl bg-[#E0B24E] hover:bg-[#d4a33f] active:scale-[0.98] border border-[#C49A3C] text-[#1F2937] font-bold text-sm flex items-center justify-center gap-2 shadow-2xs transition-all cursor-pointer">
                                    <svg class="w-4 h-4 text-[#1F2937]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span>Panggil Runner Bar</span>
                                </button>
                            </div>
                        </template>

                        <!-- Mode 2: Status SIAP DIANTAR (Big Panggil Runner Bar CTA) -->
                        <template x-if="ticket.status === 'siap'">
                            <button 
                                type="button"
                                @click="callRunnerForTicket(ticket)"
                                class="w-full h-[54px] rounded-xl bg-[#E0B24E] hover:bg-[#d4a33f] active:scale-[0.98] border border-[#C49A3C] text-[#1F2937] font-bold text-base flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer">
                                <svg class="w-5 h-5 text-[#1F2937]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span>Panggil Runner Bar</span>
                            </button>
                        </template>

                        <!-- Mode 3: Status MENUNGGU (Mulai Seduh / Proses) -->
                        <template x-if="ticket.status === 'menunggu'">
                            <button 
                                type="button"
                                @click="startBrewing(ticket)"
                                class="w-full h-[54px] rounded-xl bg-[#E0B24E] hover:bg-[#d4a33f] active:scale-[0.98] border border-[#C49A3C] text-[#1F2937] font-bold text-base flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer">
                                <span class="text-xl">☕</span>
                                <span>Mulai Seduh (Proses)</span>
                            </button>
                        </template>
                    </div>

                </article>
            </template>

        </div>
    </main>

    <!-- 04. PERSISTENT BOTTOM BAR PERFORMANCE DRAWER (81px) -->
    <section class="w-full bg-[#F4EFE6] border-t border-[#0B4D2B]/10 px-4 sm:px-8 py-3.5 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <!-- Left: Shift Output & Readiness Metrics -->
        <div class="flex items-center flex-wrap gap-6 sm:gap-8">
            <!-- 48 Minuman Selesai -->
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-[#0B4D2B] text-[#E0B24E] flex items-center justify-center shadow-xs flex-shrink-0">
                    <!-- Cup / Drink Icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-baseline gap-1.5 leading-none">
                        <span class="font-display font-bold text-3xl text-[#052E1B]" x-text="completedDrinks">48</span>
                        <span class="font-bold text-[10px] text-[#0B4D2B] uppercase tracking-wider">MINUMAN SELESAI</span>
                    </div>
                    <span class="text-[10px] font-medium text-[#707971] tracking-wide mt-1 leading-none">
                        Shift Sore Berjalan (16:00 - 22:00)
                    </span>
                </div>
            </div>

            <!-- 3.8 Mnt Rata-rata Seduh -->
            <div class="flex items-center gap-2.5 px-4 py-2 bg-white rounded-xl border border-[#0B4D2B]/10 shadow-2xs">
                <div class="w-5 h-5 rounded-full bg-[#EBF5EE] flex items-center justify-center text-[#7A5900] flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-base text-[#0B4D2B] leading-none">3.8 Mnt</span>
                    <span class="text-[10px] font-medium text-[#707971] tracking-wide leading-none mt-1">Rata-rata Seduh</span>
                </div>
            </div>

            <div class="hidden lg:block w-[1px] h-8 bg-[#0B4D2B]/15"></div>

            <!-- Stok Bar Siaga -->
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-[#EBF5EE] border border-[#0B4D2B]/20 flex items-center justify-center text-[#0B4D2B] flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-base text-[#052E1B] leading-none">Stok Bar Siaga</span>
                    <span class="text-[10px] font-semibold text-[#0B4D2B] tracking-wide leading-none mt-1">
                        Es Kristal & Biji Puntang Cukup
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: Audio Chime Test & KDS Refresh Rate -->
        <div class="flex items-center gap-3">
            <!-- Tes Lonceng KDS (80dB) Interactive Button -->
            <button 
                type="button"
                @click="testKitchenBell()"
                class="h-11 px-4 rounded-xl bg-white border border-[#0B4D2B]/20 hover:border-[#0B4D2B]/40 active:scale-95 shadow-xs flex items-center gap-2 text-xs font-bold text-[#052E1B] transition-all cursor-pointer">
                <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Tes Lonceng KDS (80dB)</span>
            </button>

            <!-- KDS Auto-Refresh Pill -->
            <div class="h-8 px-3 rounded-lg bg-white border border-[#0B4D2B]/10 shadow-2xs flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                <span class="text-[11px] font-semibold text-[#707971]">KDS Auto-Refresh: 5s</span>
            </div>
        </div>
    </section>

    <!-- 05. TECHNICAL FOOTER BAR (29px) -->
    <footer class="w-full bg-white border-t border-[#E5E2DB] shadow-2xs px-4 sm:px-8 py-2 flex flex-wrap items-center justify-between text-[10px] font-bold text-[#707971] leading-none">
        <div>
            Saung Situ Awi KDS • Node ID: <span class="font-mono text-[#0B4D2B]">AW-KDS-01</span>
        </div>
        <div class="flex items-center gap-5 sm:gap-8 font-mono">
            <span>Latency: <strong class="text-[#0B4D2B]">18ms</strong></span>
            <span>Auto-Bump: <strong class="text-[#0B4D2B]">15m</strong></span>
            <span>Touchscreen Calibration: <strong class="text-[#0B4D2B]">OK</strong></span>
        </div>
    </footer>

    <!-- INTERACTIVE TOAST FEEDBACK -->
    <div 
        x-cloak
        x-show="toast.show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-14 right-6 z-50 max-w-md bg-[#052E1B] text-white p-4 rounded-2xl shadow-xl flex items-center gap-3 border border-[#E0B24E]/30">
        <div class="w-8 h-8 rounded-xl bg-[#E0B24E] text-[#261900] flex items-center justify-center font-bold flex-shrink-0">
            🔔
        </div>
        <div class="flex-1 text-sm font-semibold" x-text="toast.message"></div>
        <button type="button" @click="toast.show = false" class="text-white/60 hover:text-white p-1">
            ✕
        </button>
    </div>

    <!-- PANGGIL RUNNER BAR MODAL / CONFIRMATION -->
    <div 
        x-cloak 
        x-show="showRunnerModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div 
            class="w-full max-w-md bg-[#FFFFFF] rounded-2xl border border-[#0B4D2B]/15 shadow-2xl p-6 flex flex-col gap-4 text-center"
            @click.outside="showRunnerModal = false">
            <div class="w-14 h-14 rounded-2xl bg-[#EBF5EE] text-[#0B4D2B] flex items-center justify-center mx-auto text-2xl">
                🔔
            </div>
            <div>
                <h4 class="font-display font-bold text-xl text-[#052E1B]">Panggil Runner Bar</h4>
                <p class="text-xs text-[#707971] mt-1" x-text="'Kirim sinyal lonceng & notifikasi ke pelayan runner untuk menjemput minuman ' + (runnerTarget || 'Stasiun Bar & Minuman') + '.'"></p>
            </div>
            <div class="flex items-center gap-3 mt-2">
                <button 
                    type="button" 
                    @click="showRunnerModal = false"
                    class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-50 active:scale-95 transition-all">
                    Batal
                </button>
                <button 
                    type="button" 
                    @click="confirmCallRunner()"
                    class="flex-1 py-3 rounded-xl bg-[#E0B24E] text-[#261900] font-bold text-sm hover:bg-[#d4a33f] active:scale-95 transition-all shadow-xs">
                    Panggil Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- ALPINE LOGIC -->
    <script>
        function kdsBar() {
            return {
                filter: 'all',
                soundEnabled: true,
                clock: '12:08:45',
                completedDrinks: 48,
                showRunnerModal: false,
                runnerTarget: '',
                toast: {
                    show: false,
                    message: ''
                },
                // 4 Ticket cards matching mockup & OCR data
                tickets: [
                    {
                        id: '#SA-20260915-084',
                        saung: 'SAUNG LB-02',
                        customer: 'Farhan Pratama',
                        status: 'diproses',
                        seconds: 6 * 60 + 20, // 06:20 mnt (> 5m Overdue for Bar!)
                        isOverdue: true,
                        items: [
                            {
                                name: 'Es Kelapa Muda Jeruk Kasturi',
                                qty: '2x',
                                checked: true,
                                note: 'Catatan: Gula aren cair terpisah, es batu sedikit',
                                noteType: 'warning'
                            },
                            {
                                name: 'Moka Pot Robusta Puntang',
                                qty: '1x',
                                checked: true,
                                note: 'Catatan: Gula terpisah, cangkir panas membara.',
                                noteType: 'info'
                            }
                        ]
                    },
                    {
                        id: '#SA-20260915-083',
                        saung: 'SAUNG LB-05',
                        customer: 'Ibu Ratna Dewi',
                        status: 'siap',
                        seconds: 4 * 60 + 15, // 04:15 mnt
                        isOverdue: false,
                        passBarMessage: 'Di Counter Bar: Menunggu pelayan mengantar ke Saung LB-05.',
                        items: [
                            {
                                name: 'Es Teh Manis Ciwidey',
                                qty: '4x',
                                checked: true,
                                sub: 'Es batu banyak, manis sedang (16oz)',
                                note: null,
                                noteType: null
                            },
                            {
                                name: 'Es Cincau Hijau Santan Aren',
                                qty: '1x',
                                checked: true,
                                sub: 'Daun Cincau Alami Situ Awi · Gula Aren Murni',
                                note: null,
                                noteType: null
                            }
                        ]
                    },
                    {
                        id: '#SA-20260915-082',
                        saung: 'SAUNG LA-01',
                        customer: 'Bpk Hendra',
                        status: 'diproses',
                        seconds: 2 * 60 + 10, // 02:10 mnt
                        isOverdue: false,
                        items: [
                            {
                                name: 'V60 Arabika Puntang Single Origin',
                                qty: '1x',
                                checked: false,
                                note: 'Catatan: Biji medium roast, rasio 1:15, cangkir panas.',
                                noteType: 'v60'
                            },
                            {
                                name: 'Kopi Vietnam Drip Susu Kental',
                                qty: '2x',
                                checked: false,
                                note: 'Catatan: Susu kental manis dipisah di sloki kecil.',
                                noteType: 'vietnam'
                            }
                        ]
                    },
                    {
                        id: '#SA-20260915-086',
                        saung: 'SAUNG KA-03',
                        customer: 'Dedi Kusnandar',
                        status: 'menunggu',
                        seconds: 50, // 00:50 mnt
                        isOverdue: false,
                        items: [
                            {
                                name: 'Es Kelapa Muda Batok Utuh',
                                qty: '3x',
                                checked: false,
                                note: 'Catatan: Kelapa muda daging lembut.',
                                noteType: 'kelapa'
                            },
                            {
                                name: 'Es Jeruk Peras Murni',
                                qty: '1x',
                                checked: false,
                                note: 'Jeruk Pontianak Peras · Tanpa Gula Tambahan',
                                noteType: 'sub'
                            }
                        ]
                    }
                ],

                get filteredTickets() {
                    if (this.filter === 'all') return this.tickets;
                    return this.tickets.filter(t => t.status === this.filter);
                },

                initKds() {
                    this.updateClock();
                    setInterval(() => {
                        this.updateClock();
                        // increment seconds for non-ready orders
                        this.tickets.forEach(t => {
                            if (t.status !== 'siap') {
                                t.seconds++;
                                // Bar threshold is 5 mins (300 seconds)
                                if (t.seconds >= 5 * 60) {
                                    t.isOverdue = true;
                                }
                            }
                        });
                    }, 1000);
                },

                updateClock() {
                    const now = new Date();
                    const h = String(now.getHours()).padStart(2, '0');
                    const m = String(now.getMinutes()).padStart(2, '0');
                    const s = String(now.getSeconds()).padStart(2, '0');
                    this.clock = `${h}:${m}:${s}`;
                },

                formatTimer(seconds) {
                    const m = Math.floor(seconds / 60);
                    const s = seconds % 60;
                    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                },

                toggleItem(ticket, itemIndex) {
                    ticket.items[itemIndex].checked = !ticket.items[itemIndex].checked;
                },

                startBrewing(ticket) {
                    ticket.status = 'diproses';
                    this.playBeepSound(580, 0.15);
                    this.showToast(`☕ Minuman ${ticket.saung} (${ticket.id}) mulai diseduh/diproses!`);
                },

                markReady(ticket) {
                    ticket.status = 'siap';
                    ticket.items.forEach(i => i.checked = true);
                    ticket.passBarMessage = `Di Counter Bar: Menunggu pelayan mengantar ke ${ticket.saung}.`;
                    this.completedDrinks += ticket.items.length;
                    this.playKitchenChime();
                    this.showToast(`✓ Minuman ${ticket.saung} siap diantar di Counter Bar!`);
                },

                callRunnerForTicket(ticket) {
                    this.runnerTarget = ticket.saung;
                    this.showRunnerModal = true;
                },

                openGlobalRunnerModal() {
                    this.runnerTarget = 'Stasiun Bar & Minuman';
                    this.showRunnerModal = true;
                },

                confirmCallRunner() {
                    this.showRunnerModal = false;
                    this.playKitchenChime();
                    this.showToast(`🔔 Runner telah dipanggil ke Bar untuk ${this.runnerTarget}!`);
                },

                testKitchenBell() {
                    this.playKitchenChime();
                    this.showToast('🔊 Uji Coba Lonceng KDS (80dB) Berhasil!');
                },

                toggleSound() {
                    this.soundEnabled = !this.soundEnabled;
                    this.showToast(this.soundEnabled ? '🔊 Suara Buzzer KDS diaktifkan' : '🔇 Suara Buzzer KDS dimatikan');
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.log('Fullscreen error:', err);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3500);
                },

                // Audio Chime Synthesizer using Web Audio API
                playKitchenChime() {
                    if (!this.soundEnabled) return;
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        const now = ctx.currentTime;
                        
                        const osc1 = ctx.createOscillator();
                        const gain1 = ctx.createGain();
                        osc1.type = 'sine';
                        osc1.frequency.setValueAtTime(880, now);
                        gain1.gain.setValueAtTime(0.4, now);
                        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
                        osc1.connect(gain1);
                        gain1.connect(ctx.destination);
                        osc1.start(now);
                        osc1.stop(now + 0.8);

                        const osc2 = ctx.createOscillator();
                        const gain2 = ctx.createGain();
                        osc2.type = 'triangle';
                        osc2.frequency.setValueAtTime(1320, now + 0.15);
                        gain2.gain.setValueAtTime(0.4, now + 0.15);
                        gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.1);
                        osc2.connect(gain2);
                        gain2.connect(ctx.destination);
                        osc2.start(now + 0.15);
                        osc2.stop(now + 1.1);
                    } catch(e) {
                        console.log('Audio Context error:', e);
                    }
                },

                playBeepSound(freq, duration) {
                    if (!this.soundEnabled) return;
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.frequency.setValueAtTime(freq, ctx.currentTime);
                        gain.gain.setValueAtTime(0.2, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + duration);
                    } catch(e) {}
                }
            }
        }
    </script>
</body>
</html>

