@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Antar) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="pelayanTabAntar()" class="relative flex flex-col min-h-screen" x-init="init()">

    <header class="sticky top-0 z-40 bg-[#FCF9F2]/90 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-[#E5E2DB]/60 px-4 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-widest uppercase leading-none">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Antar</h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white font-bold text-xs flex items-center justify-center shadow-xs ring-2 ring-[#0B4D2B]/20">
                {{ auth()->user()->nama ? strtoupper(substr(auth()->user()->nama, 0, 2)) : 'PL' }}
            </div>
        </div>
    </header>

    <div class="p-4 space-y-4 flex-1">

        <div class="bg-[#F0EEE7] rounded-xl p-2.5 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 leading-tight">
                        <span class="font-bold text-base text-[#1C1C18]" x-text="runnerName">{{ auth()->user()->nama }}</span>
                        <span class="text-[10px] font-medium text-[#404941]">(Area {{ runnerArea }})</span>
                    </div>
                    <span class="text-xs text-[#404941] block leading-tight mt-0.5" x-text="shiftInfo">Shift Siang (12:00–18:00)</span>
                </div>
            </div>

            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-bold">
                <svg class="w-3 h-3 text-[#213200]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
                <span x-text="signalStrength">-52 dBm</span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-[#0B4D2B] rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                <span class="font-display font-bold text-3xl text-[#B1F1C2] leading-none" x-text="readyCount">0</span>
                <span class="text-[10px] font-bold text-[#7FBD92] uppercase tracking-tight mt-1.5">HIDANGAN SIAP</span>
            </div>

            <div class="bg-[#FECE66] rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                <span class="font-display font-bold text-3xl text-[#261900] leading-none" x-text="deliveringCount">0</span>
                <span class="text-[10px] font-bold text-[#765600] uppercase tracking-tight mt-1.5">DIANTAR</span>
            </div>

            <div class="bg-[#EBE8E1] rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                <span class="font-display font-bold text-3xl text-[#1C1C18] leading-none" x-text="finishedCount">0</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1.5">SELESAI</span>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none -mx-4 px-4">
            <button 
                @click="filterCategory = 'all'"
                :class="filterCategory === 'all' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Semua Siap</span>
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="filterCategory === 'all' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      x-text="readyCount">0</span>
            </button>

            <button 
                @click="filterCategory = 'kitchen'"
                :class="filterCategory === 'kitchen' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Dapur Makanan</span>
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="filterCategory === 'kitchen' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      x-text="kitchenCount">0</span>
            </button>

            <button 
                @click="filterCategory = 'bar'"
                :class="filterCategory === 'bar' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Bar Minuman</span>
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="filterCategory === 'bar' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      x-text="barCount">0</span>
            </button>

            <button 
                @click="filterCategory = 'quick'"
                :class="filterCategory === 'quick' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Prioritas Cepat</span>
            </button>
        </div>

        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                    </svg>
                    <h2 class="font-bold text-base text-[#1C1C18]">Siap di Pass Counter</h2>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[#00341A] text-[10px] font-bold">
                    Prioritas Cepat
                </span>
            </div>

            <template x-for="pesanan in filteredSiapAntar" :key="pesanan.id">
                <div x-show="!delivered[pesanan.id] && (filterCategory === 'all' || filterCategory === getCategory(pesanan) || filterCategory === 'quick')" 
                     x-transition
                     class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded" :class="getCategory(pesanan) === 'kitchen' ? 'bg-[#FFDEA1] text-[#261900]' : 'bg-[#CBEF89] text-[#131F00]'" x-text="getStationName(pesanan)">Pass Dapur</span>
                            <span class="flex items-center gap-1 text-[10px] font-bold" :class="getUrgencyClass(pesanan)">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="getElapsedTime(pesanan)"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] font-bold text-[#404941]" x-text="'#' + pesanan.kode_pesanan"></span>
                            <span class="px-2 py-0.5 rounded-full bg-[#F0EEE7] text-[#00341A] text-xs font-bold" x-text="pesanan.detailPesanan.length + ' Item'"></span>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-bold text-xl text-[#1C1C18] leading-tight" x-text="pesanan.meja.kode_meja + ' ' + pesanan.meja.area"></h3>
                        <p class="text-xs text-[#404941] mt-0.5" x-text="pesanan.meja.area"></p>
                    </div>

                    <div class="bg-[#F0EEE7] rounded-xl p-3 space-y-2 border border-[#E5E2DB]/60">
                        <template x-for="item in pesanan.detailPesanan" :key="item.id">
                            <div class="flex items-start gap-2">
                                <span class="w-3.5 h-3.5 text-[#00341A] mt-0.5 flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" :class="item.menu.kategori.target_kds === 'bar' ? 'text-[#7A5900]' : 'text-[#00341A]'">
                                        <path x-show="item.menu.kategori.target_kds === 'bar'" stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        <path x-show="item.menu.kategori.target_kds !== 'bar'" stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                                    </svg>
                                </span>
                                <div>
                                    <span class="font-bold text-sm text-[#1C1C18] block leading-snug" x-text="item.jumlah + 'x ' + item.menu.nama_menu"></span>
                                    <span class="text-xs italic text-[#404941]" x-text="item.catatan || 'Tanpa catatan'"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="space-y-2 pt-1">
                        <button 
                            @click="markDelivered(pesanan.id, pesanan.meja.kode_meja + ' ' + pesanan.meja.area)"
                            class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all active:scale-98 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Ambil & Tandai Selesai Diantarkan</span>
                        </button>

                        <button 
                            @click="showMapModal = true; selectedSaung = pesanan.meja"
                            class="w-full h-9 rounded-lg bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] font-semibold text-xs flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <span x-text="'Lihat Denah ' + pesanan.meja.kode_meja"></span>
                        </button>
                    </div>
                </div>
            </template>

            <div x-show="filteredSiapAntar.length === 0" class="text-center py-8 text-[#404941]">
                <svg class="w-12 h-12 mx-auto text-[#E5E2DB]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="font-bold text-sm mt-3">Tidak ada hidangan siap diantar</p>
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18]">Sedang Diantar Rekan Runner</h3>
                </div>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider">Live Mesh</span>
            </div>

            <template x-for="runner in deliveringRunners" :key="runner.id">
                <div class="bg-[#F0EEE7] rounded-xl p-3 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#FFDEA1] text-[#261900] text-xs font-bold flex items-center justify-center flex-shrink-0 shadow-2xs" x-text="runner.initials"></div>
                        <div>
                            <div class="flex items-center gap-1.5 leading-tight">
                                <span class="font-bold text-xs text-[#1C1C18]" x-text="runner.name"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                            </div>
                            <span class="text-xs font-semibold text-[#00341A] block leading-tight mt-0.5" x-text="runner.currentTask"></span>
                        </div>
                    </div>

                    <div class="flex flex-col items-end text-right">
                        <span class="text-[10px] font-bold text-[#7A5900]" x-text="runner.elapsedTime"></span>
                        <span class="text-[10px] font-bold text-[#404941]">Di Jalur</span>
                    </div>
                </div>
            </template>

            <div x-show="deliveringRunners.length === 0" class="bg-[#F0EEE7] rounded-xl p-3 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 leading-tight">
                            <span class="font-bold text-xs text-[#1C1C18]">Tidak ada runner aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[#EBE8E1] rounded-xl p-2.5 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#E5E2DB] text-[#00341A] flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-[#1C1C18] leading-tight">Mode Suara & Getar Aktif</h4>
                    <p class="text-[11px] text-[#404941] leading-tight mt-0.5">Notifikasi pesanan siap dapur</p>
                </div>
            </div>

            <button 
                @click="soundVibrationActive = !soundVibrationActive; triggerToast(soundVibrationActive ? 'Notifikasi suara & getar dapur diaktifkan.' : 'Mode senyap diaktifkan.')"
                type="button" 
                :class="soundVibrationActive ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                <span 
                    :class="soundVibrationActive ? 'translate-x-5' : 'translate-x-0.5'"
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
            </button>
        </div>

    </div>

    <div x-show="showMapModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showMapModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="'Denah ' + (selectedSaung?.kode_meja || '')"></h3>
                </div>
                <button @click="showMapModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-4 space-y-3 text-xs text-[#404941]">
                <template x-for="(step, index) in mapSteps" :key="index">
                    <div class="flex items-start gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center font-bold text-xs flex-shrink-0" x-text="index + 1"></span>
                        <div>
                            <strong class="text-[#1C1C18] block" x-text="step.title"></strong>
                            <span x-text="step.desc"></span>
                        </div>
                    </div>
                </template>
            </div>

            <button 
                @click="showMapModal = false"
                class="w-full py-2.5 bg-[#00341A] hover:bg-[#0B4D2B] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">
                Tutup Peta
            </button>
        </div>
    </div>

    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-20 left-4 right-4 z-50 max-w-[390px] mx-auto">
        <div class="bg-[#31312C] text-[#F3F0EA] rounded-xl px-4 py-3 flex items-center gap-3 shadow-2xl border border-white/10">
            <svg class="w-5 h-5 text-[#FFDEA1] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs font-medium leading-snug flex-1" x-text="toastMessage">Notifikasi berhasil diproses.</span>
        </div>
    </div>

</div>

<script>
    function pelayanTabAntar() {
        return {
            filterCategory: 'all',
            soundVibrationActive: true,
            showMapModal: false,
            toastMessage: '',
            showToast: false,
            delivered: {},
            selectedSaung: null,
            runnerName: '',
            runnerArea: '',
            shiftInfo: '',
            signalStrength: '',
            readyCount: 0,
            deliveringCount: 0,
            finishedCount: 0,
            kitchenCount: 0,
            barCount: 0,
            siapAntar: [],
            deliveringRunners: [],
            mapSteps: [],

            init() {
                this.loadData();
                this.runnerName = '{{ auth()->user()->nama }}';
                this.runnerArea = '{{ auth()->user()->area ?? "Danau" }}';
                this.shiftInfo = 'Shift Siang (12:00–18:00)';
                this.signalStrength = '-52 dBm';
                this.mapSteps = [
                    { title: 'Pass Dapur Utama', desc: 'Ambil baki nampan dari Pass Counter.' },
                    { title: 'Jalur Dermaga Kayu', desc: 'Turun ke arah tepi danau bawah (arah barat).' },
                    { title: 'Saung Tujuan', desc: 'Saung nomor 2 dari dermaga utama, menghadap air terjun mini.' }
                ];
                this.deliveringRunners = @json($deliveringRunners);
            },

            loadData() {
                this.readyCount = @json($readyCount);
                this.kitchenCount = @json($kitchenCount);
                this.barCount = @json($barCount);
                this.deliveringCount = @json($deliveringCount);
                this.finishedCount = @json($finishedCount);
                this.siapAntar = @json($siapAntarFormatted);
                this.updateCounts();
            },

            updateCounts() {
                this.readyCount = this.siapAntar.length;
                this.kitchenCount = this.siapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'dapur').length;
                this.barCount = this.siapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'bar').length;
                this.deliveringCount = @json($deliveringCount);
            },

            get filteredSiapAntar() {
                if (this.filterCategory === 'all') return this.siapAntar;
                if (this.filterCategory === 'kitchen') return this.siapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'dapur');
                if (this.filterCategory === 'bar') return this.siapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'bar');
                if (this.filterCategory === 'quick') return this.siapAntar.filter(p => this.getElapsedMinutes(p) <= 2);
                return this.siapAntar;
            },

            getCategory(pesanan) {
                return pesanan.detailPesanan[0]?.menu?.kategori?.target_kds ?? 'dapur';
            },

            getStationName(pesanan) {
                return this.getCategory(pesanan) === 'bar' ? 'Bar Counter Beverage' : 'Pass Dapur #' + (Math.floor(Math.random() * 2) + 1);
            },

            getElapsedTime(pesanan) {
                const diff = Math.floor((Date.now() - new Date(pesanan.waktu_pesan).getTime()) / 60000);
                return diff + ' mnt lalu';
            },

            getElapsedMinutes(pesanan) {
                return Math.floor((Date.now() - new Date(pesanan.waktu_pesan).getTime()) / 60000);
            },

            getUrgencyClass(pesanan) {
                const minutes = this.getElapsedMinutes(pesanan);
                return minutes >= 5 ? 'text-[#BA1A1A]' : (minutes >= 3 ? 'text-[#7A5900]' : 'text-[#404941]');
            },

            markDelivered(id, saungName) {
                this.delivered[id] = true;
                this.readyCount = Math.max(0, this.readyCount - 1);
                this.finishedCount++;
                const category = this.siapAntar.find(p => p.id === id)?.detailPesanan[0]?.menu?.kategori?.target_kds ?? 'dapur';
                if (category === 'bar') this.barCount = Math.max(0, this.barCount - 1);
                else this.kitchenCount = Math.max(0, this.kitchenCount - 1);
                this.triggerToast('Pesanan ' + saungName + ' berhasil diantar & diselesaikan.');
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                playChime('success');
                setTimeout(() => {
                    this.showToast = false;
                }, 3500);
            }
        }
    }
</script>
@endsection