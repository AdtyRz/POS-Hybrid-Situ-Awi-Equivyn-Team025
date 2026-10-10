@extends('layouts.admin')

@section('title', 'Dasbor Analitik & Laporan Penjualan')
@section('page_category', 'Dasbor Analitik')

@section('content')
<div class="flex flex-col gap-8 pb-12" x-data="{ 
    dateFilter: 'hari_ini',
    toast: { show: false, message: '' },
    showToast(msg) {
        this.toast.message = msg;
        this.toast.show = true;
        setTimeout(() => this.toast.show = false, 3000);
    }
}">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-2">
        <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 text-xs font-semibold text-[#707971]">
                <span>Situ Awi</span>
                <span>/</span>
                <span class="text-[#1C1C18]">Dasbor Analitik</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display font-bold text-2xl sm:text-[32px] text-[#00341A] tracking-tight leading-tight">
                    Dasbor Analitik & Intelijen Penjualan
                </h1>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FECE66]/30 text-[10px] font-bold text-[#765600] uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-[#7A5900] animate-pulse"></span>
                    <span>FR-008 • REAL-TIME SYNC</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 sm:gap-4">
            <div class="bg-[#F0EEE7] p-1 rounded-xl shadow-inner flex items-center gap-1">
                <button 
                    type="button" 
                    @click="dateFilter = 'hari_ini'"
                    :class="dateFilter === 'hari_ini' ? 'bg-white text-[#00341A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                    Hari Ini
                </button>
                <button 
                    type="button" 
                    @click="dateFilter = 'mingguan'"
                    :class="dateFilter === 'mingguan' ? 'bg-white text-[#00341A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                    Mingguan
                </button>
                <button 
                    type="button" 
                    @click="dateFilter = 'bulanan'"
                    :class="dateFilter === 'bulanan' ? 'bg-white text-[#00341A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                    Bulanan
                </button>
                <button 
                    type="button" 
                    @click="dateFilter = 'kustom'"
                    :class="dateFilter === 'kustom' ? 'bg-white text-[#00341A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                    Kustom
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="button" 
                    @click="showToast('Memproses cetak PDF ringkasan penjualan...')"
                    class="h-10 px-4 rounded-xl bg-[#FECE66]/25 hover:bg-[#FECE66]/40 active:scale-95 text-[#765600] font-semibold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#765600]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak PDF</span>
                </button>

                <button 
                    type="button" 
                    @click="showToast('Mengekspor data ke Microsoft Excel (.xlsx)...')"
                    class="h-10 px-4 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Ekspor XLSX</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="relative overflow-hidden bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">TOTAL OMZET HARI INI</span>
                <div class="w-10 h-10 rounded-xl bg-[#FECE66]/30 flex items-center justify-center text-[#765600]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] tracking-tight leading-tight">
                    Rp {{ number_format($pendapatan, 0, ',', '.') }}
                </span>
                <div class="flex items-center gap-1.5 mt-2 text-xs">
                    <span class="font-bold text-[#00341A] flex items-center gap-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        {{ $pendapatanKemarin > 0 ? (($pendapatan >= $pendapatanKemarin ? '+' : '') . number_format(($pendapatan - $pendapatanKemarin) / $pendapatanKemarin * 100, 1) . '%') : '-' }}
                    </span>
                    <span class="text-[#707971]">vs kemarin (Rp {{ number_format($pendapatanKemarin / 1000000, 2) }}jt)</span>
                </div>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/30 blur-xl pointer-events-none"></div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PESANAN SELESAI</span>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/40 flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] tracking-tight leading-tight">
                    {{ $pesananHariIni }} Tiket
                </span>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#707971]">Rata-rata: <strong class="text-[#1C1C18] font-bold">Rp {{ number_format($pesananHariIni > 0 ? $pendapatan / $pesananHariIni : 0, 0, ',', '.') }}</strong></span>
                    <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/30 text-[10px] font-bold text-[#0B4D2B]">
                        {{ $pesananTotal > 0 ? number_format($pesananHariIni / $pesananTotal * 100, 1) : 0 }}% Sukses
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">OKUPANSI SAUNG</span>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/50 flex items-center justify-center text-[#7A5900]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <div class="flex items-baseline gap-2">
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] tracking-tight leading-tight">
                        {{ $mejaTotal > 0 ? round($mejaTerisi / $mejaTotal * 100) : 0 }}%
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-[#EFC05A]/40 text-[10px] font-bold text-[#261900]">
                        Puncak Lesehan
                    </span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-xs">
                    <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                    <span class="text-[#707971] font-medium">{{ $mejaTerisi }} dari {{ $mejaTotal }} Saung terisi penuh</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">KDS KECEPATAN SAJI</span>
                <div class="w-10 h-10 rounded-xl bg-[#F0EEE7] flex items-center justify-center text-[#00341A]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] tracking-tight leading-tight">
                    {{ $rataMenit ? number_format($rataMenit, 1) : '-' }} Menit
                </span>
                <div class="flex items-center gap-1.5 mt-2 text-xs">
                    <span class="font-bold text-[#00341A]">{{ $rataMenit ? number_format(15 - $rataMenit, 1) . 'm lebih gesit' : 'Belum ada data' }}</span>
                    <span class="text-[#707971]">dari target (15.0m)</span>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4">
                <div class="flex flex-col">
                    <h2 class="font-bold text-lg sm:text-xl text-[#1C1C18] leading-tight">
                        Fluktuasi Penjualan
                    </h2>
                    <p class="text-xs text-[#707971] mt-0.5">
                        Pantauan volume pesanan & nilai kotor (7 hari terakhir)
                    </p>
                </div>

                <div class="flex items-center gap-4 text-xs font-bold text-[#404941]">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#0B4D2B]"></span>
                        <span>Omzet Saung (Rp)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#7A5900]"></span>
                        <span>Titik Puncak</span>
                    </div>
                </div>
            </div>

            <div class="w-full my-4 relative">
                @php
                    $nilaiMaks = max(array_column($grafik, 'total')) ?: 1;
                    $koordinat = [];
                    foreach ($grafik as $i => $hari) {
                        $koordinat[] = [30 + $i * (740 / 6), 240 - ($hari['total'] / $nilaiMaks) * 180];
                    }
                    $garis = 'M ' . round($koordinat[0][0], 1) . ' ' . round($koordinat[0][1], 1);
                    for ($i = 0; $i < count($koordinat) - 1; $i++) {
                        $p0 = $koordinat[max(0, $i - 1)];
                        $p1 = $koordinat[$i];
                        $p2 = $koordinat[$i + 1];
                        $p3 = $koordinat[min(count($koordinat) - 1, $i + 2)];
                        $garis .= ' C ' . round($p1[0] + ($p2[0] - $p0[0]) / 6, 1) . ' ' . round($p1[1] + ($p2[1] - $p0[1]) / 6, 1) . ', ' . round($p2[0] - ($p3[0] - $p1[0]) / 6, 1) . ' ' . round($p2[1] - ($p3[1] - $p1[1]) / 6, 1) . ', ' . round($p2[0], 1) . ' ' . round($p2[1], 1);
                    }
                    $indeksPuncak = array_search(max(array_column($grafik, 'total')), array_column($grafik, 'total'));
                    $titikPuncak = $koordinat[$indeksPuncak];
                    $kotakX = min(max($titikPuncak[0] - 85, 0), 600);
                    $kotakY = max($titikPuncak[1] - 52, 0);
                @endphp
                <svg viewBox="0 0 800 320" class="w-full h-64 overflow-visible" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="curveGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0B4D2B" stop-opacity="0.35" />
                            <stop offset="60%" stop-color="#0B4D2B" stop-opacity="0.08" />
                            <stop offset="100%" stop-color="#0B4D2B" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>

                    <line x1="30" y1="60" x2="770" y2="60" stroke="#F0EEE7" stroke-width="1.5" stroke-dasharray="4 4" />
                    <line x1="30" y1="120" x2="770" y2="120" stroke="#F0EEE7" stroke-width="1.5" stroke-dasharray="4 4" />
                    <line x1="30" y1="180" x2="770" y2="180" stroke="#F0EEE7" stroke-width="1.5" stroke-dasharray="4 4" />
                    <line x1="30" y1="240" x2="770" y2="240" stroke="#EBE8E1" stroke-width="1.5" />

                    <path d="{{ $garis }} L 770 240 L 30 240 Z" fill="url(#curveGradient)" />

                    <path d="{{ $garis }}"
                             stroke="#0B4D2B" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                    <circle cx="{{ round($titikPuncak[0], 1) }}" cy="{{ round($titikPuncak[1], 1) }}" r="6" fill="#7A5900" stroke="#FCF9F2" stroke-width="3" />

                    <g transform="translate({{ round($kotakX, 1) }}, {{ round($kotakY, 1) }})">
                        <rect width="170" height="36" rx="8" fill="#0B4D2B" />
                        <text x="85" y="15" fill="#FFFFFF" font-size="9" font-weight="600" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">Puncak {{ $grafik[$indeksPuncak]['label'] }}</text>
                        <text x="85" y="29" fill="#FECE66" font-size="11" font-weight="700" font-family="'Plus Jakarta Sans', sans-serif" text-anchor="middle">Rp {{ number_format($grafik[$indeksPuncak]['total'], 0, ',', '.') }}</text>
                    </g>
                </svg>

                <div class="flex items-center justify-between text-[10px] font-bold text-[#707971] px-2 pt-2 border-t border-[#EBE8E1]">
                    @foreach ($grafik as $hari)
                    <span>{{ $hari['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="p-3.5 bg-[#F0EEE7] rounded-2xl flex items-center justify-between gap-3 mt-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#EAE4D7] flex items-center justify-center text-[#7A5900] flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <p class="text-xs text-[#1C1C18] leading-tight">
                        @if ($putaran)
                        Perputaran meja tercepat tercatat pada <strong class="font-bold">Saung {{ $putaran->meja->kode_meja }}</strong> dengan durasi saji & santap rata-rata <strong class="font-bold">{{ round($putaran->menit) }} menit</strong>.
                        @else
                        Belum ada pesanan selesai hari ini untuk mengukur perputaran meja.
                        @endif
                    </p>
                </div>
                <button type="button" @click="showToast('Membuka audit log POS Saung Kelapa...')" class="text-[10px] font-bold text-[#707971] hover:text-[#00341A] uppercase tracking-wide flex-shrink-0 cursor-pointer">
                    Audit POS Log
                </button>
            </div>
        </div>

        <div class="lg:col-span-4 bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-lg sm:text-xl text-[#1C1C18]">Kanal Transaksi</h2>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#B1F1C2]/40 text-[10px] font-bold text-[#0B4D2B]">
                        H+0 Instant
                    </span>
                </div>
                <p class="text-xs text-[#707971] mt-1">
                    Distribusi penyelesaian tagihan tamu
                </p>

                <div class="relative flex items-center justify-center my-6">
                    @php
                        $totalKanal = $qrisHariIni + $tunaiHariIni;
                        $bagianQris = $totalKanal > 0 ? round($qrisHariIni / $totalKanal * 377) : 0;
                        $bagianTunai = 377 - $bagianQris;
                        $persenQris = $totalKanal > 0 ? round($qrisHariIni / $totalKanal * 100) : 0;
                    @endphp
                    <svg viewBox="0 0 160 160" class="w-44 h-44 -rotate-90">
                        <circle cx="80" cy="80" r="60" stroke="#EBE8E1" stroke-width="20" fill="none" />
                        <circle cx="80" cy="80" r="60" stroke="#0B4D2B" stroke-width="20" fill="none"
                                stroke-dasharray="{{ $bagianQris }} 377" stroke-dashoffset="0" />
                        <circle cx="80" cy="80" r="60" stroke="#FECE66" stroke-width="20" fill="none"
                                stroke-dasharray="{{ $bagianTunai }} 377" stroke-dashoffset="-{{ $bagianQris }}" />
                    </svg>

                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="font-display font-bold text-3xl text-[#1C1C18]">{{ $persenQris }}%</span>
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">NON-TUNAI</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2.5">
                <div class="p-3 bg-[#F0EEE7] rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-md bg-[#0B4D2B] flex-shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-[#1C1C18]">QRIS / Midtrans</span>
                            <span class="text-[11px] text-[#707971]">{{ $qrisHariIni }} Transaksi ({{ $persenQris }}%)</span>
                        </div>
                    </div>
                    <span class="font-bold text-xs text-[#00341A]">Rp {{ number_format($omzetQris, 0, ',', '.') }}</span>
                </div>

                <div class="p-3 bg-[#F0EEE7] rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-md bg-[#FECE66] flex-shrink-0"></span>
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold text-[#1C1C18]">Tunai Kasir POS</span>
                            <span class="text-[11px] text-[#707971]">{{ $tunaiHariIni }} Transaksi ({{ 100 - $persenQris }}%)</span>
                        </div>
                    </div>
                    <span class="font-bold text-xs text-[#7A5900]">Rp {{ number_format($omzetTunai, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-6 bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                        <h2 class="font-bold text-lg sm:text-xl text-[#1C1C18]">5 Menu Terlaris Hari Ini</h2>
                    </div>
                    <span class="text-xs font-semibold text-[#707971]">Total Porsi: {{ $terlaris->sum('porsi') }}</span>
                </div>
                <p class="text-xs text-[#707971] mb-5">
                    Daftar hidangan dengan perputaran paling cepat & penyumbang gross profit terbesar
                </p>

                <div class="flex flex-col gap-2.5">
                    @forelse ($terlaris as $laris)
                    <div class="p-2.5 sm:p-3 bg-[#F0EEE7] rounded-2xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-xl {{ $loop->first ? 'bg-[#FECE66]/40 text-[#765600]' : 'bg-[#E5E2DB] text-[#404941]' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ $loop->iteration }}
                            </span>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-[#1C1C18] leading-tight">{{ $laris->menu->nama_menu }}</span>
                                <span class="text-xs text-[#707971] mt-0.5">{{ $laris->porsi }} Porsi</span>
                            </div>
                        </div>
                        <span class="font-bold text-xs text-[#00341A] flex-shrink-0">Rp {{ number_format($laris->omzet, 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <div class="p-3 text-xs text-[#707971] italic">Belum ada penjualan hari ini.</div>
                    @endforelse
            </div>

            <div class="p-3.5 bg-[#FFDEA1]/40 rounded-2xl flex items-center justify-between gap-3 mt-4">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-[#7A5900] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-xs font-semibold text-[#261900]">
                        Menu stok menipis (≤ 5): <strong class="font-bold text-[#261900]">{{ $stokMenipis }} menu</strong>
                    </span>
                </div>
                <a href="{{ url('/admin/menu') }}" class="text-xs font-bold text-[#7A5900] hover:underline flex items-center gap-1 flex-shrink-0">
                    <span>Kelola Stok</span>
                    <span>→</span>
                </a>
            </div>
        </div>

        <div class="lg:col-span-6 bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-100 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0B4D2B]"></span>
                        <h2 class="font-bold text-lg sm:text-xl text-[#1C1C18]">Log Audit Transaksi Terkini</h2>
                    </div>
                    <span class="text-xs font-semibold text-[#707971]">Feed POS & Kiosk</span>
                </div>
                <p class="text-xs text-[#707971] mb-5">
                    Aktivitas penutupan bill meja, pembayaran, dan otorisasi kasir
                </p>

                <div class="flex flex-col gap-3">
                    <div class="p-3.5 bg-[#F0EEE7] rounded-2xl flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-bold text-[#1C1C18]">#ORD-9821</span>
                                <span class="text-[#707971]">•</span>
                                <span class="font-semibold text-[#0B4D2B]">Saung 02 (LB-02)</span>
                            </div>
                            <span class="text-xs text-[#707971] mt-1">Pak Dedi • 4 Item (Gurame Bakar + Liwet Komplit)</span>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="font-bold text-sm text-[#1C1C18]">Rp 482.000</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/40 text-[10px] font-semibold text-[#0B4D2B]">
                                QRIS Lunas
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 bg-[#F0EEE7] rounded-2xl flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-bold text-[#1C1C18]">#ORD-9820</span>
                                <span class="text-[#707971]">•</span>
                                <span class="font-semibold text-[#0B4D2B]">Saung Gandasoli (LB-04)</span>
                            </div>
                            <span class="text-xs text-[#707971] mt-1">Keluarga Ibu Maya • 3 Item Santapan Sunda</span>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="font-bold text-sm text-[#1C1C18]">Rp 315.000</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#FFDEA1]/50 text-[10px] font-semibold text-[#7A5900]">
                                Tunai Kasir Lunas
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 bg-[#FFDAD6]/30 border border-[#FFDAD6] rounded-2xl flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-bold text-[#BA1A1A]">#ORD-9819</span>
                                <span class="text-[#707971]">•</span>
                                <span class="font-semibold text-[#BA1A1A]">Kursi Kayu KA-02</span>
                            </div>
                            <span class="text-xs text-[#404941] mt-1">VOID Disetujui: Batal Sop Gurame (Salah Input)</span>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="font-bold text-sm text-[#707971]">Rp 0</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#FFDAD6] text-[10px] font-semibold text-[#93000A]">
                                VOID Dibatalkan
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 bg-[#F0EEE7] rounded-2xl flex items-center justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-bold text-[#1C1C18]">#ORD-9818</span>
                                <span class="text-[#707971]">•</span>
                                <span class="font-semibold text-[#0B4D2B]">Saung Dermaga (LB-05)</span>
                            </div>
                            <span class="text-xs text-[#707971] mt-1">Rombongan Dinas Pertanian • Paket Prasmanan</span>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <span class="font-bold text-sm text-[#1C1C18]">Rp 890.000</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/40 text-[10px] font-semibold text-[#0B4D2B]">
                                QRIS Lunas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-4 border-t border-gray-100 mt-2">
                <span class="text-[#707971] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#00341A] animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Sinkronisasi otomatis setiap 15 detik</span>
                </span>
                <a href="{{ url('/admin/laporan') }}" class="font-bold text-[#00341A] hover:underline flex items-center gap-1">
                    <span>Lihat Seluruh Log Transaksi</span>
                    <span>→</span>
                </a>
            </div>
        </div>

    </div>

    <div 
        x-cloak
        x-show="toast.show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-8 right-8 z-50 bg-[#00341A] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 border border-[#FECE66]/30 text-sm font-semibold">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3C7.7 6.2 6 8.4 6 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span x-text="toast.message"></span>
    </div>

</div>
@endsection

