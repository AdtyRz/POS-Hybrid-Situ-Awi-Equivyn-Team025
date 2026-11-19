@extends('layouts.admin')

@section('title', 'Arus Kas & Laporan Keuangan')
@section('page_category', 'Laporan & Ekspor')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="arusKasApp()">

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">ARUS KAS</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Arus Kas & Laporan Keuangan
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Catat pembelian bahan dan pengeluaran operasional, lalu pantau arus kas bersih: pemasukan lunas dikurangi seluruh kas keluar pada periode terpilih.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.arus-kas.ekspor', ['format' => 'csv', 'start' => $mulaiQuery, 'end' => $selesaiQuery]) }}"
                   class="h-10 px-4 rounded-xl bg-[#B1F1C2] hover:bg-[#9fe4b2] active:scale-95 text-[#0B4D2B] font-semibold text-xs flex items-center gap-2 shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Dataset CSV</span>
                </a>

                <a href="{{ route('admin.arus-kas.ekspor', ['format' => 'xls', 'start' => $mulaiQuery, 'end' => $selesaiQuery]) }}"
                   class="h-10 px-4 rounded-xl bg-[#FFDEA1] hover:bg-[#ebd055] active:scale-95 text-[#261900] font-semibold text-xs flex items-center gap-2 shadow-xs transition-all">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Dataset Excel (.xls)</span>
                </a>

                <a href="{{ route('admin.arus-kas.cetak', ['start' => $mulaiQuery, 'end' => $selesaiQuery]) }}" target="_blank"
                   class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] active:scale-95 text-[#404941] font-semibold text-xs flex items-center gap-2 shadow-xs transition-all">
                    <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak / PDF</span>
                </a>
            </div>
        </div>
    </div>

    @if (session('sukses'))
        <div class="p-4 rounded-2xl bg-[#B1F1C2]/50 border border-[#0B4D2B]/15 text-xs font-semibold text-[#0B4D2B] flex items-center gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-[#FFDAD6]/60 border border-[#BA1A1A]/20 text-xs font-semibold text-[#93000A] flex items-start gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            <ul class="flex flex-col gap-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.arus-kas') }}" class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs p-5 flex flex-col lg:flex-row lg:items-end gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal Mulai</label>
            <input type="date" name="start" value="{{ $mulaiQuery }}"
                   class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal Selesai</label>
            <input type="date" name="end" value="{{ $selesaiQuery }}"
                   class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                Terapkan Periode
            </button>
            <a href="{{ route('admin.arus-kas') }}" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold flex items-center transition-all">
                Bulan Ini
            </a>
        </div>
        <div class="lg:ml-auto text-[11px] font-semibold text-[#707971]">
            Periode aktif: <span class="text-[#0B4D2B]">{{ $labelPeriode }}</span>
        </div>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PEMASUKAN KAS</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Pembayaran lunas periode ini</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/50 flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <span class="font-display font-bold text-2xl sm:text-[26px] text-[#0B4D2B] tracking-tight leading-tight">
                Rp {{ number_format($ringkasan['pemasukan'], 0, ',', '.') }}
            </span>

            <div class="flex flex-col gap-1 mt-2 text-[10px] font-semibold text-[#707971]">
                <span class="flex items-center justify-between gap-2">
                    <span>Tunai</span>
                    <span class="text-[#1C1C18]">Rp {{ number_format($ringkasan['metode']['tunai']['total'], 0, ',', '.') }}</span>
                </span>
                <span class="flex items-center justify-between gap-2">
                    <span>QRIS</span>
                    <span class="text-[#1C1C18]">Rp {{ number_format($ringkasan['metode']['qris']['total'], 0, ',', '.') }}</span>
                </span>
                <span class="flex items-center justify-between gap-2">
                    <span>Transfer</span>
                    <span class="text-[#1C1C18]">Rp {{ number_format($ringkasan['metode']['transfer']['total'], 0, ',', '.') }}</span>
                </span>
            </div>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PEMBELIAN BAHAN</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">{{ number_format($ringkasan['jumlah_pembelian'], 0, ',', '.') }} catatan pembelian</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/60 flex items-center justify-center text-[#7A5900]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <span class="font-display font-bold text-2xl sm:text-[26px] text-[#7A5900] tracking-tight leading-tight">
                Rp {{ number_format($ringkasan['pembelian'], 0, ',', '.') }}
            </span>

            <span class="text-[10px] font-semibold text-[#707971] mt-2">
                Belanja bahan baku dapur pada {{ $labelPeriode }}
            </span>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PENGELUARAN OPERASIONAL</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">{{ number_format($ringkasan['jumlah_pengeluaran'], 0, ',', '.') }} catatan pengeluaran</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FFDAD6]/70 flex items-center justify-center text-[#93000A]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
            </div>

            <span class="font-display font-bold text-2xl sm:text-[26px] text-[#93000A] tracking-tight leading-tight">
                Rp {{ number_format($ringkasan['pengeluaran'], 0, ',', '.') }}
            </span>

            <span class="text-[10px] font-semibold text-[#707971] mt-2">
                Listrik, gaji, sewa, perawatan, dan biaya lain
            </span>
        </div>

        <div class="bg-[#0B4D2B] rounded-2xl p-6 shadow-xs flex flex-col justify-between min-h-[190px] relative overflow-hidden">
            <div class="flex items-start justify-between relative z-10">
                <div>
                    <span class="text-[10px] font-bold text-[#B1F1C2] uppercase tracking-wider">ARUS KAS BERSIH</span>
                    <h4 class="text-xs font-semibold text-white/70 mt-0.5">Pemasukan − total kas keluar</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-[#B1F1C2]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>

            <span class="font-display font-bold text-2xl sm:text-[26px] tracking-tight leading-tight relative z-10 {{ $ringkasan['arus_kas'] < 0 ? 'text-[#FFDAD6]' : 'text-white' }}">
                Rp {{ number_format($ringkasan['arus_kas'], 0, ',', '.') }}
            </span>

            <span class="text-[10px] font-semibold text-[#B1F1C2]/90 mt-2 relative z-10">
                Total kas keluar: Rp {{ number_format($ringkasan['total_keluar'], 0, ',', '.') }}
            </span>

            <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-white/5 blur-lg pointer-events-none"></div>
        </div>
    </div>

    @if (! empty($ringkasan['kategori']))
        <div class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs p-5">
            <div class="flex items-center justify-between gap-3 mb-3">
                <h3 class="font-bold text-sm text-[#1C1C18]">Rincian Pengeluaran per Kategori</h3>
                <span class="text-[10px] font-semibold text-[#707971]">{{ $labelPeriode }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($ringkasan['kategori'] as $kode => $total)
                    <span class="px-3 py-1.5 rounded-lg bg-[#F6F3EC] border border-[#C0C9BF]/30 text-[11px] font-semibold text-[#404941]">
                        {{ $daftarKategori[$kode] ?? ucfirst(str_replace('_', ' ', $kode)) }}
                        <span class="text-[#93000A] font-bold ml-1">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs overflow-hidden flex flex-col">
            <div class="p-5 flex items-center justify-between gap-3 border-b border-gray-100">
                <div>
                    <h2 class="font-bold text-base text-[#1C1C18]">Pembelian Bahan</h2>
                    <p class="text-[11px] text-[#707971] mt-0.5">Belanja bahan baku pada {{ $labelPeriode }}</p>
                </div>
                <button type="button" @click="bukaTambahPembelian()"
                        class="h-9 px-3.5 rounded-xl bg-[#B1F1C2] hover:bg-[#9fe4b2] active:scale-95 text-[#0B4D2B] text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Tambah
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F6F3EC] text-[10px] uppercase tracking-wider text-[#707971]">
                        <tr>
                            <th class="px-4 py-3 font-bold">Tanggal</th>
                            <th class="px-4 py-3 font-bold">Supplier / Keterangan</th>
                            <th class="px-4 py-3 font-bold text-right">Total</th>
                            <th class="px-4 py-3 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pembelian as $baris)
                            <tr class="hover:bg-[#F6F3EC]/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-[#1C1C18] whitespace-nowrap">{{ $baris->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-[#1C1C18]">{{ $baris->supplier ?: 'Tanpa supplier' }}</div>
                                    <div class="text-[10px] text-[#707971] mt-0.5">{{ $baris->keterangan ?: '—' }}</div>
                                    @if ($baris->detail->isNotEmpty())
                                        <div class="text-[10px] font-semibold text-[#0B4D2B] mt-0.5">{{ $baris->detail->count() }} bahan baku tercatat</div>
                                    @endif
                                    <div class="text-[10px] text-[#707971] mt-0.5">Dicatat: {{ $baris->pencatat?->nama ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-[#7A5900] whitespace-nowrap">Rp {{ number_format((float) $baris->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="bukaUbahPembelian({{ $baris->id }})" title="Ubah"
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#404941] hover:bg-[#F0EEE7] flex items-center justify-center transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.arus-kas.pembelian.hapus', $baris) }}" onsubmit="return confirm('Hapus pembelian bahan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#93000A] hover:bg-[#FFDAD6]/60 flex items-center justify-center transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-[#707971]">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-8 h-8 text-[#C0C9BF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        <span class="text-xs font-semibold">Belum ada pembelian bahan pada periode ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-auto p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-3 text-[11px] font-semibold text-[#707971]">
                <span>Menampilkan {{ $pembelian->firstItem() ?? 0 }}-{{ $pembelian->lastItem() ?? 0 }} dari {{ $pembelian->total() }} pembelian</span>
                @if ($pembelian->hasPages())
                    <div class="flex items-center gap-1">
                        @if ($pembelian->onFirstPage())
                            <span class="w-8 h-8 rounded-lg bg-white/60 border border-gray-200 text-gray-400 flex items-center justify-center">‹</span>
                        @else
                            <a href="{{ $pembelian->previousPageUrl() }}" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center">‹</a>
                        @endif
                        <span class="px-2 text-[#1C1C18] font-bold">{{ $pembelian->currentPage() }} / {{ $pembelian->lastPage() }}</span>
                        @if ($pembelian->hasMorePages())
                            <a href="{{ $pembelian->nextPageUrl() }}" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center">›</a>
                        @else
                            <span class="w-8 h-8 rounded-lg bg-white/60 border border-gray-200 text-gray-400 flex items-center justify-center">›</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs overflow-hidden flex flex-col">
            <div class="p-5 flex items-center justify-between gap-3 border-b border-gray-100">
                <div>
                    <h2 class="font-bold text-base text-[#1C1C18]">Pengeluaran Operasional</h2>
                    <p class="text-[11px] text-[#707971] mt-0.5">Biaya operasional pada {{ $labelPeriode }}</p>
                </div>
                <button type="button" @click="bukaTambahPengeluaran()"
                        class="h-9 px-3.5 rounded-xl bg-[#FFDEA1] hover:bg-[#ebd055] active:scale-95 text-[#261900] text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Tambah
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F6F3EC] text-[10px] uppercase tracking-wider text-[#707971]">
                        <tr>
                            <th class="px-4 py-3 font-bold">Tanggal</th>
                            <th class="px-4 py-3 font-bold">Kategori / Keterangan</th>
                            <th class="px-4 py-3 font-bold text-right">Jumlah</th>
                            <th class="px-4 py-3 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($pengeluaran as $baris)
                            <tr class="hover:bg-[#F6F3EC]/40 transition-colors">
                                <td class="px-4 py-3 font-semibold text-[#1C1C18] whitespace-nowrap">{{ $baris->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-[#1C1C18]">{{ $baris->labelKategori() }}</div>
                                    <div class="text-[10px] text-[#707971] mt-0.5">{{ $baris->keterangan }}</div>
                                    <div class="text-[10px] text-[#707971] mt-0.5">Dicatat: {{ $baris->pencatat?->nama ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-[#93000A] whitespace-nowrap">Rp {{ number_format((float) $baris->jumlah, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="bukaUbahPengeluaran({{ $baris->id }})" title="Ubah"
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#404941] hover:bg-[#F0EEE7] flex items-center justify-center transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.arus-kas.pengeluaran.hapus', $baris) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#93000A] hover:bg-[#FFDAD6]/60 flex items-center justify-center transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-[#707971]">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-8 h-8 text-[#C0C9BF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                                        <span class="text-xs font-semibold">Belum ada pengeluaran operasional pada periode ini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-auto p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-3 text-[11px] font-semibold text-[#707971]">
                <span>Menampilkan {{ $pengeluaran->firstItem() ?? 0 }}-{{ $pengeluaran->lastItem() ?? 0 }} dari {{ $pengeluaran->total() }} pengeluaran</span>
                @if ($pengeluaran->hasPages())
                    <div class="flex items-center gap-1">
                        @if ($pengeluaran->onFirstPage())
                            <span class="w-8 h-8 rounded-lg bg-white/60 border border-gray-200 text-gray-400 flex items-center justify-center">‹</span>
                        @else
                            <a href="{{ $pengeluaran->previousPageUrl() }}" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center">‹</a>
                        @endif
                        <span class="px-2 text-[#1C1C18] font-bold">{{ $pengeluaran->currentPage() }} / {{ $pengeluaran->lastPage() }}</span>
                        @if ($pengeluaran->hasMorePages())
                            <a href="{{ $pengeluaran->nextPageUrl() }}" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center">›</a>
                        @else
                            <span class="w-8 h-8 rounded-lg bg-white/60 border border-gray-200 text-gray-400 flex items-center justify-center">›</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-base text-[#1C1C18]">HPP & Laba Kotor</h2>
                <p class="text-[11px] text-[#707971] mt-0.5">
                    HPP dihitung dari resep aktif × harga rata-rata bahan saat ini, untuk penjualan lunas pada {{ $labelPeriode }}.
                </p>
            </div>
            <a href="{{ route('admin.bahan-baku') }}"
               class="h-9 px-3.5 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold flex items-center gap-1.5 transition-all self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                Atur Resep
            </a>
        </div>

        <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl bg-[#F6F3EC] border border-[#C0C9BF]/20 p-4">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">HPP Terjual</span>
                <div class="font-display font-bold text-xl text-[#7A5900] mt-1">Rp {{ number_format($ringkasan['hpp'], 0, ',', '.') }}</div>
                <span class="text-[10px] font-semibold text-[#707971]">{{ count($hpp['baris']) }} menu terjual</span>
            </div>
            <div class="rounded-xl bg-[#F6F3EC] border border-[#C0C9BF]/20 p-4">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Laba Kotor</span>
                <div class="font-display font-bold text-xl mt-1 {{ $ringkasan['laba_kotor'] < 0 ? 'text-[#93000A]' : 'text-[#0B4D2B]' }}">Rp {{ number_format($ringkasan['laba_kotor'], 0, ',', '.') }}</div>
                <span class="text-[10px] font-semibold text-[#707971]">Omzet − HPP</span>
            </div>
            <div class="rounded-xl bg-[#F6F3EC] border border-[#C0C9BF]/20 p-4">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Margin Laba Kotor</span>
                <div class="font-display font-bold text-xl mt-1 {{ $ringkasan['margin'] < 0 ? 'text-[#93000A]' : 'text-[#00341A]' }}">{{ number_format($ringkasan['margin'], 1, ',', '.') }}%</div>
                <span class="text-[10px] font-semibold text-[#707971]">Dari omzet lunas</span>
            </div>
        </div>

        <div class="overflow-x-auto border-t border-gray-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F6F3EC] text-[10px] uppercase tracking-wider text-[#707971]">
                    <tr>
                        <th class="px-4 py-3 font-bold">Menu</th>
                        <th class="px-4 py-3 font-bold text-right">Terjual</th>
                        <th class="px-4 py-3 font-bold text-right">Omzet</th>
                        <th class="px-4 py-3 font-bold text-right">HPP / Unit</th>
                        <th class="px-4 py-3 font-bold text-right">Total HPP</th>
                        <th class="px-4 py-3 font-bold text-right">Laba Kotor</th>
                        <th class="px-4 py-3 font-bold text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($hpp['baris'] as $baris)
                        <tr class="hover:bg-[#F6F3EC]/40 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-[#1C1C18]">{{ $baris['nama_menu'] }}</div>
                                <div class="text-[10px] mt-0.5 {{ $baris['jumlah_bahan'] > 0 ? 'text-[#707971]' : 'text-[#93000A] font-semibold' }}">
                                    {{ $baris['jumlah_bahan'] > 0 ? $baris['jumlah_bahan'].' bahan resep' : 'Resep belum diatur' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-[#1C1C18]">{{ number_format($baris['jumlah'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-[#0B4D2B]">Rp {{ number_format($baris['omzet'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-[#7A5900]">Rp {{ number_format($baris['hpp_satuan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-[#7A5900]">Rp {{ number_format($baris['total_hpp'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $baris['laba_kotor'] < 0 ? 'text-[#93000A]' : 'text-[#00341A]' }}">Rp {{ number_format($baris['laba_kotor'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $baris['margin'] < 0 ? 'text-[#93000A]' : 'text-[#404941]' }}">{{ number_format($baris['margin'], 1, ',', '.') }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-[#707971]">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-[#C0C9BF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <span class="text-xs font-semibold">Belum ada penjualan lunas pada periode ini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if (count($hpp['baris']) > 0)
                    <tfoot class="bg-[#F6F3EC] text-[#1C1C18] font-bold">
                        <tr>
                            <td class="px-4 py-3" colspan="2">Total</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($hpp['total_omzet'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($hpp['total_hpp'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right {{ $hpp['laba_kotor'] < 0 ? 'text-[#93000A]' : 'text-[#0B4D2B]' }}">Rp {{ number_format($hpp['laba_kotor'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($hpp['margin'], 1, ',', '.') }}%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div x-show="modalPembelian" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[92vh] overflow-y-auto" @click.outside="modalPembelian = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="pembelianForm.id ? 'Ubah Pembelian Bahan' : 'Tambah Pembelian Bahan'"></h3>
                    <p class="text-[11px] text-[#707971] mt-0.5">Catat belanja bahan baku dapur.</p>
                </div>
                <button type="button" @click="modalPembelian = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="pembelianForm.id ? pembelianUbahUrl.replace('__ID__', pembelianForm.id) : pembelianSimpanUrl" class="p-5 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-bind:disabled="!pembelianForm.id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal <span class="text-[#93000A]">*</span></label>
                        <input type="date" name="tanggal" x-model="pembelianForm.tanggal" required
                               class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Supplier</label>
                        <input type="text" name="supplier" x-model="pembelianForm.supplier" maxlength="100" placeholder="Contoh: Pasar Induk"
                               class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Keterangan</label>
                    <textarea name="keterangan" x-model="pembelianForm.keterangan" rows="2" maxlength="255" placeholder="Contoh: Beras, sayur, dan bumbu dapur"
                              class="px-3 py-2 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30"></textarea>
                </div>

                <div class="flex flex-col gap-2 border border-[#C0C9BF]/30 rounded-2xl p-4 bg-[#F6F3EC]/40">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-xs font-bold text-[#1C1C18]">Rincian Bahan</h4>
                            <p class="text-[10px] text-[#707971] mt-0.5">Isi bahan yang dibeli agar stok dan HPP ikut diperbarui.</p>
                        </div>
                        <button type="button" @click="tambahItemPembelian()"
                                class="h-8 px-3 rounded-lg bg-[#B1F1C2] hover:bg-[#9fe4b2] text-[#0B4D2B] text-[10px] font-bold flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Tambah Bahan
                        </button>
                    </div>

                    <template x-if="pembelianForm.items.length === 0">
                        <div class="px-3 py-4 rounded-xl bg-white/70 border border-dashed border-[#C0C9BF] text-center text-[10px] font-semibold text-[#707971]">
                            Belum ada rincian bahan. Pembelian tetap bisa disimpan sebagai catatan total saja.
                        </div>
                    </template>

                    <div class="flex flex-col gap-2">
                        <template x-for="(item, index) in pembelianForm.items" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-start bg-white/80 border border-[#C0C9BF]/30 rounded-xl p-2.5">
                                <div class="col-span-12 sm:col-span-5">
                                    <select :name="`items[${index}][bahan_baku_id]`" x-model="item.bahan_baku_id" @change="pilihBahanPembelian(index)" required
                                            class="w-full h-9 px-2.5 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                        <option value="">Pilih bahan</option>
                                        <template x-for="bahan in bahanData" :key="bahan.id">
                                            <option :value="bahan.id" x-text="`${bahan.nama} (${bahan.satuan})`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-4 sm:col-span-2">
                                    <input type="number" :name="`items[${index}][jumlah]`" x-model="item.jumlah" @input="hitungTotalPembelian()" min="0.001" step="0.001" required placeholder="Qty"
                                           class="w-full h-9 px-2 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                </div>
                                <div class="col-span-5 sm:col-span-3">
                                    <input type="number" :name="`items[${index}][harga_satuan]`" x-model="item.harga_satuan" @input="hitungTotalPembelian()" min="0" step="0.01" required placeholder="Harga satuan"
                                           class="w-full h-9 px-2 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                </div>
                                <div class="col-span-3 sm:col-span-2 flex items-center justify-end gap-1.5">
                                    <span class="text-[10px] font-bold text-[#7A5900] whitespace-nowrap" x-text="formatRupiah((parseFloat(item.jumlah) || 0) * (parseFloat(item.harga_satuan) || 0))"></span>
                                    <button type="button" @click="hapusItemPembelian(index)"
                                            class="w-8 h-9 rounded-lg bg-white border border-gray-200 text-[#93000A] hover:bg-[#FFDAD6]/60 flex items-center justify-center cursor-pointer flex-shrink-0" title="Hapus bahan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Total (Rp) <span class="text-[#93000A]">*</span></label>
                    <input type="number" name="total" x-model="pembelianForm.total" :readonly="pembelianForm.items.length > 0" min="0.01" step="0.01" required placeholder="0"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30"
                           :class="pembelianForm.items.length > 0 ? 'text-[#0B4D2B] font-bold' : ''">
                    <span class="text-[10px] font-semibold text-[#707971]" x-show="pembelianForm.items.length > 0">Total dihitung otomatis dari rincian bahan.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalPembelian = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalPengeluaran" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="modalPengeluaran = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="pengeluaranForm.id ? 'Ubah Pengeluaran Operasional' : 'Tambah Pengeluaran Operasional'"></h3>
                    <p class="text-[11px] text-[#707971] mt-0.5">Catat biaya operasional di luar pembelian bahan.</p>
                </div>
                <button type="button" @click="modalPengeluaran = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="pengeluaranForm.id ? pengeluaranUbahUrl.replace('__ID__', pengeluaranForm.id) : pengeluaranSimpanUrl" class="p-5 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-bind:disabled="!pengeluaranForm.id">

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal <span class="text-[#93000A]">*</span></label>
                    <input type="date" name="tanggal" x-model="pengeluaranForm.tanggal" required
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Kategori <span class="text-[#93000A]">*</span></label>
                    <select name="kategori" x-model="pengeluaranForm.kategori" required
                            class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                        @foreach ($daftarKategori as $kode => $label)
                            <option value="{{ $kode }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Keterangan <span class="text-[#93000A]">*</span></label>
                    <textarea name="keterangan" x-model="pengeluaranForm.keterangan" rows="2" maxlength="255" required placeholder="Contoh: Token listrik bulan ini"
                              class="px-3 py-2 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30"></textarea>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Jumlah (Rp) <span class="text-[#93000A]">*</span></label>
                    <input type="number" name="jumlah" x-model="pengeluaranForm.jumlah" min="0.01" step="0.01" required placeholder="0"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalPengeluaran = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function arusKasApp() {
    return {
        modalPembelian: false,
        modalPengeluaran: false,
        hariIni: @js($hariIni),
        pembelianData: @json($pembelianJson),
        pengeluaranData: @json($pengeluaranJson),
        bahanData: @json($bahanJson),
        pembelianSimpanUrl: @js(route('admin.arus-kas.pembelian.simpan')),
        pembelianUbahUrl: @js(route('admin.arus-kas.pembelian.ubah', ['pembelian' => '__ID__'])),
        pengeluaranSimpanUrl: @js(route('admin.arus-kas.pengeluaran.simpan')),
        pengeluaranUbahUrl: @js(route('admin.arus-kas.pengeluaran.ubah', ['pengeluaran' => '__ID__'])),
        pembelianForm: { id: null, tanggal: '', supplier: '', keterangan: '', total: '', items: [] },
        pengeluaranForm: { id: null, tanggal: '', kategori: 'bahan_bakar', keterangan: '', jumlah: '' },

        bukaTambahPembelian() {
            this.pembelianForm = {
                id: null,
                tanggal: this.hariIni,
                supplier: '',
                keterangan: '',
                total: '',
                items: [],
            };
            this.modalPembelian = true;
        },

        bukaUbahPembelian(id) {
            const baris = this.pembelianData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.pembelianForm = {
                id: baris.id,
                tanggal: baris.tanggal || this.hariIni,
                supplier: baris.supplier || '',
                keterangan: baris.keterangan || '',
                total: baris.total,
                items: (baris.items || []).map((item) => ({
                    bahan_baku_id: item.bahan_baku_id,
                    jumlah: item.jumlah,
                    harga_satuan: item.harga_satuan,
                })),
            };
            this.modalPembelian = true;
        },

        tambahItemPembelian() {
            this.pembelianForm.items.push({
                bahan_baku_id: '',
                jumlah: '',
                harga_satuan: '',
            });
        },

        hapusItemPembelian(index) {
            this.pembelianForm.items.splice(index, 1);
            this.hitungTotalPembelian();
        },

        pilihBahanPembelian(index) {
            const item = this.pembelianForm.items[index];
            const bahan = this.bahanData.find((baris) => Number(baris.id) === Number(item.bahan_baku_id));

            if (bahan && (item.harga_satuan === '' || Number(item.harga_satuan) === 0)) {
                item.harga_satuan = bahan.harga_rata_rata || '';
            }

            this.hitungTotalPembelian();
        },

        hitungTotalPembelian() {
            const total = this.pembelianForm.items.reduce((jumlah, item) => {
                return jumlah + (parseFloat(item.jumlah) || 0) * (parseFloat(item.harga_satuan) || 0);
            }, 0);

            if (this.pembelianForm.items.length > 0) {
                this.pembelianForm.total = total > 0 ? total.toFixed(2) : '';
            }

            return total;
        },

        formatRupiah(nilai) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(nilai) || 0);
        },

        bukaTambahPengeluaran() {
            this.pengeluaranForm = { id: null, tanggal: this.hariIni, kategori: 'bahan_bakar', keterangan: '', jumlah: '' };
            this.modalPengeluaran = true;
        },

        bukaUbahPengeluaran(id) {
            const baris = this.pengeluaranData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.pengeluaranForm = {
                id: baris.id,
                tanggal: baris.tanggal || this.hariIni,
                kategori: baris.kategori || 'bahan_bakar',
                keterangan: baris.keterangan || '',
                jumlah: baris.jumlah,
            };
            this.modalPengeluaran = true;
        },
    };
}
</script>
@endpush
