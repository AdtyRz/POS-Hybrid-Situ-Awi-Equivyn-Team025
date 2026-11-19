<?php

namespace App\Services;

use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\PembelianBahan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;

class LaporanArusKasService
{
    public function ringkasan(array $filter): array
    {
        $mulaiUtc = $filter['mulai']->copy()->utc();
        $selesaiUtc = $filter['selesai']->copy()->utc();
        $mulaiTanggal = $filter['mulai']->toDateString();
        $selesaiTanggal = $filter['selesai']->toDateString();

        $pembayaran = Pembayaran::query()
            ->join('pesanan', 'pesanan.id', '=', 'pembayaran.pesanan_id')
            ->where('pembayaran.status_pembayaran', 'lunas')
            ->whereBetween('pembayaran.waktu_bayar', [$mulaiUtc, $selesaiUtc])
            ->where('pesanan.status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN);

        $pemasukan = (float) (clone $pembayaran)->sum('pesanan.total_bayar');
        $jumlahTransaksi = (int) (clone $pembayaran)->count();

        $perMetode = (clone $pembayaran)
            ->selectRaw('pembayaran.metode_pembayaran, COUNT(*) as transaksi, COALESCE(SUM(pesanan.total_bayar), 0) as total')
            ->groupBy('pembayaran.metode_pembayaran')
            ->get()
            ->keyBy('metode_pembayaran');

        $pembelianQuery = PembelianBahan::query()
            ->whereDate('tanggal', '>=', $mulaiTanggal)
            ->whereDate('tanggal', '<=', $selesaiTanggal);
        $pembelian = (float) (clone $pembelianQuery)->sum('total');
        $jumlahPembelian = (int) (clone $pembelianQuery)->count();

        $pengeluaranQuery = Pengeluaran::query()
            ->whereDate('tanggal', '>=', $mulaiTanggal)
            ->whereDate('tanggal', '<=', $selesaiTanggal);
        $pengeluaran = (float) (clone $pengeluaranQuery)->sum('jumlah');
        $jumlahPengeluaran = (int) (clone $pengeluaranQuery)->count();

        $perKategori = (clone $pengeluaranQuery)
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $metode = [];

        foreach (['tunai', 'qris', 'transfer'] as $kode) {
            $baris = $perMetode->get($kode);

            $metode[$kode] = [
                'total' => (float) ($baris->total ?? 0),
                'transaksi' => (int) ($baris->transaksi ?? 0),
            ];
        }

        return [
            'pemasukan' => $pemasukan,
            'jumlah_transaksi' => $jumlahTransaksi,
            'pembelian' => $pembelian,
            'jumlah_pembelian' => $jumlahPembelian,
            'pengeluaran' => $pengeluaran,
            'jumlah_pengeluaran' => $jumlahPengeluaran,
            'total_keluar' => $pembelian + $pengeluaran,
            'arus_kas' => $pemasukan - $pembelian - $pengeluaran,
            'metode' => $metode,
            'kategori' => $perKategori->map(fn ($total) => (float) $total)->all(),
        ];
    }

    public function pembelianQuery(array $filter): Builder
    {
        return PembelianBahan::query()
            ->whereDate('tanggal', '>=', $filter['mulai']->toDateString())
            ->whereDate('tanggal', '<=', $filter['selesai']->toDateString())
            ->with(['pencatat', 'detail.bahan'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');
    }

    /**
     * HPP dihitung dari resep aktif dikali harga rata-rata bahan saat ini.
     * Omzet dan jumlah terjual mengikuti basis kas: pembayaran lunas pada periode.
     *
     * @return array{baris: array<int, array<string, mixed>>, total_omzet: float, total_hpp: float, laba_kotor: float, margin: float}
     */
    public function laporanHpp(array $filter): array
    {
        $penjualan = DetailPesanan::query()
            ->join('pesanan', 'pesanan.id', '=', 'detail_pesanan.pesanan_id')
            ->join('pembayaran', 'pembayaran.pesanan_id', '=', 'pesanan.id')
            ->where('pembayaran.status_pembayaran', 'lunas')
            ->whereBetween('pembayaran.waktu_bayar', [
                $filter['mulai']->copy()->utc(),
                $filter['selesai']->copy()->utc(),
            ])
            ->where('pesanan.status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->selectRaw('detail_pesanan.menu_id, SUM(detail_pesanan.jumlah) as jumlah, SUM(detail_pesanan.subtotal) as omzet')
            ->groupBy('detail_pesanan.menu_id')
            ->get();

        $menu = Menu::query()
            ->whereIn('id', $penjualan->pluck('menu_id')->unique()->all())
            ->with(['resep.bahan'])
            ->get()
            ->keyBy('id');

        $baris = [];
        $totalOmzet = 0.0;
        $totalHpp = 0.0;

        foreach ($penjualan as $item) {
            $dataMenu = $menu->get($item->menu_id);

            if (! $dataMenu) {
                continue;
            }

            $jumlah = (int) $item->jumlah;
            $omzet = (float) $item->omzet;
            $hppSatuan = $dataMenu->hppSatuan();
            $totalHppMenu = $hppSatuan * $jumlah;
            $labaKotor = $omzet - $totalHppMenu;

            $baris[] = [
                'menu_id' => $dataMenu->id,
                'nama_menu' => $dataMenu->nama_menu,
                'jumlah' => $jumlah,
                'omzet' => $omzet,
                'hpp_satuan' => $hppSatuan,
                'total_hpp' => $totalHppMenu,
                'laba_kotor' => $labaKotor,
                'margin' => $omzet > 0 ? ($labaKotor / $omzet) * 100 : 0.0,
                'jumlah_bahan' => $dataMenu->resep->count(),
            ];

            $totalOmzet += $omzet;
            $totalHpp += $totalHppMenu;
        }

        usort($baris, fn (array $a, array $b) => $b['laba_kotor'] <=> $a['laba_kotor']);

        $labaKotor = $totalOmzet - $totalHpp;

        return [
            'baris' => $baris,
            'total_omzet' => $totalOmzet,
            'total_hpp' => $totalHpp,
            'laba_kotor' => $labaKotor,
            'margin' => $totalOmzet > 0 ? ($labaKotor / $totalOmzet) * 100 : 0.0,
        ];
    }

    public function angkaHpp(array $hpp): array
    {
        return [
            'hpp' => $hpp['total_hpp'],
            'laba_kotor' => $hpp['laba_kotor'],
            'margin' => $hpp['margin'],
        ];
    }

    public function pengeluaranQuery(array $filter): Builder
    {
        return Pengeluaran::query()
            ->whereDate('tanggal', '>=', $filter['mulai']->toDateString())
            ->whereDate('tanggal', '<=', $filter['selesai']->toDateString())
            ->with('pencatat')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');
    }
}
