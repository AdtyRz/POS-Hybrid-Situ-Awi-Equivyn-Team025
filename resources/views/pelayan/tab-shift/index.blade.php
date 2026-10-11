@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Shift) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="pelayanTabShift()" class="relative flex flex-col min-h-screen" x-init="init()">

    <header class="sticky top-0 z-40 bg-[#FCF9F2]/90 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-[#E5E2DB]/60 px-4 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-widest uppercase leading-none">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Shift</h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-xs flex items-center justify-center shadow-xs">
                {{ strtoupper(substr(auth()->user()->nama ?? 'PL', 0, 2)) }}
            </div>
        </div>
    </header>

    <div class="p-4 space-y-4 flex-1">

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] relative overflow-hidden space-y-3">
            <div class="absolute -right-10 -top-10 w-36 h-36 bg-gradient-to-br from-[#FECE66]/20 to-transparent rounded-full blur-xl pointer-events-none"></div>

            <div class="flex items-center gap-3.5 relative z-10">
                <div class="relative flex-shrink-0">
                    <div class="w-16 h-16 rounded-2xl bg-[#0B4D2B] text-[#B1F1C2] font-bold text-2xl flex items-center justify-center shadow-md">
                        {{ strtoupper(substr(auth()->user()->nama ?? 'PL', 0, 2)) }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[#0B4D2B] border border-white text-white flex items-center justify-center shadow-xs">
                        <svg class="w-3 h-3 text-[#FECE66]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-[#7A5900] tracking-wider uppercase" x-text="staffId">ID: STF-006</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#B1F1C2]/50 text-[#11512F] text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#11512F] animate-pulse"></span>
                            Aktif Bertugas
                        </span>
                    </div>
                    <h2 class="font-bold text-xl text-[#1C1C18] leading-tight truncate mt-0.5" x-text="runnerName">{{ auth()->user()->nama }}</h2>
                    <span class="text-xs text-[#404941] block truncate mt-0.5" x-text="role">Pelayan / Runner Lesehan Utama</span>
                </div>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-3 space-y-2 relative z-10 border border-[#E5E2DB]/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-[#7A5900]/10 text-[#7A5900] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider block">WILAYAH TUGAS</span>
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug" x-text="areaTugas">Area Bawah (Situ Danau • LB-01 s/d LB-05)</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-[#E5E2DB]/70">
                    <span class="flex items-center gap-1.5 text-[#1C1C18]">
                        <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="shiftInfo">Shift Siang (12:00 - 18:00 WIB)</span>
                    </span>
                    <span class="px-2 py-0.5 rounded bg-[#FFDEA1]/50 text-[#7A5900] text-[10px] font-bold" x-text="shiftRemaining">Sisa 3 Jam 28 Mnt</span>
                </div>
            </div>

            <div class="bg-[#F0EEE7] rounded-xl px-3 py-2 flex items-center justify-between text-xs relative z-10">
                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-[#1C1C18]">
                    <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                    <span x-text="posInfo">POS #02 • MQTT Mesh Synced</span>
                </div>

                <div class="flex items-center gap-1 text-[10px] font-bold text-[#00341A]" x-text="signalStrength">-52 dBm</div>
            </div>
        </div>

        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-[#1C1C18]">Kinerja Shift Hari Ini</h3>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#7A5900]">
                    <svg class="w-3 h-3 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Real-time
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#0B4D2B]/10 text-[#0B4D2B] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" /></svg>
                        </div>
                        <span class="px-1.5 py-0.5 rounded bg-[#B1F1C2]/50 text-[#11512F] text-[10px] font-bold" x-text="pesananDiantarTrend">+6 Avg</span>
                    </div>
                    <div>
                        <span class="font-bold text-2xl text-[#1C1C18] block leading-none" x-text="pesananDiantar">24</span>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Pesanan Diantar</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#7A5900]/10 text-[#7A5900] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <span class="px-1.5 py-0.5 rounded bg-[#FFDEA1] text-[#261900] text-[10px] font-bold" x-text="responStatus">Sangat Baik</span>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1 leading-none">
                            <span class="font-bold text-2xl text-[#00341A]" x-text="avgRespon">1.8</span>
                            <span class="text-xs text-[#404941]">Menit</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Respon Sensor Meja</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#F0EEE7] text-[#404941] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#00341A]" x-text="zeroLoss">100% Zero-Loss</span>
                    </div>
                    <div>
                        <span class="font-bold text-2xl text-[#1C1C18] block leading-none" x-text="panggilanSelesai">14</span>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Panggilan Selesai</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#FECE66]/30 text-[#7A5900] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941]" x-text="emenuSource">e-Menu Tamu</span>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1 leading-none">
                            <span class="font-bold text-2xl text-[#7A5900]" x-text="kepuasanTamu">4.9</span>
                            <span class="text-xs text-[#404941]">/5.0</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Kepuasan Tamu Saung</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <h3 class="font-bold text-base text-[#1C1C18]">Checklist Serah Terima Shift</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold" x-text="completedChecklistCount + '/4 Selesai'">2/4 Selesai</span>
            </div>

            <p class="text-xs text-[#404941] leading-relaxed">Lengkapi verifikasi operasional sebelum menekan tombol ganti shift sore.</p>

            <div class="space-y-2.5 pt-1">
                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkCleanliness" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Cek kebersihan 5 Saung Tepi Danau</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">Area Saung LB-01 hingga LB-05 bersih dari sampah & piring kotor.</span>
                    </div>
                </label>

                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkSensors" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Pastikan sensor sentuh & ESP32 normal</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">LED indikator panggil tamu hijau/standby tanpa alert error.</span>
                    </div>
                </label>

                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkHandover" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Serah terima pending pesanan ke Runner Sore</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">Konfirmasi tiket meja yang masih menunggu hidangan dapur.</span>
                    </div>
                </label>

                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkDock" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Handover Mobile POS & Charger Dock</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">Fisik unit bersih dan baterai terisi di atas 75%.</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="font-bold text-base text-[#1C1C18]">Log Aktivitas Terakhir</h3>
                </div>
                <button @click="triggerToast('Menampilkan seluruh riwayat log aktivitas shift...')" class="text-[10px] font-bold text-[#7A5900] hover:underline cursor-pointer">Lihat Semua</button>
            </div>

            <div class="space-y-3 relative pl-6 border-l-2 border-[#EBE8E1] ml-2">
                <template x-for="(log, index) in activityLogs" :key="index">
                    <div class="relative bg-[#F6F3EC] rounded-xl p-3 space-y-1">
                        <span class="w-2.5 h-2.5 rounded-full" :class="log.dotColor" style="position: absolute; left: -31px; top: 3px;"></span>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[10px]" :class="log.timeColor" x-text="log.time"></span>
                            <span class="px-1.5 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-bold" x-text="log.type"></span>
                        </div>
                        <p class="font-semibold text-xs text-[#1C1C18]" x-text="log.action"></p>
                        <span class="text-[10px] font-bold text-[#404941] block" x-text="log.detail"></span>
                    </div>
                </template>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <h3 class="font-bold text-base text-[#1C1C18]">Pengaturan Suara & Notifikasi Getar</h3>
            </div>

            <div class="space-y-3 pt-1">
                <div class="bg-[#F6F3EC] rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#00341A] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-tight">Bunyi Panggilan Meja</span>
                            <span class="text-xs text-[#404941] block leading-tight mt-0.5">Volume maksimal saat bel sensor ditekan</span>
                        </div>
                    </div>
                    <button @click="soundAlert = !soundAlert; triggerToast(soundAlert ? 'Bunyi panggilan aktif.' : 'Bunyi panggilan senyap.')" type="button" :class="soundAlert ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"><span :class="soundAlert ? 'translate-x-5' : 'translate-x-0.5'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span></button>
                </div>

                <div class="bg-[#F6F3EC] rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#7A5900] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-tight">Notifikasi KDS Dapur Siap</span>
                            <span class="text-xs text-[#404941] block leading-tight mt-0.5">Getar berulang saat pesanan siap di pick-up</span>
                        </div>
                    </div>
                    <button @click="vibrateAlert = !vibrateAlert; triggerToast(vibrateAlert ? 'Getar KDS aktif.' : 'Getar KDS nonaktif.')" type="button" :class="vibrateAlert ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"><span :class="vibrateAlert ? 'translate-x-5' : 'translate-x-0.5'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span></button>
                </div>

                <div class="bg-[#F6F3EC] rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#404941] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-tight">Mode Hemat Luar Ruang</span>
                            <span class="text-xs text-[#404941] block leading-tight mt-0.5">Redupkan layar di area saung terbuka</span>
                        </div>
                    </div>
                    <button @click="powerSave = !powerSave; triggerToast(powerSave ? 'Mode hemat baterai aktif.' : 'Mode normal aktif.')" type="button" :class="powerSave ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"><span :class="powerSave ? 'translate-x-5' : 'translate-x-0.5'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span></button>
                </div>
            </div>
        </div>

        <div class="space-y-2.5 pt-2">
            <button @click="startHandover()" class="w-full h-14 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-[#FCF9F2] font-bold text-sm shadow-lg flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                <svg class="w-4 h-4 text-[#FECE66]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Mulai Serah Terima Shift Sore</span>
            </button>

            <button @click="showPinModal = true" class="w-full h-12 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] font-semibold text-sm flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                <svg class="w-4 h-4 text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar / Ganti Akun PIN</span>
            </button>
        </div>

    </div>

    <div x-show="showHandoverModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showHandoverModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span><h3 class="font-bold text-base text-[#1C1C18]">Konfirmasi Serah Terima Shift</h3></div><button @click="showHandoverModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button></div>
            <p class="text-xs text-[#404941] leading-relaxed">Anda akan menyerahkan saung aktif ke <strong>Runner Sore (18:00–23:00 WIB)</strong>. Total <span x-text="pesananDiantar">24</span> pesanan telah selesai diantar.</p>
            <div class="space-y-2">
                <button @click="confirmHandover()" class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">Konfirmasi Serah Terima</button>
                <button @click="showHandoverModal = false" class="w-full py-2.5 bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] font-semibold text-xs rounded-xl">Batal</button>
            </div>
        </div>
    </div>

    <div x-show="showPinModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showPinModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-[#BA1A1A]"></span><h3 class="font-bold text-base text-[#1C1C18]">Kunci Layar / Ganti PIN</h3></div><button @click="showPinModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button></div>
            <p class="text-xs text-[#404941]">Masukkan 4 digit PIN pelayan untuk otorisasi akses:</p>
            <div class="flex justify-center gap-3 py-2"><input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]"><input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]"><input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]"><input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]"></div>
            <button @click="showPinModal = false; triggerToast('Sesi pelayan terkunci.')" class="w-full py-2.5 bg-[#BA1A1A] hover:bg-[#93000A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">Kunci Perangkat</button>
        </div>
    </div>

    <div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-20 left-4 right-4 z-50 max-w-[390px] mx-auto">
        <div class="bg-[#31312C] text-[#F3F0EA] rounded-xl px-4 py-3 flex items-center gap-3 shadow-2xl border border-white/10"><svg class="w-5 h-5 text-[#FFDEA1] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-xs font-medium leading-snug flex-1" x-text="toastMessage">Notifikasi berhasil diproses.</span></div>
    </div>

</div>

<script>
    function pelayanTabShift() {
        return {
            checkCleanliness: false,
            checkSensors: false,
            checkHandover: false,
            checkDock: false,
            soundAlert: true,
            vibrateAlert: true,
            powerSave: false,
            showHandoverModal: false,
            showPinModal: false,
            toastMessage: '',
            showToast: false,

            runnerName: '',
            staffId: '',
            role: '',
            areaTugas: '',
            shiftInfo: '',
            shiftRemaining: '',
            posInfo: '',
            signalStrength: '',
            pesananDiantar: 0,
            pesananDiantarTrend: '+6 Avg',
            avgRespon: 1.8,
            responStatus: 'Sangat Baik',
            panggilanSelesai: 0,
            zeroLoss: '100% Zero-Loss',
            kepuasanTamu: 4.9,
            emenuSource: 'e-Menu Tamu',

            get completedChecklistCount() {
                return (this.checkCleanliness ? 1 : 0) + (this.checkSensors ? 1 : 0) + (this.checkHandover ? 1 : 0) + (this.checkDock ? 1 : 0);
            },

            activityLogs: [
                { time: '14:28 WIB • Selesai', timeColor: 'text-[#00341A]', type: 'Antar Makanan', action: 'Antar Gurame Bakar & Nasi Liwet Kastrol', detail: 'Tujuan: Saung LB-02 (Keluarga Bpk. Hendra)', dotColor: 'bg-[#0B4D2B] ring-4 ring-white absolute -left-[31px] top-3' },
                { time: '14:15 WIB • Respon Cepat (1.2m)', timeColor: 'text-[#7A5900]', type: 'Sensor Meja', action: 'Panggilan TTP223 Saung LB-02', detail: 'Permintaan: Tambah sendok & sambal terasi dadak', dotColor: 'bg-[#7A5900] ring-4 ring-white absolute -left-[31px] top-3' },
                { time: '13:50 WIB • Selesai', timeColor: 'text-[#00341A]', type: 'Antar Minuman', action: 'Antar 4x Es Teh Manis Ciwidey', detail: 'Tujuan: Saung LB-05', dotColor: 'bg-[#0B4D2B] ring-4 ring-white absolute -left-[31px] top-3' },
                { time: '13:10 WIB • Meja Bersih', timeColor: 'text-[#404941]', type: 'Status Meja', action: 'Konfirmasi Siap Pakai Saung LB-03', detail: 'Tamu sebelumnya selesai checkout kasir', dotColor: 'bg-[#E5E2DB] ring-4 ring-white absolute -left-[31px] top-3' },
            ],

            init() {
                this.runnerName = '{{ auth()->user()->nama }}';
                this.staffId = 'STF-{{ auth()->user()->id }}';
                this.role = 'Pelayan / Runner Lesehan Utama';
                this.areaTugas = 'Area Bawah (Situ Danau • LB-01 s/d LB-05)';
                this.shiftInfo = 'Shift Siang (12:00 - 18:00 WIB)';
                this.shiftRemaining = 'Sisa {{ $shiftRemaining ?? "3 Jam 28 Mnt" }}';
                this.posInfo = 'POS #02 • MQTT Mesh Synced';
                this.signalStrength = '-52 dBm';
                this.pesananDiantar = {{ $pesananDiantar ?? 24 }};
                this.avgRespon = {{ $avgRespon ?? 1.8 }};
                this.panggilanSelesai = {{ $panggilanSelesai ?? 14 }};
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                playChime('success');
                setTimeout(() => this.showToast = false, 3500);
            },

            startHandover() {
                if (this.completedChecklistCount < 4) {
                    alert('Harap lengkapi semua 4 checklist verifikasi operasional sebelum serah terima shift.');
                    return;
                }
                this.showHandoverModal = true;
            },

            confirmHandover() {
                this.showHandoverModal = false;
                this.triggerToast('Serah terima shift ke Runner Sore (Kang Dadan) berhasil dicatat.');
            }
        }
    }
</script>
@endsection