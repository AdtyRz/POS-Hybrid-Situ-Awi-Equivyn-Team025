@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & IoT - Situ Awi Saung Lesehan')
@section('page_category', 'Pengaturan Sistem')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="{
    activeTab: 'iot',
    isSaving: false,
    saveSuccess: false,
    pinging: false,
    pingSuccess: false,
    buzzerTesting: false,
    restarting: false,
    isPrintingSample: false,
    testingMidtrans: false,
    pb1Enabled: true,
    serviceChargeEnabled: false,
    midtransEnv: 'sandbox',
    midtransMerchantId: 'G189204812',
    midtransClientKey: 'SB-Mid-client-8XyZ90AkLmNoPqRs',
    midtransServerKey: 'SB-Mid-server-1TuVwXyZ34567890',
    qrisExpiry: 15,
    autoCancelExpired: true,
    buzzerDuration: 3,
    antiSpamCooldown: 5,
    lcdMessage: 'Memanggil Pelayan...',
    sessionExpiry: 120,
    encryptionLevel: 'AES-256 HMAC Token (Standard Enterprise)',
    paperSize: '80mm',
    printerIp: '192.168.1.150',
    printerPort: '9100',
    autoCutter: true,
    printLogo: true,
    printQrCode: true,
    receiptHeader: 'Saung Situ Awi - Ciwidey',
    receiptFooter: 'Hatur Nuhun Parantos Sumping',
    restoName: 'Situ Awi Saung Lesehan',
    restoAddress: 'Jl. Raya Ciwidey No. 25, Rancabali, Kab. Bandung',
    restoPhone: '0812-3456-7890',
    npwpd: 'P.2.0049281.04.18',
    pb1Rate: 10,
    serviceChargeRate: 5,
    roundingRule: 'none',

    triggerSave() {
        this.isSaving = true;
        setTimeout(() => {
            this.isSaving = false;
            this.saveSuccess = true;
            setTimeout(() => this.saveSuccess = false, 3000);
        }, 800);
    },

    testPing() {
        this.pinging = true;
        setTimeout(() => {
            this.pinging = false;
            this.pingSuccess = true;
            setTimeout(() => this.pingSuccess = false, 4000);
        }, 1200);
    },

    testBuzzer() {
        this.buzzerTesting = true;
        setTimeout(() => {
            this.buzzerTesting = false;
            alert('Sinyal buzzer uji coba dikirim ke 11 node saung.');
        }, 700);
    },

    restartNodes() {
        if(confirm('Apakah Anda yakin ingin me-restart semua 11 node hardware ESP32 di saung?')) {
            this.restarting = true;
            setTimeout(() => {
                this.isSaving = false;
                this.saveSuccess = true;
                setTimeout(() => this.saveSuccess = false, 3000);
            }, 800);
        },

        testPing() {
            this.pinging = true;
            setTimeout(() => {
                this.pinging = false;
                this.pingSuccess = true;
                setTimeout(() => this.pingSuccess = false, 3000);
            }, 1200);
        },

        testBuzzer() {
            this.buzzerTesting = true;
            setTimeout(() => {
                this.buzzerTesting = false;
                alert('Sinyal buzzer uji coba dikirim ke 11 node saung.');
            }, 700);
        },

        restartNodes() {
            if (confirm('Apakah Anda yakin ingin me-restart semua 11 node hardware ESP32 di saung?')) {
                this.restarting = true;
                setTimeout(() => {
                    this.restarting = false;
                    alert('Semua node ESP32 berhasil di-reboot.');
                }, 1500);
            }
        },

        printSample() {
            this.isPrintingSample = true;
            setTimeout(() => {
                this.isPrintingSample = false;
                alert('Perintah uji cetak struk sampel terkirim ke EPSON TM-T82X (192.168.1.150:9100).');
            }, 1000);
        }
    },

    printSample() {
        this.isPrintingSample = true;
        setTimeout(() => {
            this.isPrintingSample = false;
            alert('Perintah uji cetak struk sampel terkirim ke EPSON TM-T82X (192.168.1.150:9100).');
        }, 1000);
    },

    testMidtransApi() {
        this.testingMidtrans = true;
        setTimeout(() => {
            this.testingMidtrans = false;
            alert('Koneksi Midtrans API berhasil diverifikasi. Environment: ' + this.midtransEnv.toUpperCase() + ' (HTTP 200 OK)');
        }, 1000);
    }
}">

    <!-- Top Header -->
    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI SAUNG LESEHAN</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">Pengaturan Sistem</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E5E2DB] text-[10px] font-bold text-[#0B4D2B]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#00341A] animate-pulse"></span>
                <span class="tracking-wide uppercase">SISTEM KONSOL v2.4.1 • POSTGRESQL &amp; REVERB</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Konfigurasi Sistem, Hardware IoT &amp; Integrasi Resto
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Kelola konektivitas MQTT broker ESP32, konfigurasi printer thermal kasir, kunci sandbox Midtrans QRIS, dan parameter operasional saung secara terpusat.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button 
                    @click="testPing()"
                    :disabled="pinging"
                    class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] active:scale-95 text-[#7A5900] text-xs font-semibold flex items-center gap-2 shadow-xs transition-all cursor-pointer disabled:opacity-60">
                    <svg class="w-4 h-4 text-[#7A5900]" :class="{ 'animate-spin': pinging }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="pinging ? 'Menguji Layanan...' : 'Uji Koneksi Layanan'">Uji Koneksi Layanan</span>
                </button>

                <button 
                    @click="alert('Cache query, konfigurasi, dan view blade sistem berhasil dibersihkan.')"
                    class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] active:scale-95 text-[#404941] text-xs font-semibold flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Cache</span>
                </button>

                <button 
                    @click="triggerSave()"
                    :disabled="isSaving"
                    class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white text-xs font-semibold flex items-center gap-2 shadow-md transition-all cursor-pointer disabled:opacity-75">
                    <svg x-show="!isSaving && !saveSuccess" class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="isSaving" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <svg x-show="saveSuccess" class="w-4 h-4 text-[#B1F1C2]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="isSaving ? 'Menyimpan...' : (saveSuccess ? 'Tersimpan!' : 'Simpan Semua')">Simpan Semua</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Status Koneksi -->
    <div x-show="pingSuccess" x-transition class="p-4 rounded-xl bg-[#B1F1C2]/60 border border-[#0B4D2B]/20 flex items-center justify-between text-xs font-semibold text-[#00210E]">
        <div class="flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
            <span>Semua 4 Layanan Utama Beroperasi Normal: EMQX 12ms, Reverb 14 Peers Connected, Midtrans Sandbox OK, EPSON Ready.</span>
        </div>
        <button @click="pingSuccess = false" class="text-[#00341A] font-bold hover:underline cursor-pointer">Tutup</button>
    </div>

    <!-- 4 Top Stat Cards (Harmonized with Laporan Penjualan) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- CARD 1: MQTT EMQX Broker -->
        <div class="bg-[#F6F3EC] rounded-2xl p-5 sm:p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[185px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">MQTT EMQX BROKER</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">ESP32 Saung Gateway</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/50 flex items-center justify-center text-[#0B4D2B] shrink-0">
                    <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>

            <div class="flex items-baseline gap-2">
                <span class="font-display font-bold text-xl sm:text-2xl text-[#00341A] tracking-tight leading-tight whitespace-nowrap">
                    Port 1883
                </span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold whitespace-nowrap shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                    <span>ONLINE</span>
                </span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="text-[#404941] text-[10px] font-bold flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                    Latensi: 12ms
                </span>
                <span class="font-mono text-[10px] font-bold text-[#707971] whitespace-nowrap shrink-0">resto/situawi/#</span>
            </div>

            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-[#00341A]/5 blur-xl pointer-events-none"></div>
        </div>

        <!-- CARD 2: WebSockets Host -->
        <div class="bg-[#F6F3EC] rounded-2xl p-5 sm:p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[185px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">WEBSOCKETS HOST</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Real-time Push Server</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/60 flex items-center justify-center text-[#7A5900] shrink-0">
                    <svg class="w-5 h-5 text-[#261900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <button @click="pingSuccess = false" class="text-[#00341A] font-bold hover:underline">Tutup</button>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="font-display font-bold text-xl sm:text-2xl text-[#1C1C18] tracking-tight leading-tight whitespace-nowrap">
                    Laravel Reverb
                </span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="text-[10px] font-bold text-[#00341A] truncate">pos-channel • kds-stream</span>
                <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1]/50 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">14 Peers</span>
            </div>
        </div>

        <!-- CARD 3: Payment Gateway -->
        <div class="bg-[#F6F3EC] rounded-2xl p-5 sm:p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[185px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PAYMENT GATEWAY</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Konektivitas Transaksi QRIS</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#CBEF89]/60 flex items-center justify-center text-[#213200] shrink-0">
                    <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>

            <div class="flex items-baseline gap-2">
                <span class="font-display font-bold text-xl sm:text-2xl text-[#00341A] tracking-tight leading-tight whitespace-nowrap">
                    Midtrans QRIS
                </span>
                <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1] px-2 py-0.5 rounded uppercase whitespace-nowrap shrink-0" x-text="midtransEnv">Sandbox</span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="text-[10px] font-bold text-[#00341A] flex items-center gap-1 truncate">
                    <svg class="w-3.5 h-3.5 text-[#00341A] shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="truncate">API v2 Terverifikasi</span>
                </span>
                <span class="font-mono text-[10px] font-bold text-[#707971] whitespace-nowrap shrink-0">client-***</span>
            </div>
        </div>

        <!-- CARD 4: Printer Struk Utama -->
        <div class="bg-[#F6F3EC] rounded-2xl p-5 sm:p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[185px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PRINTER STRUK UTAMA</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">Thermal Kasir &amp; Dapur</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] flex items-center justify-center text-[#1C1C18] shrink-0">
                    <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="font-display font-bold text-xl sm:text-2xl text-[#1C1C18] tracking-tight leading-tight whitespace-nowrap">
                    EPSON TM-T82X
                </span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="font-mono text-[10px] font-bold text-[#1C1C18] whitespace-nowrap" title="192.168.1.150:9100">192.168.1.150</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold whitespace-nowrap shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                    <span>READY • LAN</span>
                </span>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/20 blur-xl pointer-events-none"></div>
        </div>
    </div>

    <!-- Tab Pill Navigation -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <button 
            @click="activeTab = 'iot'"
            :class="activeTab === 'iot' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <span>1. Integrasi Hardware IoT Meja</span>
        </button>

        <button 
            @click="activeTab = 'printer'"
            :class="activeTab === 'printer' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>2. Kasir &amp; Printer Thermal</span>
        </button>

        <button 
            @click="activeTab = 'midtrans'"
            :class="activeTab === 'midtrans' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>3. Pembayaran Midtrans QRIS</span>
        </button>

        <button 
            @click="activeTab = 'tax'"
            :class="activeTab === 'tax' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>4. Profil Resto &amp; Pajak PB1</span>
        </button>

        <button 
            @click="activeTab = 'backup'"
            :class="activeTab === 'backup' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            <span>5. Database &amp; Cadangan Data</span>
        </button>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- LEFT COLUMN: Tab Panels (lg:col-span-8) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- ========================================== -->
            <!-- TAB 1: INTEGRASI HARDWARE IOT MEJA (ESP32) -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'iot'" x-transition class="bg-[#F6F3EC] rounded-2xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 space-y-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-[#C0C9BF]/20">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#1C1C18]">Konfigurasi Modul IoT Table Node</h2>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">ESP32 TABLE COMPANION HARDWARE CONTROLLER</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#B1F1C2]/60 text-[#00341A] text-[10px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-[#00341A] animate-ping"></span>
                        11 NODE AKTIF
                    </span>
                </div>

                <!-- Subnet Mesh Banner -->
                <div class="p-4 rounded-xl bg-white border border-[#C0C9BF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider block">SUBNET DEDICATED MESH</span>
                            <span class="text-base font-bold text-[#1C1C18]">SituAwi_CoreOps_5G</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="bg-[#FCF9F2] rounded-lg px-3 py-1.5 border border-[#C0C9BF]/30">
                            <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">GATEWAY IP</span>
                            <span class="font-mono text-xs font-bold text-[#1C1C18]">192.168.4.1/24</span>
                        </div>
                        <div class="bg-[#FCF9F2] rounded-lg px-3 py-1.5 border border-[#C0C9BF]/30">
                            <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">MQTT KEEP-ALIVE</span>
                            <span class="font-mono text-xs font-bold text-[#00341A]">15 Detik</span>
                        </div>
                    </div>
                </div>

                <!-- Sensor Sentuh Parameters -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        <h3 class="text-sm font-bold text-[#1C1C18]">Parameter Sensor Sentuh Kapasitif (TTP223)</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[#1C1C18]">Durasi Buzzer Feedback</label>
                                <svg class="w-4 h-4 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-xs text-[#404941] leading-relaxed">
                                Durasi bunyi feedback piezo saat tamu menekan tombol saung.
                            </p>
                            <div class="flex items-center gap-3 pt-1">
                                <input 
                                    type="number" 
                                    min="1" 
                                    max="10" 
                                    x-model="buzzerDuration"
                                    class="w-20 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <span class="text-xs font-semibold text-[#707971]">Detik (2 - 4s)</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-[#1C1C18]">Cooldown Anti-Spam Tombol</label>
                                <svg class="w-4 h-4 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <p class="text-xs text-[#404941] leading-relaxed">
                                Jeda minimum sebelum sensor dapat dipicu kembali oleh tamu meja.
                            </p>
                            <div class="flex items-center gap-3 pt-1">
                                <input 
                                    type="number" 
                                    min="1" 
                                    max="60" 
                                    x-model="antiSpamCooldown"
                                    class="w-20 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <span class="text-xs font-semibold text-[#707971]">Detik jeda proteksi</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pesan Layar LCD (Clean no-overlap layout) -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-2.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-[#1C1C18]">Pesan Tampilan Layar LCD 16x2 I2C</label>
                        <span class="font-mono text-[10px] font-bold text-[#707971] bg-[#F0EEE7] px-2 py-0.5 rounded">Maks 16 Karakter</span>
                    </div>
                    <p class="text-xs text-[#404941]">
                        Pesan konfirmasi visual pada unit meja saat sinyal berhasil terkirim ke POS Pelayan.
                    </p>
                    <div class="pt-1">
                        <input 
                            type="text" 
                            maxlength="16"
                            x-model="lcdMessage"
                            class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-4 py-2.5 font-mono text-sm text-[#1C1C18] font-medium focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                    </div>
                </div>

                <!-- Keamanan Dynamic QR -->
                <div class="pt-2 border-t border-[#C0C9BF]/20 space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h3 class="text-sm font-bold text-[#1C1C18]">Keamanan Dynamic QR &amp; Anti-Pemalsuan Token</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                            <label class="text-xs font-bold text-[#1C1C18]">Masa Berlaku Sesi Tamu</label>
                            <p class="text-xs text-[#404941] leading-relaxed">
                                Sesi otomatis kadaluarsa setelah periode tanpa transaksi menu.
                            </p>
                            <div class="flex items-center gap-3 pt-1">
                                <input 
                                    type="number" 
                                    min="15" 
                                    max="360" 
                                    x-model="sessionExpiry"
                                    class="w-24 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <span class="text-xs font-semibold text-[#707971]">Menit sejak scan</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                            <label class="text-xs font-bold text-[#1C1C18]">Tingkat Enkripsi Anti-Order Palsu</label>
                            <p class="text-xs text-[#404941] leading-relaxed">
                                Kriptografi token URL QR yang dicetak pada dudukan saung.
                            </p>
                            <div class="pt-1">
                                <select 
                                    x-model="encryptionLevel"
                                    class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                    <option value="AES-256 HMAC Token (Standard Enterprise)">AES-256 HMAC Token (Standard Enterprise)</option>
                                    <option value="ChaCha20-Poly1305 Fast Hardware">ChaCha20-Poly1305 Fast Hardware</option>
                                    <option value="HMAC-SHA256 Basic Session Key">HMAC-SHA256 Basic Session Key</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aksi Diagnostik Hardware -->
                <div class="pt-2 border-t border-[#C0C9BF]/20 space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <h3 class="text-xs font-bold text-[#1C1C18] uppercase tracking-wider">Aksi Diagnostik Hardware Remote</h3>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button 
                            @click="testPing()"
                            :disabled="pinging"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-[#F0EEE7] border border-[#C0C9BF]/30 text-[#1C1C18] text-xs font-semibold shadow-2xs transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                            <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span>Ping 11 Node ESP32</span>
                        </button>

                        <button 
                            @click="testBuzzer()"
                            :disabled="buzzerTesting"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFDEA1]/60 hover:bg-[#FECE66] border border-[#FECE66]/50 text-[#261900] text-xs font-semibold shadow-2xs transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                            <svg class="w-3.5 h-3.5 text-[#261900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            <span>Kirim Sinyal Buzzer (Test)</span>
                        </button>

                        <button 
                            @click="restartNodes()"
                            :disabled="restarting"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFDAD6]/70 hover:bg-[#FFB4AB] border border-[#FFB4AB] text-[#93000A] text-xs font-semibold shadow-2xs transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                            <svg class="w-3.5 h-3.5 text-[#93000A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span>Restart Semua Node</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: KASIR & PRINTER THERMAL ESC/POS     -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'printer'" x-transition class="bg-[#F6F3EC] rounded-2xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 space-y-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-[#C0C9BF]/20">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#1C1C18]">Konfigurasi Struk &amp; Printer Kasir</h2>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">THERMAL PRINTER ESC/POS UTAMA KASIR &amp; DAPUR</span>
                        </div>
                    </div>
                    <span class="font-mono text-[11px] font-bold text-[#0B4D2B] bg-[#B1F1C2]/60 px-3 py-1 rounded-full">
                        80MM THERMAL • RAW TCP
                    </span>
                </div>

                <!-- Network Config -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-1.5 shadow-2xs">
                        <label class="text-xs font-bold text-[#1C1C18]">IP Address Printer Kasir (LAN)</label>
                        <p class="text-xs text-[#707971]">Alamat IP statis printer EPSON di subnet lokal kasir.</p>
                        <input 
                            type="text" 
                            x-model="printerIp"
                            class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                    </div>

                    <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-1.5 shadow-2xs">
                        <label class="text-xs font-bold text-[#1C1C18]">Port Cetak Socket</label>
                        <p class="text-xs text-[#707971]">Port standar cetak thermal ESC/POS (default 9100).</p>
                        <input 
                            type="text" 
                            x-model="printerPort"
                            class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                    </div>
                </div>

                <!-- Layout Kertas & Header / Footer -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-4 shadow-2xs">
                    <h3 class="text-sm font-bold text-[#1C1C18]">Format &amp; Teks Struk Belanja</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Ukuran Kertas Thermal</label>
                            <select 
                                x-model="paperSize"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <option value="80mm">80mm Thermal (Standar Resto)</option>
                                <option value="58mm">58mm Thermal (Mini Kasir)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Header Struk Belanja</label>
                            <input 
                                type="text" 
                                x-model="receiptHeader"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Footer Salam Penutup</label>
                            <input 
                                type="text" 
                                x-model="receiptFooter"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>
                    </div>

                    <!-- Printer Feature Toggles -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-[#C0C9BF]/20">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[#1C1C18]">
                            <input type="checkbox" x-model="autoCutter" class="rounded text-[#0B4D2B] focus:ring-0 w-4 h-4">
                            <span>Auto-Cutter Aktif</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[#1C1C18]">
                            <input type="checkbox" x-model="printLogo" class="rounded text-[#0B4D2B] focus:ring-0 w-4 h-4">
                            <span>Cetak Logo Resto</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[#1C1C18]">
                            <input type="checkbox" x-model="printQrCode" class="rounded text-[#0B4D2B] focus:ring-0 w-4 h-4">
                            <span>Cetak QR Validasi</span>
                        </label>
                    </div>
                </div>

                <!-- Uji Cetak Struk Banner -->
                <div class="p-4 rounded-xl bg-white border border-[#C0C9BF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#1C1C18]">Uji Cetak Struk Sampel Kasir</h4>
                            <p class="text-xs text-[#404941]">Mencetak format struk dummy untuk kalibrasi margin, logo, dan auto-cutter printer.</p>
                        </div>
                    </div>

                    <button 
                        @click="printSample()"
                        :disabled="isPrintingSample"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold shadow-xs transition-all cursor-pointer whitespace-nowrap active:scale-95 disabled:opacity-60">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span x-text="isPrintingSample ? 'Mencetak...' : 'Cetak Halaman Uji Coba'">Cetak Halaman Uji Coba</span>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: PEMBAYARAN MIDTRANS QRIS           -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'midtrans'" x-transition class="bg-[#F6F3EC] rounded-2xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 space-y-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-[#C0C9BF]/20">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#1C1C18]">Integrasi Pembayaran Digital &amp; Gateway Midtrans</h2>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">SNAP QRIS DYNAMIC &amp; VIRTUAL ACCOUNT ENGINE</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1] px-3 py-1 rounded-full uppercase" x-text="midtransEnv">SANDBOX</span>
                </div>

                <!-- Environment Selector -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-[#1C1C18]">Mode Lingkungan Pembayaran (Environment)</h3>
                            <p class="text-xs text-[#707971]">Gunakan Sandbox untuk simulasi kasir, dan beralih ke Production saat live menerima uang riil.</p>
                        </div>
                    </div>

                    <div class="bg-[#F0EEE7] p-1 rounded-xl grid grid-cols-2 gap-1 max-w-sm">
                        <button 
                            @click="midtransEnv = 'sandbox'"
                            :class="midtransEnv === 'sandbox' ? 'bg-white text-[#0B4D2B] shadow-xs font-bold' : 'text-[#707971] hover:text-[#1C1C18] font-medium'"
                            class="py-2 text-xs rounded-lg transition-all text-center cursor-pointer">
                            Sandbox (Simulasi)
                        </button>
                        <button 
                            @click="midtransEnv = 'production'"
                            :class="midtransEnv === 'production' ? 'bg-[#00341A] text-white shadow-xs font-bold' : 'text-[#707971] hover:text-[#1C1C18] font-medium'"
                            class="py-2 text-xs rounded-lg transition-all text-center cursor-pointer">
                            Production (Live QRIS)
                        </button>
                    </div>
                </div>

                <!-- API Keys Credentials -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-4 shadow-2xs">
                    <h3 class="text-sm font-bold text-[#1C1C18]">Kredensial API Merchant Midtrans</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Merchant ID</label>
                            <input 
                                type="text" 
                                x-model="midtransMerchantId"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Client Key</label>
                            <input 
                                type="text" 
                                x-model="midtransClientKey"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-[#1C1C18]">Server Key (Rahasia)</label>
                        <input 
                            type="password" 
                            x-model="midtransServerKey"
                            class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                    </div>
                </div>

                <!-- Webhook Callback URL -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-2.5 shadow-2xs">
                    <label class="text-xs font-bold text-[#1C1C18]">URL Webhook Notifikasi Transaksi (Notification URL)</label>
                    <p class="text-xs text-[#404941]">
                        Salin URL ini ke dashboard Midtrans &gt; Settings &gt; Configuration &gt; Payment Notification URL.
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <input 
                            type="text" 
                            readonly 
                            value="{{ url('/midtrans/notification') }}"
                            class="flex-1 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-4 py-2.5 font-mono text-xs text-[#0B4D2B] font-bold shadow-inner">
                        <button 
                            @click="navigator.clipboard.writeText('{{ url('/midtrans/notification') }}'); alert('URL Webhook berhasil disalin ke clipboard.')"
                            class="px-4 py-2.5 rounded-xl bg-[#E5E2DB] hover:bg-[#ded9cf] text-[#00341A] text-xs font-bold transition-all cursor-pointer whitespace-nowrap">
                            Salin URL
                        </button>
                    </div>
                </div>

                <!-- QRIS Expiry & Action -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-2 shadow-2xs">
                        <label class="text-xs font-bold text-[#1C1C18]">Batas Waktu Pembayaran QRIS (Expiry)</label>
                        <p class="text-xs text-[#707971]">Waktu kedaluwarsa QRIS dinamis setelah di-generate tamu.</p>
                        <div class="flex items-center gap-3 pt-1">
                            <input 
                                type="number" 
                                min="5" 
                                max="60" 
                                x-model="qrisExpiry"
                                class="w-20 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                            <span class="text-xs font-semibold text-[#707971]">Menit (Standar 15m)</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                        <div>
                            <label class="text-xs font-bold text-[#1C1C18]">Batalkan Order Kedaluwarsa</label>
                            <p class="text-xs text-[#707971] mt-1">Otomatis batalkan tiket pesanan jika QRIS habis masa berlakunya.</p>
                        </div>
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[#1C1C18]">
                            <input type="checkbox" x-model="autoCancelExpired" class="rounded text-[#0B4D2B] focus:ring-0 w-4 h-4">
                            <span>Aktifkan Pembatalan Otomatis</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button 
                        @click="testMidtransApi()"
                        :disabled="testingMidtrans"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold shadow-xs transition-all cursor-pointer active:scale-95 disabled:opacity-60">
                        <svg class="w-4 h-4 text-white" :class="{ 'animate-spin': testingMidtrans }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-text="testingMidtrans ? 'Memeriksa API...' : 'Uji Koneksi API Midtrans'">Uji Koneksi API Midtrans</span>
                    </button>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: PROFIL RESTO & PAJAK PB1           -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'tax'" x-transition class="bg-[#F6F3EC] rounded-2xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 space-y-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-[#C0C9BF]/20">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#1C1C18]">Parameter Pajak Restoran (PB1) &amp; Profil Usaha</h2>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">KONFIGURASI FISKAL OPERASIONAL KASIR &amp; DATA BISNIS</span>
                        </div>
                    </div>
                </div>

                <!-- Profil Usaha Restoran -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-4 shadow-2xs">
                    <h3 class="text-sm font-bold text-[#1C1C18]">Identitas Resmi Restoran</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Nama Restoran</label>
                            <input 
                                type="text" 
                                x-model="restoName"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">NPWPD Restoran (Bapenda Kab. Bandung)</label>
                            <input 
                                type="text" 
                                x-model="npwpd"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Alamat Lengkap</label>
                            <input 
                                type="text" 
                                x-model="restoAddress"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Nomor Telepon Kasir</label>
                            <input 
                                type="text" 
                                x-model="restoPhone"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>
                    </div>
                </div>

                <!-- Parameter PB1 & Service Charge -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Pajak PB1 Card -->
                    <div class="bg-white rounded-xl p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-4 shadow-2xs">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-[#1C1C18]">Pajak PB1 Restoran (10%)</h3>
                                <p class="text-xs text-[#404941] leading-relaxed">
                                    Pajak pembangunan daerah resmi kabupaten Bandung untuk konsumsi restoran.
                                </p>
                            </div>
                            
                            <button 
                                @click="pb1Enabled = !pb1Enabled"
                                type="button" 
                                :class="pb1Enabled ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-hidden">
                                <span 
                                    :class="pb1Enabled ? 'translate-x-5' : 'translate-x-0.5'"
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                            </button>
                        </div>

                        <div class="flex items-center gap-3 pt-2 border-t border-[#C0C9BF]/20">
                            <input 
                                type="number" 
                                x-model="pb1Rate"
                                :disabled="!pb1Enabled"
                                class="w-20 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner disabled:opacity-50">
                            <span class="text-xs font-semibold text-[#404941]">% Tarif Efektif PB1</span>
                        </div>
                    </div>

                    <!-- Service Charge Card -->
                    <div class="bg-white rounded-xl p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-4 shadow-2xs" :class="{ 'opacity-75': !serviceChargeEnabled }">
                        <div class="flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold text-[#1C1C18]">Biaya Layanan (Service Charge)</h3>
                                <p class="text-xs text-[#404941] leading-relaxed">
                                    Biaya tambahan opsional jasa pelayanan untuk tim saung &amp; operasional meja.
                                </p>
                            </div>
                            
                            <button 
                                @click="serviceChargeEnabled = !serviceChargeEnabled"
                                type="button" 
                                :class="serviceChargeEnabled ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-hidden">
                                <span 
                                    :class="serviceChargeEnabled ? 'translate-x-5' : 'translate-x-0.5'"
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                            </button>
                        </div>

                        <div class="flex items-center gap-3 pt-2 border-t border-[#C0C9BF]/20">
                            <input 
                                type="number" 
                                x-model="serviceChargeRate"
                                :disabled="!serviceChargeEnabled"
                                class="w-20 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner disabled:opacity-50">
                            <span class="text-xs font-semibold" :class="serviceChargeEnabled ? 'text-[#00341A]' : 'text-[#707971]'">
                                <span x-text="serviceChargeEnabled ? '% Tarif Aktif' : '% Dinonaktifkan'">% Dinonaktifkan</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Aturan Pembulatan Harga Akhir -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-2 shadow-2xs">
                    <label class="text-xs font-bold text-[#1C1C18]">Aturan Pembulatan Nilai Akhir Transaksi</label>
                    <p class="text-xs text-[#707971]">Membulatkan nominal total belanja untuk mempermudah kembalian kasir.</p>
                    <div class="pt-1 max-w-sm">
                        <select 
                            x-model="roundingRule"
                            class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                            <option value="none">Tanpa Pembulatan (Nominal Asli)</option>
                            <option value="100">Pembulatan ke Rp 100 Terdekat</option>
                            <option value="500">Pembulatan ke Rp 500 Terdekat</option>
                            <option value="1000">Pembulatan ke Rp 1.000 Terdekat</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 5: DATABASE & CADANGAN DATA           -->
            <!-- ========================================== -->
            <div x-show="activeTab === 'backup'" x-transition class="bg-[#F6F3EC] rounded-2xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 space-y-6">
                
                <div class="flex items-center justify-between pb-4 border-b border-[#C0C9BF]/20">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#1C1C18]">Manajemen Basis Data &amp; Pencadangan Otomatis</h2>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">POSTGRESQL 16 SNAPSHOT &amp; CLOUD ARCHIVE</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#B1F1C2]/60 text-[#00341A] text-[10px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                        HEALTHY
                    </span>
                </div>

                <!-- Database Metrics Banner -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">ENGINE</span>
                        <span class="font-mono text-xs font-bold text-[#1C1C18]">PostgreSQL 16.2</span>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">UKURAN DB</span>
                        <span class="font-mono text-xs font-bold text-[#00341A]">42.8 MB</span>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">TOTAL TABEL</span>
                        <span class="font-mono text-xs font-bold text-[#1C1C18]">18 Tabel</span>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">POOL KONEKSI</span>
                        <span class="font-mono text-xs font-bold text-[#7A5900]">6/20 Aktif</span>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-4 shadow-2xs">
                    <h3 class="text-sm font-bold text-[#1C1C18]">Jadwal Pencadangan Otomatis</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Frekuensi Snapshot Harian</label>
                            <select class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <option>Setiap Penutupan Shift (23:59 WIB)</option>
                                <option>2x Sehari (12:00 WIB &amp; 23:59 WIB)</option>
                                <option>Setiap 6 Jam</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Masa Retensi Cadangan Lokal</label>
                            <select class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <option>Simpan 30 Hari Terakhir</option>
                                <option>Simpan 60 Hari Terakhir</option>
                                <option>Simpan 90 Hari Terakhir</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#C0C9BF]/20">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-[#1C1C18]">
                            <input type="checkbox" checked class="rounded text-[#0B4D2B] focus:ring-0 w-4 h-4">
                            <span>Kirim Salinan Terenkripsi Otomatis ke AWS S3 Cloud Bucket (situawi-pos-backup)</span>
                        </label>
                    </div>
                </div>

                <!-- Backup Actions -->
                <div class="p-4 rounded-xl bg-white border border-[#C0C9BF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div>
                        <h4 class="text-xs font-bold text-[#1C1C18]">Unduh Cadangan Basis Data Lengkap (.sql.gz)</h4>
                        <p class="text-xs text-[#404941]">Snapshot basis data terkompresi berisi transaksi, staf, meja, dan rekap shift.</p>
                    </div>

                    <button 
                        @click="alert('Mengunduh arsip pencadangan basis data Situ Awi (situawi_backup_' + new Date().toISOString().slice(0,10) + '.sql.gz)...')"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold shadow-xs transition-all cursor-pointer whitespace-nowrap active:scale-95">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Snapshot (.sql.gz)</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Contextual Sidebar Widgets (lg:col-span-4) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- ============================== -->
            <!-- WIDGETS FOR TAB 1: IOT         -->
            <!-- ============================== -->
            <template x-if="activeTab === 'iot'">
                <div class="space-y-6">
                    <!-- Topologi Node Saung -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Topologi Node Saung</h3>
                            <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>

                        <p class="text-xs text-[#404941] leading-relaxed">
                            Peta sebaran koneksi 11 unit ESP32 yang terhubung ke broker EMQX lokal dan diteruskan ke KDS Dapur Utama.
                        </p>

                        <!-- Grid 11 Node -->
                        <div class="bg-white rounded-xl p-3 border border-[#C0C9BF]/20 grid grid-cols-4 gap-2">
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-01</span>
                                <span class="font-mono text-[9px] text-[#707971]">11ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-02</span>
                                <span class="font-mono text-[9px] text-[#707971]">14ms</span>
                            </div>
                            <div class="bg-[#FFDEA1]/30 rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#FECE66]">
                                <span class="w-2 h-2 rounded-full bg-[#7A5900] animate-ping mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-03</span>
                                <span class="font-mono text-[9px] font-bold text-[#7A5900]">Call</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-04</span>
                                <span class="font-mono text-[9px] text-[#707971]">9ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-05</span>
                                <span class="font-mono text-[9px] text-[#707971]">12ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-06</span>
                                <span class="font-mono text-[9px] text-[#707971]">16ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-07</span>
                                <span class="font-mono text-[9px] text-[#707971]">10ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-08</span>
                                <span class="font-mono text-[9px] text-[#707971]">15ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-09</span>
                                <span class="font-mono text-[9px] text-[#707971]">13ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-10</span>
                                <span class="font-mono text-[9px] text-[#707971]">11ms</span>
                            </div>
                            <div class="bg-[#FCF9F2] rounded-lg p-2 flex flex-col items-center justify-center text-center border border-[#C0C9BF]/20">
                                <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#1C1C18]">S-11</span>
                                <span class="font-mono text-[9px] text-[#707971]">10ms</span>
                            </div>
                            <div class="bg-[#FFDEA1]/50 border border-[#FECE66] rounded-lg p-2 flex flex-col items-center justify-center text-center">
                                <span class="w-2 h-2 rounded-full bg-[#7A5900] mb-1"></span>
                                <span class="text-[11px] font-bold text-[#261900]">VIP-1</span>
                                <span class="font-mono text-[9px] font-bold text-[#261900]">8ms</span>
                            </div>
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-[#707971] tracking-wider uppercase">11 DARI 11 NODE TERHUBUNG</span>
                                <span class="text-[#00341A]">Rata-rata: 12.4ms</span>
                            </div>
                            <div class="w-full bg-[#E5E2DB] h-2 rounded-full overflow-hidden">
                                <div class="w-full bg-[#0B4D2B] h-full rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Spesifikasi Hardware Gateway -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">HARDWARE GATEWAY</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">MCU Board:</span>
                                <span class="font-bold text-[#1C1C18]">ESP32-WROOM-32D</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Antenna:</span>
                                <span class="font-bold text-[#1C1C18]">Omni 5dBi High-Gain</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">MQTT Broker:</span>
                                <span class="font-bold text-[#00341A]">EMQX v5 Enterprise</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Topic Root:</span>
                                <span class="font-mono font-bold text-[#1C1C18]">resto/situawi/#</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ============================== -->
            <!-- WIDGETS FOR TAB 2: PRINTER     -->
            <!-- ============================== -->
            <template x-if="activeTab === 'printer'">
                <div class="space-y-6">
                    <!-- Status Printer Card -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Status Perangkat Printer</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                                TERHUBUNG
                            </span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/20 space-y-2 text-xs shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Model Unit:</span>
                                <span class="font-bold text-[#1C1C18]">EPSON TM-T82X LAN</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">IP Endpoint:</span>
                                <span class="font-mono font-bold text-[#1C1C18]" x-text="printerIp + ':' + printerPort">192.168.1.150:9100</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Kertas Roll:</span>
                                <span class="font-bold text-[#00341A]" x-text="paperSize + ' (Cukup)'">80mm (Cukup)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Auto Cutter:</span>
                                <span class="font-bold text-[#1C1C18]" x-text="autoCutter ? 'Aktif' : 'Non-aktif'">Aktif</span>
                            </div>
                        </div>

                    <!-- Live Preview Struk Kasir -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">PREVIEW FORMAT STRUK</h4>
                            <span class="text-[9px] font-mono text-[#707971]" x-text="paperSize">80mm</span>
                        </div>

                        <!-- Thermal Paper Slip Mockup -->
                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/30 font-mono text-[10px] text-[#1C1C18] space-y-2 shadow-sm">
                            <div class="text-center border-b border-dashed border-[#C0C9BF] pb-2 space-y-0.5">
                                <div class="font-bold text-xs" x-text="receiptHeader">Saung Situ Awi - Ciwidey</div>
                                <div class="text-[9px] text-[#707971]" x-text="restoAddress">Jl. Raya Ciwidey No. 25, Rancabali</div>
                                <div class="text-[9px] text-[#707971]">Telp: 0812-3456-7890</div>
                            </div>

                            <div class="flex justify-between text-[9px] text-[#707971]">
                                <span>Meja: S-04 (Saung 4)</span>
                                <span>No: #SA-260901</span>
                            </div>

                            <div class="border-t border-b border-dashed border-[#C0C9BF] py-1.5 space-y-1">
                                <div class="flex justify-between">
                                    <span>1x Nasi Liwet Kastrol</span>
                                    <span>Rp 65.000</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>1x Gurame Bakar Cobek</span>
                                    <span>Rp 85.000</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>2x Es Teh Manis</span>
                                    <span>Rp 16.000</span>
                                </div>
                            </div>

                            <div class="space-y-0.5 pt-1">
                                <div class="flex justify-between">
                                    <span>Subtotal:</span>
                                    <span>Rp 166.000</span>
                                </div>
                                <div class="flex justify-between text-[#00341A]" x-show="pb1Enabled">
                                    <span x-text="'PB1 (' + pb1Rate + '%):'">PB1 (10%):</span>
                                    <span x-text="'Rp ' + (166000 * pb1Rate / 100).toLocaleString('id-ID')">Rp 16.600</span>
                                </div>
                                <div class="flex justify-between font-bold text-xs pt-1 border-t border-dashed border-[#C0C9BF]">
                                    <span>TOTAL:</span>
                                    <span x-text="'Rp ' + (pb1Enabled ? 166000 * (1 + pb1Rate / 100) : 166000).toLocaleString('id-ID')">Rp 182.600</span>
                                </div>
                            </div>

                            <div class="text-center pt-2 border-t border-dashed border-[#C0C9BF] text-[9px] text-[#707971]" x-text="receiptFooter">
                                Hatur Nuhun Parantos Sumping
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ============================== -->
            <!-- WIDGETS FOR TAB 3: MIDTRANS    -->
            <!-- ============================== -->
            <template x-if="activeTab === 'midtrans'">
                <div class="space-y-6">
                    <!-- Status Akun Midtrans -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Status Akun Midtrans</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                                TERVERIFIKASI
                            </span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/20 space-y-2 text-xs shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Mode Aktif:</span>
                                <span class="font-bold text-[#7A5900] uppercase" x-text="midtransEnv">SANDBOX</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Metode Utama:</span>
                                <span class="font-bold text-[#1C1C18]">GoPay QRIS Dynamic</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Settlement:</span>
                                <span class="font-bold text-[#00341A]">T+1 Hari Kerja</span>
                            </div>
                        </div>
                    </div>

                    <!-- Panduan Integrasi Webhook -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">PANDUAN KONFIGURASI WEBHOOK</h4>
                        <ol class="space-y-2 text-xs text-[#404941] list-decimal list-inside leading-relaxed">
                            <li>Buka portal <strong>dashboard.midtrans.com</strong>.</li>
                            <li>Masuk ke menu <strong>Settings &gt; Configuration</strong>.</li>
                            <li>Tempelkan Payment Notification URL di kolom yang tersedia.</li>
                            <li>Pilih append notification URL dan simpan perubahan.</li>
                        </ol>
                    </div>
                </div>
            </template>

            <!-- ============================== -->
            <!-- WIDGETS FOR TAB 4: TAX         -->
            <!-- ============================== -->
            <template x-if="activeTab === 'tax'">
                <div class="space-y-6">
                    <!-- Simulasi Perhitungan PB1 -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Simulasi Tagihan Kasir</h3>
                            <span class="text-[10px] font-bold text-[#00341A] bg-[#B1F1C2]/60 px-2 py-0.5 rounded">AUTO-CALCULATE</span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/20 space-y-2 text-xs shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Subtotal Makanan &amp; Minuman:</span>
                                <span class="font-bold text-[#1C1C18]">Rp 150.000</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]" x-text="'PB1 Restoran (' + (pb1Enabled ? pb1Rate : 0) + '%):'">PB1 Restoran (10%):</span>
                                <span class="font-bold" :class="pb1Enabled ? 'text-[#00341A]' : 'text-[#707971]'" x-text="'Rp ' + (pb1Enabled ? (150000 * pb1Rate / 100).toLocaleString('id-ID') : '0')">Rp 15.000</span>
                            </div>
                            <div class="flex items-center justify-between" x-show="serviceChargeEnabled">
                                <span class="text-[#707971]" x-text="'Service Charge (' + serviceChargeRate + '%):'">Service Charge:</span>
                                <span class="font-bold text-[#7A5900]" x-text="'Rp ' + (150000 * serviceChargeRate / 100).toLocaleString('id-ID')">Rp 7.500</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-[#C0C9BF]/30 font-bold text-sm text-[#00341A]">
                                <span>Total Tagihan Meja:</span>
                                <span x-text="'Rp ' + (150000 + (pb1Enabled ? 150000 * pb1Rate / 100 : 0) + (serviceChargeEnabled ? 150000 * serviceChargeRate / 100 : 0)).toLocaleString('id-ID')">Rp 165.000</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kepatuhan Fiskal Bapenda -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">KEPATUHAN FISKAL BAPENDA</h4>
                        <p class="text-xs text-[#404941] leading-relaxed">
                            Tarif PB1 10% dikonfigurasikan sesuai Peraturan Daerah Kabupaten Bandung tentang Pajak Restoran. Seluruh cetakan struk kasir menyertakan NPWPD resmi usaha.
                        </p>
                    </div>
                </div>
            </template>

            <!-- ============================== -->
            <!-- WIDGETS FOR TAB 5: BACKUP      -->
            <!-- ============================== -->
            <template x-if="activeTab === 'backup'">
                <div class="space-y-6">
                    <!-- Status Snapshot Terkini -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Snapshot Terkini</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                                TERBARU
                            </span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/20 space-y-2 text-xs shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Waktu Cadangan:</span>
                                <span class="font-mono font-bold text-[#1C1C18]">Hari ini, 00:01 WIB</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Ukuran File:</span>
                                <span class="font-mono font-bold text-[#1C1C18]">42.8 MB (.sql.gz)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Integritas:</span>
                                <span class="font-bold text-[#00341A]">SHA-256 Validated</span>
                            </div>
                        </div>
                    </div>

                    <!-- Riwayat Backup -->
                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">RIWAYAT 3 CADANGAN TERAKHIR</h4>
                        <div class="space-y-2 text-xs">
                            <div class="p-2.5 rounded-lg bg-white border border-[#C0C9BF]/20 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-[#1C1C18]">situawi_backup_20261015.sql.gz</div>
                                    <div class="text-[10px] text-[#707971]">Kemarin, 23:59 WIB • 42.1 MB</div>
                                </div>
                                <span class="text-[10px] font-bold text-[#00341A] bg-[#B1F1C2]/60 px-2 py-0.5 rounded">Sukses</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-white border border-[#C0C9BF]/20 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-[#1C1C18]">situawi_backup_20261014.sql.gz</div>
                                    <div class="text-[10px] text-[#707971]">14 Okt 2026, 23:59 WIB • 41.6 MB</div>
                                </div>
                                <span class="text-[10px] font-bold text-[#00341A] bg-[#B1F1C2]/60 px-2 py-0.5 rounded">Sukses</span>
                            </div>
                            <div class="p-2.5 rounded-lg bg-white border border-[#C0C9BF]/20 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-[#1C1C18]">situawi_backup_20261013.sql.gz</div>
                                    <div class="text-[10px] text-[#707971]">13 Okt 2026, 23:59 WIB • 41.2 MB</div>
                                </div>
                                <span class="text-[10px] font-bold text-[#00341A] bg-[#B1F1C2]/60 px-2 py-0.5 rounded">Sukses</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>

    </div>
@endsection
