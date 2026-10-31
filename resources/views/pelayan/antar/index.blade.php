@extends('layouts.pelayan')

@section('title', '07B - Mobile POS Pelayan (Tab Antar) • Situ Awi')

@section('content')
<div x-data="pelayanTabAntar()" class="relative flex flex-col min-h-screen bg-[#FCF9F2]" x-init="init()">

    <header class="sticky top-0 z-40 bg-[rgba(252,249,242,0.85)] backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-4 py-2.5 h-16 flex items-center justify-between border-b border-[#E5E2DB]/50">
        <div class="flex items-center gap-2">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 max-w-[45.25px] w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-[1px] uppercase leading-3">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-[-0.4px] leading-5">Antar</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F0EEE7] shadow-[0_1px_4px_rgba(43,42,38,0.04)]">
                <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[-0.25px]">LIVE</span>
            </div>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white font-bold text-xs flex items-center justify-center shadow-[0_0_0_2px_rgba(11,77,43,0.2),0_1px_4px_rgba(0,0,0,0.08)]">
                {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
            </div>
        </div>
    </header>

    <div class="px-4 py-3 space-y-4 flex-1">

        <section class="bg-[#F0EEE7] rounded-xl p-2 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-between h-14">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-8 h-8 rounded-full bg-[#0B4D2B] flex items-center justify-center flex-shrink-0">
                    <svg class="w-[9.75px] h-[16.12px] text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="flex flex-col min-w-0">
                    <div class="flex items-center gap-1.5 leading-6 min-w-0">
                        <span class="font-bold text-base text-[#1C1C18] truncate">{{ auth()->user()->nama ?? auth()->user()->name ?? 'Pelayan' }}</span>
                        <span class="text-[10px] font-medium text-[#404941] tracking-[0.4px] truncate">({{ $currentArea }})</span>
                    </div>
                    <span class="text-xs text-[#404941] font-normal leading-4">{{ $shift['label'] }}</span>
                </div>
            </div>

            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-[#E5E2DB] text-[#404941] text-[10px] font-bold tracking-[0.4px] flex-shrink-0">
                <svg class="w-[11.67px] h-[10.06px] text-[#213200]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1 8 8 0 0114 0 1 1 0 11-1.732 1A6 6 0 003.732 12 1 1 0 012 11zm3.5 3a1 1 0 011-1 4 4 0 017 0 1 1 0 11-1.732 1 2 2 0 00-3.536 0 1 1 0 01-1.732-1zM9 17a1 1 0 112 0 1 1 0 01-2 0z" />
                </svg>
                <span>{{ $device['label'] }}</span>
            </div>
        </section>

        <section class="grid grid-cols-3 gap-1">
            <div class="bg-[#0B4D2B] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[68px]">
                <span class="font-display font-bold text-[32px] text-[#B1F1C2] leading-8 tracking-[-0.32px]">{{ $readyCount }}</span>
                <span class="text-[10px] font-bold text-[#7FBD92] uppercase tracking-[-0.25px] leading-3 mt-1">HIDANGAN SIAP</span>
            </div>

            <div class="bg-[#FECE66] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[68px]">
                <span class="font-display font-bold text-[32px] text-[#261900] leading-8 tracking-[-0.32px]">{{ $deliveringCount }}</span>
                <span class="text-[10px] font-bold text-[#765600] uppercase tracking-[-0.25px] leading-3 mt-1">DIANTAR</span>
            </div>

            <div class="bg-[#EBE8E1] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[68px]">
                <span class="font-display font-bold text-[32px] text-[#1C1C18] leading-8 tracking-[-0.32px]">{{ $finishedCount }}</span>
                <span class="text-[10px] font-bold text-[#404941] uppercase tracking-[-0.25px] leading-3 mt-1">SELESAI</span>
            </div>
        </section>

        <section class="flex items-center gap-1.5 overflow-x-auto py-1 scrollbar-none">
            <button @click="activeFilter = 'all'" type="button"
                    :class="activeFilter === 'all' ? 'bg-[#0B4D2B] text-[#B1F1C2]' : 'bg-[#F0EEE7] text-[#1C1C18]'"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <span>Semua Siap</span>
                <span :class="activeFilter === 'all' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold tracking-[0.24px]">{{ $readyCount }}</span>
            </button>

            <button @click="activeFilter = 'kitchen'" type="button"
                    :class="activeFilter === 'kitchen' ? 'bg-[#0B4D2B] text-[#B1F1C2]' : 'bg-[#F0EEE7] text-[#1C1C18]'"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <span>Dapur Makanan</span>
                <span :class="activeFilter === 'kitchen' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold tracking-[0.24px]">{{ $kitchenCount }}</span>
            </button>

            <button @click="activeFilter = 'bar'" type="button"
                    :class="activeFilter === 'bar' ? 'bg-[#0B4D2B] text-[#B1F1C2]' : 'bg-[#F0EEE7] text-[#1C1C18]'"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <span>Bar Minuman</span>
                <span :class="activeFilter === 'bar' ? 'bg-[#7A5900] text-[#FCF9F2]' : 'bg-[#E5E2DB] text-[#404941]'"
                      class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold tracking-[0.24px]">{{ $barCount }}</span>
            </button>
        </section>

        <section class="space-y-2 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="w-[12.5px] h-[16.67px] text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                    </svg>
                    <h2 class="font-bold text-base text-[#1C1C18] leading-6">Siap di Pass Counter</h2>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[rgba(177,241,194,0.4)] text-[#00341A] text-[10px] font-bold tracking-[0.4px]">
                    Prioritas Cepat
                </span>
            </div>

            <div class="space-y-3 pt-1">
                @forelse ($siapAntarFormatted as $order)
                    <div x-show="(activeFilter === 'all' || (activeFilter === 'kitchen' && {{ $order['has_dapur'] ? 'true' : 'false' }}) || (activeFilter === 'bar' && {{ $order['has_bar'] ? 'true' : 'false' }})) && !orderState.delivered"
                         x-data="{ orderState: { delivered: false, busy: false } }"
                         class="bg-[#F6F3EC] rounded-xl p-4 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex flex-col gap-3">

                        <div class="flex items-start justify-between gap-2 border-b border-[#E5E2DB]/60 pb-2.5">
                            <div class="flex flex-col gap-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded {{ $order['sumber'] === 'bar' ? 'bg-[#CBEF89] text-[#131F00]' : 'bg-[#FFDEA1] text-[#261900]' }} text-[10px] font-bold tracking-[0.4px]">
                                        {{ $order['sumber_label'] }}
                                    </span>
                                    <div class="flex items-center gap-1 text-[#7A5900] text-[10px] font-semibold whitespace-nowrap">
                                        <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $order['time_ago'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#E5E2DB] text-[#00341A] text-xs font-bold tracking-[0.24px] flex-shrink-0">
                                {{ $order['item_count'] }} Item
                            </span>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <h3 class="font-bold text-[20px] text-[#1C1C18] leading-[25px]">{{ $order['meja']['kode_meja'] }}</h3>
                            <p class="text-xs text-[#404941] font-normal leading-4">{{ $order['meja']['area'] }} • {{ $order['customer'] }}</p>
                        </div>

                        <div class="bg-[#F0EEE7] rounded-lg p-2.5 flex flex-col gap-2">
                            @foreach ($order['items'] as $detail)
                                <div class="flex items-start gap-2">
                                    <span class="text-xs font-bold text-[#00341A] tracking-[0.24px] mt-0.5 flex-shrink-0">{{ $detail['jumlah'] }}x</span>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-[#1C1C18] leading-[19px]">{{ $detail['nama'] }}</span>
                                        @if (filled($detail['catatan']))
                                            <span class="text-xs italic text-[#404941] font-normal leading-4">{{ $detail['catatan'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-col gap-2 pt-1">
                            <button @click="deliverOrder('{{ $order['kode_pesanan'] }}', orderState)" :disabled="orderState.busy"
                                    type="button"
                                    class="w-full h-12 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-sm tracking-[0.14px] flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all cursor-pointer active:scale-98 disabled:opacity-60">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="orderState.busy ? 'Menyimpan...' : 'Ambil & Tandai Sedang Diantar'">Ambil & Tandai Sedang Diantar</span>
                            </button>

                            <button @click="openDenah('{{ $order['kode_pesanan'] }}')" type="button"
                                    class="w-full h-9 rounded-lg bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] font-semibold text-xs tracking-[0.24px] flex items-center justify-center gap-1.5 transition-all cursor-pointer active:scale-98">
                                <svg class="w-3 h-3 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" />
                                    <line x1="8" y1="2" x2="8" y2="18" />
                                    <line x1="16" y1="6" x2="16" y2="22" />
                                </svg>
                                <span>Lihat Denah {{ $order['meja']['kode_meja'] }}</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-[#F0EEE7] flex items-center justify-center mb-3">
                            <svg class="w-8 h-8 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="font-bold text-sm text-[#1C1C18]">Belum Ada Pesanan Siap</p>
                        <p class="text-xs text-[#404941] font-normal mt-1">Pesanan akan muncul di sini saat dapur atau bar menandai siap.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="space-y-2 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="w-[15px] h-[16.12px] text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18] leading-6">Sedang Diantar</h3>
                </div>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px]">{{ $deliveringCount }} Pesanan</span>
            </div>

            @forelse ($deliveringOrders as $order)
                <div class="bg-[#F0EEE7] rounded-xl p-3 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-between min-h-[60px]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-[#FFDEA1] text-[#261900] font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                            {{ $order['meja']['kode_meja'] }}
                        </div>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5 leading-4">
                                <span class="font-bold text-xs text-[#1C1C18] tracking-[0.24px]">Pemesan: {{ $order['customer'] }}</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                            </div>
                            <span class="font-semibold text-xs text-[#00341A] leading-4 mt-0.5">
                                {{ $order['meja']['kode_meja'] }} • {{ $order['main_item'] }}{{ $order['side_items'] ? ' ' . $order['side_items'] : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col items-end leading-3">
                        <span class="font-bold text-[10px] text-[#7A5900] tracking-[0.4px]">{{ $order['time_ago'] }}</span>
                        <span class="font-bold text-[10px] text-[#404941] tracking-[0.4px] mt-1">Di Jalur</span>
                    </div>
                </div>
            @empty
                <div class="bg-[#F0EEE7] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                    <p class="font-bold text-xs text-[#1C1C18]">Tidak ada pesanan sedang diantar</p>
                    <p class="text-[10px] text-[#404941] font-normal mt-0.5">Status diantar akan muncul dari pesanan yang tersimpan di database.</p>
                </div>
            @endforelse
        </section>

        <section class="bg-[#EBE8E1] rounded-xl p-2 px-3 flex items-center justify-between h-12 shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#E5E2DB] flex items-center justify-center flex-shrink-0">
                    <svg class="w-[15px] h-[14.58px] text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-xs text-[#1C1C18] tracking-[0.24px] leading-4" x-text="soundVibrationActive ? 'Mode Suara & Getar Aktif' : 'Mode Suara & Getar Senyap'">Mode Suara & Getar Aktif</span>
                    <span class="text-xs text-[#404941] font-normal leading-4">Notifikasi pesanan siap dapur</span>
                </div>
            </div>

            <button @click="soundVibrationActive = !soundVibrationActive; simpanPreferensi(); triggerToast(soundVibrationActive ? 'Mode suara & getar diaktifkan.' : 'Mode suara & getar dinonaktifkan.')"
                    type="button"
                    :class="soundVibrationActive ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                <span :class="soundVibrationActive ? 'translate-x-5' : 'translate-x-0.5'"
                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs transition duration-200 ease-in-out mt-0.5"></span>
            </button>
        </section>

    </div>

    <div x-show="showDenahModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-4">
        <div @click.away="showDenahModal = false"
             class="bg-[#FCF9F2] w-full max-w-[390px] rounded-2xl p-5 shadow-2xl border border-[#E5E2DB] space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-[#E5E2DB]">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" />
                        <line x1="8" y1="2" x2="8" y2="18" />
                        <line x1="16" y1="6" x2="16" y2="22" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="'Denah ' + (denahOrder?.meja?.kode_meja ?? '')">Denah Jalur Antar</h3>
                </div>
                <button @click="showDenahModal = false" class="text-[#404941] hover:text-[#1C1C18] text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-4 border border-[#E5E2DB] space-y-3">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#0B4D2B] text-white text-xs font-bold flex items-center justify-center">1</span>
                    <div>
                        <p class="font-bold text-xs text-[#1C1C18]" x-text="'Ambil di ' + (denahOrder?.sumber_label ?? '-')">Ambil di Pass</p>
                        <p class="text-[11px] text-[#404941]" x-text="denahOrder ? (denahOrder.customer + ' • ' + denahOrder.meja.kode_meja) : '-'">-</p>
                    </div>
                </div>
                <div class="ml-3 pl-4 border-l-2 border-dashed border-[#7A5900] py-1 text-[11px] text-[#7A5900] font-medium">
                    Ikuti jalur area menuju saung tujuan.
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#BA1A1A] text-white text-xs font-bold flex items-center justify-center">2</span>
                    <div>
                        <p class="font-bold text-xs text-[#1C1C18]" x-text="'Tujuan: ' + (denahOrder?.meja?.kode_meja ?? '-')">Tujuan: -</p>
                        <p class="text-[11px] text-[#404941]" x-text="denahOrder ? (denahOrder.meja.area + ' • Kapasitas ' + denahOrder.meja.kapasitas + ' orang') : '-'">-</p>
                    </div>
                </div>
            </div>

            <button @click="showDenahModal = false" type="button"
                    class="w-full h-11 rounded-xl bg-[#0B4D2B] text-white font-bold text-sm tracking-[0.14px] cursor-pointer">
                Tutup Denah
            </button>
        </div>
    </div>

    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-20 left-4 right-4 z-50 max-w-[390px] mx-auto pointer-events-none">
        <div class="bg-[#31312C] text-[#F3F0EA] rounded-xl px-4 py-3 flex items-center gap-3 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-white/10 pointer-events-auto">
            <svg class="w-[18.33px] h-[18.33px] text-[#FFDEA1] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-xs font-normal text-[#F3F0EA] leading-4 flex-1" x-text="toastMessage">Notifikasi berhasil diproses.</span>
        </div>
    </div>
</div>

<script>
    function pelayanTabAntar() {
        return {
            activeFilter: 'all',
            orders: @json($siapAntarFormatted),
            soundVibrationActive: true,
            showToast: false,
            toastMessage: 'Pesanan berhasil ditandai sedang diantar.',
            showDenahModal: false,
            denahOrder: null,

            init() {
                this.soundVibrationActive = localStorage.getItem('pelayan_sound_alert') !== '0';
            },

            triggerToast(message) {
                this.toastMessage = message;
                this.showToast = true;
                if (this.soundVibrationActive && typeof playChime === 'function') {
                    playChime(message.includes('gagal') ? 'urgent' : 'success');
                }
                setTimeout(() => {
                    this.showToast = false;
                }, 3500);
            },

            simpanPreferensi() {
                localStorage.setItem('pelayan_sound_alert', this.soundVibrationActive ? '1' : '0');
            },

            openDenah(kodePesanan) {
                const order = this.orders.find(item => item.kode_pesanan === kodePesanan);

                if (!order) {
                    this.triggerToast('Detail pesanan tidak ditemukan.');
                    return;
                }

                this.denahOrder = order;
                this.showDenahModal = true;
            },

            async deliverOrder(kodePesanan, state) {
                if (state.busy) return;

                state.busy = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const response = await fetch(`/pesanan/${encodeURIComponent(kodePesanan)}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ status_pesanan: 'diantar' }),
                    });

                    if (!response.ok) {
                        const payload = await response.json().catch(() => ({}));
                        throw new Error(payload.message || 'Status pesanan gagal diperbarui.');
                    }

                    state.delivered = true;
                    this.triggerToast('Pesanan ditandai sedang diantar.');
                    setTimeout(() => window.location.reload(), 500);
                } catch (error) {
                    this.triggerToast(error.message || 'Status pesanan gagal diperbarui.');
                } finally {
                    state.busy = false;
                }
            },
        };
    }
</script>
@endsection
