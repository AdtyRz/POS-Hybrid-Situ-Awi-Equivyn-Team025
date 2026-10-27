<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="realtime-version-url" content="{{ route('realtime.version') }}">
    <meta name="realtime-version" content="{{ \App\Support\Realtime::version() }}">
    <title>@yield('title', 'Mobile POS Pelayan • Situ Awi Saung Lesehan')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @endif

    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: #ECE7DC;
            color: #1C1C18;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        ::-webkit-scrollbar {
            width: 0px;
            height: 0px;
        }

        @keyframes urgent-pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(186, 26, 26, 0.4);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 0 20px 4px rgba(186, 26, 26, 0.25);
            }
        }
        .animate-urgent {
            animation: urgent-pulse 2s infinite ease-in-out;
        }

        @keyframes ripple-ping {
            0% { transform: scale(0.95); opacity: 0.8; }
            100% { transform: scale(1.6); opacity: 0; }
        }
        .animate-ripple {
            animation: ripple-ping 1.5s cubic-bezier(0, 0.2, 0.8, 1) infinite;
        }
    </style>
</head>
<body class="min-h-screen flex justify-center bg-[#ECE7DC] sm:py-6">

    <div class="w-full max-w-[390px] sm:max-w-[420px] min-h-screen bg-[#FCF9F2] shadow-2xl relative flex flex-col justify-between sm:rounded-3xl sm:border sm:border-[#E5E2DB] overflow-x-hidden">

        <main class="flex-1 pb-16">
            @yield('content')
        </main>

        <nav class="sticky bottom-0 left-0 right-0 z-50 h-16 {{ request()->routeIs('pelayan.tab-antar') || request()->routeIs('pelayan.tab-saung') || request()->routeIs('pelayan.tab-shift') ? 'bg-white/90 shadow-[0_-4px_20px_rgba(43,42,38,0.05)]' : 'bg-[#FCF9F2]/90 shadow-[0_-2px_16px_rgba(43,42,38,0.05)]' }} backdrop-blur-md border-t border-[#E5E2DB]/70 flex items-center justify-around px-2">

            <a href="{{ route('pelayan.notifikasi-panggilan') }}"
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-6 h-6 {{ request()->routeIs('pelayan.notifikasi-panggilan') || request()->routeIs('pelayan.panggilan') ? 'text-[#0B4D2B]' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(isset($panggilan) && $panggilan->count() > 0)

                        <span class="absolute -top-1 -right-2 px-1 min-w-[13.5px] h-[12px] bg-[#BA1A1A] text-white text-[9px] font-normal rounded-full flex items-center justify-center leading-none">
                            {{ $panggilan->count() }}
                        </span>
                    @elseif(isset($panggilanCount) && $panggilanCount > 0)
                        <span class="absolute -top-1 -right-2 px-1 min-w-[13.5px] h-[12px] bg-[#BA1A1A] text-white text-[9px] font-normal rounded-full flex items-center justify-center leading-none">
                            {{ $panggilanCount }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] mt-0.5 tracking-[-0.25px] {{ request()->routeIs('pelayan.notifikasi-panggilan') || request()->routeIs('pelayan.panggilan') ? 'font-bold text-[#0B4D2B]' : 'font-bold text-[#404941]' }}">
                    Panggilan
                </span>
            </a>

            <a href="{{ route('pelayan.tab-antar') }}"
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-6 h-6 {{ request()->routeIs('pelayan.tab-antar') ? 'text-[#00341A]' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                    </svg>
                    @if(isset($readyCount) && $readyCount > 0)

                        <span class="absolute -top-1 -right-2 px-1 min-w-[13.5px] h-[12px] bg-[#7A5900] text-white text-[9px] font-normal rounded-full flex items-center justify-center leading-none">
                            {{ $readyCount }}
                        </span>
                    @elseif(isset($siapAntarFormatted) && $siapAntarFormatted->count() > 0)
                        <span class="absolute -top-1 -right-2 px-1 min-w-[13.5px] h-[12px] bg-[#7A5900] text-white text-[9px] font-normal rounded-full flex items-center justify-center leading-none">
                            {{ $siapAntarFormatted->count() }}
                        </span>
                    @elseif(isset($siapAntar) && $siapAntar->count() > 0)
                        <span class="absolute -top-1 -right-2 px-1 min-w-[13.5px] h-[12px] bg-[#7A5900] text-white text-[9px] font-normal rounded-full flex items-center justify-center leading-none">
                            {{ $siapAntar->count() }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] mt-0.5 tracking-[-0.25px] {{ request()->routeIs('pelayan.tab-antar') ? 'font-bold text-[#00341A]' : 'font-bold text-[#404941]' }}">
                    Antar
                </span>
                @if(request()->routeIs('pelayan.tab-antar'))

                    <div class="flex flex-col items-center gap-0.5 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#FECE66] shadow-[0_1px_2px_rgba(0,0,0,0.05)]"></span>
                    </div>
                @endif
            </a>

            <a href="{{ route('pelayan.tab-saung') }}"
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-6 h-6 {{ request()->routeIs('pelayan.tab-saung') ? 'text-[#0B4D2B]' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" />
                    </svg>
                </div>
                <span class="text-[10px] mt-0.5 tracking-[-0.25px] {{ request()->routeIs('pelayan.tab-saung') ? 'font-bold text-[#0B4D2B]' : 'font-bold text-[#404941]' }}">
                    Saung
                </span>
                @if(request()->routeIs('pelayan.tab-saung'))

                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B] mt-0.5"></span>
                @endif
            </a>

            <a href="{{ route('pelayan.tab-shift') }}"
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-6 h-6 {{ request()->routeIs('pelayan.tab-shift') ? 'text-[#0B4D2B]' : 'text-[#404941]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    @if(request()->routeIs('pelayan.tab-shift'))

                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-[#FECE66] rounded-full shadow-[0_0_0_2px_#FFFFFF]"></span>
                    @endif
                </div>
                <span class="text-[10px] mt-0.5 tracking-[-0.25px] {{ request()->routeIs('pelayan.tab-shift') ? 'font-bold text-[#00341A]' : 'font-bold text-[#404941]' }}">
                    Shift
                </span>
            </a>

        </nav>

    </div>

    <script>
        function playChime(type = 'success') {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);

                if (type === 'urgent') {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.setValueAtTime(1100, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.35);
                } else {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(523.25, ctx.currentTime);
                    osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch(e) {}
        }
    </script>

    <script>

        window.addEventListener('realtime:panggilan.pelayan', () => {
            if (typeof playChime === 'function') playChime('urgent');
            window.realtimeReload(1200);
        });
        window.addEventListener('realtime', () => window.realtimeReload(800));
    </script>
</body>
</html>