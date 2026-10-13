@extends('layouts.pelayan')

@section('title', '07D - Mobile POS Pelayan (Tab Shift) • Situ Awi')

@section('content')
<div x-data="pelayanTabShift()" class="relative flex flex-col min-h-screen bg-[#FCF9F2]" x-init="init()">

    <!-- Header (07D - Mobile POS Header) -->
    <header class="sticky top-0 z-40 bg-[rgba(252,249,242,0.85)] backdrop-blur-md shadow-[0_1px_8px_rgba(0,0,0,0.04)] px-4 py-2.5 h-16 flex items-center justify-between border-b border-[#E5E2DB]/50">
        <div class="flex items-center gap-2">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class="h-8 max-w-[45.25px] w-auto object-contain flex-shrink-0">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-[#7A5900] tracking-[1px] uppercase leading-3">SITU AWI RUNNER</span>
                <h1 class="font-bold text-base text-[#1C1C18] tracking-[-0.4px] leading-5">Shift</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- LIVE Pill -->
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#F0EEE7] shadow-[0_1px_4px_rgba(43,42,38,0.04)]">
                <span class="w-2 h-2 rounded-full bg-[#7A5900]"></span>
                <span class="text-[10px] font-bold text-[#404941] tracking-[-0.25px]">LIVE</span>
            </div>

            <!-- Avatar -->
            <div class="w-8 h-8 rounded-full bg-[#0B4D2B] text-[#B1F1C2] font-bold text-[10px] flex items-center justify-center shadow-[0_1px_4px_rgba(0,0,0,0.08)]">
                {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="px-4 py-3 space-y-4 flex-1">

        <!-- Section 1: Waiter Profile & Operational Node Card -->
        <section class="bg-white rounded-xl p-4 shadow-[0_2px_12px_rgba(43,42,38,0.06)] relative overflow-hidden flex flex-col gap-2 border border-[#F0EEE7]">
            <!-- Ambient Gradient Blur Decorator -->
            <div class="absolute -right-10 -top-10 w-36 h-36 bg-gradient-to-br from-[#FECE66]/20 to-transparent rounded-full blur-xl pointer-events-none"></div>

            <!-- Profile Info Row -->
            <div class="flex items-center gap-4 relative z-10">
                <!-- Avatar with Badge -->
                <div class="relative flex-shrink-0">
                    <div class="w-16 h-16 rounded-xl bg-[#0B4D2B] text-[#B1F1C2] font-bold text-[20px] tracking-[1px] flex items-center justify-center shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-2px_rgba(0,0,0,0.1)]">
                        {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'P', 0, 2)) }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-[#0B4D2B] text-[#FECE66] shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-white flex items-center justify-center">
                        <svg class="w-3 h-3 text-[#FECE66]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <!-- Column Text -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-[#7A5900] tracking-[0.5px] uppercase">ID: {{ auth()->user()->id ?? 'STF' }}</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[rgba(177,241,194,0.4)] text-[#11512F] text-[10px] font-semibold tracking-[0.4px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#00341A]"></span>
                            Aktif Bertugas
                        </span>
                    </div>
                    <h2 class="font-bold text-[20px] text-[#1C1C18] leading-7 truncate mt-0.5" x-text="runnerFullName">{{ auth()->user()->nama ?? auth()->user()->name ?? 'Pelayan' }}</h2>
                    <span class="text-xs text-[#404941] font-normal leading-4 block truncate mt-0.5">Pelayan / Runner {{ auth()->user()->area ?? 'Lesehan Utama' }}</span>
                </div>
            </div>

            <!-- Duty Box -->
            <div class="bg-[#F6F3EC] rounded-lg p-2 flex flex-col gap-2 relative z-10 border border-[#E5E2DB]/60">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-[rgba(122,89,0,0.1)] text-[#7A5900] flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3 text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.5px] uppercase leading-3">WILAYAH TUGAS</span>
                        <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">{{ auth()->user()->area ?? 'Area Bawah (Situ Danau)' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1 border-t border-[rgba(235,232,225,0.6)]">
                    <div class="flex items-center gap-1.5 text-[#1C1C18]">
                        <svg class="w-3.5 h-3.5 text-[#404941]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span class="text-xs text-[#1C1C18] font-normal">Shift Siang (12:00 - 18:00 WIB)</span>
                    </div>
                    @php
                        $shiftEnd = \Carbon\Carbon::today()->setTime(18, 0, 0);
                        $now = \Carbon\Carbon::now();
                        $sisaMinutes = max(0, $now->diffInMinutes($shiftEnd, false));
                        $sisaJam = intdiv($sisaMinutes, 60);
                        $sisaMnt = $sisaMinutes % 60;
                        $sisaLabel = $sisaMinutes > 0
                            ? ($sisaJam > 0 ? "Sisa {$sisaJam} Jam {$sisaMnt} Mnt" : "Sisa {$sisaMnt} Mnt")
                            : 'Shift Selesai';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-[rgba(255,222,161,0.5)] text-[#7A5900] text-[10px] font-bold tracking-[0.4px]">
                        {{ $sisaLabel }}
                    </span>
                </div>
            </div>

            <!-- Hardware Node Strip -->
            <div class="bg-[#F0EEE7] rounded-lg px-2 py-1.5 flex items-center justify-between text-xs relative z-10">
                <div class="flex items-center gap-2">
                    <svg class="w-3 h-3 text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-[10px] font-medium text-[#1C1C18] tracking-[0.4px]">POS #02 • MQTT Mesh Synced</span>
                </div>
                <div class="flex items-center gap-1">
                    <svg class="w-2.5 h-2.5 text-[#00341A]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1 8 8 0 0114 0 1 1 0 11-1.732 1A6 6 0 003.732 12 1 1 0 012 11zm3.5 3a1 1 0 011-1 4 4 0 017 0 1 1 0 11-1.732 1 2 2 0 00-3.536 0 1 1 0 01-1.732-1zM9 17a1 1 0 112 0 1 1 0 01-2 0z" />
                    </svg>
                    <span class="text-[10px] font-bold text-[#00341A] tracking-[0.4px]">-52 dBm</span>
                </div>
            </div>
        </section>

        <!-- Section 2: Shift Performance Indicators (KPI 2x2 Grid) -->
        <section class="space-y-1.5 pt-1">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-base text-[#1C1C18] leading-6">Kinerja Shift Hari Ini</h3>
                <div class="flex items-center gap-1 text-[10px] font-bold text-[#7A5900] tracking-[0.4px]">
                    <svg class="w-3 h-2 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Real-time</span>
                </div>
            </div>

            <!-- 2x2 Grid of 110px Cards -->
            <div class="grid grid-cols-2 gap-3">
                <!-- KPI 1: Pesanan Diantar -->
                <div class="bg-white rounded-xl p-2.5 shadow-[0_2px_8px_rgba(43,42,38,0.04)] border border-[#F0EEE7] h-[110px] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[rgba(11,77,43,0.1)] flex items-center justify-center">
                            <svg class="w-[15px] h-[13.5px] text-[#0B4D2B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-[rgba(177,241,194,0.5)] text-[#11512F] text-[10px] font-bold tracking-[0.4px]">
                            +6 Avg
                        </span>
                    </div>
                    <div>
                        <span class="font-bold text-[26px] text-[#1C1C18] leading-none tracking-[-0.26px] block" x-text="pesananDiantar">24</span>
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] block mt-1">Pesanan Diantar</span>
                    </div>
                </div>

                <!-- KPI 2: Respon Sensor Meja -->
                <div class="bg-white rounded-xl p-2.5 shadow-[0_2px_8px_rgba(43,42,38,0.04)] border border-[#F0EEE7] h-[110px] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[rgba(122,89,0,0.1)] flex items-center justify-center">
                            <svg class="w-[13.5px] h-[15.75px] text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-[#FFDEA1] text-[#261900] text-[10px] font-bold tracking-[0.4px]">
                            Sangat Baik
                        </span>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1 leading-none">
                            <span class="font-bold text-[26px] text-[#00341A] tracking-[-0.26px]" x-text="avgRespon">1.8</span>
                            <span class="text-xs text-[#404941] tracking-[-0.26px]">Menit</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] block mt-1">Respon Sensor Meja</span>
                    </div>
                </div>

                <!-- KPI 3: Panggilan Selesai -->
                <div class="bg-white rounded-xl p-2.5 shadow-[0_2px_8px_rgba(43,42,38,0.04)] border border-[#F0EEE7] h-[110px] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[#F0EEE7] flex items-center justify-center">
                            <svg class="w-[15px] h-[15px] text-[#404941]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#00341A] tracking-[0.4px]">100% Zero-Loss</span>
                    </div>
                    <div>
                        <span class="font-bold text-[26px] text-[#1C1C18] leading-none tracking-[-0.26px] block" x-text="panggilanSelesai">14</span>
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] block mt-1">Panggilan Selesai</span>
                    </div>
                </div>

                <!-- KPI 4: Kepuasan Tamu Saung -->
                <div class="bg-white rounded-xl p-2.5 shadow-[0_2px_8px_rgba(43,42,38,0.04)] border border-[#F0EEE7] h-[110px] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-lg bg-[rgba(254,206,102,0.3)] flex items-center justify-center">
                            <svg class="w-[15px] h-[14.25px] text-[#7A5900]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px]">e-Menu Tamu</span>
                    </div>
                    <div>
                        <div class="flex items-baseline gap-1 leading-none">
                            <span class="font-bold text-[26px] text-[#7A5900] tracking-[-0.26px]">—</span>
                        </div>
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px] block mt-1">Kepuasan Tamu Saung</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Handover Checklist Section -->
        <section class="bg-white rounded-xl p-4 shadow-[0_2px_12px_rgba(43,42,38,0.05)] border border-[#F0EEE7] space-y-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-[16.67px] h-[12.56px] text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18] leading-6">Checklist Serah Terima Shift</h3>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#FECE66] text-[#765600] text-[10px] font-bold tracking-[0.4px]"
                      x-text="completedChecklistCount + '/4 Selesai'">
                    2/4 Selesai
                </span>
            </div>

            <p class="text-xs text-[#404941] font-normal leading-4">
                Lengkapi verifikasi operasional sebelum menekan tombol ganti shift sore.
            </p>

            <div class="space-y-2 pt-1">
                <!-- Item 1: Checked -->
                <label class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="checkCleanliness" class="w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB] mt-0.5">
                    <div class="flex flex-col">
                        <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Cek kebersihan 5 Saung Tepi Danau</span>
                        <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Area Saung LB-01 hingga LB-05 bersih dari sampah & piring kotor.</span>
                    </div>
                </label>

                <!-- Item 2: Checked -->
                <label class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="checkSensors" class="w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#E5E2DB] mt-0.5">
                    <div class="flex flex-col">
                        <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Pastikan sensor sentuh & ESP32 normal</span>
                        <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">LED indikator panggil tamu hijau/standby tanpa alert error.</span>
                    </div>
                </label>

                <!-- Item 3: Pending -->
                <label class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="checkHandover" class="w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#767676] mt-0.5">
                    <div class="flex flex-col">
                        <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Serah terima pending pesanan ke Kang Dadan</span>
                        <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Konfirmasi tiket meja yang masih menunggu hidangan dapur.</span>
                    </div>
                </label>

                <!-- Item 4: Pending -->
                <label class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-start gap-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="checkDock" class="w-5 h-5 rounded text-[#0B4D2B] focus:ring-[#0B4D2B] border-[#767676] mt-0.5">
                    <div class="flex flex-col">
                        <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Handover Mobile POS & Charger Dock</span>
                        <span class="text-xs text-[#404941] font-normal leading-4 mt-0.5">Fisik unit bersih dan baterai terisi di atas 75%.</span>
                    </div>
                </label>
            </div>
        </section>

        <!-- Section 4: Log Aktivitas Terakhir -->
        <section class="bg-white rounded-xl p-4 shadow-[0_2px_12px_rgba(43,42,38,0.05)] border border-[#F0EEE7] space-y-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-[15px] h-[15px] text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <h3 class="font-bold text-base text-[#1C1C18] leading-6">Log Aktivitas Terakhir</h3>
                </div>
                <button @click="triggerToast('Menampilkan seluruh riwayat log aktivitas shift...')" type="button" 
                        class="text-[10px] font-bold text-[#7A5900] tracking-[0.4px] hover:underline cursor-pointer">
                    Lihat Semua
                </button>
            </div>

            <!-- Timeline items: data log aktivitas dari controller jika tersedia -->
            <div class="relative pl-5 space-y-3 pt-1">
                <div class="absolute left-1.5 top-2 bottom-2 w-0.5 bg-[#EBE8E1]"></div>

                {{-- Tampilkan pesan jika tidak ada log aktivitas hari ini --}}
                @if(isset($logAktivitas) && $logAktivitas->count() > 0)
                    @foreach($logAktivitas as $log)
                    <div class="relative bg-[#F6F3EC] rounded-lg p-2.5 flex flex-col gap-1">
                        <span class="absolute -left-[23px] top-2.5 w-2.5 h-2.5 rounded-full bg-[#0B4D2B] shadow-[0_0_0_4px_#FFFFFF]"></span>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[10px] text-[#00341A] tracking-[0.4px]">{{ $log->waktu_selesai ? $log->waktu_selesai->format('H:i') . ' WIB • Selesai' : $log->waktu_panggil->format('H:i') . ' WIB' }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-[#F0EEE7] text-[#404941] text-[10px] font-bold tracking-[0.4px]">
                                Sensor Meja
                            </span>
                        </div>
                        <span class="font-medium text-xs text-[#1C1C18] leading-4">Panggilan {{ $log->meja->kode_meja ?? '-' }}</span>
                        <span class="text-[10px] font-bold text-[#404941] tracking-[0.4px]">Status: {{ $log->status_panggilan }}</span>
                    </div>
                    @endforeach
                @else
                <div class="relative flex flex-col items-center justify-center py-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-[#F0EEE7] flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-[#7A5900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <p class="font-bold text-xs text-[#1C1C18]">Belum Ada Aktivitas Hari Ini</p>
                    <p class="text-[10px] text-[#404941] font-normal mt-0.5">Log aktivitas akan muncul setelah ada panggilan atau pesanan.</p>
                </div>
                @endif
            </div>
        </section>

        <!-- Section 5: Hardware & Notification Settings Drawer-like Card -->
        <section class="bg-white rounded-xl p-4 shadow-[0_2px_12px_rgba(43,42,38,0.05)] border border-[#F0EEE7] space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-[15px] h-[15px] text-[#00341A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
                <h3 class="font-bold text-base text-[#1C1C18] leading-6">Pengaturan Suara & Notifikasi Getar</h3>
            </div>

            <div class="space-y-3 pt-1">
                <!-- Switch 1: Bunyi Panggilan Meja -->
                <div class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-[15px] h-[14.58px] text-[#00341A] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex flex-col">
                            <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Bunyi Panggilan Meja</span>
                            <span class="text-xs text-[#404941] font-normal leading-4">Volume maksimal saat bel sensor ditekan</span>
                        </div>
                    </div>
                    <button @click="soundAlert = !soundAlert; triggerToast(soundAlert ? 'Bunyi panggilan aktif.' : 'Bunyi panggilan senyap.')" 
                            type="button" 
                            :class="soundAlert ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" 
                            class="relative inline-flex h-6 w-12 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                        <span :class="soundAlert ? 'translate-x-6' : 'translate-x-0.5'" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-2px_rgba(0,0,0,0.1)] transition duration-200 ease-in-out mt-0.5"></span>
                    </button>
                </div>

                <!-- Switch 2: Notifikasi KDS Dapur Siap -->
                <div class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-[15px] text-[#7A5900] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                        </svg>
                        <div class="flex flex-col">
                            <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Notifikasi KDS Dapur Siap</span>
                            <span class="text-xs text-[#404941] font-normal leading-4">Getar berulang saat pesanan siap di pick-up</span>
                        </div>
                    </div>
                    <button @click="vibrateAlert = !vibrateAlert; triggerToast(vibrateAlert ? 'Getar KDS aktif.' : 'Getar KDS nonaktif.')" 
                            type="button" 
                            :class="vibrateAlert ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" 
                            class="relative inline-flex h-6 w-12 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                        <span :class="vibrateAlert ? 'translate-x-6' : 'translate-x-0.5'" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-2px_rgba(0,0,0,0.1)] transition duration-200 ease-in-out mt-0.5"></span>
                    </button>
                </div>

                <!-- Switch 3: Mode Hemat Luar Ruang -->
                <div class="bg-[#F6F3EC] rounded-lg p-2.5 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-[11.67px] h-[16.67px] text-[#404941] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                            <line x1="12" y1="18" x2="12.01" y2="18" />
                        </svg>
                        <div class="flex flex-col">
                            <span class="font-semibold text-sm text-[#1C1C18] leading-5 tracking-[0.14px]">Mode Hemat Luar Ruang</span>
                            <span class="text-xs text-[#404941] font-normal leading-4">Redupkan layar di area saung terbuka</span>
                        </div>
                    </div>
                    <button @click="powerSave = !powerSave; triggerToast(powerSave ? 'Mode hemat baterai aktif.' : 'Mode normal aktif.')" 
                            type="button" 
                            :class="powerSave ? 'bg-[#0B4D2B]' : 'bg-[#E5E2DB]'" 
                            class="relative inline-flex h-6 w-12 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                        <span :class="powerSave ? 'translate-x-6' : 'translate-x-0.5'" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-[0_4px_6px_-1px_rgba(0,0,0,0.1),0_2px_4px_-2px_rgba(0,0,0,0.1)] transition duration-200 ease-in-out mt-0.5"></span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Section 6: Shift Handover Action Tray -->
        <section class="space-y-2 pt-2 pb-2">
            <!-- Button 1: Mulai Serah Terima Shift Sore -->
            <button @click="startHandover()" type="button" 
                    class="w-full h-14 rounded-xl bg-[#0B4D2B] hover:bg-[#00341A] text-[#FCF9F2] font-bold text-sm tracking-[0.14px] shadow-[0_4px_16px_rgba(11,77,43,0.25)] flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                <svg class="w-[18.33px] h-[16.5px] text-[#FECE66]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Mulai Serah Terima Shift Sore</span>
            </button>

            <!-- Button 2: Keluar / Ganti Akun PIN (Laravel Auth Logout) -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full h-12 rounded-xl bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#1C1C18] font-medium text-sm tracking-[0.14px] flex items-center justify-center gap-2 transition-all active:scale-98 cursor-pointer">
                    <svg class="w-[15px] h-[15px] text-[#BA1A1A]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar / Ganti Akun PIN</span>
                </button>
            </form>
        </section>

    </div>

    <!-- Modal Handover Confirmation -->
    <div x-show="showHandoverModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="showHandoverModal = false" class="bg-white rounded-2xl p-5 max-w-sm w-full space-y-4 shadow-2xl border border-[#E5E2DB]">
            <div class="flex items-center justify-between pb-2 border-b border-[#F0EEE7]">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00341A]"></span>
                    <h3 class="font-bold text-base text-[#1C1C18]">Konfirmasi Serah Terima Shift</h3>
                </div>
                <button @click="showHandoverModal = false" class="text-[#707971] hover:text-[#1C1C18] text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <p class="text-xs text-[#404941] leading-relaxed">
                Anda akan menyerahkan saung aktif ke Runner Shift berikutnya. Total <span x-text="pesananDiantar">0</span> panggilan telah ditangani hari ini.
            </p>
            <div class="space-y-2">
                <button @click="confirmHandover()" type="button" class="w-full py-3 bg-[#0B4D2B] hover:bg-[#00341A] text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-98 cursor-pointer">
                    Konfirmasi Serah Terima
                </button>
                <button @click="showHandoverModal = false" type="button" class="w-full py-2.5 bg-[#F0EEE7] hover:bg-[#E5E2DB] text-[#404941] font-semibold text-xs rounded-xl cursor-pointer">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
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
    function pelayanTabShift() {
        return {
            // Nama dari auth user — tidak hardcode
            runnerFullName: '{{ auth()->user()->nama ?? auth()->user()->name ?? "Pelayan" }}',

            // KPI: default 0, akan di-overwrite oleh init() dengan data backend
            pesananDiantar: 0,
            avgRespon: 0,
            panggilanSelesai: 0,

            checkCleanliness: false,
            checkSensors: false,
            checkHandover: false,
            checkDock: false,

            soundAlert: true,
            vibrateAlert: true,
            powerSave: false,

            showHandoverModal: false,
            toastMessage: 'Notifikasi berhasil diproses.',
            showToast: false,

            get completedChecklistCount() {
                return (this.checkCleanliness ? 1 : 0) + (this.checkSensors ? 1 : 0) + (this.checkHandover ? 1 : 0) + (this.checkDock ? 1 : 0);
            },

            init() {
                // Selalu assign dari backend — tanpa kondisi > 0 agar nilai 0 setelah fresh seed juga terbaca
                this.pesananDiantar   = {{ $shiftAktif ?? 0 }};
                this.panggilanSelesai = {{ $panggilanDitangani ?? 0 }};
                // avgRespon tidak tersedia di controller saat ini → tampilkan 0
                this.avgRespon        = 0;
            },

            triggerToast(msg) {
                this.toastMessage = msg;
                this.showToast = true;
                if (typeof playChime === 'function') {
                    playChime('success');
                }
                setTimeout(() => {
                    this.showToast = false;
                }, 3500);
            },

            startHandover() {
                this.showHandoverModal = true;
            },

            confirmHandover() {
                this.showHandoverModal = false;
                this.triggerToast('Serah terima shift berhasil dicatat.');
            }
        };
    }
</script>
@endsection