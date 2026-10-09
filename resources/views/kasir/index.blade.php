<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Kasir POS • Saung Situ Awi Ciwidey</title>

    {{-- Fonts: Plus Jakarta Sans, Playfair Display, JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
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
            color: #2B2A26;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        /* Custom Serrated / Jagged Edge for 80mm ESC/POS Thermal Receipt */
        .thermal-tear-top {
            background: radial-gradient(circle at 10px -5px, transparent 12px, #FFFFFF 13px);
            background-size: 20px 20px;
        }
        .thermal-tear-bottom {
            background: radial-gradient(circle at 10px 15px, transparent 12px, #FFFFFF 13px);
            background-size: 20px 20px;
        }

        /* Pulse animation for alert card */
        @keyframes alert-pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(193, 68, 44, 0.4);
            }
            50% {
                box-shadow: 0 0 16px 4px rgba(193, 68, 44, 0.35);
            }
        }
        .animate-alert-pulse {
            animation: alert-pulse 1.8s infinite ease-in-out;
        }

        /* Print flash effect */
        @keyframes print-flash {
            0% { transform: translateY(-8px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .receipt-printed {
            animation: print-flash 0.4s ease-out;
        }
    </style>
</head>
<body class="bg-[#FBF8F1] min-h-screen text-[#2B2A26] flex flex-col justify-between selection:bg-[#E0B24E] selection:text-[#0B4D2B]"
      x-data="kasirPosApp()"
      x-init="initApp()"
      @keydown.window="handleKeyboardShortcuts($event)"
      x-cloak>

    {{-- ========================================================================= --}}
    {{-- 5. TOP HEADER BAR (Strictly Operational: No Tab Bar / No Multi-page Nav)   --}}
    {{-- ========================================================================= --}}
    <header class="sticky top-0 z-40 bg-[#FBF8F1]/95 backdrop-blur-md border-b border-[#C8C2B3]/60 shadow-[0_2px_8px_-2px_rgba(26,38,20,0.06)]">
        <div class="max-w-[1366px] w-full mx-auto px-6 py-3.5 flex items-center justify-between gap-6 min-h-[68px]">
            {{-- Left: Logo Saung Situ Awi & Subtitle --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#0B4D2B] border-2 border-[#E0B24E] overflow-hidden flex items-center justify-center shadow-sm shrink-0">
                    <img src="{{ asset('asset/Logo.png') }}" 
                         alt="Logo Saung Situ Awi" 
                         class="w-full h-full object-cover"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[#E0B24E] font-bold text-xs\'>SA</span>';">
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-display font-bold text-lg text-[#00341A] tracking-tight">Saung Situ Awi</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-[#E0B24E]/20 border border-[#E0B24E]/30 text-[#C9982F] text-[10px] font-extrabold uppercase tracking-widest">
                            POS
                        </span>
                    </div>
                    <div class="text-xs text-[#6B6A63] font-medium leading-none mt-0.5">
                        Terminal Kasir POS • Ciwidey
                    </div>
                </div>
            </div>

            {{-- Right: Operational Status Badges --}}
            <div class="flex items-center gap-2.5">
                {{-- 1. MQTT Broker Status --}}
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F3EEDF] border border-[#C8C2B3]/50 text-xs text-[#00341A] font-semibold">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                    </span>
                    <span>MQTT Broker: Online & Synced</span>
                </div>

                {{-- 2. Operator / Active Cashier Badge --}}
                <div class="flex items-center gap-2 px-3 py-1 rounded-xl bg-[#F3EEDF] border border-[#C8C2B3]/50 text-xs">
                    <div class="w-6 h-6 rounded-lg bg-[#0B4D2B] text-white flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="text-left leading-tight">
                        <div class="font-bold text-[#2B2A26]">Teh Neng Santi</div>
                        <div class="text-[10px] text-[#6B6A63]">Shift 1 Kasir Siang</div>
                    </div>
                </div>

                {{-- 3. Saung Occupancy Indicator --}}
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F3EEDF] border border-[#C8C2B3]/50 text-xs font-extrabold text-[#0B4D2B]">
                    <svg class="w-3.5 h-3.5 text-[#C9982F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span x-text="occupiedCount + '/11 Saung Aktif'">8/11 Saung Aktif</span>
                </div>

                {{-- 4. Live Real-time Clock --}}
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EAE4D3]/80 border border-[#C8C2B3]/50 text-xs font-mono font-bold text-[#00341A]">
                    <svg class="w-3.5 h-3.5 text-[#C9982F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="liveTimeString">12:08:25 WIB</span>
                </div>

                {{-- 5. Lock Screen Button --}}
                <button type="button" 
                        @click="lockTerminal()"
                        class="w-8 h-8 rounded-xl bg-[#F3EEDF] hover:bg-[#EAE4D3] border border-[#C8C2B3]/50 flex items-center justify-center text-[#6B6A63] hover:text-[#2B2A26] transition active:scale-95"
                        title="Kunci Terminal (Shift Lock)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- MAIN WORKSPACE (Desktop Dual Panel: Left 65% / Right 35%)                 --}}
    {{-- ========================================================================= --}}
    <main class="flex-1 max-w-[1366px] w-full mx-auto px-6 py-4 flex flex-col gap-3">

        {{-- ===================================================================== --}}
        {{-- 4. REAL-TIME IoT NOTIFICATION TOAST (Sensor TTP223 / Panggilan Saung) --}}
        {{-- ===================================================================== --}}
        <div x-show="iotAlert.active" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-4 opacity-0"
             class="bg-[#FBF3DD] border-2 border-[#E0B24E] rounded-xl p-3 shadow-[0px_0px_20px_-2px_rgba(224,178,78,0.35)] flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <div class="relative w-9 h-9 rounded-lg bg-[#0B4D2B] text-[#E0B24E] flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-[#C1442C] ring-2 ring-white"></span>
                </div>

                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded bg-[#C1442C] text-white text-[10px] font-extrabold uppercase tracking-wide">
                            PANGGILAN IOT 🚨
                        </span>
                        <h2 class="font-bold text-xs sm:text-sm text-[#00341A]" x-text="iotAlert.title">
                            PANGGILAN PELAYAN AKTIF: SAUNG LB-03 (Lesehan Bawah)
                        </h2>
                    </div>
                    <p class="text-[11px] text-[#6B6A63]" x-text="iotAlert.subtitle">
                        Sensor sentuh TTP223 terpicu (12:08:14 WIB) • Buzzer aktif 3 detik • Tamu butuh bantuan pelayan
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        @click="dismissAlert()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white border border-[#C8C2B3]/60 hover:bg-[#FAF7F0] text-xs font-semibold text-[#2B2A26] shadow-xs transition active:scale-95 min-h-[44px]">
                    <svg class="w-3.5 h-3.5 text-[#7A7E6F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 0A9 9 0 013 12m11.828-2.828l3.536-3.536M3 3l18 18"></path>
                    </svg>
                    <span>Matikan Buzzer / Tandai Selesai</span>
                </button>

                <button type="button" 
                        @click="assignWaiter()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#0B4D2B] hover:bg-[#0A4A28] text-white text-xs font-bold shadow-xs transition active:scale-95 min-h-[44px]">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Tugaskan Pelayan (Kang Asep)</span>
                </button>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- 2 & 3. 2-PANEL LAYOUT (DESKTOP 1366px): 65% GRID + 35% SETTLEMENT      --}}
        {{-- ===================================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
            
            {{-- ================================================================= --}}
            {{-- SECTION 2: LEFT PANEL (≈65% / 8 Cols) - 11 SAUNG STATUS GRID      --}}
            {{-- ================================================================= --}}
            <section class="lg:col-span-8 flex flex-col gap-3.5">
                
                {{-- Header Bar Above Grid: Filter Tabs & Meta --}}
                <div class="bg-[#FAF7F0] border border-[#C8C2B3]/60 rounded-xl p-2.5 shadow-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none">
                        {{-- Tab 1: Semua Saung --}}
                        <button type="button" 
                                @click="activeFilter = 'all'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition active:scale-95 shrink-0 min-h-[38px]"
                                :class="activeFilter === 'all' 
                                    ? 'bg-[#0B4D2B] text-white shadow-xs' 
                                    : 'bg-[#F3EEDF] text-[#2B2A26] hover:bg-[#EAE4D3]'">
                            <span>Semua Saung</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold"
                                  :class="activeFilter === 'all' ? 'bg-[#00341A] text-[#B1F1C2]' : 'bg-[#DFD7C4] text-[#6B6A63]'">
                                11
                            </span>
                        </button>

                        {{-- Tab 2: Menunggu Tunai --}}
                        <button type="button" 
                                @click="activeFilter = 'waiting_cash'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition active:scale-95 shrink-0 min-h-[38px]"
                                :class="activeFilter === 'waiting_cash' 
                                    ? 'bg-[#FFFBEB] border border-[#E0B24E]/70 text-[#C9982F] font-bold shadow-xs' 
                                    : 'bg-[#F3EEDF] text-[#2B2A26] hover:bg-[#EAE4D3]'">
                            <svg class="w-3.5 h-3.5 text-[#C9982F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Menunggu Tunai</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold bg-[#E0B24E]/20 text-[#C9982F] border border-[#E0B24E]/30">
                                3
                            </span>
                        </button>

                        {{-- Tab 3: Sudah Lunas --}}
                        <button type="button" 
                                @click="activeFilter = 'paid'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition active:scale-95 shrink-0 min-h-[38px]"
                                :class="activeFilter === 'paid' 
                                    ? 'bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46] font-bold shadow-xs' 
                                    : 'bg-[#F3EEDF] text-[#2B2A26] hover:bg-[#EAE4D3]'">
                            <svg class="w-3.5 h-3.5 text-[#047857]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Sudah Lunas</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold bg-[#D1FAE5] text-[#065F46]">
                                5
                            </span>
                        </button>

                        {{-- Tab 4: Kosong / Available --}}
                        <button type="button" 
                                @click="activeFilter = 'empty'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition active:scale-95 shrink-0 min-h-[38px]"
                                :class="activeFilter === 'empty' 
                                    ? 'bg-white border border-[#C8C2B3] text-[#2B2A26] font-bold shadow-xs' 
                                    : 'bg-[#F3EEDF] text-[#6B6A63] hover:bg-[#EAE4D3]'">
                            <span class="w-2 h-2 rounded-full bg-[#7A7E6F]"></span>
                            <span>Kosong / Available</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold bg-[#DFD7C4] text-[#6B6A63]">
                                3
                            </span>
                        </button>
                    </div>

                    {{-- Info Banner Tip --}}
                    <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-[#6B6A63] shrink-0 pr-1">
                        <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Pesanan via Scan QR Meja</span>
                    </div>
                </div>

                {{-- Clusters Container --}}
                <div class="space-y-4">

                    {{-- ========================================================= --}}
                    {{-- CLUSTER 1: LESEHAN BAWAH (5 Saung: LB-01 s/d LB-05)       --}}
                    {{-- ========================================================= --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#E0B24E]"></span>
                                <h3 class="font-display font-bold text-sm text-[#00341A]">
                                    Cluster 1: Lesehan Bawah (5 Saung Lesehan)
                                </h3>
                            </div>
                            <span class="text-xs font-mono font-semibold text-[#6B6A63]">4 Terisi • 1 Kosong</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="saung in getClusterSaungs('LB')" :key="saung.id">
                                <div @click="selectSaung(saung)"
                                     x-show="isSaungVisible(saung)"
                                     class="rounded-xl p-3 flex flex-col justify-between transition-all duration-200 cursor-pointer select-none relative"
                                     :class="{
                                         // Active & Selected
                                         'bg-[#FBF3DD]/40 border-2 border-[#0B4D2B] shadow-[0_0_0_2px_#E0B24E,0_6px_16px_-4px_rgba(26,38,20,0.08)] ring-2 ring-[#E0B24E]': selectedSaungId === saung.id && !saung.hasAlert,
                                         // Active Alert Card (LB-03)
                                         'bg-[#FFFBEB]/40 border-2 border-[#C1442C]/70 shadow-[0_0_16px_2px_rgba(193,68,44,0.25)] animate-alert-pulse': saung.hasAlert,
                                         // Normal Active (LB-02, LB-04)
                                         'bg-white border border-[#C8C2B3]/60 hover:shadow-md': selectedSaungId !== saung.id && saung.status !== 'empty' && !saung.hasAlert,
                                         // Empty Card (LB-05)
                                         'bg-[#FAF7F0]/60 border border-dashed border-[#C8C2B3]/70 opacity-80 hover:opacity-100': saung.status === 'empty'
                                     }">

                                    {{-- Card Top Header --}}
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-1.5">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <h4 class="font-bold text-sm tracking-tight"
                                                        :class="saung.status === 'empty' ? 'text-[#6B6A63]' : 'text-[#00341A]'"
                                                        x-text="saung.name">
                                                    </h4>
                                                    <span x-show="selectedSaungId === saung.id" 
                                                          class="px-1.5 py-0.2 rounded bg-[#0B4D2B] text-white text-[9px] font-bold uppercase tracking-wider">
                                                        TERPILIH
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-[#6B6A63]" x-text="saung.capacity"></div>
                                            </div>

                                            {{-- Status Badges --}}
                                            <div>
                                                {{-- Alert Badge on LB-03 --}}
                                                <template x-if="saung.hasAlert">
                                                    <div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-[#C1442C] text-white text-[9px] font-extrabold uppercase shadow-2xs">
                                                        <span>🚨</span>
                                                        <span>PANGGILAN</span>
                                                    </div>
                                                </template>
                                                {{-- Waiting Cash Badge --}}
                                                <template x-if="!saung.hasAlert && saung.status === 'waiting_cash'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E0B24E]/20 border border-[#E0B24E]/50 text-[#C9982F]">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        <span>Menunggu Bayar Kasir</span>
                                                    </span>
                                                </template>
                                                {{-- Paid Badge --}}
                                                <template x-if="saung.status === 'paid'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46]">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        <span x-text="saung.paymentType === 'qris' ? 'Lunas (QRIS)' : 'Lunas (Tunai)'"></span>
                                                    </span>
                                                </template>
                                                {{-- Empty Badge --}}
                                                <template x-if="saung.status === 'empty'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#EAE4D3] text-[#6B6A63]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#7A7E6F]"></span>
                                                        <span>Kosong</span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Sub-alert indicator for LB-03 --}}
                                        <div x-show="saung.hasAlert" class="flex items-center justify-between text-[10px]">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#E0B24E]/20 border border-[#E0B24E]/50 text-[#C9982F] font-bold">
                                                Menunggu Bayar Kasir
                                            </span>
                                            <span class="text-[#C1442C] font-bold flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#C1442C] animate-ping"></span>
                                                <span>Buzzer Aktif</span>
                                            </span>
                                        </div>

                                        {{-- Order Metadata Box --}}
                                        <template x-if="saung.status !== 'empty'">
                                            <div class="rounded-lg p-2 border text-[10px] space-y-1"
                                                 :class="selectedSaungId === saung.id ? 'bg-white/80 border-[#C8C2B3]/40' : 'bg-[#FAF7F0] border-[#C8C2B3]/30'">
                                                <div class="flex items-center justify-between text-[#6B6A63]">
                                                    <span class="font-mono" x-text="saung.orderId"></span>
                                                    <span class="font-semibold text-[#92400E] flex items-center gap-0.5">
                                                        <span>⏱️</span>
                                                        <span x-text="saung.elapsedTime"></span>
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-[#2B2A26] truncate max-w-[120px]" x-text="saung.customer"></span>
                                                    <span class="font-semibold text-[#0B4D2B]" x-text="saung.itemCount + ' Menu'"></span>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Empty Table Placeholder Box --}}
                                        <template x-if="saung.status === 'empty'">
                                            <div class="rounded-lg p-3 bg-[#F3EEDF]/50 border border-dashed border-[#C8C2B3]/50 text-center">
                                                <p class="text-[11px] italic text-[#6B6A63]">Saung bersih & siap ditempati</p>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Card Foot --}}
                                    <div class="pt-2 mt-2 border-t border-[#C8C2B3]/30 flex items-center justify-between">
                                        <span class="text-[11px] text-[#6B6A63] font-medium" 
                                              x-text="saung.status === 'empty' ? 'Status:' : 'Total Tagihan:'">
                                        </span>
                                        <span class="font-bold"
                                              :class="saung.status === 'empty' ? 'text-[#047857] text-xs' : 'text-[#00341A] text-sm'"
                                              x-text="saung.status === 'empty' ? 'Tersedia (Ready)' : formatRupiah(saung.total)">
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ========================================================= --}}
                    {{-- CLUSTER 2: LESEHAN ATAS (2 Saung: LA-01 & LA-02)          --}}
                    {{-- ========================================================= --}}
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#059669]"></span>
                                <h3 class="font-display font-bold text-sm text-[#00341A]">
                                    Cluster 2: Lesehan Atas (2 Saung Pemandangan Kebun)
                                </h3>
                            </div>
                            <span class="text-xs font-mono font-semibold text-[#6B6A63]">2 Terisi • 0 Kosong</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="saung in getClusterSaungs('LA')" :key="saung.id">
                                <div @click="selectSaung(saung)"
                                     x-show="isSaungVisible(saung)"
                                     class="rounded-xl p-3 flex flex-col justify-between transition-all duration-200 cursor-pointer select-none"
                                     :class="{
                                         'bg-[#FBF3DD]/40 border-2 border-[#0B4D2B] shadow-[0_0_0_2px_#E0B24E,0_6px_16px_-4px_rgba(26,38,20,0.08)] ring-2 ring-[#E0B24E]': selectedSaungId === saung.id,
                                         'bg-white border border-[#C8C2B3]/60 hover:shadow-md': selectedSaungId !== saung.id && saung.status !== 'empty',
                                         'bg-[#FAF7F0]/60 border border-dashed border-[#C8C2B3]/70 opacity-80': saung.status === 'empty'
                                     }">
                                    
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-1.5">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <h4 class="font-bold text-sm tracking-tight text-[#00341A]" x-text="saung.name"></h4>
                                                    <span x-show="selectedSaungId === saung.id" 
                                                          class="px-1.5 py-0.2 rounded bg-[#0B4D2B] text-white text-[9px] font-bold uppercase tracking-wider">
                                                        TERPILIH
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-[#6B6A63]" x-text="saung.capacity"></div>
                                            </div>

                                            <div>
                                                <template x-if="saung.status === 'waiting_cash'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E0B24E]/20 border border-[#E0B24E]/50 text-[#C9982F]">
                                                        <span>Menunggu Bayar Kasir</span>
                                                    </span>
                                                </template>
                                                <template x-if="saung.status === 'paid'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46]">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        <span x-text="saung.paymentType === 'qris' ? 'Lunas (QRIS)' : 'Lunas (Tunai)'"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="rounded-lg p-2 bg-[#FAF7F0] border border-[#C8C2B3]/30 text-[10px] space-y-1">
                                            <div class="flex items-center justify-between text-[#6B6A63]">
                                                <span class="font-mono" x-text="saung.orderId"></span>
                                                <span class="font-semibold text-[#92400E] flex items-center gap-0.5">
                                                    <span>⏱️</span>
                                                    <span x-text="saung.elapsedTime"></span>
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-[#2B2A26] truncate max-w-[120px]" x-text="saung.customer"></span>
                                                <span class="font-semibold text-[#0B4D2B]" x-text="saung.itemCount + ' Menu'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2 mt-2 border-t border-[#C8C2B3]/30 flex items-center justify-between">
                                        <span class="text-[11px] text-[#6B6A63] font-medium">Total Tagihan:</span>
                                        <span class="font-bold text-sm text-[#00341A]" x-text="formatRupiah(saung.total)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ========================================================= --}}
                    {{-- CLUSTER 3: KURSI ATAS (4 Saung: KA-01 s/d KA-04)          --}}
                    {{-- ========================================================= --}}
                    <div class="space-y-2 pt-1">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                                <h3 class="font-display font-bold text-sm text-[#00341A]">
                                    Cluster 3: Kursi Atas (4 Area Meja Kursi)
                                </h3>
                            </div>
                            <span class="text-xs font-mono font-semibold text-[#6B6A63]">2 Terisi • 2 Kosong</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="saung in getClusterSaungs('KA')" :key="saung.id">
                                <div @click="selectSaung(saung)"
                                     x-show="isSaungVisible(saung)"
                                     class="rounded-xl p-3 flex flex-col justify-between transition-all duration-200 cursor-pointer select-none"
                                     :class="{
                                         'bg-[#FBF3DD]/40 border-2 border-[#0B4D2B] shadow-[0_0_0_2px_#E0B24E,0_6px_16px_-4px_rgba(26,38,20,0.08)] ring-2 ring-[#E0B24E]': selectedSaungId === saung.id,
                                         'bg-white border border-[#C8C2B3]/60 hover:shadow-md': selectedSaungId !== saung.id && saung.status !== 'empty',
                                         'bg-[#FAF7F0]/60 border border-dashed border-[#C8C2B3]/70 opacity-80 hover:opacity-100': saung.status === 'empty'
                                     }">
                                    
                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-1.5">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <h4 class="font-bold text-sm tracking-tight"
                                                        :class="saung.status === 'empty' ? 'text-[#6B6A63]' : 'text-[#00341A]'"
                                                        x-text="saung.name">
                                                    </h4>
                                                    <span x-show="selectedSaungId === saung.id" 
                                                          class="px-1.5 py-0.2 rounded bg-[#0B4D2B] text-white text-[9px] font-bold uppercase tracking-wider">
                                                        TERPILIH
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-[#6B6A63]" x-text="saung.capacity"></div>
                                            </div>

                                            <div>
                                                <template x-if="saung.status === 'waiting_cash'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#E0B24E]/20 border border-[#E0B24E]/50 text-[#C9982F]">
                                                        <span>Menunggu Bayar Kasir</span>
                                                    </span>
                                                </template>
                                                <template x-if="saung.status === 'paid'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46]">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        <span x-text="saung.paymentType === 'qris' ? 'Lunas (QRIS)' : 'Lunas (Tunai)'"></span>
                                                    </span>
                                                </template>
                                                <template x-if="saung.status === 'empty'">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#EAE4D3] text-[#6B6A63]">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-[#7A7E6F]"></span>
                                                        <span>Kosong</span>
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <template x-if="saung.status !== 'empty'">
                                            <div class="rounded-lg p-2 bg-[#FAF7F0] border border-[#C8C2B3]/30 text-[10px] space-y-1">
                                                <div class="flex items-center justify-between text-[#6B6A63]">
                                                    <span class="font-mono" x-text="saung.orderId"></span>
                                                    <span class="font-semibold text-[#92400E] flex items-center gap-0.5">
                                                        <span>⏱️</span>
                                                        <span x-text="saung.elapsedTime"></span>
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-[#2B2A26] truncate max-w-[120px]" x-text="saung.customer"></span>
                                                    <span class="font-semibold text-[#0B4D2B]" x-text="saung.itemCount + ' Menu'"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="saung.status === 'empty'">
                                            <div class="rounded-lg p-3 bg-[#F3EEDF]/50 border border-dashed border-[#C8C2B3]/50 text-center">
                                                <p class="text-[11px] italic text-[#6B6A63]">Saung bersih & siap ditempati</p>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="pt-2 mt-2 border-t border-[#C8C2B3]/30 flex items-center justify-between">
                                        <span class="text-[11px] text-[#6B6A63] font-medium" 
                                              x-text="saung.status === 'empty' ? 'Status:' : 'Total Tagihan:'">
                                        </span>
                                        <span class="font-bold"
                                              :class="saung.status === 'empty' ? 'text-[#047857] text-xs' : 'text-[#00341A] text-sm'"
                                              x-text="saung.status === 'empty' ? 'Tersedia (Ready)' : formatRupiah(saung.total)">
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </section>

            {{-- ================================================================= --}}
            {{-- SECTION 3: RIGHT PANEL (≈35% / 4 Cols, Sticky) - SETTLEMENT & POS --}}
            {{-- ================================================================= --}}
            <aside class="lg:col-span-4 sticky top-20 flex flex-col gap-3">
                
                {{-- Main Transaction Card --}}
                <div class="bg-white border border-[#C8C2B3]/60 rounded-xl p-4 shadow-[0_6px_16px_-4px_rgba(26,38,20,0.08)] space-y-3">
                    
                    {{-- Selected Saung Header --}}
                    <div class="pb-2.5 border-b border-[#C8C2B3]/40 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 font-bold text-sm text-[#00341A] uppercase tracking-wide">
                                <svg class="w-4 h-4 text-[#C9982F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span x-text="'RINCIAN TAGIHAN • ' + currentSaung.name">RINCIAN TAGIHAN • SAUNG LB-01</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase"
                                  :class="{
                                      'bg-[#E0B24E]/20 text-[#C9982F] border border-[#E0B24E]/30': currentSaung.status === 'waiting_cash',
                                      'bg-[#ECFDF5] text-[#065F46] border border-[#6EE7B7]': currentSaung.status === 'paid',
                                      'bg-[#EAE4D3] text-[#6B6A63]': currentSaung.status === 'empty'
                                  }"
                                  x-text="currentSaung.status === 'empty' ? 'Kosong' : (currentSaung.status === 'paid' ? 'Lunas' : 'Aktif')">
                            </span>
                        </div>

                        <template x-if="currentSaung.status !== 'empty'">
                            <div>
                                <div class="flex items-center gap-1.5 text-xs text-[#6B6A63]">
                                    <span class="font-mono" x-text="'ID: ' + currentSaung.orderId"></span>
                                    <span>•</span>
                                    <span class="font-semibold text-[#2B2A26]" x-text="'Meja: ' + currentSaung.tableName"></span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-[#6B6A63] pt-0.5">
                                    <span x-text="'Pemesan: ' + currentSaung.customer"></span>
                                    <span class="flex items-center gap-1 font-mono text-[11px]">
                                        <svg class="w-3 h-3 text-[#C9982F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span x-text="'Waktu QR: ' + currentSaung.qrTime"></span>
                                    </span>
                                </div>

                                {{-- Status Chip --}}
                                <div class="mt-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold flex items-center gap-1.5"
                                     :class="currentSaung.status === 'waiting_cash' 
                                         ? 'bg-[#FBF3DD] border-[#E0B24E]/40 text-[#C9982F]' 
                                         : 'bg-[#ECFDF5] border-[#6EE7B7] text-[#065F46]'">
                                    <span class="w-2 h-2 rounded-full"
                                          :class="currentSaung.status === 'waiting_cash' ? 'bg-[#E0B24E] animate-pulse' : 'bg-[#059669]'"></span>
                                    <span x-text="currentSaung.status === 'waiting_cash' ? 'WAITING_CASH (Opsi Bayar Tunai di Kasir)' : 'SUDAH LUNAS (Transaksi Terverifikasi)'"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="currentSaung.status === 'empty'">
                            <div class="py-2 text-center text-xs text-[#6B6A63]">
                                Tidak ada transaksi aktif di saung ini. Silakan pilih saung lain pada overview grid.
                            </div>
                        </template>
                    </div>

                    {{-- Read-Only Itemized Order Summary (Scrollable) --}}
                    <template x-if="currentSaung.status !== 'empty'">
                        <div class="space-y-2 max-h-[185px] overflow-y-auto pr-1">
                            <template x-for="item in currentSaung.items" :key="item.name">
                                <div class="bg-[#FAF7F0] border border-[#C8C2B3]/30 rounded-lg p-2 text-xs space-y-0.5">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="text-[#2B2A26]" x-text="item.qty + 'x ' + item.name"></span>
                                        <span class="text-[#00341A]" x-text="formatRupiah(item.price * item.qty)"></span>
                                    </div>
                                    <div class="text-[10px] font-medium" 
                                         :class="item.noteColor || 'text-[#C9982F]'"
                                         x-text="item.note"></div>
                                    <div class="text-[10px] font-mono text-[#6B6A63]" x-text="'@ ' + formatRupiah(item.price)"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Order Calculation Breakdown Box (Tabular Nums) --}}
                    <template x-if="currentSaung.status !== 'empty'">
                        <div class="bg-[#F3EEDF] border border-[#C8C2B3]/40 rounded-lg p-2.5 text-xs space-y-1">
                            <div class="flex items-center justify-between text-[#6B6A63]">
                                <span x-text="'Subtotal (' + currentSaung.totalPortions + ' porsi):'"></span>
                                <span class="font-mono font-semibold text-[#2B2A26]" x-text="formatRupiah(currentSaung.subtotal)"></span>
                            </div>
                            <div class="flex items-center justify-between text-[#6B6A63]">
                                <span>Pajak PB1 Resto (10%):</span>
                                <span class="font-mono font-semibold text-[#2B2A26]" x-text="formatRupiah(currentSaung.tax)"></span>
                            </div>
                            <div class="pt-1.5 mt-1 border-t border-[#C8C2B3]/50 flex items-baseline justify-between">
                                <span class="font-extrabold text-[11px] text-[#00341A] uppercase tracking-wider">
                                    TOTAL TAGIHAN BERSIH:
                                </span>
                                <span class="font-extrabold text-lg sm:text-xl text-[#00341A] tracking-tight"
                                      x-text="formatRupiah(currentSaung.total)">
                                </span>
                            </div>
                        </div>
                    </template>

                    {{-- Cash Payment Section (Pelunasan Tunai) --}}
                    <template x-if="currentSaung.status === 'waiting_cash'">
                        <div class="space-y-2 pt-1 border-t border-[#C8C2B3]/30">
                            <label class="block text-[11px] font-bold text-[#6B6A63]">
                                Uang Diterima / Cash Received:
                            </label>

                            {{-- Quick Cash Buttons (Grid 2x2) --}}
                            <div class="grid grid-cols-2 gap-1.5">
                                <button type="button" 
                                        @click="setQuickCash(currentSaung.total)"
                                        class="py-2.5 px-2 rounded-lg text-xs font-bold transition active:scale-95 text-center min-h-[40px]"
                                        :class="cashReceived === currentSaung.total 
                                            ? 'bg-[#E0B24E]/20 border border-[#E0B24E]/70 text-[#C9982F] shadow-xs' 
                                            : 'bg-[#FAF7F0] border border-[#C8C2B3]/50 text-[#2B2A26] hover:bg-[#F3EEDF]'">
                                    <span x-text="'Uang Pas (' + formatRupiah(currentSaung.total) + ')'"></span>
                                </button>

                                <button type="button" 
                                        @click="setQuickCash(150000)"
                                        class="py-2.5 px-2 rounded-lg text-xs font-bold transition active:scale-95 text-center min-h-[40px]"
                                        :class="cashReceived === 150000 
                                            ? 'bg-[#E0B24E]/20 border border-[#E0B24E]/70 text-[#C9982F] shadow-xs' 
                                            : 'bg-[#FAF7F0] border border-[#C8C2B3]/50 text-[#2B2A26] hover:bg-[#F3EEDF]'">
                                    Rp 150.000
                                </button>

                                <button type="button" 
                                        @click="setQuickCash(200000)"
                                        class="py-2.5 px-2 rounded-lg text-xs font-bold transition active:scale-95 text-center min-h-[40px]"
                                        :class="cashReceived === 200000 
                                            ? 'bg-[#E0B24E]/20 border border-[#E0B24E]/70 text-[#C9982F] shadow-xs' 
                                            : 'bg-[#FAF7F0] border border-[#C8C2B3]/50 text-[#2B2A26] hover:bg-[#F3EEDF]'">
                                    Rp 200.000
                                </button>

                                <button type="button" 
                                        @click="setQuickCash(300000)"
                                        class="py-2.5 px-2 rounded-lg text-xs font-bold transition active:scale-95 text-center min-h-[40px]"
                                        :class="cashReceived === 300000 
                                            ? 'bg-[#E0B24E]/20 border border-[#E0B24E]/70 text-[#C9982F] shadow-xs' 
                                            : 'bg-[#FAF7F0] border border-[#C8C2B3]/50 text-[#2B2A26] hover:bg-[#F3EEDF]'">
                                    Rp 300.000
                                </button>
                            </div>

                            {{-- Input Uang Diterima & Kembalian Box --}}
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-[#6B6A63]">
                                    Rp
                                </span>
                                <input type="number" 
                                       x-model.number="cashReceived"
                                       class="w-full pl-9 pr-3 py-2 bg-white border border-[#C8C2B3]/60 rounded-lg text-sm font-mono font-bold text-right text-[#2B2A26] focus:outline-none focus:border-[#E0B24E] focus:ring-1 focus:ring-[#E0B24E]"
                                       placeholder="150000">
                            </div>

                            {{-- Kembalian Highlight Box --}}
                            <div class="bg-[#ECFDF5] border border-[#6EE7B7] rounded-lg p-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#065F46]">
                                    <svg class="w-4 h-4 text-[#065F46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Kembalian:</span>
                                </div>
                                <div class="font-extrabold text-base text-[#065F46]" 
                                     x-text="formatRupiah(calculateChange())">
                                    Rp 5.900
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Primary Action Buttons (Ergonomic Touch >= 44px) --}}
                    <div class="space-y-2 pt-1">
                        {{-- Big CTA Button: Konfirmasi Lunas & Cetak Struk --}}
                        <button type="button" 
                                @click="confirmPaymentAndPrint()"
                                :disabled="currentSaung.status === 'empty' || (currentSaung.status === 'waiting_cash' && cashReceived < currentSaung.total)"
                                class="w-full py-3.5 px-4 rounded-xl bg-[#0B4D2B] hover:bg-[#0A4A28] disabled:bg-gray-400 disabled:cursor-not-allowed text-[#FBF3DD] font-extrabold text-xs tracking-wider uppercase flex items-center justify-center gap-2 shadow-sm transition active:scale-98 min-h-[48px]">
                            <svg class="w-4 h-4 text-[#FBF3DD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>KONFIRMASI LUNAS & CETAK STRUK (ENTER)</span>
                        </button>

                        {{-- Secondary Action Buttons Row --}}
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" 
                                    @click="reprintReceipt()"
                                    :disabled="currentSaung.status === 'empty'"
                                    class="py-2.5 px-3 rounded-lg bg-[#E0B24E]/15 hover:bg-[#E0B24E]/25 border border-[#E0B24E]/40 text-[#C9982F] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 min-h-[44px]">
                                <span>🖨️</span>
                                <span>Cetak Ulang [F3]</span>
                            </button>

                            <button type="button" 
                                    @click="voidOrder()"
                                    :disabled="currentSaung.status === 'empty'"
                                    class="py-2.5 px-3 rounded-lg bg-[#FEF2F2] hover:bg-red-100 border border-[#C1442C]/30 text-[#C1442C] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 min-h-[44px]">
                                <span>⊗</span>
                                <span>Batalkan Order [F9]</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Mini Preview of Thermal Receipt 80mm ESC/POS --}}
                <div class="bg-white border border-[#C8C2B3]/60 rounded-xl overflow-hidden shadow-xs">
                    {{-- Header Summary --}}
                    <div class="bg-[#F3EEDF] px-3 py-2 flex items-center justify-between text-xs font-bold text-[#2B2A26] border-b border-[#C8C2B3]/30">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Pratinjau ESC/POS 80mm</span>
                        </div>
                        <span class="font-mono text-[10px] text-[#6B6A63]" x-text="currentSaung.orderId + ' • ' + currentSaung.id"></span>
                    </div>

                    {{-- Thermal Paper Simulation Container --}}
                    <div class="p-3 bg-[#FAF7F0] flex justify-center">
                        <div class="w-full bg-white p-3 rounded shadow-xs font-mono text-[10px] leading-tight text-[#262626] border border-[#E5E0D5]"
                             :class="isPrinting ? 'receipt-printed' : ''">
                            
                            {{-- Thermal Header --}}
                            <div class="text-center space-y-0.5 pb-2">
                                <div class="font-bold text-xs uppercase text-black tracking-wider">SAUNG SITU AWI • CIWIDEY</div>
                                <div class="text-[9px] text-[#737373]">Jl. Raya Situ Awi Km. 4 Ciwidey - Bandung</div>
                            </div>

                            <div class="border-t border-dashed border-[#A3A3A3] my-1.5"></div>

                            {{-- Receipt Meta --}}
                            <div class="flex justify-between text-[9px]">
                                <span x-text="'No: ' + (currentSaung.orderId || '#SA-20260915-084')"></span>
                                <span>Kasir: Teh Neng</span>
                            </div>
                            <div class="flex justify-between text-[9px]">
                                <span x-text="'Meja: ' + (currentSaung.name || 'SAUNG LB-01')"></span>
                                <span x-text="'15/09 ' + (currentSaung.qrTime || '12:04')"></span>
                            </div>

                            <div class="border-t border-dashed border-[#A3A3A3] my-1.5"></div>

                            {{-- Items Table --}}
                            <div class="space-y-1">
                                <template x-for="item in currentSaung.items" :key="item.name">
                                    <div class="flex justify-between">
                                        <span class="truncate max-w-[190px]" x-text="item.qty + 'x ' + item.name"></span>
                                        <span x-text="formatNumber(item.price * item.qty)"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="border-t border-dashed border-[#A3A3A3] my-1.5"></div>

                            {{-- Totals --}}
                            <div class="flex justify-between text-[9px]">
                                <span>Subtotal:</span>
                                <span x-text="formatNumber(currentSaung.subtotal || 131000)"></span>
                            </div>
                            <div class="flex justify-between text-[9px]">
                                <span>Pajak PB1 (10%):</span>
                                <span x-text="formatNumber(currentSaung.tax || 13100)"></span>
                            </div>

                            <div class="border-t border-dashed border-[#A3A3A3] my-1 pt-1 flex justify-between font-bold text-xs text-black">
                                <span>TOTAL:</span>
                                <span x-text="formatRupiah(currentSaung.total || 144100)"></span>
                            </div>

                            <div class="flex justify-between text-[9px] pt-0.5">
                                <span x-text="'Tunai: ' + formatNumber(cashReceived || 150000)"></span>
                                <span class="font-bold text-[#00341A]" x-text="'Kemb: ' + formatRupiah(calculateChange())"></span>
                            </div>

                            <div class="border-t border-dashed border-[#A3A3A3] my-1.5"></div>

                            <div class="text-center text-[9px] text-[#737373] pt-0.5 tracking-wider">
                                *** HATUR NUHUN PISAN ***
                            </div>
                        </div>
                    </div>
                </div>

            </aside>
        </div>

    </main>

    {{-- ========================================================================= --}}
    {{-- BOTTOM STATUS FOOTER BAR                                                  --}}
    {{-- ========================================================================= --}}
    <footer class="bg-[#F3EEDF] border-t border-[#C8C2B3]/60 px-6 py-2 text-xs text-[#6B6A63] font-medium z-30">
        <div class="max-w-[1366px] w-full mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-3 overflow-x-auto scrollbar-none">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#059669]"></span>
                    <span>Terminal: <strong class="font-mono text-[#2B2A26]">POS-KSR-01</strong></span>
                </div>
                <span>•</span>
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Printer: EPSON TM-T82 (Ready)</span>
                </div>
                <span>•</span>
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>WebSocket Latency: <strong class="font-mono text-[#065F46]" x-text="latencyString">14ms</strong></span>
                </div>
                <span>•</span>
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-[#C9982F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    <span>Sistem Pesanan: 100% QR Tamu (FR-003)</span>
                </div>
            </div>

            <div class="text-[11px] text-[#6B6A63]">
                © 2026 Saung Situ Awi Ciwidey • Sistem Kasir Lesehan Nusantara v1.3
            </div>
        </div>
    </footer>

    {{-- ========================================================================= --}}
    {{-- TOAST NOTIFICATION STACK                                                  --}}
    {{-- ========================================================================= --}}
    <div x-show="toast.visible" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-6 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-6 opacity-0"
         class="fixed bottom-12 right-6 z-50 max-w-sm pointer-events-none">
        <div class="rounded-xl p-3.5 shadow-xl border flex items-center gap-3 pointer-events-auto"
             :class="{
                 'bg-[#0B4D2B] text-white border-[#0A4A28]': toast.type === 'success',
                 'bg-[#FBF3DD] text-[#00341A] border-[#E0B24E]': toast.type === 'gold',
                 'bg-[#FEF2F2] text-[#991B1B] border-[#FCA5A5]': toast.type === 'danger'
             }">
            <span class="text-lg" x-text="toast.icon"></span>
            <div>
                <div class="font-bold text-xs" x-text="toast.title"></div>
                <div class="text-[11px] opacity-90" x-text="toast.message"></div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- LOCK SCREEN MODAL                                                         --}}
    {{-- ========================================================================= --}}
    <div x-show="isLocked" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 bg-[#00341A]/90 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center space-y-4 shadow-2xl border border-[#E0B24E]">
            <div class="w-14 h-14 rounded-full bg-[#FAF7F0] border border-[#C8C2B3] flex items-center justify-center mx-auto text-[#0B4D2B]">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-lg text-[#00341A]">Terminal Terkunci</h3>
                <p class="text-xs text-[#6B6A63]">Operator: Teh Neng Santi (Shift 1 Kasir Siang)</p>
            </div>
            <button type="button" 
                    @click="isLocked = false"
                    class="w-full py-3 rounded-xl bg-[#0B4D2B] hover:bg-[#0A4A28] text-white font-bold text-xs uppercase tracking-wide transition active:scale-95">
                Buka Kunci Terminal
            </button>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ALPINE APPLICATION LOGIC & STATE                                          --}}
    {{-- ========================================================================= --}}
    <script>
        function kasirPosApp() {
            return {
                liveTimeString: '12:08:25 WIB',
                latencyString: '14ms',
                activeFilter: 'all', // 'all', 'waiting_cash', 'paid', 'empty'
                selectedSaungId: 'LB-01',
                cashReceived: 150000,
                isPrinting: false,
                isLocked: false,

                // IoT Alert Banner State
                iotAlert: {
                    active: true,
                    saungId: 'LB-03',
                    title: 'PANGGILAN PELAYAN AKTIF: SAUNG LB-03 (Lesehan Bawah)',
                    subtitle: 'Sensor sentuh TTP223 terpicu (12:08:14 WIB) • Buzzer aktif 3 detik • Tamu butuh bantuan pelayan'
                },

                toast: {
                    visible: false,
                    type: 'success',
                    icon: '✓',
                    title: '',
                    message: '',
                    timeout: null
                },

                // 11 Saung Master Overview (Cluster 1: LB-01..05, Cluster 2: LA-01..02, Cluster 3: KA-01..04)
                saungs: [
                    // Cluster 1
                    {
                        id: 'LB-01',
                        cluster: 'LB',
                        name: 'SAUNG LB-01',
                        tableName: 'Lesehan Bawah 01',
                        capacity: 'Lesehan Bawah • Kap. 6 org',
                        status: 'waiting_cash', // 'waiting_cash', 'paid', 'empty'
                        paymentType: null,
                        hasAlert: false,
                        orderId: '#SA-20260915-084',
                        customer: 'Farhan Pratama',
                        qrTime: '12:04 WIB',
                        elapsedTime: '18 mnt lalu',
                        itemCount: 5,
                        totalPortions: 5,
                        subtotal: 131000,
                        tax: 13100,
                        total: 144100,
                        items: [
                            { name: 'Gurame Bakar Cobek', qty: 1, price: 85000, note: '🌶️ Catatan: Bumbu pedas manis, lalap sambal terasi', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Liwet Kastrol Mini', qty: 2, price: 15000, note: '🌾 Catatan: Ikan teri medan, sereh & petai', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Kelapa Jeruk Kasturi', qty: 2, price: 8000, note: '🧊 Catatan: Jeruk peras dipisah, sedikit gula', noteColor: 'text-[#047857]' },
                        ]
                    },
                    {
                        id: 'LB-02',
                        cluster: 'LB',
                        name: 'SAUNG LB-02',
                        tableName: 'Lesehan Bawah 02',
                        capacity: 'Lesehan Bawah • Kap. 6 org',
                        status: 'paid',
                        paymentType: 'qris',
                        hasAlert: false,
                        orderId: '#SA-20260915-081',
                        customer: 'Keluarga Ibu Dewi',
                        qrTime: '11:45 WIB',
                        elapsedTime: '35 mnt lalu',
                        itemCount: 6,
                        totalPortions: 6,
                        subtotal: 195454,
                        tax: 19546,
                        total: 215000,
                        items: [
                            { name: 'Gurame Asam Manis', qty: 1, price: 90000, note: '🌶️ Saus asam manis pisah', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Liwet Kastrol Jumbo', qty: 2, price: 35000, note: '🌾 Porsi 4 orang, petai bakar', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Teh Manis', qty: 3, price: 10000, note: '🧊 Less sugar', noteColor: 'text-[#047857]' },
                        ]
                    },
                    {
                        id: 'LB-03',
                        cluster: 'LB',
                        name: 'SAUNG LB-03',
                        tableName: 'Lesehan Bawah 03',
                        capacity: 'Lesehan Bawah • Kap. 8 org',
                        status: 'waiting_cash',
                        paymentType: null,
                        hasAlert: true, // Triggered Alert
                        orderId: '#SA-20260915-085',
                        customer: 'Pak Ridwan Kamil',
                        qrTime: '12:08 WIB',
                        elapsedTime: '8 mnt lalu',
                        itemCount: 7,
                        totalPortions: 7,
                        subtotal: 261818,
                        tax: 26182,
                        total: 288000,
                        items: [
                            { name: 'Gurame Bakar Cobek Super', qty: 2, price: 95000, note: '🌶️ Extra pedas cabai rawit merah', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Liwet Kastrol Mini', qty: 3, price: 15000, note: '🌾 Ikan teri & kemangi', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Kelapa Muda Jeruk', qty: 2, price: 12000, note: '🧊 Segar dingin', noteColor: 'text-[#047857]' },
                        ]
                    },
                    {
                        id: 'LB-04',
                        cluster: 'LB',
                        name: 'SAUNG LB-04',
                        tableName: 'Lesehan Bawah 04',
                        capacity: 'Lesehan Bawah • Kap. 6 org',
                        status: 'paid',
                        paymentType: 'cash',
                        hasAlert: false,
                        orderId: '#SA-20260915-079',
                        customer: 'Bpk. Hendra Gunawan',
                        qrTime: '11:30 WIB',
                        elapsedTime: '48 mnt lalu',
                        itemCount: 4,
                        totalPortions: 4,
                        subtotal: 89090,
                        tax: 8910,
                        total: 98000,
                        items: [
                            { name: 'Ayam Bakar Parahyangan', qty: 2, price: 32000, note: '🍗 Bumbu meresap manis gurih', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Putih Kastrol', qty: 2, price: 10000, note: '🌾 Hangat pulen', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Jeruk Nipis', qty: 2, price: 7000, note: '🧊 Dingin segar', noteColor: 'text-[#047857]' },
                        ]
                    },
                    {
                        id: 'LB-05',
                        cluster: 'LB',
                        name: 'SAUNG LB-05',
                        tableName: 'Lesehan Bawah 05',
                        capacity: 'Lesehan Bawah • Kap. 4 org',
                        status: 'empty',
                        paymentType: null,
                        hasAlert: false,
                        orderId: '-',
                        customer: '-',
                        qrTime: '-',
                        elapsedTime: '-',
                        itemCount: 0,
                        totalPortions: 0,
                        subtotal: 0,
                        tax: 0,
                        total: 0,
                        items: []
                    },

                    // Cluster 2
                    {
                        id: 'LA-01',
                        cluster: 'LA',
                        name: 'SAUNG LA-01',
                        tableName: 'Lesehan Atas 01',
                        capacity: 'Lesehan Atas • Kap. 10 org',
                        status: 'waiting_cash',
                        paymentType: null,
                        hasAlert: false,
                        orderId: '#SA-20260915-082',
                        customer: 'Rombongan Bapenda',
                        qrTime: '11:55 WIB',
                        elapsedTime: '22 mnt lalu',
                        itemCount: 12,
                        totalPortions: 12,
                        subtotal: 422727,
                        tax: 42273,
                        total: 465000,
                        items: [
                            { name: 'Gurame Bakar Cobek (2 Porsi)', qty: 2, price: 85000, note: '🌶️ Pedas sedang', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Liwet Kastrol Komplit', qty: 4, price: 45000, note: '🌾 Porsi rombongan besar', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Karedok Leunca Sunda', qty: 3, price: 16000, note: '🥗 Sayur segar terasi bakar', noteColor: 'text-[#047857]' },
                        ]
                    },
                    {
                        id: 'LA-02',
                        cluster: 'LA',
                        name: 'SAUNG LA-02',
                        tableName: 'Lesehan Atas 02',
                        capacity: 'Lesehan Atas • Kap. 8 org',
                        status: 'paid',
                        paymentType: 'qris',
                        hasAlert: false,
                        orderId: '#SA-20260915-080',
                        customer: 'Ayu Lestari',
                        qrTime: '11:38 WIB',
                        elapsedTime: '40 mnt lalu',
                        itemCount: 5,
                        totalPortions: 5,
                        subtotal: 156363,
                        tax: 15637,
                        total: 172000,
                        items: [
                            { name: 'Nasi Liwet Kastrol Mini', qty: 2, price: 45000, note: '🌾 Teri pete komplit', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Kelapa Jeruk', qty: 2, price: 18000, note: '🧊 Manis sedang', noteColor: 'text-[#047857]' },
                            { name: 'Karedok Leunca', qty: 1, price: 16000, note: '🥗 Sedikit kencur', noteColor: 'text-[#C9982F]' },
                        ]
                    },

                    // Cluster 3
                    {
                        id: 'KA-01',
                        cluster: 'KA',
                        name: 'SAUNG KA-01',
                        tableName: 'Kursi Atas 01',
                        capacity: 'Kursi Atas • Kap. 4 org',
                        status: 'paid',
                        paymentType: 'qris',
                        hasAlert: false,
                        orderId: '#SA-20260915-083',
                        customer: 'Dimas Anggara',
                        qrTime: '11:50 WIB',
                        elapsedTime: '29 mnt lalu',
                        itemCount: 3,
                        totalPortions: 3,
                        subtotal: 101818,
                        tax: 10182,
                        total: 112000,
                        items: [
                            { name: 'Gurame Bakar Situ', qty: 1, price: 68000, note: '🌶️ Bumbu rempah manis', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Liwet Mini', qty: 1, price: 18000, note: '🌾 Pulen gurih', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Kelapa Jeruk', qty: 1, price: 15000, note: '🧊 Es batu pisah', noteColor: 'text-[#047857]' },
                        ]
                    },
                    {
                        id: 'KA-02',
                        cluster: 'KA',
                        name: 'SAUNG KA-02',
                        tableName: 'Kursi Atas 02',
                        capacity: 'Kursi Atas • Kap. 4 org',
                        status: 'empty',
                        paymentType: null,
                        hasAlert: false,
                        orderId: '-',
                        customer: '-',
                        qrTime: '-',
                        elapsedTime: '-',
                        itemCount: 0,
                        totalPortions: 0,
                        subtotal: 0,
                        tax: 0,
                        total: 0,
                        items: []
                    },
                    {
                        id: 'KA-03',
                        cluster: 'KA',
                        name: 'SAUNG KA-03',
                        tableName: 'Kursi Atas 03',
                        capacity: 'Kursi Atas • Kap. 4 org',
                        status: 'empty',
                        paymentType: null,
                        hasAlert: false,
                        orderId: '-',
                        customer: '-',
                        qrTime: '-',
                        elapsedTime: '-',
                        itemCount: 0,
                        totalPortions: 0,
                        subtotal: 0,
                        tax: 0,
                        total: 0,
                        items: []
                    },
                    {
                        id: 'KA-04',
                        cluster: 'KA',
                        name: 'SAUNG KA-04',
                        tableName: 'Kursi Atas 04',
                        capacity: 'Kursi Atas • Kap. 6 org',
                        status: 'paid',
                        paymentType: 'cash',
                        hasAlert: false,
                        orderId: '#SA-20260915-078',
                        customer: 'Bu Rina Wati',
                        qrTime: '11:24 WIB',
                        elapsedTime: '55 mnt lalu',
                        itemCount: 4,
                        totalPortions: 4,
                        subtotal: 113636,
                        tax: 11364,
                        total: 125000,
                        items: [
                            { name: 'Gurame Bakar Bumbu Cobek', qty: 1, price: 75000, note: '🌶️ Pedas gurih', noteColor: 'text-[#C9982F]' },
                            { name: 'Nasi Liwet Kastrol', qty: 1, price: 25000, note: '🌾 Ikan teri renyah', noteColor: 'text-[#0B4D2B]' },
                            { name: 'Es Kelapa Jeruk Purut', qty: 2, price: 12500, note: '🧊 Segar asam manis', noteColor: 'text-[#047857]' },
                        ]
                    }
                ],

                initApp() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    // Jitter latency simulation
                    setInterval(() => {
                        const ms = Math.floor(12 + Math.random() * 5);
                        this.latencyString = `${ms}ms`;
                    }, 4000);
                },

                updateClock() {
                    const now = new Date();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    this.liveTimeString = `${hours}:${minutes}:${seconds} WIB`;
                },

                get currentSaung() {
                    return this.saungs.find(s => s.id === this.selectedSaungId) || this.saungs[0];
                },

                get occupiedCount() {
                    return this.saungs.filter(s => s.status !== 'empty').length;
                },

                getClusterSaungs(prefix) {
                    return this.saungs.filter(s => s.cluster === prefix);
                },

                isSaungVisible(saung) {
                    if (this.activeFilter === 'all') return true;
                    if (this.activeFilter === 'waiting_cash') return saung.status === 'waiting_cash';
                    if (this.activeFilter === 'paid') return saung.status === 'paid';
                    if (this.activeFilter === 'empty') return saung.status === 'empty';
                    return true;
                },

                selectSaung(saung) {
                    this.selectedSaungId = saung.id;
                    if (saung.status === 'waiting_cash') {
                        // Set default cash suggestion
                        this.cashReceived = 150000;
                    }
                },

                setQuickCash(amount) {
                    this.cashReceived = amount;
                },

                calculateChange() {
                    if (!this.currentSaung || this.currentSaung.status !== 'waiting_cash') return 0;
                    const change = this.cashReceived - this.currentSaung.total;
                    return change > 0 ? change : 0;
                },

                formatRupiah(val) {
                    return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
                },

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('id-ID');
                },

                // Action Operations
                confirmPaymentAndPrint() {
                    if (this.currentSaung.status !== 'waiting_cash') return;

                    const saung = this.currentSaung;
                    saung.status = 'paid';
                    saung.paymentType = 'cash';

                    // Trigger thermal printer animation
                    this.isPrinting = true;
                    setTimeout(() => { this.isPrinting = false; }, 800);

                    this.showToast('success', '✓', 'Transaksi Lunas!', `Pembayaran tunai ${saung.name} berhasil. Struk thermal dicetak.`);
                },

                reprintReceipt() {
                    this.isPrinting = true;
                    setTimeout(() => { this.isPrinting = false; }, 800);
                    this.showToast('gold', '🖨️', 'Cetak Ulang Struk', `Mencetak ulang salinan struk thermal ${this.currentSaung.name}.`);
                },

                voidOrder() {
                    if (confirm(`Yakin ingin membatalkan pesanan ${this.currentSaung.name} (${this.currentSaung.orderId})?`)) {
                        this.currentSaung.status = 'empty';
                        this.currentSaung.items = [];
                        this.currentSaung.total = 0;
                        this.showToast('danger', '⊗', 'Pesanan Dibatalkan', `Order ${this.currentSaung.name} telah dibatalkan & dikosongkan.`);
                    }
                },

                dismissAlert() {
                    this.iotAlert.active = false;
                    const lb3 = this.saungs.find(s => s.id === 'LB-03');
                    if (lb3) lb3.hasAlert = false;
                    this.showToast('success', '✓', 'Buzzer Dimatikan', 'Panggilan meja Saung LB-03 telah ditandai selesai.');
                },

                assignWaiter() {
                    this.showToast('gold', '🔔', 'Pelayan Ditugaskan', 'Kang Asep telah dikirim via Mobile POS menuju Saung LB-03.');
                    this.dismissAlert();
                },

                lockTerminal() {
                    this.isLocked = true;
                },

                handleKeyboardShortcuts(e) {
                    // Enter key triggers confirmation
                    if (e.key === 'Enter' && !this.isLocked && this.currentSaung.status === 'waiting_cash') {
                        e.preventDefault();
                        this.confirmPaymentAndPrint();
                    }
                    // F3 key triggers reprint
                    if (e.key === 'F3') {
                        e.preventDefault();
                        this.reprintReceipt();
                    }
                    // F9 key triggers void
                    if (e.key === 'F9') {
                        e.preventDefault();
                        this.voidOrder();
                    }
                },

                showToast(type, icon, title, message) {
                    clearTimeout(this.toast.timeout);
                    this.toast = {
                        visible: true,
                        type: type,
                        icon: icon,
                        title: title,
                        message: message,
                        timeout: null
                    };
                    this.toast.timeout = setTimeout(() => {
                        this.toast.visible = false;
                    }, 4000);
                }
            };
        }
    </script>
</body>
</html>

