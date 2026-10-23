<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pelacakan {{ $pesanan->kode_pesanan }} - Saung Situ Awi</title>
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

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
    <div class="max-w-md mx-auto px-4 pt-4 pb-8">
        <div class="flex items-center justify-between">
            <a href="{{ route('emenu.meja', $pesanan->meja->id) }}" class="w-9 h-9 flex items-center justify-center"
                aria-label="Kembali">
                <svg class="w-6 h-6 text-stone-800" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="text-center">
                <p class="text-[11px] font-bold tracking-widest text-[#8a6d1f]">• SAUNG SITU AWI</p>
                <p class="font-bold text-lg leading-tight">Pelacakan & Status Pesanan</p>
            </div>
            <div class="flex items-center gap-2">
                <span
                    class="bg-white border border-stone-200 rounded-full px-3 py-1.5 text-xs font-bold whitespace-nowrap">{{ $pesanan->meja->kode_meja }}</span>
                <div class="w-9 h-9 rounded-full bg-forest-900 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 8h1a3 3 0 0 1 0 6h-1M4 8h14v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-4">
            <p class="text-[11px] font-bold tracking-widest text-[#8a6d1f]">LIVE TRACKER & PAYMENT</p>
            <div class="flex items-start justify-between gap-3 mt-1">
                <div>
                    <p class="font-bold text-lg leading-snug">Order #{{ $pesanan->kode_pesanan }}</p>
                    <p class="text-stone-500 text-xs mt-0.5">{{ $pesanan->meja->kode_meja }} • Dine-in</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-stone-400 text-xs">Total Tagihan</p>
                    <p class="text-forest-600 font-extrabold text-xl">
                        Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                </div>
            </div>
            @if ($pesanan->status_pesanan === 'dibatalkan')
                <span class="inline-block bg-red-100 text-red-700 text-xs font-bold rounded-full px-3 py-1 mt-2">Pesanan
                    Dibatalkan</span>
            @elseif ($pesanan->status_pembayaran === 'sudah_bayar')
                <span
                    class="inline-block bg-forest-100 text-forest-600 text-xs font-bold rounded-full px-3 py-1 mt-2">Lunas
                    - Masuk Dapur</span>
            @else
                <span
                    class="inline-block bg-gold-50 text-[#7a5c0f] text-xs font-bold rounded-full px-3 py-1 mt-2">Menunggu
                    Pembayaran</span>
            @endif
        </div>
        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold">Status Pesanan {{ $pesanan->meja->kode_meja }}</h2>
                <span class="bg-stone-100 text-stone-500 text-[11px] font-bold rounded-full px-3 py-1">Realtime
                    MQTT</span>
            </div>
            <div class="mt-3">
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span
                            class="w-7 h-7 rounded-full {{ $tahap >= 1 ? 'bg-forest-600 text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center">
                            @if ($tahap >= 1)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <span class="text-sm font-bold">1</span>
                            @endif
                        </span>
                        <span
                            class="w-0.5 flex-1 {{ $tahap >= 2 ? 'bg-forest-600' : 'bg-stone-200' }} min-h-[28px]"></span>
                    </div>
                    <div class="pb-4">
                        <p class="font-bold text-sm">Pesanan Diterima</p>
                        <p class="text-stone-500 text-xs">Tiket order masuk ke sistem antrean dapur</p>
                        <p class="text-stone-400 text-[11px] mt-0.5">{{ $pesanan->waktu_pesan->format('H.i') }} WIB</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span
                            class="w-7 h-7 rounded-full {{ $tahap >= 2 ? 'bg-gold-500 text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center">
                            @if ($tahap >= 3)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                                </svg>
                            @endif
                        </span>
                        <span
                            class="w-0.5 flex-1 {{ $tahap >= 3 ? 'bg-forest-600' : 'bg-stone-200' }} min-h-[28px]"></span>
                    </div>
                    <div class="pb-4">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-sm">Diproses di Dapur & Bar</p>
                            @if ($tahap === 2)
                                <span
                                    class="bg-gold-50 text-[#7a5c0f] text-[10px] font-bold rounded-full px-2.5 py-0.5">Aktif</span>
                            @endif
                        </div>
                        <p class="text-stone-500 text-xs">Sedang dimasak oleh Chef Saung Situ Awi</p>
                        <p class="text-stone-400 text-[11px] mt-0.5">Estimasi 15 - 20 menit</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span
                            class="w-7 h-7 rounded-full {{ $tahap >= 3 ? 'bg-forest-600 text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center">
                            @if ($tahap >= 4)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                                </svg>
                            @endif
                        </span>
                        <span
                            class="w-0.5 flex-1 {{ $tahap >= 4 ? 'bg-forest-600' : 'bg-stone-200' }} min-h-[28px]"></span>
                    </div>
                    <div class="pb-4">
                        <p class="font-bold text-sm">Siap Diantarkan Pelayan</p>
                        <p class="text-stone-500 text-xs">Penyajian dari meja pass bar</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span
                            class="w-7 h-7 rounded-full {{ $tahap >= 4 ? 'bg-forest-600 text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center">
                            @if ($tahap >= 5)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                                </svg>
                            @endif
                        </span>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Pesanan Tiba di {{ $pesanan->meja->kode_meja }}</p>
                        <p class="text-stone-500 text-xs">Selamat menikmati hidangan khas Sunda!</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-forest-900 text-white rounded-2xl p-4 mt-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-sm">IoT Table Node {{ $pesanan->meja->kode_meja }}</h2>
                <span class="bg-white/15 text-[11px] font-bold rounded-full px-3 py-1">• MQTT Synced</span>
            </div>
            <div class="font-mono text-[13px] mt-3 leading-relaxed text-forest-100">
                <p>SAUNG-IOT-{{ str_replace('-', '', $pesanan->meja->kode_meja) }} // ESP32 CH-02 WIFI: -54dBm</p>
                <p class="text-white font-bold tracking-widest">STATUS: {{ strtoupper($pesanan->status_pesanan) }}</p>
                <p>{{ $pesanan->meja->kode_meja }} &nbsp;&nbsp; {{ now()->format('H:i') }} WIB</p>
            </div>
            <p class="text-white/70 text-xs mt-3">Layar LCD fisik di saung Anda telah terupdate otomatis via MQTT
                WebSocket.</p>
        </div>
        @if ($pesanan->metode_pembayaran === 'qris')
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold">Pembayaran QRIS</h2>
                    <span class="bg-stone-100 text-stone-500 text-[11px] font-bold rounded-full px-3 py-1">Midtrans
                        Sandbox</span>
                </div>
                @if ($pesanan->status_pembayaran === 'sudah_bayar')
                    <div class="bg-forest-100/60 rounded-2xl p-4 mt-3 text-center">
                        <p class="font-bold text-forest-900">Pembayaran QRIS Lunas</p>
                        <p class="text-forest-600 text-xs mt-1">Pembayaran via QRIS berhasil. Pesanan otomatis
                            diteruskan ke dapur.</p>
                    </div>
                @elseif (!empty($qris['qr_url']))
                    <div class="rounded-2xl p-4 mt-3 text-center">
                        <p class="font-bold text-[#7a5c0f]">Scan QRIS untuk Membayar</p>
                        <p class="text-stone-500 text-xs mt-1">Scan QR di bawah dengan e-wallet / m-banking Anda.</p>
                        <div class="mt-4 inline-block p-3 bg-white rounded-2xl border-2 border-forest-100">
                            <img src="{{ $qris['qr_url'] }}" alt="QRIS {{ $pesanan->kode_pesanan }}"
                                class="w-56 h-56 mx-auto object-contain" data-qris-image>
                        </div>
                        <p class="text-stone-400 text-xs mt-3">QR berlaku <span id="countdown"
                                class="font-mono font-bold text-stone-600">--:--</span>. Diperbarui otomatis bila
                            kedaluwarsa.</p>
                        <p id="statusTeks" class="text-forest-600 text-xs font-semibold mt-2">Menunggu pembayaran...
                        </p>
                    </div>
                @else
                    <div class="bg-gold-50 rounded-2xl p-4 mt-3 text-center">
                        <p class="font-bold text-[#7a5c0f]">Menunggu Pembayaran QRIS</p>
                        <p class="text-[#7a5c0f] text-xs mt-1">QR belum dapat dibuat. Muat ulang halaman ini sebentar
                            lagi.</p>
                    </div>
                @endif
                <p class="text-stone-500 text-xs mt-3">Pembayaran QRIS diproses otomatis melalui Midtrans (GoPay, OVO,
                    Dana, ShopeePay, BCA Mobile, m-Banking).</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
                <h2 class="font-bold">Pembayaran Tunai</h2>
                <div class="bg-cream-50 rounded-2xl p-4 mt-3 text-center">
                    <p class="font-extrabold text-2xl text-forest-900">
                        Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                    <p class="text-stone-500 text-xs mt-1">Sebutkan kode <span
                            class="font-mono font-bold text-stone-700">{{ $pesanan->kode_pesanan }}</span> di kasir
                        utama.</p>
                </div>
            </div>
        @endif
        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
            <h2 class="font-bold text-sm">Rincian Item Pesanan</h2>
            <div class="divide-y divide-stone-100 mt-1">
                @foreach ($pesanan->detailPesanan as $item)
                    <div class="flex items-center justify-between py-2.5">
                        <div>
                            <p class="font-bold text-sm">{{ $item->jumlah }}× {{ $item->menu->nama_menu }}</p>
                            @if ($item->catatan)
                                <p class="text-stone-400 text-xs">{{ $item->catatan }}</p>
                            @endif
                        </div>
                        <p class="font-bold text-sm">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-3 mt-1">
                <p class="font-bold text-sm">Total Pembayaran</p>
                <p class="font-extrabold text-forest-600">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}
                </p>
            </div>
        </div>
        <a href="{{ route('emenu.meja', $pesanan->meja->id) }}"
            class="block text-center w-full mt-4 bg-white border border-forest-900 text-forest-900 font-bold rounded-full py-3">Kembali
            ke Katalog</a>
    </div>
    @if (
        $pesanan->metode_pembayaran === 'qris' &&
            $pesanan->status_pembayaran !== 'sudah_bayar' &&
            $pesanan->status_pesanan !== 'dibatalkan')
        <script>
            let checkCount = 0;
            const maxChecks = 200;
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
                            window.location.reload();
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
        </script>
    @endif

    <script>
        // Realtime: perubahan status pesanan/pembayaran → muat ulang pelacakan.
        window.addEventListener('realtime', () => window.realtimeReload(600));
    </script>
</body>

</html>
