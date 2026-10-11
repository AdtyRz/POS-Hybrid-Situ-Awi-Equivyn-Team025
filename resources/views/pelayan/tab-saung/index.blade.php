@extends('layouts.pelayan')

@section('title', 'Mobile POS Pelayan (Tab Saung) • Situ Awi Saung Lesehan')

@section('content')
<div x-data="pelayanTabSaung()" class="relative flex flex-col min-h-screen" x-init="init()">

    <header class="sticky top-0 z-40 bg-[#FCF9F2]/90 backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] border-b border-[#E5E2DB]/60 px-4 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-widest uppercase leading-none">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-tight leading-tight mt-0.5">Saung</h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                <span>LIVE</span>
            </div>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-xs flex items-center justify-center shadow-xs">
                {{ strtoupper(substr(auth()->user()->nama ?? 'PL', 0, 2)) }}
            </div>
        </div>
    </header>

    <div class="p-4 space-y-4 flex-1">

        <div class="bg-[#F6F3EC] rounded-2xl p-4 shadow-xs border border-[#E5E2DB]/80 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-11 h-11 rounded-full bg-[#0B4D2B] text-white font-bold text-base flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(auth()->user()->nama ?? 'PL', 0, 2)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-[#B1F1C2] ring-2 ring-[#F6F3EC]"></span>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 leading-tight">
                            <span class="font-bold text-base text-[#1C1C18]" x-text="runnerName">{{ auth()->user()->nama }}</span>
                            <span class="px-1.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold" x-text="staffId">STF-006</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-[#404941] mt-0.5">
                            <svg class="w-3 h-3 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="shiftInfo">Shift Siang (12:00 – 18:00 WIB)</span>
                        </div>
                    </div>
                </div>

                <button 
                    @click="showQrModal = true"
                    title="Pindai QR Meja"
                    class="w-10 h-10 rounded-xl bg-white text-[#0B4D2B] shadow-xs border border-[#E5E2DB]/60 flex items-center justify-center active:scale-95 transition-transform cursor-pointer">
                    <svg class="w-5 h-5 text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </button>
            </div>

            <div class="bg-[#F0EEE7] rounded-xl px-3 py-2 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-[#1C1C18] font-semibold">
                    <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="currentArea">Tepi Danau (LB-01 – LB-05)</span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white text-[#404941] text-[10px] font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                    <span x-text="meshStatus">IoT Mesh 11/11 (-52dBm)</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2">
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-center shadow-xs border border-[#E5E2DB]/60 flex flex-col items-center justify-center">
                <span class="font-bold text-xl text-[#1C1C18] leading-tight" x-text="totalMeja">11</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1" x-text="totalPercentage + '%'">Total</span>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-center shadow-xs border border-[#E5E2DB]/60 flex flex-col items-center justify-center">
                <span class="font-bold text-xl text-[#0B4D2B] leading-tight" x-text="terisiCount">7</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1">Terisi</span>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-2.5 text-center shadow-xs border border-[#E5E2DB]/60 flex flex-col items-center justify-center">
                <span class="font-bold text-xl text-[#7A5900] leading-tight" x-text="tersediaCount">3</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-tight mt-1">Tersedia</span>
            </div>

            <div class="bg-[#FFDAD6] rounded-xl p-2.5 text-center shadow-xs border border-[#BA1A1A]/30 flex flex-col items-center justify-center relative">
                <span class="font-bold text-xl text-[#93000A] leading-tight" x-text="panggilanCount">1</span>
                <span class="text-[10px] font-bold text-[#93000A] uppercase tracking-tight mt-1">Panggilan</span>
                <template x-if="panggilanCount > 0">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#BA1A1A] animate-ping absolute -top-1 -right-1"></span>
                </template>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none -mx-4 px-4">
            <button 
                @click="selectedCluster = 'all'"
                :class="selectedCluster === 'all' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span>Semua Saung (11)</span>
            </button>

            <button 
                @click="selectedCluster = 'danau'"
                :class="selectedCluster === 'danau' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>
                <span>Cluster Danau (5)</span>
            </button>

            <button 
                @click="selectedCluster = 'balong'"
                :class="selectedCluster === 'balong' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <span>Cluster Balong (2)</span>
            </button>

            <button 
                @click="selectedCluster = 'kayu'"
                :class="selectedCluster === 'kayu' ? 'bg-[#0B4D2B] text-white shadow-xs' : 'bg-[#EBE8E1] text-[#1C1C18] hover:bg-[#E5E2DB]'"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5 text-[#707971]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0v-4m14 4v-4M4 6h16a1 1 0 011 1v1a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"/></svg>
                <span>Cluster Meja (4)</span>
            </button>
        </div>

        <template x-for="cluster in clusters" :key="cluster.key">
            <div x-show="selectedCluster === 'all' || selectedCluster === cluster.key" class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" :class="cluster.color"></span>
                        <h2 class="font-bold text-base text-[#1C1C18]" x-text="cluster.name"></h2>
                        <template x-if="cluster.key === 'danau'">
                            <span class="px-2 py-0.5 rounded-md bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">Area Kamu</span>
                        </template>
                    </div>
                    <span class="text-xs text-[#404941]" x-text="cluster.count + ' Saung'"></span>
                </div>

                <template x-for="meja in cluster.meja" :key="meja.id">
                    <div class="bg-white rounded-2xl shadow-sm border" :class="meja.isCalling ? 'border-[#BA1A1A]/30 ring-1 ring-[#BA1A1A]' : 'border-[#F0EEE7]'">
                        <template x-if="meja.isCalling">
                            <div class="h-1.5 bg-[#BA1A1A] w-full"></div>
                        </template>

                        <div class="p-3.5 space-y-3">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl" :class="meja.statusColor" x-text="meja.number"></div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-bold text-base text-[#1C1C18]" x-text="meja.name"></h3>
                                            <template x-if="meja.isCalling">
                                                <span class="px-2 py-0.5 rounded-full bg-[#BA1A1A] text-white text-[10px] font-bold animate-pulse">BELL ON</span>
                                            </template>
                                            <template x-if="meja.status === 'siap_antar'">
                                                <span class="px-2 py-0.5 rounded-full bg-[#FFDEA1] text-[#5C4300] text-[10px] font-bold">Siap Antar</span>
                                            </template>
                                            <template x-if="meja.status === 'makan'">
                                                <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-semibold">Santap Makan</span>
                                            </template>
                                            <template x-if="meja.status === 'masak'">
                                                <span class="px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-semibold">Masak Dapur</span>
                                            </template>
                                            <template x-if="meja.status === 'tersedia'">
                                                <span class="px-2 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-[10px] font-bold">Tersedia</span>
                                            </template>
                                        </div>
                                        <span class="text-xs text-[#404941] block mt-0.5" x-text="meja.billInfo"></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 text-[10px] font-bold" :class="meja.isCalling ? 'text-[#BA1A1A]' : 'text-[#404941]'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="meja.timeAgo"></span>
                                </div>
                            </div>

                            <template x-if="meja.requestNote">
                                <div class="bg-[#FFDAD6]/60 rounded-xl p-2.5 flex items-start gap-2 border border-[#BA1A1A]/20">
                                    <svg class="w-4 h-4 text-[#BA1A1A] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    <div>
                                        <span class="text-[10px] font-bold text-[#93000A] uppercase tracking-wider block">PERMINTAAN TAMU</span>
                                        <p class="text-sm font-semibold text-[#93000A] mt-0.5" x-text="meja.requestNote"></p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="meja.isCalling">
                                <div class="flex items-center gap-2 pt-1">
                                    <button 
                                        @click="respondCall(meja.id)"
                                        class="flex-1 h-12 rounded-xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm shadow-xs flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer">
                                        <svg class="w-4 h-4 text-[#765600]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Respon & Temui</span>
                                    </button>

                                    <button 
                                        @click="muteBuzzer(meja.id)"
                                        :disabled="!buzzerActive"
                                        class="flex-1 h-12 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] font-semibold text-sm flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer disabled:opacity-50">
                                        <svg class="w-4 h-4 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                                        <span x-text="buzzerActive ? 'Matikan Buzzer' : 'Buzzer Mati'">Matikan Buzzer</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="meja.status === 'siap_antar'">
                                <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-sm text-[#1C1C18] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253"/></svg>
                                        <span x-text="meja.orderSummary"></span>
                                    </span>
                                    <span class="font-bold text-[10px] text-[#404941]" x-text="meja.itemCount + ' Item'"></span>
                                </div>

                                <button 
                                    @click="triggerToast('Pesanan minuman Saung ' + meja.number + ' diambil dari Bar Counter.')"
                                    class="w-full h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xs transition-all active:scale-98 cursor-pointer">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253"/></svg>
                                    <span x-text="'Ambil di Bar & Antar ke ' + meja.name"></span>
                                </button>
                            </template>

                            <template x-if="meja.status === 'makan'">
                                <div class="flex items-center justify-between text-xs pt-2 border-t" :class="meja.status === 'makan' ? 'border-[#F0EEE7]' : ''">
                                    <span class="text-[#404941] flex items-center gap-1 text-xs">
                                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span x-text="meja.puckInfo"></span>
                                    </span>
                                    <button @click="triggerToast('Menampilkan rincian pesanan Saung ' + meja.number + '...')" class="font-semibold text-xs text-[#00341A] hover:underline cursor-pointer">Lihat Rincian Pesanan</button>
                                </div>
                            </template>

                            <template x-if="meja.status === 'masak'">
                                <div class="space-y-1.5 pt-1">
                                    <div class="flex items-center justify-between text-[10px] font-bold text-[#404941]">
                                        <span x-text="meja.cookingItem"></span>
                                        <span x-text="meja.cookingProgress + '% Selesai'"></span>
                                    </div>
                                    <div class="w-full bg-[#F0EEE7] h-2 rounded-full overflow-hidden">
                                        <div class="bg-[#0B4D2B] h-full rounded-full" :style="'width: ' + meja.cookingProgress + '%'"></div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="meja.status === 'tersedia'">
                                <div class="flex items-center gap-2 pt-1">
                                    <button 
                                        @click="triggerToast('Token QR Meja ' + meja.number + ' siap discan oleh tamu baru.')"
                                        class="flex-1 h-11 rounded-xl bg-white text-[#1C1C18] font-semibold text-xs shadow-xs border border-[#E5E2DB] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        <span>QR Meja Aktif</span>
                                    </button>

                                    <button 
                                        @click="openTable(meja.code)"
                                        class="flex-1 h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs shadow-xs flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                        <span>Buka Meja Tamu</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <div class="pt-4">
            <button 
                @click="showNewOrderModal = true"
                class="w-full h-14 rounded-2xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm shadow-lg flex items-center justify-center gap-2.5 transition-all active:scale-98 cursor-pointer">
                <span class="w-7 h-7 rounded-full bg-[#765600] text-[#FECE66] flex items-center justify-center font-bold text-lg">+</span>
                <span>Input Pesanan Walk-in / Tambahan</span>
            </button>
        </div>

    </div>

    <div x-show="showQrModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showQrModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span><h3 class="font-bold text-base text-[#1C1C18]">Pindai Dudukan QR Saung</h3></div>
                <button @click="showQrModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button>
            </div>
            <div class="bg-[#1C1C18] rounded-xl h-48 flex flex-col items-center justify-center relative overflow-hidden border-2 border-dashed border-[#B1F1C2]">
                <div class="w-28 h-28 border-2 border-[#FECE66] rounded-xl relative flex items-center justify-center">
                    <span class="w-full h-0.5 bg-[#FECE66] absolute animate-pulse"></span>
                    <span class="text-3xl text-white">📷</span>
                </div>
                <span class="text-xs text-white/80 mt-3 font-mono">Arahkan ke QR Saung...</span>
            </div>
            <button @click="showQrModal = false; openTable('LB-03')" class="w-full py-2.5 bg-[#00341A] hover:bg-[#0B4D2B] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">Simulasi Scan Saung LB-03</button>
        </div>
    </div>

    <div x-show="showNewOrderModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showNewOrderModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span><h3 class="font-bold text-base text-[#1C1C18]">Input Pesanan Tambahan</h3></div><button @click="showNewOrderModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold">&times;</button></div>
            <div class="space-y-3 text-xs">
                <div><label class="font-bold text-[#1C1C18] block mb-1">Pilih Saung / Meja:</label><select class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl p-2.5 font-semibold text-[#1C1C18]"><option>LB-01 Teratai Air (4 Tamu)</option><option>LB-02 Teratai 2 (4 Tamu)</option><option>LB-03 Bambu Hitam (Tersedia)</option><option>LB-04 Gandasoli (7 Tamu)</option><option>LB-05 Dermaga Ujung</option></select></div>
                <div><label class="font-bold text-[#1C1C18] block mb-1">Menu Tambahan Cepat:</label><div class="space-y-1.5"><label class="flex items-center justify-between p-2 rounded-lg bg-[#F0EEE7]"><span>+ Nasi Liwet Kastrol Mini</span><span class="font-bold text-[#00341A]">Rp 28.000</span></label><label class="flex items-center justify-between p-2 rounded-lg bg-[#F0EEE7]"><span>+ Es Teh Manis Ciwidey</span><span class="font-bold text-[#00341A]">Rp 8.000</span></label><label class="flex items-center justify-between p-2 rounded-lg bg-[#F0EEE7]"><span>+ Sambal Cobek Dadak</span><span class="font-bold text-[#00341A]">Rp 6.000</span></label></div></div>
            </div>
            <button @click="showNewOrderModal = false; triggerToast('Pesanan tambahan terkirim ke KDS Dapur & Bar.')" class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98">Kirim ke Dapur / Bar</button>
        </div>
    </div>

    <div x-show="showToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="fixed bottom-20 left-4 right-4 z-50 max-w-[390px] mx-auto">
        <div class="bg-[#31312C] text-[#F3F0EA] rounded-xl px-4 py-3 flex items-center gap-3 shadow-2xl border border-white/10">
            <svg class="w-5 h-5 text-[#FFDEA1] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-xs font-medium leading-snug flex-1" x-text="toastMessage">Notifikasi berhasil diproses.</span>
        </div>
    </div>

</div>

<script>
    function pelayanTabSaung() {
        return {
            selectedCluster: 'all',
            toastMessage: '',
            showToast: false,
            saungCalling: [],
            buzzerActive: true,
            showQrModal: false,
            showNewOrderModal: false,
            clusters: [],
            runnerName: '',
            staffId: '',
            shiftInfo: '',
            currentArea: '',
            meshStatus: '',
            totalMeja: 0,
            terisiCount: 0,
            tersediaCount: 0,
            panggilanCount: 0,
            clusters: [],

            init() {
                this.runnerName = '{{ auth()->user()->nama }}';
                this.staffId = 'STF-{{ auth()->user()->id }}';
                this.shiftInfo = 'Shift Siang (12:00 – 18:00 WIB)';
                this.currentArea = '{{ $currentArea ?? "Tepi Danau (LB-01 – LB-05)" }}';
                this.meshStatus = 'IoT Mesh 11/11 (-52dBm)';
                this.loadData();
            },

            loadData() {
                this.clusters = @json($clusters);
                this.totalMeja = @json($totalMeja);
                this.terisiCount = @json($terisiCount);
                this.tersediaCount = @json($tersediaCount);
                this.panggilanCount = @json($panggilanCount);
            },

            respondCall(mejaId) {
                this.triggerToast('Anda merespons panggilan. Menuju lokasi saung...');
            },

            muteBuzzer(mejaId) {
                this.triggerToast('Sinyal MQTT terkirim: Buzzer dimatikan.');
            },

            openTable(saungId) {
                this.triggerToast('Sesi meja ' + saungId + ' berhasil dibuka. QR dinamis diaktifkan.');
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                playChime('success');
                setTimeout(() => this.showToast = false, 3500);
            }
        }
    }
</script>
@endsection