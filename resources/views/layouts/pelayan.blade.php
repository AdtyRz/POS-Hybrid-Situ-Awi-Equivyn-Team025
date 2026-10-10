<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mobile POS Pelayan • Situ Awi Saung Lesehan')</title>

    {{-- Fonts: Plus Jakarta Sans, Playfair Display, JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
            background-color: #EDE8DE;
            color: #1C1C18;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 0px;
            height: 0px;
        }

        /* Pulse animation for urgent alert */
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

        /* Ambient ripple animation */
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

    <!-- Mobile Device Shell Simulation on Desktop / Native on Mobile -->
    <div class="w-full max-w-[420px] min-h-screen bg-[#FCF9F2] shadow-2xl relative flex flex-col justify-between sm:rounded-3xl sm:border sm:border-[#E5E2DB] overflow-x-hidden">

        <!-- Main Dynamic Content Slot -->
        <main class="flex-1 pb-20">
            @yield('content')
        </main>

        <!-- ========================================================
             BOTTOM FIXED NAVIGATION BAR (Standard 64px Height)
             ======================================================== -->
        <nav class="sticky bottom-0 left-0 right-0 z-50 h-16 bg-[#FCF9F2]/90 backdrop-blur-md border-t border-[#E5E2DB]/70 shadow-[0_-4px_20px_rgba(43,42,38,0.06)] flex items-center justify-around px-2">
            
            <!-- 1. Panggilan (Alerts Tab) -->
            <a href="{{ url('/pelayan/notifikasi-panggilan') }}" 
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-5 h-5 {{ request()->is('pelayan/notifikasi-panggilan*') || request()->is('pelayan') ? 'text-[#0B4D2B]' : 'text-[#404941] group-hover:text-[#1C1C18]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Urgent Badge Counter: 3 -->
                    <span class="absolute -top-1 -right-2 px-1 min-w-[15px] h-3.5 bg-[#BA1A1A] text-white text-[9px] font-bold rounded-full flex items-center justify-center border border-white leading-none">
                        3
                    </span>
                </div>
                <span class="text-[10px] mt-0.5 tracking-tight {{ request()->is('pelayan/notifikasi-panggilan*') || request()->is('pelayan') ? 'font-bold text-[#0B4D2B]' : 'font-medium text-[#404941]' }}">
                    Panggilan
                </span>
                @if(request()->is('pelayan/notifikasi-panggilan*') || request()->is('pelayan'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B] absolute bottom-0.5"></span>
                @endif
            </a>

            <!-- 2. Antar (KDS Pass Queue Tab) -->
            <a href="{{ url('/pelayan/tab-antar') }}" 
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-5 h-5 {{ request()->is('pelayan/tab-antar*') ? 'text-[#00341A]' : 'text-[#404941] group-hover:text-[#1C1C18]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <!-- Orders Ready Badge Counter: 5 -->
                    <span class="absolute -top-1 -right-2 px-1 min-w-[15px] h-3.5 bg-[#7A5900] text-white text-[9px] font-bold rounded-full flex items-center justify-center border border-white leading-none">
                        5
                    </span>
                </div>
                <span class="text-[10px] mt-0.5 tracking-tight {{ request()->is('pelayan/tab-antar*') ? 'font-bold text-[#00341A]' : 'font-medium text-[#404941]' }}">
                    Antar
                </span>
                @if(request()->is('pelayan/tab-antar*'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FECE66] shadow-xs absolute bottom-0.5"></span>
                @endif
            </a>

            <!-- 3. Saung (Floor Monitoring Tab) -->
            <a href="{{ url('/pelayan/tab-saung') }}" 
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-5 h-5 {{ request()->is('pelayan/tab-saung*') ? 'text-[#0B4D2B]' : 'text-[#404941] group-hover:text-[#1C1C18]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0v-4m14 4v-4M4 6h16a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z" />
                    </svg>
                </div>
                <span class="text-[10px] mt-0.5 tracking-tight {{ request()->is('pelayan/tab-saung*') ? 'font-bold text-[#0B4D2B]' : 'font-medium text-[#404941]' }}">
                    Saung
                </span>
                @if(request()->is('pelayan/tab-saung*'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B] absolute bottom-0.5"></span>
                @endif
            </a>

            <!-- 4. Shift (Runner Profile & Duty Tab) -->
            <a href="{{ url('/pelayan/tab-shift') }}" 
               class="flex flex-col items-center justify-center w-16 h-12 relative transition-all active:scale-95 group">
                <div class="relative w-6 h-6 flex items-center justify-center">
                    <svg class="w-5 h-5 {{ request()->is('pelayan/tab-shift*') ? 'text-[#00341A]' : 'text-[#404941] group-hover:text-[#1C1C18]' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <!-- Shift live status pip -->
                    <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-[#FECE66] ring-1 ring-white"></span>
                </div>
                <span class="text-[10px] mt-0.5 tracking-tight {{ request()->is('pelayan/tab-shift*') ? 'font-bold text-[#00341A]' : 'font-medium text-[#404941]' }}">
                    Shift
                </span>
                @if(request()->is('pelayan/tab-shift*'))
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00341A] absolute bottom-0.5"></span>
                @endif
            </a>

        </nav>

    </div>

    <!-- Web Audio Helper Script for Realistic Feedback Sound -->
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
</body>
</html>

