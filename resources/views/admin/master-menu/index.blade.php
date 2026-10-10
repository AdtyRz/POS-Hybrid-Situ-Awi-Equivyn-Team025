@extends('layouts.admin')

@section('title', 'Master Data Menu, Resep & Stok')
@section('page_category', 'Katalog & Manajemen Stok')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="masterMenuApp()">

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI</span>
                <span>/</span>
                <span class="text-[#404941]">KATALOG & MANAJEMEN STOK</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FFDEA1]/40 text-[10px] font-bold text-[#261900]">
                <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                <span class="tracking-wide">FR-002 • PB-002 • AUTO-CUT SYSTEM</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Katalog Menu, Resep Dapur/Bar & Stok
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Kelola harga porsi, pemisahan pesanan KDS Dapur vs KDS Bar, sakelar ketersediaan instan (&lt;200ms), serta peringatan stok bahan segar saung secara tersinkronisasi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button 
                    type="button" 
                    @click="showToast('Membuka sakelar cepat habis darurat...')"
                    class="h-10 px-4 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] active:scale-95 text-[#1C1C18] font-semibold text-xs flex items-center gap-2 transition-all shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Sakelar Cepat Habis</span>
                </button>

                <button 
                    type="button" 
                    @click="showToast('Membuka dialog Impor / Ekspor data menu CSV...')"
                    class="h-10 px-4 rounded-xl bg-white hover:bg-gray-50 active:scale-95 text-[#1C1C18] font-semibold text-xs flex items-center gap-2 border border-gray-200 transition-all shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Impor / Ekspor CSV</span>
                </button>

                <button 
                    type="button" 
                    @click="addModalOpen = true"
                    class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs flex items-center gap-2 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#B1F1C2] stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>+ Tambah Menu Baru</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px] relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">TOTAL MENU AKTIF</span>
                <div class="w-10 h-10 rounded-xl bg-[#B1F1C2]/50 flex items-center justify-center text-[#0B4D2B]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight">
                    42 Item
                </span>
                <div class="p-2 bg-[#F0EEE7]/60 rounded-lg flex items-center justify-between mt-3 text-[10px] font-bold text-[#404941]">
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                        26 Makanan Dapur
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                        16 Minuman Bar
                    </span>
                </div>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/25 blur-xl pointer-events-none"></div>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">PERINGATAN STOK MENIPIS</span>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/60 flex items-center justify-center text-[#261900]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#7A5900] tracking-tight leading-tight">
                    3 Bahan
                </span>
                <div class="p-2 bg-[#FFDEA1]/20 rounded-lg flex items-center justify-between mt-3 text-[10px] font-bold text-[#1C1C18]">
                    <span class="truncate">Gurame Kolam, Kelapa Ijo, Maranggi</span>
                    <span class="text-[#7A5900]">→</span>
                </div>
            </div>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">ITEM NONAKTIF / HABIS</span>
                <div class="w-10 h-10 rounded-xl bg-[#FFDAD6] flex items-center justify-center text-[#93000A]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#BA1A1A] tracking-tight leading-tight">
                    2 Menu
                </span>
                <div class="p-2 bg-[#FFDAD6]/40 rounded-lg flex items-center justify-between mt-3 text-[10px] font-bold text-[#93000A]">
                    <span class="truncate">Sate Maranggi, Es Cincau</span>
                    <button type="button" @click="showToast('Buka antrean restock')" class="underline hover:text-black">Restock</button>
                </div>
            </div>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px] relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">RATA–RATA MARGIN MENU</span>
                <div class="w-10 h-10 rounded-xl bg-[#CBEF89]/60 flex items-center justify-center text-[#213200]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#334A00] tracking-tight leading-tight">
                    68.4%
                </span>
                <div class="p-2 bg-[#CBEF89]/20 rounded-lg flex items-center justify-between mt-3 text-[10px] font-bold">
                    <span class="text-[#213200]">Target Cost 31.6%</span>
                    <span class="text-[#0B4D2B] flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Terkendali</span>
                    </span>
                </div>
            </div>

            <div class="absolute -left-6 -top-6 w-24 h-24 rounded-full bg-[#00341A]/5 blur-xl pointer-events-none"></div>
        </div>

    </div>

    <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 flex flex-col gap-4">
        
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:flex-1">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    placeholder="Cari nama kuliner, SKU, bahan dasar, saung..." 
                    class="w-full h-11 pl-11 pr-4 rounded-xl bg-[#F6F3EC] text-xs border border-transparent focus:outline-hidden focus:border-[#0B4D2B] focus:bg-white transition-all shadow-inner">
                <svg class="w-4 h-4 text-[#707971] absolute left-4 top-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
                </svg>
            </div>

            <div class="bg-[#F6F3EC] p-1 rounded-xl flex items-center gap-1 w-full md:w-auto">
                <button 
                    type="button" 
                    @click="statusFilter = 'semua'"
                    :class="statusFilter === 'semua' ? 'bg-white text-[#00341A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs transition-all cursor-pointer">
                    Semua Status
                </button>
                <button 
                    type="button" 
                    @click="statusFilter = 'tersedia'"
                    :class="statusFilter === 'tersedia' ? 'bg-white text-[#00341A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs transition-all cursor-pointer">
                    Hanya Tersedia
                </button>
                <button 
                    type="button" 
                    @click="statusFilter = 'kritis_habis'"
                    :class="statusFilter === 'kritis_habis' ? 'bg-white text-[#BA1A1A] shadow-xs font-bold' : 'text-[#404941] font-semibold hover:text-black'"
                    class="flex-1 md:flex-none px-4 py-2 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-[#BA1A1A]"></span>
                    <span>Kritis / Habis</span>
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <button
                type="button"
                @click="categoryFilter = 'all'"
                :class="categoryFilter === 'all' ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-medium'"
                class="px-4 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>Semua Kategori</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="categoryFilter === 'all' ? 'bg-white/20 text-white' : 'bg-[#DCDAD3] text-[#404941]'">{{ $menuJson->count() }}</span>
            </button>
            @foreach ($kategoriJson as $kat)
            <button
                type="button"
                @click="categoryFilter = '{{ $kat['id'] }}'"
                :class="categoryFilter === '{{ $kat['id'] }}' ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-medium'"
                class="px-4 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                <span>{{ $kat['name'] }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="categoryFilter === '{{ $kat['id'] }}' ? 'bg-white/20 text-white' : 'bg-[#DCDAD3] text-[#404941]'">{{ $menuJson->where('category', $kat['id'])->count() }}</span>
            </button>
            @endforeach
        </div>

    </div>

    <div class="p-4 bg-white rounded-2xl shadow-xs border border-[#C0C9BF]/30 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#B1F1C2] flex items-center justify-center text-[#0B4D2B] flex-shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
            </div>
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-[#1C1C18]">Protokol Sinkronisasi MQTT WebSocket Aktif</span>
                    <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                </div>
                <p class="text-xs text-[#404941] mt-0.5 leading-snug">
                    Perubahan sakelar stok instan otomatis mematikan / menyalakan opsi di Kios Mandiri & 11 QR Saung dalam &lt;200ms.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto justify-end">
            <button 
                type="button" 
                @click="showToast('Sinyal sinkronisasi dikirim ke KDS Dapur & Bar.')"
                class="h-10 px-4 rounded-xl bg-[#EBE8E1] hover:bg-[#ded9cf] text-[#1C1C18] font-semibold text-xs flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Sinkronkan ke KDS Dapur & Bar</span>
            </button>
            <button 
                type="button" 
                @click="showToast('Seluruh perubahan stok massal berhasil disimpan.')"
                class="h-10 px-5 rounded-xl bg-[#FECE66] hover:bg-[#ebd055] active:scale-95 text-[#765600] font-bold text-xs flex items-center gap-1.5 transition-all shadow-xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2M8 7h8M12 12v5m0 0l-2-2m2 2l2-2" />
                </svg>
                <span>Simpan Perubahan Stok Masal</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
        
        <div class="p-4 bg-[#F6F3EC] border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-sm text-[#1C1C18]">Daftar Menu & Stok Terkini</h3>
                <span class="px-2.5 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[10px] font-bold text-[#00210E]" x-text="filteredMenus.length + ' Terpilih dari ' + menus.length">
                    7 Terpilih dari 42
                </span>
            </div>

            <div class="flex items-center gap-1 text-[10px] font-bold text-[#707971]">
                <svg class="w-3.5 h-3.5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                </svg>
                <span>Diurutkan: Popularitas & Urutan Dapur</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F0EEE7] border-b border-gray-200 text-[10px] font-bold text-[#404941] uppercase tracking-wider">
                        <th class="py-3 px-4 min-w-[240px]">FOTO & NAMA MENU / SKU</th>
                        <th class="py-3 px-4 min-w-[160px]">KATEGORI KDS</th>
                        <th class="py-3 px-4 min-w-[120px]">HARGA PORSI</th>
                        <th class="py-3 px-4 min-w-[120px]">STOK FISIK / SATUAN</th>
                        <th class="py-3 px-4 min-w-[140px]">STATUS ETALASE E-MENU</th>
                        <th class="py-3 px-4 text-center min-w-[100px]">SAKELAR KETERSEDIAAN</th>
                        <th class="py-3 px-4 text-right min-w-[100px]">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <template x-for="item in filteredMenus" :key="item.id">
                        <tr 
                            class="transition-colors"
                            :class="!item.enabled ? 'bg-red-50/25 opacity-85' : 'hover:bg-[#FBF8F1]/60'">
                            
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#F0EEE7] flex items-center justify-center flex-shrink-0 shadow-2xs border border-gray-200/60">
                                        <svg class="w-6 h-6 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4.5"/><path stroke-linecap="round" d="M12 2v2M12 20v2M2 12h2M20 12h2"/></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-[#1C1C18] leading-tight" :class="!item.enabled ? 'line-through text-gray-500' : ''" x-text="item.name"></span>
                                        <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                                            <span class="font-mono font-bold text-[#707971]" x-text="item.sku"></span>
                                            <span class="w-1 h-1 rounded-full bg-[#C0C9BF]"></span>
                                            <span class="font-medium" :class="!item.enabled ? 'text-[#BA1A1A] font-bold' : 'text-[#00341A]'" x-text="item.badge"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold"
                                    :class="item.kdsType === 'dapur' ? 'bg-[#B1F1C2]/40 text-[#0B4D2B]' : 'bg-[#FFDEA1]/50 text-[#7A5900]'">
                                     <span x-text="item.kds"></span>
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-sm text-[#00341A]" x-text="'Rp ' + item.price.toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] font-bold text-[#707971] mt-0.5" x-text="'HPP: Rp ' + item.hpp.toLocaleString('id-ID')"></span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span 
                                        class="font-bold text-xs"
                                        :class="{
                                            'text-[#BA1A1A]': item.stockStatus === 'habis',
                                            'text-[#7A5900]': item.stockStatus === 'kritis',
                                            'text-[#1C1C18]': item.stockStatus === 'normal'
                                        }"
                                        x-text="item.stockQty">
                                    </span>
                                    <span class="text-[10px] font-semibold text-[#707971] mt-0.5" x-text="item.stockNote"></span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide"
                                    :class="{
                                        'bg-[#B1F1C2]/60 text-[#00210E]': item.status === 'tersedia',
                                        'bg-[#FFDEA1] text-[#261900]': item.status === 'hampir_habis',
                                        'bg-[#FFDAD6] text-[#93000A]': item.status === 'habis'
                                    }">
                                    <span 
                                        class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-[#0B4D2B]': item.status === 'tersedia',
                                            'bg-[#7A5900]': item.status === 'hampir_habis',
                                            'bg-[#93000A]': item.status === 'habis'
                                        }">
                                    </span>
                                    <span x-text="item.statusText"></span>
                                </span>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <button 
                                    type="button" 
                                    @click="toggleSwitch(item)"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors cursor-pointer"
                                    :class="item.enabled ? 'bg-[#0B4D2B]' : 'bg-[#DCDAD3]'">
                                    <span 
                                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform"
                                        :class="item.enabled ? 'translate-x-5' : 'translate-x-1'">
                                    </span>
                                </button>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <template x-if="!item.enabled">
                                        <button 
                                            type="button" 
                                            @click="toggleSwitch(item)"
                                            class="px-2.5 py-1 rounded-lg bg-[#FFDAD6] text-[#93000A] font-bold text-[10px] hover:bg-[#fcd0cb] cursor-pointer">
                                            Restock
                                        </button>
                                    </template>
                                    <button 
                                        type="button" 
                                        @click="showToast('Membuka rincian resep & formula ' + item.name)"
                                        class="p-1.5 rounded-lg text-[#404941] hover:bg-[#F0EEE7] cursor-pointer" title="Lihat Resep">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="showToast('Membuka riwayat persediaan bahan ' + item.name)"
                                        class="p-1.5 rounded-lg text-[#7A5900] hover:bg-[#F0EEE7] cursor-pointer" title="Riwayat Stok">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-[#707971]">
            <div class="flex items-center gap-1.5">
                <span>Menampilkan {{ $menuJson->count() }} Menu</span>
                <span>•</span>
                <span class="text-[#0B4D2B]">Pembaruan Terakhir: Baru saja (14:32 WIB)</span>
            </div>

            <div class="flex items-center gap-1">
                <button type="button" class="w-8 h-8 rounded-lg bg-[#0B4D2B] text-white font-bold flex items-center justify-center">1</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center font-bold">2</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#1C1C18] hover:bg-gray-100 flex items-center justify-center font-bold">3</button>
                <button type="button" class="w-8 h-8 rounded-lg bg-[#F0EEE7] text-[#1C1C18] hover:bg-gray-200 flex items-center justify-center font-bold">&gt;</button>
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
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Tambah Menu & Resep Baru</h3>
                    <p class="text-xs text-[#707971] mt-0.5">Konfigurasi harga, stasiun KDS & alokasi bahan</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.menu.simpan') }}" class="flex flex-col gap-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kategori *</label>
                        <select name="kategori_id" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            @foreach ($kategoriForm as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Stok Awal (Porsi) *</label>
                        <input type="number" name="stok_menu" placeholder="Contoh: 50" min="0" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#1C1C18]">Nama Menu Kuliner *</label>
                    <input type="text" name="nama_menu" placeholder="Contoh: Gurame Saus Padang Situ Awi" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Harga Porsi Jual (Rp) *</label>
                        <input type="number" name="harga" placeholder="85000" min="0" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Deskripsi</label>
                        <input type="text" name="deskripsi" placeholder="Deskripsi singkat menu" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
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
                        Simpan Menu
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
    function masterMenuApp() {
        return {
            statusFilter: 'semua',
            categoryFilter: 'all',
            searchQuery: '',
            addModalOpen: false,
            selectedItem: null,
            toast: { show: false, message: '' },
            menus: @json($menuJson),

            showToast(msg) {
                this.toast.message = msg;
                this.toast.show = true;
                setTimeout(() => this.toast.show = false, 3000);
            },

            toggleSwitch(item) {
                item.enabled = !item.enabled;
                if (item.enabled) {
                    item.status = 'tersedia';
                    item.statusText = 'Tersedia Live';
                    this.showToast(item.name + ' aktif di E-Menu & Kios (<200ms)');
                } else {
                    item.status = 'habis';
                    item.statusText = 'HABIS / Nonaktif';
                    this.showToast(item.name + ' dinonaktifkan di E-Menu & Kios');
                }
            },

            get filteredMenus() {
                return this.menus.filter(item => {
                    if (this.statusFilter === 'tersedia' && !item.enabled) return false;
                    if (this.statusFilter === 'kritis_habis' && item.status !== 'habis' && item.status !== 'hampir_habis') return false;
                    if (this.categoryFilter !== 'all' && item.category !== this.categoryFilter) return false;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        return item.name.toLowerCase().includes(q) || item.sku.toLowerCase().includes(q) || item.badge.toLowerCase().includes(q);
                    }
                    return true;
                });
            }
        };
    }
</script>
@endsection

