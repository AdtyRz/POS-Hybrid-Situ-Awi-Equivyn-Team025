<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bayar Tunai - Saung Situ Awi</title>
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
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans flex flex-col">
    <div class="max-w-md mx-auto px-4 pt-10 pb-10 flex-1 flex flex-col items-center justify-center">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div
                    class="w-24 h-24 mx-auto rounded-3xl bg-forest-900 flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-12 h-12 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                    </svg>
                </div>
                <p class="text-[11px] font-bold tracking-[0.2em] text-[#8a6d1f] mt-4">TRADISI SUNDA • PRESISI DIGITAL
                </p>
                <h1 class="font-display font-bold text-3xl text-forest-900 mt-1">Saung Situ Awi</h1>
            </div>

            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6 text-center">
                <div class="bg-forest-100 rounded-xl p-4 mb-4">
                    <svg class="w-16 h-16 mx-auto text-forest-600" fill="none" stroke="currentColor"
                        stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                    </svg>
                </div>
                <h2 class="font-display font-bold text-2xl text-forest-900">Bayar di Kasir</h2>
                <p class="text-stone-500 text-sm mt-2">Sebutkan kode meja dan nama pemesan kepada kasir</p>
            </div>

            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
                <div class="bg-cream-50 rounded-xl p-4 mb-4 border border-gold-200">
                    <p class="font-bold text-forest-900 text-lg mb-2">Kode Meja Anda</p>
                    <div class="font-mono text-4xl font-bold text-forest-900 tracking-[0.18em]">{{ $pesanan->meja->kode_meja }}</div>
                    <p class="text-stone-600 text-sm mt-2">Pemesan: <span class="font-bold text-forest-900">{{ $pesanan->nama_pelanggan ?: 'Tamu' }}</span></p>
                    <p class="text-stone-500 text-xs mt-2">Cukup sebutkan kode meja dan nama pemesan, tidak perlu menunjukkan kode pesanan.</p>
                </div>

                <div class="space-y-3 text-sm text-stone-600">
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">1</span>
                        <span>Sebutkan <span class="font-bold font-mono">{{ $pesanan->meja->kode_meja }}</span> dan nama <span class="font-bold">{{ $pesanan->nama_pelanggan ?: 'pemesan' }}</span> ke kasir</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">2</span>
                        <span>Kasir akan mencari pesanan berdasarkan kode meja dan nama pemesan</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">3</span>
                        <span>Halaman ini otomatis diperbarui setelah pembayaran dikonfirmasi kasir</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 mt-4 border border-stone-100">
                    <p class="font-bold text-forest-900 mb-2">Total Bayar</p>
                    <p class="font-display font-bold text-3xl text-forest-600">
                        Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                    <p class="text-stone-500 text-xs mt-1">Total pembayaran</p>
                    <div id="statusTunai" class="mt-3 inline-flex items-center gap-2 rounded-full bg-gold-50 px-3 py-1.5 text-xs font-bold text-[#7A5900]">
                        <span class="h-2 w-2 rounded-full bg-gold-500 animate-pulse"></span>
                        Menunggu konfirmasi kasir
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
                <h3 class="font-bold text-forest-900 mb-4">Detail Pesanan</h3>
                <div class="space-y-2">
                    @foreach ($pesanan->detailPesanan as $item)
                        <div class="flex items-center justify-between py-2 border-b border-stone-100 last:border-0">
                            <div>
                                <p class="font-bold text-sm">{{ $item->jumlah }}x {{ $item->menu->nama_menu }}</p>
                                @if ($item->catatan)
                                    <p class="text-stone-400 text-xs">{{ $item->catatan }}</p>
                                @endif
                            </div>
                            <p class="font-bold text-sm">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-3 mt-3">
                    <p class="font-bold text-lg">Total</p>
                    <p class="font-extrabold text-forest-600 text-xl">
                        Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="space-y-3 mt-6">
                <a href="{{ route('emenu.status', $pesanan->kode_pesanan) }}"
                    class="flex w-full items-center justify-center gap-2 text-center bg-forest-900 text-white font-extrabold rounded-2xl py-4 shadow-sm hover:bg-forest-600 transition active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Lihat Status Pesanan</span>
                </a>
                <a href="{{ route('emenu.meja', $pesanan->meja_id) }}"
                    class="block w-full text-center bg-white border border-stone-200 text-stone-700 font-semibold rounded-2xl py-3 hover:bg-stone-50 transition active:scale-95">
                    ← Kembali ke Katalog
                </a>
            </div>

            <p class="text-center text-stone-400 text-xs mt-6">© 2026 Saung Situ Awi Ciwidey</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusUrl = @json(route('emenu.bayar-tunai.status', $pesanan->kode_pesanan));
            const statusElement = document.getElementById('statusTunai');
            let selesai = false;

            async function periksaStatus() {
                if (selesai || document.hidden) {
                    return;
                }

                try {
                    const response = await fetch(statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();

                    if (data.lunas) {
                        selesai = true;
                        statusElement.className = 'mt-3 inline-flex items-center gap-2 rounded-full bg-forest-100 px-3 py-1.5 text-xs font-bold text-forest-900';
                        statusElement.innerHTML = '<span class="h-2 w-2 rounded-full bg-forest-600"></span>Pembayaran lunas, membuka status pesanan...';
                        window.setTimeout(function () {
                            window.location.replace(data.redirect);
                        }, 800);
                    }
                } catch (error) {
                    return;
                }
            }

            window.setInterval(periksaStatus, 4000);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    periksaStatus();
                }
            });
            periksaStatus();
        });
    </script>
</body>

</html>
