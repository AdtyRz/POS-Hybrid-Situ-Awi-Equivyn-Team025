<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Pembayaran;
use App\Models\PembelianBahan;
use App\Models\Pengaturan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Services\BahanStokService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $this->resolveFilter($request);
        $hpp = $this->laporanHpp($filter);
        $ringkasan = array_merge($this->ringkasan($filter), $this->angkaHpp($hpp));

        $pembelian = $this->pembelianQuery($filter)
            ->paginate(10, ['*'], 'pembelian_page')
            ->withQueryString();

        $pengeluaran = $this->pengeluaranQuery($filter)
            ->paginate(10, ['*'], 'pengeluaran_page')
            ->withQueryString();

        $pembelianJson = $pembelian->getCollection()
            ->map(fn (PembelianBahan $baris) => [
                'id' => $baris->id,
                'tanggal' => $baris->tanggal?->toDateString(),
                'supplier' => (string) ($baris->supplier ?? ''),
                'keterangan' => (string) ($baris->keterangan ?? ''),
                'total' => (float) $baris->total,
                'items' => $baris->detail->map(fn ($item) => [
                    'bahan_baku_id' => (int) $item->bahan_baku_id,
                    'nama' => (string) ($item->bahan?->nama ?? ''),
                    'satuan' => (string) ($item->bahan?->satuan ?? ''),
                    'jumlah' => (float) $item->jumlah,
                    'harga_satuan' => (float) $item->harga_satuan,
                ])->values(),
            ])
            ->values();

        $pengeluaranJson = $pengeluaran->getCollection()
            ->map(fn (Pengeluaran $baris) => [
                'id' => $baris->id,
                'tanggal' => $baris->tanggal?->toDateString(),
                'kategori' => (string) $baris->kategori,
                'keterangan' => (string) $baris->keterangan,
                'jumlah' => (float) $baris->jumlah,
            ])
            ->values();

        $bahanJson = BahanBaku::query()
            ->where('is_aktif', true)
            ->orderBy('nama')
            ->get()
            ->map(fn (BahanBaku $baris) => [
                'id' => $baris->id,
                'nama' => $baris->nama,
                'satuan' => $baris->satuan,
                'harga_rata_rata' => (float) $baris->harga_rata_rata,
                'stok' => (float) $baris->stok,
            ])
            ->values();

        return view('admin.arus-kas.index', array_merge($filter, [
            'ringkasan' => $ringkasan,
            'hpp' => $hpp,
            'pembelian' => $pembelian,
            'pengeluaran' => $pengeluaran,
            'pembelianJson' => $pembelianJson,
            'pengeluaranJson' => $pengeluaranJson,
            'bahanJson' => $bahanJson,
            'daftarKategori' => Pengeluaran::KATEGORI,
            'namaResto' => Pengaturan::nilai('nama_resto'),
        ]));
    }

    public function simpanPembelian(Request $request, BahanStokService $stokService): RedirectResponse
    {
        $data = $this->validasiPembelian($request);
        $items = $data['items'] ?? [];
        unset($data['items']);

        $stokService->buatPembelian($data, $items, auth()->id());

        return back()->with('sukses', 'Pembelian bahan berhasil dicatat.');
    }

    public function ubahPembelian(Request $request, PembelianBahan $pembelian, BahanStokService $stokService): RedirectResponse
    {
        $data = $this->validasiPembelian($request);
        $items = $data['items'] ?? [];
        unset($data['items']);

        try {
            $stokService->perbaruiPembelian($pembelian, $data, $items, auth()->id());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return back()->with('sukses', 'Pembelian bahan berhasil diperbarui.');
    }

    public function hapusPembelian(PembelianBahan $pembelian, BahanStokService $stokService): RedirectResponse
    {
        try {
            $stokService->hapusPembelian($pembelian);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('sukses', 'Pembelian bahan berhasil dihapus.');
    }

    public function simpanPengeluaran(Request $request): RedirectResponse
    {
        $data = $this->validasiPengeluaran($request);
        $data['created_by'] = auth()->id();

        Pengeluaran::create($data);

        return back()->with('sukses', 'Pengeluaran operasional berhasil dicatat.');
    }

    public function ubahPengeluaran(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $pengeluaran->update($this->validasiPengeluaran($request));

        return back()->with('sukses', 'Pengeluaran operasional berhasil diperbarui.');
    }

    public function hapusPengeluaran(Pengeluaran $pengeluaran): RedirectResponse
    {
        $pengeluaran->delete();

        return back()->with('sukses', 'Pengeluaran operasional berhasil dihapus.');
    }

    public function cetak(Request $request): View
    {
        $filter = $this->resolveFilter($request);
        $hpp = $this->laporanHpp($filter);
        $ringkasan = array_merge($this->ringkasan($filter), $this->angkaHpp($hpp));

        return view('admin.arus-kas.cetak', array_merge($filter, [
            'ringkasan' => $ringkasan,
            'hpp' => $hpp,
            'pembelian' => $this->pembelianQuery($filter)->get(),
            'pengeluaran' => $this->pengeluaranQuery($filter)->get(),
            'namaResto' => Pengaturan::nilai('nama_resto'),
            'alamatResto' => Pengaturan::nilai('alamat_resto'),
            'teleponResto' => Pengaturan::nilai('telepon_resto'),
            'dicetakPada' => Carbon::now('Asia/Jakarta'),
            'dicetakOleh' => auth()->user()?->nama ?? 'Admin',
        ]));
    }

    public function ekspor(Request $request, string $format): StreamedResponse
    {
        $filter = $this->resolveFilter($request);
        $format = $format === 'csv' ? 'csv' : 'xls';
        $hpp = $this->laporanHpp($filter);
        $ringkasan = array_merge($this->ringkasan($filter), $this->angkaHpp($hpp));
        $pembelian = $this->pembelianQuery($filter)->get();
        $pengeluaran = $this->pengeluaranQuery($filter)->get();

        $filename = 'laporan-arus-kas-'
            .$filter['mulai']->format('Ymd')
            .'-'
            .$filter['selesai']->format('Ymd')
            .'.'
            .$format;

        $headers = $format === 'csv'
            ? [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
            : [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];

        return response()->stream(function () use ($format, $filter, $ringkasan, $pembelian, $pengeluaran, $hpp) {
            if ($format === 'csv') {
                $this->streamCsv($filter, $ringkasan, $pembelian, $pengeluaran, $hpp);

                return;
            }

            $this->streamXls($filter, $ringkasan, $pembelian, $pengeluaran, $hpp);
        }, 200, $headers);
    }

    private function resolveFilter(Request $request): array
    {
        $zona = 'Asia/Jakarta';
        $hariIni = Carbon::now($zona);

        try {
            $mulai = $request->filled('start')
                ? Carbon::parse((string) $request->query('start'), $zona)->startOfDay()
                : $hariIni->copy()->startOfMonth()->startOfDay();
        } catch (\Throwable) {
            $mulai = $hariIni->copy()->startOfMonth()->startOfDay();
        }

        try {
            $selesai = $request->filled('end')
                ? Carbon::parse((string) $request->query('end'), $zona)->endOfDay()
                : $hariIni->copy()->endOfDay();
        } catch (\Throwable) {
            $selesai = $hariIni->copy()->endOfDay();
        }

        if ($selesai->lt($mulai)) {
            [$mulai, $selesai] = [$selesai->copy()->startOfDay(), $mulai->copy()->endOfDay()];
        }

        return [
            'mulai' => $mulai,
            'selesai' => $selesai,
            'mulaiQuery' => $mulai->toDateString(),
            'selesaiQuery' => $selesai->toDateString(),
            'hariIni' => $hariIni->toDateString(),
            'labelPeriode' => $mulai->isSameDay($selesai)
                ? $mulai->format('d M Y')
                : $mulai->format('d M Y').' - '.$selesai->format('d M Y'),
        ];
    }

    private function ringkasan(array $filter): array
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

    private function pembelianQuery(array $filter): Builder
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
    private function laporanHpp(array $filter): array
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

    private function angkaHpp(array $hpp): array
    {
        return [
            'hpp' => $hpp['total_hpp'],
            'laba_kotor' => $hpp['laba_kotor'],
            'margin' => $hpp['margin'],
        ];
    }

    private function pengeluaranQuery(array $filter): Builder
    {
        return Pengeluaran::query()
            ->whereDate('tanggal', '>=', $filter['mulai']->toDateString())
            ->whereDate('tanggal', '<=', $filter['selesai']->toDateString())
            ->with('pencatat')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');
    }

    private function validasiPembelian(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'supplier' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'total' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'items' => ['nullable', 'array'],
            'items.*.bahan_baku_id' => ['required', 'integer', 'exists:bahan_baku,id'],
            'items.*.jumlah' => ['required', 'numeric', 'min:0.001', 'max:99999999999.999'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
        ], [
            'tanggal.required' => 'Tanggal pembelian wajib diisi.',
            'tanggal.date' => 'Tanggal pembelian tidak valid.',
            'total.required' => 'Total pembelian wajib diisi.',
            'total.numeric' => 'Total pembelian harus berupa angka.',
            'total.min' => 'Total pembelian harus lebih dari nol.',
            'items.*.bahan_baku_id.required' => 'Bahan pada rincian pembelian wajib dipilih.',
            'items.*.bahan_baku_id.exists' => 'Bahan pada rincian pembelian tidak ditemukan.',
            'items.*.jumlah.required' => 'Jumlah bahan pada rincian pembelian wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah bahan pada rincian pembelian harus lebih dari nol.',
            'items.*.harga_satuan.required' => 'Harga satuan pada rincian pembelian wajib diisi.',
            'items.*.harga_satuan.numeric' => 'Harga satuan pada rincian pembelian harus berupa angka.',
            'items.*.harga_satuan.min' => 'Harga satuan tidak boleh negatif.',
        ]);
    }

    private function validasiPengeluaran(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'kategori' => ['required', 'in:'.implode(',', array_keys(Pengeluaran::KATEGORI))],
            'keterangan' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
        ], [
            'tanggal.required' => 'Tanggal pengeluaran wajib diisi.',
            'tanggal.date' => 'Tanggal pengeluaran tidak valid.',
            'kategori.required' => 'Kategori pengeluaran wajib dipilih.',
            'kategori.in' => 'Kategori pengeluaran tidak valid.',
            'keterangan.required' => 'Keterangan pengeluaran wajib diisi.',
            'jumlah.required' => 'Jumlah pengeluaran wajib diisi.',
            'jumlah.numeric' => 'Jumlah pengeluaran harus berupa angka.',
            'jumlah.min' => 'Jumlah pengeluaran harus lebih dari nol.',
        ]);
    }

    private function streamCsv(array $filter, array $ringkasan, $pembelian, $pengeluaran, array $hpp): void
    {
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Laporan Arus Kas - '.Pengaturan::nilai('nama_resto')]);
        fputcsv($out, ['Periode', $filter['labelPeriode']]);
        fputcsv($out, ['Pemasukan Kas', $ringkasan['pemasukan']]);
        fputcsv($out, ['Pembelian Bahan', $ringkasan['pembelian']]);
        fputcsv($out, ['Pengeluaran Operasional', $ringkasan['pengeluaran']]);
        fputcsv($out, ['Total Kas Keluar', $ringkasan['total_keluar']]);
        fputcsv($out, ['Arus Kas Bersih', $ringkasan['arus_kas']]);
        fputcsv($out, ['HPP (Standar Resep)', $ringkasan['hpp']]);
        fputcsv($out, ['Laba Kotor', $ringkasan['laba_kotor']]);
        fputcsv($out, ['Margin Laba Kotor (%)', round($ringkasan['margin'], 2)]);
        fputcsv($out, []);
        fputcsv($out, ['PEMBELIAN BAHAN']);
        fputcsv($out, ['Tanggal', 'Supplier', 'Keterangan', 'Total', 'Dicatat Oleh']);

        foreach ($pembelian as $baris) {
            fputcsv($out, [
                $baris->tanggal?->format('d/m/Y') ?? '-',
                $this->safeCsv((string) ($baris->supplier ?? '-')),
                $this->safeCsv((string) ($baris->keterangan ?? '-')),
                (float) $baris->total,
                $this->safeCsv($baris->pencatat?->nama ?? '-'),
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['RINCIAN PEMBELIAN BAHAN']);
        fputcsv($out, ['Tanggal', 'Supplier', 'Bahan', 'Jumlah', 'Satuan', 'Harga Satuan', 'Subtotal']);

        foreach ($pembelian as $baris) {
            foreach ($baris->detail as $item) {
                fputcsv($out, [
                    $baris->tanggal?->format('d/m/Y') ?? '-',
                    $this->safeCsv((string) ($baris->supplier ?? '-')),
                    $this->safeCsv((string) ($item->bahan?->nama ?? '-')),
                    (float) $item->jumlah,
                    $this->safeCsv((string) ($item->bahan?->satuan ?? '')),
                    (float) $item->harga_satuan,
                    (float) $item->subtotal,
                ]);
            }
        }

        fputcsv($out, []);
        fputcsv($out, ['PENGELUARAN OPERASIONAL']);
        fputcsv($out, ['Tanggal', 'Kategori', 'Keterangan', 'Jumlah', 'Dicatat Oleh']);

        foreach ($pengeluaran as $baris) {
            fputcsv($out, [
                $baris->tanggal?->format('d/m/Y') ?? '-',
                $this->safeCsv($baris->labelKategori()),
                $this->safeCsv((string) $baris->keterangan),
                (float) $baris->jumlah,
                $this->safeCsv($baris->pencatat?->nama ?? '-'),
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['HPP & LABA KOTOR PER MENU']);
        fputcsv($out, ['Menu', 'Jumlah Terjual', 'Omzet', 'HPP Satuan', 'Total HPP', 'Laba Kotor', 'Margin (%)', 'Jumlah Bahan Resep']);

        foreach ($hpp['baris'] as $baris) {
            fputcsv($out, [
                $this->safeCsv($baris['nama_menu']),
                $baris['jumlah'],
                $baris['omzet'],
                $baris['hpp_satuan'],
                $baris['total_hpp'],
                $baris['laba_kotor'],
                round($baris['margin'], 2),
                $baris['jumlah_bahan'],
            ]);
        }

        fputcsv($out, ['TOTAL', '', $hpp['total_omzet'], '', $hpp['total_hpp'], $hpp['laba_kotor'], round($hpp['margin'], 2), '']);

        fclose($out);
    }

    private function streamXls(array $filter, array $ringkasan, $pembelian, $pengeluaran, array $hpp): void
    {
        echo "\xEF\xBB\xBF";
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8">';
        echo '<style>table{border-collapse:collapse}th{background:#0B4D2B;color:#fff;font-weight:bold;border:1px solid #08381F;padding:6px}td{border:1px solid #C0C9BF;padding:6px}tr:nth-child(even){background:#F6F3EC}</style>';
        echo '</head><body><table>';
        echo '<tr><th colspan="5">LAPORAN ARUS KAS '.e(strtoupper((string) Pengaturan::nilai('nama_resto'))).'</th></tr>';
        echo '<tr><th colspan="5">PERIODE: '.e($filter['labelPeriode']).'</th></tr>';
        echo '<tr><th>PEMASUKAN KAS</th><th>PEMBELIAN BAHAN</th><th>PENGELUARAN OPERASIONAL</th><th>TOTAL KAS KELUAR</th><th>ARUS KAS BERSIH</th></tr>';
        echo '<tr>'
            .'<td>Rp '.number_format($ringkasan['pemasukan'], 0, ',', '.').'</td>'
            .'<td>Rp '.number_format($ringkasan['pembelian'], 0, ',', '.').'</td>'
            .'<td>Rp '.number_format($ringkasan['pengeluaran'], 0, ',', '.').'</td>'
            .'<td>Rp '.number_format($ringkasan['total_keluar'], 0, ',', '.').'</td>'
            .'<td>Rp '.number_format($ringkasan['arus_kas'], 0, ',', '.').'</td>'
            .'</tr>';
        echo '<tr><th>HPP (STANDAR RESEP)</th><th>LABA KOTOR</th><th>MARGIN LABA KOTOR</th><th colspan="2"></th></tr>';
        echo '<tr>'
            .'<td>Rp '.number_format($ringkasan['hpp'], 0, ',', '.').'</td>'
            .'<td>Rp '.number_format($ringkasan['laba_kotor'], 0, ',', '.').'</td>'
            .'<td>'.number_format($ringkasan['margin'], 2, ',', '.').'%</td>'
            .'<td colspan="2"></td>'
            .'</tr>';
        echo '<tr><th colspan="5">PEMBELIAN BAHAN</th></tr>';
        echo '<tr><th>TANGGAL</th><th>SUPPLIER</th><th>KETERANGAN</th><th>TOTAL (Rp)</th><th>DICATAT OLEH</th></tr>';

        foreach ($pembelian as $baris) {
            echo '<tr>'
                .'<td>'.e($baris->tanggal?->format('d/m/Y') ?? '-').'</td>'
                .'<td>'.e((string) ($baris->supplier ?? '-')).'</td>'
                .'<td>'.e((string) ($baris->keterangan ?? '-')).'</td>'
                .'<td>'.(float) $baris->total.'</td>'
                .'<td>'.e($baris->pencatat?->nama ?? '-').'</td>'
                .'</tr>';

            foreach ($baris->detail as $item) {
                echo '<tr>'
                    .'<td></td>'
                    .'<td>Rincian</td>'
                    .'<td>'.e(($item->bahan?->nama ?? '-').' · '.$this->formatAngka((float) $item->jumlah).' '.($item->bahan?->satuan ?? '')).'</td>'
                    .'<td>'.(float) $item->subtotal.'</td>'
                    .'<td></td>'
                    .'</tr>';
            }
        }

        echo '<tr><th colspan="5">PENGELUARAN OPERASIONAL</th></tr>';
        echo '<tr><th>TANGGAL</th><th>KATEGORI</th><th>KETERANGAN</th><th>JUMLAH (Rp)</th><th>DICATAT OLEH</th></tr>';

        foreach ($pengeluaran as $baris) {
            echo '<tr>'
                .'<td>'.e($baris->tanggal?->format('d/m/Y') ?? '-').'</td>'
                .'<td>'.e($baris->labelKategori()).'</td>'
                .'<td>'.e((string) $baris->keterangan).'</td>'
                .'<td>'.(float) $baris->jumlah.'</td>'
                .'<td>'.e($baris->pencatat?->nama ?? '-').'</td>'
                .'</tr>';
        }

        echo '<tr><th colspan="8">HPP &amp; LABA KOTOR PER MENU</th></tr>';
        echo '<tr><th>MENU</th><th>JUMLAH TERJUAL</th><th>OMZET (Rp)</th><th>HPP SATUAN (Rp)</th><th>TOTAL HPP (Rp)</th><th>LABA KOTOR (Rp)</th><th>MARGIN</th><th>JUMLAH BAHAN</th></tr>';

        foreach ($hpp['baris'] as $baris) {
            echo '<tr>'
                .'<td>'.e($baris['nama_menu']).'</td>'
                .'<td>'.$baris['jumlah'].'</td>'
                .'<td>'.(float) $baris['omzet'].'</td>'
                .'<td>'.(float) $baris['hpp_satuan'].'</td>'
                .'<td>'.(float) $baris['total_hpp'].'</td>'
                .'<td>'.(float) $baris['laba_kotor'].'</td>'
                .'<td>'.number_format($baris['margin'], 2, ',', '.').'%</td>'
                .'<td>'.$baris['jumlah_bahan'].'</td>'
                .'</tr>';
        }

        echo '<tr>'
            .'<th>TOTAL</th><th></th>'
            .'<th>'.(float) $hpp['total_omzet'].'</th><th></th>'
            .'<th>'.(float) $hpp['total_hpp'].'</th>'
            .'<th>'.(float) $hpp['laba_kotor'].'</th>'
            .'<th>'.number_format($hpp['margin'], 2, ',', '.').'%</th><th></th>'
            .'</tr>';

        echo '</table></body></html>';
    }

    private function formatAngka(float $angka): string
    {
        return rtrim(rtrim(number_format($angka, 3, ',', '.'), '0'), ',');
    }

    private function safeCsv(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
