@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & IoT - Situ Awi Saung Lesehan')

@section('content')
<div x-data="{
    activeTab: 'iot',
    isSaving: false,
    saveSuccess: false,
    pinging: false,
    pingSuccess: false,
    buzzerTesting: false,
    restarting: false,
    isPrintingSample: false,
    pb1Enabled: true,
    serviceChargeEnabled: false,
    midtransEnv: 'sandbox',
    buzzerDuration: 3,
    antiSpamCooldown: 5,
    lcdMessage: 'Memanggil Pelayan...',
    sessionExpiry: 120,
    encryptionLevel: 'AES-256 HMAC Token (Standard Enterprise)',
    paperSize: '80mm',
    receiptHeader: 'Saung Situ Awi - Ciwidey',
    receiptFooter: 'Hatur Nuhun Parantos Sumping',

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
        if(confirm('Apakah Anda yakin ingin me-restart semua 11 node hardware ESP32 di saung?')) {
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
}" class="relative min-h-screen pb-16">

    
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#00341A]/5 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute top-80 right-1/3 w-80 h-80 bg-[#FECE66]/10 rounded-full blur-2xl pointer-events-none -z-10"></div>

    <div class="p-6 sm:p-8 space-y-8 max-w-[1400px] mx-auto">

        
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-2">
            
            <div class="space-y-2 max-w-2xl">
                
                <div class="flex flex-wrap items-center gap-3">
                    <nav class="flex items-center gap-2 text-xs font-semibold text-[#707971]">
                        <span class="hover:text-[#0B4D2B] transition-colors">Situ Awi Saung Lesehan</span>
                        <span class="text-[#C0C9BF]">/</span>
                        <span class="text-[#00341A] font-bold">Pengaturan Sistem</span>
                    </nav>
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-[#E5E2DB] shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00341A] animate-pulse"></span>
                        <span class="text-[10px] font-bold text-[#0B4D2B] tracking-wider uppercase">SISTEM KONSOL v2.4.1 • POSTGRESQL & REVERB</span>
                    </div>
                </div>

                
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Konfigurasi Sistem, Hardware IoT & Integrasi Resto
                </h1>

                
                <p class="text-sm text-[#404941] leading-relaxed">
                    Kelola konektivitas MQTT broker ESP32, konfigurasi printer thermal kasir, kunci sandbox Midtrans QRIS, dan parameter operasional saung secara terpusat.
                </p>
            </div>

            
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                <button 
                    @click="testPing()"
                    :disabled="pinging"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#7A5900] text-xs font-semibold border border-transparent shadow-xs transition-all active:scale-95 disabled:opacity-60 cursor-pointer">
                    <svg class="w-4 h-4 text-[#7A5900]" :class="{ 'animate-spin': pinging }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="pinging ? 'Menguji Layanan...' : 'Uji Koneksi Semua Layanan'">Uji Koneksi Semua Layanan</span>
                </button>

                <button 
                    @click="alert('Cache query, konfig, dan view blade sistem berhasil dibersihkan.')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] text-xs font-semibold border border-transparent shadow-xs transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset Cache Sistem</span>
                </button>

                <button 
                    @click="triggerSave()"
                    :disabled="isSaving"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#00341A] hover:bg-[#0B4D2B] text-white text-xs font-semibold shadow-md transition-all active:scale-95 cursor-pointer disabled:opacity-75">
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
                    <span x-text="isSaving ? 'Menyimpan...' : (saveSuccess ? 'Tersimpan!' : 'Simpan Semua Perubahan')">Simpan Semua Perubahan</span>
                </button>
            </div>
        </div>

        
        <div x-show="pingSuccess" x-transition class="p-4 rounded-xl bg-[#B1F1C2]/50 border border-[#0B4D2B]/20 flex items-center justify-between text-xs font-semibold text-[#00210E]">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                <span>Semua 4 Layanan Utama Beroperasi Normal: EMQX 12ms, Reverb 14 Peers Connected, Midtrans Sandbox OK, EPSON Ready.</span>
            </div>
            <button @click="pingSuccess = false" class="text-[#00341A] font-bold hover:underline">Tutup</button>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <div class="bg-white rounded-xl p-5 shadow-xs border border-[#F0EEE7] relative overflow-hidden flex flex-col justify-between h-44 hover:shadow-md transition-shadow">
                <div class="absolute -top-10 -left-10 w-28 h-28 bg-[#00341A]/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/40 text-[#0B4D2B] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                        ONLINE (1883)
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">MQTT EMQX BROKER</span>
                    <h3 class="text-xl font-bold text-[#1C1C18] mt-0.5">ESP32 Gateway</h3>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#F0EEE7] text-xs">
                    <span class="text-[#404941] flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                        Latensi: 12ms
                    </span>
                    <span class="font-mono text-[11px] text-[#707971]">resto/situawi/#</span>
                </div>
            </div>

            
            <div class="bg-white rounded-xl p-5 shadow-xs border border-[#F0EEE7] relative overflow-hidden flex flex-col justify-between h-44 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-[#FECE66]/30 text-[#765600] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#261900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                        CONNECTED
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">WEBSOCKETS HOST</span>
                    <h3 class="text-xl font-bold text-[#1C1C18] mt-0.5">Laravel Reverb</h3>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#F0EEE7] text-xs">
                    <span class="text-[11px] text-[#00341A] font-semibold truncate">pos-channel • kds-stream</span>
                    <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1]/50 px-2 py-0.5 rounded-md">14 Peers</span>
                </div>
            </div>

            
            <div class="bg-white rounded-xl p-5 shadow-xs border border-[#F0EEE7] relative overflow-hidden flex flex-col justify-between h-44 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#EFC05A] text-[#261900] text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                        SANDBOX MODE
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">PAYMENT GATEWAY</span>
                    <h3 class="text-lg font-bold text-[#1C1C18] mt-0.5">Midtrans QRIS Snap</h3>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#F0EEE7] text-xs">
                    <span class="text-xs text-[#00341A] font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        API v2 OK
                    </span>
                    <span class="font-mono text-[11px] text-[#707971]">SB-Mid-client-***</span>
                </div>
            </div>

            
            <div class="bg-white rounded-xl p-5 shadow-xs border border-[#F0EEE7] relative overflow-hidden flex flex-col justify-between h-44 hover:shadow-md transition-shadow">
                <div class="absolute -bottom-6 -right-6 w-28 h-28 bg-[#FFDEA1]/20 rounded-full blur-xl pointer-events-none"></div>
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/40 text-[#0B4D2B] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                        READY
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">PRINTER STRUK UTAMA</span>
                    <h3 class="text-lg font-bold text-[#1C1C18] mt-0.5">EPSON TM-T82X</h3>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-[#F0EEE7] text-xs">
                    <span class="font-mono text-[11px] text-[#1C1C18] font-semibold">192.168.1.150:9100</span>
                    <span class="text-[10px] font-bold text-[#213200] bg-[#B1F1C2]/30 px-2 py-0.5 rounded-md">ESC/POS LAN</span>
                </div>
            </div>
        </div>

        
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
                <span>2. Kasir & Printer Thermal</span>
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
                <span>4. Profil Resto & Pajak PB1</span>
            </button>

            <button 
                @click="activeTab = 'backup'"
                :class="activeTab === 'backup' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
                <span>5. Database & Cadangan Data</span>
            </button>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            
            <div class="lg:col-span-8 space-y-6">

                
                <div x-show="activeTab === 'iot' || activeTab === 'all'" class="bg-white rounded-xl p-6 sm:p-8 shadow-xs border border-[#F0EEE7] space-y-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-[#F0EEE7]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-[#1C1C18]">Konfigurasi Modul IoT Table Node</h2>
                                <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">ESP32 TABLE COMPANION HARDWARE CONTROLLER</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#B1F1C2]/40 text-[#00341A] text-[10px] font-bold">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] animate-ping"></span>
                            11 NODE AKTIF
                        </span>
                    </div>

                    
                    <div class="p-4 rounded-xl bg-[#F6F3EC] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-full bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
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
                            <div class="bg-white rounded-lg px-3 py-1.5 shadow-2xs border border-[#F0EEE7]">
                                <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">GATEWAY IP</span>
                                <span class="font-mono text-xs font-bold text-[#1C1C18]">192.168.4.1/24</span>
                            </div>
                            <div class="bg-white rounded-lg px-3 py-1.5 shadow-2xs border border-[#F0EEE7]">
                                <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">MQTT KEEP-ALIVE</span>
                                <span class="font-mono text-xs font-bold text-[#00341A]">15 Detik</span>
                            </div>
                        </div>
                    </div>

                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                            <h3 class="text-sm font-bold text-[#1C1C18]">Parameter Sensor Sentuh Kapasitif (TTP223)</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <div class="bg-[#F6F3EC] rounded-xl p-4 flex flex-col justify-between gap-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-[#1C1C18]">Durasi Buzzer Feedback</label>
                                    <svg class="w-3.5 h-3.5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                        class="w-20 bg-white border border-[#E5E2DB] rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-none focus:border-[#00341A] shadow-inner">
                                    <span class="text-xs font-semibold text-[#707971]">Detik (2 - 4s)</span>
                                </div>
                            </div>

                            
                            <div class="bg-[#F6F3EC] rounded-xl p-4 flex flex-col justify-between gap-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-[#1C1C18]">Cooldown Anti-Spam Tombol</label>
                                    <svg class="w-3.5 h-3.5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                        class="w-20 bg-white border border-[#E5E2DB] rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-none focus:border-[#00341A] shadow-inner">
                                    <span class="text-xs font-semibold text-[#707971]">Detik jeda proteksi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-[#F6F3EC] rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[#1C1C18]">Pesan Tampilan Layar LCD 16x2 I2C</label>
                            <span class="font-mono text-[10px] font-bold text-[#707971]">LCD 1602 BLUE</span>
                        </div>
                        <p class="text-xs text-[#404941]">
                            Pesan konfirmasi visual pada unit meja saat sinyal berhasil terkirim ke POS Pelayan.
                        </p>
                        <div class="relative pt-1">
                            <input 
                                type="text" 
                                maxlength="16"
                                x-model="lcdMessage"
                                class="w-full bg-white border border-[#E5E2DB] rounded-xl px-4 py-2.5 font-mono text-sm text-[#1C1C18] font-medium pr-28 focus:outline-none focus:border-[#00341A] shadow-inner">
                            <span class="absolute right-3 top-3 text-[10px] font-mono font-bold text-[#213200] bg-[#F0EEE7] px-2 py-0.5 rounded">
                                Maks 16 Karakter
                            </span>
                        </div>
                    </div>

                    
                    <div class="pt-4 border-t border-[#F0EEE7] space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h3 class="text-sm font-bold text-[#1C1C18]">Keamanan Dynamic QR & Anti-Pemalsuan Token</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <div class="bg-[#F6F3EC] rounded-xl p-4 flex flex-col justify-between gap-3">
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
                                        class="w-24 bg-white border border-[#E5E2DB] rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-none focus:border-[#00341A] shadow-inner">
                                    <span class="text-xs font-semibold text-[#707971]">Menit sejak scan</span>
                                </div>
                            </div>

                            
                            <div class="bg-[#F6F3EC] rounded-xl p-4 flex flex-col justify-between gap-3">
                                <label class="text-xs font-bold text-[#1C1C18]">Tingkat Enkripsi Anti-Order Palsu</label>
                                <p class="text-xs text-[#404941] leading-relaxed">
                                    Kriptografi token URL QR yang dicetak pada dudukan saung.
                                </p>
                                <div class="pt-1">
                                    <select 
                                        x-model="encryptionLevel"
                                        class="w-full bg-white border border-[#E5E2DB] rounded-xl px-3 py-2 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:border-[#00341A] shadow-inner">
                                        <option value="AES-256 HMAC Token (Standard Enterprise)">AES-256 HMAC Token (Standard Enterprise)</option>
                                        <option value="ChaCha20-Poly1305 Fast Hardware">ChaCha20-Poly1305 Fast Hardware</option>
                                        <option value="HMAC-SHA256 Basic Session Key">HMAC-SHA256 Basic Session Key</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="pt-4 border-t border-[#F0EEE7] space-y-3">
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
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] text-xs font-semibold transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                                <svg class="w-3.5 h-3.5 text-[#1C1C18]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                <span>Ping 11 Node ESP32</span>
                            </button>

                            <button 
                                @click="testBuzzer()"
                                :disabled="buzzerTesting"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFDEA1]/50 hover:bg-[#FECE66]/70 text-[#261900] text-xs font-semibold transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                                <svg class="w-3.5 h-3.5 text-[#261900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                <span>Kirim Sinyal Buzzer (Test)</span>
                            </button>

                            <button 
                                @click="restartNodes()"
                                :disabled="restarting"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFDAD6] hover:bg-[#FFB4AB] text-[#93000A] text-xs font-semibold transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                                <svg class="w-3.5 h-3.5 text-[#93000A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                <span>Restart Semua Node</span>
                            </button>
                        </div>
                    </div>
                </div>

                
                <div x-show="activeTab === 'printer' || activeTab === 'all' || activeTab === 'iot'" class="bg-white rounded-xl p-6 sm:p-8 shadow-xs border border-[#F0EEE7] space-y-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-[#F0EEE7]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-[#FECE66] text-[#765600] flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#765600]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-[#1C1C18]">Konfigurasi Struk & Printer Kasir</h2>
                                <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">THERMAL PRINTER ESC/POS UTAMA KASIR</span>
                            </div>
                        </div>
                        <span class="font-mono text-[11px] font-bold text-[#707971] bg-[#F0EEE7] px-3 py-1 rounded-full">
                            80MM THERMAL
                        </span>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Ukuran Kertas Thermal</label>
                            <select 
                                x-model="paperSize"
                                class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:border-[#00341A] shadow-inner">
                                <option value="80mm">80mm Thermal (Standar Resto)</option>
                                <option value="58mm">58mm Thermal (Mini Kasir)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Header Struk Belanja</label>
                            <input 
                                type="text" 
                                x-model="receiptHeader"
                                class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:border-[#00341A] shadow-inner">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Footer Salam Penutup</label>
                            <input 
                                type="text" 
                                x-model="receiptFooter"
                                class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:border-[#00341A] shadow-inner">
                        </div>
                    </div>

                    
                    <div class="p-4 rounded-xl bg-[#F0EEE7] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-white text-[#707971] flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-[#F6F3EC] text-[#00341A] text-xs font-bold shadow-xs border border-[#E5E2DB] transition-all cursor-pointer whitespace-nowrap active:scale-95 disabled:opacity-60">
                            <svg class="w-4 h-4 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span x-text="isPrintingSample ? 'Mencetak...' : 'Cetak Halaman Uji Coba'">Cetak Halaman Uji Coba</span>
                        </button>
                    </div>
                </div>

                
                <div x-show="activeTab === 'tax' || activeTab === 'all' || activeTab === 'iot'" class="bg-white rounded-xl p-6 sm:p-8 shadow-xs border border-[#F0EEE7] space-y-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-[#F0EEE7]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-9 h-9 rounded-xl bg-[#B1F1C2] text-[#00210E] flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#00210E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-[#1C1C18]">Parameter Pajak Restoran (PB1) & Biaya Layanan</h2>
                                <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">KONFIGURASI FISKAL OPERASIONAL KASIR</span>
                            </div>
                        </div>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <div class="bg-[#F6F3EC] rounded-xl p-5 flex flex-col justify-between gap-4">
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
                                    :class="pb1Enabled ? 'bg-[#00341A]' : 'bg-[#E5E2DB]'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                                    <span 
                                        :class="pb1Enabled ? 'translate-x-5' : 'translate-x-0.5'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                                </button>
                            </div>

                            <div class="flex items-center gap-3 pt-2 border-t border-[#E5E2DB]/60">
                                <input 
                                    type="number" 
                                    value="10" 
                                    :disabled="!pb1Enabled"
                                    class="w-20 bg-white border border-[#E5E2DB] rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-none focus:border-[#00341A] shadow-inner disabled:opacity-50">
                                <span class="text-xs font-semibold text-[#404941]">% Tarif Efektif PB1</span>
                            </div>
                        </div>

                        
                        <div class="bg-[#F6F3EC] rounded-xl p-5 flex flex-col justify-between gap-4" :class="{ 'opacity-75': !serviceChargeEnabled }">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <h3 class="text-sm font-bold text-[#1C1C18]">Biaya Layanan (Service Charge)</h3>
                                    <p class="text-xs text-[#404941] leading-relaxed">
                                        Biaya tambahan opsional jasa pelayanan untuk tim saung & operasional meja.
                                    </p>
                                </div>
                                
                                <button 
                                    @click="serviceChargeEnabled = !serviceChargeEnabled"
                                    type="button" 
                                    :class="serviceChargeEnabled ? 'bg-[#00341A]' : 'bg-[#E5E2DB]'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                                    <span 
                                        :class="serviceChargeEnabled ? 'translate-x-5' : 'translate-x-0.5'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                                </button>
                            </div>

                            <div class="flex items-center gap-3 pt-2 border-t border-[#E5E2DB]/60">
                                <input 
                                    type="number" 
                                    value="0" 
                                    :disabled="!serviceChargeEnabled"
                                    class="w-20 bg-white border border-[#E5E2DB] rounded-xl px-3 py-2 text-center font-bold text-[#707971] text-sm focus:outline-none focus:border-[#00341A] shadow-inner disabled:opacity-50">
                                <span class="text-xs font-semibold" :class="serviceChargeEnabled ? 'text-[#00341A]' : 'text-[#707971]'">
                                    <span x-text="serviceChargeEnabled ? '% Tarif Aktif' : '% Dinonaktifkan'">% Dinonaktifkan</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            
            <div class="lg:col-span-4 space-y-6">

                
                <div class="bg-white rounded-xl p-6 shadow-xs border border-[#F0EEE7] space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[#1C1C18]">Topologi Node Saung</h3>
                        <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>

                    <p class="text-xs text-[#404941] leading-relaxed">
                        Peta sebaran koneksi 11 unit ESP32 yang terhubung ke broker EMQX lokal dan diteruskan ke KDS Dapur Utama.
                    </p>

                    
                    <div class="bg-[#F6F3EC] rounded-xl p-3 grid grid-cols-4 gap-2">
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-01</span>
                            <span class="font-mono text-[9px] text-[#707971]">11ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-02</span>
                            <span class="font-mono text-[9px] text-[#707971]">14ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform ring-1 ring-[#FECE66]">
                            <span class="w-2 h-2 rounded-full bg-[#7A5900] animate-ping mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-03</span>
                            <span class="font-mono text-[9px] font-bold text-[#7A5900]">Call</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-04</span>
                            <span class="font-mono text-[9px] text-[#707971]">9ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-05</span>
                            <span class="font-mono text-[9px] text-[#707971]">12ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-06</span>
                            <span class="font-mono text-[9px] text-[#707971]">16ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-07</span>
                            <span class="font-mono text-[9px] text-[#707971]">10ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-08</span>
                            <span class="font-mono text-[9px] text-[#707971]">15ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-09</span>
                            <span class="font-mono text-[9px] text-[#707971]">13ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-10</span>
                            <span class="font-mono text-[9px] text-[#707971]">11ms</span>
                        </div>
                        
                        <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#00341A] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#1C1C18]">S-11</span>
                            <span class="font-mono text-[9px] text-[#707971]">10ms</span>
                        </div>
                        
                        <div class="bg-[#FFDEA1]/40 border border-[#FECE66] rounded-lg p-2 flex flex-col items-center justify-center text-center shadow-2xs hover:scale-105 transition-transform">
                            <span class="w-2 h-2 rounded-full bg-[#7A5900] mb-1"></span>
                            <span class="text-[11px] font-bold text-[#261900]">VIP-1</span>
                            <span class="font-mono text-[9px] font-bold text-[#261900]">8ms</span>
                        </div>
                    </div>

                    
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-[10px] font-bold">
                            <span class="text-[#707971] tracking-wider uppercase">11 DARI 11 NODE TERHUBUNG</span>
                            <span class="text-[#00341A]">Rata-rata ping: 12.4ms</span>
                        </div>
                        <div class="w-full bg-[#F0EEE7] h-2 rounded-full overflow-hidden">
                            <div class="w-[88%] bg-[#00341A] h-full rounded-full"></div>
                        </div>
                    </div>
                </div>

                
                <div class="bg-white rounded-xl p-6 shadow-xs border border-[#F0EEE7] space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#1C1C18] leading-tight">PostgreSQL 16 Basis Data</h3>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">CADANGAN & REPLIKASI AKTIF</span>
                        </div>
                    </div>

                    <p class="text-xs text-[#404941] leading-relaxed">
                        Pencadangan snapshot basis data otomatis ke S3 Cloud Storage setiap penutupan shift kasir pukul 23:59 WIB.
                    </p>

                    
                    <div class="bg-[#F6F3EC] rounded-xl p-3.5 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[#707971]">Snapshot Terakhir:</span>
                            <span class="font-mono font-bold text-[#1C1C18]">Hari ini, 00:01 WIB</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#707971]">Ukuran File:</span>
                            <span class="font-mono font-bold text-[#1C1C18]">42.8 MB (.sql.gz)</span>
                        </div>
                    </div>

                    
                    <div class="space-y-2 pt-1">
                        <button 
                            @click="alert('Mengunduh arsip pencadangan basis data Situ Awi (situawi_backup_20261010.sql.gz)...')"
                            class="w-full bg-[#0B4D2B] hover:bg-[#00341A] text-white text-xs font-semibold py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-xs transition-all active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Cadangan Basis Data (.sql)</span>
                        </button>

                        <button 
                            @click="alert('Jadwal backup rutin PostgreSQL otomatis setiap 23:59 WIB telah divalidasi.')"
                            class="w-full bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] text-xs font-semibold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Jadwalkan Backup Rutin 23:59 WIB</span>
                        </button>
                    </div>
                </div>

                
                <div class="bg-white rounded-xl p-6 shadow-xs border border-[#F0EEE7] space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <h3 class="text-sm font-bold text-[#1C1C18]">Environment Midtrans</h3>
                        </div>
                        <span class="text-[10px] font-bold text-[#261900] bg-[#FFDEA1] px-2.5 py-0.5 rounded-full uppercase" x-text="midtransEnv">
                            SANDBOX
                        </span>
                    </div>

                    <p class="text-xs text-[#404941] leading-relaxed">
                        Ganti ke Production sebelum meluncurkan QRIS resmi dari Bank Indonesia.
                    </p>

                    <div class="bg-[#F0EEE7] p-1 rounded-xl grid grid-cols-2 gap-1 pt-1">
                        <button 
                            @click="midtransEnv = 'sandbox'"
                            :class="midtransEnv === 'sandbox' ? 'bg-white text-[#404941] shadow-xs font-semibold' : 'text-[#707971] hover:text-[#1C1C18] font-medium'"
                            class="py-1.5 text-xs rounded-lg transition-all text-center cursor-pointer">
                            Sandbox
                        </button>
                        <button 
                            @click="midtransEnv = 'production'"
                            :class="midtransEnv === 'production' ? 'bg-[#00341A] text-white shadow-xs font-semibold' : 'text-[#707971] hover:text-[#1C1C18] font-medium'"
                            class="py-1.5 text-xs rounded-lg transition-all text-center cursor-pointer">
                            Production
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection

