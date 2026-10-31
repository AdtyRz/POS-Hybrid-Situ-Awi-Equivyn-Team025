@extends('layouts.admin')

@section('title', 'Master Data Meja, Saung & Dynamic QR')
@section('page_category', 'Master Data Meja/Saung')

@section('content')
    <div class="flex flex-col gap-8 pb-16" x-data="masterMejaApp()">

        @if (session('success'))
            <div class="p-3.5 rounded-2xl bg-[#B1F1C2]/50 border border-[#2E6A45]/30 text-xs font-semibold text-[#00341A]">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 rounded-2xl bg-[#FFDAD6] border border-red-300 text-xs font-semibold text-[#BA1A1A]">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col gap-3">
            <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
                <div class="flex items-center gap-2 text-[#707971]">
                    <span>Situ Awi</span>
                    <span>/</span>
                    <span class="text-[#00341A]">Master Data Meja/Saung</span>
                </div>

                <div
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F0EEE7] text-[10px] font-bold text-[#707971]">
                    <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                    <span class="tracking-wide">INFRASTRUKTUR SAUNG & IOT TABLE NODE</span>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
                <div class="flex flex-col max-w-2xl">
                    <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                        Master Data Meja, Saung Lesehan & Dynamic QR
                    </h1>
                    <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                        Pemetaan real-time {{ $statistik['total_meja'] }} unit saung, status hardware IoT, dan proteksi Dynamic QR
                        anti-order palsu.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                    <button type="button" @click="window.open('{{ route('admin.master-meja.cetak-qr') }}', '_blank')"
                        class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] active:scale-95 text-[#1C1C18] font-semibold text-xs sm:text-sm flex items-center gap-2 transition-all shadow-xs cursor-pointer">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak Semua QR Saung (PDF)</span>
                    </button>

                    <button type="button" @click="addModalOpen = true"
                        class="h-10 sm:h-11 px-4 sm:px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs sm:text-sm flex items-center gap-2 shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-white stroke-[2.5]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Meja / Saung Baru</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <div
                class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px]">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">KAPASITAS TOTAL SAUNG</span>
                    <div class="w-9 h-9 rounded-xl bg-[#F0EEE7] flex items-center justify-center text-[#0B4D2B]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>

                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    {{ $statistik['total_meja'] }} Unit Saung
                </span>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#404941] font-semibold text-[10px]">Maks. {{ $statistik['total_kapasitas'] }} Tamu Lesehan</span>
                    <span
                        class="px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[10px] font-semibold text-[#00341A] flex flex-col justify-center items-center text-center">
                        {{ $statistik['jumlah_area'] }} Klaster Wilayah
                    </span>
                </div>
            </div>

            <div
                class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px]">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STATUS KETERISIAN
                        LIVE</span>
                    <div class="w-9 h-9 rounded-xl bg-[#FFDEA1]/40 flex items-center justify-center text-[#7A5900]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-baseline gap-1.5">
                    <span
                        class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                        {{ $statistik['terisi'] }} Terisi
                    </span>
                    <span class="text-xs font-semibold text-[#00341A]">({{ $statistik['persen_terisi'] }}% Full)</span>
                </div>

                <div class="w-full h-2 rounded-full bg-[#E5E2DB] overflow-hidden flex my-2">
                    <div class="h-full bg-[#FECE66]" style="width: {{ $statistik['persen_terisi'] }}%"></div>
                    <div class="h-full bg-[#B1F1C2]" style="width: {{ 100 - $statistik['persen_terisi'] }}%"></div>
                </div>

                <div class="flex items-center justify-between text-[10px] font-bold text-[#404941]">
                    <span>{{ $statistik['tersedia'] }} Tersedia</span>
                    <span>{{ $statistik['buzzer_aktif'] }} Panggilan Aktif</span>
                </div>
            </div>

            <div
                class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px]">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STATUS IOT HARDWARE</span>
                    <div class="w-9 h-9 rounded-xl bg-[#B1F1C2]/40 flex items-center justify-center text-[#00341A]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>
                </div>

                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    {{ $statistik['perangkat_terhubung'] }}/{{ $statistik['total_meja'] }} Terpasang
                </span>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#404941] font-bold text-[10px]">{{ $statistik['total_meja'] - $statistik['perangkat_terhubung'] }} Node Belum Terpasang</span>
                    @if ($statistik['buzzer_aktif'] > 0)
                        <span class="px-2 py-0.5 rounded-full bg-[#FFDAD6] text-[10px] font-bold text-[#93000A] animate-pulse">
                            {{ $statistik['buzzer_aktif'] }} Buzzer Aktif
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[10px] font-bold text-[#00341A]">
                            Tidak Ada Panggilan
                        </span>
                    @endif
                </div>
            </div>

            <div
                class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px] relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">DYNAMIC QR SECURITY</span>
                    <div class="w-9 h-9 rounded-xl bg-[#F0EEE7] flex items-center justify-center text-[#0B4D2B]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>

                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    {{ $statistik['qr_aktif'] }}/{{ $statistik['total_meja'] }} Token Aktif
                </span>
                <div class="flex items-center justify-between mt-2 text-[10px] font-bold">
                    <span class="text-[#404941]">Tersimpan di Database</span>
                    <span class="text-[#0B4D2B]">Anti-Order Palsu</span>
                </div>

                <div
                    class="absolute -right-8 -bottom-8 w-24 h-24 rounded-full bg-[#FFDEA1]/25 blur-xl pointer-events-none">
                </div>
            </div>

        </div>

        <div
            class="p-2.5 bg-[#F6F3EC] rounded-2xl shadow-xs border border-[#C0C9BF]/20 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center flex-wrap gap-2" id="meja-area-filters">
                <button type="button" data-area-filter="all"
                    class="meja-filter-btn px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                    Semua Area ({{ $daftarMeja->count() }})
                </button>
                @php
                    $areas = $areaOptions;
                @endphp
                @foreach ($areas as $idx => $areaName)
                    <button type="button" data-area-filter="{{ $areaName }}"
                        class="meja-filter-btn px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                        {{ $areaName }} ({{ $daftarMeja->where('area', $areaName)->count() }})
                    </button>
                @endforeach
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-72">
                    <input type="text" id="meja-search" placeholder="Cari saung, ID meja, IP node..."
                        class="w-full h-10 pl-10 pr-4 rounded-xl bg-white text-xs border border-gray-200 focus:outline-hidden focus:border-[#0B4D2B] focus:ring-1 focus:ring-[#0B4D2B] transition-all shadow-2xs">
                    <svg class="w-4 h-4 text-[#707971] absolute left-3.5 top-3" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
                    </svg>
                </div>

                <button type="button" @click="kategoriModalOpen = true"
                    class="h-10 px-3.5 rounded-xl bg-white border border-gray-200 hover:bg-[#F0EEE7] active:scale-95 text-[#1C1C18] font-semibold text-xs flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Kategori</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <div class="lg:col-span-8 flex flex-col gap-8">

                @foreach ($areas as $idx => $areaName)
                    <div data-area-section="{{ $areaName }}" class="flex flex-col gap-4">

                        <div class="flex items-center justify-between pb-1">
                            <div class="flex items-center gap-2.5">
                                <span class="w-3 h-3 rounded-full bg-[#0B4D2B]"></span>
                                <h2 class="font-bold text-lg sm:text-xl text-[#00341A]">
                                    {{ $areaName }}
                                </h2>
                                <span class="px-2 py-0.5 rounded-md bg-[#EBE8E1] text-[10px] font-bold text-[#707971]">
                                    {{ $daftarMeja->where('area', $areaName)->count() }} Saung
                                </span>
                            </div>
                            <span class="text-xs font-semibold text-[#00341A]">Area Penataan</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                            @foreach ($daftarMeja->where('area', $areaName) as $m)
                                @if (isset($m->adaPanggilan) && $m->adaPanggilan)
                                    <div @click="select('{{ $m->kode_meja }}')"
                                        data-search="{{ strtolower($m->kode_meja . ' ' . $m->area . ' ' . ($m->id_device ?? '')) }}"
                                        data-meja-card
                                        class="rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between min-h-[200px] relative overflow-hidden bg-white border-2 border-[#BA1A1A] shadow-lg shadow-red-500/10">
                                        <div
                                            class="absolute top-0 right-0 bg-[#BA1A1A] text-white text-[10px] font-bold px-3 py-0.5 rounded-bl-xl shadow-xs flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3C7.7 6.2 6 8.4 6 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <span>PANGGILAN AKTIF!</span>
                                        </div>
                                        <div>
                                            <div class="flex items-start justify-between gap-1 pt-1">
                                                <div>
                                                    <span class="text-[10px] font-bold text-[#BA1A1A]">{{ $m->kode_meja }}
                                                        • TERPILIH</span>
                                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">
                                                        Saung {{ $m->kode_meja }}</h3>
                                                </div>
                                            </div>
                                            <div
                                                class="mt-3 p-2 bg-[#FFDAD6]/40 rounded-xl text-xs flex flex-col gap-1 text-[#93000A]">
                                                <div class="flex justify-between">
                                                    <span class="font-medium">Sensor TTP223:</span>
                                                    <span class="font-bold text-[#BA1A1A]">Terpicu Buzzer ON</span>
                                                </div>
                                                @if ($m->billKode)
                                                    <div class="flex justify-between">
                                                        <span class="font-medium">Bill {{ $m->billKode }}:</span>
                                                        <span
                                                            class="font-bold text-[#00341A]">Rp{{ number_format($m->billTotal, 0, ',', '.') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#BA1A1A]">
                                            <span class="flex items-center gap-1 animate-pulse">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3C7.7 6.2 6 8.4 6 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                </svg> Suara Buzzer Menyala
                                            </span>
                                            <span>IP: {{ $m->id_device ?? '-' }}</span>
                                        </div>
                                    </div>
                                @elseif ($m->status_meja === 'terisi')
                                    <div @click="select('{{ $m->kode_meja }}')"
                                        data-search="{{ strtolower($m->kode_meja . ' ' . $m->area . ' ' . ($m->id_device ?? '')) }}"
                                        data-meja-card
                                        :class="selectedSaung === '{{ $m->kode_meja }}' ? 'ring-2 ring-[#0B4D2B] bg-white' :
                                            'bg-[#F6F3EC] hover:bg-[#efece3]'"
                                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[200px]">
                                        <div>
                                            <div class="flex items-start justify-between gap-1">
                                                <div>
                                                    <span
                                                        class="text-[10px] font-bold text-[#707971]">{{ $m->kode_meja }}</span>
                                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">
                                                        Saung {{ $m->kode_meja }}</h3>
                                                </div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[10px] font-bold text-[#261900] flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                                                    Terisi ({{ $m->itemCount }})
                                                </span>
                                            </div>
                                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex flex-col gap-1">
                                                <div class="flex justify-between">
                                                    <span class="text-[#707971]">Bill Aktif:</span>
                                                    <span
                                                        class="font-mono font-semibold text-[#1C1C18]">{{ $m->billKode }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-[#707971]">Total Pesanan:</span>
                                                    <span
                                                        class="font-bold text-[#00341A]">Rp{{ number_format($m->billTotal, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                                            <span class="flex items-center gap-1 text-[#0B4D2B]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="9" />
                                                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                                                </svg> LCD: "{{ $m->billKode ? 'Diproses' : 'Siap' }}"
                                            </span>
                                            <span>Kode {{ $panjangKode }} Digit</span>
                                        </div>
                                    </div>
                                @else
                                    <div @click="select('{{ $m->kode_meja }}')"
                                        data-search="{{ strtolower($m->kode_meja . ' ' . $m->area . ' ' . ($m->id_device ?? '')) }}"
                                        data-meja-card
                                        :class="selectedSaung === '{{ $m->kode_meja }}' ? 'ring-2 ring-[#0B4D2B] bg-white' :
                                            'bg-[#F6F3EC] hover:bg-[#efece3]'"
                                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[200px]">
                                        <div>
                                            <div class="flex items-start justify-between gap-1">
                                                <div>
                                                    <span
                                                        class="text-[10px] font-bold text-[#707971]">{{ $m->kode_meja }}</span>
                                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">
                                                        Saung {{ $m->kode_meja }}</h3>
                                                </div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/50 text-[10px] font-semibold text-[#00341A] flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                                                    Tersedia
                                                </span>
                                            </div>
                                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex flex-col gap-1">
                                                <span class="text-[#707971] italic">Meja Bersih & Siap Tamu</span>
                                                <div class="flex justify-between text-[#707971]">
                                                    <span>Kapasitas:</span>
                                                    <span class="font-semibold text-[#1C1C18]">{{ $m->kapasitas }}
                                                        Orang</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                                            <span class="flex items-center gap-1 text-[#0B4D2B]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg> Node Ready
                                            </span>
                                            <span>Kode {{ $panjangKode }} Digit</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        </div>
                    </div>

                @endforeach

            </div>

            <div class="lg:col-span-4 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col gap-6 sticky top-24"
                id="tambah-meja">

                <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                    <div class="flex flex-col">
                        <span
                            class="px-2.5 py-0.5 rounded-full bg-[#E5E2DB] text-[10px] font-bold text-[#00341A] w-fit font-mono">
                            <span x-text="selectedSaung || '-'">{{ $mejaPertama ?? '-' }}</span> <span
                                x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].area : ''"></span>
                        </span>
                        <h2 class="font-display font-bold text-2xl text-[#00341A] mt-1">
                            <span x-text="selectedSaung ? 'Saung ' + selectedSaung : 'Pilih Saung'">Saung Detail</span>
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="editModalOpen = true"
                            class="w-9 h-9 rounded-xl bg-[#F0EEE7] hover:bg-[#e4e1d7] active:scale-95 flex items-center justify-center text-[#404941] transition-all cursor-pointer"
                            title="Edit Saung">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>

                        <button type="button" :disabled="!selectedSaung"
                            data-confirm-title="Hapus saung ini?"
                            data-confirm-message="Data saung, sesi aktif, dan QR akan dihapus permanen. Saung dengan riwayat pesanan tidak dapat dihapus."
                            data-confirm-action="{{ route('admin.master-meja.hapus-meja') }}"
                            data-confirm-field="kode_meja"
                            :data-confirm-value="selectedSaung"
                            data-confirm-label="Hapus Saung"
                            @click="askDeleteFromEl($el)"
                            class="w-9 h-9 rounded-xl bg-[#FFDAD6] hover:bg-[#ffc9c3] disabled:opacity-40 disabled:cursor-not-allowed active:scale-95 flex items-center justify-center text-[#BA1A1A] transition-all cursor-pointer"
                            title="Hapus Saung">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-cloak x-show="detailMeja[selectedSaung] && detailMeja[selectedSaung].panggilan"
                    class="p-4 rounded-2xl bg-[#FFDAD6] border border-red-300 flex flex-col gap-3 shadow-xs">
                    <div class="flex items-start gap-2.5 text-[#BA1A1A]">
                        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3C7.7 6.2 6 8.4 6 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <div class="flex flex-col">
                            <span class="font-bold text-xs leading-tight">PANGGILAN PELAYAN AKTIF!</span>
                            <span class="text-[11px] text-[#93000A] mt-0.5"
                                x-text="'Dipanggil ' + (detailMeja[selectedSaung] && detailMeja[selectedSaung].waktuPanggil ? detailMeja[selectedSaung].waktuPanggil : '-')"></span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-1">
                        <form method="POST" action="{{ route('admin.master-meja.selesaikan-panggilan') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="kode_meja" :value="selectedSaung">
                            <button type="submit"
                                class="w-full py-2.5 rounded-xl bg-[#BA1A1A] hover:bg-[#93000a] text-white font-bold text-xs transition-all active:scale-95 cursor-pointer shadow-xs">
                                Selesaikan Panggilan
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.master-meja.panggil-pelayan') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="kode_meja" :value="selectedSaung">
                            <button type="submit"
                                class="w-full py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#93000A] font-bold text-xs transition-all active:scale-95 cursor-pointer shadow-xs">
                                Panggil Ulang
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-[#F6F3EC] border border-[#C0C9BF]/30 flex flex-col gap-3">
                    <div class="flex items-center justify-between pb-1 border-b border-[#C0C9BF]/20">
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STATUS PERANGKAT IOT</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#00341A]">
                            <span class="w-1.5 h-1.5 rounded-full"
                                :class="detailMeja[selectedSaung] && detailMeja[selectedSaung].ip && detailMeja[selectedSaung].ip !== '-' ? 'bg-[#0B4D2B]' : 'bg-gray-400'"></span>
                            <span x-text="detailMeja[selectedSaung] && detailMeja[selectedSaung].ip && detailMeja[selectedSaung].ip !== '-' ? 'Node Terpasang' : 'Node Belum Terpasang'"></span>
                        </span>
                    </div>

                    <div class="flex flex-col gap-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-200/50">
                            <span class="text-[#404941]">ID Device ESP32:</span>
                            <span class="font-mono font-semibold text-[#1C1C18]"
                                x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].ip : '-'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-200/50">
                            <span class="text-[#404941]">Area:</span>
                            <span class="font-semibold text-[#1C1C18]"
                                x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].area : '-'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-200/50">
                            <span class="text-[#404941]">Kapasitas:</span>
                            <span class="font-semibold text-[#1C1C18]"
                                x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].kapasitas + ' Orang' : '-'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-200/50 items-center">
                            <span class="text-[#404941]">Status Meja:</span>
                            <span class="px-2 py-0.5 rounded-md bg-[#0B4D2B] text-[#EFC05A] font-mono text-[10px] font-bold capitalize"
                                x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].status : '-'"></span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-[#404941]">Panggilan Pelayan:</span>
                            <span class="font-mono font-bold"
                                :class="detailMeja[selectedSaung] && detailMeja[selectedSaung].panggilan ? 'text-[#BA1A1A]' : 'text-[#00341A]'"
                                x-text="detailMeja[selectedSaung] && detailMeja[selectedSaung].panggilan ? 'AKTIF' : 'Tidak Ada'"></span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.master-meja.panggil-pelayan') }}" class="pt-1">
                        @csrf
                        <input type="hidden" name="kode_meja" :value="selectedSaung">
                        <button type="submit"
                            class="w-full py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] transition-all cursor-pointer">
                            Panggil Pelayan ke Meja Ini
                        </button>
                    </form>
                </div>

                <div class="p-4 rounded-2xl bg-[#F6F3EC] border border-[#C0C9BF]/30 flex flex-col gap-3">
                    <div class="flex items-center justify-between pb-1 border-b border-[#C0C9BF]/20">
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">KODE AKSES &amp; QR MEJA</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[10px] font-bold text-[#00341A]"
                            x-text="detailMeja[selectedSaung] && detailMeja[selectedSaung].token ? 'Kode Aktif' : 'Kode Kosong'">
                            Token Aktif
                        </span>
                    </div>

                    <div class="flex flex-col gap-3 p-3 bg-white rounded-xl">
                        <div
                            class="w-36 h-36 mx-auto rounded-xl bg-[#FCF9F2] p-2 flex items-center justify-center flex-shrink-0 border border-gray-200 shadow-inner">
                            <canvas id="qr-canvas" class="w-full h-full rounded-lg"></canvas>
                        </div>

                        <div class="flex flex-col text-xs overflow-hidden text-center">
                            <span class="text-[10px] font-bold text-[#707971] uppercase">URL MEJA:</span>
                            <a :href="detailMeja[selectedSaung] && detailMeja[selectedSaung].url ? detailMeja[selectedSaung].url : window.location.origin + '/meja/' + selectedSaung" target="_blank"
                                class="font-mono text-[11px] font-bold text-[#0B4D2B] hover:underline break-all mt-0.5">
                                <span x-text="detailMeja[selectedSaung] && detailMeja[selectedSaung].url ? detailMeja[selectedSaung].url : window.location.origin + '/meja/' + selectedSaung"></span>
                            </a>
                            <span class="text-[10px] text-[#707971] mt-2">Kode Masuk ({{ $panjangKode }} Digit):</span>
                            <span class="font-mono text-2xl font-bold text-[#00341A] tracking-[0.3em] mt-0.5"
                                x-text="detailMeja[selectedSaung] && detailMeja[selectedSaung].token ? detailMeja[selectedSaung].token : '-'"></span>
                            <span class="text-[10px] text-[#2E6A45] font-semibold mt-1 flex items-center justify-center gap-1">
                                Anti Fake Order Verified
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="downloadQr()"
                            class="flex-1 py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] flex items-center justify-center gap-1 transition-all cursor-pointer">
                            Unduh QR
                        </button>
                        <form method="POST" action="{{ route('admin.master-meja.regenerasi-token') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="kode_meja" :value="selectedSaung">
                            <button type="submit"
                                class="w-full py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] flex items-center justify-center gap-1 transition-all cursor-pointer">
                                Regenerasi Token
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-[#F6F3EC] border border-[#C0C9BF]/30 flex flex-col gap-3">
                    <div class="flex items-center justify-between pb-1 border-b border-[#C0C9BF]/20">
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PESANAN TAMU SAAT
                            INI</span>
                        <span class="font-mono font-bold text-xs text-[#1C1C18]"
                            x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].billKode : ''"></span>
                    </div>

                    <div class="flex flex-col gap-2 text-xs">
                        <template x-for="(item, idx) in (detailMeja[selectedSaung] ? detailMeja[selectedSaung].items : [])"
                            :key="idx">
                            <div class="flex items-center justify-between py-1 border-b border-gray-200/40">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-5 h-5 rounded-md bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center font-bold text-[10px]"
                                        x-text="item.jumlah"></span>
                                    <span class="text-[#1C1C18]" x-text="item.nama"></span>
                                </div>
                                <span class="font-mono text-[#1C1C18]"
                                    x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                            </div>
                        </template>
                        <template x-if="!detailMeja[selectedSaung] || detailMeja[selectedSaung].items.length === 0">
                            <div class="py-1 text-[#707971] italic">Belum ada pesanan aktif di meja ini.</div>
                        </template>
                    </div>

                    <div class="flex items-baseline justify-between pt-1">
                        <span class="text-xs font-semibold text-[#707971]">Total Bill Kasir:</span>
                        <span class="font-bold text-base text-[#00341A]"
                            x-text="detailMeja[selectedSaung] ? 'Rp ' + Number(detailMeja[selectedSaung].billTotal).toLocaleString('id-ID') : 'Rp 0'"></span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" @click="billModalOpen = true"
                            :disabled="!detailMeja[selectedSaung] || !detailMeja[selectedSaung].billKode"
                            class="flex-1 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs text-center transition-all shadow-xs cursor-pointer">
                            Lihat Bill POS Kasir
                        </button>
                        <button type="button" @click="editModalOpen = true"
                            class="px-4 py-2.5 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-xs transition-all cursor-pointer">
                            Edit Meja
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <div x-cloak x-show="addModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-lg bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-6"
                @click.outside="addModalOpen = false">

                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex flex-col">
                        <h3 class="font-display font-bold text-xl text-[#00341A]">Tambah Meja / Saung Baru</h3>
                        <p class="text-xs text-[#707971] mt-0.5">Konfigurasi node fisik ESP32 & kode meja POS</p>
                    </div>
                    <button type="button" @click="addModalOpen = false"
                        class="text-gray-400 hover:text-black p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.meja.simpan') }}" class="flex flex-col gap-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">Kode Saung/Meja *</label>
                            <input type="text" name="kode_meja" placeholder="Contoh: LB-06" maxlength="10"
                                class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]"
                                required>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">Area *</label>
                            <select name="area"
                                class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]"
                                required>
                                @foreach ($areaOptions as $a)
                                    <option value="{{ $a }}">{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">Kapasitas Tamu</label>
                            <input type="number" name="kapasitas" value="6" min="1" max="20"
                                class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">ID Device ESP32</label>
                            <input type="text" name="id_device" placeholder="Contoh: ESP32-12" maxlength="20"
                                class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="addModalOpen = false"
                            class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] active:scale-95 transition-all shadow-xs cursor-pointer">
                            Simpan Saung
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="editModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="w-full max-w-lg bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-6"
                @click.outside="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex flex-col">
                        <h3 class="font-display font-bold text-xl text-[#00341A]">Edit Meja / Saung</h3>
                        <p class="text-xs text-[#707971] mt-0.5">Perbarui data saung <span class="font-mono font-bold" x-text="selectedSaung"></span></p>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.meja.simpan') }}" class="flex flex-col gap-4 text-xs">
                    @csrf
                    <input type="hidden" name="kode_meja_lama" :value="selectedSaung">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">Kode Saung/Meja *</label>
                            <input type="text" name="kode_meja" :value="selectedSaung" maxlength="10"
                                class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">Area *</label>
                            <select name="area" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                                @foreach ($areaOptions as $a)
                                    <option value="{{ $a }}" :selected="detailMeja[selectedSaung] && detailMeja[selectedSaung].area === @js($a)">{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">Kapasitas Tamu</label>
                            <input type="number" name="kapasitas" :value="detailMeja[selectedSaung] ? detailMeja[selectedSaung].kapasitas : 6"
                                min="1" max="20" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="font-bold text-[#1C1C18]">ID Device ESP32</label>
                            <input type="text" name="id_device" :value="detailMeja[selectedSaung] && detailMeja[selectedSaung].ip !== '-' ? detailMeja[selectedSaung].ip : ''"
                                maxlength="20" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false"
                            class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] active:scale-95 transition-all shadow-xs cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-cloak x-show="kategoriModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="w-full max-w-md bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5"
                @click.outside="kategoriModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-[#B1F1C2]/60 text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7 7h.01M7 3h5a2 2 0 011.6.8l7 9.33a2 2 0 010 2.4l-4.8 6.4a2 2 0 01-3.2 0L3.8 12.2a2 2 0 01-.8-1.6V5a2 2 0 012-2z" />
                            </svg>
                        </span>
                        <div class="flex flex-col">
                            <h3 class="font-display font-bold text-xl text-[#00341A]">Kelola Kategori Area</h3>
                            <p class="text-xs text-[#707971] mt-0.5">Tambah atau hapus kategori area saung</p>
                        </div>
                    </div>
                    <button type="button" @click="kategoriModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.master-meja.tambah-area') }}" class="flex flex-col gap-2 text-xs">
                    @csrf
                    <label class="font-bold text-[#1C1C18]">Nama Area/Cluster Baru *</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="nama_area" placeholder="Contoh: Gazebo Tepi Kolam" maxlength="100"
                            class="flex-1 h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                        <button type="submit"
                            class="h-10 px-4 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] active:scale-95 transition-all shadow-xs cursor-pointer whitespace-nowrap">
                            Tambah
                        </button>
                    </div>
                </form>

                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-[#707971]">
                        <span>Kategori Tersimpan ({{ count($kategoriList) }})</span>
                        <span>Dipakai</span>
                    </div>

                    <div class="flex flex-col gap-1.5 max-h-60 overflow-y-auto pr-1">
                        @forelse ($kategoriList as $k)
                            <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl bg-[#F6F3EC] border border-[#C0C9BF]/30">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-bold text-[#1C1C18] truncate">{{ $k['nama'] }}</span>
                                    <span class="text-[10px] text-[#707971]">{{ $k['jumlah_meja'] }} meja</span>
                                </div>

                                @if ($k['jumlah_meja'] > 0)
                                    <span class="w-8 h-8 rounded-lg bg-[#E5E2DB] text-[#707971] flex items-center justify-center flex-shrink-0"
                                        title="Tidak bisa dihapus: masih dipakai {{ $k['jumlah_meja'] }} meja">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </span>
                                @else
                                    <button type="button"
                                        data-confirm-title="Hapus kategori {{ $k['nama'] }}?"
                                        data-confirm-message="Kategori ini akan dihapus permanen dari daftar area."
                                        data-confirm-action="{{ route('admin.master-meja.hapus-area') }}"
                                        data-confirm-field="nama_area"
                                        data-confirm-value="{{ $k['nama'] }}"
                                        data-confirm-label="Hapus Kategori"
                                        @click="askDeleteFromEl($el)"
                                        class="w-8 h-8 rounded-lg bg-[#FFDAD6] hover:bg-[#ffc9c3] active:scale-95 text-[#BA1A1A] flex items-center justify-center flex-shrink-0 transition-all cursor-pointer"
                                        title="Hapus kategori">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="py-3 text-xs text-[#707971] italic">Belum ada kategori tersimpan.</div>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-gray-100">
                    <button type="button" @click="kategoriModalOpen = false"
                        class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <div x-cloak x-show="billModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="w-full max-w-md bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5"
                @click.outside="billModalOpen = false">
                <div class="flex items-start justify-between pb-3 border-b border-gray-100">
                    <div class="flex flex-col">
                        <h3 class="font-display font-bold text-xl text-[#00341A]">Bill POS Kasir</h3>
                        <p class="text-xs text-[#707971] mt-0.5">
                            <span x-text="selectedSaung ? 'Saung ' + selectedSaung : '-'"></span>
                            <span class="mx-1">•</span>
                            <span class="font-mono" x-text="detailMeja[selectedSaung] ? detailMeja[selectedSaung].billKode : '-'"></span>
                        </p>
                    </div>
                    <button type="button" @click="billModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-col gap-2 text-xs max-h-72 overflow-y-auto">
                    <template x-for="(item, idx) in (detailMeja[selectedSaung] ? detailMeja[selectedSaung].items : [])" :key="idx">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center font-bold text-[10px]" x-text="item.jumlah"></span>
                                <span class="text-[#1C1C18]" x-text="item.nama"></span>
                            </div>
                            <span class="font-mono text-[#1C1C18]" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></span>
                        </div>
                    </template>
                    <template x-if="!detailMeja[selectedSaung] || detailMeja[selectedSaung].items.length === 0">
                        <div class="py-2 text-[#707971] italic">Belum ada pesanan aktif di meja ini.</div>
                    </template>
                </div>

                <div class="flex items-baseline justify-between pt-2 border-t border-gray-200">
                    <span class="text-xs font-semibold text-[#707971]">Total Bill:</span>
                    <span class="font-bold text-lg text-[#00341A]"
                        x-text="detailMeja[selectedSaung] ? 'Rp ' + Number(detailMeja[selectedSaung].billTotal).toLocaleString('id-ID') : 'Rp 0'"></span>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="billModalOpen = false"
                        class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 active:scale-95 transition-all cursor-pointer text-xs">
                        Tutup
                    </button>
                    <a x-show="detailMeja[selectedSaung] && detailMeja[selectedSaung].billKode"
                        :href="detailMeja[selectedSaung] ? '/pesanan/' + detailMeja[selectedSaung].billKode : '#'"
                        class="flex-1 py-3 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] active:scale-95 transition-all shadow-xs text-center text-xs">
                        Buka Halaman Pesanan
                    </a>
                </div>
            </div>
        </div>

        <div x-cloak x-show="confirmDialog.open"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/45 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="w-full max-w-sm bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5 text-center"
                @click.outside="confirmDialog.open = false">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#FFDAD6] text-[#BA1A1A] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <div class="flex flex-col gap-2">
                    <h3 class="font-display font-bold text-xl text-[#00341A]" x-text="confirmDialog.title"></h3>
                    <p class="text-xs text-[#707971] leading-relaxed" x-text="confirmDialog.message"></p>
                </div>

                <form method="POST" :action="confirmDialog.action" class="flex items-center gap-3 pt-1">
                    @csrf
                    <input type="hidden" :name="confirmDialog.field" :value="confirmDialog.value">
                    <button type="button" @click="confirmDialog.open = false"
                        class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-[#BA1A1A] text-white font-bold text-xs hover:bg-[#93000A] active:scale-95 transition-all shadow-xs cursor-pointer">
                        <span x-text="confirmDialog.label"></span>
                    </button>
                </form>
            </div>
        </div>

        <div x-cloak x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-8 right-8 z-50 bg-[#00341A] text-white px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 border border-[#FECE66]/30 text-sm font-semibold">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3C7.7 6.2 6 8.4 6 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span x-text="toast.message"></span>
        </div>

    </div>

    <script>
        function masterMejaApp() {
            return {
                selectedSaung: sessionStorage.getItem('masterMejaSelected') || @json($mejaPertama),
                addModalOpen: false,
                editModalOpen: false,
                kategoriModalOpen: false,
                billModalOpen: false,
                detailMeja: @json($detailJson),
                toast: {
                    show: false,
                    message: ''
                },
                confirmDialog: {
                    open: false,
                    title: '',
                    message: '',
                    action: '',
                    field: 'kode_meja',
                    value: '',
                    label: 'Hapus'
                },
                init() {
                    if (!this.detailMeja[this.selectedSaung]) {
                        this.selectedSaung = @json($mejaPertama);
                    }

                    this.$nextTick(() => this.renderQr());
                    this.$watch('selectedSaung', () => this.$nextTick(() => this.renderQr()));

                    window.addEventListener('realtime', () => window.realtimeReload(800));
                    window.addEventListener('realtime:panggilan.pelayan', () => window.realtimeReload(400));
                    window.addEventListener('realtime:status.berubah', () => window.realtimeReload(400));
                    window.addEventListener('realtime:sesi.berubah', () => window.realtimeReload(400));
                },
                qrUrl() {
                    const detail = this.detailMeja[this.selectedSaung];

                    return detail && detail.url ? detail.url : window.location.origin + '/meja/' + this.selectedSaung;
                },
                renderQr() {
                    const canvas = document.getElementById('qr-canvas');

                    if (!canvas || !window.QRCode) return;

                    window.QRCode.toCanvas(canvas, this.qrUrl(), {
                        width: 256,
                        margin: 1,
                        color: {
                            dark: '#00341A',
                            light: '#FFFFFF'
                        }
                    }).then(() => {
                        canvas.style.width = '100%';
                        canvas.style.height = '100%';
                    }).catch(() => {});
                },
                downloadQr() {
                    const canvas = document.getElementById('qr-canvas');

                    if (!canvas) return;

                    const a = document.createElement('a');
                    a.href = canvas.toDataURL('image/png');
                    a.download = 'qr-' + this.selectedSaung + '.png';
                    a.click();
                    this.showToast('QR saung ' + this.selectedSaung + ' berhasil diunduh.');
                },
                askDeleteFromEl(el) {
                    const data = el.dataset;

                    this.confirmDialog.title = data.confirmTitle || 'Konfirmasi Hapus';
                    this.confirmDialog.message = data.confirmMessage || '';
                    this.confirmDialog.action = data.confirmAction || '';
                    this.confirmDialog.field = data.confirmField || 'kode_meja';
                    this.confirmDialog.value = data.confirmValue || '';
                    this.confirmDialog.label = data.confirmLabel || 'Hapus';
                    this.confirmDialog.open = true;
                },
                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => this.toast.show = false, 3000);
                },
                select(id) {
                    this.selectedSaung = id;
                    sessionStorage.setItem('masterMejaSelected', id);
                    this.$nextTick(() => this.renderQr());
                }
            };
        }
    </script>

    <script>
        (function () {
            const buttons = document.querySelectorAll('.meja-filter-btn');
            const sections = document.querySelectorAll('[data-area-section]');
            const search = document.getElementById('meja-search');
            let activeArea = 'all';

            const setButtonState = (btn, active) => {
                btn.classList.toggle('bg-[#0B4D2B]', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('shadow-xs', active);
                btn.classList.toggle('font-bold', active);
                btn.classList.toggle('bg-[#F0EEE7]', !active);
                btn.classList.toggle('text-[#404941]', !active);
                btn.classList.toggle('font-semibold', !active);
                btn.classList.toggle('hover:text-black', !active);
            };

            const applyFilter = () => {
                const query = (search ? search.value : '').trim().toLowerCase();

                sections.forEach((section) => {
                    const areaName = section.dataset.areaSection || '';
                    const areaMatch = activeArea === 'all' || activeArea === areaName;
                    let visible = 0;

                    section.querySelectorAll('[data-meja-card]').forEach((card) => {
                        const match = areaMatch && (query === '' || (card.dataset.search || '').includes(query));
                        card.style.display = match ? '' : 'none';
                        if (match) visible += 1;
                    });

                    section.style.display = (!areaMatch || visible === 0) ? 'none' : '';
                });
            };

            buttons.forEach((btn) => {
                setButtonState(btn, btn.dataset.areaFilter === 'all');
                btn.addEventListener('click', () => {
                    activeArea = btn.dataset.areaFilter;
                    buttons.forEach((b) => setButtonState(b, b === btn));
                    applyFilter();
                });
            });

            if (search) {
                search.addEventListener('input', applyFilter);
            }

            applyFilter();
        })();
    </script>
@endsection
