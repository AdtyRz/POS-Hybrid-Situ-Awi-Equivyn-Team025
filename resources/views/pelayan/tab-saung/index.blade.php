@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Saung) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="{
    // Filter Cluster: 'all', 'danau', 'balong', 'kayu'
    selectedCluster: 'all',
    toastMessage: '',
    showToast: false,

    // Dynamic Saung 02 Alert State
    saung02Calling: true,
    buzzerActive: true,

    // Modals
    showQrModal: false,
    showNewOrderModal: false,
    selectedSaungDetail: null,

    triggerToast(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        playChime('success');
        setTimeout(() => this.showToast = false, 3500);
    },

    respondSaung02() {
        this.saung02Calling = false;
        this.triggerToast('Anda merespons panggilan Saung 02 (Teratai 2). Menuju lokasi...');
    },

    muteSaung02Buzzer() {
        this.buzzerActive = false;
        this.triggerToast('Sinyal MQTT terkirim: Buzzer Saung 02 dimatikan.');
    },

    openTable(saungId) {
        this.triggerToast(`Sesi meja ${saungId} berhasil dibuka. QR dinamis diaktifkan.`);
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
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Saung</h1>
            </div>
        </div>

        <!-- Right: Status Badge & Profile Avatar -->
        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <!-- Profile Avatar (KA) with Forest Green Badge -->
            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-xs flex items-center justify-center shadow-xs">
                KA
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="p-4 space-y-4 flex-1">

        <!-- ========================================================
             SECTION 1: RUNNER CONTEXT & IOT MESH STATUS HEADER CARD
             ======================================================== -->
        <div class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/80 space-y-3">
            <!-- Top Row: Avatar KA, Name, Shift, and QR Scan Button -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-11 h-11 rounded-full bg-[#0B4D2B] text-white font-bold text-base flex items-center justify-center shadow-xs">
                            KA
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-[#B1F1C2] ring-2 ring-[#F6F3EC]"></span>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 leading-tight">
                            <span class="font-bold text-base text-[#1C1C18]">Kang Asep</span>
                            <span class="px-1.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold">
                                STF-006
                            </span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-[#404941] mt-0.5">
                            <svg class="w-3 h-3 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Shift Siang (12:00 – 18:00 WIB)</span>
                        </div>
                    </div>
                </div>

                <!-- Quick QR Scanner Action Button -->
                <button 
                    @click="showQrModal = true"
                    title="Pindai QR Meja"
                    class="w-10 h-10 rounded-xl bg-white text-[#0B4D2B] shadow-xs border border-[#E5E2DB]/60 flex items-center justify-center active:scale-95 transition-transform cursor-pointer">
                    <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </button>
            </div>

            <!-- Active Area & IoT Mesh Status Strip -->
            <div class="bg-[#F0EEE7] rounded-xl px-3 py-2 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-[#1C1C18] font-semibold">
                    <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Tepi Danau (LB-01 – LB-05)</span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white text-[#404941] text-[10px] font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                    <span>IoT Mesh 11/11 (-52dBm)</span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 2: OCCUPANCY OVERVIEW METRICS (4 Mini Cards)
             ======================================================== -->
        <div class="grid grid-cols-4 gap-2">
            <!-- Total (64%) -->
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-center shadow-xs border border-[#E5E2DB]/60 flex flex-col items-center justify-center">
                <span class="font-bold text-xl text-[#1C1C18] leading-tight">11</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1">Total (64%)</span>
            </div>

            <!-- Terisi -->
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-center shadow-xs border border-[#E5E2DB]/60 flex flex-col items-center justify-center">
                <span class="font-bold text-xl text-[#0B4D2B] leading-tight">7</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1">Terisi</span>
            </div>

            <!-- Tersedia -->
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-center shadow-xs border border-[#E5E2DB]/60 flex flex-col items-center justify-center">
                <span class="font-bold text-xl text-[#7A5900] leading-tight">3</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1">Tersedia</span>
            </div>

            <!-- Panggilan (Alert Active) -->
            <div class="bg-[#FFDAD6] rounded-xl p-2.5 text-center shadow-xs border border-[#BA1A1A]/30 flex flex-col items-center justify-center relative">
                <span class="font-bold text-xl text-[#93000A] leading-tight" x-text="saung02Calling ? '1' : '0'">1</span>
                <span class="text-[10px] font-bold text-[#93000A] uppercase tracking-tight mt-1">Panggilan</span>
                <template x-if="saung02Calling">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#BA1A1A] animate-ping absolute -top-1 -right-1"></span>
                </template>
            </div>
        </div>

        <!-- ========================================================
             SECTION 3: CLUSTER FILTER CHIPS (Horizontal Scrollable)
             ======================================================== -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none -mx-4 px-4">
            <!-- Semua Saung (11) -->
            <button 
                @click="selectedCluster = 'all'"
                :class="selectedCluster === 'all' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Semua Saung (11)</span>
            </button>

            <!-- Cluster Danau (5) -->
            <button 
                @click="selectedCluster = 'danau'"
                :class="selectedCluster === 'danau' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>
                <span>Cluster Danau (5)</span>
            </button>

            <!-- Cluster Balong (2) -->
            <button 
                @click="selectedCluster = 'balong'"
                :class="selectedCluster === 'balong' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span>Cluster Balong (2)</span>
            </button>

            <!-- Cluster Meja (4) -->
            <button 
                @click="selectedCluster = 'kayu'"
                :class="selectedCluster === 'kayu' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0v-4m14 4v-4M4 6h16a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                <span>Cluster Meja (4)</span>
            </button>
        </div>

        <!-- ========================================================
             SECTION 4: CLUSTER A • TEPI DANAU (5 Saung Lesehan)
             ======================================================== -->
        <div x-show="selectedCluster === 'all' || selectedCluster === 'danau'" class="space-y-3 pt-1">
            <!-- Cluster A Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h2 class="font-bold text-base text-[#1C1C18]">Cluster A • Tepi Danau</h2>
                    <span class="px-2 py-0.5 rounded-md bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                        Area Kamu
                    </span>
                </div>
                <span class="text-xs text-[#404941]">5 Saung</span>
            </div>

            <!-- ----------------------------------------------------
                 SAUNG 1: LB-02 Teratai 2 (CRITICAL CALL ALERT)
                 ---------------------------------------------------- -->
            <div class="bg-white rounded-2xl shadow-md border border-[#BA1A1A]/30 overflow-hidden relative"
                 :class="{ 'ring-1 ring-[#BA1A1A]': saung02Calling }">
                <!-- Red Top Accent Bar -->
                <div class="h-1.5 bg-[#BA1A1A] w-full" x-show="saung02Calling"></div>

                <div class="p-3.5 space-y-3">
                    <!-- Top Info Row -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-[#FFDAD6] text-[#93000A] flex flex-col items-center justify-center flex-shrink-0">
                                <span class="text-[10px] font-bold uppercase leading-none">SAUNG</span>
                                <span class="text-xl font-bold leading-none mt-0.5">02</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-base text-[#1C1C18]">LB-02 Teratai 2</h3>
                                    <template x-if="saung02Calling">
                                        <span class="px-2 py-0.5 rounded-full bg-[#BA1A1A] text-white text-[10px] font-bold animate-pulse">
                                            BELL ON
                                        </span>
                                    </template>
                                </div>
                                <span class="text-xs text-[#404941] block mt-0.5">
                                    Bill #SW-8819 • 4 Tamu • Rp 144.100
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 text-[10px] font-bold text-[#BA1A1A]">
                            <svg class="w-3.5 h-3.5 text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>3m lalu</span>
                        </div>
                    </div>

                    <!-- Request Note Banner -->
                    <div class="bg-[#FFDAD6]/60 rounded-xl p-2.5 flex items-start gap-2 border border-[#BA1A1A]/20">
                        <svg class="w-4 h-4 text-[#BA1A1A] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <div>
                            <span class="text-[10px] font-bold text-[#93000A] uppercase tracking-wider block">PERMINTAAN TAMU (SENSOR TTP223)</span>
                            <p class="text-sm font-semibold text-[#93000A] mt-0.5">"Minta sendok ekstra & sambal terasi dadak"</p>
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="flex items-center gap-2 pt-1">
                        <button 
                            @click="respondSaung02()"
                            class="flex-1 h-12 rounded-xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm shadow-xs flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                            <svg class="w-4 h-4 text-[#765600]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Respon & Temui</span>
                        </button>

                        <button 
                            @click="muteSaung02Buzzer()"
                            :disabled="!buzzerActive"
                            class="flex-1 h-12 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] font-semibold text-sm flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer disabled:opacity-50">
                            <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                            <span x-text="buzzerActive ? 'Matikan Buzzer' : 'Buzzer Mati'">Matikan Buzzer</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ----------------------------------------------------
                 SAUNG 2: LB-05 Dermaga Ujung (Siap Antar Bar Pass)
                 ---------------------------------------------------- -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#F0EEE7] space-y-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-[#FFDEA1] text-[#261900] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase leading-none">SAUNG</span>
                            <span class="text-xl font-bold leading-none mt-0.5">05</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18]">LB-05 Dermaga Ujung</h3>
                                <span class="px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[#5C4300] text-[10px] font-bold">
                                    Siap Antar
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] block mt-0.5">Bar Pass • Siap 1 mnt lalu</span>
                        </div>
                    </div>

                    <div class="w-8 h-8 rounded-full bg-[#F0EEE7] text-[#7A5900] flex items-center justify-center shadow-2xs">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                </div>

                <!-- Order Content Strip -->
                <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex items-center justify-between text-xs">
                    <span class="font-semibold text-sm text-[#1C1C18] flex items-center gap-1.5">
                        🍹 4x Es Teh Jumbo, 1x Kelapa Muda
                    </span>
                    <span class="font-bold text-[10px] text-[#404941]">5 Item</span>
                </div>

                <!-- Action Button -->
                <button 
                    @click="triggerToast('Pesanan minuman Saung 05 diambil dari Bar Counter.')"
                    class="w-full h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all active:scale-98 cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Ambil di Bar & Antar ke Saung</span>
                </button>
            </div>

            <!-- ----------------------------------------------------
                 SAUNG 3: LB-01 Teratai Air (Santap Makan)
                 ---------------------------------------------------- -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#F0EEE7] space-y-2.5">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-[#F0EEE7] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase leading-none">SAUNG</span>
                            <span class="text-xl font-bold leading-none mt-0.5">01</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18]">LB-01 Teratai Air</h3>
                                <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-semibold">
                                    Santap Makan
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] block mt-0.5">Bill #SW-8812 • 4 Tamu • Rp 385.000</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 text-[10px] font-medium text-[#404941]">
                        <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>42m</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-2 border-t border-[#F0EEE7]">
                    <span class="text-[#404941] flex items-center gap-1 text-xs">
                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Puck LCD: "Selamat Menikmati"
                    </span>
                    <button @click="triggerToast('Menampilkan rincian pesanan Saung LB-01...')" class="font-semibold text-xs text-[#00341A] hover:underline cursor-pointer">
                        Lihat Rincian Pesanan
                    </button>
                </div>
            </div>

            <!-- ----------------------------------------------------
                 SAUNG 4: LB-04 Gandasoli (Masak Dapur)
                 ---------------------------------------------------- -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#F0EEE7] space-y-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-[#F0EEE7] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase leading-none">SAUNG</span>
                            <span class="text-xl font-bold leading-none mt-0.5">04</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18]">LB-04 Gandasoli</h3>
                                <span class="px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-semibold">
                                    Masak Dapur
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] block mt-0.5">Tiket KDS #14 • 7 Tamu • Rp 520.000</span>
                        </div>
                    </div>

                    <span class="text-[10px] font-bold text-[#7A5900]">Est. 12 mnt</span>
                </div>

                <!-- Cooking Progress Bar -->
                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center justify-between text-[10px] font-bold text-[#404941]">
                        <span>Gurame Bakar Cobek & Karedok</span>
                        <span>70% Selesai</span>
                    </div>
                    <div class="w-full bg-[#F0EEE7] h-2 rounded-full overflow-hidden">
                        <div class="w-[70%] bg-[#0B4D2B] h-full rounded-full"></div>
                    </div>
                </div>
            </div>

            <!-- ----------------------------------------------------
                 SAUNG 5: LB-03 Bambu Hitam (Tersedia / Siap Pakai)
                 ---------------------------------------------------- -->
            <div class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB] space-y-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-[#E5E2DB] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-[10px] font-bold uppercase leading-none">SAUNG</span>
                            <span class="text-xl font-bold leading-none mt-0.5">03</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-base text-[#1C1C18]">LB-03 Bambu Hitam</h3>
                                <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                                    Tersedia
                                </span>
                            </div>
                            <span class="text-xs text-[#404941] block mt-0.5">Kapasitas 6 Orang • Bersih & Siap</span>
                        </div>
                    </div>

                    <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button 
                        @click="triggerToast('Token QR Meja LB-03 siap discan oleh tamu baru.')"
                        class="flex-1 h-11 rounded-xl bg-white text-[#1C1C18] font-semibold text-xs shadow-xs border border-[#E5E2DB] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>QR Meja Aktif</span>
                    </button>

                    <button 
                        @click="openTable('LB-03')"
                        class="flex-1 h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs shadow-xs flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>Buka Meja Tamu</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 5: CLUSTER B • BALONG INDAH (2 Saung)
             ======================================================== -->
        <div x-show="selectedCluster === 'all' || selectedCluster === 'balong'" class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                    <h2 class="font-bold text-base text-[#1C1C18]">Cluster B • Balong Indah</h2>
                </div>
                <span class="text-xs text-[#404941]">2 Saung</span>
            </div>

            <!-- LA-01 Panorama Balong -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#F0EEE7] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-[#F0EEE7] text-[#1C1C18] flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-[9px] font-bold uppercase leading-none">SAUNG</span>
                        <span class="text-base font-bold leading-none mt-0.5">A01</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-base text-[#1C1C18]">LA-01 Panorama Balong</h4>
                        <span class="text-xs text-[#404941] block mt-0.5">Diantar oleh: Kang Dadan (Runner B)</span>
                    </div>
                </div>

                <span class="px-2.5 py-1 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-medium">
                    Bill #SW-8820
                </span>
            </div>

            <!-- LA-02 Kicau Burung (Booking) -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#F0EEE7] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-[#FFDEA1] text-[#261900] flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-[9px] font-bold uppercase leading-none">SAUNG</span>
                        <span class="text-base font-bold leading-none mt-0.5">A02</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-base text-[#1C1C18]">LA-02 Kicau Burung</h4>
                            <span class="px-2 py-0.5 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold">
                                Booking
                            </span>
                        </div>
                        <span class="text-xs text-[#404941] block mt-0.5">18:30 WIB • Pak H. Dani (8 Tamu)</span>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-[10px] font-bold text-[#7A5900] block">DP Masuk</span>
                    <span class="text-xs font-bold text-[#404941]">Rp 200rb</span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 6: CLUSTER C • MEJA KURSI KAYU (2x2 Grid)
             ======================================================== -->
        <div x-show="selectedCluster === 'all' || selectedCluster === 'kayu'" class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#707971]"></span>
                    <h2 class="font-bold text-base text-[#1C1C18]">Cluster C • Meja Kursi Kayu</h2>
                </div>
                <span class="text-xs text-[#404941]">4 Meja</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- KA-01 -->
                <div class="bg-white rounded-2xl p-3 shadow-xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-01</span>
                        <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-medium inline-block">
                            4 Tamu
                        </span>
                        <span class="text-xs font-semibold text-[#404941] block mt-1">Bill #SW-8815</span>
                    </div>
                </div>

                <!-- KA-02 -->
                <div class="bg-[#F6F3EC] rounded-2xl p-3 shadow-xs border border-[#E5E2DB] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-02</span>
                        <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold inline-block">
                            Tersedia
                        </span>
                        <span class="text-xs font-normal text-[#404941] block mt-1">Kapasitas 4 Org</span>
                    </div>
                </div>

                <!-- KA-03 -->
                <div class="bg-[#F6F3EC] rounded-2xl p-3 shadow-xs border border-[#E5E2DB] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-03</span>
                        <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold inline-block">
                            Tersedia
                        </span>
                        <span class="text-xs font-normal text-[#404941] block mt-1">Kapasitas 4 Org</span>
                    </div>
                </div>

                <!-- KA-04 -->
                <div class="bg-white rounded-2xl p-3 shadow-xs border border-[#F0EEE7] flex flex-col justify-between h-28">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-base text-[#1C1C18]">KA-04</span>
                        <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                    </div>
                    <div>
                        <span class="px-2 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-medium inline-block">
                            8 Tamu
                        </span>
                        <span class="text-xs font-semibold text-[#404941] block mt-1">Bill #SW-8809</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================
             PRIMARY FLOATING ACTION TRIGGER
             ======================================================== -->
        <div class="pt-4">
            <button 
                @click="showNewOrderModal = true"
                class="w-full h-14 rounded-2xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm shadow-lg flex items-center justify-center gap-2.5 transition-all active:scale-98 cursor-pointer">
                <span class="w-7 h-7 rounded-full bg-[#765600] text-[#FECE66] flex items-center justify-center font-bold text-lg">
                    +
                </span>
                <span>Input Pesanan Walk-in / Tambahan</span>
            </button>
        </div>

    </div>

    <!-- ========================================================
         MODAL 1: QR CODE SCANNER SIMULATOR
         ======================================================== -->
    <div x-show="showQrModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showQrModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Pindai Dudukan QR Saung</h3>
                </div>
                <button @click="showQrModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>

            <!-- Camera Viewfinder Mockup -->
            <div class="bg-[#1C1C18] rounded-xl h-48 flex flex-col items-center justify-center relative overflow-hidden border-2 border-dashed border-[#B1F1C2]">
                <div class="w-28 h-28 border-2 border-[#FECE66] rounded-xl relative flex items-center justify-center">
                    <span class="w-full h-0.5 bg-[#FECE66] absolute animate-pulse"></span>
                    <span class="text-3xl text-white">📷</span>
                </div>
                <span class="text-xs text-white/80 mt-3 font-mono">Arahkan ke QR Saung...</span>
            </div>

            <button 
                @click="showQrModal = false; openTable('LB-03')"
                class="w-full py-2.5 bg-[#00341A] hover:bg-[#0B4D2B] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">
                Simulasi Scan Saung LB-03
            </button>
        </div>
    </div>

    <!-- ========================================================
         MODAL 2: INPUT PESANAN WALK-IN MODAL
         ======================================================== -->
    <div x-show="showNewOrderModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showNewOrderModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Input Pesanan Tambahan</h3>
                </div>
                <button @click="showNewOrderModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[#1C1C18] block mb-1">Pilih Saung / Meja:</label>
                    <select class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl p-2.5 font-semibold text-[#1C1C18]">
                        <option>LB-01 Teratai Air (4 Tamu)</option>
                        <option>LB-02 Teratai 2 (4 Tamu)</option>
                        <option>LB-03 Bambu Hitam (Tersedia)</option>
                        <option>LB-04 Gandasoli (7 Tamu)</option>
                        <option>LB-05 Dermaga Ujung</option>
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

            <button 
                @click="showNewOrderModal = false; triggerToast('Pesanan tambahan terkirim ke KDS Dapur & Bar.')"
                class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">
                Kirim ke Dapur / Bar
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

