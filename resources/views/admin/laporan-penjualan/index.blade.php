@extends('layouts.admin')

@section('title', 'Laporan Penjualan & Rekonsiliasi Kasir')
@section('page_category', 'Laporan & Ekspor')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="{
    exportFormat: 'pdf',
    searchQuery: '',
    selectedCluster: 'all',
    toast: { show: false, message: '' },
    showToast(msg) {
        this.toast.message = msg;
        this.toast.show = true;
        setTimeout(() => this.toast.show = false, 3000);
    },
    // Transaction records matching design & OCR data
    transactions: [
        {
            id: '#ORD-9821',
            time: '20:45 WIB',
            table: 'Saung 02 (LB-02)',
            items: '4 Item (Gurame, Liwet, Es Kelapa)',
            method: 'QRIS Midtrans',
            subtotal: 438182,
            pb1: 43818,
            total: 482000,
            cashier: 'Teh Neng Santi',
            status: 'valid',
            statusText: 'Match/Valid'
        },
        {
            id: '#ORD-9820',
            time: '20:15 WIB',
            table: 'Saung Gandasoli (LB-04)',
            items: '5 Item (Ayam Bakakak, Karedok, dll)',
            method: 'Tunai Kasir',
            subtotal: 563636,
            pb1: 56364,
            total: 620000,
            cashier: 'Teh Neng Santi',
            status: 'valid',
            statusText: 'Match/Valid'
        },
        {
            id: '#ORD-9819',
            time: '19:40 WIB',
            table: 'Meja Kursi KA-02',
            items: '1 Item (Sop Gurame - Void Batal)',
            method: 'VOID Kasir',
            subtotal: 0,
            pb1: 0,
            total: 0,
            cashier: 'Teh Neng Santi',
            status: 'void',
            statusText: 'VOID Approved'
        },
        {
            id: '#ORD-9818',
            time: '19:10 WIB',
            table: 'Saung Dermaga (LB-05)',
            items: '8 Item Paket Rombongan',
            method: 'QRIS Midtrans',
            subtotal: 809091,
            pb1: 80909,
            total: 890000,
            cashier: 'Teh Neng Santi',
            status: 'valid',
            statusText: 'Match/Valid'
        },
        {
            id: '#ORD-9817',
            time: '18:30 WIB',
            table: 'Saung Panorama (LA-01)',
            items: '6 Item Liwet Komplit',
            method: 'QRIS Midtrans',
            subtotal: 463636,
            pb1: 46364,
            total: 510000,
            cashier: 'Teh Neng Santi',
            status: 'valid',
            statusText: 'Match/Valid'
        },
        {
            id: '#ORD-9816',
            time: '17:55 WIB',
            table: 'Saung Teratai (LB-01)',
            items: '3 Item Gurame Cobek',
            method: 'Tunai Kasir',
            subtotal: 350000,
            pb1: 35000,
            total: 385000,
            cashier: 'Teh Neng Santi',
            status: 'valid',
            statusText: 'Match/Valid'
        }
    ],

    get filteredTransactions() {
        if (!this.searchQuery) return this.transactions;
        const q = this.searchQuery.toLowerCase();
        return this.transactions.filter(t => 
            t.id.toLowerCase().includes(q) || 
            t.table.toLowerCase().includes(q) || 
            t.items.toLowerCase().includes(q) ||
            t.method.toLowerCase().includes(q)
        );
    }
}">

    <!-- 01. PAGE HEADER & ACTION BAR -->
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">LAPORAN & EKSPOR</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E5E2DB] text-[10px] font-bold text-[#0B4D2B]">
                <svg class="w-3.5 h-3.5 text-[#334A00]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="tracking-wide">FR-008 • PB-008 • AUDIT TRAIL</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Laporan Penjualan, Rekonsiliasi Kasir & Ekspor Data
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Unduh rekap transaksi terperinci, laporan perputaran stok bahan, dan rekonsiliasi Midtrans QRIS vs Uang Tunai Kasir.
                </p>
            </div>

            <!-- Export Trays -->
            <div class="flex flex-wrap items-center gap-3">
                <button 
                    type="button" 
                    @click="showToast('Mengunduh Laporan Resmi PDF A4...')"
                    class="h-10 px-4 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Ekspor PDF Resmi</span>
                </button>

                <button 
                    type="button" 
                    @click="showToast('Mengunduh Dataset Finansial Excel (.xlsx)...')"
                    class="h-10 px-4 rounded-xl bg-[#FFDEA1] hover:bg-[#ebd055] active:scale-95 text-[#261900] font-semibold text-xs flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Dataset Excel (.xlsx)</span>
                </button>

                <button 
                    type="button" 
                    @click="showToast('Mencetak rekap shift kasir berjalan...')"
                    class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] active:scale-95 text-[#404941] font-semibold text-xs flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Cetak Rekap Shift</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 02. SECTION - INDIKATOR KUNCI FINANSIAL (4 BENTO CARDS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- KPI 1: Omzet Terkonsolidasi -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">OMZET TERKONSOLIDASI</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Bulan Ini (Sep 2026)</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/50 flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#0B4D2B] tracking-tight leading-tight">
                    Rp 284.650.000
                </span>
                
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 h-1.5 rounded-full bg-[#E5E2DB] overflow-hidden">
                        <div class="h-full bg-[#0B4D2B] rounded-full" style="width: 92%"></div>
                    </div>
                    <span class="text-[10px] font-semibold text-[#00341A]">92% Target</span>
                </div>
            </div>

            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-[#00341A]/5 blur-xl pointer-events-none"></div>
        </div>

        <!-- KPI 2: Total Tiket Transaksi -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">TOTAL TIKET TRANSAKSI</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Saung & Kios Kasir</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/60 flex items-center justify-center text-[#7A5900]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] tracking-tight leading-tight">
                    2.418 Pesanan
                </span>
                <span class="text-[10px] font-bold text-[#261900] mt-2 flex items-center gap-1">
                    <span>✓</span> 100% Audit Reconciled
                </span>
            </div>
        </div>

        <!-- KPI 3: Selisih Fisik vs Sistem -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">SELISIH FISIK VS SISTEM</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Variance Kas Operasional</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#CBEF89]/60 flex items-center justify-center text-[#334A00]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <div class="flex items-baseline gap-1.5">
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                        Rp 0
                    </span>
                    <span class="text-sm font-semibold text-[#364E00]">(Sempurna)</span>
                </div>
                <div class="flex items-center gap-1.5 mt-2 text-[10px] font-bold text-[#707971]">
                    <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                    <span>Zero Variance (Pagi & Malam)</span>
                </div>
            </div>
        </div>

        <!-- KPI 4: Total Pajak Resto PB1 -->
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[190px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">TOTAL PAJAK RESTORAN</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">PB1 (10% Terbitan Resmi)</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] flex items-center justify-center text-[#1C1C18]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-4">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] tracking-tight leading-tight">
                    Rp 28.465.000
                </span>
                <span class="text-[10px] font-bold text-[#404941] mt-2 flex items-center gap-1">
                    <span>🏛️</span> Siap Lapor Bapenda Kab. Bandung
                </span>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/20 blur-xl pointer-events-none"></div>
        </div>

    </div>

    <!-- 03. SECTION - FILTER GENERATOR CONSOLE CARD -->
    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 flex flex-col gap-5">
        
        <div class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-[#C0C9BF]/20">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#0B4D2B] flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-base text-[#1C1C18]">Parameter Generator Laporan</h2>
                    <p class="text-xs text-[#707971]">Pilih rentang waktu, kluster saung, dan format output yang dibutuhkan untuk audit.</p>
                </div>
            </div>

            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#E5E2DB] text-[10px] font-bold text-[#404941]">
                <svg class="w-3 h-3 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Auto-Sync Realtime Active</span>
            </div>
        </div>

        <!-- Filter Inputs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <!-- 1. Rentang Periode -->
            <div class="flex flex-col gap-1.5">
                <label class="font-bold text-[10px] text-[#707971] uppercase tracking-wider">RENTANG PERIODE</label>
                <div class="relative">
                    <input 
                        type="text" 
                        value="01 Sep 2026 - 15 Sep 2026"
                        class="w-full h-11 pl-10 pr-3 rounded-xl bg-[#FCF9F2] border border-gray-200 text-xs font-semibold text-[#1C1C18] shadow-2xs focus:outline-hidden focus:border-[#0B4D2B]">
                    <svg class="w-4 h-4 text-[#707971] absolute left-3.5 top-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <!-- 2. Tipe Rekapitulasi -->
            <div class="flex flex-col gap-1.5">
                <label class="font-bold text-[10px] text-[#707971] uppercase tracking-wider">TIPE REKAPITULASI</label>
                <div class="relative">
                    <select class="w-full h-11 pl-10 pr-8 rounded-xl bg-[#FCF9F2] border border-gray-200 text-xs font-semibold text-[#1C1C18] shadow-2xs focus:outline-hidden focus:border-[#0B4D2B] appearance-none">
                        <option>Laporan Penjualan Harian &amp; Shift Kasir</option>
                        <option>Laporan Rekonsiliasi Midtrans vs Tunai</option>
                        <option>Laporan Pajak Daerah PB1 (10%)</option>
                        <option>Laporan Perputaran Stok Bahan Baku</option>
                    </select>
                    <svg class="w-4 h-4 text-[#707971] absolute left-3.5 top-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <svg class="w-3.5 h-3.5 text-[#707971] absolute right-3 top-4 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- 3. Kluster Saung / Meja -->
            <div class="flex flex-col gap-1.5">
                <label class="font-bold text-[10px] text-[#707971] uppercase tracking-wider">KLUSTER SAUNG / MEJA</label>
                <div class="relative">
                    <select x-model="selectedCluster" class="w-full h-11 pl-10 pr-8 rounded-xl bg-[#FCF9F2] border border-gray-200 text-xs font-semibold text-[#1C1C18] shadow-2xs focus:outline-hidden focus:border-[#0B4D2B] appearance-none">
                        <option value="all">Semua Saung (11 Meja)</option>
                        <option value="cluster_a">Cluster A: Danau (LB-01 s/d LB-05)</option>
                        <option value="cluster_b">Cluster B: Balong (LA-01 &amp; LA-02)</option>
                        <option value="cluster_c">Cluster C: Kursi Kayu (KA-01 s/d KA-04)</option>
                    </select>
                    <svg class="w-4 h-4 text-[#707971] absolute left-3.5 top-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <svg class="w-3.5 h-3.5 text-[#707971] absolute right-3 top-4 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <!-- 4. Format Ekspor -->
            <div class="flex flex-col gap-1.5">
                <label class="font-bold text-[10px] text-[#707971] uppercase tracking-wider">FORMAT EKSPOR</label>
                <div class="h-11 bg-[#FCF9F2] p-1 rounded-xl border border-gray-200 shadow-2xs flex items-center justify-between">
                    <button 
                        type="button" 
                        @click="exportFormat = 'pdf'"
                        :class="exportFormat === 'pdf' ? 'bg-[#0B4D2B] text-white font-bold shadow-xs' : 'text-[#404941] hover:text-black font-semibold'"
                        class="flex-1 py-1.5 rounded-lg text-center text-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        <span>PDF A4</span>
                    </button>
                    <button 
                        type="button" 
                        @click="exportFormat = 'excel'"
                        :class="exportFormat === 'excel' ? 'bg-[#0B4D2B] text-white font-bold shadow-xs' : 'text-[#404941] hover:text-black font-semibold'"
                        class="flex-1 py-1.5 rounded-lg text-center text-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Excel</span>
                    </button>
                    <button 
                        type="button" 
                        @click="exportFormat = 'csv'"
                        :class="exportFormat === 'csv' ? 'bg-[#0B4D2B] text-white font-bold shadow-xs' : 'text-[#404941] hover:text-black font-semibold'"
                        class="flex-1 py-1.5 rounded-lg text-center text-xs transition-all flex items-center justify-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" /></svg>
                        <span>CSV</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Generate Button -->
        <div class="flex justify-end pt-1">
            <button 
                type="button" 
                @click="showToast('Pratinjau laporan berhasil dimuat ulang dengan parameter terbaru.')"
                class="w-full sm:w-auto h-11 px-8 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Generate &amp; Pratinjau Laporan</span>
            </button>
        </div>

    </div>

    <!-- 04. INTERACTIVE PREVIEW TABLE SECTION -->
    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        
        <!-- Table Header Info Bar -->
        <div class="p-5 bg-[#F6F3EC] border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-6 rounded-full bg-[#EFC05A]"></span>
                <div>
                    <h2 class="font-bold text-base text-[#1C1C18]">Pratinjau Rekap Transaksi Harian (15 September 2026)</h2>
                    <p class="text-xs text-[#707971] mt-0.5">Menampilkan 6 transaksi shift sore-malam terpilih • Auditor ID: POS-ADM-01</p>
                </div>
            </div>

            <!-- Search & Filter Controls -->
            <div class="flex items-center gap-2">
                <div class="relative w-44 sm:w-56">
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Cari Order / Saung..." 
                        class="w-full h-9 pl-8 pr-3 rounded-lg bg-[#FCF9F2] text-xs border border-gray-200 focus:outline-hidden focus:border-[#0B4D2B]">
                    <svg class="w-3.5 h-3.5 text-[#707971] absolute left-2.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
                    </svg>
                </div>

                <button type="button" @click="showToast('Filter kolom tabel aktif')" class="h-9 px-3 rounded-lg bg-[#F0EEE7] hover:bg-gray-200 text-[#1C1C18] text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-3 h-3 text-[#1C1C18]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 8h12M8 12h8" />
                    </svg>
                    <span>Filter</span>
                </button>
            </div>
        </div>

        <!-- Responsive Table Wrapper -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F0EEE7] border-b border-gray-200 text-[10px] font-bold text-[#404941] uppercase tracking-wider">
                        <th class="py-3 px-4 min-w-[100px]">NO. TRANSAKSI</th>
                        <th class="py-3 px-4 min-w-[90px]">WAKTU / SESI</th>
                        <th class="py-3 px-4 min-w-[140px]">SAUNG / MEJA</th>
                        <th class="py-3 px-4 min-w-[220px]">ITEM DIPESAN</th>
                        <th class="py-3 px-4 min-w-[120px]">METODE BAYAR</th>
                        <th class="py-3 px-4 text-right min-w-[110px]">SUBTOTAL</th>
                        <th class="py-3 px-4 text-right min-w-[90px]">PAJAK PB1</th>
                        <th class="py-3 px-4 text-right min-w-[120px]">TOTAL BERSIH</th>
                        <th class="py-3 px-4 min-w-[120px]">KASIR PETUGAS</th>
                        <th class="py-3 px-4 text-center min-w-[110px]">STATUS AUDIT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="t in filteredTransactions" :key="t.id">
                        <tr 
                            class="transition-colors"
                            :class="t.status === 'void' ? 'bg-red-50/30 text-gray-500' : 'hover:bg-[#FBF8F1]/60'">
                            
                            <!-- No. Transaksi -->
                            <td class="py-3 px-4">
                                <span 
                                    class="font-mono font-bold"
                                    :class="t.status === 'void' ? 'line-through text-[#BA1A1A]' : 'text-[#0B4D2B]'"
                                    x-text="t.id">
                                </span>
                            </td>

                            <!-- Waktu / Sesi -->
                            <td class="py-3 px-4 text-[#404941]" x-text="t.time"></td>

                            <!-- Saung / Meja -->
                            <td class="py-3 px-4">
                                <span class="font-semibold text-[#1C1C18]" x-text="t.table"></span>
                            </td>

                            <!-- Item Dipesan -->
                            <td class="py-3 px-4">
                                <span :class="t.status === 'void' ? 'line-through text-red-700' : 'text-[#404941]'" x-text="t.items"></span>
                            </td>

                            <!-- Metode Bayar -->
                            <td class="py-3 px-4">
                                <span 
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#FFDEA1]/50 text-[#7A5900]': t.method === 'QRIS Midtrans',
                                        'bg-[#E5E2DB] text-[#1C1C18]': t.method === 'Tunai Kasir',
                                        'bg-[#FFDAD6] text-[#93000A]': t.method === 'VOID Kasir'
                                    }">
                                    <span x-text="t.method"></span>
                                </span>
                            </td>

                            <!-- Subtotal -->
                            <td class="py-3 px-4 text-right" :class="t.status === 'void' ? 'line-through text-gray-400' : 'text-[#404941]'" x-text="'Rp ' + t.subtotal.toLocaleString('id-ID')"></td>

                            <!-- Pajak PB1 -->
                            <td class="py-3 px-4 text-right text-gray-500" :class="t.status === 'void' ? 'line-through' : ''" x-text="'Rp ' + t.pb1.toLocaleString('id-ID')"></td>

                            <!-- Total Bersih -->
                            <td class="py-3 px-4 text-right font-bold text-sm" :class="t.status === 'void' ? 'line-through text-gray-400' : 'text-[#0B4D2B]'" x-text="'Rp ' + t.total.toLocaleString('id-ID')"></td>

                            <!-- Kasir Petugas -->
                            <td class="py-3 px-4 text-[#404941]" x-text="t.cashier"></td>

                            <!-- Status Audit -->
                            <td class="py-3 px-4 text-center">
                                <span 
                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="t.status === 'void' ? 'bg-[#FFDAD6] text-[#93000A]' : 'bg-[#B1F1C2]/50 text-[#11512F]'">
                                    <span x-text="t.status === 'void' ? '✕' : '✓'"></span>
                                    <span x-text="t.statusText"></span>
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <!-- Table Summary Footer Row -->
                <tfoot>
                    <tr class="bg-[#F0EEE7] border-t-2 border-gray-300 font-bold text-xs text-[#1C1C18]">
                        <td colspan="5" class="py-3.5 px-4 text-right">
                            Total Sampel Pratinjau (5 Valid + 1 Void):
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-[#1C1C18]">Rp 2.624.545</td>
                        <td class="py-3.5 px-4 text-right font-bold text-[#707971]">Rp 262.455</td>
                        <td class="py-3.5 px-4 text-right font-bold text-sm text-[#0B4D2B]">Rp 2.887.000</td>
                        <td colspan="2" class="py-3.5 px-4 text-left text-[11px] text-[#707971]">
                            Shift Sore: Sukses Terkonsolidasi
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Table Pagination Tray -->
        <div class="p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-[#707971]">
            <span>Menampilkan 1-6 dari 142 transaksi pada 15 Sep 2026</span>

            <div class="flex items-center gap-1">
                <button type="button" class="w-8 h-8 rounded-lg bg-[#0B4D2B] text-white font-bold flex items-center justify-center">1</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center font-bold">2</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center font-bold">3</button>
                <span class="px-1 text-gray-400">...</span>
                <button type="button" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center font-bold">24</button>
            </div>
        </div>

    </div>

    <!-- 05. SECTION - BOTTOM VISUAL BREAK: RECONCILIATION CARDS & OWNERSHIP AUDIT STAMP -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        <!-- KOTAK REKONSILIASI KASIR & MIDTRANS (8 cols) -->
        <div class="lg:col-span-8 bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 flex flex-col justify-between gap-5">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="font-bold text-base text-[#1C1C18]">Status Tutup Kasir Shift &amp; Rekonsiliasi Rekening</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[10px] font-bold text-[#0B4D2B]">
                        Settlement Berhasil
                    </span>
                </div>
                <p class="text-xs text-[#404941] mt-1.5 leading-relaxed">
                    Perbandingan fisik laci kasir tunai vs settlement online payment gateway Midtrans yang telah masuk ke rekening operasional BCA Situ Awi.
                </p>

                <!-- Split Metrics Bento -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                    <!-- Drawer Cash -->
                    <div class="p-4 bg-[#F0EEE7] rounded-xl shadow-2xs flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-[#707971] uppercase">FISIK LACI KASIR</span>
                            <span class="text-base text-[#0B4D2B]">💵</span>
                        </div>
                        <span class="font-display font-bold text-xl text-[#1C1C18] mt-2">Rp 4.158.000</span>
                        <span class="text-[10px] font-semibold text-[#0B4D2B] mt-1 flex items-center gap-1">
                            <span>✓</span> Tersinkron (Laci Kasir A &amp; B)
                        </span>
                    </div>

                    <!-- Midtrans Settlement -->
                    <div class="p-4 bg-[#FFDEA1]/30 rounded-xl shadow-2xs flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-[#765600] uppercase">MIDTRANS QRIS SETTLEMENT</span>
                            <span class="text-base text-[#7A5900]">💳</span>
                        </div>
                        <span class="font-display font-bold text-xl text-[#261900] mt-2">Rp 10.692.000</span>
                        <span class="text-[10px] font-semibold text-[#7A5900] mt-1 flex items-center gap-1">
                            <span>✓</span> Disbursed to BCA • Batch ID: MID-99214
                        </span>
                    </div>
                </div>
            </div>

            <!-- Verification Timestamp Footer -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#C0C9BF]/30 text-[10px] font-bold text-[#707971]">
                <span>🔒 Enkripsi SHA-256 Validated via Situ Awi Edge Core</span>
                <span class="font-mono text-[#404941]">Audit Timestamp: 15/09/2026 21:05:12 WIB</span>
            </div>
        </div>

        <!-- CATATAN PENGESAHAN PEMILIK & CAP RESMI (4 cols) -->
        <div class="lg:col-span-4 bg-[#EBE8E1] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 flex flex-col justify-between relative overflow-hidden">
            <div>
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">LEMBAR PENGESAHAN RESMI</span>
                <h3 class="font-bold text-base text-[#0B4D2B] mt-1">Verifikasi &amp; Audit Owner</h3>
                <p class="text-xs text-[#404941] mt-1.5 leading-relaxed">
                    Laporan ini sah sebagai arsip pembukuan internal &amp; lampiran kepatuhan PB1 Pemkab Bandung.
                </p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-[#C0C9BF]/40 mt-4">
                <div class="flex flex-col text-xs">
                    <span class="text-[10px] font-bold text-[#707971]">Disahkan oleh Pemilik:</span>
                    <strong class="font-bold text-sm text-[#1C1C18] mt-0.5">Bpk. Rodiansyah Ariwibowo</strong>
                    <span class="text-[10px] font-semibold text-[#0B4D2B]">Situ Awi Saung Lesehan</span>
                    <span class="text-[10px] text-[#707971] mt-0.5">Tanggal: 15/09/2026</span>
                </div>

                <!-- Physical Stamp / Seal Graphic (Dashed Circle Rotated -3deg) -->
                <div class="relative w-20 h-20 rounded-full border-2 border-dashed border-[#0B4D2B] p-1 flex flex-col items-center justify-center text-center -rotate-6 bg-white/70 shadow-xs flex-shrink-0">
                    <span class="text-[7px] font-bold text-[#0B4D2B] uppercase tracking-tighter">SITU AWI</span>
                    <svg class="w-4 h-4 text-[#0B4D2B] my-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[7px] font-bold text-[#0B4D2B] uppercase tracking-tighter">SAH • AUDITED</span>
                </div>
            </div>
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

