@extends('layouts.admin')

@section('title', 'Master Data Menu & Stok')
@section('page_category', 'Katalog & Manajemen Stok')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="masterMenuApp()">

    @if (session('sukses'))
    <div class="p-4 rounded-2xl bg-[#B1F1C2]/50 border border-[#0B4D2B]/15 text-xs font-semibold text-[#0B4D2B] flex items-center gap-2.5">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('sukses') }}</span>
    </div>
    @endif

    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-[#FFDAD6]/50 border border-[#BA1A1A]/15 text-xs font-semibold text-[#93000A] flex items-start gap-2.5">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <ul class="flex flex-col gap-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI</span>
                <span>/</span>
                <span class="text-[#404941]">KATALOG & MANAJEMEN STOK</span>
            </div>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#B1F1C2]/50 text-[10px] font-bold text-[#0B4D2B]">
                <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                <span class="tracking-wide">STOK OTOMATIS SETELAH LUNAS</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Master Data Menu & Stok
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Kelola harga, stok porsi, kategori KDS, dan ketersediaan menu. Perubahan data disiarkan realtime ke e-menu, kasir, dan KDS.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <button
                    type="button"
                    @click="addModalOpen = true"
                    class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs flex items-center gap-2 shadow-md transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#B1F1C2] stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Menu Baru</span>
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

            <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight" x-text="activeMenus.length + ' Item'"></span>
            <div class="p-2 bg-[#F0EEE7]/60 rounded-lg flex items-center justify-between mt-3 text-[10px] font-bold text-[#404941]">
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0B4D2B]"></span>
                    <span x-text="dapurCount + ' Makanan'"></span>
                </span>
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                    <span x-text="barCount + ' Minuman'"></span>
                </span>
            </div>

            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-[#FFDEA1]/25 blur-xl pointer-events-none"></div>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STOK PORSI<br>MENIPIS</span>
                <div class="w-10 h-10 rounded-xl bg-[#FFDEA1]/60 flex items-center justify-center text-[#261900]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight" x-text="lowStockMenus.length + ' Menu'"></span>
            <button type="button" @click="lihatKritis()" class="p-2 bg-[#FFDEA1]/20 rounded-lg flex items-center justify-between gap-2 mt-3 text-[10px] font-bold text-[#1C1C18] hover:bg-[#FFDEA1]/35 transition-colors cursor-pointer text-left">
                <span class="truncate" x-text="lowStockNames"></span>
                <svg class="w-3.5 h-3.5 text-[#7A5900] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">MENU NONAKTIF / HABIS</span>
                <div class="w-10 h-10 rounded-xl bg-[#FFDAD6] flex items-center justify-center text-[#93000A]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight" x-text="inactiveMenus.length + ' Menu'"></span>
                <button type="button" @click="lihatKritis()" class="p-2 bg-[#FFDAD6]/40 rounded-lg flex items-center justify-between gap-2 mt-3 text-[10px] font-bold text-[#93000A] hover:bg-[#FFDAD6]/65 transition-colors cursor-pointer text-left">
                    <span class="truncate" x-text="inactiveNames"></span>
                    <span class="underline flex-shrink-0">Kelola</span>
                </button>
            </div>
        </div>

        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px] relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">RATA-RATA HARGA<br>MENU</span>
                <div class="w-10 h-10 rounded-xl bg-[#CBEF89]/60 flex items-center justify-center text-[#213200]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>

            <div class="flex flex-col mt-2">
                <span class="font-display font-bold text-2xl sm:text-[26px] text-[#00341A] tracking-tight leading-tight" x-text="formatRupiah(averagePrice)"></span>
                <div class="p-2 bg-[#CBEF89]/20 rounded-lg flex items-center justify-between gap-2 mt-3 text-[10px] font-bold text-[#213200]">
                    <span class="truncate" x-text="priceRangeText"></span>
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
                    placeholder="Cari nama menu, SKU, kategori, atau deskripsi..."
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
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="categoryFilter === 'all' ? 'bg-white/20 text-white' : 'bg-[#DCDAD3] text-[#404941]'" x-text="menus.length"></span>
            </button>
            <template x-for="kat in categories" :key="kat.id">
                <button
                    type="button"
                    @click="categoryFilter = kat.id"
                    :class="categoryFilter === kat.id ? 'bg-[#0B4D2B] text-white shadow-xs font-semibold' : 'bg-[#F0EEE7] text-[#404941] hover:text-black font-medium'"
                    class="px-4 py-1.5 rounded-full whitespace-nowrap flex items-center gap-1.5 transition-all cursor-pointer">
                    <span x-text="kat.name"></span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="categoryFilter === kat.id ? 'bg-white/20 text-white' : 'bg-[#DCDAD3] text-[#404941]'" x-text="countCategory(kat.id)"></span>
                </button>
            </template>
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
                    <span class="font-bold text-xs text-[#1C1C18]">Sinkronisasi Realtime Reverb Aktif</span>
                    <span class="w-2 h-2 rounded-full bg-[#0B4D2B] animate-pulse"></span>
                </div>
                <p class="text-xs text-[#404941] mt-0.5 leading-snug">
                    Stok dipotong otomatis setelah pembayaran berhasil. Perubahan data menu dan ketersediaan disiarkan ke e-menu, kasir, dan KDS.
                </p>
            </div>
        </div>

        <span class="text-[10px] font-semibold text-[#707971] text-right" x-text="'Pembaruan ' + updatedAt + ' WIB'"></span>
    </div>

    <div id="tabel-menu" class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">

        <div class="p-4 bg-[#F6F3EC] border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h3 class="font-bold text-sm text-[#1C1C18]">Daftar Menu & Stok Terkini</h3>
                <span class="px-2.5 py-0.5 rounded-full bg-[#B1F1C2]/60 text-[10px] font-bold text-[#00210E]" x-text="filteredMenus.length + ' Terpilih dari ' + menus.length"></span>
            </div>

            <div class="flex items-center gap-1 text-[10px] font-bold text-[#707971]">
                <svg class="w-3.5 h-3.5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                </svg>
                <span>Diurutkan: Nama Menu (A-Z)</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F0EEE7] border-b border-gray-200 text-[10px] font-bold text-[#404941] uppercase tracking-wider">
                        <th class="py-3 px-4 min-w-[240px]">FOTO & NAMA MENU / SKU</th>
                        <th class="py-3 px-4 min-w-[160px]">KATEGORI KDS</th>
                        <th class="py-3 px-4 min-w-[120px]">HARGA PORSI</th>
                        <th class="py-3 px-4 min-w-[130px]">STOK PORSI</th>
                        <th class="py-3 px-4 min-w-[140px]">STATUS E-MENU</th>
                        <th class="py-3 px-4 text-center min-w-[100px]">KETERSEDIAAN</th>
                        <th class="py-3 px-4 text-right min-w-[130px]">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    <template x-for="item in filteredMenus" :key="item.id">
                        <tr
                            class="transition-colors"
                            :class="!item.is_aktif ? 'bg-[#FFDAD6]/15' : (item.stock <= 0 ? 'bg-[#FFDEA1]/10' : 'hover:bg-[#FBF8F1]/60')">

                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#F0EEE7] flex items-center justify-center flex-shrink-0 shadow-2xs border border-gray-200/60 overflow-hidden">
                                        <img x-show="item.photo" :src="item.photo" :alt="item.name" class="w-full h-full object-cover">
                                        <svg x-show="!item.photo" class="w-6 h-6 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4.5"/><path stroke-linecap="round" d="M12 2v2M12 20v2M2 12h2M20 12h2"/></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-[#1C1C18] leading-tight" :class="!item.is_aktif ? 'line-through text-gray-500' : ''" x-text="item.name"></span>
                                        <div class="flex items-center gap-1.5 mt-1 text-[10px]">
                                            <span class="font-mono font-bold text-[#707971]" x-text="item.sku"></span>
                                            <span class="w-1 h-1 rounded-full bg-[#C0C9BF]"></span>
                                            <span class="font-medium" :class="!item.is_aktif ? 'text-[#BA1A1A] font-bold' : 'text-[#00341A]'" x-text="item.badge"></span>
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
                                    <span class="font-bold text-sm text-[#00341A]" x-text="formatRupiah(item.price)"></span>
                                    <span class="text-[10px] font-semibold text-[#707971] mt-0.5">per porsi</span>
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
                                    <span class="text-[10px] font-semibold text-[#707971] mt-0.5" x-text="!item.is_aktif ? 'Menu dinonaktifkan' : (item.stock <= 0 ? 'Perlu isi ulang' : 'Porsi tersedia')"></span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide"
                                    :class="{
                                        'bg-[#B1F1C2]/60 text-[#00210E]': item.status === 'tersedia',
                                        'bg-[#FFDEA1] text-[#261900]': item.status === 'hampir_habis',
                                        'bg-[#FFDAD6] text-[#93000A]': item.status === 'habis',
                                        'bg-[#EBE8E1] text-[#707971]': item.status === 'nonaktif'
                                    }">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full"
                                        :class="{
                                            'bg-[#0B4D2B]': item.status === 'tersedia',
                                            'bg-[#7A5900]': item.status === 'hampir_habis',
                                            'bg-[#93000A]': item.status === 'habis',
                                            'bg-[#707971]': item.status === 'nonaktif'
                                        }">
                                    </span>
                                    <span x-text="item.statusText"></span>
                                </span>
                            </td>

                            <td class="py-3 px-4 text-center">
                                <button
                                    type="button"
                                    @click="toggleSwitch(item)"
                                    :disabled="item.updating"
                                    :aria-checked="item.is_aktif ? 'true' : 'false'"
                                    role="switch"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors cursor-pointer disabled:opacity-60 disabled:cursor-wait"
                                    :class="item.is_aktif ? 'bg-[#0B4D2B]' : 'bg-[#DCDAD3]'">
                                    <span
                                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform"
                                        :class="item.is_aktif ? 'translate-x-5' : 'translate-x-1'">
                                    </span>
                                </button>
                            </td>

                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        x-show="item.stock <= 0"
                                        @click="openEdit(item)"
                                        class="px-2.5 py-1 rounded-lg bg-[#FFDAD6] text-[#93000A] font-bold text-[10px] hover:bg-[#fcd0cb] cursor-pointer">
                                        Isi Stok
                                    </button>
                                    <button
                                        type="button"
                                        @click="openEdit(item)"
                                        class="p-1.5 rounded-lg text-[#404941] hover:bg-[#F0EEE7] cursor-pointer" title="Edit menu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click="askDelete(item)"
                                        class="p-1.5 rounded-lg text-[#BA1A1A] hover:bg-[#FFDAD6]/60 cursor-pointer" title="Hapus menu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredMenus.length === 0">
                        <td colspan="7" class="py-10 px-4 text-center text-xs text-[#707971]">
                            Tidak ada menu yang cocok dengan filter atau pencarian.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-[#F6F3EC] border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-[#707971]">
            <div class="flex items-center gap-1.5">
                <span>Menampilkan</span>
                <span class="text-[#1C1C18]" x-text="filteredMenus.length"></span>
                <span>dari</span>
                <span class="text-[#1C1C18]" x-text="menus.length"></span>
                <span>menu</span>
                <span>•</span>
                <span>Pembaruan terakhir:</span>
                <span class="text-[#0B4D2B]" x-text="updatedAt + ' WIB'"></span>
            </div>

            <div class="flex items-center gap-2 text-[10px]">
                <span class="w-2 h-2 rounded-full bg-[#0B4D2B]"></span>
                <span>Sumber data: tabel menu</span>
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
            class="w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5"
            @click.outside="addModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Tambah Menu Baru</h3>
                    <p class="text-xs text-[#707971] mt-0.5">Lengkapi data menu, stok porsi, dan kategori KDS.</p>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.menu.simpan') }}" enctype="multipart/form-data" class="flex flex-col gap-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kategori</label>
                        <select name="kategori_id" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            @foreach ($kategoriForm as $kat)
                            <option value="{{ $kat->id }}" @selected(old('kategori_id') == $kat->id)>{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Stok Awal (Porsi)</label>
                        <input type="number" name="stok_menu" value="{{ old('stok_menu') }}" placeholder="Contoh: 50" min="0" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#1C1C18]">Nama Menu</label>
                    <input type="text" name="nama_menu" value="{{ old('nama_menu') }}" placeholder="Contoh: Gurame Saus Padang Situ Awi" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#1C1C18]">Foto Menu</label>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 rounded-xl bg-[#F0EEE7] border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                            <img x-show="addPhotoPreview" :src="addPhotoPreview" alt="Pratinjau foto menu" class="w-full h-full object-cover">
                            <svg x-show="!addPhotoPreview" class="w-6 h-6 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="foto_menu" accept="image/jpeg,image/png,image/webp" @change="previewAddPhoto($event)" class="block w-full text-[11px] text-[#404941] file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-[#EBE8E1] file:text-[#1C1C18] file:font-semibold file:cursor-pointer hover:file:bg-[#ded9cf]">
                            <p class="text-[10px] text-[#707971] mt-1">JPG, PNG, atau WEBP. Maksimal 3 MB.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Harga Porsi (Rp)</label>
                        <input type="number" name="harga" value="{{ old('harga') }}" placeholder="85000" min="0" max="9999999999" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Deskripsi</label>
                        <input type="text" name="deskripsi" value="{{ old('deskripsi') }}" placeholder="Deskripsi singkat menu" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
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
        x-show="editModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div
            class="w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 sm:p-8 flex flex-col gap-5"
            @click.outside="editModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="font-display font-bold text-xl text-[#00341A]">Edit Menu</h3>
                    <p class="text-xs text-[#707971] mt-0.5">Perubahan stok dan ketersediaan langsung disiarkan ke e-menu.</p>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-black p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="simpanEdit()" class="flex flex-col gap-4 text-xs">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Kategori</label>
                        <select x-model="editForm.kategori_id" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                            @foreach ($kategoriForm as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Stok Porsi</label>
                        <input type="number" x-model.number="editForm.stok_menu" min="0" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#1C1C18]">Nama Menu</label>
                    <input type="text" x-model="editForm.nama_menu" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="font-bold text-[#1C1C18]">Foto Menu</label>
                    <div class="flex items-center gap-3">
                        <div class="w-16 h-16 rounded-xl bg-[#F0EEE7] border border-gray-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                            <img x-show="editPhotoPreview" :src="editPhotoPreview" alt="Pratinjau foto menu" class="w-full h-full object-cover">
                            <svg x-show="!editPhotoPreview" class="w-6 h-6 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <input type="file" x-ref="editPhotoInput" accept="image/jpeg,image/png,image/webp" @change="previewEditPhoto($event)" class="block w-full text-[11px] text-[#404941] file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-[#EBE8E1] file:text-[#1C1C18] file:font-semibold file:cursor-pointer hover:file:bg-[#ded9cf]">
                            <p class="text-[10px] text-[#707971] mt-1">Biarkan kosong jika tidak mengganti foto. Maksimal 3 MB.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Harga Porsi (Rp)</label>
                        <input type="number" x-model.number="editForm.harga" min="0" max="9999999999" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]" required>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="font-bold text-[#1C1C18]">Deskripsi</label>
                        <input type="text" x-model="editForm.deskripsi" placeholder="Deskripsi singkat menu" class="h-10 px-3 rounded-xl border border-gray-300 focus:outline-hidden focus:border-[#0B4D2B]">
                    </div>
                </div>

                <div x-show="editError" x-text="editError" class="p-3 rounded-xl bg-[#FFDAD6]/50 border border-[#BA1A1A]/15 text-[11px] font-semibold text-[#93000A]"></div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        @click="editModalOpen = false"
                        :disabled="editSaving"
                        class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 disabled:opacity-60 active:scale-95 transition-all cursor-pointer">
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="editSaving"
                        class="flex-1 py-3 rounded-xl bg-[#0B4D2B] text-white font-bold hover:bg-[#08381F] disabled:opacity-60 disabled:cursor-wait active:scale-95 transition-all shadow-xs cursor-pointer"
                        x-text="editSaving ? 'Menyimpan...' : 'Simpan Perubahan'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        x-cloak
        x-show="deleteModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-full max-w-md bg-white rounded-3xl border border-gray-200 shadow-2xl p-6 flex flex-col gap-5" @click.outside="deleteModalOpen = false">
            <div class="flex items-start gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#FFDAD6] text-[#93000A] flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-display font-bold text-lg text-[#00341A]">Hapus Menu</h3>
                    <p class="text-xs text-[#404941] mt-1 leading-relaxed">
                        Menu <span class="font-bold text-[#1C1C18]" x-text="deleteItem ? deleteItem.name : ''"></span> akan dihapus permanen dari katalog. Menu yang sudah dipakai transaksi tidak dapat dihapus.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="button"
                    @click="deleteModalOpen = false"
                    :disabled="deleteSaving"
                    class="flex-1 py-3 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-50 disabled:opacity-60 transition-all cursor-pointer">
                    Batal
                </button>
                <button
                    type="button"
                    @click="hapusItem()"
                    :disabled="deleteSaving"
                    class="flex-1 py-3 rounded-xl bg-[#BA1A1A] text-white font-bold text-xs hover:bg-[#93000A] disabled:opacity-60 disabled:cursor-wait transition-all cursor-pointer"
                    x-text="deleteSaving ? 'Menghapus...' : 'Hapus Menu'">
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
        <svg class="w-4 h-4 text-[#B1F1C2] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="toast.message"></span>
    </div>

</div>

<script>
    function masterMenuApp() {
        const dataUrl = @json(route('admin.menu.data'));
        const menuBaseUrl = @json(url('/admin/menu'));
        const csrfToken = @json(csrf_token());

        return {
            statusFilter: 'semua',
            categoryFilter: 'all',
            searchQuery: '',
            addModalOpen: @json(old('nama_menu') !== null),
            editModalOpen: false,
            deleteModalOpen: false,
            editSaving: false,
            deleteSaving: false,
            refreshing: false,
            editError: '',
            addPhotoPreview: '',
            editPhoto: null,
            editPhotoPreview: '',
            editForm: {
                id: null,
                nama_menu: '',
                kategori_id: '',
                harga: 0,
                stok_menu: 0,
                deskripsi: ''
            },
            deleteItem: null,
            updatedAt: @json(now('Asia/Jakarta')->format('H:i:s')),
            menus: @json($menuJson),
            categories: @json($kategoriJson),
            toast: { show: false, message: '' },

            init() {
                this.menus = this.menus.map((item) => this.normalisasiMenu(item));
                this._refreshTimer = null;
                this._refreshQueued = false;
                this._toastTimer = null;

                window.addEventListener('realtime:menu.diperbarui', () => {
                    clearTimeout(this._refreshTimer);
                    this._refreshTimer = setTimeout(() => this.refreshData(), 180);
                });
            },

            normalisasiMenu(item) {
                return {
                    ...item,
                    price: Number(item.price) || 0,
                    stock: Number(item.stock) || 0,
                    updating: false
                };
            },

            headers() {
                return {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                };
            },

            uploadHeaders() {
                return {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                };
            },

            previewAddPhoto(event) {
                const file = event.target.files && event.target.files[0];
                this.addPhotoPreview = '';
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (loadEvent) => this.addPhotoPreview = loadEvent.target.result;
                reader.readAsDataURL(file);
            },

            previewEditPhoto(event) {
                const file = event.target.files && event.target.files[0];
                this.editPhoto = file || null;
                this.editPhotoPreview = '';
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (loadEvent) => this.editPhotoPreview = loadEvent.target.result;
                reader.readAsDataURL(file);
            },

            async refreshData() {
                if (this.refreshing) {
                    this._refreshQueued = true;
                    return;
                }

                this.refreshing = true;

                try {
                    const response = await fetch(dataUrl, {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    });

                    if (!response.ok) {
                        throw new Error('Data menu gagal dimuat.');
                    }

                    const data = await response.json();
                    this.menus = (data.menus || []).map((item) => this.normalisasiMenu(item));
                    this.categories = data.categories || [];
                    this.updatedAt = data.updated_at || this.updatedAt;
                } catch (error) {
                    this.showToast(error.message || 'Data menu gagal dimuat.');
                } finally {
                    this.refreshing = false;

                    if (this._refreshQueued) {
                        this._refreshQueued = false;
                        this.refreshData();
                    }
                }
            },

            async toggleSwitch(item) {
                const sebelumnya = item.is_aktif;
                item.is_aktif = !sebelumnya;
                item.updating = true;
                this.perbaruiStatusItem(item);

                try {
                    const response = await fetch(menuBaseUrl + '/' + item.id + '/ketersediaan', {
                        method: 'PATCH',
                        headers: this.headers(),
                        body: JSON.stringify({ is_aktif: item.is_aktif })
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'Ketersediaan menu gagal diperbarui.');
                    }

                    this.showToast(payload.message || 'Ketersediaan menu diperbarui.');
                    await this.refreshData();
                } catch (error) {
                    item.is_aktif = sebelumnya;
                    this.perbaruiStatusItem(item);
                    this.showToast(error.message || 'Ketersediaan menu gagal diperbarui.');
                } finally {
                    item.updating = false;
                }
            },

            perbaruiStatusItem(item) {
                if (!item.is_aktif) {
                    item.status = 'nonaktif';
                    item.statusText = 'Nonaktif';
                } else if (item.stock <= 0) {
                    item.status = 'habis';
                    item.statusText = 'Habis';
                    item.stockStatus = 'habis';
                } else if (item.stock <= 5) {
                    item.status = 'hampir_habis';
                    item.statusText = 'Stok Menipis';
                    item.stockStatus = 'kritis';
                } else {
                    item.status = 'tersedia';
                    item.statusText = 'Tersedia Live';
                    item.stockStatus = 'normal';
                }

                item.available = item.is_aktif && item.stock > 0;
                item.enabled = item.available;
                item.stockQty = item.stock + ' Porsi';
            },

            openEdit(item) {
                this.editForm = {
                    id: item.id,
                    nama_menu: item.name,
                    kategori_id: item.kategoriId,
                    harga: item.price,
                    stok_menu: item.stock,
                    deskripsi: item.description || ''
                };
                this.editError = '';
                this.editPhoto = null;
                this.editPhotoPreview = item.photo || '';
                this.editModalOpen = true;
                this.$nextTick(() => {
                    if (this.$refs.editPhotoInput) this.$refs.editPhotoInput.value = '';
                });
            },

            async simpanEdit() {
                this.editSaving = true;
                this.editError = '';

                try {
                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('nama_menu', this.editForm.nama_menu);
                    formData.append('kategori_id', this.editForm.kategori_id);
                    formData.append('harga', this.editForm.harga);
                    formData.append('stok_menu', this.editForm.stok_menu);
                    formData.append('deskripsi', this.editForm.deskripsi || '');
                    if (this.editPhoto) formData.append('foto_menu', this.editPhoto);

                    const response = await fetch(menuBaseUrl + '/' + this.editForm.id, {
                        method: 'POST',
                        headers: this.uploadHeaders(),
                        body: formData
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        this.editError = payload.errors
                            ? Object.values(payload.errors).flat().join(' ')
                            : (payload.message || 'Menu gagal diperbarui.');
                        return;
                    }

                    this.editModalOpen = false;
                    this.showToast(payload.message || 'Menu berhasil diperbarui.');
                    await this.refreshData();
                } catch (error) {
                    this.editError = error.message || 'Menu gagal diperbarui.';
                } finally {
                    this.editSaving = false;
                }
            },

            askDelete(item) {
                this.deleteItem = item;
                this.deleteModalOpen = true;
            },

            async hapusItem() {
                if (!this.deleteItem) return;

                this.deleteSaving = true;
                const target = this.deleteItem;

                try {
                    const response = await fetch(menuBaseUrl + '/' + target.id, {
                        method: 'DELETE',
                        headers: this.headers()
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'Menu gagal dihapus.');
                    }

                    this.deleteModalOpen = false;
                    this.deleteItem = null;
                    this.showToast(payload.message || 'Menu berhasil dihapus.');
                    await this.refreshData();
                } catch (error) {
                    this.showToast(error.message || 'Menu gagal dihapus.');
                } finally {
                    this.deleteSaving = false;
                }
            },

            showToast(message) {
                clearTimeout(this._toastTimer);
                this.toast.message = message;
                this.toast.show = true;
                this._toastTimer = setTimeout(() => this.toast.show = false, 3200);
            },

            formatRupiah(value) {
                return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
            },

            ringkasNama(list, kosong) {
                if (!list.length) return kosong;

                const nama = list.slice(0, 3).map((item) => item.name).join(', ');

                return nama + (list.length > 3 ? ' +' + (list.length - 3) + ' lainnya' : '');
            },

            countCategory(id) {
                return this.menus.filter((item) => item.category === id).length;
            },

            lihatKritis() {
                this.statusFilter = 'kritis_habis';
                this.$nextTick(() => {
                    document.getElementById('tabel-menu')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },

            get activeMenus() {
                return this.menus.filter((item) => item.is_aktif);
            },

            get dapurCount() {
                return this.activeMenus.filter((item) => item.kdsType === 'dapur').length;
            },

            get barCount() {
                return this.activeMenus.filter((item) => item.kdsType === 'bar').length;
            },

            get lowStockMenus() {
                return this.menus.filter((item) => item.is_aktif && item.stock > 0 && item.stock <= 5);
            },

            get inactiveMenus() {
                return this.menus.filter((item) => !item.is_aktif || item.stock <= 0);
            },

            get lowStockNames() {
                return this.ringkasNama(this.lowStockMenus, 'Semua stok porsi aman');
            },

            get inactiveNames() {
                return this.ringkasNama(this.inactiveMenus, 'Tidak ada menu');
            },

            get averagePrice() {
                if (!this.activeMenus.length) return 0;

                return Math.round(this.activeMenus.reduce((total, item) => total + item.price, 0) / this.activeMenus.length);
            },

            get minPrice() {
                if (!this.activeMenus.length) return 0;

                return Math.min(...this.activeMenus.map((item) => item.price));
            },

            get maxPrice() {
                if (!this.activeMenus.length) return 0;

                return Math.max(...this.activeMenus.map((item) => item.price));
            },

            get priceRangeText() {
                if (!this.activeMenus.length) return 'Belum ada menu aktif';

                return 'Termurah ' + this.formatRupiah(this.minPrice) + ' • Termahal ' + this.formatRupiah(this.maxPrice);
            },

            get filteredMenus() {
                const keyword = this.searchQuery.trim().toLowerCase();

                return this.menus.filter((item) => {
                    if (this.statusFilter === 'tersedia' && !item.available) return false;
                    if (this.statusFilter === 'kritis_habis' && item.status === 'tersedia') return false;
                    if (this.categoryFilter !== 'all' && item.category !== this.categoryFilter) return false;
                    if (!keyword) return true;

                    return [item.name, item.sku, item.badge, item.description]
                        .some((value) => String(value || '').toLowerCase().includes(keyword));
                });
            }
        };
    }
</script>
@endsection
