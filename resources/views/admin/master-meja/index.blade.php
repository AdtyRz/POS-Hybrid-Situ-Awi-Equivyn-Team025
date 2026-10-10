@extends('layouts.admin')

@section('title', 'Master Data Meja, Saung & Dynamic QR')
@section('page_category', 'Master Data Meja/Saung')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="{
    activeClusterFilter: 'all',
    searchQuery: '',
    selectedSaung: 'LB-02',
    buzzerMuted: false,
    addModalOpen: false,
    toast: { show: false, message: '' },
    showToast(msg) {
        this.toast.message = msg;
        this.toast.show = true;
        setTimeout(() => this.toast.show = false, 3000);
    },
    // Saung selection helper
    select(id) {
        this.selectedSaung = id;
    }
}">

    <!-- 01. BREADCRUMBS & META HEADER -->
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>Situ Awi</span>
                <span>/</span>
                <span class="text-[#00341A]">Master Data Meja/Saung</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F0EEE7] text-[10px] font-bold text-[#707971]">
                <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                <span class="tracking-wide">INFRASTRUKTUR SAUNG & IOT TABLE NODE • FR-002A</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Master Data Meja, Saung Lesehan & Dynamic QR
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Pemetaan real-time 11 unit saung terapung, status telemetry hardware ESP32, dan proteksi Dynamic QR anti-order palsu.
                </p>
            </div>

            <!-- Primary Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <button 
                    type="button" 
                    @click="showToast('Mencetak lembar kompilasi QR code seluruh 11 saung...')"
                    class="h-11 px-5 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] active:scale-95 text-[#1C1C18] font-semibold text-sm flex items-center gap-2.5 transition-all shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Semua QR Saung (PDF)</span>
                </button>

                <button 
                    type="button" 
                    @click="addModalOpen = true"
                    class="h-11 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-sm flex items-center gap-2.5 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Meja / Saung Baru</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 02. SUMMARY KPI SECTION (4 CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Kapasitas Total Saung -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">KAPASITAS TOTAL SAUNG</span>
                <div class="w-9 h-9 rounded-xl bg-[#F0EEE7] flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    11 Unit Saung
                </span>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#404941] font-semibold text-[10px]">Maks. 68 Tamu Lesehan</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[10px] font-semibold text-[#00341A]">
                        3 Klaster Wilayah
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Status Keterisian Live -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STATUS KETERISIAN LIVE</span>
                <div class="w-9 h-9 rounded-xl bg-[#FFDEA1]/40 flex items-center justify-center text-[#7A5900]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                        7 Terisi
                    </span>
                    <span class="text-xs font-semibold text-[#00341A]">(64% Full)</span>
                </div>

                <!-- Segmented Multi-Color Progress Bar -->
                <div class="w-full h-2 rounded-full bg-[#E5E2DB] overflow-hidden flex my-2">
                    <div class="h-full bg-[#FECE66]" style="width: 64%"></div>
                    <div class="h-full bg-[#B1F1C2]" style="width: 27%"></div>
                    <div class="h-full bg-[#C0C9BF]" style="width: 9%"></div>
                </div>

                <div class="flex items-center justify-between text-[10px] font-bold text-[#404941]">
                    <span>3 Tersedia</span>
                    <span>1 Ter-reservasi</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Status IoT Hardware -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STATUS IOT HARDWARE</span>
                <div class="w-9 h-9 rounded-xl bg-[#B1F1C2]/40 flex items-center justify-center text-[#00341A]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    11/11 Synced
                </span>
                <div class="flex items-center justify-between mt-2 text-xs">
                    <span class="text-[#404941] font-bold text-[10px]">ESP32 + TTP223 OK</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#FFDAD6] text-[10px] font-bold text-[#93000A] animate-pulse">
                        1 Buzzer Aktif
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 4: Dynamic QR Security -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[175px] relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">DYNAMIC QR SECURITY</span>
                <div class="w-9 h-9 rounded-xl bg-[#F0EEE7] flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    100% Valid
                </span>
                <div class="flex items-center justify-between mt-2 text-[10px] font-bold">
                    <span class="text-[#404941]">Auto-Renewing per Sesi</span>
                    <span class="text-[#0B4D2B]">Anti-Order Palsu</span>
                </div>
            </div>

            <!-- Soft blur glow corner -->
            <div class="absolute -right-8 -bottom-8 w-24 h-24 rounded-full bg-[#FFDEA1]/25 blur-xl pointer-events-none"></div>
        </div>

    </div>

    <!-- 03. FILTER & SEARCH SUB-HEADER BAR -->
    <div class="p-2.5 bg-[#F6F3EC] rounded-2xl shadow-xs border border-[#C0C9BF]/20 flex flex-wrap items-center justify-between gap-4">
        <!-- Area Filter Tabs -->
        <div class="flex items-center flex-wrap gap-2">
            <button 
                type="button"
                @click="activeClusterFilter = 'all'"
                :class="activeClusterFilter === 'all' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                Semua Area (11)
            </button>
            <button 
                type="button"
                @click="activeClusterFilter = 'cluster_a'"
                :class="activeClusterFilter === 'cluster_a' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                Cluster A: Danau (5)
            </button>
            <button 
                type="button"
                @click="activeClusterFilter = 'cluster_b'"
                :class="activeClusterFilter === 'cluster_b' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                Cluster B: Balong (2)
            </button>
            <button 
                type="button"
                @click="activeClusterFilter = 'cluster_c'"
                :class="activeClusterFilter === 'cluster_c' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                Cluster C: Kursi Kayu (4)
            </button>
        </div>

        <!-- Search Input with icon -->
        <div class="relative w-full sm:w-72">
            <input 
                type="text" 
                x-model="searchQuery"
                placeholder="Cari saung, ID meja, IP node..." 
                class="w-full h-10 pl-10 pr-4 rounded-xl bg-white text-xs border border-gray-200 focus:outline-hidden focus:border-[#0B4D2B] focus:ring-1 focus:ring-[#0B4D2B] transition-all shadow-2xs">
            <svg class="w-4 h-4 text-[#707971] absolute left-3.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
            </svg>
        </div>
    </div>

    <!-- 04. MAIN CONTENT GRID (LEFT: CLUSTERS / RIGHT: INSPECTOR PANEL) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT COLUMN: CLUSTERS (8 cols / ~66%) -->
        <div class="lg:col-span-8 flex flex-col gap-8">
            
            <!-- SECTION - CLUSTER A: LESEHAN BAWAH TEPI DANAU -->
            <div 
                x-show="activeClusterFilter === 'all' || activeClusterFilter === 'cluster_a'"
                class="flex flex-col gap-4">
                
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-[#0B4D2B]"></span>
                        <h2 class="font-bold text-lg sm:text-xl text-[#00341A]">
                            Cluster A • Lesehan Bawah Tepi Danau
                        </h2>
                        <span class="px-2 py-0.5 rounded-md bg-[#EBE8E1] text-[10px] font-bold text-[#707971]">
                            5 Saung Terapung
                        </span>
                    </div>
                    <span class="text-xs font-semibold text-[#00341A]">Tepi Danau Indah</span>
                </div>

                <!-- Cluster A Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    
                    <!-- 1. LB-01: Saung Teratai Air -->
                    <div 
                        @click="select('LB-01')"
                        :class="selectedSaung === 'LB-01' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[200px]">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#707971]">LB-01</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Teratai Air</h3>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[10px] font-bold text-[#261900] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                                    Terisi (4)
                                </span>
                            </div>

                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex flex-col gap-1">
                                <div class="flex justify-between">
                                    <span class="text-[#707971]">Bill Aktif:</span>
                                    <span class="font-mono font-semibold text-[#1C1C18]">SW-8812</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#707971]">Total Pesanan:</span>
                                    <span class="font-bold text-[#00341A]">Rp 385.000</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                            <span class="flex items-center gap-1 text-[#213200]">
                                <span>📱</span> LCD: "Diproses"
                            </span>
                            <span>QR Auto-Renewed</span>
                        </div>
                    </div>

                    <!-- 2. LB-02: Saung Lesehan 02 (SELECTED & ACTIVE BUZZER CALL) -->
                    <div 
                        @click="select('LB-02')"
                        class="rounded-2xl p-4 cursor-pointer transition-all flex flex-col justify-between min-h-[200px] relative overflow-hidden bg-white border-2 border-[#BA1A1A] shadow-lg shadow-red-500/10">
                        
                        <!-- Top Red Corner Call Ribbon -->
                        <div class="absolute top-0 right-0 bg-[#BA1A1A] text-white text-[10px] font-bold px-3 py-0.5 rounded-bl-xl shadow-xs flex items-center gap-1">
                            <span>🚨</span>
                            <span>PANGGILAN AKTIF!</span>
                        </div>

                        <div>
                            <div class="flex items-start justify-between gap-1 pt-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#BA1A1A]">LB-02 • TERPILIH</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Lesehan 02</h3>
                                </div>
                            </div>

                            <div class="mt-3 p-2 bg-[#FFDAD6]/40 rounded-xl text-xs flex flex-col gap-1 text-[#93000A]">
                                <div class="flex justify-between">
                                    <span class="font-medium">Sensor TTP223:</span>
                                    <span class="font-bold text-[#BA1A1A]">Terpicu Buzzer ON</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Bill SW-8819:</span>
                                    <span class="font-bold text-[#00341A]">Rp 144.100</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#BA1A1A]">
                            <span class="flex items-center gap-1 animate-pulse">
                                <span>🔔</span> Suara Buzzer Menyala
                            </span>
                            <span>📡 IP: .102</span>
                        </div>
                    </div>

                    <!-- 3. LB-03: Saung Bambu Hitam (Tersedia) -->
                    <div 
                        @click="select('LB-03')"
                        :class="selectedSaung === 'LB-03' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[200px]">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#707971]">LB-03</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Bambu Hitam</h3>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/50 text-[10px] font-semibold text-[#00341A] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                                    Tersedia
                                </span>
                            </div>

                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex flex-col gap-1">
                                <span class="text-[#707971] italic">Meja Bersih & Siap Tamu</span>
                                <div class="flex justify-between text-[#707971]">
                                    <span>Kapasitas:</span>
                                    <span class="font-semibold text-[#1C1C18]">6 Orang</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                            <span class="flex items-center gap-1 text-[#0B4D2B]">
                                <span>✓</span> Node Ready
                            </span>
                            <span>QR Auto-Renewed</span>
                        </div>
                    </div>

                    <!-- 4. LB-04: Saung Gandasoli -->
                    <div 
                        @click="select('LB-04')"
                        :class="selectedSaung === 'LB-04' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[200px]">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#707971]">LB-04</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Gandasoli</h3>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[10px] font-bold text-[#261900] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                                    Memasak (8)
                                </span>
                            </div>

                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex flex-col gap-1">
                                <div class="flex justify-between">
                                    <span class="text-[#707971]">Bill Aktif:</span>
                                    <span class="font-mono font-semibold text-[#1C1C18]">SW-8809</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#707971]">Total:</span>
                                    <span class="font-bold text-[#00341A]">Rp 620.000</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                            <span class="flex items-center gap-1 text-[#7A5900]">
                                <span>🍳</span> KDS Ticket #14
                            </span>
                            <span>📡 IP: .104</span>
                        </div>
                    </div>

                    <!-- 5. LB-05: Saung Dermaga Ujung -->
                    <div 
                        @click="select('LB-05')"
                        :class="selectedSaung === 'LB-05' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[200px]">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#707971]">LB-05</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Dermaga Ujung</h3>
                                </div>
                                <span class="px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[10px] font-bold text-[#261900] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                                    Terisi (5)
                                </span>
                            </div>

                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex flex-col gap-1">
                                <div class="flex justify-between">
                                    <span class="text-[#707971]">Bill Aktif:</span>
                                    <span class="font-mono font-semibold text-[#1C1C18]">SW-8815</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[#707971]">Total:</span>
                                    <span class="font-bold text-[#00341A]">Rp 290.000</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                            <span class="flex items-center gap-1 text-[#00341A]">
                                <span>☕</span> Teh Poci sedang dibawa
                            </span>
                            <span>📡 IP: .105</span>
                        </div>
                    </div>

                    <!-- 6. Cluster Telemetry Node Card -->
                    <div class="rounded-2xl p-4 bg-[#0B4D2B] text-white shadow-xs flex flex-col justify-between min-h-[200px]">
                        <div>
                            <span class="text-[10px] font-bold tracking-wider text-[#B1F1C2] uppercase">CLUSTER TELEMETRY</span>
                            <h3 class="font-bold text-base text-white mt-1">Danau Gateway Node</h3>
                            <p class="text-xs text-[#96D5A7] mt-2 leading-relaxed">
                                Seluruh 5 node saung terhubung ke Access Point Mesh 'SITU_AWI_MESH_A'.
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-[#B1F1C2]/20 text-[10px] font-bold text-[#B1F1C2]">
                            <span>Ping 14ms • Paket Hilang 0%</span>
                            <span class="font-mono">192.168.4.101</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION - CLUSTER B: AREA LESEHAN ATAS BALONG INDAH -->
            <div 
                x-show="activeClusterFilter === 'all' || activeClusterFilter === 'cluster_b'"
                class="flex flex-col gap-4">
                
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-[#7A5900]"></span>
                        <h2 class="font-bold text-lg sm:text-xl text-[#00341A]">
                            Cluster B • Area Lesehan Atas Balong Indah
                        </h2>
                        <span class="px-2 py-0.5 rounded-md bg-[#EBE8E1] text-[10px] font-bold text-[#707971]">
                            2 Saung Panggung
                        </span>
                    </div>
                    <span class="text-xs font-semibold text-[#7A5900]">Area Kolam Atas</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- LA-01 -->
                    <div 
                        @click="select('LA-01')"
                        :class="selectedSaung === 'LA-01' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[165px]">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#707971]">LA-01</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Panorama Balong</h3>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#FFDEA1] text-[10px] font-bold text-[#261900] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                                    Terisi (6)
                                </span>
                            </div>

                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex justify-between">
                                <span class="text-[#707971]">Bill Aktif: <strong class="font-mono text-[#1C1C18]">SW-8820</strong></span>
                                <span class="font-bold text-[#00341A]">Rp 510.000</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                            <span class="text-[#213200]">🍽️ Makanan Selesai Disajikan</span>
                            <span>📡 IP: .201</span>
                        </div>
                    </div>

                    <!-- LA-02 -->
                    <div 
                        @click="select('LA-02')"
                        :class="selectedSaung === 'LA-02' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between min-h-[165px]">
                        <div>
                            <div class="flex items-start justify-between gap-1">
                                <div>
                                    <span class="text-[10px] font-bold text-[#707971]">LA-02</span>
                                    <h3 class="font-bold text-base text-[#1C1C18] leading-tight mt-0.5">Saung Kicau Burung</h3>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#E5E2DB] text-[10px] font-bold text-[#404941] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#707971]"></span>
                                    Booking 18:30
                                </span>
                            </div>

                            <div class="mt-3 p-2 bg-white/80 rounded-xl text-xs flex justify-between">
                                <span class="text-[#707971]">Atas Nama: <strong class="text-[#1C1C18]">Pak H. Dani (8 Org)</strong></span>
                                <span class="font-bold text-[#00341A]">DP Rp 200.000</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 text-[10px] font-bold text-[#707971]">
                            <span>🔒 Kunci QR dikunci s/d Check-in</span>
                            <span>📡 IP: .202</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION - CLUSTER C: AREA KURSI KAYU DEKAT KASIR & DAPUR -->
            <div 
                x-show="activeClusterFilter === 'all' || activeClusterFilter === 'cluster_c'"
                class="flex flex-col gap-4">
                
                <div class="flex items-center justify-between pb-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full bg-[#213200]"></span>
                        <h2 class="font-bold text-lg sm:text-xl text-[#00341A]">
                            Cluster C • Area Kursi Kayu Dekat Kasir & Dapur
                        </h2>
                        <span class="px-2 py-0.5 rounded-md bg-[#EBE8E1] text-[10px] font-bold text-[#707971]">
                            4 Meja Non-Lesehan
                        </span>
                    </div>
                    <span class="text-xs font-semibold text-[#707971]">Lantai Utama Semen Ekspos</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <!-- KA-01 -->
                    <div 
                        @click="select('KA-01')"
                        :class="selectedSaung === 'KA-01' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base text-[#1C1C18]">KA-01</span>
                            <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                        </div>
                        <span class="text-[10px] font-bold text-[#707971] mt-1">Meja Kayu Jati 4P</span>
                        <div class="mt-3 p-1.5 bg-white rounded-lg text-xs flex justify-between font-semibold">
                            <span class="text-[#404941]">Status:</span>
                            <span class="text-[#7A5900]">Terisi (4)</span>
                        </div>
                    </div>

                    <!-- KA-02 -->
                    <div 
                        @click="select('KA-02')"
                        :class="selectedSaung === 'KA-02' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base text-[#1C1C18]">KA-02</span>
                            <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                        </div>
                        <span class="text-[10px] font-bold text-[#707971] mt-1">Meja Kayu Jati 4P</span>
                        <div class="mt-3 p-1.5 bg-white rounded-lg text-xs flex justify-between font-semibold">
                            <span class="text-[#404941]">Status:</span>
                            <span class="text-[#0B4D2B]">Tersedia</span>
                        </div>
                    </div>

                    <!-- KA-03 -->
                    <div 
                        @click="select('KA-03')"
                        :class="selectedSaung === 'KA-03' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base text-[#1C1C18]">KA-03</span>
                            <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                        </div>
                        <span class="text-[10px] font-bold text-[#707971] mt-1">Meja Kayu Jati 4P</span>
                        <div class="mt-3 p-1.5 bg-white rounded-lg text-xs flex justify-between font-semibold">
                            <span class="text-[#404941]">Status:</span>
                            <span class="text-[#0B4D2B]">Tersedia</span>
                        </div>
                    </div>

                    <!-- KA-04 -->
                    <div 
                        @click="select('KA-04')"
                        :class="selectedSaung === 'KA-04' ? 'ring-2 ring-[#0B4D2B] bg-white' : 'bg-[#F6F3EC] hover:bg-[#efece3]'"
                        class="rounded-2xl p-4 shadow-xs border border-[#C0C9BF]/30 cursor-pointer transition-all flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-base text-[#1C1C18]">KA-04</span>
                            <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                        </div>
                        <span class="text-[10px] font-bold text-[#707971] mt-1 leading-tight">Meja Panjang Famili 8P</span>
                        <div class="mt-3 p-1.5 bg-white rounded-lg text-xs flex justify-between font-semibold">
                            <span class="text-[#404941]">Status:</span>
                            <span class="text-[#7A5900]">Terisi (8)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION - VISUAL CONTEXTUAL PHOTO SHOWCASE -->
            <div class="p-6 bg-[#F6F3EC] rounded-2xl shadow-xs border border-[#C0C9BF]/30 flex flex-col sm:flex-row items-center gap-6">
                <!-- Lake Saung Photo Graphic -->
                <div class="w-full sm:w-48 h-32 rounded-xl bg-gradient-to-tr from-[#0B4D2B] to-[#2E6A45] flex items-center justify-center text-white flex-shrink-0 shadow-inner p-4 text-center">
                    <div class="flex flex-col items-center gap-1.5">
                        <span class="text-3xl">🏕️</span>
                        <span class="font-display font-semibold text-xs tracking-wide">Saung Situ Awi</span>
                    </div>
                </div>

                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <span class="text-xs">🌿</span>
                        <span class="text-[10px] font-bold text-[#00341A] uppercase tracking-wider">
                            KONSEP PENATAAN SAUNG SUNDA TRADISIONAL
                        </span>
                    </div>
                    <h3 class="font-display font-bold text-base sm:text-lg text-[#00341A] mt-1">
                        Harmoni Vernakular Bambu & Presisi POS Terintegrasi
                    </h3>
                    <p class="text-xs text-[#404941] mt-1.5 leading-relaxed">
                        Setiap saung dirancang terpisah dengan jarak 3.5 meter di atas air situ. Modul IoT ESP32 terlindung dalam kotak ukir kayu tahan kelembapan dengan baterai cadangan hingga 18 jam operasional penuh.
                    </p>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: DETAIL INSPECTOR PANEL (4 cols / ~33%) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col gap-6 sticky top-24">
            
            <!-- Inspector Header -->
            <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                <div class="flex flex-col">
                    <span class="px-2.5 py-0.5 rounded-full bg-[#E5E2DB] text-[10px] font-bold text-[#00341A] w-fit font-mono">
                        <span x-text="selectedSaung">LB-02</span> CLUSTER A (DANAU)
                    </span>
                    <h2 class="font-display font-bold text-2xl text-[#00341A] mt-1">
                        <span x-text="selectedSaung === 'LB-02' ? 'Saung Lesehan 02' : 'Saung Detail (' + selectedSaung + ')'">Saung Lesehan 02</span>
                    </h2>
                </div>

                <button type="button" @click="showToast('Membuka form edit meja/saung...')" class="w-9 h-9 rounded-xl bg-[#F0EEE7] hover:bg-[#e4e1d7] active:scale-95 flex items-center justify-center text-[#404941] transition-all cursor-pointer" title="Edit Saung">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
            </div>

            <!-- CRITICAL / CALL ALERT BANNER -->
            <div 
                x-show="!buzzerMuted && selectedSaung === 'LB-02'"
                class="p-4 rounded-2xl bg-[#FFDAD6] border border-red-300 flex flex-col gap-3 shadow-xs">
                <div class="flex items-start gap-2.5 text-[#BA1A1A]">
                    <span class="text-xl animate-bounce">🚨</span>
                    <div class="flex flex-col">
                        <span class="font-bold text-xs leading-tight">PANGGILAN PELAYAN AKTIF!</span>
                        <span class="text-[11px] text-[#93000A] mt-0.5">Tamu menyentuh sensor TTP223 (3 Menit lalu)</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-1">
                    <button 
                        type="button" 
                        @click="buzzerMuted = true; showToast('Suara buzzer dimatikan. Status diperbarui.')"
                        class="flex-1 py-2.5 rounded-xl bg-[#BA1A1A] hover:bg-[#93000a] text-white font-bold text-xs transition-all active:scale-95 cursor-pointer shadow-xs">
                        Selesaikan / Mute
                    </button>
                    <button 
                        type="button" 
                        @click="showToast('Sinyal notifikasi dikirimkan ke Runner/Pelayan.')"
                        class="flex-1 py-2.5 rounded-xl bg-white hover:bg-gray-50 text-[#93000A] font-bold text-xs transition-all active:scale-95 cursor-pointer shadow-xs">
                        Panggil Pelayan
                    </button>
                </div>
            </div>

            <!-- IOT HARDWARE TELEMETRY DETAILS -->
            <div class="p-4 rounded-2xl bg-[#F6F3EC] border border-[#C0C9BF]/30 flex flex-col gap-3">
                <div class="flex items-center justify-between pb-1 border-b border-[#C0C9BF]/20">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">IOT HARDWARE TELEMETRY</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-[#00341A]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                        Online ESP32
                    </span>
                </div>

                <div class="flex flex-col gap-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-gray-200/50">
                        <span class="text-[#404941]">Hardware Model:</span>
                        <span class="font-mono font-semibold text-[#1C1C18]">ESP32 DOIT DevKit V1</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/50">
                        <span class="text-[#404941]">Sinyal Wi-Fi AP:</span>
                        <span class="font-mono font-semibold text-[#00341A]">-52 dBm (Sangat Baik)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-200/50 items-center">
                        <span class="text-[#404941]">LCD 16x2 I2C:</span>
                        <span class="px-2 py-0.5 rounded-md bg-[#0B4D2B] text-[#EFC05A] font-mono text-[10px] font-bold">
                            "Memanggil Pelayan..."
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-[#404941]">Piezo Buzzer:</span>
                        <span class="font-mono font-bold text-[#BA1A1A]">AKTIF (Frekuensi 2.7kHz)</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button 
                        type="button" 
                        @click="showToast('Uji coba sinyal bunyi buzzer 1 detik berhasil dikirim.')"
                        class="flex-1 py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] transition-all cursor-pointer">
                        🔊 Uji Buzzer
                    </button>
                    <button 
                        type="button" 
                        @click="showToast('Perintah restart modul ESP32 terkirim via MQTT.')"
                        class="flex-1 py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] transition-all cursor-pointer">
                        🔄 Restart Node
                    </button>
                </div>
            </div>

            <!-- DYNAMIC QR CODE CARD -->
            <div class="p-4 rounded-2xl bg-[#F6F3EC] border border-[#C0C9BF]/30 flex flex-col gap-3">
                <div class="flex items-center justify-between pb-1 border-b border-[#C0C9BF]/20">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">DYNAMIC QR CODE ORDER</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[10px] font-bold text-[#00341A]">
                        Token Aktif
                    </span>
                </div>

                <div class="flex items-center gap-3 p-3 bg-white rounded-xl">
                    <!-- QR Graphic Box -->
                    <div class="w-20 h-20 rounded-xl bg-[#FCF9F2] p-2 flex items-center justify-center flex-shrink-0 border border-gray-200 shadow-inner">
                        <svg viewBox="0 0 100 100" class="w-full h-full text-[#00341A]" fill="currentColor">
                            <!-- Pattern QR Representation -->
                            <rect x="5" y="5" width="25" height="25" rx="3" fill="#00341A"/>
                            <rect x="9" y="9" width="17" height="17" rx="2" fill="#FCF9F2"/>
                            <rect x="13" y="13" width="9" height="9" fill="#00341A"/>
                            
                            <rect x="70" y="5" width="25" height="25" rx="3" fill="#00341A"/>
                            <rect x="74" y="9" width="17" height="17" rx="2" fill="#FCF9F2"/>
                            <rect x="78" y="13" width="9" height="9" fill="#00341A"/>

                            <rect x="5" y="70" width="25" height="25" rx="3" fill="#00341A"/>
                            <rect x="9" y="74" width="17" height="17" rx="2" fill="#FCF9F2"/>
                            <rect x="13" y="78" width="9" height="9" fill="#00341A"/>

                            <!-- Matrix Data Dots -->
                            <rect x="40" y="10" width="8" height="8" fill="#00341A"/>
                            <rect x="52" y="10" width="8" height="8" fill="#00341A"/>
                            <rect x="40" y="24" width="8" height="8" fill="#00341A"/>
                            <rect x="52" y="24" width="8" height="8" fill="#00341A"/>

                            <rect x="10" y="40" width="8" height="8" fill="#00341A"/>
                            <rect x="24" y="40" width="8" height="8" fill="#00341A"/>
                            <rect x="70" y="40" width="8" height="8" fill="#00341A"/>
                            <rect x="84" y="40" width="8" height="8" fill="#00341A"/>

                            <!-- Center Situ Awi Gold Emblem Dot -->
                            <circle cx="50" cy="50" r="8" fill="#E0B24E"/>
                            <circle cx="50" cy="50" r="3" fill="#0B4D2B"/>

                            <rect x="40" y="70" width="8" height="8" fill="#00341A"/>
                            <rect x="52" y="70" width="8" height="8" fill="#00341A"/>
                            <rect x="70" y="70" width="8" height="8" fill="#00341A"/>
                            <rect x="84" y="84" width="8" height="8" fill="#00341A"/>
                        </svg>
                    </div>

                    <div class="flex flex-col text-xs overflow-hidden">
                        <span class="text-[10px] font-bold text-[#707971] uppercase">URL SESI ENKRIPSI:</span>
                        <span class="font-mono text-[11px] font-bold text-[#00341A] truncate mt-0.5">
                            https://situawi.com/order/<span x-text="selectedSaung">LB-02</span>?token=9902xrt-ciwidey
                        </span>
                        <span class="text-[10px] text-[#2E6A45] font-semibold mt-1 flex items-center gap-1">
                            <span>🛡️</span> Anti Fake Order Verified
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="showToast('Mengunduh QR Code (SVG/PNG)...')"
                        class="flex-1 py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] flex items-center justify-center gap-1 transition-all cursor-pointer">
                        <span>📥</span> Unduh SVG/PNG
                    </button>
                    <button 
                        type="button" 
                        @click="showToast('Token sesi berhasil di-regenerasi!')"
                        class="flex-1 py-2 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-[10px] flex items-center justify-center gap-1 transition-all cursor-pointer">
                        <span>🔄</span> Regenerasi Token
                    </button>
                </div>
            </div>

            <!-- ACTIVE ORDER QUICK GLANCE -->
            <div class="p-4 rounded-2xl bg-[#F6F3EC] border border-[#C0C9BF]/30 flex flex-col gap-3">
                <div class="flex items-center justify-between pb-1 border-b border-[#C0C9BF]/20">
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PESANAN TAMU SAAT INI</span>
                    <span class="font-mono font-bold text-xs text-[#1C1C18]">SW-8819</span>
                </div>

                <div class="flex flex-col gap-2 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-gray-200/40">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center font-bold text-[10px]">1</span>
                            <span class="text-[#1C1C18]">Gurame Bakar Cobek Sedap</span>
                        </div>
                        <span class="font-mono text-[#1C1C18]">Rp 85.000</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-gray-200/40">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center font-bold text-[10px]">2</span>
                            <span class="text-[#1C1C18]">Nasi Liwet Kastrol Komplit</span>
                        </div>
                        <span class="font-mono text-[#1C1C18]">Rp 44.000</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-gray-200/40">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-[#E5E2DB] text-[#1C1C18] flex items-center justify-center font-bold text-[10px]">1</span>
                            <span class="text-[#1C1C18]">Es Kelapa Batok Gula Aren</span>
                        </div>
                        <span class="font-mono text-[#1C1C18]">Rp 15.100</span>
                    </div>
                </div>

                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-xs font-semibold text-[#707971]">Total Bill Kasir:</span>
                    <span class="font-bold text-base text-[#00341A]">Rp 144.100</span>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <a 
                        href="{{ url('/kasir') }}"
                        class="flex-1 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white font-bold text-xs text-center transition-all shadow-xs">
                        Lihat Bill POS Kasir
                    </a>
                    <button 
                        type="button" 
                        @click="showToast('Membuka dialog edit data saung...')"
                        class="px-4 py-2.5 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-bold text-xs transition-all cursor-pointer">
                        Edit Meja
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL: + TAMBAH MEJA / SAUNG BARU -->
    <div 
        x-cloak
        x-show="addModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div 
            class="w-full max-w-lg bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-6"
            @click.outside="addModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex flex-col">
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Tambah Meja / Saung Baru</h3>
                    <p class="text-xs text-[#707971] mt-0.5">Konfigurasi node fisik ESP32 & kode meja POS</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-gray-400 hover:text-black p-1 text-lg">✕</button>
            </div>

            <form @submit.prevent="addModalOpen = false; showToast('Unit saung baru berhasil ditambahkan!')" class="flex flex-col gap-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kode Saung/Meja *</label>
                        <input type="text" placeholder="Contoh: LB-06" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Klaster Wilayah *</label>
                        <select class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            <option value="cluster_a">Cluster A: Danau</option>
                            <option value="cluster_b">Cluster B: Balong</option>
                            <option value="cluster_c">Cluster C: Kursi Kayu</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#1C1C18]">Nama Saung *</label>
                    <input type="text" placeholder="Contoh: Saung Teratai Biru" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kapasitas Tamu</label>
                        <input type="number" value="6" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">IP Address ESP32 Node</label>
                        <input type="text" placeholder="192.168.4.106" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button 
                        type="button" 
                        @click="addModalOpen = false" 
                        class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="flex-1 py-3 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] active:scale-95 transition-all shadow-xs cursor-pointer">
                        Simpan Saung
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FLOATING TOAST -->
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
        <span>🔔</span>
        <span x-text="toast.message"></span>
    </div>

</div>
@endsection

