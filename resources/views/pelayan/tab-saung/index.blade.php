@extends('layouts.pelayan')

@section('title', '07C - Mobile POS Pelayan (Tab Saung) • Situ Awi')

@section('content')
<div x-data="pelayanTabSaung()" class="relative flex flex-col min-h-screen bg-[#FCF9F2]" x-init="init()">

    <!-- Header (07C - Mobile POS Header) -->
    <header class="sticky top-0 z-40 bg-[rgba(252,249,242,0.85)] backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-4 py-2.5 h-16 flex items-center justify-between border-b border-[#E5E2DB]/50">
        <div class="flex items-center gap-2">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 max-w-[45.25px] w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-[1px] uppercase leading-3">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-[-0.4px] leading-5">Saung</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- LIVE Pill -->
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F0EEE7] shadow-[0_1px_4px_rgba(43,42,38,0.04)]">
                <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[-0.25px]">LIVE</span>
            </div>

            <!-- Avatar -->
            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-[10px] flex items-center justify-center shadow-[0_1px_4px_rgba(0,0,0,0.08)]">
                {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="px-4 py-3 space-y-4 flex-1">

        <!-- Runner Context & IoT Mesh Status Header Card -->
        <section class="bg-[#F6F3EC] rounded-2xl p-4 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="relative flex-shrink-0">
                        <div class="w-11 h-11 rounded-full bg-[#0B4D2B] text-white font-bold text-base flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                            {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-[#B1F1C2] shadow-[0_0_0_2px_#F6F3EC]"></span>
                    </div>

                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5 leading-6">
                            <span class="font-bold text-base text-[#1C1C18]" x-text="runnerName">{{ auth()->user()->nama ?? auth()->user()->name ?? 'Kang Asep' }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold tracking-[0.4px]">
                                STF-{{ str_pad(auth()->user()->id ?? 1, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-[#404941] leading-4">
                            <svg class="w-3 h-3 text-[#7A5900] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span x-text="shiftInfo">Shift Siang (12:00 – 18:00 WIB)</span>
                        </div>
                    </div>
                </div>

                <!-- Quick QR Scanner Action for Table Check-in -->
                <button @click="showQrModal = true" type="button" 
                        class="w-10 h-10 rounded-xl bg-white shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center transition-all active:scale-95 cursor-pointer">
                    <svg class="w-[18.33px] h-[18.33px] text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </button>
            </div>

            <!-- Active Area & IoT Mesh Status Strip -->
            <div class="bg-[#F0EEE7] rounded-xl px-3 py-2 flex items-center justify-between text-xs h-8">
                <div class="flex items-center gap-1.5 text-[#1C1C18] font-semibold tracking-[0.24px] truncate">
                    <svg class="w-[13.88px] h-[15.36px] text-[#00341A] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    <span class="truncate" x-text="currentArea">Tepi Danau (LB-01 – LB-05)</span>
                </div>

                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-[#404941] text-[10px] font-bold tracking-[0.4px] flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                    <span x-text="meshStatus">IoT Mesh 11/11 (-52dBm)</span>
                </div>
            </div>
        </section>

        <!-- Occupancy Overview Metrics (4 Cards) -->
        <section class="grid grid-cols-4 gap-2">
            <!-- Total -->
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px]">
                <span class="font-bold text-[20px] text-[#1C1C18] leading-7" x-text="totalMeja">{{ $totalMeja ?? 11 }}</span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5" x-text="'Total (' + totalPercentage + '%)'">Total ({{ ($totalMeja ?? 0) > 0 ? round((($terisiCount ?? 0) / $totalMeja) * 100) : 0 }}%)</span>
            </div>

            <!-- Terisi -->
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px]">
                <span class="font-bold text-[20px] text-[#0B4D2B] leading-7" x-text="terisiCount">{{ $terisiCount ?? 0 }}</span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5">Terisi</span>
            </div>

            <!-- Tersedia -->
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px]">
                <span class="font-bold text-[20px] text-[#7A5900] leading-7" x-text="tersediaCount">{{ $tersediaCount ?? 0 }}</span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5">Tersedia</span>
            </div>

            <!-- Panggilan -->
            <div class="bg-[#FFDAD6] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px] relative">
                <span class="font-bold text-[20px] text-[#93000A] leading-7" x-text="panggilanCount">{{ $panggilanCount ?? 0 }}</span>
                <span class="text-[10px] font-semibold text-[#93000A] tracking-[0.4px] mt-0.5">Panggilan</span>
                <template x-if="panggilanCount > 0">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#BA1A1A] animate-ping absolute -top-1 -right-1"></span>
                </template>
            </div>
        </section>

        <!-- Cluster Filter Chips (Horizontal Scroll) -->
        <section class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none">
            <!-- Semua Saung -->
            <button @click="selectedCluster = 'all'" type="button" 
                    :class="selectedCluster === 'all' ? 'bg-[#0B4D2B] text-white' : 'bg-[#EBE8E1] text-[#1C1C18]'"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span x-text="'Semua Saung (' + totalMeja + ')'">Semua Saung ({{ $totalMeja ?? 11 }})</span>
            </button>

            <!-- Cluster Danau -->
            <button @click="selectedCluster = 'danau'" type="button" 
                    :class="selectedCluster === 'danau' ? 'bg-[#0B4D2B] text-white' : 'bg-[#EBE8E1] text-[#1C1C18]'"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <svg class="w-3.5 h-3 text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                </svg>
                <span>Cluster Danau ({{ $clusters->firstWhere('key', 'lb')['count'] ?? 5 }})</span>
            </button>

            <!-- Cluster Balong -->
            <button @click="selectedCluster = 'balong'" type="button" 
                    :class="selectedCluster === 'balong' ? 'bg-[#0B4D2B] text-white' : 'bg-[#EBE8E1] text-[#1C1C18]'"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span>Cluster Balong ({{ $clusters->firstWhere('key', 'la')['count'] ?? 2 }})</span>
            </button>

            <!-- Meja Kayu -->
            <button @click="selectedCluster = 'kayu'" type="button" 
                    :class="selectedCluster === 'kayu' ? 'bg-[#0B4D2B] text-white' : 'bg-[#EBE8E1] text-[#1C1C18]'"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <svg class="w-3.5 h-3 text-[#707971]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
                </svg>
                <span>Meja Kayu ({{ $clusters->firstWhere('key', 'ka')['count'] ?? 4 }})</span>
            </button>
        </section>

        <!-- CLUSTER A: Lesehan Tepi Danau (Runner's Assigned Sector) -->
        <section x-show="selectedCluster === 'all' || selectedCluster === 'danau'" class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h2 class="font-bold text-base text-[#1C1C18] leading-6">Cluster A • Tepi Danau</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                        Area Kamu
                    </span>
                </div>
                <span class="text-xs font-medium text-[#404941]">5 Saung</span>
            </div>

            <!-- CARD LB-02: ACTIVE CRITICAL CALL / NORMAL STATE -->
            <div class="bg-white rounded-2xl shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-2px_rgba(0,0,0,0.1)] relative overflow-hidden flex flex-col gap-2 transition-all"
                 :class="lb02Calling ? 'border border-[#BA1A1A]/30 ring-1 ring-[#BA1A1A]' : 'border border-[#F0EEE7]'">
                
                <!-- Glow & Warning Accent Bar (Shown only when calling) -->
                <div x-show="lb02Calling" class="h-1.5 bg-[#BA1A1A] w-full"></div>

                <div class="p-3.5 space-y-2.5">
                    <!-- Top Info Row -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <!-- Square Badge -->
                            <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex-shrink-0"
                                 :class="lb02Calling ? 'bg-[#FFDAD6] text-[#93000A]' : 'bg-[#F0EEE7] text-[#1C1C18]'">
                                <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                                <span class="text-[20px] font-bold leading-5">{{ $panggilanAktif->first() ? filter_var($panggilanAktif->first()->meja->kode_meja, FILTER_SANITIZE_NUMBER_INT) : '02' }}</span>
                            </div>

                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-6">{{ $panggilanAktif->first() ? $panggilanAktif->first()->meja->kode_meja : 'LB-02' }} Teratai 2</h3>
                                    <!-- Bell on badge: ONLY when calling -->
                                    <template x-if="lb02Calling">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#BA1A1A] text-white text-[10px] font-bold tracking-[0.4px] animate-pulse">
                                            BELL ON
                                        </span>
                                    </template>
                                    <template x-if="!lb02Calling">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-semibold">
                                            Santap Makan
                                        </span>
                                    </template>
                                </div>
                                <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Bill #SW-8819 • 4 Tamu • Rp 144.100</span>
                            </div>
                        </div>

                        <!-- Time Ago -->
                        <div class="flex items-center gap-1 text-[10px] font-bold"
                             :class="lb02Calling ? 'text-[#BA1A1A]' : 'text-[#404941]'">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>{{ $panggilanAktif->first() && $panggilanAktif->first()->waktu_panggil ? $panggilanAktif->first()->waktu_panggil->diffForHumans() : '3m lalu' }}</span>
                        </div>
                    </div>

                    <!-- Request Note Banner (Shown ONLY when calling) -->
                    <div x-show="lb02Calling" 
                         x-transition:leave="transition ease-in duration-200 transform opacity-0 -translate-y-2"
                         class="bg-[rgba(255,218,214,0.6)] rounded-xl p-2.5 flex items-start gap-2 border border-[#BA1A1A]/20">
                        <svg class="w-4 h-4 text-[#BA1A1A] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-[#93000A] uppercase tracking-[0.5px]">
                                PERMINTAAN TAMU (SENSOR TTP223)
                            </span>
                            <p class="text-sm font-semibold text-[#93000A] leading-snug mt-0.5">
                                {{ $panggilanAktif->first() && !empty($panggilanAktif->first()->catatan) ? '"' . $panggilanAktif->first()->catatan . '"' : '"Tamu butuh bantuan / piring ekstra & sendok"' }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Action Buttons for Call (Shown ONLY when calling) -->
                    <div x-show="lb02Calling" class="flex items-center gap-2 pt-1">
                        <!-- Button 1: Respon & Temui (Clicking clears call) -->
                        <button @click="respondCall()" type="button" 
                                class="flex-1 h-12 rounded-xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm tracking-[0.14px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <svg class="w-3.5 h-4.5 text-[#765600]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Respon & Temui</span>
                        </button>

                        <!-- Button 2: Matikan Buzzer -->
                        <button @click="muteBuzzer()" type="button" :disabled="!buzzerActive"
                                class="flex-1 h-12 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] font-semibold text-sm tracking-[0.14px] flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer disabled:opacity-50">
                            <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                            </svg>
                            <span x-text="buzzerActive ? 'Matikan Buzzer' : 'Buzzer Mati'">Matikan Buzzer</span>
                        </button>
                    </div>

                    <!-- Normal State Bottom Footer (When NOT calling) -->
                    <div x-show="!lb02Calling" class="flex items-center justify-between text-xs pt-2 border-t border-[#F0EEE7]">
                        <span class="text-[#404941] flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                            </svg>
                            <span>Puck LCD: "Selamat Menikmati"</span>
                        </span>
                        <button @click="showOrderModal('LB-02 Teratai 2', 'SW-8819', 'Rp 144.100')" class="font-semibold text-xs text-[#00341A] hover:underline cursor-pointer">
                            Lihat Rincian Pesanan
                        </button>
                    </div>
                </div>
            </div>

            <!-- CARD LB-05: Siap Antar Bar Pass -->
            <div class="bg-white rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] p-4 flex flex-col gap-2">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-12 h-12 rounded-xl bg-[#FFDEA1] text-[#261900] flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                            <span class="text-[20px] font-bold leading-5">05</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18] leading-6">LB-05 Dermaga Ujung</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[#5C4300] text-[10px] font-bold tracking-[0.4px]">
                                    Siap Antar
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Bar Pass • Siap 1 mnt lalu</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-[#F0EEE7] flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                        </svg>
                    </div>
                </div>

                <!-- Drink Order Summary Strip -->
                <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 font-semibold text-sm text-[#1C1C18] leading-5">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z" />
                        </svg>
                        <span>4x Es Teh Jumbo, 1x Kelapa Muda</span>
                    </div>
                    <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px]">5 Item</span>
                </div>

                <!-- Action Button -->
                <button @click="triggerToast('Pesanan minuman Saung 05 diambil dari Bar Counter.')" type="button" 
                        class="w-full h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm tracking-[0.14px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                    <svg class="w-4 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>Ambil di Bar & Antar ke Saung</span>
                </button>
            </div>

            <!-- CARD LB-01: Dining in Progress -->
            <div class="bg-white rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] p-4 flex flex-col gap-2">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-12 h-12 rounded-xl bg-[#F0EEE7] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                            <span class="text-[20px] font-bold leading-5">01</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18] leading-6">LB-01 Teratai Air</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-semibold tracking-[0.4px]">
                                    Santap Makan
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Bill #SW-8812 • 4 Tamu • Rp 385.000</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 text-[10px] font-medium text-[#404941]">
                        <svg class="w-3 h-3 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>42m</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-[#F0EEE7]">
                    <span class="text-[#404941] flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                        </svg>
                        <span>Puck LCD: "Selamat Menikmati"</span>
                    </span>
                    <button @click="showOrderModal('LB-01 Teratai Air', 'SW-8812', 'Rp 385.000')" class="font-semibold text-xs text-[#00341A] hover:underline cursor-pointer">
                        Lihat Rincian Pesanan
                    </button>
                </div>
            </div>

            <!-- CARD LB-04: Cooking in Kitchen Progress -->
            <div class="bg-white rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] p-4 flex flex-col gap-2">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-12 h-12 rounded-xl bg-[#F0EEE7] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                            <span class="text-[20px] font-bold leading-5">04</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18] leading-6">LB-04 Gandasoli</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-semibold tracking-[0.4px]">
                                    Masak Dapur
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Tiket KDS #14 • 7 Tamu • Rp 520.000</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-[#7A5900] tracking-[0.4px]">Est. 12 mnt</span>
                </div>

                <!-- Cooking Progress Bar -->
                <div class="space-y-1 pt-1">
                    <div class="flex items-center justify-between text-[10px] font-bold text-[#404941] tracking-[0.4px]">
                        <span>Gurame Bakar Cobek & Karedok</span>
                        <span>70% Selesai</span>
                    </div>
                    <div class="w-full bg-[#F0EEE7] h-2 rounded-full overflow-hidden">
                        <div class="bg-[#0B4D2B] h-full rounded-full" style="width: 70%"></div>
                    </div>
                </div>
            </div>

            <!-- CARD LB-03: Empty & Ready for Guests -->
            <div class="bg-[#F6F3EC] rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#E5E2DB]/70 p-4 flex flex-col gap-2.5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-12 h-12 rounded-xl bg-[#E5E2DB] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                            <span class="text-[20px] font-bold leading-5">03</span>
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18] leading-6">LB-03 Bambu Hitam</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold tracking-[0.4px]">
                                    Tersedia
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Kapasitas 6 Orang • Bersih & Siap</span>
                        </div>
                    </div>
                    <svg class="w-[15px] h-[18.33px] text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button @click="triggerToast('Token QR Meja LB-03 siap discan oleh tamu baru.')" type="button" 
                            class="flex-1 h-11 rounded-xl bg-white text-[#1C1C18] font-semibold text-xs tracking-[0.24px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#E5E2DB] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>QR Meja Aktif</span>
                    </button>

                    <button @click="openTable('LB-03')" type="button" 
                            class="flex-1 h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs tracking-[0.24px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                        <svg class="w-4 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span>+ Buka Meja Tamu</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- CLUSTER B: Lesehan Atas Balong Indah (2 Saung) -->
        <section x-show="selectedCluster === 'all' || selectedCluster === 'balong'" class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                    <h2 class="font-bold text-base text-[#1C1C18] leading-6">Cluster B • Balong Indah</h2>
                </div>
                <span class="text-xs font-medium text-[#404941]">2 Saung</span>
            </div>

            <!-- Card LA-01 -->
            <div class="bg-white rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] p-4 flex items-center justify-between h-[76px]">
                <div class="flex items-center gap-2.5">
                    <div class="w-11 h-11 rounded-xl bg-[#F0EEE7] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                        <span class="text-[16px] font-bold leading-4">A01</span>
                    </div>
                    <div class="flex flex-col">
                        <h4 class="font-bold text-base text-[#1C1C18] leading-6">LA-01 Panorama Balong</h4>
                        <span class="text-xs text-[#404941] font-normal leading-4">Diantar oleh: Kang Dadan (Runner B)</span>
                    </div>
                </div>

                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-medium tracking-[0.4px]">
                    Bill #SW-8820
                </span>
            </div>

            <!-- Card LA-02 (Reserved) -->
            <div class="bg-white rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] p-4 flex items-center justify-between h-[76px]">
                <div class="flex items-center gap-2.5">
                    <div class="w-11 h-11 rounded-xl bg-[#FFDEA1] text-[#261900] flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                        <span class="text-[16px] font-bold leading-4">A02</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-bold text-base text-[#1C1C18] leading-6">LA-02 Kicau Burung</h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold tracking-[0.4px]">
                                Booking
                            </span>
                        </div>
                        <span class="text-xs text-[#404941] font-normal leading-4">18:30 WIB • Pak H. Dani (8 Tamu)</span>
                    </div>
                </div>

                <div class="flex flex-col items-end leading-3">
                    <span class="font-bold text-[10px] text-[#7A5900] tracking-[0.4px]">DP Masuk</span>
                    <span class="font-semibold text-xs text-[#404941] mt-1">Rp 200rb</span>
                </div>
            </div>
        </section>

        <!-- CLUSTER C: Area Meja Kursi Kayu (4 Meja - 2x2 Grid) -->
        <section x-show="selectedCluster === 'all' || selectedCluster === 'kayu'" class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#707971]"></span>
                    <h2 class="font-bold text-base text-[#1C1C18] leading-6">Cluster C • Meja Kursi Kayu</h2>
                </div>
                <span class="text-xs font-medium text-[#404941]">4 Meja</span>
            </div>

            <!-- 2x2 Grid -->
            <div class="grid grid-cols-2 gap-3">
                <!-- KA-01 -->
                <div class="bg-white rounded-2xl p-3 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] h-28 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-01</span>
                        <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#F0EEE7] text-[#404941] text-[10px] font-medium w-fit">
                            4 Tamu
                        </span>
                        <span class="text-xs text-[#404941] font-semibold">Bill #SW-8815</span>
                    </div>
                </div>

                <!-- KA-02 -->
                <div class="bg-[#F6F3EC] rounded-2xl p-3 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#E5E2DB]/60 h-28 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-02</span>
                        <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold w-fit">
                            Tersedia
                        </span>
                        <span class="text-xs text-[#404941] font-normal">Kapasitas 4 Org</span>
                    </div>
                </div>

                <!-- KA-03 -->
                <div class="bg-[#F6F3EC] rounded-2xl p-3 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#E5E2DB]/60 h-28 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-03</span>
                        <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold w-fit">
                            Tersedia
                        </span>
                        <span class="text-xs text-[#404941] font-normal">Kapasitas 4 Org</span>
                    </div>
                </div>

                <!-- KA-04 -->
                <div class="bg-white rounded-2xl p-3 shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#F0EEE7] h-28 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-04</span>
                        <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#F0EEE7] text-[#404941] text-[10px] font-medium w-fit">
                            8 Tamu
                        </span>
                        <span class="text-xs text-[#404941] font-semibold">Bill #SW-8809</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Primary Action Trigger (Input Pesanan Tambahan / Walk-in) -->
        <div class="pt-3 pb-2">
            <button @click="showNewOrderModal = true" type="button" 
                    class="w-full h-14 rounded-2xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm tracking-[0.35px] shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1),0_4px_6px_-4px_rgba(0,0,0,0.1)] flex items-center justify-center gap-2.5 transition-all active:scale-98 cursor-pointer">
                <div class="w-7 h-7 rounded-full bg-[#765600] text-[#FECE66] flex items-center justify-center font-bold text-sm">
                    +
                </div>
                <span>Input Pesanan Walk-in / Tambahan</span>
            </button>
        </div>

    </div>

    <!-- Modal QR Scanner -->
    <div x-show="showQrModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showQrModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Pindai Dudukan QR Saung</h3>
                </div>
                <button @click="showQrModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <div class="bg-[#1C1C18] rounded-xl h-48 flex flex-col items-center justify-center relative overflow-hidden border-2 border-dashed border-[#B1F1C2]">
                <div class="w-28 h-28 border-2 border-[#FECE66] rounded-xl relative flex items-center justify-center">
                    <span class="w-full h-0.5 bg-[#FECE66] absolute animate-pulse"></span>
                    <span class="text-3xl text-white">📷</span>
                </div>
                <span class="text-xs text-white/80 mt-3 font-mono">Arahkan ke QR Saung...</span>
            </div>
            <button @click="showQrModal = false; openTable('LB-03')" type="button" class="w-full py-2.5 bg-[#00341A] hover:bg-[#0B4D2B] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98 cursor-pointer">
                Simulasi Scan Saung LB-03
            </button>
        </div>
    </div>

    <!-- Modal Input Pesanan Tambahan -->
    <div x-show="showNewOrderModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showNewOrderModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Input Pesanan Tambahan</h3>
                </div>
                <button @click="showNewOrderModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[#1C1C18] block mb-1">Pilih Saung / Meja:</label>
                    <select class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl p-2.5 font-semibold text-[#1C1C18]">
                        @foreach ($meja ?? [] as $m)
                            <option value="{{ $m->id }}">{{ $m->kode_meja }} ({{ $m->area }} - {{ $m->kapasitas }} Tamu)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-[#1C1C18] block mb-1">Menu Tambahan Cepat:</label>
                    <div class="space-y-1.5">
                        <label class="flex items-center justify-between p-2 rounded-lg bg-[#F0EEE7]">
                            <span>+ Nasi Liwet Kastrol Mini</span>
                            <span class="font-bold text-[#00341A]">Rp 28.000</span>
                        </label>
                        <label class="flex items-center justify-between p-2 rounded-lg bg-[#F0EEE7]">
                            <span>+ Es Teh Manis Ciwidey</span>
                            <span class="font-bold text-[#00341A]">Rp 8.000</span>
                        </label>
                        <label class="flex items-center justify-between p-2 rounded-lg bg-[#F0EEE7]">
                            <span>+ Sambal Cobek Dadak</span>
                            <span class="font-bold text-[#00341A]">Rp 6.000</span>
                        </label>
                    </div>
                </div>
            </div>
            <button @click="showNewOrderModal = false; triggerToast('Pesanan tambahan terkirim ke KDS Dapur & Bar.')" type="button" 
                    class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98 cursor-pointer">
                Kirim ke Dapur / Bar
            </button>
        </div>
    </div>

    <!-- Modal Rincian Pesanan -->
    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showDetailModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="detailTitle">Rincian Pesanan</h3>
                </div>
                <button @click="showDetailModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1 border-b border-[#F0EEE7]">
                    <span class="text-[#404941]">Nomor Tagihan:</span>
                    <span class="font-bold text-[#1C1C18]" x-text="detailBill">#SW-8812</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#F0EEE7]">
                    <span class="text-[#404941]">Total Tagihan:</span>
                    <span class="font-bold text-[#00341A]" x-text="detailTotal">Rp 385.000</span>
                </div>
                <div class="py-1">
                    <span class="font-bold text-[#1C1C18] block mb-1">Item Pesanan:</span>
                    <p class="text-[#404941]" x-text="detailItems || 'Gurame Bakar Situ Awi, Nasi Liwet Kastrol Mini x2, Karedok Leunca Sunda, Es Teh Manis x4'">Gurame Bakar Situ Awi, Nasi Liwet Kastrol Mini x2, Karedok Leunca Sunda, Es Teh Manis x4</p>
                </div>
            </div>
            <button @click="showDetailModal = false" type="button" class="w-full py-2.5 bg-[#0B4D2B] text-white font-bold text-xs rounded-xl cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 translate-y-4" 
         x-transition:enter-end="opacity-100 translate-y-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 translate-y-0" 
         x-transition:leave-end="opacity-0 translate-y-4" 
         class="fixed bottom-20 left-4 right-4 z-50 max-w-[390px] mx-auto pointer-events-none">
        <div class="bg-[#31312C] text-[#F3F0EA] rounded-xl px-4 py-3 flex items-center gap-3 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-white/10 pointer-events-auto">
            <svg class="w-[18.33px] h-[18.33px] text-[#FFDEA1] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs font-normal text-[#F3F0EA] leading-4 flex-1" x-text="toastMessage">Notifikasi berhasil diproses.</span>
        </div>
    </div>

</div>

<script>
    function pelayanTabSaung() {
        return {
            runnerName: '{{ auth()->user()->nama ?? auth()->user()->name ?? "Runner" }}',
            shiftInfo: 'Shift Siang (12:00 – 18:00 WIB)',
            currentArea: '{{ $currentArea ?? "Tepi Danau (LB-01 – LB-05)" }}',
            meshStatus: 'IoT Mesh 11/11 (-52dBm)',

            totalMeja: {{ $totalMeja ?? 11 }},
            totalPercentage: {{ ($totalMeja ?? 0) > 0 ? round((($terisiCount ?? 0) / $totalMeja) * 100) : 64 }},
            terisiCount: {{ $terisiCount ?? 7 }},
            tersediaCount: {{ $tersediaCount ?? 3 }},
            panggilanCount: {{ $panggilanCount ?? 1 }},

            selectedCluster: 'all', // 'all', 'danau', 'balong', 'kayu'

            // CRITICAL: Bell ON & Permintaan Tamu merah on LB-02 only active while calling!
            lb02Calling: {{ ($panggilanCount ?? 0) > 0 ? 'true' : 'false' }},
            buzzerActive: true,

            showQrModal: false,
            showNewOrderModal: false,
            showDetailModal: false,
            detailTitle: '',
            detailBill: '',
            detailTotal: '',
            detailItems: '',

            toastMessage: 'Notifikasi berhasil diproses.',
            showToast: false,

            init() {
                // Selalu assign dari backend — tanpa kondisi > 0 agar 0 pun terbaca
                this.currentArea     = '{{ $currentArea ?? "Tepi Danau (LB-01 – LB-05)" }}';
                this.totalMeja       = {{ $totalMeja ?? 0 }};
                this.terisiCount     = {{ $terisiCount ?? 0 }};
                this.tersediaCount   = {{ $tersediaCount ?? 0 }};
                this.panggilanCount  = {{ $panggilanCount ?? 0 }};
                this.lb02Calling     = {{ ($panggilanCount ?? 0) > 0 ? 'true' : 'false' }};
                this.totalPercentage = this.totalMeja > 0 ? Math.round((this.terisiCount / this.totalMeja) * 100) : 0;
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                if (typeof playChime === 'function') {
                    playChime('success');
                }
                setTimeout(() => {
                    this.showToast = false;
                }, 3500);
            },

            async respondCall() {
                // When waiter clicks Respon & Temui, clear the urgent calling state!
                this.lb02Calling = false;
                this.panggilanCount = Math.max(0, this.panggilanCount - 1);

                // Optional: find active call ID to send PATCH to backend
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                try {
                    // Try to send update to backend if route exists
                    @if(isset($panggilanAktif) && count($panggilanAktif) > 0)
                        const callId = {{ $panggilanAktif[0]->id }};
                        await fetch(`/pelayan/panggilan/${callId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ status_panggilan: 'selesai' }),
                        });
                    @endif
                } catch(e) {}

                this.triggerToast('Respons panggilan Saung dicatat! Menuju lokasi...');
            },

            muteBuzzer() {
                this.buzzerActive = false;
                this.triggerToast('Sinyal MQTT terkirim: Buzzer meja dinonaktifkan.');
            },

            openTable(saungCode) {
                this.triggerToast('Sesi tamu untuk ' + saungCode + ' dibuka. QR dinamis aktif.');
            },

            showOrderModal(title, bill, total, items = '') {
                this.detailTitle = title;
                this.detailBill = bill;
                this.detailTotal = total;
                this.detailItems = items;
                this.showDetailModal = true;
            }
        };
    }
</script>
@endsection