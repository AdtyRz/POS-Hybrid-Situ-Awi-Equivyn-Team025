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
                    Manajemen Akun Staf &amp; Hak Akses {{ count($labelPeran) }} Role
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Kelola kredensial login, hak akses multi-role ({{ implode(', ', array_values($labelPeran)) }}), dan riwayat sesi staf aktif dalam satu kendali terpusat.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    @click="securityModalOpen = true"
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
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">{{ $statistik['total'] }} Akun</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/40 flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-3 border-t border-gray-100">
                <span class="text-[10px] font-semibold text-[#0B4D2B]">{{ count($labelPeran) }} Peran Operasional</span>
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
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">{{ $statistik['online'] }} Perangkat</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/50 flex items-center justify-center text-[#7A5900]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-1.5 text-[10px] font-bold text-[#404941] pt-3 border-t border-gray-100">
                <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                <span>{{ $statistik['ringkasan_online'] }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PROTEKSI KREDENSIAL</span>
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">100% {{ ucfirst($statistik['hash_driver']) }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#CBEF89]/60 flex items-center justify-center text-[#213200]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between text-[10px] font-bold text-[#707971] pt-3 border-t border-gray-100">
                <span>Sandi Terhash Aktif</span>
                <span class="text-[#0B4D2B]">{{ request()->isSecure() ? 'TLS Aktif' : 'Session Guard' }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col justify-between min-h-[160px] relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">AKUN NONAKTIF</span>
                    <span class="font-display font-bold text-2xl sm:text-[26px] text-[#1C1C18] block mt-1">{{ $statistik['nonaktif'] }} Akun</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-[#E5E2DB] flex items-center justify-center text-[#1C1C18]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center justify-between text-[10px] font-bold text-[#404941] pt-3 border-t border-gray-100">
                <span>Status Keamanan Akun</span>
                <span class="{{ $statistik['nonaktif'] === 0 ? 'text-[#0B4D2B]' : 'text-[#BA1A1A]' }} flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $statistik['nonaktif'] === 0 ? 'Stabil' : 'Perlu Tinjauan' }}</span>
                </span>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#00341A]/5 blur-xl pointer-events-none"></div>
        </div>

    </div>

    <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 text-xs">
            <button 
                type="button"
                @click="activeRoleFilter = 'all'; currentPage = 1"
                :class="activeRoleFilter === 'all' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Semua Staf</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 text-[#1C1C18]'">{{ $statistik['total'] }}</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'admin'; currentPage = 1"
                :class="activeRoleFilter === 'admin' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Admin/Owner</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'admin' ? 'bg-white/20 text-white' : 'bg-[#FFDEA1] text-[#261900]'">{{ $jumlahPeran['admin'] }}</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'kasir'; currentPage = 1"
                :class="activeRoleFilter === 'kasir' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Kasir</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'kasir' ? 'bg-white/20 text-white' : 'bg-[#B1F1C2] text-[#00210E]'">{{ $jumlahPeran['kasir'] }}</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'koki'; currentPage = 1"
                :class="activeRoleFilter === 'koki' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Koki Dapur</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'koki' ? 'bg-white/20 text-white' : 'bg-[#E5E2DB] text-[#1C1C18]'">{{ $jumlahPeran['koki'] }}</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'barista'; currentPage = 1"
                :class="activeRoleFilter === 'barista' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Barista</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'barista' ? 'bg-white/20 text-white' : 'bg-[#E5E2DB] text-[#1C1C18]'">{{ $jumlahPeran['barista'] }}</span>
            </button>

            <button 
                type="button"
                @click="activeRoleFilter = 'pelayan'; currentPage = 1"
                :class="activeRoleFilter === 'pelayan' ? 'bg-[#0B4D2B] text-white shadow-xs font-bold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-semibold'"
                class="px-3.5 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Pelayan / Runner</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeRoleFilter === 'pelayan' ? 'bg-white/20 text-white' : 'bg-[#E5E2DB] text-[#1C1C18]'">{{ $jumlahPeran['pelayan'] }}</span>
            </button>
        </div>

        <div class="relative w-full md:w-72">
            <input 
                type="text" 
                x-model="searchQuery"
                @input="currentPage = 1"
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
            <span class="font-bold text-[#1C1C18]">Menampilkan <span x-text="filteredStaffs.length"></span> dari {{ $statistik['total'] }} akun staf resmi Situ Awi Resto</span>
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
                        <th class="py-3 px-4 text-center min-w-[110px]">STATUS SESI</th>
                        <th class="py-3 px-4 min-w-[160px]">AKTIVITAS TERAKHIR</th>
                        <th class="py-3 px-4 text-right min-w-[120px]">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="s in paginatedStaffs" :key="s.id">
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
                                <span 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#B1F1C2]/40 text-[#00341A]': s.status === 'online',
                                        'bg-[#FFDEA1]/60 text-[#7A4B00]': s.status === 'standby',
                                        'bg-[#E5E2DB] text-[#707971]': s.status !== 'online' && s.status !== 'standby'
                                    }">
                                    <span 
                                        class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-[#0B4D2B]': s.status === 'online',
                                            'bg-[#C9982F]': s.status === 'standby',
                                            'bg-[#707971]': s.status !== 'online' && s.status !== 'standby'
                                        }">
                                    </span>
                                    <span x-text="s.statusText"></span>
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="flex flex-col">
                                    <span class="text-xs text-[#1C1C18] leading-tight" x-text="waktuRelatif(s.lastActiveTs)"></span>
                                    <span class="text-[10px] font-bold text-[#707971] mt-0.5" x-text="formatWib(s.lastActiveTs)"></span>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        @click="openDetail(s)"
                                        class="px-2 py-1 rounded-lg bg-[#F6F3EC] hover:bg-gray-200 text-[#707971] font-bold text-[10px] transition-all cursor-pointer">
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="paginatedStaffs.length === 0">
                        <tr>
                            <td colspan="7" class="py-10 px-4 text-center text-xs font-semibold text-[#707971]">
                                Tidak ada akun staf yang sesuai filter.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-[#707971]">
            <button type="button" @click="prevPage" :disabled="currentPage === 1" :class="currentPage === 1 ? 'text-gray-400 cursor-not-allowed' : 'hover:text-black cursor-pointer'">Sebelumnya</button>

            <div class="flex items-center gap-1">
                <template x-for="halaman in pageNumbers" :key="halaman">
                    <button
                        type="button"
                        @click="goPage(halaman)"
                        class="w-8 h-8 rounded-lg font-bold flex items-center justify-center transition-colors"
                        :class="halaman === currentPage ? 'bg-[#0B4D2B] text-white' : 'bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100'"
                        x-text="halaman">
                    </button>
                </template>
            </div>

            <button type="button" @click="nextPage" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'text-gray-400 cursor-not-allowed' : 'hover:text-black cursor-pointer'">Berikutnya</button>
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
                        <h4 class="font-bold text-sm text-[#1C1C18]">Koki Dapur</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#213200]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Layar KDS Dapur, filter pesanan per kategori menu, update status masakan (Diproses / Siap Saji), dan tandai stok habis.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#213200]">Level 2 (Produksi Dapur)</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Barista Bar</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2E6A45]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Layar KDS Bar, filter pesanan minuman, update status racikan (Diproses / Siap Saji), dan kelola ketersediaan bahan minuman.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#1C1C18]">Level 3 (Produksi Bar)</span>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-2xs border border-gray-100 flex flex-col justify-between min-h-[220px]">
                <div>
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-sm text-[#1C1C18]">Pelayan / Runner</h4>
                        <span class="w-2.5 h-2.5 rounded-full bg-[#707971]"></span>
                    </div>
                    <p class="text-xs text-[#404941] mt-2 leading-relaxed">
                        Penerimaan notifikasi panggilan saung via HTTP API, konfirmasi antar hidangan ke meja, dan pemantauan status sesi saung.
                    </p>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-[10px] font-bold">
                    <span class="text-[#707971]">Tingkat Akses</span>
                    <span class="text-[#707971]">Level 4 (Mobile Floor)</span>
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
                    <p class="text-xs text-[#707971] mt-0.5">Konfigurasi peran operasional &amp; akses akun staf</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.akun.simpan') }}" class="flex flex-col gap-4 text-xs">
                @csrf
                <input type="hidden" name="_form" value="tambah">
                @if ($errors->any() && old('_form') === 'tambah')
                    <div class="rounded-xl bg-[#FFDAD6] border border-[#BA1A1A]/20 px-3 py-2 text-[11px] font-semibold text-[#93000A]">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Nama Lengkap *</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Contoh: Kang Dani" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Peran Operasional *</label>
                        <select name="role" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            <option value="kasir" @selected(old('role') === 'kasir')>Kasir Utama</option>
                            <option value="koki" @selected(old('role') === 'koki')>Koki Dapur</option>
                            <option value="barista" @selected(old('role') === 'barista')>Barista Bar</option>
                            <option value="pelayan" @selected(old('role') === 'pelayan')>Pelayan / Runner</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin Konsol</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Email Akun *</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="staf@situawi.com" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
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
        x-show="detailOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-xl bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5 max-h-[90vh] overflow-y-auto" @click.outside="closeDetail()">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Detail &amp; Edit Akun Staf</h3>
                    <p class="text-xs text-[#707971] mt-0.5"><span x-text="selectedStaff?.name || ''"></span> • <span x-text="selectedStaff?.role || ''"></span></p>
                </div>
                <button type="button" @click="closeDetail()" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" :action="'{{ url('/admin/akun') }}/' + (selectedStaff?.id || '')" class="flex flex-col gap-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">
                <input type="hidden" name="_edit_id" :value="selectedStaff?.id || ''">
                @if ($errors->any() && old('_form') === 'edit')
                    <div class="rounded-xl bg-[#FFDAD6] border border-[#BA1A1A]/20 px-3 py-2 text-[11px] font-semibold text-[#93000A]">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Nama Lengkap *</label>
                        <input type="text" name="nama" x-model="editForm.nama" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Peran Operasional *</label>
                        <select name="role" x-model="editForm.role" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            <option value="admin">Admin Konsol</option>
                            <option value="kasir">Kasir Utama</option>
                            <option value="koki">Koki Dapur</option>
                            <option value="barista">Barista Bar</option>
                            <option value="pelayan">Pelayan / Runner</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Email Akun *</label>
                        <input type="email" name="email" x-model="editForm.email" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Nomor Telepon</label>
                        <input type="text" name="no_telp" x-model="editForm.no_telp" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Area Penugasan</label>
                        <input type="text" name="area" x-model="editForm.area" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kata Sandi Baru</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tetap" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="closeDetail()" class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 active:scale-95 transition-all cursor-pointer">Batal</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] active:scale-95 transition-all shadow-xs cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100 text-[11px]">
                <div class="flex flex-col gap-0.5 text-[#707971]">
                    <span>ID Akun: <strong class="font-mono text-[#1C1C18]" x-text="selectedStaff?.id || '-'"></strong></span>
                    <span>Terakhir aktif: <strong class="text-[#1C1C18]" x-text="formatWib(selectedStaff?.lastActiveTs)"></strong></span>
                </div>
                <form method="POST" :action="'{{ url('/admin/akun') }}/' + (selectedStaff?.id || '')" @submit.prevent="mintaKonfirmasiHapus($event)">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        :disabled="selectedStaff?.isSelf || selectedStaff?.isLastAdmin"
                        class="px-3 py-2 rounded-lg font-bold text-[10px] transition-all bg-[#FFDAD6] text-[#93000A] hover:bg-[#ffc9c4]"
                        :class="{ 'opacity-50 cursor-not-allowed': selectedStaff?.isSelf || selectedStaff?.isLastAdmin }">
                        Hapus Akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div
        x-cloak
        x-show="securityModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="w-full max-w-2xl bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5 max-h-[90vh] overflow-y-auto" @click.outside="securityModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Log Akses Keamanan</h3>
                    <p class="text-xs text-[#707971] mt-0.5">Sesi autentikasi staf yang masih aktif pada masa berlaku session.</p>
                </div>
                <button type="button" @click="securityModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col divide-y divide-gray-100">
                <template x-if="sessions.length === 0">
                    <div class="py-8 text-center text-xs font-semibold text-[#707971]">Belum ada sesi staf aktif.</div>
                </template>
                <template x-for="sesi in sessions" :key="sesi.nama + sesi.terakhir + sesi.ip">
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="flex flex-col">
                            <strong class="text-[#1C1C18]" x-text="sesi.nama"></strong>
                            <span class="text-[10px] text-[#707971]" x-text="sesi.peran + ' • ' + sesi.perangkat"></span>
                        </div>
                        <div class="flex flex-col items-end text-right">
                            <span class="font-mono text-[10px] font-bold text-[#1C1C18]" x-text="sesi.ip"></span>
                            <span class="text-[10px] text-[#707971]" x-text="waktuRelatif(sesi.terakhirTs) + ' • ' + formatWib(sesi.terakhirTs)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div
        x-cloak
        x-show="deleteConfirmOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden" @click.outside="deleteConfirmOpen = false">
            <div class="px-5 py-4 border-b border-gray-100 flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#FFDAD6] text-[#93000A] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-display font-bold text-lg text-[#00341A]">Hapus Akun?</h3>
                    <p class="text-xs text-[#707971] mt-1 leading-relaxed">
                        Akun <strong class="text-[#1C1C18]" x-text="selectedStaff?.name || 'staf ini'"></strong> akan dihapus permanen beserta sesi login yang aktif.
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="px-5 py-4 bg-[#FAF7F0] flex items-center justify-end gap-2">
                <button type="button" @click="deleteConfirmOpen = false"
                        class="px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-bold text-xs hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" @click="jalankanKonfirmasiHapus()"
                        class="px-4 py-2.5 rounded-xl bg-[#BA1A1A] hover:bg-[#93000A] text-white font-bold text-xs transition">
                    Ya, Hapus Akun
                </button>
            </div>
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
            addModalOpen: @js($errors->any() && old('_form') === 'tambah'),
            detailOpen: false,
            securityModalOpen: false,
            currentPage: 1,
            perPage: 8,
            selectedStaff: null,
            editForm: {
                nama: '',
                email: '',
                role: 'kasir',
                no_telp: '',
                area: ''
            },
            deleteConfirmOpen: false,
            pendingDeleteForm: null,
            toast: { show: false, message: '' },
            now: Date.now(),
            staffs: @json($stafJson),
            sessions: @json($sesiJson),
            initialEditId: @js(old('_edit_id')),
            flashSuccess: @js(session('sukses')),

            init() {
                if (this.flashSuccess) {
                    this.showToast(this.flashSuccess);
                }

                if (this.initialEditId) {
                    const staf = this.staffs.find(item => String(item.id) === String(this.initialEditId));
                    if (staf) {
                        this.openDetail(staf);
                    }
                }

                this.penunjukWaktu = setInterval(() => {
                    this.now = Date.now();
                }, 1000);
            },

            waktuRelatif(ts) {
                if (! ts) {
                    return 'Belum ada sesi';
                }

                const detik = Math.max(0, Math.floor((this.now - (Number(ts) * 1000)) / 1000));

                if (detik < 10) return 'Baru saja';
                if (detik < 60) return detik + ' detik lalu';

                const menit = Math.floor(detik / 60);
                if (menit < 60) return menit + ' menit lalu';

                const jam = Math.floor(menit / 60);
                if (jam < 24) return jam + ' jam lalu';

                const hari = Math.floor(jam / 24);
                return hari + ' hari lalu';
            },

            formatWib(ts) {
                if (! ts) {
                    return '-';
                }

                const bagian = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                }).formatToParts(new Date(Number(ts) * 1000));

                const nilai = Object.fromEntries(bagian.map(item => [item.type, item.value]));

                return `${nilai.day} ${nilai.month} ${nilai.year} ${nilai.hour}:${nilai.minute} WIB`;
            },

            showToast(msg) {
                this.toast.message = msg;
                this.toast.show = true;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => this.toast.show = false, 3200);
            },

            get filteredStaffs() {
                return this.staffs.filter(s => {
                    if (this.activeRoleFilter !== 'all' && s.roleKey !== this.activeRoleFilter) return false;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        return s.name.toLowerCase().includes(q) || s.email.toLowerCase().includes(q) || String(s.id).includes(q) || s.role.toLowerCase().includes(q) || s.device.toLowerCase().includes(q);
                    }
                    return true;
                });
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredStaffs.length / this.perPage));
            },

            get pageNumbers() {
                return Array.from({ length: this.totalPages }, (_, index) => index + 1);
            },

            get paginatedStaffs() {
                const halaman = Math.min(this.currentPage, this.totalPages);
                const awal = (halaman - 1) * this.perPage;

                return this.filteredStaffs.slice(awal, awal + this.perPage);
            },

            goPage(halaman) {
                this.currentPage = Math.max(1, Math.min(halaman, this.totalPages));
            },

            prevPage() {
                this.goPage(this.currentPage - 1);
            },

            nextPage() {
                this.goPage(this.currentPage + 1);
            },

            openDetail(staf) {
                this.selectedStaff = staf;
                this.editForm = {
                    nama: staf.name,
                    email: staf.email,
                    role: staf.roleKey,
                    no_telp: staf.phone === '-' ? '' : staf.phone,
                    area: staf.area === 'Semua Area' ? '' : staf.area
                };
                this.detailOpen = true;
            },

            closeDetail() {
                this.detailOpen = false;
            },

            mintaKonfirmasiHapus(event) {
                this.pendingDeleteForm = event.target;
                this.deleteConfirmOpen = true;
            },

            jalankanKonfirmasiHapus() {
                this.deleteConfirmOpen = false;
                const form = this.pendingDeleteForm;
                this.pendingDeleteForm = null;
                if (form) {
                    HTMLFormElement.prototype.submit.call(form);
                }
            }
        };
    }
</script>
@endsection

