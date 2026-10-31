@extends('layouts.pelayan')

@section('title', '07 - Mobile POS & Notifikasi Panggilan Pelayan • Situ Awi')

@section('content')
<div x-data="pelayanNotifikasiPanggilan()" class="relative flex flex-col min-h-screen bg-[#FCF9F2]" x-init="init()">

    <header class="sticky top-0 z-40 bg-[rgba(252,249,242,0.85)] backdrop-blur-md shadow-[0_2px_12px_rgba(43,42,38,0.04)] px-4 py-3 h-20 flex items-center justify-between border-b border-[#E5E2DB]/50">
        <div class="flex items-center gap-2">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 max-w-[45.25px] w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <div class="flex items-center gap-1.5 leading-6">
                    <span class="font-semibold text-base text-[#00341A] tracking-[-0.4px]">Situ Awi Runner</span>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-[rgba(177,241,194,0.5)] text-[#11512F] text-[10px] font-bold tracking-[0.4px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2E6A45]"></span>
                        LIVE
                    </span>
                </div>
                <span class="text-xs text-[#404941] font-normal leading-4">{{ auth()->user()->nama ?? auth()->user()->name ?? 'Pelayan' }} • {{ $currentArea }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button @click="triggerToast('Halaman ini mengikuti perubahan panggilan, pesanan, dan menu secara realtime.')" type="button" class="w-11 h-11 rounded-full bg-[#F6F3EC] flex items-center justify-center relative active:scale-95 transition-transform cursor-pointer">
                <svg class="w-[14.67px] h-[18.33px] text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if ($panggilan->isNotEmpty())
                    <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full bg-[#BA1A1A] shadow-[0_0_0_2px_#FCF9F2]"></span>
                @endif
            </button>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-white font-bold text-[10px] tracking-[0.4px] flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
            </div>
        </div>
    </header>

    <div class="px-4 py-3 space-y-4 flex-1">

        <section class="bg-[#F6F3EC] rounded-xl p-4 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1 min-w-0">
                    <svg class="w-[13.33px] h-[16.67px] text-[#00341A] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold text-base text-[#1C1C18] leading-6 truncate">{{ $currentArea }}</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-[#EBE8E1] text-[#404941] text-[10px] font-bold tracking-[0.4px] flex-shrink-0">
                    <svg class="w-[10.83px] h-[9.34px] text-[#213200]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1 8 8 0 0114 0 1 1 0 11-1.732 1A6 6 0 003.732 12 1 1 0 012 11zm3.5 3a1 1 0 011-1 4 4 0 017 0 1 1 0 11-1.732 1 2 2 0 00-3.536 0 1 1 0 01-1.732-1zM9 17a1 1 0 112 0 1 1 0 01-2 0z" />
                    </svg>
                    <span>{{ $device['label'] }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between pb-1">
                <div class="flex items-center gap-1 text-[#404941]">
                    <svg class="w-[12.5px] h-[12.5px] text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <span class="text-xs text-[#404941] font-normal leading-4">{{ $shift['label'] }}</span>
                </div>
                <div class="flex items-center gap-1 text-[#00341A] font-semibold text-xs leading-4 tracking-[0.24px]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                    <span>Koneksi Sinkron</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-1 pt-1">
                <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[69px]">
                    <span class="font-bold text-[20px] text-[#00341A] leading-7">{{ $statistik['calls_answered'] }}</span>
                    <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] text-center leading-3 uppercase mt-0.5">PANGGILAN<br>DIRESPON</span>
                </div>
                <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[69.5px]">
                    <span class="font-bold text-[20px] text-[#7A5900] leading-7">{{ $statistik['orders_delivered'] }}</span>
                    <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] text-center leading-3 uppercase mt-0.5">PESANAN<br>DIANTAR</span>
                </div>
                <div class="bg-white rounded-lg p-2 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[69.5px]">
                    <span class="font-bold text-[20px] text-[#334A00] leading-7">{{ $statistik['avg_response_menit'] > 0 ? $statistik['avg_response_menit'] . 'm' : '—' }}</span>
                    <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] text-center leading-3 uppercase mt-0.5">RATA<br>RESPON</span>
                </div>
            </div>
        </section>

        @if ($urgentCall)
        <section x-show="hasUrgentAlert && !alertResponded"
                 x-transition:leave="transition ease-in duration-300 transform opacity-0 -translate-y-4"
                 class="bg-[#FFDAD6] rounded-xl p-4 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] relative overflow-hidden flex flex-col gap-2 isolation-auto">

            <div class="absolute -right-8 -top-8 w-28 h-28 bg-[rgba(254,206,102,0.4)] blur-md rounded-full pointer-events-none"></div>

            <div class="flex items-start justify-between relative z-10">
                <div class="flex items-start gap-2">
                    <div class="w-10 h-10 rounded-full bg-[#BA1A1A] flex items-center justify-center shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] flex-shrink-0">
                        <svg class="w-5 h-5 text-white animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5 h-5">
                            <span class="text-[10px] font-bold text-[#BA1A1A] tracking-[0.5px] uppercase">PANGGILAN PELAYAN</span>
                            <span class="px-2 h-5 rounded-full text-[10px] font-normal text-white flex items-center justify-center leading-5 bg-[#BA1A1A]">
                                {{ $panggilan->count() }} AKTIF
                            </span>
                        </div>
                        <h2 class="text-[20px] font-semibold text-[#93000A] tracking-[-0.5px] leading-7">
                            {{ $urgentCall['nama_saung'] }} ({{ $urgentCall['kode_meja'] }})
                        </h2>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-1">
                    <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                        <span class="w-2 h-2 rounded-full bg-[#BA1A1A] animate-ping"></span>
                        <span class="text-[10px] font-bold text-[#BA1A1A] tracking-[0.4px] text-right" x-text="formatElapsed(alertSeconds)">00:00</span>
                    </div>
                    <span class="text-[10px] font-normal text-[rgba(147,0,10,0.8)] text-right">{{ $urgentCall['device'] }}</span>
                </div>
            </div>

            <div class="bg-[rgba(255,255,255,0.8)] backdrop-blur-xs rounded-lg p-2 flex flex-col gap-1 relative z-10 border border-white/60">
                <div class="flex items-center justify-between text-xs">
                    <div class="inline-flex items-center gap-1 font-bold text-xs text-[#1C1C18] tracking-[0.24px]">
                        <svg class="w-3 h-3.5 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span>Permintaan Tamu:</span>
                    </div>
                    <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px]">{{ $urgentCall['time_ago'] }}</span>
                </div>

                <p class="text-[14px] italic font-normal text-[#1C1C18] leading-[23px]">
                    "Tamu memanggil pelayan dari {{ $urgentCall['nama_saung'] }}. Mohon segera temui meja di {{ $urgentCall['area'] }}."
                </p>

                <div class="flex items-center gap-2 pt-1 text-[11px] font-normal text-[#404941] leading-5 border-t border-[#F0EEE7]">
                    <div class="inline-flex items-center gap-0.5">
                        <svg class="w-2.5 h-2.5 text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ $urgentCall['device'] }}</span>
                    </div>
                    <span>•</span>
                    <span>Panggilan masuk {{ $urgentCall['waktu_label'] }} WIB</span>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-2 relative z-10">
                <button @click="respondToAlert()" type="button" :disabled="processing"
                        class="w-full h-12 rounded-full bg-[#00341A] hover:bg-[#0B4D2B] active:scale-98 text-white font-semibold text-sm tracking-[0.14px] flex items-center justify-center gap-2 shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1)] transition-all cursor-pointer disabled:opacity-60">
                    <svg class="w-3.5 h-4.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-text="processing ? 'Menyimpan...' : 'Respon & Menuju {{ $urgentCall['nama_saung'] }}'">Respon & Menuju {{ $urgentCall['nama_saung'] }}</span>
                </button>

                <button @click="soundVibrationActive = !soundVibrationActive; simpanPreferensi(); triggerToast(soundVibrationActive ? 'Alarm lokal diaktifkan.' : 'Alarm lokal disenyapkan.')" type="button"
                        class="w-full h-12 rounded-full bg-[#EBE8E1] hover:bg-[#E5E2DB] active:scale-98 text-[#1C1C18] font-semibold text-sm tracking-[0.14px] flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                    </svg>
                    <span x-text="soundVibrationActive ? 'Senyapkan Alarm Lokal' : 'Alarm Lokal Senyap'">Senyapkan Alarm Lokal</span>
                </button>
            </div>
        </section>
        @endif

        <section class="flex flex-col gap-2 pt-1">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <svg class="w-[13.75px] h-[18.33px] text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                    </svg>
                    <h3 class="font-semibold text-[20px] text-[#1C1C18] leading-7">Siap Diantarkan</h3>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#B1F1C2] text-[#00210E] text-xs font-semibold tracking-[0.24px]">
                    {{ $siapAntarFormatted->count() }} Hidangan Siap
                </span>
            </div>
            <p class="text-xs text-[#404941] font-normal leading-4 -mt-1">
                Ambil dari pass dapur atau bar sesuai target pesanan di database.
            </p>

            <div class="space-y-3 pt-1">
            @forelse ($siapAntarFormatted as $order)
                <div class="bg-[#F6F3EC] rounded-xl p-4 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex flex-col gap-2"
                     x-data="{ orderState: { delivered: false, busy: false } }"
                     x-show="!orderState.delivered">
                    <div class="flex items-start justify-between">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md {{ $order['sumber'] === 'bar' ? 'bg-[#CBEF89] text-[#364E00]' : 'bg-[#FECE66] text-[#765600]' }} text-xs font-bold tracking-[0.24px]">
                                    {{ $order['meja']['kode_meja'] }}
                                </span>
                                <h4 class="font-semibold text-base text-[#1C1C18] leading-6">{{ $order['meja']['kode_meja'] }}</h4>
                            </div>
                            <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5">
                                {{ $order['sumber_label'] }} • {{ $order['customer'] }}
                            </span>
                        </div>
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded bg-[#E5E2DB] text-[#404941] text-[10px] font-bold tracking-[0.4px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#7A5900]"></span>
                            <span>{{ $order['time_ago'] }}</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg p-2.5 flex items-start gap-2">
                        <div class="w-16 h-16 rounded-lg {{ $order['sumber'] === 'bar' ? 'bg-gradient-to-br from-[#CBEF89] to-[#8BAA3F]' : 'bg-gradient-to-br from-[#FFDEA1] to-[#FECE66]' }} flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex-shrink-0">
                            @if ($order['sumber'] === 'bar')
                                <svg class="w-7 h-7 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16l-8 9-8-9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 13v7M8 20h8" />
                                </svg>
                            @else
                                <svg class="w-7 h-7 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 17h18M5 17a7 7 0 0114 0M12 5v3" />
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 flex flex-col justify-center gap-0.5">
                            <h5 class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">{{ $order['main_item'] }}</h5>
                            @if ($order['side_items'])
                                <p class="font-semibold text-xs text-[#404941] leading-4 tracking-[0.24px]">{{ $order['side_items'] }}</p>
                            @endif
                            <p class="font-medium text-xs text-[#7A5900] leading-4">{{ $order['note'] !== 'Tanpa catatan' ? 'Catatan: ' . $order['note'] : 'Tanpa catatan' }}</p>
                        </div>
                    </div>

                    <button @click="deliverOrder('{{ $order['kode_pesanan'] }}', orderState)" :disabled="orderState.busy"
                        type="button"
                        class="w-full h-12 rounded-xl {{ $order['sumber'] === 'bar' ? 'bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600]' : 'bg-[#0B4D2B] hover:bg-[#00341A] text-white' }} font-semibold text-sm tracking-[0.14px] flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all cursor-pointer active:scale-98 disabled:opacity-60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="orderState.busy ? 'Menyimpan...' : 'Ambil & Antar ke {{ $order['meja']['kode_meja'] }}'">Ambil & Antar ke {{ $order['meja']['kode_meja'] }}</span>
                    </button>
                </div>
            @empty
                <div class="bg-[#F6F3EC] rounded-xl p-6 flex flex-col items-center justify-center gap-2 text-center">
                    <svg class="w-10 h-10 text-[#B1F1C2]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-semibold text-sm text-[#0B4D2B]">Semua pesanan sudah diantar</span>
                    <span class="text-xs text-[#404941]">Tidak ada hidangan yang berstatus siap di area Anda.</span>
                </div>
            @endforelse
            </div>
        </section>

        <section class="bg-[#F6F3EC] rounded-xl p-4 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg class="w-[15px] h-[15px] text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <h3 class="font-semibold text-base text-[#1C1C18] leading-6">Status Saung</h3>
                </div>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] truncate max-w-[150px]">{{ $currentArea }}</span>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-1">
                @forelse ($saungStatus as $saung)
                    @php
                        $saungPanggil = $saung['status'] === 'PANGGIL!';
                        $saungSiap = $saung['status'] === 'Siap Antar';
                    @endphp
                    <div class="rounded-lg p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[60px] border {{ $saungPanggil ? 'bg-[#FFDAD6] border-[#BA1A1A]/30' : ($saungSiap ? 'bg-white border-[#FECE66]/70' : 'bg-white border-[#E5E2DB]') }}">
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full {{ $saungPanggil ? 'bg-[#BA1A1A] animate-ping' : ($saungSiap ? 'bg-[#7A5900]' : 'bg-[#0B4D2B]') }}"></span>
                            <span class="font-bold text-xs tracking-[0.24px] {{ $saungPanggil ? 'text-[#93000A]' : ($saungSiap ? 'text-[#7A5900]' : 'text-[#00341A]') }}">{{ $saung['kode_meja'] }}</span>
                        </div>
                        <span class="text-[10px] font-bold leading-5 text-center mt-1 {{ $saungPanggil ? 'text-[#93000A]' : ($saungSiap ? 'text-[#7A5900]' : 'text-[#00341A]') }}">{{ $saung['status'] }}</span>
                    </div>
                @empty
                    <p class="col-span-3 text-center text-xs text-[#404941]">Belum ada meja untuk area ini.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-[#F0EEE7] rounded-xl p-2 px-3 flex items-center justify-between h-[52px]">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-[15px] text-[#7A5900] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                </svg>
                <div class="flex flex-col">
                    <span class="font-semibold text-xs text-[#1C1C18] leading-4 tracking-[0.24px]" x-text="soundVibrationActive ? 'Mode Suara + Getar Aktif' : 'Mode Suara + Getar Senyap'">Mode Suara + Getar Aktif</span>
                    <span class="text-[11px] text-[#404941] font-normal leading-5">Notifikasi realtime untuk {{ $currentArea }}</span>
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
    function pelayanNotifikasiPanggilan() {
        return {
            hasUrgentAlert: {{ $urgentCall ? 'true' : 'false' }},
            alertResponded: false,
            alertSeconds: {{ $urgentCall['elapsed_seconds'] ?? 0 }},
            urgentPanggilanId: {{ $urgentCall['id'] ?? 'null' }},
            processing: false,
            soundVibrationActive: true,
            toastMessage: 'Notifikasi berhasil diproses.',
            showToast: false,

            init() {
                this.soundVibrationActive = localStorage.getItem('pelayan_sound_alert') !== '0';

                setInterval(() => {
                    if (this.hasUrgentAlert && !this.alertResponded) {
                        this.alertSeconds++;
                    }
                }, 1000);
            },

            formatElapsed(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
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

            async respondToAlert() {
                if (!this.urgentPanggilanId || this.processing) return;

                this.processing = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const response = await fetch(`/pelayan/panggilan/${this.urgentPanggilanId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ status_panggilan: 'selesai' }),
                    });

                    if (!response.ok) throw new Error('Gagal memperbarui panggilan.');

                    this.alertResponded = true;
                    this.hasUrgentAlert = false;
                    this.triggerToast('Panggilan berhasil ditandai selesai.');
                    setTimeout(() => window.location.reload(), 500);
                } catch (error) {
                    this.triggerToast(error.message || 'Panggilan gagal diperbarui.');
                } finally {
                    this.processing = false;
                }
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
