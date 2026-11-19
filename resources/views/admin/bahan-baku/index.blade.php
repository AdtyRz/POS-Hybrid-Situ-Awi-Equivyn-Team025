@extends('layouts.admin')

@section('title', 'Bahan Baku, Resep & HPP')
@section('page_category', 'Arus Kas & Bahan Baku')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="bahanBakuApp()">

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">BAHAN BAKU & RESEP</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Bahan Baku, Resep & HPP
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Kelola stok bahan baku, susun resep per menu, dan pantau HPP standar. Stok bahan otomatis berkurang saat penjualan dan kembali saat pesanan dibatalkan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="bukaTambahBahan()"
                        class="h-10 px-4 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white font-semibold text-xs flex items-center gap-2 shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Tambah Bahan
                </button>
                <a href="{{ route('admin.arus-kas') }}"
                   class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] font-semibold text-xs flex items-center gap-2 shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    Arus Kas
                </a>
            </div>
        </div>
    </div>

    @if (session('sukses'))
        <div class="p-4 rounded-2xl bg-[#B1F1C2]/50 border border-[#0B4D2B]/15 text-xs font-semibold text-[#0B4D2B] flex items-center gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-[#FFDAD6]/60 border border-[#BA1A1A]/20 text-xs font-semibold text-[#93000A] flex items-center gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-[#FFDAD6]/60 border border-[#BA1A1A]/20 text-xs font-semibold text-[#93000A] flex items-start gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
            <ul class="flex flex-col gap-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">TOTAL BAHAN BAKU</span>
            <span class="font-display font-bold text-3xl text-[#0B4D2B] tracking-tight leading-tight">{{ number_format($ringkasan['total_bahan'], 0, ',', '.') }}</span>
            <span class="text-[10px] font-semibold text-[#707971]">Bahan aktif dan nonaktif</span>
        </div>
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">NILAI STOK BAHAN</span>
            <span class="font-display font-bold text-2xl text-[#00341A] tracking-tight leading-tight">Rp {{ number_format($ringkasan['nilai_stok'], 0, ',', '.') }}</span>
            <span class="text-[10px] font-semibold text-[#707971]">Stok × harga rata-rata pembelian</span>
        </div>
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">STOK MENIPIS</span>
            <span class="font-display font-bold text-3xl {{ $ringkasan['menipis'] > 0 ? 'text-[#93000A]' : 'text-[#0B4D2B]' }} tracking-tight leading-tight">{{ number_format($ringkasan['menipis'], 0, ',', '.') }}</span>
            <span class="text-[10px] font-semibold text-[#707971]">Stok di bawah atau sama dengan minimum</span>
        </div>
        <div class="bg-[#F6F3EC] rounded-2xl p-6 shadow-xs border border-[#C0C9BF]/20 flex flex-col justify-between min-h-[160px]">
            <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">MENU BERESEP</span>
            <span class="font-display font-bold text-3xl text-[#7A5900] tracking-tight leading-tight">{{ number_format($ringkasan['menu_beresep'], 0, ',', '.') }}</span>
            <span class="text-[10px] font-semibold text-[#707971]">Menu dengan resep untuk hitung HPP</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs overflow-hidden">
        <div class="p-5 flex items-center justify-between gap-3 border-b border-gray-100">
            <div>
                <h2 class="font-bold text-base text-[#1C1C18]">Stok Bahan Baku</h2>
                <p class="text-[11px] text-[#707971] mt-0.5">Harga rata-rata dihitung dari seluruh rincian pembelian bahan.</p>
            </div>
            <button type="button" @click="bukaTambahBahan()"
                    class="h-9 px-3.5 rounded-xl bg-[#B1F1C2] hover:bg-[#9fe4b2] active:scale-95 text-[#0B4D2B] text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Tambah
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F6F3EC] text-[10px] uppercase tracking-wider text-[#707971]">
                    <tr>
                        <th class="px-4 py-3 font-bold">Bahan</th>
                        <th class="px-4 py-3 font-bold text-right">Stok</th>
                        <th class="px-4 py-3 font-bold text-right">Harga Rata-rata</th>
                        <th class="px-4 py-3 font-bold text-right">Nilai Stok</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bahan as $baris)
                        <tr class="hover:bg-[#F6F3EC]/40 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-[#1C1C18]">{{ $baris->nama }}</div>
                                <div class="text-[10px] text-[#707971] mt-0.5">
                                    Satuan {{ $baris->satuan }} · minimum {{ rtrim(rtrim(number_format((float) $baris->stok_minimum, 3, ',', '.'), '0'), ',') }} {{ $baris->satuan }}
                                </div>
                                @if (! $baris->is_aktif)
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full bg-[#E5E2DB] text-[9px] font-bold text-[#707971] uppercase tracking-wide">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold {{ $baris->menipis() ? 'text-[#93000A]' : 'text-[#0B4D2B]' }}">
                                    {{ rtrim(rtrim(number_format((float) $baris->stok, 3, ',', '.'), '0'), ',') }}
                                </span>
                                <span class="text-[10px] text-[#707971]">{{ $baris->satuan }}</span>
                                @if ($baris->menipis())
                                    <div class="text-[9px] font-bold text-[#93000A] uppercase">Menipis</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-[#7A5900] font-semibold">Rp {{ number_format((float) $baris->harga_rata_rata, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-[#00341A]">Rp {{ number_format($baris->nilaiStok(), 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" @click="bukaStok({{ $baris->id }})" title="Sesuaikan stok"
                                            class="h-8 px-2 rounded-lg bg-white border border-gray-200 text-[#0B4D2B] hover:bg-[#B1F1C2]/40 flex items-center justify-center text-[10px] font-bold transition-colors cursor-pointer">
                                        Stok
                                    </button>
                                    <button type="button" @click="bukaRiwayat({{ $baris->id }})" title="Riwayat stok"
                                            class="h-8 px-2 rounded-lg bg-white border border-gray-200 text-[#404941] hover:bg-[#F0EEE7] flex items-center justify-center text-[10px] font-bold transition-colors cursor-pointer">
                                        Riwayat
                                    </button>
                                    <button type="button" @click="bukaUbahBahan({{ $baris->id }})" title="Ubah"
                                            class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#404941] hover:bg-[#F0EEE7] flex items-center justify-center transition-colors cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.bahan-baku.hapus', $baris) }}" onsubmit="return confirm('Hapus bahan {{ $baris->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                                class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-[#93000A] hover:bg-[#FFDAD6]/60 flex items-center justify-center transition-colors cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-[#707971]">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-9 h-9 text-[#C0C9BF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    <span class="text-xs font-semibold">Belum ada bahan baku. Tambahkan bahan pertama untuk mulai menghitung HPP.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-base text-[#1C1C18]">Resep Menu & HPP Standar</h2>
                <p class="text-[11px] text-[#707971] mt-0.5">Resep menentukan pemakaian bahan saat menu terjual dan HPP per porsi.</p>
            </div>
            <span class="text-[10px] font-bold text-[#7A5900] bg-[#FFDEA1]/60 px-3 py-1.5 rounded-lg">{{ $ringkasan['menu_beresep'] }} menu beresep</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#F6F3EC] text-[10px] uppercase tracking-wider text-[#707971]">
                    <tr>
                        <th class="px-4 py-3 font-bold">Menu</th>
                        <th class="px-4 py-3 font-bold text-center">Bahan Resep</th>
                        <th class="px-4 py-3 font-bold text-right">HPP / Porsi</th>
                        <th class="px-4 py-3 font-bold text-right">Harga Jual</th>
                        <th class="px-4 py-3 font-bold text-right">Margin</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($menu as $baris)
                        @php
                            $hpp = $baris->hppSatuan();
                            $margin = (float) $baris->harga > 0 ? (((float) $baris->harga - $hpp) / (float) $baris->harga) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-[#F6F3EC]/40 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-[#1C1C18]">{{ $baris->nama_menu }}</div>
                                <div class="text-[10px] text-[#707971] mt-0.5">{{ $baris->kategori?->nama_kategori ?? 'Tanpa kategori' }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($baris->resep->isEmpty())
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-[#FFDAD6]/70 text-[9px] font-bold text-[#93000A] uppercase tracking-wide">Belum diatur</span>
                                @else
                                    <span class="font-bold text-[#0B4D2B]">{{ $baris->resep->count() }}</span>
                                    <span class="text-[10px] text-[#707971]">bahan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-[#7A5900]">Rp {{ number_format($hpp, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-[#1C1C18]">Rp {{ number_format((float) $baris->harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $margin < 0 ? 'text-[#93000A]' : 'text-[#00341A]' }}">{{ number_format($margin, 1, ',', '.') }}%</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    <button type="button" @click="bukaResep({{ $baris->id }})"
                                            class="h-8 px-3 rounded-lg bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-[10px] font-bold transition-colors cursor-pointer">
                                        Atur Resep
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-xs font-semibold text-[#707971]">Belum ada menu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="modalBahan" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="modalBahan = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="bahanForm.id ? 'Ubah Bahan Baku' : 'Tambah Bahan Baku'"></h3>
                    <p class="text-[11px] text-[#707971] mt-0.5">Master bahan untuk pembelian, resep, dan stok.</p>
                </div>
                <button type="button" @click="modalBahan = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="bahanForm.id ? bahanUbahUrl.replace('__ID__', bahanForm.id) : bahanSimpanUrl" class="p-5 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-bind:disabled="!bahanForm.id">
                <input type="hidden" name="is_aktif" value="0">

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Nama Bahan <span class="text-[#93000A]">*</span></label>
                    <input type="text" name="nama" x-model="bahanForm.nama" maxlength="120" required placeholder="Contoh: Beras"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Satuan <span class="text-[#93000A]">*</span></label>
                        <input type="text" name="satuan" x-model="bahanForm.satuan" maxlength="20" required placeholder="kg / liter / pcs"
                               class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Stok Minimum</label>
                        <input type="number" name="stok_minimum" x-model="bahanForm.stok_minimum" min="0" step="0.001" placeholder="0"
                               class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5" x-show="!bahanForm.id">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Stok Awal</label>
                    <input type="number" name="stok_awal" x-model="bahanForm.stok_awal" min="0" step="0.001" placeholder="0"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    <span class="text-[10px] text-[#707971]">Stok awal dicatat sebagai mutasi penyesuaian.</span>
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_aktif" value="1" x-model="bahanForm.is_aktif"
                           class="w-4 h-4 rounded border-[#C0C9BF] text-[#0B4D2B] focus:ring-[#0B4D2B]/30">
                    <span class="text-xs font-semibold text-[#404941]">Bahan aktif dipakai di resep dan pembelian</span>
                </label>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalBahan = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalStok" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="modalStok = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]">Sesuaikan Stok</h3>
                    <p class="text-[11px] text-[#707971] mt-0.5" x-text="stokForm.nama ? stokForm.nama + ' · stok saat ini ' + formatAngka(stokForm.stok_sekarang) + ' ' + stokForm.satuan : ''"></p>
                </div>
                <button type="button" @click="modalStok = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="stokUrl.replace('__ID__', stokForm.id)" class="p-5 flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Stok Baru <span class="text-[#93000A]">*</span></label>
                    <input type="number" name="stok_baru" x-model="stokForm.stok_baru" min="0" step="0.001" required
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    <span class="text-[10px] text-[#707971]">Masukkan jumlah stok fisik hasil hitung ulang.</span>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Keterangan</label>
                    <input type="text" name="keterangan" x-model="stokForm.keterangan" maxlength="255" placeholder="Contoh: Stock opname akhir bulan"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                </div>
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalStok = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan Stok</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalRiwayat" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[88vh] overflow-y-auto" @click.outside="modalRiwayat = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]">Riwayat Stok Bahan</h3>
                    <p class="text-[11px] text-[#707971] mt-0.5" x-text="riwayat.nama ? riwayat.nama + ' · stok saat ini ' + formatAngka(riwayat.stok) + ' ' + riwayat.satuan : 'Memuat...'"></p>
                </div>
                <button type="button" @click="modalRiwayat = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-5">
                <div x-show="riwayat.loading" class="py-10 text-center text-xs font-semibold text-[#707971]">Memuat riwayat stok...</div>
                <div x-show="!riwayat.loading" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#F6F3EC] text-[10px] uppercase tracking-wider text-[#707971]">
                            <tr>
                                <th class="px-3 py-2.5 font-bold">Waktu</th>
                                <th class="px-3 py-2.5 font-bold">Jenis</th>
                                <th class="px-3 py-2.5 font-bold">Keterangan</th>
                                <th class="px-3 py-2.5 font-bold">Pencatat</th>
                                <th class="px-3 py-2.5 font-bold text-right">Perubahan</th>
                                <th class="px-3 py-2.5 font-bold text-right">Stok Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="baris in riwayat.rows" :key="baris.id">
                                <tr>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-[#707971]" x-text="baris.waktu"></td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide"
                                              :class="baris.jumlah_delta >= 0 ? 'bg-[#B1F1C2]/60 text-[#0B4D2B]' : 'bg-[#FFDAD6]/70 text-[#93000A]'"
                                              x-text="baris.jenis.replaceAll('_', ' ')"></span>
                                    </td>
                                    <td class="px-3 py-2.5 text-[#404941]" x-text="baris.keterangan || '-'"></td>
                                    <td class="px-3 py-2.5 text-[#707971]" x-text="baris.pencatat"></td>
                                    <td class="px-3 py-2.5 text-right font-bold" :class="baris.jumlah_delta >= 0 ? 'text-[#0B4D2B]' : 'text-[#93000A]'" x-text="(baris.jumlah_delta >= 0 ? '+' : '') + formatAngka(baris.jumlah_delta)"></td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-[#1C1C18]" x-text="formatAngka(baris.stok_akhir)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="!riwayat.loading && riwayat.rows.length === 0" class="py-8 text-center text-xs font-semibold text-[#707971]">Belum ada mutasi stok.</div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modalResep" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[92vh] overflow-y-auto" @click.outside="modalResep = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]">Atur Resep Menu</h3>
                    <p class="text-[11px] text-[#707971] mt-0.5" x-text="resepForm.nama || 'Pilih bahan dan jumlah pemakaian per porsi.'"></p>
                </div>
                <button type="button" @click="modalResep = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="resepUrl.replace('__ID__', resepForm.menu_id)" class="p-5 flex flex-col gap-4">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between gap-3 rounded-xl bg-[#F6F3EC] border border-[#C0C9BF]/30 p-4">
                    <div>
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">HPP Standar</span>
                        <div class="font-display font-bold text-xl text-[#7A5900]" x-text="formatRupiah(hppResep)"></div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Harga Jual</span>
                        <div class="font-display font-bold text-xl text-[#00341A]" x-text="formatRupiah(resepForm.harga)"></div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Margin</span>
                        <div class="font-display font-bold text-xl" :class="marginResep < 0 ? 'text-[#93000A]' : 'text-[#0B4D2B]'" x-text="formatAngka(marginResep) + '%'"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-bold text-[#1C1C18]">Bahan Resep</h4>
                        <p class="text-[10px] text-[#707971] mt-0.5">Jumlah pemakaian untuk satu porsi menu.</p>
                    </div>
                    <button type="button" @click="tambahItemResep()"
                            class="h-8 px-3 rounded-lg bg-[#B1F1C2] hover:bg-[#9fe4b2] text-[#0B4D2B] text-[10px] font-bold flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Tambah Bahan
                    </button>
                </div>

                <div class="flex flex-col gap-2">
                    <template x-if="resepForm.items.length === 0">
                        <div class="px-3 py-5 rounded-xl bg-[#F6F3EC]/60 border border-dashed border-[#C0C9BF] text-center text-[10px] font-semibold text-[#707971]">
                            Resep masih kosong. Menu tetap bisa dijual, tetapi HPP dihitung Rp 0.
                        </div>
                    </template>

                    <template x-for="(item, index) in resepForm.items" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-start bg-[#F6F3EC]/40 border border-[#C0C9BF]/30 rounded-xl p-2.5">
                            <div class="col-span-7">
                                <select :name="`items[${index}][bahan_baku_id]`" x-model="item.bahan_baku_id" required
                                        class="w-full h-9 px-2.5 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                    <option value="">Pilih bahan</option>
                                    <template x-for="bahan in bahanData" :key="bahan.id">
                                        <option :value="bahan.id" x-text="`${bahan.nama} (${bahan.satuan})`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" :name="`items[${index}][jumlah]`" x-model="item.jumlah" min="0.001" step="0.001" required placeholder="Jumlah"
                                       class="w-full h-9 px-2 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                            </div>
                            <div class="col-span-2 flex items-center justify-end">
                                <button type="button" @click="hapusItemResep(index)"
                                        class="w-8 h-9 rounded-lg bg-white border border-gray-200 text-[#93000A] hover:bg-[#FFDAD6]/60 flex items-center justify-center cursor-pointer" title="Hapus bahan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalResep = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan Resep</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bahanBakuApp() {
    return {
        modalBahan: false,
        modalStok: false,
        modalRiwayat: false,
        modalResep: false,
        bahanData: @json($bahanJson),
        menuData: @json($menuJson),
        bahanSimpanUrl: @js(route('admin.bahan-baku.simpan')),
        bahanUbahUrl: @js(route('admin.bahan-baku.ubah', ['bahan' => '__ID__'])),
        stokUrl: @js(route('admin.bahan-baku.stok', ['bahan' => '__ID__'])),
        mutasiUrl: @js(route('admin.bahan-baku.mutasi', ['bahan' => '__ID__'])),
        resepUrl: @js(route('admin.bahan-baku.resep', ['menu' => '__ID__'])),
        bahanForm: { id: null, nama: '', satuan: 'kg', stok_minimum: '', stok_awal: '', is_aktif: true },
        stokForm: { id: null, nama: '', satuan: '', stok_sekarang: 0, stok_baru: '', keterangan: '' },
        resepForm: { menu_id: null, nama: '', harga: 0, items: [] },
        riwayat: { nama: '', satuan: '', stok: 0, rows: [], loading: false },

        bukaTambahBahan() {
            this.bahanForm = { id: null, nama: '', satuan: 'kg', stok_minimum: '', stok_awal: '', is_aktif: true };
            this.modalBahan = true;
        },

        bukaUbahBahan(id) {
            const baris = this.bahanData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.bahanForm = {
                id: baris.id,
                nama: baris.nama,
                satuan: baris.satuan,
                stok_minimum: baris.stok_minimum,
                stok_awal: '',
                is_aktif: !!baris.is_aktif,
            };
            this.modalBahan = true;
        },

        bukaStok(id) {
            const baris = this.bahanData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.stokForm = {
                id: baris.id,
                nama: baris.nama,
                satuan: baris.satuan,
                stok_sekarang: baris.stok,
                stok_baru: baris.stok,
                keterangan: '',
            };
            this.modalStok = true;
        },

        async bukaRiwayat(id) {
            const baris = this.bahanData.find((item) => Number(item.id) === Number(id));
            this.riwayat = {
                nama: baris ? baris.nama : '',
                satuan: baris ? baris.satuan : '',
                stok: baris ? baris.stok : 0,
                rows: [],
                loading: true,
            };
            this.modalRiwayat = true;

            try {
                const response = await fetch(this.mutasiUrl.replace('__ID__', id), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await response.json();
                this.riwayat = {
                    nama: data.bahan?.nama || this.riwayat.nama,
                    satuan: data.bahan?.satuan || this.riwayat.satuan,
                    stok: data.bahan?.stok ?? this.riwayat.stok,
                    rows: data.mutasi || [],
                    loading: false,
                };
            } catch (error) {
                this.riwayat.loading = false;
                this.riwayat.rows = [];
            }
        },

        bukaResep(id) {
            const baris = this.menuData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.resepForm = {
                menu_id: baris.id,
                nama: baris.nama,
                harga: baris.harga,
                items: (baris.resep || []).map((item) => ({
                    bahan_baku_id: item.bahan_baku_id,
                    jumlah: item.jumlah,
                })),
            };
            this.modalResep = true;
        },

        tambahItemResep() {
            this.resepForm.items.push({ bahan_baku_id: '', jumlah: '' });
        },

        hapusItemResep(index) {
            this.resepForm.items.splice(index, 1);
        },

        get hppResep() {
            return this.resepForm.items.reduce((total, item) => {
                const bahan = this.bahanData.find((baris) => Number(baris.id) === Number(item.bahan_baku_id));
                return total + ((parseFloat(item.jumlah) || 0) * (bahan ? Number(bahan.harga_rata_rata) || 0 : 0));
            }, 0);
        },

        get marginResep() {
            const harga = Number(this.resepForm.harga) || 0;
            return harga > 0 ? ((harga - this.hppResep) / harga) * 100 : 0;
        },

        formatAngka(nilai) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 3 }).format(Number(nilai) || 0);
        },

        formatRupiah(nilai) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(nilai) || 0);
        },
    };
}
</script>
@endpush
