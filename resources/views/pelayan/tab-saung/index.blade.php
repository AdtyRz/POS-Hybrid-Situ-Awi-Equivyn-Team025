@extends('layouts.pelayan')

@section('title', '07C - Mobile POS Pelayan (Tab Saung) • Situ Awi')

@section('content')
@php
    $persentaseMeja = $totalMeja > 0 ? (int) round(($terisiCount / $totalMeja) * 100) : 0;
    $mejaOptions = $meja->map(fn ($item) => [
        'id' => $item->id,
        'kode_meja' => $item->kode_meja,
        'area' => $item->area,
        'kapasitas' => $item->kapasitas,
    ])->values();
@endphp

<div x-data="pelayanTabSaung()" class="relative flex flex-col min-h-screen bg-[#FCF9F2]" x-init="init()">

    <header class="sticky top-0 z-40 bg-[rgba(252,249,242,0.85)] backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-4 py-2.5 h-16 flex items-center justify-between border-b border-[#E5E2DB]/50">
        <div class="flex items-center gap-2">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 max-w-[45.25px] w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-[1px] uppercase leading-3">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-[-0.4px] leading-5">Saung</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F0EEE7] shadow-[0_1px_4px_rgba(43,42,38,0.04)]">
                <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[-0.25px]">LIVE</span>
            </div>

            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-[10px] flex items-center justify-center shadow-[0_1px_4px_rgba(0,0,0,0.08)]">
                {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
            </div>
        </div>
    </header>

    <div class="px-4 py-3 space-y-4 flex-1">

        <section class="bg-[#F6F3EC] rounded-2xl p-4 shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="relative flex-shrink-0">
                        <div class="w-11 h-11 rounded-full bg-[#0B4D2B] text-white font-bold text-base flex items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)]">
                            {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-[#B1F1C2] shadow-[0_0_0_2px_#F6F3EC]"></span>
                    </div>

                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5 leading-6 min-w-0">
                            <span class="font-bold text-base text-[#1C1C18] truncate">{{ auth()->user()->nama ?? auth()->user()->name ?? 'Pelayan' }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-[#F0EEE7] text-[#404941] text-[10px] font-bold tracking-[0.4px] flex-shrink-0">
                                STF-{{ str_pad(auth()->user()->id ?? 1, 3, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-[#404941] leading-4">
                            <svg class="w-3 h-3 text-[#7A5900] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>{{ $shift['label'] }}</span>
                        </div>
                    </div>
                </div>

                <button @click="openQr({{ $tables->first()['id'] ?? 'null' }})" type="button"
                        class="w-10 h-10 rounded-xl bg-white shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center transition-all active:scale-95 cursor-pointer flex-shrink-0">
                    <svg class="w-[18.33px] h-[18.33px] text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </button>
            </div>

            <div class="bg-[#F0EEE7] rounded-xl px-3 py-2 flex items-center justify-between text-xs h-8 gap-2">
                <div class="flex items-center gap-1.5 text-[#1C1C18] font-semibold tracking-[0.24px] truncate">
                    <svg class="w-[13.88px] h-[15.36px] text-[#00341A] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                    </svg>
                    <span class="truncate">{{ $currentArea }} • {{ $totalMeja }} Saung</span>
                </div>

                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-[#404941] text-[10px] font-bold tracking-[0.4px] flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-[#00341A]"></span>
                    <span>{{ $device['ringkas'] }}</span>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-4 gap-2">
            <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px]">
                <span class="font-bold text-[20px] text-[#1C1C18] leading-7">{{ $totalMeja }}</span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5">Total Saung</span>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px]">
                <span class="font-bold text-[20px] text-[#0B4D2B] leading-7">{{ $terisiCount }}</span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5">Terisi ({{ $persentaseMeja }}%)</span>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px]">
                <span class="font-bold text-[20px] text-[#7A5900] leading-7">{{ $tersediaCount }}</span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] mt-0.5">Tersedia</span>
            </div>

            <div class="bg-[#FFDAD6] rounded-xl p-2.5 flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] h-[62px] relative">
                <span class="font-bold text-[20px] text-[#93000A] leading-7">{{ $panggilanCount }}</span>
                <span class="text-[10px] font-semibold text-[#93000A] tracking-[0.4px] mt-0.5">Panggilan</span>
                @if ($panggilanCount > 0)
                    <span class="w-2.5 h-2.5 rounded-full bg-[#BA1A1A] animate-ping absolute -top-1 -right-1"></span>
                @endif
            </div>
        </section>

        <section class="flex items-center gap-2 overflow-x-auto py-1 scrollbar-none">
            <button @click="selectedCluster = 'all'" type="button"
                    :class="selectedCluster === 'all' ? 'bg-[#0B4D2B] text-white' : 'bg-[#EBE8E1] text-[#1C1C18]'"
                    class="inline-flex items-center gap-1.5 px-4 h-9 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Semua Saung ({{ $totalMeja }})</span>
            </button>

            @foreach ($clusters as $cluster)
                <button @click="selectedCluster = @js($cluster['key'])" type="button"
                        :class="selectedCluster === @js($cluster['key']) ? 'bg-[#0B4D2B] text-white' : 'bg-[#EBE8E1] text-[#1C1C18]'"
                        class="inline-flex items-center gap-1.5 px-4 h-9 rounded-full shadow-[0_1px_2px_rgba(0,0,0,0.05)] text-xs font-semibold tracking-[0.24px] whitespace-nowrap transition-all active:scale-95 cursor-pointer flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                    <span>{{ $cluster['name'] }} ({{ $cluster['count'] }})</span>
                </button>
            @endforeach
        </section>

        @forelse ($clusters as $cluster)
            <section x-show="selectedCluster === 'all' || selectedCluster === @js($cluster['key'])" class="space-y-3 pt-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                        <h2 class="font-bold text-base text-[#1C1C18] leading-6">{{ $cluster['name'] }}</h2>
                    </div>
                    <span class="text-xs font-medium text-[#404941]">{{ $cluster['count'] }} Saung</span>
                </div>

                <div class="space-y-3">
                    @foreach ($cluster['tables'] as $table)
                        <div x-data="{ tableState: { busy: false, done: false } }"
                             x-show="!tableState.done"
                             class="rounded-2xl shadow-[0_1px_2px_rgba(0,0,0,0.05)] border p-4 flex flex-col gap-2 {{ $table['is_calling'] ? 'bg-white border-[#BA1A1A]/30 ring-1 ring-[#BA1A1A]' : ($table['status'] === 'siap_antar' ? 'bg-white border-[#FECE66]/70' : ($table['status'] === 'tersedia' ? 'bg-[#F6F3EC] border-[#E5E2DB]/70' : 'bg-white border-[#F0EEE7]')) }}">

                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-12 h-12 rounded-xl flex flex-col items-center justify-center shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex-shrink-0
                                        {{ $table['is_calling'] ? 'bg-[#FFDAD6] text-[#93000A]' : ($table['status'] === 'siap_antar' ? 'bg-[#FFDEA1] text-[#261900]' : 'bg-[#F0EEE7] text-[#1C1C18]') }}">
                                        <span class="text-[10px] font-bold uppercase tracking-[0.4px]">SAUNG</span>
                                        <span class="text-[20px] font-bold leading-5">{{ str_pad($table['number'], 2, '0', STR_PAD_LEFT) }}</span>
                                    </div>

                                    <div class="flex flex-col min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-bold text-base text-[#1C1C18] leading-6">{{ $table['kode_meja'] }}</h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-[0.4px]
                                                {{ $table['status'] === 'panggilan' ? 'bg-[#BA1A1A] text-white animate-pulse' : ($table['status'] === 'siap_antar' ? 'bg-[#FECE66] text-[#765600]' : 'bg-[#F0EEE7] text-[#404941]') }}">
                                                {{ $table['status_label'] }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5 truncate">{{ $table['bill_info'] }}</span>
                                        <span class="text-[10px] text-[#7A5900] font-semibold leading-4 mt-0.5">{{ $table['source_label'] }} • {{ $table['area'] }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 text-[10px] font-bold {{ $table['is_calling'] ? 'text-[#BA1A1A]' : 'text-[#404941]' }} flex-shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span>{{ $table['time_ago'] }}</span>
                                </div>
                            </div>

                            @if ($table['is_calling'] && $table['call'])
                                <div class="bg-[rgba(255,218,214,0.6)] rounded-xl p-2.5 flex items-start gap-2 border border-[#BA1A1A]/20">
                                    <svg class="w-4 h-4 text-[#BA1A1A] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-[#93000A] uppercase tracking-[0.5px]">PANGGILAN PELAYAN</span>
                                        <p class="text-sm font-semibold text-[#93000A] leading-snug mt-0.5">
                                            Tamu memanggil dari {{ $table['kode_meja'] }} • {{ $table['call']['device'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button @click="respondCall({{ $table['call']['id'] }}, tableState)" :disabled="tableState.busy"
                                            type="button"
                                            class="flex-1 h-12 rounded-xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm tracking-[0.14px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center gap-1.5 transition-all active:scale-98 cursor-pointer disabled:opacity-60">
                                        <svg class="w-3.5 h-4.5 text-[#765600]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span x-text="tableState.busy ? 'Menyimpan...' : 'Respon & Temui'">Respon & Temui</span>
                                    </button>
                                    <button @click="openDetail({{ $table['id'] }})" type="button"
                                            class="h-12 px-3 rounded-xl bg-[#F0EEE7] text-[#404941] font-semibold text-xs transition-all active:scale-98 cursor-pointer">
                                        Detail
                                    </button>
                                </div>
                            @elseif ($table['order_count'] > 0)
                                <div class="bg-[#F6F3EC] rounded-xl p-2.5 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <svg class="w-4 h-4 text-[#7A5900] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z" />
                                        </svg>
                                        <span class="font-semibold text-sm text-[#1C1C18] leading-5 truncate">
                                            {{ $table['items_summary'] ?: 'Belum ada item' }}{{ $table['sisa_item'] > 0 ? ', +' . $table['sisa_item'] . ' item' : '' }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] flex-shrink-0">{{ $table['total_qty'] }} Porsi</span>
                                </div>

                                @if ($table['status'] === 'diproses')
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[10px] font-bold text-[#404941] tracking-[0.4px]">
                                            <span>Progres item siap</span>
                                            <span>{{ $table['progress_label'] }}</span>
                                        </div>
                                        <div class="w-full bg-[#F0EEE7] h-2 rounded-full overflow-hidden">
                                            <div class="bg-[#0B4D2B] h-full rounded-full" style="width: {{ $table['progress'] }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-center gap-2 pt-1">
                                    @if ($table['status'] === 'siap_antar' && $table['primary_order'])
                                        <button @click="deliverOrder('{{ $table['primary_order']['kode_pesanan'] }}', tableState)" :disabled="tableState.busy"
                                                type="button"
                                                class="flex-1 h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs tracking-[0.24px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer disabled:opacity-60">
                                            <svg class="w-4 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <span x-text="tableState.busy ? 'Menyimpan...' : 'Ambil & Antar'">Ambil & Antar</span>
                                        </button>
                                    @endif

                                    <button @click="openDetail({{ $table['id'] }})" type="button"
                                            class="flex-1 h-11 rounded-xl bg-white text-[#1C1C18] font-semibold text-xs tracking-[0.24px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#E5E2DB] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                                        Lihat Rincian
                                    </button>

                                    <button @click="openMenu({{ $table['id'] }})" type="button"
                                            class="w-11 h-11 rounded-xl bg-[#FECE66] text-[#765600] font-bold text-lg shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center active:scale-98 cursor-pointer">
                                        +
                                    </button>
                                </div>
                            @else
                                <div class="text-xs text-[#404941] leading-4">
                                    Kapasitas {{ $table['kapasitas'] }} orang • {{ $table['device'] }}
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <button @click="openQr({{ $table['id'] }})" type="button"
                                            class="flex-1 h-11 rounded-xl bg-white text-[#1C1C18] font-semibold text-xs tracking-[0.24px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-[#E5E2DB] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        <span>QR & Sesi</span>
                                    </button>

                                    <button @click="openMenu({{ $table['id'] }})" type="button"
                                            class="flex-1 h-11 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs tracking-[0.24px] shadow-[0_1px_2px_rgba(0,0,0,0.05)] flex items-center justify-center gap-1.5 active:scale-98 cursor-pointer">
                                        <svg class="w-4 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                        <span>Pesan Menu</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="bg-[#F6F3EC] rounded-2xl p-8 text-center">
                <p class="font-bold text-sm text-[#1C1C18]">Belum ada meja di area tugas</p>
                <p class="text-xs text-[#404941] mt-1">Data meja akan muncul setelah ditambahkan pada master meja.</p>
            </div>
        @endforelse

        <div class="pt-3 pb-2">
            <button @click="openMenu({{ $tables->first()['id'] ?? 'null' }})" type="button"
                    class="w-full h-14 rounded-2xl bg-[#FECE66] hover:bg-[#F5BF45] text-[#765600] font-bold text-sm tracking-[0.35px] shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1),0_4px_6px_-4px_rgba(0,0,0,0.1)] flex items-center justify-center gap-2.5 transition-all active:scale-98 cursor-pointer">
                <div class="w-7 h-7 rounded-full bg-[#765600] text-[#FECE66] flex items-center justify-center font-bold text-sm">
                    +
                </div>
                <span>Input Pesanan Walk-in / Tambahan</span>
            </button>
        </div>

    </div>

    <div x-show="showQrModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showQrModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="qrTable ? 'QR ' + qrTable.kode_meja : 'QR Saung'">QR Saung</h3>
                </div>
                <button @click="showQrModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <div class="bg-[#F6F3EC] rounded-xl p-4 flex flex-col items-center gap-3 border border-[#E5E2DB]">
                <canvas x-ref="qrCanvas" class="w-40 h-40 bg-white rounded-lg p-2"></canvas>
                <p x-show="!qrReady" class="text-xs text-[#404941] text-center">Pratinjau QR memerlukan build frontend. Gunakan tautan di bawah untuk membuka meja.</p>
                <div class="text-center">
                    <p class="font-bold text-sm text-[#1C1C18]" x-text="qrTable?.kode_meja ?? '-'">-</p>
                    <p class="text-xs text-[#404941]" x-text="qrTable?.area ?? '-'">-</p>
                    <p class="text-[11px] font-mono text-[#00341A] mt-1" x-text="qrTable?.session_code ? 'Kode sesi: ' + qrTable.session_code : 'Sesi belum aktif'">Sesi belum aktif</p>
                </div>
                <a :href="qrTable?.qr_url ?? '#'" target="_blank" rel="noopener"
                   class="text-[11px] text-[#0B4D2B] underline break-all text-center" x-text="qrTable?.qr_url ?? '-'">-</a>
            </div>

            <button x-show="qrTable && !qrTable.session_active" @click="aktifkanSesi()" :disabled="qrLoading"
                    type="button"
                    class="w-full py-2.5 bg-[#00341A] hover:bg-[#0B4D2B] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98 cursor-pointer disabled:opacity-60">
                <span x-text="qrLoading ? 'Mengaktifkan...' : 'Aktifkan Sesi & QR'">Aktifkan Sesi & QR</span>
            </button>
            <button @click="showQrModal = false" type="button" class="w-full py-2.5 bg-[#F0EEE7] text-[#404941] font-semibold text-xs rounded-xl cursor-pointer">
                Tutup
            </button>
        </div>
    </div>

    <div x-show="showMenuModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showMenuModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full max-h-[88vh] overflow-y-auto space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#7A5900]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Input Pesanan Tambahan</h3>
                </div>
                <button @click="showMenuModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[#1C1C18] block mb-1">Pilih Saung / Meja</label>
                    <select x-model.number="menuMejaId" class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl p-2.5 font-semibold text-[#1C1C18]">
                        <template x-for="mejaItem in mejaOptions" :key="mejaItem.id">
                            <option :value="mejaItem.id" x-text="mejaItem.kode_meja + ' (' + mejaItem.area + ' - ' + mejaItem.kapasitas + ' Tamu)'"></option>
                        </template>
                    </select>
                </div>

                <div x-show="menuCategories.length === 0" class="bg-[#F6F3EC] rounded-xl p-3 text-center text-[#404941]">
                    Belum ada menu aktif dan bersedia stok.
                </div>

                <div class="space-y-2" x-show="menuCategories.length > 0">
                    <div class="flex gap-1.5 overflow-x-auto pb-1">
                        <template x-for="kategori in menuCategories" :key="kategori.id">
                            <button @click="selectedMenuCategory = kategori.id" type="button"
                                    :class="selectedMenuCategory === kategori.id ? 'bg-[#0B4D2B] text-white' : 'bg-[#F0EEE7] text-[#404941]'"
                                    class="px-3 py-1.5 rounded-full text-[10px] font-bold whitespace-nowrap transition-all"
                                    x-text="kategori.nama"></button>
                        </template>
                    </div>

                    <div class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                        <template x-for="menuItem in menuTampil" :key="menuItem.id">
                            <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-[#F0EEE7]">
                                <div class="flex flex-col min-w-0">
                                    <span class="font-semibold text-[#1C1C18] truncate" x-text="menuItem.nama"></span>
                                    <span class="text-[10px] text-[#404941]" x-text="menuItem.harga_format + ' • Stok ' + menuItem.stok"></span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    <button @click="ubahJumlah(menuItem.id, -1)" type="button" class="w-6 h-6 rounded-full bg-white text-[#1C1C18] font-bold">−</button>
                                    <span class="w-5 text-center font-bold text-[#00341A]" x-text="cart[menuItem.id] || 0"></span>
                                    <button @click="ubahJumlah(menuItem.id, 1)" type="button" class="w-6 h-6 rounded-full bg-[#0B4D2B] text-white font-bold">+</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-[#1C1C18] block mb-1">Catatan Pesanan</label>
                    <textarea x-model="menuCatatan" rows="2" maxlength="255" placeholder="Opsional" class="w-full bg-[#F6F3EC] border border-[#E5E2DB] rounded-xl p-2.5 text-[#1C1C18]"></textarea>
                </div>

                <div class="flex items-center justify-between bg-[#F6F3EC] rounded-xl p-2.5">
                    <span class="font-bold text-[#1C1C18]">Total</span>
                    <span class="font-bold text-[#00341A]" x-text="formatRupiah(cartTotal)">Rp 0</span>
                </div>
            </div>

            <button @click="submitPesanan()" :disabled="menuSubmitting || cartTotal <= 0" type="button"
                    class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98 cursor-pointer disabled:opacity-60">
                <span x-text="menuSubmitting ? 'Mengirim...' : 'Kirim ke Dapur / Bar'">Kirim ke Dapur / Bar</span>
            </button>
        </div>
    </div>

    <div x-show="showDetailModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showDetailModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full max-h-[88vh] overflow-y-auto space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="detailTable ? 'Rincian ' + detailTable.kode_meja : 'Rincian Pesanan'">Rincian Pesanan</h3>
                </div>
                <button @click="showDetailModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <template x-if="detailTable && detailTable.orders.length === 0">
                <p class="text-xs text-[#404941] text-center py-4">Belum ada pesanan aktif untuk saung ini.</p>
            </template>

            <template x-for="order in (detailTable?.orders ?? [])" :key="order.id">
                <div class="bg-[#F6F3EC] rounded-xl p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="font-mono font-bold text-[11px] text-[#404941]" x-text="'#' + order.kode_pesanan"></span>
                            <span class="text-[10px] text-[#7A5900] font-semibold" x-text="order.customer + ' • ' + order.status_label"></span>
                        </div>
                        <span class="font-bold text-xs text-[#00341A]" x-text="order.total_format"></span>
                    </div>
                    <div class="space-y-1">
                        <template x-for="item in order.items" :key="item.id">
                            <div class="flex items-start gap-2 text-xs">
                                <span class="font-bold text-[#00341A]" x-text="item.jumlah + 'x'"></span>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-[#1C1C18]" x-text="item.nama"></span>
                                    <span class="text-[10px] text-[#404941]" x-text="item.kategori + ' • ' + item.status_label"></span>
                                    <span x-show="item.catatan" class="text-[10px] italic text-[#7A5900]" x-text="'Catatan: ' + item.catatan"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="order.catatan" class="text-[10px] text-[#404941] border-t border-[#E5E2DB] pt-1" x-text="'Catatan pesanan: ' + order.catatan"></div>
                </div>
            </template>

            <button @click="showDetailModal = false" type="button" class="w-full py-2.5 bg-[#0B4D2B] text-white font-bold text-xs rounded-xl cursor-pointer">
                Tutup
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
    function pelayanTabSaung() {
        return {
            selectedCluster: 'all',
            tables: @json($tables),
            menuCategories: @json($menu),
            mejaOptions: @json($mejaOptions),
            selectedMenuCategory: {{ $menu->first()['id'] ?? 'null' }},
            menuMejaId: {{ $tables->first()['id'] ?? 'null' }},
            menuCatatan: '',
            cart: {},
            menuSubmitting: false,

            showQrModal: false,
            qrTable: null,
            qrReady: false,
            qrLoading: false,
            showMenuModal: false,
            showDetailModal: false,
            detailTable: null,

            soundVibrationActive: true,
            toastMessage: 'Notifikasi berhasil diproses.',
            showToast: false,

            init() {
                this.soundVibrationActive = localStorage.getItem('pelayan_sound_alert') !== '0';

                this.$watch('showQrModal', (open) => {
                    if (open) this.$nextTick(() => this.renderQr());
                });
            },

            get menuTampil() {
                const kategori = this.menuCategories.find(item => item.id === this.selectedMenuCategory);
                return kategori ? kategori.menus : [];
            },

            get menuList() {
                return this.menuCategories.flatMap(item => item.menus);
            },

            get cartItems() {
                return this.menuList.filter(item => (this.cart[item.id] || 0) > 0);
            },

            get cartTotal() {
                return this.cartItems.reduce((total, item) => total + (item.harga * this.cart[item.id]), 0);
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

            formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value || 0);
            },

            openDetail(tableId) {
                const table = this.tables.find(item => item.id === tableId);

                if (!table) {
                    this.triggerToast('Data saung tidak ditemukan.');
                    return;
                }

                this.detailTable = table;
                this.showDetailModal = true;
            },

            openMenu(mejaId) {
                if (!mejaId && !this.menuMejaId) {
                    this.triggerToast('Belum ada saung pada area ini.');
                    return;
                }

                if (mejaId && mejaId !== this.menuMejaId) {
                    this.cart = {};
                    this.menuCatatan = '';
                }

                this.menuMejaId = mejaId || this.menuMejaId;
                this.showMenuModal = true;
            },

            ubahJumlah(menuId, delta) {
                const menuItem = this.menuList.find(item => item.id === menuId);
                const maksimal = menuItem ? menuItem.stok : 99;
                const berikutnya = Math.max(0, Math.min(maksimal, (this.cart[menuId] || 0) + delta));

                if (berikutnya === 0) {
                    delete this.cart[menuId];
                } else {
                    this.cart[menuId] = berikutnya;
                }
                this.cart = { ...this.cart };
            },

            async submitPesanan() {
                if (this.menuSubmitting || this.cartTotal <= 0) return;

                if (!this.menuMejaId) {
                    this.triggerToast('Pilih saung tujuan terlebih dahulu.');
                    return;
                }

                this.menuSubmitting = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const response = await fetch('{{ route('pelayan.pesanan.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            meja_id: this.menuMejaId,
                            jumlah: this.cart,
                            catatan: this.menuCatatan,
                        }),
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) throw new Error(payload.message || 'Pesanan gagal dikirim.');

                    this.cart = {};
                    this.menuCatatan = '';
                    this.showMenuModal = false;
                    this.triggerToast(payload.message || 'Pesanan berhasil dikirim.');
                    setTimeout(() => window.location.reload(), 500);
                } catch (error) {
                    this.triggerToast(error.message || 'Pesanan gagal dikirim.');
                } finally {
                    this.menuSubmitting = false;
                }
            },

            async respondCall(panggilanId, state) {
                if (state.busy) return;

                state.busy = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const response = await fetch(`/pelayan/panggilan/${panggilanId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ status_panggilan: 'selesai' }),
                    });

                    if (!response.ok) throw new Error('Panggilan gagal diperbarui.');

                    state.done = true;
                    this.triggerToast('Panggilan berhasil ditandai selesai.');
                    setTimeout(() => window.location.reload(), 500);
                } catch (error) {
                    this.triggerToast(error.message || 'Panggilan gagal diperbarui.');
                } finally {
                    state.busy = false;
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

                    state.done = true;
                    this.triggerToast('Pesanan ditandai sedang diantar.');
                    setTimeout(() => window.location.reload(), 500);
                } catch (error) {
                    this.triggerToast(error.message || 'Status pesanan gagal diperbarui.');
                } finally {
                    state.busy = false;
                }
            },

            openQr(tableId) {
                const table = this.tables.find(item => item.id === tableId);

                if (!table) {
                    this.triggerToast('Belum ada saung pada area ini.');
                    return;
                }

                this.qrTable = { ...table };
                this.qrReady = false;
                this.showQrModal = true;
            },

            async aktifkanSesi() {
                if (!this.qrTable || this.qrLoading) return;

                this.qrLoading = true;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const response = await fetch(`/pelayan/meja/${encodeURIComponent(this.qrTable.kode_meja)}/buka`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) throw new Error(payload.message || 'Sesi gagal diaktifkan.');

                    this.qrTable.session_active = true;
                    this.qrTable.session_code = payload.kode_unik;
                    this.qrTable.qr_url = payload.qr_url;

                    const table = this.tables.find(item => item.id === this.qrTable.id);
                    if (table) {
                        table.session_active = true;
                        table.session_code = payload.kode_unik;
                        table.qr_url = payload.qr_url;
                    }

                    this.$nextTick(() => this.renderQr());
                    this.triggerToast('Sesi meja berhasil diaktifkan.');
                } catch (error) {
                    this.triggerToast(error.message || 'Sesi gagal diaktifkan.');
                } finally {
                    this.qrLoading = false;
                }
            },

            renderQr() {
                this.qrReady = false;

                if (!this.qrTable?.qr_url || !window.QRCode) return;

                const canvas = this.$refs.qrCanvas;
                window.QRCode.toCanvas(canvas, this.qrTable.qr_url, {
                    width: 160,
                    margin: 1,
                    color: {
                        dark: '#00341A',
                        light: '#FFFFFF',
                    },
                }).then(() => {
                    this.qrReady = true;
                }).catch(() => {});
            },
        };
    }
</script>
@endsection
