@extends('layouts.admin')

@section('title', 'Arus Kas & Laporan Keuangan')
@section('page_category', 'Laporan & Ekspor')

@section('content')
<div class="flex flex-col gap-8 pb-16" x-data="arusKasApp()">

    <div class="flex flex-col gap-3">
        <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#707971]">
                <span>SITU AWI</span>
                <span>/</span>
                <span class="text-[#0B4D2B]">ARUS KAS</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pt-1">
            <div class="flex flex-col max-w-2xl">
                <h1 class="font-display font-bold text-3xl sm:text-4xl text-[#00341A] tracking-tight leading-tight">
                    Arus Kas & Laporan Keuangan
                </h1>
                <p class="text-xs sm:text-sm text-[#404941] mt-2 leading-relaxed">
                    Catat pembelian bahan dan pengeluaran operasional, lalu pantau arus kas bersih: pemasukan lunas dikurangi seluruh kas keluar pada periode terpilih.
                </p>
            </div>
        </div>
    </div>

    @if (session('sukses'))
        <div class="p-4 rounded-2xl bg-[#B1F1C2]/50 border border-[#0B4D2B]/15 text-xs font-semibold text-[#0B4D2B] flex items-center gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-[#FFDAD6]/60 border border-[#BA1A1A]/20 text-xs font-semibold text-[#93000A] flex items-start gap-2.5">
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

    <form method="GET" action="{{ route('admin.arus-kas') }}" class="bg-white rounded-2xl border border-[#C0C9BF]/30 shadow-xs p-5 flex flex-col lg:flex-row lg:items-end gap-4">
        <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal Mulai</label>
            <input type="date" name="start" value="{{ $mulaiQuery }}"
                   class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
        </div>
        <div class="flex flex-col gap-1.5">
            <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal Selesai</label>
            <input type="date" name="end" value="{{ $selesaiQuery }}"
                   class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold text-[#1C1C18] focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] active:scale-95 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                Terapkan Periode
            </button>
            <a href="{{ route('admin.arus-kas') }}" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold flex items-center transition-all">
                Bulan Ini
            </a>
        </div>
        <div class="lg:ml-auto text-[11px] font-semibold text-[#707971]">
            Periode aktif: <span class="text-[#0B4D2B]">{{ $labelPeriode }}</span>
        </div>
    </form>

    @include('admin.arus-kas.partials.laporan', ['modeLaporan' => false])

    <div x-show="modalPembelian" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[92vh] overflow-y-auto" @click.outside="modalPembelian = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="pembelianForm.id ? 'Ubah Pembelian Bahan' : 'Tambah Pembelian Bahan'"></h3>
                    <p class="text-[11px] text-[#707971] mt-0.5">Catat belanja bahan baku dapur.</p>
                </div>
                <button type="button" @click="modalPembelian = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="pembelianForm.id ? pembelianUbahUrl.replace('__ID__', pembelianForm.id) : pembelianSimpanUrl" class="p-5 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-bind:disabled="!pembelianForm.id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal <span class="text-[#93000A]">*</span></label>
                        <input type="date" name="tanggal" x-model="pembelianForm.tanggal" required
                               class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Supplier</label>
                        <input type="text" name="supplier" x-model="pembelianForm.supplier" maxlength="100" placeholder="Contoh: Pasar Induk"
                               class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Keterangan</label>
                    <textarea name="keterangan" x-model="pembelianForm.keterangan" rows="2" maxlength="255" placeholder="Contoh: Beras, sayur, dan bumbu dapur"
                              class="px-3 py-2 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30"></textarea>
                </div>

                <div class="flex flex-col gap-2 border border-[#C0C9BF]/30 rounded-2xl p-4 bg-[#F6F3EC]/40">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h4 class="text-xs font-bold text-[#1C1C18]">Rincian Bahan</h4>
                            <p class="text-[10px] text-[#707971] mt-0.5">Isi bahan yang dibeli agar stok dan HPP ikut diperbarui.</p>
                        </div>
                        <button type="button" @click="tambahItemPembelian()"
                                class="h-8 px-3 rounded-lg bg-[#B1F1C2] hover:bg-[#9fe4b2] text-[#0B4D2B] text-[10px] font-bold flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Tambah Bahan
                        </button>
                    </div>

                    <template x-if="pembelianForm.items.length === 0">
                        <div class="px-3 py-4 rounded-xl bg-white/70 border border-dashed border-[#C0C9BF] text-center text-[10px] font-semibold text-[#707971]">
                            Belum ada rincian bahan. Pembelian tetap bisa disimpan sebagai catatan total saja.
                        </div>
                    </template>

                    <div class="flex flex-col gap-2">
                        <template x-for="(item, index) in pembelianForm.items" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-start bg-white/80 border border-[#C0C9BF]/30 rounded-xl p-2.5">
                                <div class="col-span-12 sm:col-span-5">
                                    <select :name="`items[${index}][bahan_baku_id]`" x-model="item.bahan_baku_id" @change="pilihBahanPembelian(index)" required
                                            class="w-full h-9 px-2.5 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                        <option value="">Pilih bahan</option>
                                        <template x-for="bahan in bahanData" :key="bahan.id">
                                            <option :value="bahan.id" x-text="`${bahan.nama} (${bahan.satuan})`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-4 sm:col-span-2">
                                    <input type="number" :name="`items[${index}][jumlah]`" x-model="item.jumlah" @input="hitungTotalPembelian()" min="0.001" step="0.001" required placeholder="Qty"
                                           class="w-full h-9 px-2 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                </div>
                                <div class="col-span-5 sm:col-span-3">
                                    <input type="number" :name="`items[${index}][harga_satuan]`" x-model="item.harga_satuan" @input="hitungTotalPembelian()" min="0" step="0.01" required placeholder="Harga satuan"
                                           class="w-full h-9 px-2 rounded-lg border border-[#C0C9BF]/50 bg-white text-[11px] font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                                </div>
                                <div class="col-span-3 sm:col-span-2 flex items-center justify-end gap-1.5">
                                    <span class="text-[10px] font-bold text-[#7A5900] whitespace-nowrap" x-text="formatRupiah((parseFloat(item.jumlah) || 0) * (parseFloat(item.harga_satuan) || 0))"></span>
                                    <button type="button" @click="hapusItemPembelian(index)"
                                            class="w-8 h-9 rounded-lg bg-white border border-gray-200 text-[#93000A] hover:bg-[#FFDAD6]/60 flex items-center justify-center cursor-pointer flex-shrink-0" title="Hapus bahan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Total (Rp) <span class="text-[#93000A]">*</span></label>
                    <input type="number" name="total" x-model="pembelianForm.total" :readonly="pembelianForm.items.length > 0" min="0.01" step="0.01" required placeholder="0"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30"
                           :class="pembelianForm.items.length > 0 ? 'text-[#0B4D2B] font-bold' : ''">
                    <span class="text-[10px] font-semibold text-[#707971]" x-show="pembelianForm.items.length > 0">Total dihitung otomatis dari rincian bahan.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalPembelian = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalPengeluaran" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" @click.outside="modalPengeluaran = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-[#1C1C18]" x-text="pengeluaranForm.id ? 'Ubah Pengeluaran Operasional' : 'Tambah Pengeluaran Operasional'"></h3>
                    <p class="text-[11px] text-[#707971] mt-0.5">Catat biaya operasional di luar pembelian bahan.</p>
                </div>
                <button type="button" @click="modalPengeluaran = false" class="w-8 h-8 rounded-lg hover:bg-[#F0EEE7] text-[#707971] flex items-center justify-center cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="pengeluaranForm.id ? pengeluaranUbahUrl.replace('__ID__', pengeluaranForm.id) : pengeluaranSimpanUrl" class="p-5 flex flex-col gap-4">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-bind:disabled="!pengeluaranForm.id">

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Tanggal <span class="text-[#93000A]">*</span></label>
                    <input type="date" name="tanggal" x-model="pengeluaranForm.tanggal" required
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Kategori <span class="text-[#93000A]">*</span></label>
                    <select name="kategori" x-model="pengeluaranForm.kategori" required
                            class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                        @foreach ($daftarKategori as $kode => $label)
                            <option value="{{ $kode }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Keterangan <span class="text-[#93000A]">*</span></label>
                    <textarea name="keterangan" x-model="pengeluaranForm.keterangan" rows="2" maxlength="255" required placeholder="Contoh: Token listrik bulan ini"
                              class="px-3 py-2 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30"></textarea>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-[#707971] uppercase tracking-wider">Jumlah (Rp) <span class="text-[#93000A]">*</span></label>
                    <input type="number" name="jumlah" x-model="pengeluaranForm.jumlah" min="0.01" step="0.01" required placeholder="0"
                           class="h-10 px-3 rounded-xl border border-[#C0C9BF]/50 bg-[#F6F3EC]/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#0B4D2B]/30">
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button type="button" @click="modalPengeluaran = false" class="h-10 px-4 rounded-xl bg-[#F0EEE7] hover:bg-[#ded9cf] text-[#404941] text-xs font-bold cursor-pointer">Batal</button>
                    <button type="submit" class="h-10 px-5 rounded-xl bg-[#0B4D2B] hover:bg-[#08381F] text-white text-xs font-bold cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function arusKasApp() {
    return {
        modalPembelian: false,
        modalPengeluaran: false,
        hariIni: @js($hariIni),
        pembelianData: @json($pembelianJson),
        pengeluaranData: @json($pengeluaranJson),
        bahanData: @json($bahanJson),
        pembelianSimpanUrl: @js(route('admin.arus-kas.pembelian.simpan')),
        pembelianUbahUrl: @js(route('admin.arus-kas.pembelian.ubah', ['pembelian' => '__ID__'])),
        pengeluaranSimpanUrl: @js(route('admin.arus-kas.pengeluaran.simpan')),
        pengeluaranUbahUrl: @js(route('admin.arus-kas.pengeluaran.ubah', ['pengeluaran' => '__ID__'])),
        pembelianForm: { id: null, tanggal: '', supplier: '', keterangan: '', total: '', items: [] },
        pengeluaranForm: { id: null, tanggal: '', kategori: 'bahan_bakar', keterangan: '', jumlah: '' },

        bukaTambahPembelian() {
            this.pembelianForm = {
                id: null,
                tanggal: this.hariIni,
                supplier: '',
                keterangan: '',
                total: '',
                items: [],
            };
            this.modalPembelian = true;
        },

        bukaUbahPembelian(id) {
            const baris = this.pembelianData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.pembelianForm = {
                id: baris.id,
                tanggal: baris.tanggal || this.hariIni,
                supplier: baris.supplier || '',
                keterangan: baris.keterangan || '',
                total: baris.total,
                items: (baris.items || []).map((item) => ({
                    bahan_baku_id: item.bahan_baku_id,
                    jumlah: item.jumlah,
                    harga_satuan: item.harga_satuan,
                })),
            };
            this.modalPembelian = true;
        },

        tambahItemPembelian() {
            this.pembelianForm.items.push({
                bahan_baku_id: '',
                jumlah: '',
                harga_satuan: '',
            });
        },

        hapusItemPembelian(index) {
            this.pembelianForm.items.splice(index, 1);
            this.hitungTotalPembelian();
        },

        pilihBahanPembelian(index) {
            const item = this.pembelianForm.items[index];
            const bahan = this.bahanData.find((baris) => Number(baris.id) === Number(item.bahan_baku_id));

            if (bahan && (item.harga_satuan === '' || Number(item.harga_satuan) === 0)) {
                item.harga_satuan = bahan.harga_rata_rata || '';
            }

            this.hitungTotalPembelian();
        },

        hitungTotalPembelian() {
            const total = this.pembelianForm.items.reduce((jumlah, item) => {
                return jumlah + (parseFloat(item.jumlah) || 0) * (parseFloat(item.harga_satuan) || 0);
            }, 0);

            if (this.pembelianForm.items.length > 0) {
                this.pembelianForm.total = total > 0 ? total.toFixed(2) : '';
            }

            return total;
        },

        formatRupiah(nilai) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(nilai) || 0);
        },

        bukaTambahPengeluaran() {
            this.pengeluaranForm = { id: null, tanggal: this.hariIni, kategori: 'bahan_bakar', keterangan: '', jumlah: '' };
            this.modalPengeluaran = true;
        },

        bukaUbahPengeluaran(id) {
            const baris = this.pengeluaranData.find((item) => Number(item.id) === Number(id));
            if (!baris) return;
            this.pengeluaranForm = {
                id: baris.id,
                tanggal: baris.tanggal || this.hariIni,
                kategori: baris.kategori || 'bahan_bakar',
                keterangan: baris.keterangan || '',
                jumlah: baris.jumlah,
            };
            this.modalPengeluaran = true;
        },
    };
}
</script>
@endpush
