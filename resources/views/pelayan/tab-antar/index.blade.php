@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Antar) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="{
    // Filter Chip Selection: 'all', 'kitchen', 'bar', 'quick'
    filterCategory: 'all',
    soundVibrationActive: true,
    showMapModal: false,
    toastMessage: '',
    showToast: false,

    // Delivered States for the 3 Orders
    order1Delivered: false,
    order2Delivered: false,
    order3Delivered: false,

    // Metrics Counters
    readyCount: 5,
    deliveringCount: 2,
    finishedCount: 12,

    triggerToast(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        playChime('success');
        setTimeout(() => {
            this.showToast = false;
        }, 3500);
    },

    markDelivered(orderId, saungName) {
        if (orderId === 1) this.order1Delivered = true;
        if (orderId === 2) this.order2Delivered = true;
        if (orderId === 3) this.order3Delivered = true;

        if (this.readyCount > 0) this.readyCount--;
        this.finishedCount++;

        this.triggerToast(`Pesanan ${saungName} berhasil diantar & diselesaikan.`);
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
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Antar</h1>
            </div>
        </div>

        <!-- Right: Status Badge & Profile Avatar -->
        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <!-- Profile Avatar (Kang Asep) with Forest Green Ring -->
            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white font-bold text-xs flex items-center justify-center shadow-xs ring-2 ring-[#0B4D2B]/20">
                KA
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="p-4 space-y-4 flex-1">

        <!-- ========================================================
             SECTION 1: RUNNER STATUS BAR
             ======================================================== -->
        <div class="bg-[#F0EEE7] rounded-xl p-2.5 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 leading-tight">
                        <span class="font-bold text-base text-[#1C1C18]">Kang Asep</span>
                        <span class="text-[10px] font-medium text-[#404941]">(Area Danau)</span>
                    </div>
                    <span class="text-xs text-[#404941] block leading-tight mt-0.5">Shift Siang (12:00–18:00)</span>
                </div>
            </div>

            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-bold">
                <svg class="w-3 h-3 text-[#213200]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
                <span>-52 dBm</span>
            </div>
        </div>

        <!-- ========================================================
             SECTION 2: SUMMARY METRIC BADGES (3 Badges Grid)
             ======================================================== -->
        <div class="grid grid-cols-3 gap-2">
            <!-- Badge 1: Hidangan Siap (5) -->
            <div class="bg-[#0B4D2B] rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                <span class="font-display font-bold text-3xl text-[#B1F1C2] leading-none" x-text="readyCount">5</span>
                <span class="text-[10px] font-bold text-[#7FBD92] uppercase tracking-tight mt-1.5">HIDANGAN SIAP</span>
            </div>

            <!-- Badge 2: Sedang Diantar (2) -->
            <div class="bg-[#FECE66] rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                <span class="font-display font-bold text-3xl text-[#261900] leading-none" x-text="deliveringCount">2</span>
                <span class="text-[10px] font-bold text-[#765600] uppercase tracking-tight mt-1.5">DIANTAR</span>
            </div>

            <!-- Badge 3: Selesai Shift (12) -->
            <div class="bg-[#EBE8E1] rounded-xl p-2.5 text-center shadow-xs flex flex-col justify-center items-center">
                <span class="font-display font-bold text-3xl text-[#1C1C18] leading-none" x-text="finishedCount">12</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1.5">SELESAI</span>
            </div>
        </div>

        <!-- ========================================================
             SECTION 3: FILTER CHIPS BAR (Horizontal Scrollable)
             ======================================================== -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none -mx-4 px-4">
            <!-- Chip 1: Semua Siap (5) -->
            <button 
                @click="filterCategory = 'all'"
                :class="filterCategory === 'all' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Semua Siap</span>
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="filterCategory === 'all' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      x-text="readyCount">5</span>
            </button>

            <!-- Chip 2: Dapur Makanan (3) -->
            <button 
                @click="filterCategory = 'kitchen'"
                :class="filterCategory === 'kitchen' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Dapur Makanan</span>
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="filterCategory === 'kitchen' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'">3</span>
            </button>

            <!-- Chip 3: Bar Minuman (2) -->
            <button 
                @click="filterCategory = 'bar'"
                :class="filterCategory === 'bar' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Bar Minuman</span>
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                      :class="filterCategory === 'bar' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'">2</span>
            </button>

            <!-- Chip 4: Prioritas Cepat -->
            <button 
                @click="filterCategory = 'quick'"
                :class="filterCategory === 'quick' ? 'bg-[#0B4D2B] text-[#B1F1C2] shadow-xs' : 'bg-[#F0EEE7] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <span>Prioritas Cepat</span>
            </button>
        </div>

        <!-- ========================================================
             SECTION 4: PRIORITY QUEUE GROUP
             ======================================================== -->
        <div class="space-y-3 pt-1">
            <!-- Group Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h2 class="font-bold text-base text-[#1C1C18]">Siap di Pass Counter</h2>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[#00341A] text-[10px] font-bold">
                    Prioritas Cepat
                </span>
            </div>

            <!-- CARD 1: Pass Dapur #01 -> Saung 02 (LB-02) -->
            <div x-show="(!order1Delivered) && (filterCategory === 'all' || filterCategory === 'kitchen' || filterCategory === 'quick')" 
                 x-transition
                 class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
                <!-- Meta Row: Station, Timer, Order Code, Item Count -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-[#FFDEA1] text-[#261900] text-[10px] font-bold">
                            Pass Dapur #01
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-[#BA1A1A]">
                            <svg class="w-3 h-3 text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            3 mnt lalu
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] font-bold text-[#404941]">#ORD-9821</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#F0EEE7] text-[#00341A] text-xs font-bold">
                            4 Item
                        </span>
                    </div>
                </div>

                <!-- Saung Title & Subtitle -->
                <div>
                    <h3 class="font-bold text-xl text-[#1C1C18] leading-tight">LB-02 Saung 02</h3>
                    <p class="text-xs text-[#404941] mt-0.5">Tepi Danau Bawah (Kapasitas 6-8 Org)</p>
                </div>

                <!-- Items Breakdown Card -->
                <div class="bg-[#F0EEE7] rounded-xl p-3 space-y-2 border border-[#E5E2DB]/60">
                    <div class="flex items-start gap-2">
                        <span class="w-3.5 h-3.5 text-[#00341A] mt-0.5 flex-shrink-0">🍲</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">1x Gurame Bakar Situ Awi</span>
                            <span class="text-xs italic text-[#404941]">Khas Bakar Kering, Sambal Cobek Pisah</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="text-xs font-bold text-[#00341A] mt-0.5 flex-shrink-0">2x</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Nasi Liwet Kastrol Mini</span>
                            <span class="text-xs italic text-[#404941]">Standby Panas Berasap</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="w-3.5 h-3.5 text-[#00341A] mt-0.5 flex-shrink-0">🥗</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">1x Karedok Leunca Sunda</span>
                            <span class="text-xs italic text-[#404941]">Tingkat Pedas: Sedang</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Primary & Secondary (Denah) -->
                <div class="space-y-2 pt-1">
                    <button 
                        @click="markDelivered(1, 'LB-02 Saung 02')"
                        class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all active:scale-98 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Ambil & Tandai Selesai Diantarkan</span>
                    </button>

                    <button 
                        @click="showMapModal = true"
                        class="w-full h-9 rounded-lg bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] font-semibold text-xs flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>Lihat Denah Saung 02 (Jalur Dermaga)</span>
                    </button>
                </div>
            </div>

            <!-- CARD 2: Bar Counter Beverage -> Saung Dermaga Ujung (LB-05) -->
            <div x-show="(!order2Delivered) && (filterCategory === 'all' || filterCategory === 'bar')" 
                 x-transition
                 class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
                <!-- Meta Row: Station, Timer, Order Code, Item Count -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-[#CBEF89] text-[#131F00] text-[10px] font-bold">
                            Bar Counter Beverage
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-bold text-[#7A5900]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                            Baru Saja
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] font-bold text-[#404941]">#ORD-9818</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#F0EEE7] text-[#00341A] text-xs font-bold">
                            5 Item
                        </span>
                    </div>
                </div>

                <!-- Saung Title & Subtitle -->
                <div>
                    <h3 class="font-bold text-xl text-[#1C1C18] leading-tight">LB-05 Saung Dermaga Ujung</h3>
                    <p class="text-xs text-[#404941] mt-0.5">Lesehan Kayu Ujung Barat</p>
                </div>

                <!-- Items Breakdown Card -->
                <div class="bg-[#F0EEE7] rounded-xl p-3 space-y-2 border border-[#E5E2DB]/60">
                    <div class="flex items-start gap-2">
                        <span class="text-xs font-bold text-[#00341A] mt-0.5 flex-shrink-0">4x</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">Es Teh Manis Ciwidey</span>
                            <span class="text-xs italic text-[#404941]">Es Batu Banyak, Gula Sedang</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="w-3.5 h-3.5 text-[#00341A] mt-0.5 flex-shrink-0">🍊</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">1x Es Jeruk Peras Murni Selasih</span>
                            <span class="text-xs italic text-[#404941]">Gula Pasir Alami, Dingin Segar</span>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-1">
                    <button 
                        @click="markDelivered(2, 'LB-05 Saung Dermaga Ujung')"
                        class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all active:scale-98 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Ambil & Tandai Selesai Diantarkan</span>
                    </button>
                </div>
            </div>

            <!-- CARD 3: Pass Dapur #02 -> Panorama Balong (LA-01) -->
            <div x-show="(!order3Delivered) && (filterCategory === 'all' || filterCategory === 'kitchen')" 
                 x-transition
                 class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/70 space-y-3">
                <!-- Meta Row: Station, Timer, Order Code, Item Count -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-[#FFDEA1] text-[#261900] text-[10px] font-bold">
                            Pass Dapur #02
                        </span>
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-[#404941]">
                            <svg class="w-3 h-3 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            1 mnt lalu
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] font-bold text-[#404941]">#ORD-9817</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#F0EEE7] text-[#00341A] text-xs font-bold">
                            2 Item
                        </span>
                    </div>
                </div>

                <!-- Saung Title & Subtitle -->
                <div>
                    <h3 class="font-bold text-xl text-[#1C1C18] leading-tight">LA-01 Panorama Balong</h3>
                    <p class="text-xs text-[#404941] mt-0.5">Lesehan Atas Dekat Air Terjun Mini</p>
                </div>

                <!-- Items Breakdown Card -->
                <div class="bg-[#F0EEE7] rounded-xl p-3 space-y-2 border border-[#E5E2DB]/60">
                    <div class="flex items-start gap-2">
                        <span class="w-3.5 h-3.5 text-[#00341A] mt-0.5 flex-shrink-0">🍗</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">1x Ayam Bakakak Hayam Kampung Utuh</span>
                            <span class="text-xs italic text-[#404941]">Bumbu Rujak Manis Gurih, Lalap Segar</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="w-3.5 h-3.5 text-[#00341A] mt-0.5 flex-shrink-0">🐟</span>
                        <div>
                            <span class="font-bold text-sm text-[#1C1C18] block leading-snug">1x Cobek Gurame Goreng Renyah</span>
                            <span class="text-xs italic text-[#404941]">Sambal Jahe Kencur Hangat</span>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-1">
                    <button 
                        @click="markDelivered(3, 'LA-01 Panorama Balong')"
                        class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all active:scale-98 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Ambil & Tandai Selesai Diantarkan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 5: LIVE COWORKER TRACKING SECTION
             ======================================================== -->
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

            <!-- Mini Card Live Runner Kang Dadan -->
            <div class="bg-[#F0EEE7] rounded-xl p-3 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#FFDEA1] text-[#261900] text-xs font-bold flex items-center justify-center flex-shrink-0 shadow-2xs">
                        KD
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 leading-tight">
                            <span class="font-bold text-xs text-[#1C1C18]">Kang Dadan</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                        </div>
                        <span class="text-xs font-semibold text-[#00341A] block leading-tight mt-0.5">
                            LA-02 Kicau Burung (2x Nasi Timbel)
                        </span>
                    </div>
                </div>

                <div class="flex flex-col items-end text-right">
                    <span class="text-[10px] font-bold text-[#7A5900]">4 mnt lalu</span>
                    <span class="text-[10px] font-bold text-[#404941]">Di Jalur</span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             SECTION 6: SOUND & VIBRATION TOGGLE ACTION BAR
             ======================================================== -->
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

    <!-- ========================================================
         MODAL: DENAH JALUR SAUNG 02 (Wayfinding Helper)
         ======================================================== -->
    <div x-show="showMapModal" 
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showMapModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Denah Saung 02 (LB-02)</h3>
                </div>
                <button @click="showMapModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>

            <!-- Route Visualization Map Guide -->
            <div class="bg-[#F6F3EC] rounded-xl p-4 space-y-3 text-xs text-[#404941]">
                <div class="flex items-start gap-2.5">
                    <span class="w-6 h-6 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center font-bold text-xs flex-shrink-0">1</span>
                    <div>
                        <strong class="text-[#1C1C18] block">Pass Dapur Utama:</strong>
                        Ambil baki nampan dari Pass Counter #01.
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="w-6 h-6 rounded-full bg-[#FECE66] text-[#261900] flex items-center justify-center font-bold text-xs flex-shrink-0">2</span>
                    <div>
                        <strong class="text-[#1C1C18] block">Jalur Dermaga Kayu:</strong>
                        Turun ke arah tepi danau bawah (arah barat).
                    </div>
                </div>

                <div class="flex items-start gap-2.5">
                    <span class="w-6 h-6 rounded-full bg-[#BA1A1A] text-white flex items-center justify-center font-bold text-xs flex-shrink-0">3</span>
                    <div>
                        <strong class="text-[#1C1C18] block">Saung LB-02:</strong>
                        Saung nomor 2 dari dermaga utama, menghadap air terjun mini.
                    </div>
                </div>
            </div>

            <button 
                @click="showMapModal = false"
                class="w-full py-2.5 bg-[#00341A] hover:bg-[#0B4D2B] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">
                Tutup Peta
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

