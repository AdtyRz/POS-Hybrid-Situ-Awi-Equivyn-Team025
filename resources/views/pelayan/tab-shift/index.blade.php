@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Shift) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="{
    // Checklist verification state
    checkCleanliness: true,
    checkSensors: true,
    checkHandover: false,
    checkDock: false,

    // Settings switches
    soundAlert: true,
    vibrateAlert: true,
    powerSave: false,

    // Modals & Feedback
    showHandoverModal: false,
    showPinModal: false,
    toastMessage: '',
    showToast: false,

    get completedChecklistCount() {
        return (this.checkCleanliness ? 1 : 0) +
               (this.checkSensors ? 1 : 0) +
               (this.checkHandover ? 1 : 0) +
               (this.checkDock ? 1 : 0);
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
}" class="relative flex flex-col min-h-screen">

    <!-- ========================================================
         STICKY TOP HEADER (64px)
         ======================================================== -->
    <header class="sticky top-0 z-40 bg-[#FCF9F2]/90 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-[#E5E2DB]/60 px-4 py-2.5 flex items-center justify-between">
        <!-- Logo & Runner Title -->
        <div class="flex items-center gap-3">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-widest uppercase leading-none">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Shift</h1>
            </div>
        </div>

        <!-- Right: Status Badge & Profile Avatar -->
        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <!-- Profile Avatar (KA) with Green Tint -->
            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-xs flex items-center justify-center shadow-xs">
                KA
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="p-4 space-y-4 flex-1">

        <!-- ========================================================
             SECTION 1: WAITER PROFILE & OPERATIONAL NODE CARD
             ======================================================== -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] relative overflow-hidden space-y-3">
            <!-- Decorative Subtle Gold Glow in Corner -->
            <div class="absolute -right-10 -top-10 w-36 h-36 bg-gradient-to-br from-[#FECE66]/20 to-transparent rounded-full blur-xl pointer-events-none"></div>

            <!-- Profile Info Row -->
            <div class="flex items-center gap-3.5 relative z-10">
                <!-- Large Avatar Badge with Duty Pip -->
                <div class="relative flex-shrink-0">
                    <div class="w-16 h-16 rounded-2xl bg-[#0B4D2B] text-[#B1F1C2] font-bold text-2xl flex items-center justify-center shadow-md">
                        KA
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[#0B4D2B] border border-white text-white flex items-center justify-center shadow-xs">
                        <svg class="w-3 h-3 text-[#FECE66]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <!-- Runner Identity -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-[#7A5900] tracking-wider uppercase">ID: STF-006</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#B1F1C2]/50 text-[#11512F] text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#11512F] animate-pulse"></span>
                            Aktif Bertugas
                        </span>
                    </div>
                    <h2 class="font-bold text-xl text-[#1C1C18] leading-tight truncate mt-0.5">
                        Kang Asep Supriatna
                    </h2>
                    <span class="text-xs text-[#404941] block truncate mt-0.5">
                        Pelayan / Runner Lesehan Utama
                    </span>
                </div>
            </div>

            <!-- Wilayah Tugas Box -->
            <div class="bg-[#F6F3EC] rounded-xl p-3 space-y-2 relative z-10 border border-[#E5E2DB]/60">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-[#7A5900]/10 text-[#7A5900] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider block">WILAYAH TUGAS</span>
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">
                            Area Bawah (Situ Danau • LB-01 s/d LB-05)
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-[#E5E2DB]/70">
                    <span class="flex items-center gap-1.5 text-[#1C1C18]">
                        <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Shift Siang (12:00 - 18:00 WIB)
                    </span>
                    <span class="px-2 py-0.5 rounded bg-[#FFDEA1]/50 text-[#7A5900] text-[10px] font-bold">
                        Sisa 3 Jam 28 Mnt
                    </span>
                </div>
            </div>

            <!-- Strip Node Hardware Connection Status -->
            <div class="bg-[#F0EEE7] rounded-xl px-3 py-2 flex items-center justify-between text-xs relative z-10">
                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-[#1C1C18]">
                    <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    <span>POS #02 • MQTT Mesh Synced</span>
                </div>

                <div class="flex items-center gap-1 text-[10px] font-bold text-[#00341A]">
                    <svg class="w-3 h-3 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                    <span>-52 dBm</span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 2: SHIFT PERFORMANCE INDICATORS (KPI 2x2 Grid)
             ======================================================== -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-[#1C1C18]">Kinerja Shift Hari Ini</h3>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#7A5900]">
                    <svg class="w-3 h-3 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Real-time
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- KPI 1: Pesanan Diantar (24) -->
                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#0B4D2B]/10 text-[#0B4D2B] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="px-1.5 py-0.5 rounded bg-[#B1F1C2]/50 text-[#11512F] text-[10px] font-bold">
                            +6 Avg
                        </span>
                    </div>
                    <div>
                        <span class="font-bold text-2xl text-[#1C1C18] block leading-none">24</span>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Pesanan Diantar</span>
                    </div>
                </div>

                <!-- KPI 2: Respon Sensor Meja (1.8 Menit) -->
                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#7A5900]/10 text-[#7A5900] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="px-1.5 py-0.5 rounded bg-[#FFDEA1] text-[#261900] text-[10px] font-bold">
                            Sangat Baik
                        </span>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1 leading-none">
                            <span class="font-bold text-2xl text-[#00341A]">1.8</span>
                            <span class="text-xs text-[#404941]">Menit</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Respon Sensor Meja</span>
                    </div>
                </div>

                <!-- KPI 3: Panggilan Selesai (14) -->
                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#F0EEE7] text-[#404941] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#00341A]">
                            100% Zero-Loss
                        </span>
                    </div>
                    <div>
                        <span class="font-bold text-2xl text-[#1C1C18] block leading-none">14</span>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Panggilan Selesai</span>
                    </div>
                </div>

                <!-- KPI 4: Kepuasan Tamu (4.9 / 5.0) -->
                <div class="bg-white rounded-xl p-3 shadow-2xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#FECE66]/30 text-[#7A5900] flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941]">
                            e-Menu Tamu
                        </span>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1 leading-none">
                            <span class="font-bold text-2xl text-[#7A5900]">4.9</span>
                            <span class="text-xs text-[#404941]">/5.0</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Kepuasan Tamu Saung</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 3: HANDOVER CHECKLIST SECTION
             ======================================================== -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18]">Checklist Serah Terima Shift</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold"
                      x-text="`${completedChecklistCount}/4 Selesai`">
                    2/4 Selesai
                </span>
            </div>

            <p class="text-xs text-[#404941] leading-relaxed">
                Lengkapi verifikasi operasional sebelum menekan tombol ganti shift sore.
            </p>

            <!-- Checklist Items -->
            <div class="space-y-2.5 pt-1">
                <!-- Item 1: Cek kebersihan -->
                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkCleanliness" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Cek kebersihan 5 Saung Tepi Danau</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">Area Saung LB-01 hingga LB-05 bersih dari sampah & piring kotor.</span>
                    </div>
                </label>

                <!-- Item 2: Sensor sentuh -->
                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkSensors" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Pastikan sensor sentuh & ESP32 normal</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">LED indikator panggil tamu hijau/standby tanpa alert error.</span>
                    </div>
                </label>

                <!-- Item 3: Serah terima pending -->
                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkHandover" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Serah terima pending pesanan ke Kang Dadan</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">Konfirmasi tiket meja yang masih menunggu hidangan dapur.</span>
                    </div>
                </label>

                <!-- Item 4: Handover Mobile POS -->
                <label class="bg-[#F6F3EC] rounded-xl p-3 flex items-start gap-3 cursor-pointer border border-transparent hover:border-[#0B4D2B]/30 transition-all select-none">
                    <input type="checkbox" x-model="checkDock" class="mt-0.5 w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB]">
                    <div class="flex-1 min-w-0">
                        <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Handover Mobile POS & Charger Dock</span>
                        <span class="text-xs text-[#404941] block leading-relaxed mt-0.5">Fisik unit bersih dan baterai terisi di atas 75%.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- ========================================================
             SECTION 4: ACTIVITY & TASK LOGS (Log Aktivitas Terakhir)
             ======================================================== -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18]">Log Aktivitas Terakhir</h3>
                </div>
                <button @click="triggerToast('Menampilkan seluruh riwayat log aktivitas shift...')" class="text-[10px] font-bold text-[#7A5900] hover:underline cursor-pointer">
                    Lihat Semua
                </button>
            </div>

            <!-- Timeline Items -->
            <div class="space-y-3 relative pl-6 border-l-2 border-[#EBE8E1] ml-2">
                <!-- Log 1 -->
                <div class="relative bg-[#F6F3EC] rounded-xl p-3 space-y-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0B4D2B] ring-4 ring-white absolute -left-[31px] top-3"></span>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-[10px] text-[#00341A]">14:28 WIB • Selesai</span>
                        <span class="px-1.5 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-bold">Antar Makanan</span>
                    </div>
                    <p class="font-semibold text-xs text-[#1C1C18]">Antar Gurame Bakar & Nasi Liwet Kastrol</p>
                    <span class="text-[10px] font-bold text-[#404941] block">Tujuan: Saung LB-02 (Keluarga Bpk. Hendra)</span>
                </div>

                <!-- Log 2 -->
                <div class="relative bg-[#F6F3EC] rounded-xl p-3 space-y-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900] ring-4 ring-white absolute -left-[31px] top-3"></span>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-[10px] text-[#7A5900]">14:15 WIB • Respon Cepat (1.2m)</span>
                        <span class="px-1.5 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-bold">Sensor Meja</span>
                    </div>
                    <p class="font-semibold text-xs text-[#1C1C18]">Panggilan TTP223 Saung LB-02</p>
                    <span class="text-[10px] font-bold text-[#404941] block">Permintaan: Tambah sendok & sambal terasi dadak</span>
                </div>

                <!-- Log 3 -->
                <div class="relative bg-[#F6F3EC] rounded-xl p-3 space-y-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0B4D2B] ring-4 ring-white absolute -left-[31px] top-3"></span>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-[10px] text-[#00341A]">13:50 WIB • Selesai</span>
                        <span class="px-1.5 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-bold">Antar Minuman</span>
                    </div>
                    <p class="font-semibold text-xs text-[#1C1C18]">Antar 4x Es Teh Manis Ciwidey</p>
                    <span class="text-[10px] font-bold text-[#404941] block">Tujuan: Saung LB-05</span>
                </div>

                <!-- Log 4 -->
                <div class="relative bg-[#F6F3EC] rounded-xl p-3 space-y-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#E5E2DB] ring-4 ring-white absolute -left-[31px] top-3"></span>
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-[10px] text-[#404941]">13:10 WIB • Meja Bersih</span>
                        <span class="px-1.5 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-bold">Status Meja</span>
                    </div>
                    <p class="font-semibold text-xs text-[#1C1C18]">Konfirmasi Siap Pakai Saung LB-03</p>
                    <span class="text-[10px] font-bold text-[#404941] block">Tamu sebelumnya selesai checkout kasir</span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 5: HARDWARE & NOTIFICATION SETTINGS DRAWER CARD
             ======================================================== -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#F0EEE7] space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <h3 class="font-bold text-base text-[#1C1C18]">Pengaturan Suara & Notifikasi Getar</h3>
            </div>

            <div class="space-y-3 pt-1">
                <!-- Switch 1: Bunyi Panggilan -->
                <div class="bg-[#F6F3EC] rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#00341A] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-tight">Bunyi Panggilan Meja</span>
                            <span class="text-xs text-[#404941] block leading-tight mt-0.5">Volume maksimal saat bel sensor ditekan</span>
                        </div>
                    </div>
                    <button 
                        @click="soundAlert = !soundAlert; triggerToast(soundAlert ? 'Bunyi panggilan aktif.' : 'Bunyi panggilan senyap.')"
                        type="button" 
                        :class="soundAlert ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                        <span 
                            :class="soundAlert ? 'translate-x-5' : 'translate-x-0.5'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                    </button>
                </div>

                <!-- Switch 2: Notifikasi Getar KDS -->
                <div class="bg-[#F6F3EC] rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#7A5900] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-tight">Notifikasi KDS Dapur Siap</span>
                            <span class="text-xs text-[#404941] block leading-tight mt-0.5">Getar berulang saat pesanan siap di pick-up</span>
                        </div>
                    </div>
                    <button 
                        @click="vibrateAlert = !vibrateAlert; triggerToast(vibrateAlert ? 'Getar KDS aktif.' : 'Getar KDS nonaktif.')"
                        type="button" 
                        :class="vibrateAlert ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                        <span 
                            :class="vibrateAlert ? 'translate-x-5' : 'translate-x-0.5'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                    </button>
                </div>

                <!-- Switch 3: Mode Hemat Luar Ruang -->
                <div class="bg-[#F6F3EC] rounded-xl p-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-[#404941] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-tight">Mode Hemat Luar Ruang</span>
                            <span class="text-xs text-[#404941] block leading-tight mt-0.5">Redupkan layar di area saung terbuka</span>
                        </div>
                    </div>
                    <button 
                        @click="powerSave = !powerSave; triggerToast(powerSave ? 'Mode hemat baterai aktif.' : 'Mode normal aktif.')"
                        type="button" 
                        :class="powerSave ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                        <span 
                            :class="powerSave ? 'translate-x-5' : 'translate-x-0.5'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 6: SHIFT ACTION TRAY (Bottom Buttons)
             ======================================================== -->
        <div class="space-y-2.5 pt-2">
            <!-- Button 1: Mulai Serah Terima Shift -->
            <button 
                @click="startHandover()"
                class="w-full h-14 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-[#FCF9F2] font-bold text-sm shadow-lg flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                <svg class="w-4 h-4 text-[#FECE66]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Mulai Serah Terima Shift Sore</span>
            </button>

            <!-- Button 2: Keluar / Ganti Akun PIN -->
            <button 
                @click="showPinModal = true"
                class="w-full h-12 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] font-semibold text-sm flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                <svg class="w-4 h-4 text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar / Ganti Akun PIN</span>
            </button>
        </div>

    </div>

    <!-- ========================================================
         MODAL 1: SERAH TERIMA SHIFT KONFIRMASI
         ======================================================== -->
    <div x-show="showHandoverModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showHandoverModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Konfirmasi Serah Terima Shift</h3>
                </div>
                <button @click="showHandoverModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-[#404941] leading-relaxed">
                Anda akan menyerahkan 5 saung aktif ke <strong>Kang Dadan (Shift Sore 18:00–23:00 WIB)</strong>. Total 24 pesanan telah selesai diantar.
            </p>

            <div class="space-y-2">
                <button 
                    @click="confirmHandover()"
                    class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">
                    Konfirmasi Serah Terima
                </button>
                <button 
                    @click="showHandoverModal = false"
                    class="w-full py-2.5 bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] font-semibold text-xs rounded-xl">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================
         MODAL 2: GANTI AKUN / PIN DIALOG
         ======================================================== -->
    <div x-show="showPinModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showPinModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#BA1A1A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Kunci Layar / Ganti PIN</h3>
                </div>
                <button @click="showPinModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-[#404941]">Masukkan 4 digit PIN pelayan untuk otorisasi akses:</p>

            <div class="flex justify-center gap-3 py-2">
                <input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]">
                <input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]">
                <input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]">
                <input type="password" maxlength="1" class="w-12 h-12 text-center text-xl font-bold bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl focus:border-[#00341A]">
            </div>

            <button 
                @click="showPinModal = false; triggerToast('Sesi pelayan terkunci.')"
                class="w-full py-2.5 bg-[#BA1A1A] hover:bg-[#93000A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">
                Kunci Perangkat
            </button>
        </div>
    </div>

    <!-- ========================================================
         TOAST FEEDBACK NOTIFICATION
         ======================================================== -->
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
            <span class="text-xs font-medium leading-snug flex-1" x-text="toastMessage">
                Notifikasi berhasil diproses.
            </span>
        </div>
    </div>

</div>
@endsection

