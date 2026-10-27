<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="realtime-version-url" content="{{ route('realtime.version') }}">
    <meta name="realtime-version" content="{{ \App\Support\Realtime::version() }}">
    <title>Terminal Kasir POS • Saung Situ Awi Ciwidey</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script>
            window.__CHANNELS__ = ['kasir', 'admin', 'menu'];
        </script>
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

        .thermal-tear-top {
            background: radial-gradient(circle at 10px -5px, transparent 12px, #FFFFFF 13px);
            background-size: 20px 20px;
        }
        .thermal-tear-bottom {
            background: radial-gradient(circle at 10px 15px, transparent 12px, #FFFFFF 13px);
            background-size: 20px 20px;
        }

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
      @realtime.window="refreshKasirState()"
      x-cloak>

    <header class="sticky top-0 z-40 bg-[#FBF8F1]/95 backdrop-blur-md border-b border-[#C8C2B3]/60 shadow-[0_2px_8px_-2px_rgba(26,38,20,0.06)]">
        <div class="max-w-[1366px] w-full mx-auto px-6 py-3.5 flex items-center justify-between gap-6 min-h-[68px]">

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

            <div class="flex items-center gap-2.5">

                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F3EEDF] border border-[#C8C2B3]/50 text-xs text-[#00341A] font-semibold">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
                    </span>
                    <span>Koneksi Server: Online &amp; Synced</span>
                </div>

                <div class="flex items-center gap-2 px-3 py-1 rounded-xl bg-[#F3EEDF] border border-[#C8C2B3]/50 text-xs">
                    <div class="w-6 h-6 rounded-lg bg-[#0B4D2B] text-white flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="text-left leading-tight">
                        <div class="font-bold text-[#2B2A26]">{{ auth()->user()->nama ?? 'Kasir' }}</div>
                        <div class="text-[10px] text-[#6B6A63]">Shift 1 Kasir Siang</div>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F3EEDF] border border-[#C8C2B3]/50 text-xs font-extrabold text-[#0B4D2B]">
                    <svg class="w-3.5 h-3.5 text-[#C9982F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span x-text="occupiedCount + '/' + saungs.length + ' Saung Aktif'">Memuat data saung</span>
                </div>

                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#EAE4D3]/80 border border-[#C8C2B3]/50 text-xs font-mono font-bold text-[#00341A]">
                    <svg class="w-3.5 h-3.5 text-[#C9982F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="liveTimeString">12:08:25 WIB</span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FDF5E6] hover:bg-[#F5ECD8] border border-[#E0B24E]/40 hover:border-[#E0B24E] active:scale-95 transition text-xs font-bold text-[#7A5C0F] whitespace-nowrap" title="Keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>

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

    <main class="flex-1 max-w-[1366px] w-full mx-auto px-6 py-4 flex flex-col gap-3">

        @if (session('error'))
        <div class="bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] rounded-xl px-4 py-3 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if (session('sukses'))
        <div class="bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46] rounded-xl px-4 py-3 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
        @endif

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
                            PANGGILAN IOT
                        </span>
                        <h2 class="font-bold text-xs sm:text-sm text-[#00341A]" x-text="iotAlert.title">
                            Memuat panggilan meja
                        </h2>
                    </div>
                    <p class="text-[11px] text-[#6B6A63]" x-text="iotAlert.subtitle">
                        Memuat detail panggilan
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
                    <span>Tugaskan Pelayan</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

            <section class="lg:col-span-8 flex flex-col gap-3.5">

                <div class="bg-[#FAF7F0] border border-[#C8C2B3]/60 rounded-xl p-2.5 shadow-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none">

                        <button type="button"
                                @click="activeFilter = 'all'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition active:scale-95 shrink-0 min-h-[38px]"
                                :class="activeFilter === 'all'
                                    ? 'bg-[#0B4D2B] text-white shadow-xs'
                                    : 'bg-[#F3EEDF] text-[#2B2A26] hover:bg-[#EAE4D3]'">
                            <span>Semua Saung</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold"
                                  :class="activeFilter === 'all' ? 'bg-[#00341A] text-[#B1F1C2]' : 'bg-[#DFD7C4] text-[#6B6A63]'"
                                   x-text="saungs.length">
                            </span>
                        </button>

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
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold bg-[#E0B24E]/20 text-[#C9982F] border border-[#E0B24E]/30" x-text="waitingCount">
                            </span>
                        </button>

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
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold bg-[#D1FAE5] text-[#065F46]" x-text="paidCount">
                            </span>
                        </button>

                        <button type="button"
                                @click="activeFilter = 'empty'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold transition active:scale-95 shrink-0 min-h-[38px]"
                                :class="activeFilter === 'empty'
                                    ? 'bg-white border border-[#C8C2B3] text-[#2B2A26] font-bold shadow-xs'
                                    : 'bg-[#F3EEDF] text-[#6B6A63] hover:bg-[#EAE4D3]'">
                            <span class="w-2 h-2 rounded-full bg-[#7A7E6F]"></span>
                            <span>Kosong / Available</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full font-mono text-[10px] font-bold bg-[#DFD7C4] text-[#6B6A63]" x-text="emptyCount">
                            </span>
                        </button>
                    </div>

                    <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-[#6B6A63] shrink-0 pr-1">
                        <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Pesanan via Scan QR Meja</span>
                    </div>
                    <a href="{{ route('kasir.pesanan.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition active:scale-95 shrink-0 min-h-[38px] bg-[#F3EEDF] text-[#2B2A26] hover:bg-[#EAE4D3]">
                        <span>Tambah Pesanan</span>
                    </a>
                </div>

                <div class="space-y-4">
                    @include('kasir.partials.cluster-saung', [
                        'prefix' => 'LB',
                        'judul' => 'Cluster 1: Lesehan Bawah (5 Saung Lesehan)',
                        'warna' => 'bg-[#E0B24E]',
                    ])

                    @include('kasir.partials.cluster-saung', [
                        'prefix' => 'LA',
                        'judul' => 'Cluster 2: Lesehan Atas (2 Saung Pemandangan Kebun)',
                        'warna' => 'bg-[#059669]',
                    ])

                    @include('kasir.partials.cluster-saung', [
                        'prefix' => 'KA',
                        'judul' => 'Cluster 3: Kursi Atas (4 Area Meja Kursi)',
                        'warna' => 'bg-[#0B4D2B]',
                    ])
                </div>
            </section>

            <aside class="lg:col-span-4 sticky top-20 flex flex-col gap-3">

                <div class="bg-white border border-[#C8C2B3]/60 rounded-xl p-4 shadow-[0_6px_16px_-4px_rgba(26,38,20,0.08)] space-y-3">

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
                                      'bg-[#E0B24E]/20 text-[#8A5A00] border border-[#E0B24E]/40': currentSaung.status === 'waiting_qris',
                                      'bg-[#ECFDF5] text-[#065F46] border border-[#6EE7B7]': currentSaung.status === 'paid',
                                      'bg-[#EAE4D3] text-[#6B6A63]': currentSaung.status === 'empty'
                                  }"
                                  x-text="currentSaung.status === 'empty' ? 'Kosong' : (currentSaung.status === 'paid' ? 'Lunas' : (currentSaung.status === 'waiting_qris' ? 'QRIS' : 'Aktif'))">
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

                                <template x-if="currentTable && currentTable.orders && currentTable.orders.length > 1">
                                    <div class="mt-2 rounded-lg border border-[#E0B24E]/50 bg-[#FBF3DD]/50 p-2 space-y-1.5">
                                        <div class="flex items-center justify-between text-[10px] font-extrabold uppercase tracking-wide text-[#7A5C0F]">
                                            <span x-text="'Pilih Pesanan • ' + currentTable.orders.length + ' order'"></span>
                                            <span class="font-mono text-[9px] text-[#991B1B]" x-text="currentTable.unpaidOrderCount + ' belum bayar'"></span>
                                        </div>
                                        <div class="grid grid-cols-1 gap-1">
                                            <template x-for="(order, orderIndex) in currentTable.orders" :key="order.kodePesanan">
                                                <button type="button"
                                                        @click="selectOrder(order.kodePesanan)"
                                                        class="w-full rounded-lg border px-2 py-1.5 text-left transition active:scale-[0.99]"
                                                        :class="selectedOrderKode === order.kodePesanan
                                                            ? 'bg-white border-[#0B4D2B] ring-1 ring-[#0B4D2B]/20'
                                                            : 'bg-white/70 border-[#C8C2B3]/70 hover:bg-white'">
                                                    <div class="flex items-center justify-between gap-2 text-[10px]">
                                                        <span class="font-extrabold text-[#00341A]" x-text="'Order ' + (orderIndex + 1)"></span>
                                                        <span class="font-mono font-bold text-[#00341A]" x-text="formatRupiah(order.total)"></span>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-2 mt-0.5">
                                                        <span class="font-mono text-[9px] text-[#6B6A63] truncate" x-text="order.kodePesanan"></span>
                                                        <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-extrabold"
                                                              :class="order.status === 'paid' ? 'bg-[#ECFDF5] text-[#065F46]' : 'bg-[#FEF2F2] text-[#991B1B]'"
                                                              x-text="order.status === 'paid' ? 'Lunas' : 'Belum bayar'"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <div class="mt-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-bold flex items-center gap-1.5"
                                     :class="currentSaung.status === 'waiting_cash'
                                         ? 'bg-[#FBF3DD] border-[#E0B24E]/40 text-[#C9982F]'
                                         : (currentSaung.status === 'waiting_qris'
                                             ? 'bg-[#FFF7E0] border-[#E0B24E]/50 text-[#8A5A00]'
                                             : (currentSaung.status === 'paid'
                                                 ? 'bg-[#ECFDF5] border-[#6EE7B7] text-[#065F46]'
                                                 : 'bg-[#E8F0E9] border-[#A7C4AB] text-[#0B4D2B]'))">
                                    <span class="w-2 h-2 rounded-full"
                                          :class="currentSaung.status === 'waiting_cash' || currentSaung.status === 'waiting_qris' ? 'bg-[#E0B24E] animate-pulse' : (currentSaung.status === 'paid' ? 'bg-[#059669]' : 'bg-[#0B4D2B]')"></span>
                                    <span x-text="currentSaung.status === 'waiting_cash'
                                        ? 'WAITING_CASH (Opsi Bayar Tunai di Kasir)'
                                        : (currentSaung.status === 'waiting_qris'
                                            ? 'MENUNGGU PEMBAYARAN QRIS'
                                            : (currentSaung.status === 'paid'
                                                ? 'SUDAH LUNAS (Transaksi Terverifikasi)'
                                                : 'PESANAN SEDANG DIPROSES'))"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="currentSaung.status === 'empty'">
                            <div class="py-2 text-center text-xs text-[#6B6A63]">
                                Tidak ada transaksi aktif di saung ini. Silakan pilih saung lain pada overview grid.
                            </div>
                        </template>
                    </div>

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

                    <template x-if="currentSaung.status !== 'empty'">
                        <div class="bg-[#F3EEDF] border border-[#C8C2B3]/40 rounded-lg p-2.5 text-xs space-y-1">
                            <div class="flex items-start justify-between gap-2 text-[#6B6A63]">
                                <span x-text="'Subtotal (' + currentSaung.totalPortions + ' porsi):'"></span>
                                <span class="font-mono font-semibold text-[#2B2A26]" x-text="formatRupiah(currentSaung.subtotal)"></span>
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

                    <template x-if="currentSaung.status === 'waiting_cash'">
                        <div class="space-y-2 pt-1 border-t border-[#C8C2B3]/30">
                            <label class="block text-[11px] font-bold text-[#6B6A63]">
                                Uang Diterima / Cash Received:
                            </label>

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

                            <div class="relative bg-white border-2 border-[#C8C2B3]/70 rounded-xl px-3 py-2 flex items-center justify-between shadow-xs">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded bg-[#FAF7F0] border border-[#C8C2B3]/50 text-[10px] font-extrabold text-[#6B6A63] tracking-wider uppercase">
                                        Rp
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-extrabold text-lg sm:text-xl text-[#00341A] tracking-tight"
                                          x-text="formatNumber(cashReceived)">
                                        0
                                    </span>
                                    <button type="button"
                                            @click="pressNumpad('clear')"
                                            x-show="cashReceived > 0"
                                            title="Reset Uang Diterima"
                                            class="p-1 rounded-md text-[#8A8980] hover:text-[#C1442C] hover:bg-[#FEF2F2] transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-1.5 pt-0.5 select-none">

                                <button type="button"
                                        @click="pressNumpad('1')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    1
                                </button>
                                <button type="button"
                                        @click="pressNumpad('2')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    2
                                </button>
                                <button type="button"
                                        @click="pressNumpad('3')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    3
                                </button>
                                <button type="button"
                                        @click="pressNumpad('clear')"
                                        class="h-10 rounded-lg bg-[#FEF2F2] hover:bg-red-100 border border-[#FCA5A5]/60 text-[#C1442C] font-mono font-extrabold text-sm shadow-xs active:scale-95 transition flex items-center justify-center tracking-wider"
                                        title="Reset ke 0">
                                    C
                                </button>

                                <button type="button"
                                        @click="pressNumpad('4')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    4
                                </button>
                                <button type="button"
                                        @click="pressNumpad('5')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    5
                                </button>
                                <button type="button"
                                        @click="pressNumpad('6')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    6
                                </button>
                                <button type="button"
                                        @click="pressNumpad('backspace')"
                                        class="h-10 rounded-lg bg-[#FAF7F0] hover:bg-[#EAE4D3] border border-[#C8C2B3]/70 text-[#6B6A63] hover:text-[#2B2A26] shadow-xs active:scale-95 transition flex items-center justify-center"
                                        title="Hapus 1 angka">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z"></path>
                                    </svg>
                                </button>

                                <button type="button"
                                        @click="pressNumpad('7')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    7
                                </button>
                                <button type="button"
                                        @click="pressNumpad('8')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    8
                                </button>
                                <button type="button"
                                        @click="pressNumpad('9')"
                                        class="h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    9
                                </button>
                                <button type="button"
                                        @click="pressNumpad('00')"
                                        class="h-10 rounded-lg bg-[#FAF7F0] hover:bg-[#EAE4D3] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-xs shadow-xs active:scale-95 transition flex items-center justify-center">
                                    00
                                </button>

                                <button type="button"
                                        @click="pressNumpad('0')"
                                        class="col-span-2 h-10 rounded-lg bg-white hover:bg-[#F3EEDF] border border-[#C8C2B3]/70 text-[#2B2A26] font-mono font-extrabold text-base shadow-xs active:scale-95 transition flex items-center justify-center">
                                    0
                                </button>
                                <button type="button"
                                        @click="pressNumpad('000')"
                                        class="col-span-2 h-10 rounded-lg bg-[#E0B24E]/15 hover:bg-[#E0B24E]/25 border border-[#E0B24E]/50 text-[#C9982F] font-mono font-extrabold text-xs shadow-xs active:scale-95 transition flex items-center justify-center">
                                    000
                                </button>
                            </div>

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

                    <div class="space-y-2 pt-1">

                        <template x-if="currentSaung.status === 'paid'">
                            <div class="bg-[#ECFDF5] border-2 border-[#6EE7B7] rounded-xl p-4 text-center space-y-1">
                                <div class="flex items-center justify-center gap-2 text-[#065F46] font-extrabold text-sm">
                                    <svg class="w-5 h-5 text-[#059669]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>TRANSAKSI SELESAI — SUDAH LUNAS</span>
                                </div>
                                <p class="text-[11px] text-[#047857]" x-text="'Metode: ' + (currentSaung.paymentType === 'qris' ? 'QRIS Midtrans' : 'Tunai Kasir')"></p>
                                <p class="text-[11px] text-[#6B6A63]">Pesanan sedang diproses di dapur / bar.</p>
                            </div>
                        </template>

                        <button type="button"
                                @click="confirmPayment()"
                                :disabled="currentSaung.status === 'empty' || currentSaung.status === 'paid' || (currentSaung.status === 'waiting_cash' && cashReceived < currentSaung.total)"
                                class="w-full py-3.5 px-4 rounded-xl bg-[#0B4D2B] hover:bg-[#0A4A28] disabled:bg-gray-400 disabled:cursor-not-allowed text-[#FBF3DD] font-extrabold text-xs tracking-wider uppercase flex items-center justify-center gap-2 shadow-sm transition active:scale-98 min-h-[48px]">
                            <svg class="w-4 h-4 text-[#FBF3DD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="currentSaung.status === 'paid' ? 'SUDAH LUNAS' : 'KONFIRMASI LUNAS (ENTER)'"></span>
                        </button>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                    @click="cetakStruk()"
                                    :disabled="!currentTable || !currentTable.hasUnprintedPaid"
                                    class="py-2.5 px-3 rounded-lg bg-[#E0B24E]/15 hover:bg-[#E0B24E]/25 border border-[#E0B24E]/40 text-[#C9982F] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed min-h-[44px]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Cetak Struk [F3]</span>
                                <span x-show="currentTable && currentTable.hasUnprintedPaid"
                                      class="px-1.5 py-0.5 rounded-full bg-[#C9982F] text-white text-[10px] font-extrabold"
                                      x-text="selectedReceiptCount + '/' + ((currentTable && currentTable.receiptOrders) ? currentTable.receiptOrders.length : 0)"></span>
                            </button>

                            <button type="button"
                                    @click="voidOrder()"
                                    :disabled="currentSaung.status === 'empty' || currentSaung.status === 'paid'"
                                    class="py-2.5 px-3 rounded-lg bg-[#FEF2F2] hover:bg-red-100 border border-[#C1442C]/30 text-[#C1442C] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Batalkan Order [F9]</span>
                            </button>
                        </div>

                        <button type="button"
                                @click="akhiriSesiMeja()"
                                :disabled="!currentTable || !currentTable.allOrdersPaid"
                                class="w-full py-2.5 px-3 rounded-lg bg-[#F3EEDF] hover:bg-[#EFE8D6] border border-[#C8C2B3]/60 text-[#2B2A26] font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 min-h-[44px] disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Akhiri Sesi Meja</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-[#C8C2B3]/60 rounded-xl overflow-hidden shadow-xs">

                    <div class="bg-[#F3EEDF] px-3 py-2 flex items-center justify-between text-xs font-bold text-[#2B2A26] border-b border-[#C8C2B3]/30">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Pilih Pesanan &amp; Cetak Struk</span>
                        </div>
                        <span class="font-mono text-[10px] text-[#6B6A63]"
                              x-text="(currentTable && currentTable.hasUnprintedPaid) ? (selectedReceiptCount + '/' + currentTable.receiptOrders.length + ' dipilih') : 'Belum ada struk'"></span>
                    </div>

                    <div class="p-3 bg-[#FAF7F0] space-y-2 max-h-[360px] overflow-y-auto">
                        <template x-if="currentTable && currentTable.hasUnprintedPaid">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-bold text-[#6B6A63]">Centang pesanan yang akan dicetak:</span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="pilihSemuaStruk()" class="px-2 py-1 rounded-md bg-white border border-[#C8C2B3]/70 text-[9px] font-bold text-[#404941] hover:bg-[#F3EEDF]">Semua</button>
                                        <button type="button" @click="kosongkanPilihanStruk()" class="px-2 py-1 rounded-md bg-white border border-[#C8C2B3]/70 text-[9px] font-bold text-[#404941] hover:bg-[#F3EEDF]">Kosongkan</button>
                                    </div>
                                </div>

                                <template x-for="(order, receiptIndex) in currentTable.receiptOrders" :key="order.kode_pesanan">
                                    <label class="block w-full rounded-lg border bg-white p-2.5 cursor-pointer transition"
                                           :class="selectedReceiptCodes.includes(order.kode_pesanan)
                                               ? 'border-[#0B4D2B] ring-1 ring-[#0B4D2B]/15'
                                               : 'border-[#C8C2B3]/70 opacity-80'">
                                        <div class="flex items-start gap-2">
                                            <input type="checkbox"
                                                   class="mt-0.5 h-3.5 w-3.5 rounded border-[#C8C2B3] text-[#0B4D2B] focus:ring-[#0B4D2B]"
                                                   :checked="selectedReceiptCodes.includes(order.kode_pesanan)"
                                                   @change="toggleStruk(order.kode_pesanan)">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-extrabold text-[#00341A]" x-text="'Order ' + (receiptIndex + 1) + ' • ' + (order.waktu_pesan || '-')"></span>
                                                    <span class="font-mono text-[10px] font-bold text-[#00341A]" x-text="formatRupiah(order.total_bayar)"></span>
                                                </div>
                                                <div class="font-mono text-[9px] text-[#6B6A63] break-all mt-0.5" x-text="order.kode_pesanan"></div>

                                                <div class="mt-1.5 border-t border-dashed border-[#C8C2B3]/70 pt-1.5 space-y-0.5">
                                                    <template x-for="(item, itemIndex) in order.items" :key="order.kode_pesanan + '-' + itemIndex">
                                                        <div class="flex items-center justify-between gap-2 text-[10px]">
                                                            <span class="text-[#2B2A26] truncate" x-text="item.qty + 'x ' + item.name"></span>
                                                            <span class="font-mono text-[#404941] shrink-0" x-text="formatNumber(item.price * item.qty)"></span>
                                                        </div>
                                                    </template>
                                                </div>

                                                <div class="mt-1.5 flex items-center justify-between text-[9px] text-[#6B6A63]">
                                                    <span x-text="'Subtotal: ' + formatRupiah(order.subtotal)"></span>
                                                    <span x-text="order.metode_pembayaran === 'qris' ? 'QRIS Midtrans' : 'Tunai Kasir'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </template>

                                <div class="rounded-lg bg-[#F3EEDF] border border-[#C8C2B3]/50 px-2.5 py-2 flex items-center justify-between text-[10px]">
                                    <span class="font-bold text-[#6B6A63]">Dipilih: <strong class="text-[#00341A]" x-text="selectedReceiptCount + ' order'"></strong></span>
                                    <span class="font-mono font-extrabold text-[#00341A]" x-text="formatRupiah((currentTable.receiptOrders || []).filter(o => selectedReceiptCodes.includes(o.kode_pesanan)).reduce((t, o) => t + Number(o.total_bayar || 0), 0))"></span>
                                </div>
                            </div>
                        </template>

                        <div x-show="!currentTable || !currentTable.hasUnprintedPaid"
                             x-cloak
                             class="w-full bg-white p-5 rounded border border-dashed border-[#C8C2B3] text-center space-y-2">
                            <div class="w-10 h-10 rounded-full bg-[#FAF7F0] border border-[#C8C2B3]/60 flex items-center justify-center mx-auto text-[#C9982F]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </div>
                            <div class="text-[11px] font-bold text-[#2B2A26]">Belum ada pesanan lunas yang belum dicetak</div>
                            <div class="text-[10px] text-[#6B6A63] leading-relaxed">
                                Setelah pembayaran dikonfirmasi, pesanan lunas akan muncul di sini.
                                Struk hanya memuat pesanan baru yang belum pernah dicetak.
                            </div>
                        </div>
                    </div>
                </div>

            </aside>
        </div>

    </main>

    <form id="formBayar" method="POST" action="{{ route('kasir.pembayaran.store') }}" class="hidden">
        @csrf
        <input type="hidden" name="kode_pesanan" :value="currentSaung.kodePesanan">
        <input type="hidden" name="metode_pembayaran" value="tunai">
        <input type="hidden" name="jumlah_bayar" :value="cashReceived">
    </form>
    <form id="formSelesaikanSesi" method="POST" :action="sesiTutupTemplate.replace('__MEJA__', currentSaung.id)" class="hidden">
        @csrf
        @method('PATCH')
    </form>
    <form id="formBatal" method="POST" :action="statusUrl" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status_pesanan" value="dibatalkan">
    </form>
    <form id="formLock" method="POST" action="{{ route('kasir.lock') }}" class="hidden">
        @csrf
    </form>

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
            <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
                <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <svg x-show="toast.type === 'gold'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div>
                <div class="font-bold text-xs" x-text="toast.title"></div>
                <div class="text-[11px] opacity-90" x-text="toast.message"></div>
            </div>
        </div>
    </div>

    <div x-show="confirmModal.open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-[#1C1C18]/45 backdrop-blur-sm"
         @keydown.escape.window="confirmModal.open = false">
        <div class="w-full max-w-md bg-white rounded-2xl border border-[#C8C2B3]/70 shadow-2xl overflow-hidden"
             @click.outside="confirmModal.open = false">
            <div class="px-5 py-4 border-b border-[#E5E0D5] flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     :class="confirmModal.type === 'danger' ? 'bg-[#FEF2F2] text-[#C1442C]' : 'bg-[#FBF3DD] text-[#C9982F]'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-display font-bold text-lg text-[#00341A]" x-text="confirmModal.title"></h3>
                    <p class="text-xs text-[#6B6A63] mt-1 leading-relaxed" x-text="confirmModal.message"></p>
                </div>
            </div>
            <div class="px-5 py-4 bg-[#FAF7F0] flex items-center justify-end gap-2">
                <button type="button"
                        @click="confirmModal.open = false"
                        class="px-4 py-2.5 rounded-xl border border-[#C8C2B3] bg-white text-[#404941] font-bold text-xs hover:bg-[#F3EEDF] transition">
                    Batal
                </button>
                <button type="button"
                        @click="jalankanKonfirmasi()"
                        class="px-4 py-2.5 rounded-xl text-white font-bold text-xs transition active:scale-[0.99]"
                        :class="confirmModal.type === 'danger' ? 'bg-[#C1442C] hover:bg-[#A93A26]' : 'bg-[#0B4D2B] hover:bg-[#0A4A28]'"
                        x-text="confirmModal.confirmLabel">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <script>
        function kasirPosApp() {
            return {
                liveTimeString: '{{ now('Asia/Jakarta')->format('H:i:s') }} WIB',
                latencyString: '14ms',
                activeFilter: 'all',
                selectedSaungId: @json($selectedSaungId),
                selectedOrderKode: null,
                selectedReceiptCodes: [],
                knownReceiptCodes: [],
                cashReceived: 0,
                _lastNumpadTime: 0,
                _stateLoading: false,
                isPrinting: false,
                isLocked: false,
                stateUrl: @json(route('kasir.state')),
                lockedUrl: @json(route('kasir.locked')),
                strukTandaiUrl: @json(route('kasir.struk.tandai')),
                statusUrlTemplate: @json(route('pesanan.status', ['kode' => '__KODE__'])),
                sesiTutupTemplate: @json(route('kasir.session.tutup', ['meja' => '__MEJA__'])),
                cashierName: @json(auth()->user()->nama ?? 'Kasir'),
                receiptHeader: @json($receiptHeader),
                receiptFooter: @json($receiptFooter),
                restoAddress: @json($restoAddress),
                restoPhone: @json($restoPhone),
                printLogo: @json($printLogo),
                logoSrc: @json($logoSrc),

                confirmModal: {
                    open: false,
                    type: 'danger',
                    title: '',
                    message: '',
                    confirmLabel: 'Ya, Lanjutkan',
                    action: null,
                },

                iotAlert: @json($iotAlertJson),

                toast: {
                    visible: false,
                    type: 'success',
                    title: '',
                    message: '',
                    timeout: null
                },

                saungs: @json($saungsJson),

                initApp() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);

                    setInterval(() => {
                        const ms = Math.floor(12 + Math.random() * 5);
                        this.latencyString = `${ms}ms`;
                    }, 4000);

                    this.pilihOrderAwal();
                    this.sinkronkanPilihanStruk(true);

                    // Fallback mandiri: tetap sinkron walau WebSocket mati.
                    setInterval(() => this.refreshKasirState(), 8000);
                },

                updateClock() {
                    this.liveTimeString = new Intl.DateTimeFormat('en-GB', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                    }).format(new Date()) + ' WIB';
                },

                get currentTable() {
                    return this.saungs.find(s => s.id === this.selectedSaungId) || this.saungs[0] || null;
                },

                get currentSaung() {
                    const table = this.currentTable;

                    if (!table) {
                        return {
                            status: 'empty',
                            items: [],
                            orders: [],
                            receiptOrders: [],
                            receiptOrderIds: [],
                            hasUnprintedPaid: false,
                            total: 0,
                        };
                    }

                    const orders = table.orders || [];
                    const order = orders.find(o => o.kodePesanan === this.selectedOrderKode)
                        || this.cariOrderDefault(table);

                    return order ? { ...table, ...order } : table;
                },

                get selectedReceiptCount() {
                    return (this.currentTable?.receiptOrders || [])
                        .filter(order => this.selectedReceiptCodes.includes(order.kode_pesanan))
                        .length;
                },

                get occupiedCount() {
                    return this.saungs.filter(s => s.status !== 'empty').length;
                },

                get waitingCount() {
                    return this.saungs.filter(s => s.status === 'waiting_cash').length;
                },

                get paidCount() {
                    return this.saungs.filter(s => s.status === 'paid').length;
                },

                get emptyCount() {
                    return this.saungs.filter(s => s.status === 'empty').length;
                },

                get statusUrl() {
                    if (!this.currentSaung || !this.currentSaung.kodePesanan) return '#';
                    return this.statusUrlTemplate.replace('__KODE__', this.currentSaung.kodePesanan);
                },

                getClusterSaungs(prefix) {
                    return this.saungs.filter(s => s.cluster === prefix);
                },

                cariOrderDefault(saung) {
                    const orders = saung?.orders || [];

                    if (orders.length === 0) return null;

                    return [...orders].reverse().find(order => order.status !== 'paid') || orders[orders.length - 1];
                },

                defaultOrderKode(saung) {
                    return this.cariOrderDefault(saung)?.kodePesanan || null;
                },

                pilihOrderAwal() {
                    const table = this.currentTable;
                    if (!table) return;

                    if (!this.selectedSaungId) {
                        this.selectedSaungId = table.id;
                    }

                    if (!(table.orders || []).some(order => order.kodePesanan === this.selectedOrderKode)) {
                        this.selectedOrderKode = this.defaultOrderKode(table);
                    }
                },

                isSaungVisible(saung) {
                    if (this.activeFilter === 'all') return true;
                    if (this.activeFilter === 'waiting_cash') return saung.status === 'waiting_cash';
                    if (this.activeFilter === 'paid') return saung.status === 'paid';
                    if (this.activeFilter === 'empty') return saung.status === 'empty';
                    return true;
                },

                saungCardClass(saung) {
                    if (saung.hasAlert) {
                        return 'bg-[#FFFBEB]/40 border-2 border-[#C1442C]/70 shadow-[0_0_16px_2px_rgba(193,68,44,0.25)] animate-alert-pulse';
                    }

                    if (this.selectedSaungId === saung.id) {
                        return 'bg-[#FBF3DD]/40 border-2 border-[#0B4D2B] shadow-[0_0_0_2px_#E0B24E,0_6px_16px_-4px_rgba(26,38,20,0.08)] ring-2 ring-[#E0B24E]';
                    }

                    if (saung.status === 'empty') {
                        return 'bg-[#FAF7F0]/60 border border-dashed border-[#C8C2B3]/70 opacity-80 hover:opacity-100';
                    }

                    return 'bg-white border border-[#C8C2B3]/60 hover:shadow-md';
                },

                clusterInfo(prefix) {
                    const list = this.getClusterSaungs(prefix);
                    const isi = list.filter(s => s.status !== 'empty').length;
                    return isi + ' Terisi • ' + (list.length - isi) + ' Kosong';
                },

                selectSaung(saung) {
                    this.selectedSaungId = saung.id;
                    this.selectedOrderKode = this.defaultOrderKode(saung);
                    this.cashReceived = 0;
                    this.sinkronkanPilihanStruk(true);
                },

                selectOrder(kodePesanan) {
                    if (this.selectedOrderKode === kodePesanan) return;

                    this.selectedOrderKode = kodePesanan;
                    this.cashReceived = 0;
                },

                async refreshKasirState() {
                    if (this._stateLoading || document.hidden) return;

                    this._stateLoading = true;
                    const kodeTerpilih = this.currentSaung?.kodePesanan || null;
                    const kodeSebelumnya = this.selectedOrderKode;

                    try {
                        const response = await fetch(this.stateUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            cache: 'no-store',
                            credentials: 'same-origin',
                        });

                        if (response.status === 423) {
                            window.location.href = this.lockedUrl;
                            return;
                        }

                        if (!response.ok) return;

                        const data = await response.json();
                        if (!Array.isArray(data.saungs)) return;

                        this.saungs = data.saungs;
                        this.iotAlert = data.iotAlert || this.iotAlert;

                        if (!this.saungs.some(saung => saung.id === this.selectedSaungId)) {
                            this.selectedSaungId = this.saungs[0]?.id || null;
                            this.selectedOrderKode = null;
                        }

                        const table = this.currentTable;
                        if (!table) return;

                        const kodeMasihAda = (table.orders || []).some(order => order.kodePesanan === kodeTerpilih);

                        if (kodeMasihAda) {
                            this.selectedOrderKode = kodeTerpilih;
                        } else if (!(table.orders || []).some(order => order.kodePesanan === this.selectedOrderKode)) {
                            this.selectedOrderKode = this.defaultOrderKode(table);
                        }

                        if (this.selectedOrderKode !== kodeSebelumnya) {
                            this.cashReceived = 0;
                        }

                        this.sinkronkanPilihanStruk(false);

                        if (!this.currentSaung || this.currentSaung.status !== 'waiting_cash') {
                            this.cashReceived = 0;
                        }
                    } catch (error) {
                        // Polling berikutnya akan mencoba lagi.
                    } finally {
                        this._stateLoading = false;
                    }
                },

                sinkronkanPilihanStruk(reset = false) {
                    const codes = (this.currentTable?.receiptOrders || []).map(order => order.kode_pesanan);

                    if (reset) {
                        this.selectedReceiptCodes = [...codes];
                        this.knownReceiptCodes = [...codes];
                        return;
                    }

                    const baru = codes.filter(kode => !this.knownReceiptCodes.includes(kode));
                    const tersisa = this.selectedReceiptCodes.filter(kode => codes.includes(kode));

                    this.selectedReceiptCodes = [...new Set([...tersisa, ...baru])];
                    this.knownReceiptCodes = [...codes];
                },

                toggleStruk(kodePesanan) {
                    if (this.selectedReceiptCodes.includes(kodePesanan)) {
                        this.selectedReceiptCodes = this.selectedReceiptCodes.filter(kode => kode !== kodePesanan);
                    } else {
                        this.selectedReceiptCodes = [...this.selectedReceiptCodes, kodePesanan];
                    }
                },

                pilihSemuaStruk() {
                    this.selectedReceiptCodes = (this.currentTable?.receiptOrders || []).map(order => order.kode_pesanan);
                },

                kosongkanPilihanStruk() {
                    this.selectedReceiptCodes = [];
                },

                hitungUlangStruk(saung) {
                    const orders = saung.receiptOrders || [];

                    saung.receiptItems = orders.flatMap(order => order.items || []);
                    saung.receiptOrderIds = orders.map(order => order.kode_pesanan);
                    saung.receiptOrderId = saung.receiptOrderIds.join(' / ');
                    saung.receiptSubtotal = orders.reduce((total, order) => total + Number(order.subtotal || 0), 0);
                    saung.receiptTotal = orders.reduce((total, order) => total + Number(order.total_bayar || 0), 0);
                    saung.receiptPaymentType = [...new Set(orders.map(order => order.metode_pembayaran).filter(Boolean))].join(' / ');
                    saung.hasUnprintedPaid = orders.length > 0;
                },

                setQuickCash(amount) {
                    this.cashReceived = Number(amount) || 0;
                    if (document.activeElement && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }
                },

                pressNumpad(val) {
                    const now = Date.now();
                    if (this._lastNumpadTime && (now - this._lastNumpadTime < 50)) {
                        return;
                    }
                    this._lastNumpadTime = now;

                    if (document.activeElement && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }

                    if (val === 'clear') {
                        this.cashReceived = 0;
                        return;
                    }

                    let currentStr = String(this.cashReceived || 0);

                    if (val === 'backspace') {
                        if (currentStr.length <= 1 || currentStr === '0') {
                            this.cashReceived = 0;
                        } else {
                            this.cashReceived = parseInt(currentStr.slice(0, -1), 10) || 0;
                        }
                        return;
                    }

                    if (val === '0' || val === '00' || val === '000') {
                        if (this.cashReceived === 0) return;
                        let nextStr = currentStr + val;
                        if (nextStr.length <= 10) {
                            this.cashReceived = parseInt(nextStr, 10) || 0;
                        }
                        return;
                    }

                    let nextStr = (this.cashReceived === 0) ? String(val) : currentStr + String(val);
                    if (nextStr.length <= 10) {
                        this.cashReceived = parseInt(nextStr, 10) || 0;
                    }
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

                waktuSekarangWib() {
                    return new Intl.DateTimeFormat('en-GB', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    }).format(new Date());
                },

                cetakStruk() {
                    const saung = this.currentTable;

                    if (! saung || ! saung.hasUnprintedPaid) {
                        this.showToast('gold', 'Tidak Ada Struk', 'Belum ada pesanan lunas yang belum dicetak pada sesi ini.');
                        return;
                    }

                    const orders = (saung.receiptOrders || [])
                        .filter(order => this.selectedReceiptCodes.includes(order.kode_pesanan));

                    if (orders.length === 0) {
                        this.showToast('gold', 'Pilih Pesanan', 'Centang minimal satu pesanan yang akan dicetak.');
                        return;
                    }

                    const jendela = window.open('', '_blank', 'width=420,height=680');
                    if (! jendela) {
                        this.showToast('danger', 'Popup Diblokir', 'Izinkan popup browser untuk mencetak struk.');
                        return;
                    }

                    const dokumen = jendela.document;
                    dokumen.title = 'Struk ' + (saung.name || '') + ' (' + orders.length + ' pesanan)';

                    const gaya = dokumen.createElement('style');
                    gaya.textContent = '@page{size:80mm auto;margin:6mm 5mm}html,body{margin:0;padding:0}body{font-family:monospace;font-size:10.5px;color:#000;padding:0 2mm 4mm}.logo{display:block;margin:0 auto 8px;height:38px;max-width:48mm;width:auto;object-fit:contain}.receipt{page-break-after:auto;break-after:auto}pre{white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;margin:0;padding:0;font:inherit;line-height:1.45}@media screen{body{padding:16px}.receipt{border:0;margin:0;padding:0}}';
                    dokumen.head.appendChild(gaya);

                    // Semua pesanan yang dicentang digabung menjadi SATU struk.
                    // Logo, header, dan footer hanya muncul sekali.
                    const itemMap = new Map();

                    orders.forEach((order) => {
                        (order.items || []).forEach((item) => {
                            const harga = Number(item.price || 0);
                            const kunci = String(item.name || 'Menu') + '|' + harga;
                            const baris = itemMap.get(kunci) || {
                                name: item.name || 'Menu',
                                qty: 0,
                                price: harga,
                            };

                            baris.qty += Number(item.qty || 0);
                            itemMap.set(kunci, baris);
                        });
                    });

                    const itemLines = Array.from(itemMap.values()).map((item) => {
                        const nama = item.qty + 'x ' + item.name;
                        const harga = this.formatNumber(item.qty * item.price);
                        return nama.slice(0, 30).padEnd(31, ' ') + harga.padStart(12, ' ');
                    });

                    const subtotal = orders.reduce((total, order) => {
                        const dariOrder = Number(order.subtotal || 0)
                            || (order.items || []).reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0), 0);

                        return total + dariOrder;
                    }, 0);

                    const total = orders.reduce((sum, order) => sum + Number(order.total_bayar || 0), 0);
                    const kodePesanan = orders.map(order => order.kode_pesanan).filter(Boolean).join(' / ');
                    const waktuList = orders.map(order => order.waktu_pesan).filter(Boolean);
                    const waktu = waktuList.length > 1 && waktuList[0] !== waktuList[waktuList.length - 1]
                        ? waktuList[0] + ' s/d ' + waktuList[waktuList.length - 1]
                        : (waktuList[0] || this.waktuSekarangWib());

                    const metode = [...new Set(orders.map(order => order.metode_pembayaran).filter(Boolean))]
                        .map((metode) => metode === 'qris' ? 'QRIS Midtrans' : (metode === 'tunai' ? 'Tunai Kasir' : metode))
                        .join(' / ');

                    const lines = [
                        (this.receiptHeader || 'SAUNG SITU AWI').toUpperCase(),
                        this.restoAddress,
                        'Telp: ' + this.restoPhone,
                        '--------------------------------',
                        'No: ' + (kodePesanan || '-'),
                        'Meja: ' + (saung.name || saung.id),
                        'Kasir: ' + this.cashierName,
                        'Pesanan: ' + orders.length + ' order',
                        'Waktu: ' + waktu + ' WIB',
                        '--------------------------------',
                        ...(itemLines.length ? itemLines : ['(Tidak ada item)']),
                        '--------------------------------',
                        'Subtotal: ' + this.formatRupiah(subtotal),
                        'TOTAL: ' + this.formatRupiah(total),
                        'Status: LUNAS',
                        'Pembayaran: ' + (metode || '-'),
                        '--------------------------------',
                        this.receiptFooter,
                    ];

                    const bagian = dokumen.createElement('section');
                    bagian.className = 'receipt';

                    if (this.printLogo) {
                        const logo = dokumen.createElement('img');
                        logo.src = this.logoSrc;
                        logo.alt = 'Logo ' + (this.receiptHeader || 'Resto');
                        logo.className = 'logo';
                        bagian.appendChild(logo);
                    }

                    const isi = dokumen.createElement('pre');
                    isi.textContent = lines.join('\n');
                    bagian.appendChild(isi);
                    dokumen.body.appendChild(bagian);

                    const skrip = dokumen.createElement('script');
                    skrip.textContent = '(function(){let selesai=false;const cetak=()=>{if(selesai)return;selesai=true;window.focus();window.print();};const logo=document.querySelector("img.logo");if(logo){logo.addEventListener("load",cetak);logo.addEventListener("error",cetak);}window.addEventListener("load",cetak);setTimeout(cetak,1000);})();';
                    dokumen.body.appendChild(skrip);

                    this.isPrinting = true;
                    setTimeout(() => { this.isPrinting = false; }, 1200);

                    this.tandaiStrukTercetak(saung, orders.map(order => order.kode_pesanan));
                },

                confirmPayment() {
                    if (! this.currentSaung || this.currentSaung.status !== 'waiting_cash') return;

                    if (Number(this.cashReceived) < Number(this.currentSaung.total)) {
                        this.showToast('danger', 'Uang Kurang', 'Uang diterima belum mencukupi total tagihan.');
                        return;
                    }

                    document.getElementById('formBayar').requestSubmit();
                },

                async tandaiStrukTercetak(saung, kodePesanan) {
                    const kode = [...new Set(kodePesanan || [])];

                    if (kode.length === 0) return;

                    try {
                        const response = await fetch(this.strukTandaiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({
                                meja_id: saung.dbId,
                                kode_pesanan: kode,
                            }),
                        });

                        if (! response.ok) {
                            throw new Error('Status cetak gagal disimpan.');
                        }

                        const hasil = await response.json();
                        const target = this.saungs.find(item => item.dbId === saung.dbId) || saung;
                        const terpilih = new Set(kode);

                        target.receiptOrders = (target.receiptOrders || [])
                            .filter(order => !terpilih.has(order.kode_pesanan));

                        this.hitungUlangStruk(target);
                        this.selectedReceiptCodes = (target.receiptOrders || []).map(order => order.kode_pesanan);
                        this.knownReceiptCodes = [...this.selectedReceiptCodes];

                        this.showToast('success', 'Struk Dicetak', `${hasil.jumlah || kode.length} pesanan ditandai sudah dicetak.`);
                        setTimeout(() => this.refreshKasirState(), 1200);
                    } catch (error) {
                        this.showToast('danger', 'Gagal Menandai Struk', error.message || 'Struk tercetak, tetapi status cetak gagal disimpan.');
                    }
                },

                akhiriSesiMeja() {
                    const table = this.currentTable;

                    if (! table || table.orderCount === 0) {
                        this.showToast('gold', 'Tidak Ada Pesanan', 'Tidak ada pesanan aktif pada meja ini.');
                        return;
                    }

                    if (! table.allOrdersPaid) {
                        this.showToast('danger', 'Belum Lunas', 'Masih ada pesanan yang belum dibayar pada meja ini.');
                        return;
                    }

                    this.confirmModal = {
                        open: true,
                        type: 'danger',
                        title: 'Akhiri Sesi Meja',
                        message: `Akhiri sesi ${table.name}? ${table.orderCount} pesanan aktif akan ditutup dan kode sesi baru dibuat untuk pengunjung berikutnya.`,
                        confirmLabel: 'Ya, Akhiri Sesi',
                        action: 'session',
                    };
                },

                voidOrder() {
                    if (! this.currentSaung || ! this.currentSaung.kodePesanan) return;

                    if (this.currentSaung.status === 'paid') {
                        this.showToast('danger', 'Tidak Dapat Dibatalkan', 'Pesanan yang sudah lunas tidak dapat dibatalkan.');
                        return;
                    }

                    this.confirmModal = {
                        open: true,
                        type: 'danger',
                        title: 'Batalkan Pesanan',
                        message: `Yakin ingin membatalkan pesanan ${this.currentSaung.name} (${this.currentSaung.orderId})? Tindakan ini tidak dapat diurungkan.`,
                        confirmLabel: 'Ya, Batalkan Order',
                        action: 'void',
                    };
                },

                jalankanKonfirmasi() {
                    const action = this.confirmModal.action;
                    this.confirmModal.open = false;

                    if (action === 'session') {
                        document.getElementById('formSelesaikanSesi')?.requestSubmit();
                    } else if (action === 'void') {
                        document.getElementById('formBatal')?.requestSubmit();
                    }
                },

                dismissAlert() {
                    this.iotAlert.active = false;
                    const target = this.saungs.find(s => s.id === this.iotAlert.saungId);
                    if (target) target.hasAlert = false;
                    this.showToast('success', 'Panggilan Selesai', 'Panggilan meja telah ditandai selesai.');
                },

                assignWaiter() {
                    this.showToast('success', 'Pelayan Ditugaskan', 'Pelayan telah dikirim menuju saung pemanggil.');
                    this.dismissAlert();
                },

                lockTerminal() {
                    document.getElementById('formLock')?.requestSubmit();
                },

                handleKeyboardShortcuts(e) {
                    if (this.confirmModal.open) {
                        if (e.key === 'Escape') this.confirmModal.open = false;
                        return;
                    }

                    if (e.repeat) return;

                    const isInputActive = e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);

                    if (e.key === 'Enter' && this.currentSaung.status === 'waiting_cash') {
                        e.preventDefault();
                        this.confirmPayment();
                        return;
                    }
                    if (e.key === 'F3') {
                        e.preventDefault();
                        this.cetakStruk();
                        return;
                    }
                    if (e.key === 'F9') {
                        e.preventDefault();
                        this.voidOrder();
                        return;
                    }

                    if (!isInputActive && this.currentSaung && this.currentSaung.status === 'waiting_cash') {
                        if (e.key >= '0' && e.key <= '9') {
                            this.pressNumpad(e.key);
                        } else if (e.key === 'Backspace') {
                            e.preventDefault();
                            this.pressNumpad('backspace');
                        } else if (e.key === 'Delete' || e.key === 'Escape') {
                            e.preventDefault();
                            this.pressNumpad('clear');
                        }
                    }
                },

                showToast(type, title, message) {
                    clearTimeout(this.toast.timeout);
                    this.toast = {
                        visible: true,
                        type: type,
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

    <script>
        // Reload halaman tidak lagi dipakai di kasir. Event realtime memanggil
        // refreshKasirState() agar input uang dan pilihan order tidak hilang.
    </script>
</body>
</html>
