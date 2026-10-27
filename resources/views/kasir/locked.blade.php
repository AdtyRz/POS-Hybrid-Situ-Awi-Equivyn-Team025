<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Terminal Terkunci • Saung Situ Awi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            background: #FBF8F1;
            color: #2B2A26;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .lock-pattern {
            background-image:
                radial-gradient(circle at 20% 20%, rgba(224, 178, 78, 0.14) 0, transparent 28%),
                radial-gradient(circle at 80% 70%, rgba(11, 77, 43, 0.10) 0, transparent 30%);
        }
    </style>
</head>
<body class="min-h-screen lock-pattern flex flex-col">
    <header class="border-b border-[#C8C2B3]/60 bg-white/70 backdrop-blur">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#0B4D2B] border-2 border-[#E0B24E] overflow-hidden flex items-center justify-center">
                    <img src="{{ asset('asset/Logo.png') }}" alt="Logo Saung Situ Awi" class="w-full h-full object-cover"
                         onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[#E0B24E] font-bold text-xs\'>SA</span>';">
                </div>
                <div>
                    <div class="font-display font-bold text-lg text-[#00341A] leading-tight">Saung Situ Awi</div>
                    <div class="text-[11px] text-[#6B6A63]">Terminal Kasir • Ciwidey</div>
                </div>
            </div>

            <div class="text-right">
                <div class="font-mono font-bold text-sm text-[#00341A]" id="jam-terminal">{{ now('Asia/Jakarta')->format('H:i:s') }} WIB</div>
                <div class="text-[10px] uppercase tracking-widest text-[#6B6A63]">Shift Lock</div>
            </div>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-lg space-y-4">
            <div class="bg-white border border-[#C8C2B3]/70 rounded-2xl shadow-[0_18px_50px_-18px_rgba(26,38,20,0.28)] overflow-hidden">
                <div class="bg-[#0B4D2B] px-6 py-5 text-center space-y-2">
                    <div class="w-14 h-14 rounded-full bg-white/10 border border-[#E0B24E]/50 flex items-center justify-center mx-auto text-[#E0B24E]">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-display font-bold text-2xl text-[#FBF3DD]">Terminal Terkunci</h1>
                        <p class="text-xs text-[#D8E9DC]">Operator: <strong class="text-white">{{ $namaKasir }}</strong></p>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-3 gap-2.5">
                        <div class="rounded-xl border border-[#E0B24E]/40 bg-[#FBF3DD]/70 p-3 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-[#7A5C0F]">Pesanan Baru</div>
                            <div class="font-mono font-extrabold text-2xl text-[#00341A] mt-1" id="notif-pesanan">{{ $notifikasi['pesanan_baru'] }}</div>
                        </div>
                        <div class="rounded-xl border border-[#C1442C]/25 bg-[#FEF2F2] p-3 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-[#991B1B]">Panggilan</div>
                            <div class="font-mono font-extrabold text-2xl text-[#7F1D1D] mt-1" id="notif-panggilan">{{ $notifikasi['panggilan'] }}</div>
                        </div>
                        <div class="rounded-xl border border-[#C8C2B3]/60 bg-[#FAF7F0] p-3 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-[#6B6A63]">Belum Bayar</div>
                            <div class="font-mono font-extrabold text-2xl text-[#2B2A26] mt-1" id="notif-belum-bayar">{{ $notifikasi['belum_bayar'] }}</div>
                        </div>
                    </div>

                    <p class="text-[11px] leading-relaxed text-[#6B6A63] text-center">
                        Layar dikunci. Detail transaksi disembunyikan. Notifikasi hanya menampilkan jumlah pesanan dan panggilan yang masuk.
                    </p>

                    <form method="POST" action="{{ route('kasir.unlock') }}" class="space-y-3" id="form-unlock">
                        @csrf
                        <label for="password" class="block text-xs font-bold text-[#00341A]">Password akun kasir</label>
                        <div class="relative">
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   autofocus
                                   autocomplete="current-password"
                                   placeholder="Masukkan password akun ini"
                                   class="w-full h-12 rounded-xl border-2 border-[#C8C2B3] bg-white px-4 pr-12 text-sm font-semibold text-[#1C1C18] outline-none transition focus:border-[#0B4D2B] @error('password') border-[#C1442C] @enderror">
                            <button type="button"
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-0 w-12 flex items-center justify-center text-[#6B6A63] hover:text-[#00341A]"
                                    aria-label="Tampilkan password">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <div class="rounded-lg border border-[#FCA5A5] bg-[#FEF2F2] px-3 py-2 text-[11px] font-semibold text-[#991B1B]">
                                {{ $message }}
                            </div>
                        @enderror

                        <button type="submit"
                                class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#0A4A28] text-[#FBF3DD] font-extrabold text-xs uppercase tracking-wider transition active:scale-[0.99]">
                            Buka Kunci Terminal
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center text-[10px] text-[#6B6A63]">
                Hanya akun kasir yang sedang login dapat membuka terminal ini.
            </div>
        </div>
    </main>

    <script>
        const notifUrl = @json(route('kasir.locked.notifications'));

        function perbaruiJam() {
            const el = document.getElementById('jam-terminal');
            if (!el) return;
            el.textContent = new Intl.DateTimeFormat('en-GB', {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }).format(new Date()) + ' WIB';
        }

        async function perbaruiNotifikasi() {
            try {
                const response = await fetch(notifUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                });
                if (!response.ok) return;
                const data = await response.json();
                document.getElementById('notif-pesanan').textContent = data.pesanan_baru ?? 0;
                document.getElementById('notif-panggilan').textContent = data.panggilan ?? 0;
                document.getElementById('notif-belum-bayar').textContent = data.belum_bayar ?? 0;
            } catch (error) {
                // Diamkan; polling berikutnya akan mencoba lagi.
            }
        }

        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
            input.focus();
        });

        setInterval(perbaruiJam, 1000);
        setInterval(perbaruiNotifikasi, 5000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) perbaruiNotifikasi();
        });
    </script>
</body>
</html>
