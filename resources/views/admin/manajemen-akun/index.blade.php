@extends('layouts.admin')

@section('title', 'Manajemen Akun & Hak Akses Staf')
@section('page_category', 'Keamanan & Staf')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="manajemenAkunApp()">

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI SAUNG LESEHAN</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">Keamanan &amp; Staf</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#0B4D2B] text-[10px] font-bold text-white shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-[#FFDEA1]"></span>
                <span class="tracking-wide">FR-001 • PB-001 • LARAVEL BREEZE / SANCTUM GUARD</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Manajemen Akun Staf &amp; Hak Akses 6 Role
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Kelola kredensial login, PIN operasional, hak akses multi-role (Admin, Kasir, Koki, Barista, Pelayan, Pengguna), dan riwayat sesi staf aktif dalam satu kendali terpusat.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button 
                    type="button" 
                    @click="showToast('Memulai prosedur reset PIN massal staf operasional...')"
                    class="h-10 px-4 rounded-xl bg-[#E5E2DB] hover:bg-[#ded9cf] active:scale-95 text-[#1C1C18] font-semibold text-xs flex items-center gap-2 transition-all shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-[#1C1C18]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Reset PIN Masal</span>
                </button>

                <button 
                    type="button" 
                    @click="showToast('Membuka audit log riwayat autentikasi & sesi staf...')"
                    class="h-10 px-4 rounded-xl bg-[#E5E2DB] hover:bg-[#ded9cf] active:scale-95 text-[#1C1C18] font-semibold text-xs flex items-center gap-2 transition-all shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-[#1C1C18]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Log Akses Keamanan</span>
                </button>

                <button 
                    type="button" 
                    @click="addModalOpen = true"
                    class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs flex items-center gap-2 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Akun Staf Baru</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[160px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">TOTAL STAF TERDAFTAR</span>
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">14 Akun</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/40 flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-3 border-t border-gray-100">
                <span class="text-[10px] font-semibold text-[#0B4D2B]">6 Peran Operasional</span>
                <div class="flex items-center -space-x-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900] ring-2 ring-white"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0B4D2B] ring-2 ring-white"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#213200] ring-2 ring-white"></span>
                </div>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/20 blur-xl pointer-events-none"></div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">SESI STAF ONLINE</span>
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">5 Perangkat</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/50 flex items-center justify-center text-[#7A5900]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-1.5 text-[10px] font-bold text-[#404941] pt-3 border-t border-gray-100">
                <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                <span>1 Kasir, 1 Dapur, 1 Bar, 2 Mobile</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PROTEKSI KREDENSIAL</span>
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">100% Bcrypt</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#CBEF89]/60 flex items-center justify-center text-[#213200]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between text-[10px] font-bold text-[#707971] pt-3 border-t border-gray-100">
                <span>PIN-6 &amp; Sandi Terhash Aktif</span>
                <span class="text-[#0B4D2B]">TLS 1.3</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[160px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">RATA–RATA SESI SHIFT</span>
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">6.5 Jam</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] flex items-center justify-center text-[#1C1C18]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between text-[10px] font-bold text-[#404941] pt-3 border-t border-gray-100">
                <span>Rotasi Shift Saung</span>
                <span class="text-[#0B4D2B] flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Stabil</span>
                </span>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#00341A]/5 blur-xl pointer-events-none"></div>
        </div>

    </div>

    <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 text-xs">
            <button 
                type="button"
                @click="activeRoleFilter = 'all'"
                :class="activeRoleFilter === 'all' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Semua Staf</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 text-[#1C1C18]'">14</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'admin'"
                :class="activeRoleFilter === 'admin' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Admin/Owner</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'admin' ? 'bg-white/20 text-white' : 'bg-[#FFDEA1] text-[#261900]'">2</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'kasir'"
                :class="activeRoleFilter === 'kasir' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Kasir</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'kasir' ? 'bg-white/20 text-white' : 'bg-[#B1F1C2] text-[#00210E]'">3</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'koki'"
                :class="activeRoleFilter === 'koki' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Koki Dapur</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'koki' ? 'bg-white/20 text-white' : 'bg-[#E5E2DB] text-[#1C1C18]'">3</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'barista'"
                :class="activeRoleFilter === 'barista' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Barista</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'barista' ? 'bg-white/20 text-white' : 'bg-[#E5E2DB] text-[#1C1C18]'">2</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'pelayan'"
                :class="activeRoleFilter === 'pelayan' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Pelayan / Runner</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'pelayan' ? 'bg-white/20 text-white' : 'bg-[#E5E2DB] text-[#1C1C18]'">4</span>
            </button>
        </div>

        <div class="relative w-full md:w-72">
            <input 
                type="text" 
                x-model="searchQuery"
                placeholder="Cari nama staf, email akun, nomor ID..." 
                class="w-full h-10 pl-10 pr-4 rounded-xl bg-[#F6F3EC] text-xs border border-transparent focus:outline-hidden focus:border-[#0B4D2B] focus:bg-white transition-all shadow-inner">
            <svg class="w-4 h-4 text-[#707971] absolute left-3.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
            </svg>
        </div>

    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        
        <div class="p-4 bg-[#F6F3EC] border-b border-gray-200 flex items-center justify-between text-xs">
            <span class="font-bold text-[#1C1C18]">Menampilkan 7 dari 14 akun staf resmi Situ Awi Resto</span>
            <span class="text-[10px] font-bold text-[#0B4D2B] flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Enkripsi Sesi Aktif</span>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F0EEE7] border-b border-gray-200 text-[10px] font-bold text-[#404941] uppercase tracking-wider">
                        <th class="py-3 px-4 min-w-[200px]">PEGAWAI &amp; IDENTITAS</th>
                        <th class="py-3 px-4 min-w-[130px]">ROLE OPERASIONAL</th>
                        <th class="py-3 px-4 min-w-[170px]">EMAIL / ID AKUN</th>
                        <th class="py-3 px-4 min-w-[160px]">PERANGKAT &amp; STASIUN</th>
                        <th class="py-3 px-4 text-center min-w-[90px]">PIN CEPAT</th>
                        <th class="py-3 px-4 text-center min-w-[110px]">STATUS SESI</th>
                        <th class="py-3 px-4 min-w-[160px]">AKTIVITAS TERAKHIR</th>
                        <th class="py-3 px-4 text-right min-w-[120px]">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="s in filteredStaffs" :key="s.id">
                        <tr class="hover:bg-[#FBF8F1]/60 transition-colors">
                            
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-2xs"
                                        :class="s.avatarBg"
                                        x-text="s.avatar">
                                    </div>
                                    <div class="flex flex-col">
                                        <strong class="font-bold text-sm text-[#1C1C18] leading-tight" x-text="s.name"></strong>
                                        <span class="text-[10px] font-bold text-[#707971] mt-0.5" x-text="s.title"></span>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#FFDEA1] text-[#261900]': s.roleBadge === 'owner',
                                        'bg-[#0B4D2B] text-white shadow-2xs': s.roleBadge === 'kasir',
                                        'bg-[#334A00] text-[#99BB5C]': s.roleBadge === 'kitchen',
                                        'bg-[#E5E2DB] text-[#404941]': s.roleBadge === 'runner'
                                    }"
                                    x-text="s.role">
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-[#1C1C18]" x-text="s.email"></span>
                                    <span class="font-mono text-[10px] font-bold text-[#707971] mt-0.5" x-text="'ID: ' + s.id"></span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-[#404941]" x-text="s.device"></td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-md bg-[#F0EEE7] font-mono font-bold tracking-widest text-xs text-[#404941]">
                                    ••••••
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="s.status === 'online' ? 'bg-[#B1F1C2]/40 text-[#00341A]' : 'bg-[#E5E2DB] text-[#707971]'">
                                    <span 
                                        class="w-1.5 h-1.5 rounded-full"
                                        :class="s.status === 'online' ? 'bg-[#0B4D2B]' : 'bg-[#707971]'">
                                    </span>
                                    <span x-text="s.statusText"></span>
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="text-xs text-[#1C1C18] leading-tight" x-text="s.lastActive"></span>
                                    <span class="text-[10px] font-bold text-[#707971] mt-0.5" x-text="s.lastActiveTime"></span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <template x-if="s.roleKey !== 'admin'">
                                        <button 
                                            type="button" 
                                            @click="showToast('Permintaan reset PIN untuk ' + s.name + ' telah dikirim.')"
                                            class="px-2 py-1 rounded-lg bg-[#F0EEE7] hover:bg-gray-200 text-[#1C1C18] font-bold text-[10px] transition-all cursor-pointer">
                                            Reset PIN
                                        </button>
                                    </template>
                                    <button 
                                        type="button" 
                                        @click="showToast('Membuka rincian sesi dan log ' + s.name)"
                                        class="px-2 py-1 rounded-lg bg-[#F6F3EC] hover:bg-gray-200 text-[#707971] font-bold text-[10px] transition-all cursor-pointer">
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-[#707971]">
            <span>Sebelumnya</span>

            <div class="flex items-center gap-1">
                <button type="button" class="w-8 h-8 rounded-lg bg-[#0B4D2B] text-white font-bold flex items-center justify-center">1</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center font-bold">2</button>
            </div>

            <button type="button" class="hover:text-black">Berikutnya</button>
        </div>

    </div>

    <div class="bg-[#F0EEE7] rounded-3xl p-6 sm:p-8 shadow-xs border border-[#C0C9BF]/30 flex flex-col gap-6">
        
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-[#C0C9BF]/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#0B4D2B] flex items-center justify-center text-white shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-lg text-[#1C1C18]">Matrix Perizinan Hak Akses (Permissions Guard)</h2>
                    <p class="text-xs text-[#707971]">Pemisahan wewenang berdasar token bearer &amp; session guard Laravel Sanctum</p>
                </div>
            </div>

            <span class="px-3 py-1 rounded-full bg-[#B1F1C2] text-[10px] font-bold text-[#00341A] shadow-2xs flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>Enforced by Policy &amp; Gate</span>
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Role Admin</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Akses total seluruh sistem: CRUD Menu &amp; Stok, Tata Kelola Meja/Saung, Finansial Resto, Manajemen Akun, dan Telemetri Sensor IoT.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#7A5900]">Level 0 (Super)</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Role Kasir</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0B4D2B]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Buka/Tutup Kas shift kasir, Entri pesanan walk-in, Void tagihan berkode otorisasi, Cetak struk thermal Bluetooth, &amp; Pantau antrean bayar.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#0B4D2B]">Level 1 (POS Finansial)</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Koki &amp; Barista</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#213200]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Layar interaktif KDS Kitchen/Bar, filter pesanan per kategori menu, update status racikan (Diproses / Siap Saji), tandai stok habis instan.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#213200]">Level 2 (Produksi KDS)</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Pelayan / Runner</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2E6A45]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Penerimaan push MQTT notifikasi panggilan IoT saung, konfirmasi antar hidangan ke saung/lesehan, dan mobile input pesanan meja tamu.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#1C1C18]">Level 3 (Mobile Floor)</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Tamu (Self-Order)</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#707971]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Pemesanan mandiri via scan Token QR Saung. Akses dibatasi ketat tanpa izin membaca konsol internal manajemen resto.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#707971]">Level 4 (Guest Token)</span>
                </div>
            </div>

        </div>
    </div>

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
            class="w-full max-w-lg bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5"
            @click.outside="addModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Tambah Akun Staf Baru</h3>
                    <p class="text-xs text-[#707971] mt-0.5">Konfigurasi peran operasional &amp; PIN login POS</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.akun.simpan') }}" class="flex flex-col gap-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Nama Lengkap *</label>
                        <input type="text" name="nama" placeholder="Contoh: Kang Dani" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Peran Operasional *</label>
                        <select name="role" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            <option value="kasir">Kasir Utama</option>
                            <option value="koki">Koki Dapur</option>
                            <option value="barista">Barista Bar</option>
                            <option value="pelayan">Pelayan / Runner</option>
                            <option value="admin">Admin Konsol</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Email Akun *</label>
                        <input type="email" name="email" placeholder="staf@situawi.com" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kata Sandi (min 6) *</label>
                        <input type="password" name="password" placeholder="123456" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
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
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

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
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3C7.7 6.2 6 8.4 6 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span x-text="toast.message"></span>
    </div>

</div>

<script>
    function manajemenAkunApp() {
        return {
            activeRoleFilter: 'all',
            searchQuery: '',
            addModalOpen: false,
            toast: { show: false, message: '' },
            staffs: @json($stafJson),

            showToast(msg) {
                this.toast.message = msg;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 3000);
            },

            get filteredStaffs() {
                return this.staffs.filter(s => {
                    if (this.activeRoleFilter !== 'all' && s.roleKey !== this.activeRoleFilter) return false;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        return s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q) || s.id.toLowerCase().includes(q) || s.role.toLowerCase().includes(q);
                    }
                    return true;
                });
            }
        };
    }
</script>
@endsection

