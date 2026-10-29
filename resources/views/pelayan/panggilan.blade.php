@extends('layouts.pelayan')

@section('title', 'Antrean Pelayan')

@section('content')
<div class="pb-20">
    <header class="sticky top-0 z-40 bg-[#FCF9F2]/95 backdrop-blur-md border-b border-[#E5E2DB]/70 px-4 py-3 flex items-center justify-between">
        <h1 class="font-display font-bold text-lg text-[#1C1C18]">Antrean Pelayan</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#FDF5E6] hover:bg-[#F5ECD8] border border-[#E0B24E]/40 hover:border-[#E0B24E] text-xs font-bold text-[#7A5C0F] whitespace-nowrap flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar</span>
            </button>
        </form>
    </header>

    <main class="px-4 py-4 space-y-4">
        <p class="text-[#404941] text-sm">Daftar panggilan meja yang menunggu respons</p>

        @if ($panggilan->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-[#E5E2DB] shadow-sm p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-[#908E8A] mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p class="font-bold text-lg text-[#404941]">Tidak ada panggilan menunggu</p>
                <p class="text-[#908E8A] text-sm mt-1">Semua panggilan sudah ditangani</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($panggilan as $p)
                    <div class="bg-white rounded-2xl border border-[#E5E2DB] shadow-sm p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="font-bold text-lg text-[#1C1C18]">Meja {{ $p->meja->kode_meja }} ({{ $p->meja->area }})</p>
                                <p class="text-[#404941] text-sm mt-1">Dipanggil: {{ $p->waktu_panggil->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</p>
                                <p class="text-[#908E8A] text-xs mt-0.5">Waktu tunggu: {{ $p->waktu_panggil->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form method="POST" action="{{ route('pelayan.panggilan.ubah', $p->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status_panggilan" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-[#E5E2DB] focus:border-[#0B4D2B] focus:ring-2 focus:ring-[#0B4D2B]/20 outline-none text-sm font-medium bg-white">
                                        <option value="ditangani" {{ $p->status_panggilan == 'ditangani' ? 'selected' : '' }}>Ditangani</option>
                                        <option value="selesai" {{ $p->status_panggilan == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-6 p-4 bg-white rounded-2xl border border-[#E5E2DB] shadow-sm">
            <h2 class="font-bold text-[#1C1C18] mb-3">Pesanan Siap Antar</h2>
            @if ($siapAntar->isEmpty())
                <p class="text-[#908E8A] text-sm text-center py-4">Tidak ada pesanan siap diantar</p>
            @else
                <div class="space-y-2">
                    @foreach ($siapAntar as $p)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-[#D9EBE0] border border-[#0B4D2B]/20">
                            <div>
                                <p class="font-bold text-[#0B4D2B]">Pemesan: {{ $p->nama_pelanggan ?: 'Tamu' }}</p>
                                <p class="text-[#404941] text-xs">Meja {{ $p->meja->kode_meja }} • {{ $p->detailPesanan->sum('jumlah') }} item</p>
                            </div>
                            <form method="POST" action="{{ route('pesanan.status', $p->kode_pesanan) }}" class="shrink-0">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status_pesanan" value="diantar">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0B4D2B] hover:bg-[#052E1B] text-white text-xs font-bold transition">Selesai Antar</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
