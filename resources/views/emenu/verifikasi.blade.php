<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Pembayaran QRIS - Saung Situ Awi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            900: '#052E1B',
                            600: '#0B4D2B',
                            100: '#D9EBE0'
                        },
                        gold: {
                            500: '#E0B24E',
                            50: '#FBF3DD'
                        },
                        cream: {
                            50: '#FBF8F1'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Playfair Display"', 'Georgia', 'serif']
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=playfair-display:600,700|plus-jakarta-sans:400,500,600,700,800&display=swap"
        rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script>
            window.__CHANNELS__ = ['pesanan.{{ $pesanan->kode_pesanan }}'];
        </script>
        @vite('resources/js/reverb.js')
    @endif
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans flex flex-col">
    <div class="max-w-md mx-auto px-4 pt-10 pb-10 flex-1 flex flex-col items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div
                    class="w-24 h-24 mx-auto rounded-3xl bg-forest-900 flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-12 h-12 text-gold-500 animate-pulse" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold tracking-[0.2em] text-[#8a6d1f] mt-4">TRADISI SUNDA • PRESISI DIGITAL
                </p>
                <h1 class="font-display font-bold text-3xl text-forest-900 mt-1">Saung Situ Awi</h1>
            </div>

            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6 text-center">
                @if (!empty($qris['qr_url']))
                    <h2 class="font-display font-bold text-2xl text-forest-900">Scan untuk Bayar</h2>
                    <p class="text-stone-500 text-sm mt-2">Scan QRIS di bawah ini dengan aplikasi e-wallet / m-banking
                        Anda.</p>
                    <div class="mt-5 inline-block p-3 bg-white rounded-2xl border-2 border-forest-100">
                        <img src="{{ $qris['qr_url'] }}" alt="QRIS Meja {{ $pesanan->meja->kode_meja }}"
                            class="w-64 h-64 mx-auto object-contain" data-qris-image>
                    </div>
                    <div class="mt-5 flex items-center justify-center gap-2 text-forest-600 text-sm font-semibold">
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span id="statusTeks">Menunggu pembayaran...</span>
                    </div>
                    <p class="text-stone-400 text-xs mt-2">QR berlaku <span id="countdown"
                            class="font-mono font-bold text-stone-600">--:--</span>. QR akan diperbarui otomatis bila
                        kedaluwarsa.</p>
                @else
                    <svg class="w-16 h-16 mx-auto text-gold-500 animate-spin" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    <h2 class="font-display font-bold text-2xl text-forest-900 mt-4">Menunggu Verifikasi QRIS</h2>
                    <p class="text-stone-500 text-sm mt-2">QR Code belum dapat dibuat. Pastikan konfigurasi Midtrans
                        sudah benar, lalu muat ulang halaman ini.</p>
                @endif
                <div class="mt-6 p-4 bg-cream-50 rounded-xl border border-stone-100">
                    <p class="font-bold text-forest-900">Kode Meja: <span
                            class="font-mono">{{ $pesanan->meja->kode_meja }}</span></p>
                    <p class="text-stone-500 text-sm mt-1">Total:
                        Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
                <h3 class="font-bold text-forest-900 mb-4">Instruksi Pembayaran QRIS</h3>
                <div class="space-y-3 text-sm text-stone-600">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">1</span>
                        <span>Buka aplikasi e-wallet atau m-banking yang mendukung QRIS</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">2</span>
                        <span>Pilih fitur "Bayar QRIS" atau "Scan QR"</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">3</span>
                        <span>Arahkan kamera ke QR Code di layar ini</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">4</span>
                        <span>Konfirmasi pembayaran di aplikasi Anda</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">5</span>
                        <span>Halaman ini akan otomatis berpindah ke halaman status pesanan</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3 mt-4">
                <a href="{{ route('emenu.status', $pesanan->kode_pesanan) }}"
                    class="flex w-full items-center justify-center gap-2 text-center bg-forest-900 text-white font-extrabold rounded-2xl py-4 shadow-sm hover:bg-forest-600 transition active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Lihat Status Pesanan</span>
                </a>
            </div>

            <p class="text-center text-stone-400 text-xs mt-6">© 2026 Saung Situ Awi Ciwidey</p>
        </div>
    </div>

    <script>
        let checkCount = 0;
        const maxChecks = 200;
        const orderCode = "{{ $pesanan->kode_pesanan }}";
        const statusUrl = "{{ route('emenu.status', $pesanan->kode_pesanan) }}";
        const qrisStatusUrl = "{{ route('emenu.qris.status', $pesanan->kode_pesanan) }}";
        const qrTtl = {{ (int) ($qrisTtl ?? 300) }};
        let remaining = qrTtl;

        function tickCountdown() {
            const el = document.getElementById('countdown');
            if (!el) return;
            if (remaining <= 0) {
                el.textContent = '00:00';
                const s = document.getElementById('statusTeks');
                if (s) s.textContent = 'Memperbarui QR Code...';
                return;
            }
            const m = String(Math.floor(remaining / 60)).padStart(2, '0');
            const sec = String(remaining % 60).padStart(2, '0');
            el.textContent = m + ':' + sec;
            remaining--;
            setTimeout(tickCountdown, 1000);
        }

        function checkPaymentStatus() {
            checkCount++;
            fetch(qrisStatusUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.lunas) {
                        window.location.href = statusUrl;
                        return;
                    }
                    if (data.expired && data.qr_url) {
                        const img = document.querySelector('[data-qris-image]');
                        if (img) img.src = data.qr_url;
                        const s = document.getElementById('statusTeks');
                        if (s) s.textContent = 'Menunggu pembayaran...';
                        remaining = qrTtl;
                    }
                    if (checkCount < maxChecks) {
                        setTimeout(checkPaymentStatus, 3000);
                    }
                })
                .catch(() => {
                    if (checkCount < maxChecks) {
                        setTimeout(checkPaymentStatus, 3000);
                    }
                });
        }

        tickCountdown();
        checkPaymentStatus();

        window.addEventListener('realtime:status.berubah', () => {
            fetch(qrisStatusUrl, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.lunas) window.location.href = statusUrl;
                })
                .catch(() => {});
        });
    </script>
</body>

</html>
