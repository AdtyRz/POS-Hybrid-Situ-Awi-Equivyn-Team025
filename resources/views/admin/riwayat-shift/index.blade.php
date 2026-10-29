@extends('layouts.admin')

@section('title', 'Riwayat Shift Staf')
@section('page_category', 'Operasional & Staf')

@section('content')
<div class="flex flex-col gap-6 pb-16">

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI SAUNG LESEHAN</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">Riwayat Shift</span>
            </div>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $pembatasanAktif ? 'bg-[#B1F1C2] text-[#00341A]' : 'bg-[#E5E2DB] text-[#404941]' }} text-[10px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full {{ $pembatasanAktif ? 'bg-[#00341A]' : 'bg-[#707971]' }}"></span>
                {{ $pembatasanAktif ? 'PEMBATASAN SHIFT AKTIF' : 'PEMBATASAN SHIFT NONAKTIF' }}
            </span>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="font-display font-bold text-3xl text-[#1C1C18]">Riwayat Shift Staf</h1>
                <p class="text-sm text-[#707971] mt-1">Durasi dihitung dari login sampai logout untuk pelayan, dapur, bar, dan kasir.</p>
            </div>

            <a href="{{ route('admin.pengaturan-sistem') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] px-4 py-2.5 text-xs font-bold text-white transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                Atur Jadwal Shift
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($ringkasanRole as $data)
            <div class="rounded-2xl border border-[#C0C9BF]/30 bg-white p-5 shadow-xs">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#707971]">{{ $data['label'] }}</p>
                        <p class="mt-2 font-display text-3xl font-bold text-[#1C1C18]">{{ $data['jumlah'] }}</p>
                        <p class="text-[11px] text-[#707971]">shift hari ini</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $data['aktif'] > 0 ? 'bg-[#B1F1C2] text-[#00341A]' : 'bg-[#F0EEE7] text-[#707971]' }} px-2.5 py-1 text-[10px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full {{ $data['aktif'] > 0 ? 'bg-[#00341A] animate-pulse' : 'bg-[#707971]' }}"></span>
                        {{ $data['aktif'] }} aktif
                    </span>
                </div>
                <div class="mt-4 border-t border-[#C0C9BF]/20 pt-3 text-[11px] text-[#707971]">
                    Total durasi: <span class="font-bold text-[#1C1C18]">{{ \App\Services\ShiftService::durasiTeks($data['durasi']) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-[#C0C9BF]/30 bg-[#F6F3EC] p-5 sm:p-6 shadow-xs">
        <form method="GET" action="{{ route('admin.riwayat-shift') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-wider text-[#707971]">Peran</label>
                <select name="role" class="w-full rounded-xl border border-[#C0C9BF]/40 bg-white px-3 py-2.5 text-xs font-semibold text-[#1C1C18] focus:border-[#0B4D2B] focus:outline-hidden">
                    <option value="">Semua peran</option>
                    @foreach (\App\Services\ShiftService::ROLES as $kunci => $label)
                        <option value="{{ $kunci }}" @selected($role === $kunci)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-wider text-[#707971]">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full rounded-xl border border-[#C0C9BF]/40 bg-white px-3 py-2.5 text-xs font-semibold text-[#1C1C18] focus:border-[#0B4D2B] focus:outline-hidden">
            </div>

            <div class="space-y-1.5">
                <label class="text-[10px] font-bold uppercase tracking-wider text-[#707971]">Cari Nama</label>
                <input type="text" name="cari" value="{{ $cari }}" placeholder="Nama staf" class="w-full rounded-xl border border-[#C0C9BF]/40 bg-white px-3 py-2.5 text-xs font-semibold text-[#1C1C18] focus:border-[#0B4D2B] focus:outline-hidden">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#0B4D2B] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#08381F] active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('admin.riwayat-shift') }}" class="rounded-xl border border-[#C0C9BF]/50 bg-white px-3 py-2.5 text-xs font-bold text-[#404941] transition hover:bg-[#F0EEE7]">Reset</a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-[#C0C9BF]/30 bg-white shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#C0C9BF]/20 px-5 py-4">
            <div>
                <h2 class="text-sm font-bold text-[#1C1C18]">Log Login &amp; Logout</h2>
                <p class="text-[11px] text-[#707971]">Sesi aktif yang tidak logout akan ditutup otomatis setelah batas sesi.</p>
            </div>
            <span class="text-[11px] font-bold text-[#707971]">{{ $riwayat->total() }} catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-[#F6F3EC] text-[10px] font-bold uppercase tracking-wider text-[#707971]">
                    <tr>
                        <th class="px-5 py-3">Staf</th>
                        <th class="px-4 py-3">Peran</th>
                        <th class="px-4 py-3">Shift</th>
                        <th class="px-4 py-3">Login</th>
                        <th class="px-4 py-3">Logout</th>
                        <th class="px-4 py-3">Durasi</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#C0C9BF]/20">
                    @forelse ($riwayat as $baris)
                        @php
                            $aktif = $baris->status === 'aktif' && $baris->selesai_pada === null;
                            $durasi = $aktif
                                ? \App\Services\ShiftService::durasiTeks((int) $baris->mulai_pada->diffInSeconds(now()))
                                : $baris->durasiTeks();
                        @endphp
                        <tr class="align-top hover:bg-[#FCF9F2]">
                            <td class="px-5 py-4">
                                <p class="font-bold text-[#1C1C18]">{{ $baris->user?->nama ?? 'Staf' }}</p>
                                <p class="mt-0.5 text-[10px] text-[#707971]">{{ $baris->ip_address ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-[#F0EEE7] px-2.5 py-1 text-[10px] font-bold text-[#404941]">
                                    {{ \App\Services\ShiftService::ROLES[$baris->role] ?? ucfirst($baris->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-[#1C1C18]">{{ $baris->nama_shift ?: 'Luar Shift' }}</p>
                                @if ($baris->jadwal_mulai && $baris->jadwal_selesai)
                                    <p class="mt-0.5 text-[10px] text-[#707971]">{{ substr($baris->jadwal_mulai, 0, 5) }} - {{ substr($baris->jadwal_selesai, 0, 5) }} WIB</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-[#404941]">
                                {{ $baris->mulai_pada->timezone('Asia/Jakarta')->format('d M Y') }}
                                <span class="block text-[10px] font-bold text-[#1C1C18]">{{ $baris->mulai_pada->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="px-4 py-4 text-[#404941]">
                                @if ($baris->selesai_pada)
                                    {{ $baris->selesai_pada->timezone('Asia/Jakarta')->format('d M Y') }}
                                    <span class="block text-[10px] font-bold text-[#1C1C18]">{{ $baris->selesai_pada->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
                                @else
                                    <span class="text-[10px] font-bold text-[#7A5900]">Belum logout</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 font-semibold text-[#1C1C18]">{{ $durasi }}</td>
                            <td class="px-5 py-4">
                                @if ($aktif)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#B1F1C2] px-2.5 py-1 text-[10px] font-bold text-[#00341A]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-[#00341A] animate-pulse"></span>
                                        Aktif
                                    </span>
                                @elseif ($baris->status === 'kedaluwarsa')
                                    <span class="inline-flex rounded-full bg-[#FFDEA1] px-2.5 py-1 text-[10px] font-bold text-[#7A5900]">Sesi berakhir</span>
                                @else
                                    <span class="inline-flex rounded-full bg-[#F0EEE7] px-2.5 py-1 text-[10px] font-bold text-[#404941]">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-bold text-[#1C1C18]">Belum ada riwayat shift</p>
                                <p class="mt-1 text-[11px] text-[#707971]">Riwayat akan muncul setelah staf login.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($riwayat->hasPages())
            <div class="border-t border-[#C0C9BF]/20 px-5 py-4">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @foreach ($shiftSlots as $shift)
            <div class="rounded-2xl border border-[#C0C9BF]/30 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-bold text-[#1C1C18]">{{ $shift['nama'] }}</p>
                    <span class="inline-flex rounded-full {{ $shift['aktif'] ? 'bg-[#B1F1C2] text-[#00341A]' : 'bg-[#F0EEE7] text-[#707971]' }} px-2.5 py-1 text-[10px] font-bold">
                        {{ $shift['aktif'] ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <p class="mt-2 font-mono text-lg font-bold text-[#0B4D2B]">{{ $shift['mulai'] }} - {{ $shift['selesai'] }}</p>
                <p class="mt-1 text-[10px] text-[#707971]">Waktu Indonesia Barat</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
