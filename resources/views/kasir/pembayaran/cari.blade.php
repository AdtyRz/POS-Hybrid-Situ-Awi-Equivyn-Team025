@extends('layouts.admin')

@section('title', 'Cari Pesanan - Kasir')

@section('content')
<div class="max-w-3xl mx-auto p-6" x-data="pembayaranCariPage()">
    <style>[x-cloak] { display: none !important; }</style>
    <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
        <h2 class="font-bold text-2xl text-forest-900 mb-2">Cari Pesanan untuk Bayar</h2>
        <p class="text-sm text-stone-500 mb-4">Gunakan kode meja dan nama pemesan agar pesanan lebih mudah ditemukan.</p>

        <form method="GET" action="{{ route('kasir.pembayaran.cari') }}" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-600 mb-1">Kode Meja / Nama Pemesan</label>
                <input type="text" name="kode_pembayaran" value="{{ request()->input('kode_pembayaran') ?? request()->input('kode_pesanan') }}"
                       class="w-full px-4 py-3 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-lg"
                       placeholder="Contoh: LB-01 atau nama pemesan" autofocus>
            </div>
            <button type="submit" class="w-full bg-forest-900 hover:bg-[#042616] text-white font-bold py-3 rounded-xl text-lg shadow-sm transition">
                Cari Pesanan
            </button>
        </form>

        @if(isset($error))
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                {{ $error }}
            </div>
        @endif
    </div>

    <div x-show="sukses" x-cloak class="mb-4 bg-[#ECFDF5] border border-[#6EE7B7] text-[#065F46] rounded-xl px-4 py-3 text-xs font-semibold flex items-start gap-2">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="sukses"></span>
    </div>

    <div x-show="galat" x-cloak class="mb-4 bg-[#FEF2F2] border border-[#FCA5A5] text-[#991B1B] rounded-xl px-4 py-3 text-xs font-semibold flex items-start gap-2">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
        </svg>
        <span x-text="galat"></span>
    </div>

    <div data-live="kasir-pembayaran-cari">
    @if(isset($daftarPesanan) && !isset($error))
        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="font-bold text-xl text-forest-900">Hasil Pencarian</h2>
                <span class="text-xs font-bold text-stone-500">{{ $daftarPesanan->count() }} pesanan untuk &ldquo;{{ $kode }}&rdquo;</span>
            </div>

            <div class="space-y-3">
                @foreach($daftarPesanan as $item)
                    <a href="{{ route('kasir.pembayaran.create', $item->kode_pesanan) }}"
                       class="block rounded-xl border border-stone-100 bg-stone-50/60 p-4 hover:border-forest-300 hover:bg-forest-50/40 transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-forest-900">Meja {{ $item->meja->kode_meja }} &middot; {{ $item->nama_pelanggan ?: 'Tamu' }}</p>
                                <p class="text-xs text-stone-500 mt-1">{{ $item->waktu_pesan->timezone('Asia/Jakarta')->format('d M Y, H.i') }} WIB &middot; {{ $item->detailPesanan->sum('jumlah') }} item</p>
                                <p class="text-xs mt-1">
                                    <span class="font-semibold {{ $item->status_pembayaran === 'sudah_bayar' ? 'text-forest-700' : 'text-amber-700' }}">
                                        {{ $item->status_pembayaran === 'sudah_bayar' ? 'Sudah dibayar' : 'Belum dibayar' }}
                                    </span>
                                    <span class="text-stone-400">&middot; {{ ucfirst(str_replace('_', ' ', $item->status_pesanan)) }}</span>
                                </p>
                            </div>
                            <p class="font-extrabold text-forest-600 whitespace-nowrap">Rp{{ number_format($item->total_bayar, 0, ',', '.') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if(isset($pesanan))
        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="font-bold text-xl text-forest-900">Meja {{ $pesanan->meja->kode_meja }}</h2>
                    <p class="text-sm text-stone-500">Pemesan: <span class="font-bold text-stone-700">{{ $pesanan->nama_pelanggan ?: 'Tamu' }}</span></p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'bg-forest-100 text-forest-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'Lunas' : 'Belum Dibayar' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <div><span class="text-stone-500">Kode Meja</span><p class="font-bold font-mono">{{ $pesanan->meja->kode_meja }}</p></div>
                <div><span class="text-stone-500">Area</span><p class="font-bold">{{ $pesanan->meja->area }}</p></div>
                <div><span class="text-stone-500">Metode</span><p class="font-bold">{{ $pesanan->metode_pembayaran === 'transfer' ? 'Transfer Bank' : ucfirst($pesanan->metode_pembayaran) }}</p></div>
                <div><span class="text-stone-500">Status Pesanan</span><p class="font-bold">{{ ucfirst(str_replace('_', ' ', $pesanan->status_pesanan)) }}</p></div>
            </div>

            <div class="space-y-2 mb-4">
                @foreach($pesanan->detailPesanan as $item)
                    <div class="flex items-center justify-between py-2 border-b border-stone-100">
                        <div>
                            <p class="font-bold text-sm">{{ $item->jumlah }}x {{ $item->menu->nama_menu }}</p>
                            @if($item->catatan)
                                <p class="text-stone-400 text-xs">{{ $item->catatan }}</p>
                            @endif
                        </div>
                        <p class="font-bold text-sm">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-3 mt-3">
                <p class="font-bold text-lg">Total</p>
                <p class="font-extrabold text-2xl text-forest-600">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
            </div>

            @if(($pesanan->status_pembayaran === 'pending_pembayaran' || $pesanan->status_pembayaran === 'belum_bayar') && $pesanan->metode_pembayaran === 'transfer')
                <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50/70 p-4 space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-sky-800">Rekening Tujuan Transfer</p>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-500">Bank</span>
                        <strong class="text-forest-900">{{ $transfer['bank'] ?: '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-500">Nomor Rekening</span>
                        <strong class="font-mono text-forest-900">{{ $transfer['nomor'] ?: 'Belum diatur' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-500">Atas Nama</span>
                        <strong class="text-forest-900">{{ $transfer['atas_nama'] ?: '-' }}</strong>
                    </div>
                    <div class="flex items-center justify-between border-t border-sky-200 pt-2 mt-2">
                        <span class="text-sm font-bold text-sky-900">Nominal Transfer</span>
                        <strong class="text-lg font-extrabold text-forest-900">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('kasir.pembayaran.store') }}" class="mt-4 space-y-4"
                      data-no-ajax
                      @submit.prevent="bayar($event)">
                    @csrf
                    <input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">
                    <input type="hidden" name="metode_pembayaran" value="transfer">
                    <input type="hidden" name="jumlah_bayar" value="{{ $pesanan->total_bayar }}">

                    <p class="text-xs text-stone-500">Pastikan dana sudah masuk ke rekening resto sebelum mengonfirmasi.</p>

                    <button type="submit" :disabled="memproses"
                            class="w-full bg-sky-700 hover:bg-sky-800 text-white font-bold py-4 rounded-xl text-lg shadow-sm transition disabled:opacity-60 disabled:cursor-wait">
                        <span x-text="memproses ? 'Memproses...' : 'Konfirmasi Transfer Diterima'">Konfirmasi Transfer Diterima</span>
                    </button>
                </form>
            @elseif(in_array($pesanan->status_pembayaran, ['pending_verifikasi', 'pending_pembayaran', 'belum_bayar'], true) && $pesanan->metode_pembayaran === 'qris')
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-center space-y-1">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-amber-800">Menunggu Pembayaran QRIS</p>
                    <p class="text-sm text-amber-900">Pembayaran QRIS dikonfirmasi otomatis oleh Midtrans.</p>
                    <p class="text-xs text-amber-700">Tombol konfirmasi manual di kasir hanya tersedia untuk pembayaran Tunai dan Transfer.</p>
                </div>
            @elseif($pesanan->status_pembayaran === 'pending_pembayaran' || $pesanan->status_pembayaran === 'belum_bayar')
                <form method="POST" action="{{ route('kasir.pembayaran.store') }}" class="mt-6 space-y-4"
                      data-no-ajax
                      @submit.prevent="bayar($event)">
                    @csrf
                    <input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">
                    <input type="hidden" name="metode_pembayaran" value="tunai">

                    <div>
                        <label class="block text-sm font-medium text-stone-600 mb-1">Jumlah Bayar (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">Rp</span>
                            <input type="number" name="jumlah_bayar" min="0" step="500" value="{{ $pesanan->total_bayar }}" required
                                   class="w-full pl-8 pr-4 py-3 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-lg font-mono"
                                   placeholder="0">
                        </div>
                    </div>

                    <button type="submit" :disabled="memproses"
                            class="w-full bg-forest-900 hover:bg-[#042616] text-white font-bold py-4 rounded-xl text-lg shadow-sm transition disabled:opacity-60 disabled:cursor-wait">
                        <span x-text="memproses ? 'Memproses...' : 'Proses Pembayaran Tunai'">Proses Pembayaran Tunai</span>
                    </button>
                </form>
            @elseif($pesanan->status_pembayaran === 'sudah_bayar')
                <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-center">
                    <p class="font-bold">Pesanan sudah dibayar</p>
                    <p class="text-sm mt-1">Status: {{ $pesanan->status_pesanan }}</p>
                </div>
            @else
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700 text-center">
                    <p class="font-bold">Status pembayaran: {{ $pesanan->status_pembayaran }}</p>
                    <p class="text-sm mt-1">Pesanan tidak dalam status menunggu pembayaran tunai</p>
                </div>
            @endif
        </div>
    @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function pembayaranCariPage() {
        return {
            memproses: false,
            sukses: '',
            galat: '',
            async bayar(event) {
                if (this.memproses) return;

                const form = event.target;

                this.memproses = true;
                this.galat = '';
                this.sukses = '';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    const respons = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: new FormData(form),
                        credentials: 'same-origin',
                    });

                    const payload = await respons.json().catch(() => null);

                    if (respons.status === 419) {
                        throw new Error('Sesi Anda sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.');
                    }

                    if (!respons.ok || payload?.success !== true) {
                        const pesanField = payload?.errors
                            ? Object.values(payload.errors).flat()[0]
                            : null;

                        throw new Error(pesanField || payload?.message || 'Pembayaran gagal diproses.');
                    }

                    this.sukses = payload.message || 'Pembayaran berhasil diproses.';

                    if (typeof window.refreshLiveRegions === 'function') {
                        await window.refreshLiveRegions({ immediate: true });
                    } else {
                        window.location.reload();
                    }
                } catch (error) {
                    this.galat = error.message || 'Pembayaran gagal diproses.';
                } finally {
                    this.memproses = false;
                }
            },
        };
    }
</script>
@endpush
