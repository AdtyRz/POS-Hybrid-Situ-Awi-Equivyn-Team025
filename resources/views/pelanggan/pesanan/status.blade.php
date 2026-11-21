<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="realtime-version-url" content="{{ route('realtime.version') }}">
    <meta name="realtime-version" content="{{ \App\Support\Realtime::version() }}">
    <title>Status Meja {{ $pesanan->meja->kode_meja }} - Saung Situ Awi</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/app.css')
    @else
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
    @endif
    <link href="{{ asset('fonts/fonts.css') }}" rel="stylesheet">
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script>
            window.__CHANNELS__ = ['pesanan.{{ $pesanan->kode_pesanan }}'];
        </script>
        @vite('resources/js/reverb.js')
    @endif
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
    <div class="max-w-md mx-auto px-4 pt-4 pb-8" data-live="pesanan-status">
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
                    <p class="font-bold text-lg leading-snug">Meja {{ $pesanan->meja->kode_meja }}</p>
                    <p class="text-stone-500 text-xs mt-0.5">Kode: <span class="font-mono font-bold text-stone-700">{{ $pesanan->kode_pesanan }}</span></p>
                    @if ($pesanan->nama_pelanggan)
                        <p class="text-stone-500 text-xs mt-0.5">Pemesan: <span
                                class="font-bold text-stone-700">{{ $pesanan->nama_pelanggan }}</span></p>
                    @else
                        <p class="text-stone-500 text-xs mt-0.5">Dine-in</p>
                    @endif
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
                <span class="bg-stone-100 text-stone-500 text-[11px] font-bold rounded-full px-3 py-1">Realtime</span>
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
                        <p class="text-stone-400 text-[11px] mt-0.5">{{ $pesanan->waktu_pesan->timezone('Asia/Jakarta')->format('H.i') }} WIB</p>
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
                            class="w-7 h-7 rounded-full {{ $tahap >= 4 ? 'bg-forest-600 text-white' : ($tahap >= 3 ? 'bg-gold-500 text-white' : 'bg-stone-200 text-stone-400') }} flex items-center justify-center">
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
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-sm">Siap Diantarkan Pelayan</p>
                            @if ($tahap === 3)
                                <span
                                    class="bg-gold-50 text-[#7a5c0f] text-[10px] font-bold rounded-full px-2.5 py-0.5">Aktif</span>
                            @endif
                        </div>
                        <p class="text-stone-500 text-xs">Penyajian dari meja pass bar</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span
                            class="w-7 h-7 rounded-full {{ $tahap >= 5 ? 'bg-forest-600 text-white' : ($tahap >= 4 ? 'bg-gold-500 text-white' : 'bg-stone-200 text-stone-400') }} flex items-center justify-center">
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
                        <span
                            class="w-0.5 flex-1 {{ $tahap >= 5 ? 'bg-forest-600' : 'bg-stone-200' }} min-h-[28px]"></span>
                    </div>
                    <div class="pb-4">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-sm">Sedang Diantar ke Meja</p>
                            @if ($tahap === 4)
                                <span
                                    class="bg-gold-50 text-[#7a5c0f] text-[10px] font-bold rounded-full px-2.5 py-0.5">Aktif</span>
                            @endif
                        </div>
                        <p class="text-stone-500 text-xs">Pelayan sedang membawa pesanan ke saung Anda</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <span
                            class="w-7 h-7 rounded-full {{ $tahap >= 5 ? 'bg-forest-600 text-white' : 'bg-stone-200 text-stone-400' }} flex items-center justify-center">
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
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-sm">Pesanan Tiba di {{ $pesanan->meja->kode_meja }}</p>
                            @if ($tahap === 5)
                                <span
                                    class="bg-forest-100 text-forest-600 text-[10px] font-bold rounded-full px-2.5 py-0.5">Tiba</span>
                            @endif
                        </div>
                        <p class="text-stone-500 text-xs">Selamat menikmati hidangan khas Sunda!</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-forest-900 text-white rounded-2xl p-4 mt-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-sm">IoT Table Node {{ $pesanan->meja->kode_meja }}</h2>
                <span class="bg-white/15 text-[11px] font-bold rounded-full px-3 py-1">• HTTP Tersinkron</span>
            </div>
            <div class="font-mono text-[13px] mt-3 leading-relaxed text-forest-100">
                <p class="text-white font-bold tracking-widest">STATUS: {{ strtoupper($pesanan->status_pesanan) }}</p>
                <p>{{ $pesanan->meja->kode_meja }} &nbsp;&nbsp; {{ now('Asia/Jakarta')->format('H:i') }} WIB</p>
            </div>
            <p class="text-white/70 text-xs mt-3">Layar LCD fisik di saung Anda telah terupdate otomatis via WiFi + HTTP REST.</p>
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
                        <div class="mt-4 rounded-2xl border border-gold-500/30 bg-gold-50 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#8a6d1f]">
                                Batas waktu scan &amp; bayar
                            </p>
                            <p id="countdown"
                                class="mt-1 font-mono text-4xl font-bold leading-none tracking-tight tabular-nums text-forest-900">
                                --:--</p>
                            <p class="mt-2 text-[11px] text-[#8a6d1f]">
                                Berlaku sampai <span id="countdownJam" class="font-bold">--:--</span> WIB.
                                QR diperbarui otomatis bila waktu habis.
                            </p>
                        </div>
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
                @if ($pesanan->status_pembayaran !== 'sudah_bayar')
                    <p class="text-stone-500 text-xs mt-3">Pembayaran QRIS diproses otomatis melalui Midtrans.</p>
                @endif
            </div>
        @elseif ($pesanan->metode_pembayaran === 'transfer')
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold">Pembayaran Transfer Bank</h2>
                    <span class="text-[11px] font-bold rounded-full px-3 py-1 {{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'bg-forest-100 text-forest-900' : ($pesanan->status_pesanan === 'dibatalkan' ? 'bg-red-100 text-red-700' : 'bg-sky-100 text-sky-800') }}">
                        {{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'Lunas' : ($pesanan->status_pesanan === 'dibatalkan' ? 'Dibatalkan' : 'Menunggu Konfirmasi') }}
                    </span>
                </div>
                @if ($pesanan->status_pembayaran === 'sudah_bayar')
                    <div class="bg-forest-100/60 rounded-2xl p-4 mt-3 text-center">
                        <p class="font-bold text-forest-900">Pembayaran Transfer Lunas</p>
                        <p class="text-forest-600 text-xs mt-1">Transfer sudah dikonfirmasi kasir. Pesanan diteruskan ke dapur.</p>
                    </div>
                @elseif ($pesanan->status_pesanan === 'dibatalkan')
                    <div class="bg-red-50 rounded-2xl p-4 mt-3 text-center">
                        <p class="font-bold text-red-700">Pesanan Dibatalkan</p>
                        <p class="text-red-600 text-xs mt-1">Pesanan ini tidak lagi menunggu pembayaran transfer.</p>
                    </div>
                @else
                    <div class="bg-sky-50 rounded-2xl p-4 mt-3 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-sky-700">Total Transfer</p>
                        <p class="font-extrabold text-2xl text-forest-900 mt-1">
                            Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                        <p class="text-stone-500 text-xs mt-1">Transfer sesuai nominal, lalu tunjukkan bukti ke kasir.</p>
                    </div>
                    <div class="mt-3 rounded-2xl border border-sky-200 bg-sky-50/60 p-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-stone-500">Bank</span>
                            <strong class="text-forest-900">{{ $transfer['bank'] ?: '-' }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-stone-500">Nomor Rekening</span>
                            <strong class="font-mono text-forest-900">{{ $transfer['nomor'] ?: 'Belum diatur' }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-stone-500">Atas Nama</span>
                            <strong class="text-forest-900">{{ $transfer['atas_nama'] ?: '-' }}</strong>
                        </div>
                    </div>
                    @if (blank($transfer['nomor']))
                        <p class="text-amber-700 text-xs font-semibold mt-3">Nomor rekening belum diatur. Silakan hubungi kasir untuk tujuan transfer.</p>
                    @endif
                    <p class="text-stone-500 text-xs mt-3">Pesanan diproses setelah kasir mengonfirmasi dana transfer diterima.</p>
                @endif
            </div>
        @elseif ($pesanan->status_pembayaran !== 'sudah_bayar' && $pesanan->status_pesanan !== 'dibatalkan')
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-3">
                <h2 class="font-bold">Pembayaran Tunai</h2>
                <div class="bg-cream-50 rounded-2xl p-4 mt-3 text-center">
                    <p class="font-extrabold text-2xl text-forest-900">
                        Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                    <p class="text-stone-500 text-xs mt-1">Sebutkan kode meja <span
                            class="font-mono font-bold text-stone-700">{{ $pesanan->meja->kode_meja }}</span> dan nama
                        <span class="font-bold text-stone-700">{{ $pesanan->nama_pelanggan ?: 'pemesan' }}</span> di kasir
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
            const qrisStatusUrl = @json($qrisStatusUrl);
            const qrTtl = {{ (int) ($qrisTtl ?? 300) }};
            let qrisDeadline = null;

            function aturBatasQris(detik) {
                const sisa = Number(detik);
                qrisDeadline = Number.isFinite(sisa) && sisa > 0 ? Date.now() + sisa * 1000 : null;
            }

            function tickCountdown() {
                const el = document.getElementById('countdown');
                const jam = document.getElementById('countdownJam');
                if (!el) return;

                if (qrisDeadline === null) {
                    el.textContent = '--:--';
                    if (jam) jam.textContent = '--:--';
                    return;
                }

                const remaining = Math.max(0, Math.ceil((qrisDeadline - Date.now()) / 1000));
                const m = String(Math.floor(remaining / 60)).padStart(2, '0');
                const sec = String(remaining % 60).padStart(2, '0');
                el.textContent = m + ':' + sec;
                el.style.color = remaining <= 60 ? '#BA1A1A' : '';

                if (jam) {
                    jam.textContent = new Date(qrisDeadline).toLocaleTimeString('en-GB', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                }

                if (remaining <= 0) {
                    const s = document.getElementById('statusTeks');
                    if (s) s.textContent = 'Waktu habis. Menyiapkan QR baru...';
                }
            }

            aturBatasQris(qrTtl);
            setInterval(tickCountdown, 1000);

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
                            if (typeof window.refreshLiveRegions === 'function') {
                                window.refreshLiveRegions({ immediate: true });
                            } else {
                                window.location.reload();
                            }
                            return;
                        }
                        if (data.dibatalkan) {
                            qrisDeadline = null;
                            const s = document.getElementById('statusTeks');
                            if (s) s.textContent = 'Pesanan dibatalkan.';
                            const c = document.getElementById('countdown');
                            if (c) c.textContent = '--:--';
                            const j = document.getElementById('countdownJam');
                            if (j) j.textContent = '--:--';

                            if (typeof window.refreshLiveRegions === 'function') {
                                window.refreshLiveRegions({ immediate: true });
                            } else {
                                window.location.reload();
                            }
                            return;
                        }
                        if (data.expired && data.qr_url) {
                            const img = document.querySelector('[data-qris-image]');
                            if (img) img.src = data.qr_url;
                            const s = document.getElementById('statusTeks');
                            if (s) s.textContent = 'Menunggu pembayaran...';
                            aturBatasQris(Number(data.expires_in) > 0 ? Number(data.expires_in) : qrTtl);
                            tickCountdown();
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

    @if (
        $pesanan->metode_pembayaran !== 'qris' &&
            ! in_array($pesanan->status_pesanan, ['selesai', 'dibatalkan'], true))
        <script>
            (() => {
                const statusApiUrl = @json($statusApiUrl);
                let statusTerakhir = @json($pesanan->status_pesanan);
                let timer = null;
                let berhenti = false;

                function jadwalkan() {
                    if (berhenti) return;
                    clearTimeout(timer);
                    timer = setTimeout(periksaStatusPesanan, 3000);
                }

                async function periksaStatusPesanan() {
                    if (berhenti) return;

                    if (document.hidden) {
                        jadwalkan();
                        return;
                    }

                    try {
                        const response = await fetch(statusApiUrl, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            cache: 'no-store',
                            credentials: 'same-origin',
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const berubah = data.status_pesanan
                                && data.status_pesanan !== statusTerakhir;
                            const final = data.lunas
                                || data.dibatalkan
                                || data.status_pesanan === 'selesai';

                            if (final) {
                                berhenti = true;

                                if (typeof window.refreshLiveRegions === 'function') {
                                    await window.refreshLiveRegions({ immediate: true });
                                } else {
                                    window.location.reload();
                                }
                                return;
                            }

                            if (berubah) {
                                statusTerakhir = data.status_pesanan;

                                if (typeof window.refreshLiveRegions === 'function') {
                                    await window.refreshLiveRegions({ immediate: true });
                                } else {
                                    window.location.reload();
                                    return;
                                }
                            }
                        }
                    } catch (error) {
                        // Abaikan gangguan jaringan sementara, lalu coba lagi.
                    }

                    jadwalkan();
                }

                document.addEventListener('visibilitychange', () => {
                    if (! document.hidden) periksaStatusPesanan();
                });

                jadwalkan();
            })();
        </script>
    @endif
</body>

</html>
