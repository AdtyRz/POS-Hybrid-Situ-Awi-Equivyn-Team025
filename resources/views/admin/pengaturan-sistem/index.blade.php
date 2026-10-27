@extends('layouts.admin')

@section('title', 'Pengaturan Sistem & IoT - Situ Awi Saung Lesehan')
@section('page_category', 'Pengaturan Sistem')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="pengaturanSistemApp()">

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
                    Kelola konektivitas HTTP API ESP32, konfigurasi printer thermal kasir, kredensial Midtrans QRIS, dan parameter operasional saung secara terpusat.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    @click="ujiKoneksi()"
                    :disabled="pinging"
                    class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] active:scale-95 text-[#7A5900] text-xs font-semibold flex items-center gap-2 shadow-xs transition-all cursor-pointer disabled:opacity-60">
                    <svg class="w-4 h-4 text-[#7A5900]" :class="{ 'animate-spin': pinging }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="pinging ? 'Menguji Layanan...' : 'Uji Koneksi Layanan'">Uji Koneksi Layanan</span>
                </button>

                <button
                    type="button"
                    @click="bersihkanCache()"
                    :disabled="cacheClearing"
                    class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] active:scale-95 text-[#404941] text-xs font-semibold flex items-center gap-2 shadow-xs transition-all cursor-pointer disabled:opacity-60">
                    <svg class="w-4 h-4 text-[#404941]" :class="{ 'animate-spin': cacheClearing }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="cacheClearing ? 'Membersihkan...' : 'Reset Cache'">Reset Cache</span>
                </button>

                <button
                    type="button"
                    @click="simpan()"
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

    <div x-show="pingSuccess" x-transition class="p-4 rounded-xl bg-[#B1F1C2]/60 border border-[#0B4D2B]/20 flex items-center justify-between text-xs font-semibold text-[#00210E]">
        <div class="flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
            <span x-text="ringkasanLayanan"></span>
        </div>
        <button @click="pingSuccess = false" class="text-[#00341A] font-bold hover:underline cursor-pointer">Tutup</button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <div class="bg-[#F6F3EC] rounded-2xl p-5 sm:p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[185px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">HTTP API IOT</span>
                    <h4 class="text-xs font-semibold text-[#404941] mt-0.5">ESP32 Saung Gateway</h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/50 flex items-center justify-center text-[#0B4D2B] shrink-0">
                    <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="font-display font-bold text-xl sm:text-2xl text-[#00341A] tracking-tight leading-tight whitespace-nowrap">
                    HTTP REST
                </span>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold whitespace-nowrap shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                    <span>AKTIF</span>
                </span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="text-[#404941] text-[10px] font-bold flex items-center gap-1.5 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                    Polling: 30 dtk
                </span>
                <span class="font-mono text-[10px] font-bold text-[#707971] whitespace-nowrap shrink-0">{{ $mejaTerdaftar }} Meja</span>
            </div>

            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-[#00341A]/5 blur-xl pointer-events-none"></div>
        </div>

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
            </div>

            <div class="flex items-baseline gap-2">
                <span class="font-display font-bold text-xl sm:text-2xl text-[#1C1C18] tracking-tight leading-tight whitespace-nowrap">
                    Laravel Reverb
                </span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="text-[10px] font-bold text-[#00341A] truncate">pos-channel • kds-stream</span>
                <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1]/50 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                    {{ $diagnostik['reverb']['aktif'] ? 'Terhubung' : 'Offline' }}
                </span>
            </div>
        </div>

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
                    <span class="truncate" x-text="midtransSiap ? 'Kredensial Lengkap' : 'Belum Lengkap'">Kredensial Lengkap</span>
                </span>
                <span class="font-mono text-[10px] font-bold text-[#707971] whitespace-nowrap shrink-0">{{ $midtrans['client_key'] }}</span>
            </div>
        </div>

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
                    Printer Thermal
                </span>
            </div>

            <div class="flex items-center justify-between text-xs mt-2 gap-2">
                <span class="font-mono text-[10px] font-bold text-[#1C1C18] whitespace-nowrap" x-text="printerIp ? (printerIp + ':' + printerPort) : 'Belum diatur'">Belum diatur</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold whitespace-nowrap shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                    <span x-text="printerIp ? 'TERKONFIGURASI' : 'BELUM DIATUR'">BELUM DIATUR</span>
                </span>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/20 blur-xl pointer-events-none"></div>
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
            @click="activeTab = 'profile'"
            :class="activeTab === 'profile' ? 'bg-[#00341A] text-white shadow-xs' : 'bg-[#F0EEE7] text-[#404941] hover:bg-[#E5E2DB]'"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
            </svg>
            <span>4. Profil Resto</span>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <div class="lg:col-span-8 space-y-6">

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
                        {{ $mejaTerdaftar }} MEJA TERDAFTAR
                    </span>
                </div>

                <div class="p-4 rounded-xl bg-white border border-[#C0C9BF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider block">JARINGAN SERVER</span>
                            <span class="text-base font-bold text-[#1C1C18]">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="bg-[#FCF9F2] rounded-lg px-3 py-1.5 border border-[#C0C9BF]/30">
                            <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">ENDPOINT HTTP</span>
                            <span class="font-mono text-xs font-bold text-[#1C1C18]">/api/iot/status-meja</span>
                        </div>
                        <div class="bg-[#FCF9F2] rounded-lg px-3 py-1.5 border border-[#C0C9BF]/30">
                            <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">INTERVAL POLLING</span>
                            <span class="font-mono text-xs font-bold text-[#00341A]">30 Detik</span>
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

                <div class="pt-2 border-t border-[#C0C9BF]/20 space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h3 class="text-sm font-bold text-[#1C1C18]">Kode Akses &amp; Pengelolaan Sesi Meja</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <label class="text-xs font-bold text-[#1C1C18]">Pengelolaan Sesi Tamu</label>
                                    <p class="text-xs text-[#404941] leading-relaxed mt-1" x-text="sessionAutoClose ? 'Sesi ditutup otomatis setelah batas waktu tanpa transaksi. Sesi yang masih memiliki pesanan aktif tidak akan ditutup.' : 'Sesi diakhiri manual oleh kasir atau pelayan setelah meja selesai digunakan. Gunakan tombol Akhiri Sesi Meja pada terminal kasir.'"></p>
                                </div>
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="sessionAutoClose"
                                    @click="sessionAutoClose = !sessionAutoClose"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-[#0B4D2B]/30"
                                    :class="sessionAutoClose ? 'bg-[#0B4D2B]' : 'bg-[#D8D3C7]'">
                                    <span
                                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform"
                                        :class="sessionAutoClose ? 'translate-x-5' : 'translate-x-0.5'"></span>
                                </button>
                            </div>

                            <div x-show="sessionAutoClose" x-transition class="flex items-center gap-3 pt-2 border-t border-[#C0C9BF]/20">
                                <input
                                    type="number"
                                    min="5"
                                    max="1440"
                                    x-model.number="sessionExpiry"
                                    class="w-24 bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2 text-center font-bold text-[#1C1C18] text-sm focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <span class="text-xs font-semibold text-[#707971]">Menit tanpa transaksi</span>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 flex flex-col justify-between gap-3 shadow-2xs">
                            <label class="text-xs font-bold text-[#1C1C18]">Panjang Kode Sesi Meja</label>
                            <p class="text-xs text-[#404941] leading-relaxed">
                                Jumlah digit kode unik yang tampil di LCD meja dan dimasukkan tamu saat membuka e-menu.
                            </p>
                            <div class="pt-1">
                                <select
                                    x-model="encryptionLevel"
                                    class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                    <option value="4 Digit">4 Digit (Standar)</option>
                                    <option value="6 Digit">6 Digit (Lebih Ketat)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

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
                            type="button"
                            @click="ujiKoneksi()"
                            :disabled="pinging"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-[#F0EEE7] border border-[#C0C9BF]/30 text-[#1C1C18] text-xs font-semibold shadow-2xs transition-all active:scale-95 cursor-pointer disabled:opacity-60">
                            <svg class="w-3.5 h-3.5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span x-text="pinging ? 'Memeriksa...' : 'Uji Koneksi Server'">Uji Koneksi Server</span>
                        </button>

                        <button
                            type="button"
                            @click="bukaEndpoint()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FFDEA1]/60 hover:bg-[#FECE66] border border-[#FECE66]/50 text-[#261900] text-xs font-semibold shadow-2xs transition-all active:scale-95 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#261900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            <span>Buka Endpoint Status</span>
                        </button>

                        <button
                            type="button"
                            @click="muatUlangMeja()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#E5E2DB]/70 hover:bg-[#ded9cf] border border-[#C0C9BF]/40 text-[#404941] text-xs font-semibold shadow-2xs transition-all active:scale-95 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span>Muat Ulang Data Meja</span>
                        </button>
                    </div>
                </div>
            </div>

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

                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-3 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-[#1C1C18]">Logo Restoran untuk Struk</h3>
                            <p class="text-xs text-[#707971]">Unggah logo JPG, JPEG, PNG, atau WEBP. Ukuran maksimal 3 MB.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F0EEE7] text-[10px] font-bold text-[#707971] whitespace-nowrap self-start sm:self-auto">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                            OTOMATIS DIPAKAI
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.pengaturan-sistem.logo') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-end gap-3">
                        @csrf
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-16 h-16 rounded-xl bg-[#FCF9F2] border border-[#C0C9BF]/30 flex items-center justify-center overflow-hidden shrink-0">
                                <img :src="logoSrc" alt="Logo restoran saat ini" class="max-h-14 max-w-14 object-contain">
                            </div>
                            <div class="flex-1 min-w-0">
                                <label for="logo-resto" class="text-xs font-semibold text-[#1C1C18] block mb-1.5">Pilih Berkas Logo</label>
                                <input
                                    id="logo-resto"
                                    type="file"
                                    name="logo"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    required
                                    class="block w-full text-xs text-[#404941] file:mr-3 file:rounded-lg file:border-0 file:bg-[#F0EEE7] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#0B4D2B] hover:file:bg-[#E5E2DB] cursor-pointer">
                                @error('logo')
                                    <p class="text-[11px] font-semibold text-[#B42318] mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="h-10 px-4 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white text-xs font-semibold flex items-center justify-center gap-2 shadow-xs transition-all cursor-pointer whitespace-nowrap">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unggah Logo</span>
                        </button>
                    </form>
                </div>

                <div class="p-4 rounded-xl bg-white border border-[#C0C9BF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] text-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-[#1C1C18]">Pratinjau Cetak Struk Kasir</h4>
                            <p class="text-xs text-[#404941]">Membuka pratinjau struk dengan data contoh untuk memeriksa margin, header, dan footer.</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="printSample()"
                        :disabled="isPrintingSample"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold shadow-xs transition-all cursor-pointer whitespace-nowrap active:scale-95 disabled:opacity-60">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span x-text="isPrintingSample ? 'Membuka Pratinjau...' : 'Buka Pratinjau Cetak'">Buka Pratinjau Cetak</span>
                    </button>
                </div>
            </div>

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
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">QRIS DYNAMIC &amp; NOTIFIKASI PEMBAYARAN</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1] px-3 py-1 rounded-full uppercase" x-text="midtransEnv">SANDBOX</span>
                </div>

                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-3 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-[#1C1C18]">Mode Lingkungan Pembayaran (Environment)</h3>
                            <p class="text-xs text-[#707971]">Mode aktif dibaca dari konfigurasi server (.env). Perubahan environment dilakukan saat deployment.</p>
                        </div>
                    </div>

                    <div class="bg-[#F0EEE7] p-1 rounded-xl grid grid-cols-2 gap-1 max-w-sm">
                        <button
                            type="button"
                            @click="infoMidtransEnv()"
                            :class="midtransEnv === 'sandbox' ? 'bg-white text-[#0B4D2B] shadow-xs font-bold' : 'text-[#707971] hover:text-[#1C1C18] font-medium'"
                            class="py-2 text-xs rounded-lg transition-all text-center cursor-pointer">
                            Sandbox (Simulasi)
                        </button>
                        <button
                            type="button"
                            @click="infoMidtransEnv()"
                            :class="midtransEnv === 'production' ? 'bg-[#00341A] text-white shadow-xs font-bold' : 'text-[#707971] hover:text-[#1C1C18] font-medium'"
                            class="py-2 text-xs rounded-lg transition-all text-center cursor-pointer">
                            Production (Live QRIS)
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-4 shadow-2xs">
                    <h3 class="text-sm font-bold text-[#1C1C18]">Kredensial API Merchant Midtrans</h3>
                    <p class="text-xs text-[#707971]">Kredensial dibaca dari <span class="font-mono">.env</span> server dan tidak dapat diubah dari halaman ini.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Merchant ID</label>
                            <input
                                type="text"
                                readonly
                                x-model="midtransMerchantId"
                                class="w-full bg-[#F0EEE7] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] cursor-not-allowed shadow-inner">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Client Key</label>
                            <input
                                type="text"
                                readonly
                                x-model="midtransClientKey"
                                class="w-full bg-[#F0EEE7] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] cursor-not-allowed shadow-inner">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-[#1C1C18]">Server Key (Rahasia)</label>
                        <input
                            type="password"
                            readonly
                            x-model="midtransServerKey"
                            class="w-full bg-[#F0EEE7] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 font-mono text-xs font-bold text-[#1C1C18] cursor-not-allowed shadow-inner">
                    </div>
                </div>

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
                            type="button"
                            @click="salinNotifikasi()"
                            class="px-4 py-2.5 rounded-xl bg-[#E5E2DB] hover:bg-[#ded9cf] text-[#00341A] text-xs font-bold transition-all cursor-pointer whitespace-nowrap">
                            Salin URL
                        </button>
                    </div>
                </div>

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
                            <span class="text-xs font-semibold text-[#707971]">Menit (5-60)</span>
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
                        type="button"
                        @click="testMidtransApi()"
                        :disabled="testingMidtrans"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold shadow-xs transition-all cursor-pointer active:scale-95 disabled:opacity-60">
                        <svg class="w-4 h-4 text-white" :class="{ 'animate-spin': testingMidtrans }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span x-text="testingMidtrans ? 'Memeriksa...' : 'Periksa Konfigurasi Midtrans'">Periksa Konfigurasi Midtrans</span>
                    </button>
                </div>
            </div>

            <div x-show="activeTab === 'profile'" x-transition class="bg-[#F6F3EC] rounded-2xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 space-y-6">

                <div class="flex items-center justify-between pb-4 border-b border-[#C0C9BF]/20">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] text-white flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-[#1C1C18]">Profil Usaha</h2>
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">KONFIGURASI DATA BISNIS &amp; OPERASIONAL KASIR</span>
                        </div>
                    </div>
                </div>

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
                            <label class="text-xs font-semibold text-[#1C1C18]">Email Resto</label>
                            <input
                                type="email"
                                x-model="restoEmail"
                                class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                        </div>
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

                <div class="grid grid-cols-1 gap-4">

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
                            <span class="text-[10px] font-bold text-[#707971] tracking-wider uppercase block">POSTGRESQL • CADANGAN DATA OPERASIONAL</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#B1F1C2]/60 text-[#00341A] text-[10px] font-bold">
                        <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                        {{ $diagnostik['database']['terhubung'] ? 'TERHUBUNG' : 'GAGAL' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">ENGINE</span>
                        <span class="font-mono text-xs font-bold text-[#1C1C18]">{{ $diagnostik['database']['driver'] }}</span>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">UKURAN DB</span>
                        <span class="font-mono text-xs font-bold text-[#00341A]">{{ $diagnostik['database']['ukuran'] ?? '-' }}</span>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">TOTAL TABEL</span>
                        <span class="font-mono text-xs font-bold text-[#1C1C18]">{{ $diagnostik['database']['jumlah_tabel'] ?? '-' }} Tabel</span>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-[#C0C9BF]/20 shadow-2xs">
                        <span class="text-[9px] font-bold text-[#707971] uppercase tracking-wider block">LATENSI QUERY</span>
                        <span class="font-mono text-xs font-bold text-[#7A5900]">{{ $diagnostik['database']['latensi'] ?? '-' }} ms</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 sm:p-5 border border-[#C0C9BF]/20 space-y-4 shadow-2xs">
                    <h3 class="text-sm font-bold text-[#1C1C18]">Jadwal Pencadangan Otomatis</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Frekuensi Rencana Snapshot</label>
                            <select x-model="backupFrequency" class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <option value="harian">Harian</option>
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-[#1C1C18]">Masa Retensi Rencana</label>
                            <select x-model="backupRetention" class="w-full bg-[#FCF9F2] border border-[#C0C9BF]/40 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-[#1C1C18] focus:outline-hidden focus:border-[#0B4D2B] shadow-inner">
                                <option :value="7">7 Hari</option>
                                <option :value="30">30 Hari</option>
                                <option :value="90">90 Hari</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#C0C9BF]/20">
                        <div class="flex items-start gap-2.5 text-xs text-[#404941]">
                            <svg class="w-4 h-4 text-[#707971] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Cadangan dibuat saat tombol unduh ditekan. Integrasi arsip cloud belum dikonfigurasi pada server ini.</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white border border-[#C0C9BF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                    <div>
                        <h4 class="text-xs font-bold text-[#1C1C18]">Unduh Cadangan Data Operasional (.json)</h4>
                        <p class="text-xs text-[#404941]">Berisi transaksi, staf, meja, sesi, dan pengaturan yang tersimpan di basis data saat ini.</p>
                    </div>

                    <button
                        type="button"
                        @click="unduhCadangan()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold shadow-xs transition-all cursor-pointer whitespace-nowrap active:scale-95">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh Cadangan (.json)</span>
                    </button>
                </div>
            </div>

        </div>

        <div class="lg:col-span-4 space-y-6">

            <template x-if="activeTab === 'iot'">
                <div class="space-y-6">

                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Topologi Node Saung</h3>
                            <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                            </svg>
                        </div>

                        <p class="text-xs text-[#404941] leading-relaxed">
                            Daftar meja yang terdaftar dan dapat mengambil status melalui endpoint HTTP API.
                        </p>

                        <div class="bg-white rounded-xl p-3 border border-[#C0C9BF]/20 grid grid-cols-4 gap-2">
                            @forelse ($daftarMeja as $meja)
                                <div class="rounded-lg p-2 flex flex-col items-center justify-center text-center border {{ $meja->panggilan_aktif ? 'bg-[#FFDEA1]/30 border-[#FECE66]' : 'bg-[#FCF9F2] border-[#C0C9BF]/20' }}">
                                    <span class="w-2 h-2 rounded-full mb-1 {{ $meja->panggilan_aktif ? 'bg-[#7A5900] animate-ping' : ($meja->id_device ? 'bg-[#00341A]' : 'bg-[#C0C9BF]') }}"></span>
                                    <span class="text-[11px] font-bold text-[#1C1C18]">{{ $meja->kode_meja }}</span>
                                    <span class="font-mono text-[9px] {{ $meja->panggilan_aktif ? 'font-bold text-[#7A5900]' : 'text-[#707971]' }}">
                                        {{ $meja->panggilan_aktif ? 'Call' : ($meja->id_device ? 'Device' : 'QR') }}
                                    </span>
                                </div>
                            @empty
                                <div class="col-span-4 py-4 text-center text-xs text-[#707971]">Belum ada meja terdaftar.</div>
                            @endforelse
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-[#707971] tracking-wider uppercase">{{ $mejaTerdaftar }} MEJA • {{ $perangkatTerpasang }} ID PERANGKAT • {{ $sesiAktif }} SESI AKTIF</span>
                                <span class="text-[#00341A]">{{ $panggilanAktif }} panggilan</span>
                            </div>
                            <div class="w-full bg-[#E5E2DB] h-2 rounded-full overflow-hidden">
                                <div class="bg-[#0B4D2B] h-full rounded-full" style="width: {{ $mejaTerdaftar > 0 ? round($sesiAktif / $mejaTerdaftar * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">HARDWARE GATEWAY</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">MCU Board:</span>
                                <span class="font-bold text-[#1C1C18]">ESP32-WROOM-32D</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Koneksi:</span>
                                <span class="font-bold text-[#1C1C18]">WiFi 2.4 GHz</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Transport:</span>
                                <span class="font-bold text-[#00341A]">HTTP REST</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Endpoint Status:</span>
                                <span class="font-mono font-bold text-[#1C1C18]">/api/iot/status-meja</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="activeTab === 'printer'">
                <div class="space-y-6">

                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Status Perangkat Printer</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                :class="printerIp ? 'bg-[#B1F1C2] text-[#00210E]' : 'bg-[#FFDEA1] text-[#7A5900]'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="printerIp ? 'bg-[#00341A]' : 'bg-[#7A5900]'"></span>
                                <span x-text="printerIp ? 'TERKONFIGURASI' : 'BELUM DIATUR'">BELUM DIATUR</span>
                            </span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/20 space-y-2 text-xs shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Model Unit:</span>
                                <span class="font-bold text-[#1C1C18]">Printer Thermal ESC/POS</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">IP Endpoint:</span>
                                <span class="font-mono font-bold text-[#1C1C18]" x-text="printerIp ? (printerIp + ':' + printerPort) : 'Belum diatur'">Belum diatur</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Kertas Roll:</span>
                                <span class="font-bold text-[#00341A]" x-text="paperSize">80mm</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Auto Cutter:</span>
                                <span class="font-bold text-[#1C1C18]" x-text="autoCutter ? 'Aktif' : 'Non-aktif'">Aktif</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">PREVIEW FORMAT STRUK</h4>
                            <span class="text-[9px] font-mono text-[#707971]" x-text="paperSize">80mm</span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/30 font-mono text-[10px] text-[#1C1C18] space-y-2 shadow-sm">
                            <div class="text-center border-b border-dashed border-[#C0C9BF] pb-2 space-y-0.5">
                                <div class="flex justify-center pb-1" x-show="printLogo">
                                    <img :src="logoSrc" alt="Logo Resto" class="h-10 w-auto max-w-[88px] object-contain">
                                </div>
                                <div class="font-bold text-xs" x-text="receiptHeader">Saung Situ Awi - Ciwidey</div>
                                <div class="text-[9px] text-[#707971]" x-text="restoAddress">Jl. Raya Ciwidey No. 25, Rancabali</div>
                                <div class="text-[9px] text-[#707971]" x-text="'Telp: ' + restoPhone">Telp: 0812-3456-7890</div>
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
                                <div class="flex justify-between font-bold text-xs pt-1 border-t border-dashed border-[#C0C9BF]">
                                    <span>TOTAL:</span>
                                    <span>Rp 166.000</span>
                                </div>
                            </div>

                            <div class="text-center pt-2 border-t border-dashed border-[#C0C9BF] text-[9px] text-[#707971]" x-text="receiptFooter">
                                Hatur Nuhun Parantos Sumping
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="activeTab === 'midtrans'">
                <div class="space-y-6">

                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-[#1C1C18]">Status Akun Midtrans</h3>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                :class="midtransSiap ? 'bg-[#B1F1C2] text-[#00210E]' : 'bg-[#FFDEA1] text-[#7A5900]'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="midtransSiap ? 'bg-[#00341A]' : 'bg-[#7A5900]'"></span>
                                <span x-text="midtransSiap ? 'KREDENSIAL LENGKAP' : 'BELUM LENGKAP'">BELUM LENGKAP</span>
                            </span>
                        </div>

                        <div class="bg-white rounded-xl p-4 border border-[#C0C9BF]/20 space-y-2 text-xs shadow-2xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Mode Aktif:</span>
                                <span class="font-bold text-[#7A5900] uppercase" x-text="midtransEnv">SANDBOX</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Metode:</span>
                                <span class="font-bold text-[#1C1C18]">QRIS Midtrans</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[#707971] shrink-0">Webhook:</span>
                                <span class="font-mono font-bold text-[#00341A] truncate" x-text="notificationUrl">-</span>
                            </div>
                        </div>
                    </div>

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

            <template x-if="activeTab === 'profile'">
                <div class="space-y-6">

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
                            <div class="flex items-center justify-between" x-show="serviceChargeEnabled">
                                <span class="text-[#707971]" x-text="'Service Charge (' + serviceChargeRate + '%):'">Service Charge:</span>
                                <span class="font-bold text-[#7A5900]" x-text="'Rp ' + (150000 * serviceChargeRate / 100).toLocaleString('id-ID')">Rp 7.500</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-[#C0C9BF]/30 font-bold text-sm text-[#00341A]">
                                <span>Total Tagihan Meja:</span>
                                <span x-text="'Rp ' + (150000 + (serviceChargeEnabled ? 150000 * serviceChargeRate / 100 : 0)).toLocaleString('id-ID')">Rp 150.000</span>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

            <template x-if="activeTab === 'backup'">
                <div class="space-y-6">

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
                                <span class="text-[#707971]">Waktu Server:</span>
                                <span class="font-mono font-bold text-[#1C1C18]">{{ \Illuminate\Support\Carbon::parse($diagnostik['diperiksa_pada'])->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Ukuran Basis Data:</span>
                                <span class="font-mono font-bold text-[#1C1C18]">{{ $diagnostik['database']['ukuran'] ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#707971]">Sumber Data:</span>
                                <span class="font-bold text-[#00341A]">{{ $diagnostik['database']['driver'] }} • {{ $diagnostik['database']['nama'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/30 space-y-3">
                        <h4 class="text-xs font-bold text-[#707971] uppercase tracking-wider">RIWAYAT CADANGAN</h4>
                        <div class="space-y-2 text-xs">
                            <div class="p-3 rounded-lg bg-white border border-[#C0C9BF]/20 text-center">
                                <div class="font-bold text-[#1C1C18]">Belum ada file cadangan tersimpan</div>
                                <div class="text-[10px] text-[#707971] mt-1">Gunakan tombol unduh untuk membuat cadangan data terbaru.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>

        <div
        x-cloak
        x-show="toast.show"
        x-transition
        class="fixed bottom-8 right-8 z-50 px-5 py-3.5 rounded-2xl shadow-xl flex items-center gap-3 border text-sm font-semibold"
        :class="toast.error ? 'bg-[#93000A] text-white border-[#FFB4AB]/40' : 'bg-[#00341A] text-white border-[#FECE66]/30'">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span x-text="toast.message"></span>
    </div>

</div>

</div>

<script>
    function pengaturanSistemApp() {
        return {
            activeTab: 'iot',
            isSaving: false,
            saveSuccess: false,
            pinging: false,
            pingSuccess: false,
            cacheClearing: false,
            isPrintingSample: false,
            testingMidtrans: false,
            serviceChargeEnabled: false,
            midtransEnv: @js(strtolower($midtrans['mode'])),
            midtransMerchantId: @js($midtrans['merchant_id']),
            midtransClientKey: @js($midtrans['client_key']),
            midtransServerKey: @js($midtrans['server_key_terpasang'] ? 'Tersimpan di .env' : 'Belum diatur'),
            midtransSiap: @js($midtrans['siap']),
            qrisExpiry: Number(@js($profil['qris_expiry_minutes'] ?? 5)),
            autoCancelExpired: false,
            buzzerDuration: Number(@js($profil['buzzer_duration'] ?? 3)),
            antiSpamCooldown: Number(@js($profil['anti_spam_cooldown'] ?? 5)),
            lcdMessage: @js($profil['lcd_message'] ?? 'Memanggil Pelayan...'),
            encryptionLevel: @js($profil['encryption_level'] ?? '4 Digit'),
            sessionAutoClose: @js((bool) ($profil['session_auto_close'] ?? false)),
            sessionExpiry: Number(@js($profil['session_expiry_minutes'] ?? 120)),
            paperSize: @js($profil['paper_size'] ?? '80mm'),
            printerIp: @js($profil['printer_ip'] ?? ''),
            printerPort: String(@js($profil['printer_port'] ?? 9100)),
            autoCutter: false,
            printLogo: @js((bool) ($profil['print_logo'] ?? true)),
            printQrCode: false,
            receiptHeader: @js($profil['receipt_header'] ?? ''),
            receiptFooter: @js($profil['struk_footer'] ?? ''),
            restoName: @js($profil['nama_resto'] ?? ''),
            restoAddress: @js($profil['alamat_resto'] ?? ''),
            restoPhone: @js($profil['telepon_resto'] ?? ''),
            restoEmail: @js($profil['email_resto'] ?? ''),
            serviceChargeRate: Number(@js($profil['service_charge_rate'] ?? 0)),
            roundingRule: @js($profil['rounding_rule'] ?? 'none'),
            backupFrequency: @js($profil['backup_frequency'] ?? 'harian'),
            backupRetention: Number(@js($profil['backup_retention'] ?? 7)),
            backupCloud: false,
            profil: @js($profil),
            diagnostik: @js($diagnostik),
            midtrans: @js($midtrans),
            toast: { show: false, message: '', error: false },
            simpanUrl: @js(route('admin.pengaturan-sistem.simpan')),
            diagnostikUrl: @js(route('admin.pengaturan-sistem.diagnostik')),
            cacheUrl: @js(route('admin.pengaturan-sistem.cache')),
            cadanganUrl: @js(route('admin.pengaturan-sistem.cadangan')),
            notificationUrl: @js($midtrans['notification_url']),
            statusEndpoint: @js(route('admin.pengaturan-sistem.iot-status')),
            csrf: @js(csrf_token()),
            logoSrc: @js($logoSrc ?? asset('asset/Logo.png')),
            logoUploadUrl: @js(route('admin.pengaturan-sistem.logo')),
            flashSuccess: @js(session('sukses')),

            init() {
                const p = this.profil || {};

                this.serviceChargeEnabled = this.keBool(p.service_charge_enabled);
                this.autoCancelExpired = this.keBool(p.midtrans_auto_cancel);
                this.autoCutter = this.keBool(p.auto_cutter);
                this.printLogo = this.keBool(p.print_logo);
                this.printQrCode = this.keBool(p.print_qr);
                this.sessionAutoClose = this.keBool(p.session_auto_close);
                this.backupCloud = this.keBool(p.backup_cloud);

                if (this.flashSuccess) {
                    this.showToast(this.flashSuccess);
                }
            },

            keBool(nilai) {
                return nilai === true || nilai === 1 || nilai === '1' || nilai === 'true';
            },

            get ringkasanLayanan() {
                const db = this.diagnostik?.database || {};
                const iot = this.diagnostik?.iot || {};
                const reverb = this.diagnostik?.reverb || {};
                const midtrans = this.diagnostik?.midtrans || {};

                return [
                    'Database ' + (db.terhubung ? 'terhubung' : 'gagal terhubung'),
                    iot.meja_terdaftar + ' meja terdaftar',
                    'Midtrans ' + (midtrans.siap ? 'siap' : 'belum lengkap'),
                    'Reverb ' + (reverb.aktif ? 'aktif' : 'tidak aktif'),
                ].join(' • ');
            },

            payload() {
                return {
                    nama_resto: this.restoName,
                    alamat_resto: this.restoAddress,
                    telepon_resto: this.restoPhone,
                    email_resto: this.restoEmail,
                    struk_footer: this.receiptFooter,
                    qris_expiry_minutes: Number(this.qrisExpiry),
                    buzzer_duration: Number(this.buzzerDuration),
                    anti_spam_cooldown: Number(this.antiSpamCooldown),
                    lcd_message: this.lcdMessage,
                    encryption_level: this.encryptionLevel,
                    session_auto_close: this.sessionAutoClose,
                    session_expiry_minutes: Number(this.sessionExpiry),
                    printer_ip: this.printerIp,
                    printer_port: Number(this.printerPort),
                    paper_size: this.paperSize,
                    auto_cutter: this.autoCutter,
                    print_logo: this.printLogo,
                    print_qr: this.printQrCode,
                    receipt_header: this.receiptHeader,
                    service_charge_enabled: this.serviceChargeEnabled,
                    service_charge_rate: Number(this.serviceChargeRate),
                    rounding_rule: this.roundingRule,
                    backup_frequency: this.backupFrequency,
                    backup_retention: Number(this.backupRetention),
                    backup_cloud: this.backupCloud,
                    midtrans_auto_cancel: this.autoCancelExpired,
                };
            },

            async simpan() {
                this.isSaving = true;
                this.saveSuccess = false;

                try {
                    const respons = await fetch(this.simpanUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(this.payload()),
                    });

                    if (respons.status === 422) {
                        const data = await respons.json();
                        const pesan = Object.values(data.errors || {}).flat()[0] || 'Periksa kembali isian pengaturan.';
                        this.showToast(pesan, true);
                        return;
                    }

                    if (! respons.ok) {
                        throw new Error('HTTP ' + respons.status);
                    }

                    this.saveSuccess = true;
                    this.showToast('Pengaturan sistem berhasil disimpan.');
                    setTimeout(() => this.saveSuccess = false, 3000);
                } catch (error) {
                    this.showToast('Pengaturan gagal disimpan. Periksa koneksi server.', true);
                } finally {
                    this.isSaving = false;
                }
            },

            async ujiKoneksi() {
                this.pinging = true;
                this.pingSuccess = false;

                try {
                    const respons = await fetch(this.diagnostikUrl, {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (! respons.ok) {
                        throw new Error('HTTP ' + respons.status);
                    }

                    this.diagnostik = await respons.json();
                    const midtrans = this.diagnostik.midtrans || {};
                    this.midtransSiap = !!midtrans.siap;
                    if (midtrans.mode) {
                        this.midtransEnv = String(midtrans.mode).toLowerCase();
                    }
                    this.pingSuccess = true;
                    this.showToast('Diagnostik server berhasil diperbarui.');
                } catch (error) {
                    this.showToast('Gagal mengambil diagnostik server.', true);
                } finally {
                    this.pinging = false;
                }
            },

            async bersihkanCache() {
                this.cacheClearing = true;

                try {
                    const respons = await fetch(this.cacheUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await respons.json();

                    this.showToast(data.pesan || 'Cache selesai diproses.', ! respons.ok);
                } catch (error) {
                    this.showToast('Cache gagal dibersihkan.', true);
                } finally {
                    this.cacheClearing = false;
                }
            },

            async testMidtransApi() {
                this.testingMidtrans = true;

                try {
                    const respons = await fetch(this.diagnostikUrl, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await respons.json();
                    const midtrans = data.midtrans || {};
                    this.midtransSiap = !!midtrans.siap;

                    this.showToast(
                        midtrans.siap
                            ? 'Kredensial Midtrans lengkap pada mode ' + (midtrans.mode || this.midtransEnv) + '.'
                            : 'Kredensial Midtrans belum lengkap. Periksa konfigurasi .env.',
                        !midtrans.siap
                    );
                } catch (error) {
                    this.showToast('Gagal memeriksa konfigurasi Midtrans.', true);
                } finally {
                    this.testingMidtrans = false;
                }
            },

            testPing() {
                this.ujiKoneksi();
            },

            infoMidtransEnv() {
                this.showToast('Mode Midtrans diatur melalui konfigurasi server (.env).');
            },

            salinNotifikasi() {
                navigator.clipboard.writeText(this.notificationUrl)
                    .then(() => this.showToast('URL notifikasi berhasil disalin.'))
                    .catch(() => this.showToast('Gagal menyalin URL notifikasi.', true));
            },

            bukaEndpoint() {
                window.open(this.statusEndpoint, '_blank', 'noopener');
            },

            muatUlangMeja() {
                window.location.reload();
            },

            unduhCadangan() {
                this.showToast('Menyiapkan cadangan data...');
                window.location.href = this.cadanganUrl;
            },

            printSample() {
                const baris = [
                    this.receiptHeader || this.restoName,
                    this.restoAddress,
                    'Telp: ' + this.restoPhone,
                    '--------------------------------',
                    'Meja: S-04 (Saung 4)',
                    'No: #SA-260901',
                    '--------------------------------',
                    '1x Nasi Liwet Kastrol      Rp 65.000',
                    '1x Gurame Bakar Cobek      Rp 85.000',
                    '2x Es Teh Manis             Rp 16.000',
                    '--------------------------------',
                    'SUBTOTAL:                 Rp 166.000',
                    'TOTAL:                    Rp 166.000',
                    '--------------------------------',
                    this.receiptFooter,
                ];

                const jendela = window.open('', '_blank', 'width=420,height=640');
                if (! jendela) {
                    this.showToast('Popup diblokir browser. Izinkan popup untuk mencetak.', true);
                    return;
                }

                const dokumen = jendela.document;
                dokumen.title = 'Struk Uji Kasir';
                const gaya = dokumen.createElement('style');
                gaya.textContent = '@page{size:' + (this.paperSize || '80mm') + ' auto;margin:6mm 5mm}html,body{margin:0;padding:0}body{font-family:monospace;font-size:10.5px;color:#000;padding:0 2mm 4mm}.logo{display:block;margin:0 auto 8px;height:42px;max-width:52mm;width:auto;object-fit:contain}pre{white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;margin:0;padding:0;font:inherit;line-height:1.45}@media screen{body{padding:16px}}';
                dokumen.head.appendChild(gaya);

                if (this.printLogo) {
                    const logo = dokumen.createElement('img');
                    logo.src = this.logoSrc;
                    logo.alt = 'Logo ' + (this.restoName || 'Resto');
                    logo.className = 'logo';
                    dokumen.body.appendChild(logo);
                }

                const isi = dokumen.createElement('pre');
                isi.textContent = baris.join('\\n');
                dokumen.body.appendChild(isi);

                const skrip = dokumen.createElement('script');
                skrip.textContent = '(function(){let selesai=false;const cetak=()=>{if(selesai)return;selesai=true;window.focus();window.print();};const logo=document.querySelector("img.logo");if(logo){logo.addEventListener("load",cetak);logo.addEventListener("error",cetak);}window.addEventListener("load",cetak);setTimeout(cetak,900);})();';
                dokumen.body.appendChild(skrip);
            },

            showToast(pesan, error = false) {
                this.toast.message = pesan;
                this.toast.error = error;
                this.toast.show = true;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => this.toast.show = false, 3500);
            },
        };
    }
</script>
@endsection
