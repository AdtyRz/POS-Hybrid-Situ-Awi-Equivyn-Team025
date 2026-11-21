<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="realtime-version-url" content="{{ route('realtime.version') }}">
    <meta name="realtime-version" content="{{ \App\Support\Realtime::version() }}">
    <title>Session Meja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script>
            window.__CHANNELS__ = ['kasir'];
        </script>
        @vite('resources/js/reverb.js')
    @endif
</head>

<body class="min-h-screen bg-[#FBF8F1] p-4" x-data="sessionPage()">
    <style>[x-cloak] { display: none !important; }</style>
    <div class="max-w-3xl mx-auto">
        <header class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('kasir.dashboard') }}"
                    class="flex items-center gap-2 text-[#6B6A63] hover:text-[#2B2A26] text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                @csrf
                <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FDF5E6] hover:bg-[#F5ECD8] border border-[#E0B24E]/40 hover:border-[#E0B24E] active:scale-95 transition text-xs font-bold text-[#7A5C0F] whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </header>

        <h1 class="font-display font-bold text-2xl text-[#052E1B] mb-2">Session Meja</h1>
        <p class="text-stone-500 text-sm mb-6">Kelola sesi meja aktif. Selesaikan sesi untuk menutup sesi pelanggan dan
            membuat kode unik baru.</p>

        @if (session('error'))
        <div class="bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] rounded-xl px-4 py-3 text-xs font-semibold flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if (session('sukses'))
        <div class="bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46] rounded-xl px-4 py-3 text-xs font-semibold flex items-center gap-2 mb-4">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
        @endif

        <div data-live="kasir-session-list">
        @foreach ($meja as $m)
            @php($daftarPesanan = $pesananSesi[$m->id] ?? collect())
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-bold text-lg text-forest-900">{{ $m->kode_meja }} ({{ $m->area }})</p>
                        <p class="text-stone-500 text-sm">Status: <span
                                class="font-semibold text-forest-700">{{ ucfirst($m->status_meja) }}</span></p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-stone-500">Kode sesi aktif:</span>
                            <span
                                class="px-3 py-1 rounded-lg bg-forest-900 text-gold-500 font-mono font-extrabold text-lg tracking-[0.3em]">{{ ($sesiAktif[$m->id] ?? null)->kode_unik ?? '----' }}</span>
                        </div>
                    </div>
                    @if ($daftarPesanan->count() > 0)
                        <button type="button"
                            @click="mintaKonfirmasi('{{ $m->kode_meja }}', true)"
                            class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold text-sm shadow-sm transition">
                            Selesaikan Sesi
                        </button>
                    @else
                        <button type="button"
                            @click="mintaKonfirmasi('{{ $m->kode_meja }}', false)"
                            class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold text-sm shadow-sm transition">
                            Selesaikan Sesi
                        </button>
                    @endif
                </div>

                @if ($daftarPesanan->count() > 0)
                    <div class="space-y-2 mb-3">
                        @foreach ($daftarPesanan as $p)
                            <div class="bg-cream-50 rounded-xl border border-stone-100 p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-sm text-forest-900">Pemesan: {{ $p->nama_pelanggan ?: 'Tamu' }}</p>
                                        <p class="text-stone-500 text-xs">{{ $p->detailPesanan->sum('jumlah') }} item •
                                            Rp{{ number_format($p->total_bayar, 0, ',', '.') }} •
                                            <span class="font-semibold">{{ $p->status_pesanan === 'selesai' ? 'Selesai' : 'Aktif' }}</span></p>
                                    </div>
                                    <span
                                        class="px-2 py-1 rounded-full text-[10px] font-bold {{ $p->status_pembayaran === 'sudah_bayar' ? 'bg-forest-100 text-forest-700' : 'bg-gold-100 text-gold-700' }}">
                                        {{ $p->status_pembayaran === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-stone-400 text-sm text-center py-4">Tidak ada pesanan pada sesi ini</p>
                @endif
            </div>
        @endforeach
        </div>

        <p class="text-center text-stone-400 text-xs mt-8">© 2026 Saung Situ Awi Ciwidey</p>
    </div>

    <div x-show="konfirmasi.open"
         x-cloak
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white rounded-2xl border border-stone-200 shadow-2xl overflow-hidden" @click.outside="konfirmasi.open = false">
            <div class="px-5 py-4 border-b border-stone-100 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-display font-bold text-lg text-[#00341A]">Selesaikan Sesi Meja</h3>
                    <p class="text-xs text-stone-500 mt-1 leading-relaxed" x-text="konfirmasi.message"></p>
                </div>
            </div>
            <div class="px-5 py-4 bg-[#FAF7F0] flex items-center justify-end gap-2">
                <button type="button" @click="konfirmasi.open = false"
                        :disabled="konfirmasi.busy"
                        class="px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-stone-700 font-bold text-xs hover:bg-stone-50 transition disabled:opacity-60 disabled:cursor-wait">
                    Batal
                </button>
                <button type="button" @click="jalankanKonfirmasi()"
                        :disabled="konfirmasi.busy"
                        class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-xs transition disabled:opacity-60 disabled:cursor-wait">
                    <span x-text="konfirmasi.busy ? 'Memproses...' : 'Ya, Selesaikan'">Ya, Selesaikan</span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="toast.open"
         x-cloak
         x-transition
         role="status"
         aria-live="polite"
         class="fixed bottom-6 right-6 z-[60] max-w-sm px-5 py-3.5 rounded-2xl shadow-xl text-sm font-semibold text-white flex items-start gap-3 border border-white/10"
         :class="toast.error ? 'bg-[#93000A]' : 'bg-[#00341A]'">
        <span class="mt-0.5 shrink-0">
            <svg x-show="toast.error" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
            </svg>
            <svg x-show="!toast.error" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </span>
        <span x-text="toast.message"></span>
    </div>

    <script>
        function sessionPage() {
            return {
                konfirmasi: {
                    open: false,
                    message: '',
                    kodeMeja: null,
                    busy: false,
                },
                toast: {
                    open: false,
                    message: '',
                    error: false,
                },
                toastTimer: null,
                mintaKonfirmasi(kodeMeja, adaPesanan) {
                    this.konfirmasi = {
                        open: true,
                        kodeMeja,
                        busy: false,
                        message: adaPesanan
                            ? 'Selesaikan sesi meja ini? Semua pesanan pada sesi ini akan ditutup dan kode unik baru akan dibuat.'
                            : 'Selesaikan sesi meja ini dan buat kode unik baru?',
                    };
                },
                tampilkanToast(message, error = false) {
                    this.toast = { open: true, message, error };
                    clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => {
                        this.toast.open = false;
                    }, 4000);
                },
                async jalankanKonfirmasi() {
                    if (this.konfirmasi.busy || !this.konfirmasi.kodeMeja) return;

                    this.konfirmasi.busy = true;
                    const kodeMeja = this.konfirmasi.kodeMeja;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    try {
                        const respons = await fetch(
                            @json(route('kasir.session.index')) + '/' + encodeURIComponent(kodeMeja),
                            {
                                method: 'PATCH',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({}),
                                credentials: 'same-origin',
                            }
                        );

                        const payload = await respons.json().catch(() => null);

                        if (!respons.ok || payload?.status !== 'ok') {
                            throw new Error(payload?.message || 'Sesi meja gagal diakhiri.');
                        }

                        this.konfirmasi = { open: false, message: '', kodeMeja: null, busy: false };
                        this.tampilkanToast(payload.message || 'Sesi meja berhasil diakhiri.');

                        if (typeof window.refreshLiveRegions === 'function') {
                            await window.refreshLiveRegions({ immediate: true });
                        } else {
                            window.location.reload();
                        }
                    } catch (error) {
                        this.tampilkanToast(error.message || 'Sesi meja gagal diakhiri.', true);
                    } finally {
                        this.konfirmasi.busy = false;
                    }
                },
            };
        }
    </script>
</body>

</html>
