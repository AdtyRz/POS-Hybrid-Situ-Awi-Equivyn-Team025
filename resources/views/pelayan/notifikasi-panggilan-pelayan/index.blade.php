@extends('layouts.pelayan')

@section('title', 'Mobile POS & Panggilan Pelayan • Situ Awi Saung Lesehan')

@section('content')
<div x-data="pelayanNotifikasiPanggilan()" class="relative flex flex-col min-h-screen" x-init="init()">

    <header class="sticky top-0 z-40 bg-[#FCF9F2]/90 backdrop-blur-md shadow-[0_2px_12px_rgba(43,42,38,0.04)] border-b border-[#E5E2DB]/60 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-9 w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-base text-[#00341A] tracking-tight leading-tight">Situ Awi Runner</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[#11512F] text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#11512F] animate-pulse"></span>
                        LIVE
                    </span>
                </div>
                <span class="text-xs text-[#404941] leading-tight mt-0.5" x-text="runnerInfo">Kang Asep • Runner Lesehan</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button @click="triggerToast('Semua notifikasi sinkron dengan EMQX Broker & Reverb.')" class="w-10 h-10 rounded-full bg-[#F6F3EC] text-[#404941] flex items-center justify-center relative active:scale-95 transition-transform cursor-pointer shadow-2xs">
                <svg class="w-5 h-5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-[#FECE66] ring-2 ring-[#FCF9F2]" x-show="panggilanCount > 0"></span>
            </button>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white font-bold text-xs flex items-center justify-center shadow-xs">
                {{ strtoupper(substr(auth()->user()->nama ?? 'PL', 0, 2)) }}
            </div>
        </div>
    </header>

    <div class="p-4 space-y-4 flex-1">

        <div class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 font-bold text-[#1C1C18]">
                    <svg class="w-4 h-4 text-[#00341A] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="currentArea">Area Bawah (Situ Danau)</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#EBE8E1] text-[#404941] text-[10px] font-bold">
                    <svg class="w-3 h-3 text-[#213200]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    <span x-text="meshStatus">Mesh -52 dBm</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs border-b border-[#E5E2DB]/70 pb-2.5">
                <div class="flex items-center gap-1.5 text-[#404941]">
                    <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="shiftInfo">Shift Siang (12:00 - 18:00 WIB)</span>
                </div>
                <div class="flex items-center gap-1.5 text-[#00341A] font-semibold text-xs">
                    <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                    <span>Koneksi Sinkron</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-1">
                <div class="bg-white rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                    <span class="font-bold text-xl text-[#00341A] leading-tight" x-text="callsAnswered">3</span>
                    <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider mt-1 leading-tight">Panggilan Direspon</span>
                </div>
                <div class="bg-white rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                    <span class="font-bold text-xl text-[#7A5900] leading-tight" x-text="ordersDelivered">18</span>
                    <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider mt-1 leading-tight">Pesanan Diantar</span>
                </div>
                <div class="bg-white rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                    <span class="font-bold text-xl text-[#334A00] leading-tight" x-text="avgResponse">1.8m</span>
                    <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider mt-1 leading-tight">Rata Respon</span>
                </div>
            </div>
        </div>

        <div x-show="hasUrgentAlert" x-transition class="bg-[#FFDAD6] rounded-2xl p-4 shadow-xl border border-[#BA1A1A]/30 relative overflow-hidden space-y-3" :class="{ 'ring-2 ring-[#BA1A1A] animate-urgent': !alertResponded }">

            <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#FECE66]/40 rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-start justify-between relative z-10">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#BA1A1A] text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-5 h-5 text-white animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-[#BA1A1A] tracking-wider uppercase">DARURAT IOT MEJA</span>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold text-white uppercase" :class="buzzerActive ? 'bg-[#BA1A1A] animate-pulse' : 'bg-[#707971]'">
                                <span x-text="buzzerActive ? 'Buzzer ON' : 'Buzzer OFF'">Buzzer ON</span>
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-[#93000A] tracking-tight leading-tight mt-0.5" x-text="urgentSaungName">Saung 02 (LB-02)</h2>
                    </div>
                </div>

                <div class="flex flex-col items-end">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white text-[#BA1A1A] text-[11px] font-bold shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-[#BA1A1A] animate-ping"></span>
                        <span x-text="formatTime(alertSecondsLeft)">00:50</span>
                    </span>
                    <span class="text-[10px] text-[#93000A]/80 font-semibold mt-1">Sensor TTP223</span>
                </div>
            </div>

            <div class="bg-white/85 rounded-xl p-3.5 space-y-2 backdrop-blur-xs relative z-10 border border-white">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-[#1C1C18] flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Permintaan Tamu:
                    </span>
                    <span class="text-[10px] font-semibold text-[#404941]">1 mnt yang lalu</span>
                </div>

                <blockquote class="text-sm italic font-medium text-[#1C1C18] leading-relaxed" x-text="urgentRequest">"Tamu butuh bantuan tambahan / piring ekstra & sendok liwet"</blockquote>

                <div class="flex items-center gap-2 pt-1.5 border-t border-[#F0EEE7] text-[11px] text-[#404941]">
                    <svg class="w-3.5 h-3.5 text-[#00341A] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <span x-text="urgentEspInfo">ESP32 Meja #02 • LCD: 'Memanggil Pelayan...'</span>
                </div>
            </div>

            <div class="space-y-2 relative z-10">
                <button @click="respondToAlert()" :disabled="alertResponded" class="w-full h-12 rounded-full font-bold text-sm shadow-md flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer" :class="alertResponded ? 'bg-[#2E6A45] text-white cursor-default' : 'bg-[#00341A] hover:bg-[#0B4D2B] text-white'">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="alertResponded ? 'Sedang Dihampiri (Dalam Penanganan)' : 'Respon & Menuju Saung 02'">Respon & Menuju Saung 02</span>
                </button>

                <button @click="muteBuzzer()" :disabled="!buzzerActive" class="w-full h-12 rounded-full font-bold text-sm bg-[#EBE8E1] hover:bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer disabled:opacity-60">
                    <svg class="w-4 h-4 text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                    <span x-text="buzzerActive ? 'Matikan Buzzer Meja' : 'Buzzer Telah Dimatikan'">Matikan Buzzer Meja</span>
                </button>
            </div>
        </div>

        <div class="space-y-3 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253"/></svg>
                    <h2 class="text-xl font-bold text-[#1C1C18]">Siap Diantarkan</h2>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#B1F1C2] text-[#00210E] text-xs font-bold shadow-2xs" x-text="siapCount + ' Hidangan Siap'">3 Hidangan Siap</span>
            </div>
            <p class="text-xs text-[#404941]">Ambil dari Meja Pass Dapur / Bar & verifikasi nomor saung.</p>

            <template x-for="pesanan in siapAntar" :key="pesanan.id">
                <div x-show="!delivered[pesanan.id]" class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md" :class="pesanan.source === 'dapur' ? 'bg-[#FECE66] text-[#765600]' : 'bg-[#CBEF89] text-[#364E00]'" x-text="pesanan.sourceLabel"></span>
                                <h3 class="text-base font-bold text-[#1C1C18]" x-text="pesanan.meja.kode_meja + ' ' + pesanan.meja.area"></h3>
                            </div>
                            <span class="text-[10px] font-semibold text-[#404941] block mt-0.5" x-text="pesanan.source"></span>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#E5E2DB] text-[#404941] text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full" :class="pesanan.source === 'dapur' ? 'bg-[#7A5900]' : 'bg-[#213200]'"></span>
                            <span x-text="pesanan.timeAgo"></span>
                        </span>
                    </div>

                    <div class="bg-white rounded-xl p-3 flex gap-3 items-start border" :class="pesanan.source === 'dapur' ? 'border-[#FECE66]/40' : 'border-[#B1F1C2]'">
                        <div class="w-16 h-16 rounded-xl" :class="pesanan.source === 'dapur' ? 'bg-[#FECE66]/20 border border-[#FECE66]/40' : 'bg-[#B1F1C2]/30 border border-[#B1F1C2]'" x-text="pesanan.icon"></div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm text-[#1C1C18] leading-tight" x-text="pesanan.mainItem"></h4>
                            <p class="font-semibold text-xs text-[#404941] mt-0.5" x-text="pesanan.sideItems"></p>
                            <p class="text-xs font-medium text-[#7A5900] italic mt-1 leading-snug" x-text="pesanan.note"></p>
                        </div>
                    </div>

                    <button @click="deliverOrder(pesanan.id)" class="w-full h-12 rounded-xl" :class="pesanan.source === 'dapur' ? 'bg-[#0B4D2B] hover:bg-[#00341A] text-white' : 'bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600]'" x-text="pesanan.actionText"></button>
                </div>
            </template>

            <div x-show="siapAntar.length === 0 || siapAntar.every(p => delivered[p.id])" class="text-center py-8 text-[#404941]">
                <svg class="w-12 h-12 mx-auto text-[#E5E2DB]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="font-bold text-sm mt-3">Tidak ada hidangan siap diantar</p>
            </div>
        </div>

        <div id="status-saung" class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <h3 class="text-base font-bold text-[#1C1C18]">Status Saung Terdekat</h3>
                </div>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider" x-text="statusArea">Area Situ Bawah</span>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                <template x-for="saung in saungStatus" :key="saung.code">
                    <div class="bg-white rounded-xl p-2.5 shadow-2xs border" :class="saung.borderClass">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :class="saung.dotColor"></span>
                            <span class="font-bold text-xs text-[#1C1C18]" x-text="saung.code"></span>
                        </div>
                        <span class="text-[10px] font-semibold" :class="saung.statusColor" x-text="saung.status"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="bg-[#F0EEE7] rounded-2xl p-3.5 flex items-center justify-between border border-[#E5E2DB]/80">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#E5E2DB] text-[#7A5900] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-[#1C1C18] leading-tight">Mode Suara + Getar Aktif</h4>
                    <p class="text-[11px] text-[#404941] leading-tight mt-0.5">Volume Keras untuk Saung Tepi Danau</p>
                </div>
            </div>

            <button @click="soundVibrationActive = !soundVibrationActive; triggerToast(soundVibrationActive ? 'Mode suara & getar diaktifkan.' : 'Mode suara & getar dinonaktifkan.')" type="button" :class="soundVibrationActive ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"><span :class="soundVibrationActive ? 'translate-x-5' : 'translate-x-0.5'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span></button>
        </div>

    </div>

    <div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-20 left-4 right-4 z-50 max-w-[390px] mx-auto">
        <div class="bg-[#31312C] text-[#F3F0EA] rounded-xl px-4 py-3 flex items-center gap-3 shadow-2xl border border-white/10"><svg class="w-5 h-5 text-[#FFDEA1] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-xs font-medium leading-snug flex-1" x-text="toastMessage">Notifikasi berhasil diproses.</span></div>
    </div>

</div>

<script>
    function pelayanNotifikasiPanggilan() {
        return {
            hasUrgentAlert: true,
            alertResponded: false,
            buzzerActive: true,
            alertSecondsLeft: 50,
            toastMessage: '',
            showToast: false,
            soundVibrationActive: true,
            delivered: {},

            callsAnswered: 0,
            ordersDelivered: 0,
            avgResponse: '1.8m',

            init() {
                this.loadData();
                setInterval(() => {
                    if (this.hasUrgentAlert && this.alertSecondsLeft > 0) {
                        this.alertSecondsLeft--;
                    }
                }, 1000);
            },

            loadData() {
                this.runnerInfo = '{{ auth()->user()->nama }} • Runner Lesehan';
                this.currentArea = '{{ $currentArea ?? "Area Bawah (Situ Danau)" }}';
                this.meshStatus = 'Mesh -52 dBm';
                this.shiftInfo = 'Shift Siang (12:00 - 18:00 WIB)';
                this.callsAnswered = {{ $callsAnswered ?? 3 }};
                this.ordersDelivered = {{ $ordersDelivered ?? 18 }};
                this.avgResponse = '{{ $avgResponse ?? "1.8m" }}';

                this.siapAntar = @json($siapAntarFormatted);

                this.saungStatus = @json($saungStatus);
            },

            formatTime(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                playChime(msg.includes('Darurat') ? 'urgent' : 'success');
                setTimeout(() => {
                    this.showToast = false;
                }, 3500);
            },

            respondToAlert() {
                this.alertResponded = true;
                this.callsAnswered++;
                this.triggerToast('Respons panggilan dicatat! Menuju lokasi saung...');
            },

            muteBuzzer() {
                this.buzzerActive = false;
                this.triggerToast('Sinyal MQTT terkirim: Buzzer dinonaktifkan.');
            },

            deliverOrder(id) {
                this.delivered[id] = true;
                this.ordersDelivered++;
                this.triggerToast('Pesanan berhasil ditandai selesai diantar.');
            }
        }
    }
</script>
@endsection