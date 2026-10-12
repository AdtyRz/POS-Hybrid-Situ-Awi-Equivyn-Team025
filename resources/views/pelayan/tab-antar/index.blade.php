@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Antar) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="pelayanTabAntar()" class="relative flex flex-col min-h-screen" x-init="init()">

    <header class="sticky top-0 z-40 bg-[#FCF9F2]/90 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-[#E5E2DB]/60 px-4 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-widest uppercase leading-none">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Antar</h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white font-bold text-xs flex items-center justify-center shadow-xs ring-2 ring-[#0B4D2B]/20">
                {{ auth()->user()->nama ? strtoupper(substr(auth()->user()->nama, 0, 2)) : 'PL' }}
            </div>
        </div>
    </header>

    <div class="p-4 space-y-4 flex-1">

        <div class="bg-[#F0EEE7] rounded-xl p-2.5 flex items-center justify-between shadow-xs border border-[#E5E2DB]/80">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 leading-tight">
                        <span class="font-bold text-base text-[#1C1C18]" x-text="runnerName">{{ auth()->user()->nama }}</span>
                        <span class="text-[10px] font-medium text-[#404941]">(Area {{ $runnerArea }})</span>
                    </div>
                    <span class="text-xs text-[#404941] block leading-tight mt-0.5" x-text="shiftInfo">Shift Siang (12:00–18:00)</span>
                </div>
            </div>

            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-bold">
                <svg class="w-3 h-3 text-[#213200]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                </svg>
                <span x-text="signalStrength">-52 dBm</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="flex-1 px-3 py-2 bg-white rounded-xl border border-[#E5E2DB] text-sm font-medium text-[#404941] placeholder-[#908E8A] focus:outline-none focus:border-[#0B4D2B] focus:ring-2 focus:ring-[#0B4D2B]/20" placeholder="Cari kode pesanan / meja...">
                <input type="text" x-model="searchQuery" @input="filterData()" class="w-full bg-transparent outline-none" placeholder="Cari kode pesanan / meja...">
            </div>
            <div class="flex items-center gap-1.5">
                <template x-for="cat in categories" :key="cat.value">
                    <button @click="filterCategory = cat.value" :class="filterCategory === cat.value ? 'bg-[#0B4D2B] text-white' : 'bg-[#F0EEE7] text-[#404941]'" class="px-3 py-1.5 rounded-full text-[10px] font-bold transition-all active:scale-95" x-text="cat.label"></button>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white rounded-xl p-3 shadow-xs border border-[#E5E2DB] text-center">
                <span class="font-bold text-2xl text-[#1C1C18]" x-text="readyCount">0</span>
                <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Siap Antar</span>
            </div>
            <div class="bg-white rounded-xl p-3 shadow-xs border border-[#E5E2DB] text-center">
                <span class="font-bold text-2xl text-[#7A5900]" x-text="deliveringCount">0</span>
                <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Diantar</span>
            </div>
            <div class="bg-white rounded-xl p-3 shadow-xs border border-[#E5E2DB] text-center">
                <span class="font-bold text-2xl text-[#00341A]" x-text="finishedCount">0</span>
                <span class="text-[9px] font-bold text-[#404941] uppercase tracking-wider block mt-1">Selesai Hari Ini</span>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-lg text-[#1C1C18]">Siap Diantarkan (Dapur)</h2>
                <span class="px-2 py-0.5 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold" x-text="kitchenCount + ' Pesanan'">0 Pesanan</span>
            </div>
            <template x-for="pesanan in filteredKitchen" :key="pesanan.id">
                <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#E5E2DB] space-y-3" x-show="!delivered[pesanan.id]">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#FECE66] text-[#765600] text-[10px] font-bold">Pass Dapur</span>
                                <span class="font-bold text-sm text-[#1C1C18]" x-text="pesanan.kode_pesanan"></span>
                                <span class="px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-medium" x-text="pesanan.meja.kode_meja"></span>
                            </div>
                            <p class="text-xs text-[#404941] mt-1" x-text="pesanan.timeAgo"></p>
                        </div>
                        <button @click="markDelivered(pesanan.id, pesanan.meja.kode_meja)" class="px-4 py-2 bg-[#0B4D2B] hover:bg-[#052E1B] text-white text-xs font-bold rounded-xl transition-all active:scale-95 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Selesai Antar</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#F0EEE7]">
                        <template x-for="item in pesanan.detailPesanan" :key="item.id">
                            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-left">
                                <p class="font-bold text-xs text-[#1C1C18]" x-text="item.menu.nama_menu"></p>
                                <p class="text-[10px] text-[#404941]" x-text="item.jumlah + 'x'"></p>
                                <template x-if="item.catatan">
                                    <p class="text-[9px] text-[#7A5900] italic mt-0.5" x-text="item.catatan"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            @if ($kitchenCount > 0)
            <div class="flex items-center justify-between mt-4">
                <h2 class="font-bold text-lg text-[#1C1C18]">Siap Diantarkan (Bar)</h2>
                <span class="px-2 py-0.5 rounded-full bg-[#CBEF89] text-[#364E00] text-[10px] font-bold" x-text="barCount + ' Pesanan'">0 Pesanan</span>
            </div>
            <template x-for="pesanan in filteredBar" :key="pesanan.id">
                <div class="bg-white rounded-2xl p-4 shadow-xs border border-[#E5E2DB] space-y-3" x-show="!delivered[pesanan.id]">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-[#CBEF89] text-[#364E00] text-[10px] font-bold">Bar Counter</span>
                                <span class="font-bold text-sm text-[#1C1C18]" x-text="pesanan.kode_pesanan"></span>
                                <span class="px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-medium" x-text="pesanan.meja.kode_meja"></span>
                            </div>
                            <p class="text-xs text-[#404941] mt-1" x-text="pesanan.timeAgo"></p>
                        </div>
                        <button @click="markDelivered(pesanan.id, pesanan.meja.kode_meja)" class="px-4 py-2 bg-[#0B4D2B] hover:bg-[#052E1B] text-white text-xs font-bold rounded-xl transition-all active:scale-95 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Selesai Antar</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#F0EEE7]">
                        <template x-for="item in pesanan.detailPesanan" :key="item.id">
                            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-left">
                                <p class="font-bold text-xs text-[#1C1C18]" x-text="item.menu.nama_menu"></p>
                                <p class="text-[10px] text-[#404941]" x-text="item.jumlah + 'x'"></p>
                                <template x-if="item.catatan">
                                    <p class="text-[9px] text-[#7A5900] italic mt-0.5" x-text="item.catatan"></p>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            @endif

            <template x-if="filteredSiapAntar.length === 0">
                <div class="bg-white rounded-2xl border border-dashed border-[#E5E2DB] p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-[#908E8A] mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253"/></svg>
                    <p class="font-bold text-[#404941]">Tidak ada pesanan siap antar</p>
                    <p class="text-xs text-[#908E8A] mt-1">Semua pesanan sudah diantar atau belum siap</p>
                </div>
            </template>
        </div>

        <div class="mt-6 p-4 bg-[#F6F3EC] rounded-2xl border border-[#E5E2DB] space-y-3">
            <h2 class="font-bold text-[#1C1C18] flex items-center gap-2">
                <svg class="w-5 h-5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a3 3 0 010 6h-1M4 8h14v6a4 4 0 01-4 4H8a4 4 0 01-4-4V8z"/></svg>
                Pesanan Sedang Diantar
            </h2>
            <template x-for="runner in deliveringRunners" :key="runner.id">
                <div class="bg-white rounded-xl p-3 shadow-xs border border-[#E5E2DB] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white flex items-center justify-center font-bold text-xs" x-text="runner.initials"></div>
                        <div>
                            <p class="font-bold text-sm text-[#1C1C18]" x-text="runner.name"></p>
                            <p class="text-xs text-[#404941]" x-text="runner.currentTask"></p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold" x-text="runner.elapsedTime"></span>
                </div>
            </template>
            <div x-show="deliveringRunners.length === 0" class="text-center text-xs text-[#908E8A] py-4">Tidak ada runner aktif</div>
        </div>
    </div>
</div>

<script>
function pelayanTabAntar() {
    return {
        searchQuery: '',
        filterCategory: 'all',
        categories: [
            { value: 'all', label: 'Semua' },
            { value: 'kitchen', label: 'Dapur' },
            { value: 'bar', label: 'Bar' }
        ],
        delivered: {},
        runnerName: '',
        runnerArea: '',
        shiftInfo: '',
        signalStrength: '',
        readyCount: 0,
        deliveringCount: 0,
        finishedCount: 0,
        kitchenCount: 0,
        barCount: 0,
        siapAntar: [],
        deliveringRunners: [],

        init() {
            this.loadData();
            this.runnerName = '{{ auth()->user()->nama }}';
            this.runnerArea = '{{ auth()->user()->area ?? "Danau" }}';
            this.shiftInfo = 'Shift Siang (12:00–18:00)';
            this.signalStrength = '-52 dBm';
            this.deliveringRunners = @json($deliveringRunners);
        },

        loadData() {
            this.readyCount = @json($readyCount);
            this.kitchenCount = @json($kitchenCount);
            this.barCount = @json($barCount);
            this.deliveringCount = @json($deliveringCount);
            this.finishedCount = @json($finishedCount);
            this.siapAntar = @json($siapAntarFormatted);
            this.updateCounts();
        },

        updateCounts() {
            this.readyCount = this.siapAntar.length;
            this.kitchenCount = this.siapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'dapur').length;
            this.barCount = this.siapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'bar').length;
            this.deliveringCount = @json($deliveringCount);
        },

        get filteredKitchen() {
            if (this.filterCategory === 'bar') return [];
            return this.filteredSiapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'dapur');
        },

        get filteredBar() {
            if (this.filterCategory === 'kitchen') return [];
            return this.filteredSiapAntar.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'bar');
        },

        get filteredSiapAntar() {
            let data = this.siapAntar;
            if (this.filterCategory === 'kitchen') {
                data = data.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'dapur');
            } else if (this.filterCategory === 'bar') {
                data = data.filter(p => p.detailPesanan[0]?.menu?.kategori?.target_kds === 'bar');
            }
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                data = data.filter(p => 
                    p.kode_pesanan.toLowerCase().includes(q) || 
                    p.meja.kode_meja.toLowerCase().includes(q)
                );
            }
            return data.filter(p => !this.delivered[p.id]);
        },

        markDelivered(id, saungName) {
            this.delivered[id] = true;
            this.readyCount = Math.max(0, this.readyCount - 1);
            this.finishedCount++;
            const category = this.siapAntar.find(p => p.id === id)?.detailPesanan[0]?.menu?.kategori?.target_kds ?? 'dapur';
            if (category === 'bar') this.barCount = Math.max(0, this.barCount - 1);
            else this.kitchenCount = Math.max(0, this.kitchenCount - 1);
        }
    }
}
</script>
@endsection
