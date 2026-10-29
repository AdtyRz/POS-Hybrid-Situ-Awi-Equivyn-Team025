<?php

namespace App\Http\Controllers;

use App\Models\MejaMakan;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Services\ShiftService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $this->resolveFilter($request);
        $base = $this->baseQuery($filter);
        $ringkasan = $this->ringkasan($base);

        $ringkasan['persen_lunas'] = $ringkasan['jumlah_transaksi'] > 0
            ? (int) round($ringkasan['jumlah_lunas'] / $ringkasan['jumlah_transaksi'] * 100)
            : 0;
        $ringkasan['persen_qris'] = $ringkasan['omzet'] > 0
            ? (int) round($ringkasan['omzet_qris'] / $ringkasan['omzet'] * 100)
            : 0;

        $transaksi = (clone $base)
            ->with(['meja', 'kasir', 'detailPesanan.menu'])
            ->withSum('detailPesanan as total_item', 'jumlah')
            ->orderByDesc('waktu_pesan')
            ->paginate(20)
            ->withQueryString();

        $transaksiJson = $transaksi->getCollection()
            ->map(fn (Pesanan $pesanan) => $this->barisTransaksi($pesanan))
            ->values();

        $rekapHalaman = [
            'total' => (float) $transaksi->getCollection()->sum('total_bayar'),
            'valid' => $transaksi->getCollection()
                ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
                ->count(),
            'void' => $transaksi->getCollection()
                ->where('status_pesanan', Pesanan::STATUS_DIBATALKAN)
                ->count(),
        ];

        return view('admin.laporan-penjualan.index', array_merge($filter, [
            'ringkasan' => $ringkasan,
            'transaksi' => $transaksi,
            'transaksiJson' => $transaksiJson,
            'rekapHalaman' => $rekapHalaman,
            'namaResto' => Pengaturan::nilai('nama_resto'),
            'dicatatPada' => Carbon::now('Asia/Jakarta'),
            'namaAdmin' => auth()->user()?->nama ?? 'Admin',
        ]));
    }

    public function cetak(Request $request): View
    {
        $filter = $this->resolveFilter($request);
        $base = $this->baseQuery($filter);

        $transaksi = (clone $base)
            ->with(['meja', 'kasir', 'detailPesanan.menu'])
            ->withSum('detailPesanan as total_item', 'jumlah')
            ->orderBy('waktu_pesan')
            ->get();

        return view('admin.laporan-penjualan.cetak', array_merge($filter, [
            'ringkasan' => $this->ringkasan($base),
            'transaksi' => $transaksi,
            'namaResto' => Pengaturan::nilai('nama_resto'),
            'alamatResto' => Pengaturan::nilai('alamat_resto'),
            'teleponResto' => Pengaturan::nilai('telepon_resto'),
            'dicetakPada' => Carbon::now('Asia/Jakarta'),
            'dicetakOleh' => auth()->user()?->nama ?? 'Admin',
        ]));
    }

    public function cetakShift(Request $request): View
    {
        $filter = $this->resolveFilter($request);
        $zona = 'Asia/Jakarta';
        $hariIni = Carbon::now($zona);
        $shift = $this->shiftSaatIni($hariIni);

        // Gunakan tanggal yang sedang difilter bila satu hari, selain itu
        // cetak rekap shift untuk hari ini.
        $tanggal = $filter['mulai']->isSameDay($filter['selesai'])
            ? $filter['mulai']->copy()->setTimezone($zona)
            : $hariIni->copy();

        $mulaiShift = $tanggal->copy()->setTimeFromTimeString($shift['mulai']);
        $selesaiShift = $tanggal->copy()->setTimeFromTimeString($shift['selesai']);

        if ($selesaiShift->lessThanOrEqualTo($mulaiShift)) {
            $selesaiShift->addDay();
        }

        $filterShift = $filter;
        $filterShift['mulai'] = $mulaiShift;
        $filterShift['selesai'] = $selesaiShift;
        $filterShift['jenis'] = 'harian';

        $base = $this->baseQuery($filterShift);
        $ringkasan = $this->ringkasan($base);
        $ringkasan['persen_lunas'] = $ringkasan['jumlah_transaksi'] > 0
            ? (int) round($ringkasan['jumlah_lunas'] / $ringkasan['jumlah_transaksi'] * 100)
            : 0;

        $transaksi = (clone $base)
            ->with(['meja', 'kasir', 'detailPesanan.menu'])
            ->withSum('detailPesanan as total_item', 'jumlah')
            ->orderBy('waktu_pesan')
            ->get();

        return view('admin.laporan-penjualan.cetak-shift', [
            'ringkasan' => $ringkasan,
            'transaksi' => $transaksi,
            'namaResto' => Pengaturan::nilai('nama_resto'),
            'alamatResto' => Pengaturan::nilai('alamat_resto'),
            'teleponResto' => Pengaturan::nilai('telepon_resto'),
            'namaShift' => $shift['nama'],
            'jamShift' => $mulaiShift->format('H:i') . ' - ' . $selesaiShift->format('H:i') . ' WIB',
            'tanggalShift' => $mulaiShift->copy()->locale('id')->translatedFormat('l, d F Y'),
            'area' => $filter['area'],
            'labelArea' => $filter['labelArea'],
            'cari' => $filter['cari'],
            'dicetakPada' => Carbon::now($zona),
            'dicetakOleh' => auth()->user()?->nama ?? 'Admin',
        ]);
    }

    private function shiftSaatIni(Carbon $waktu): array
    {
        $shift = ShiftService::shiftAktif($waktu);

        if (! $shift) {
            return [
                'nama' => 'Luar Shift',
                'mulai' => '00:00',
                'selesai' => '23:59',
            ];
        }

        return $shift;
    }

    public function ekspor(Request $request, string $format): StreamedResponse
    {
        $filter = $this->resolveFilter($request);
        $base = $this->baseQuery($filter);
        $ringkasan = $this->ringkasan($base);
        $filename = 'laporan-penjualan-' . $filter['mulai']->format('Ymd') . '-' . $filter['selesai']->format('Ymd') . '.' . $format;

        $headers = $format === 'csv'
            ? [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
            : [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

        return response()->stream(function () use ($base, $format, $filter, $ringkasan) {
            if ($format === 'csv') {
                $this->streamCsv($base, $filter, $ringkasan);
                return;
            }

            $this->streamXls($base, $filter, $ringkasan);
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

        $daftarArea = MejaMakan::query()
            ->select('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->values();

        $area = (string) $request->query('area', 'semua');
        if ($area !== 'semua' && ! $daftarArea->contains($area)) {
            $area = 'semua';
        }

        $cari = trim((string) $request->query('q', ''));
        if (mb_strlen($cari) > 80) {
            $cari = mb_substr($cari, 0, 80);
        }

        $jenis = (string) $request->query('jenis', 'harian');
        if (! in_array($jenis, ['harian', 'rekonsiliasi', 'void'], true)) {
            $jenis = 'harian';
        }

        return [
            'mulai' => $mulai,
            'selesai' => $selesai,
            'mulaiQuery' => $mulai->toDateString(),
            'selesaiQuery' => $selesai->toDateString(),
            'area' => $area,
            'cari' => $cari,
            'jenis' => $jenis,
            'daftarArea' => $daftarArea,
            'labelPeriode' => $mulai->isSameDay($selesai)
                ? $mulai->format('d M Y')
                : $mulai->format('d M Y') . ' - ' . $selesai->format('d M Y'),
            'labelArea' => $area === 'semua' ? 'Semua Area Saung' : $area,
            'labelJenis' => match ($jenis) {
                'rekonsiliasi' => 'Rekonsiliasi Transaksi Lunas',
                'void' => 'Transaksi Void / Dibatalkan',
                default => 'Laporan Penjualan Harian & Shift Kasir',
            },
        ];
    }

    private function baseQuery(array $filter): Builder
    {
        $mulaiUtc = $filter['mulai']->copy()->utc();
        $selesaiUtc = $filter['selesai']->copy()->utc();

        $query = Pesanan::query()
            ->whereBetween('waktu_pesan', [$mulaiUtc, $selesaiUtc]);

        if ($filter['area'] !== 'semua') {
            $query->whereHas('meja', fn (Builder $meja) => $meja->where('area', $filter['area']));
        }

        if ($filter['cari'] !== '') {
            $cari = $filter['cari'];
            $query->where(function (Builder $sub) use ($cari) {
                $sub->where('kode_pesanan', 'like', '%' . $cari . '%')
                    ->orWhereHas('meja', function (Builder $meja) use ($cari) {
                        $meja->where('kode_meja', 'like', '%' . $cari . '%')
                            ->orWhere('area', 'like', '%' . $cari . '%');
                    })
                    ->orWhereHas('kasir', fn (Builder $kasir) => $kasir->where('nama', 'like', '%' . $cari . '%'));
            });
        }

        if ($filter['jenis'] === 'rekonsiliasi') {
            $query->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
                ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN);
        } elseif ($filter['jenis'] === 'void') {
            $query->where('status_pesanan', Pesanan::STATUS_DIBATALKAN);
        }

        return $query;
    }

    private function ringkasan(Builder $base): array
    {
        $lunas = (clone $base)
            ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN);

        $omzet = (float) (clone $lunas)->sum('total_bayar');
        $jumlahLunas = (clone $lunas)->count();

        $jumlahQris = (clone $lunas)->where('metode_pembayaran', 'qris')->count();
        $omzetQris = (float) (clone $lunas)->where('metode_pembayaran', 'qris')->sum('total_bayar');

        $tunai = (clone $lunas)->where(function (Builder $query) {
            $query->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran');
        });
        $jumlahTunai = (clone $tunai)->count();
        $omzetTunai = (float) (clone $tunai)->sum('total_bayar');

        $jumlahTransaksi = (clone $base)
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->count();

        $belumLunas = (clone $base)
            ->where('status_pesanan', '!=', Pesanan::STATUS_DIBATALKAN)
            ->where('status_pembayaran', '!=', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->count();

        $void = (clone $base)->where('status_pesanan', Pesanan::STATUS_DIBATALKAN)->count();

        return [
            'omzet' => $omzet,
            'jumlah_lunas' => $jumlahLunas,
            'rata_rata' => $jumlahLunas > 0 ? $omzet / $jumlahLunas : 0.0,
            'jumlah_qris' => $jumlahQris,
            'omzet_qris' => $omzetQris,
            'jumlah_tunai' => $jumlahTunai,
            'omzet_tunai' => $omzetTunai,
            'jumlah_transaksi' => $jumlahTransaksi,
            'belum_lunas' => $belumLunas,
            'void' => $void,
        ];
    }

    private function barisTransaksi(Pesanan $pesanan): array
    {
        $dibatalkan = $pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN;
        $lunas = $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR;
        $items = $pesanan->detailPesanan
            ->take(2)
            ->map(fn ($detail) => (int) $detail->jumlah . '× ' . ($detail->menu?->nama_menu ?? 'Menu'))
            ->implode(', ');
        $jumlahItem = (int) ($pesanan->total_item ?? $pesanan->detailPesanan->sum('jumlah'));

        if ($items !== '') {
            $items .= ' • ' . $jumlahItem . ' item';
        } else {
            $items = $jumlahItem . ' item';
        }

        return [
            'id' => '#' . $pesanan->kode_pesanan,
            'time' => optional($pesanan->waktu_pesan)->timezone('Asia/Jakarta')->format('d M Y • H:i') ?? '-',
            'table' => $pesanan->meja
                ? $pesanan->meja->area . ' (' . $pesanan->meja->kode_meja . ')'
                : '-',
            'area' => $pesanan->meja?->area ?? '-',
            'items' => $items,
            'method' => $this->metodeLabel($pesanan),
            'subtotal' => (float) $pesanan->detailPesanan->sum('subtotal'),
            'total' => (float) $pesanan->total_bayar,
            'cashier' => $pesanan->kasir?->nama ?? '-',
            'status' => $dibatalkan ? 'void' : ($lunas ? 'lunas' : 'pending'),
            'statusText' => $this->statusLabel($pesanan),
        ];
    }

    private function metodeLabel(Pesanan $pesanan): string
    {
        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return 'VOID Kasir';
        }

        return match ($pesanan->metode_pembayaran) {
            'qris' => 'QRIS Midtrans',
            'tunai' => 'Tunai Kasir',
            default => $pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR ? 'Tunai Kasir' : '-',
        };
    }

    private function statusLabel(Pesanan $pesanan): string
    {
        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return 'Dibatalkan';
        }

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return 'Lunas';
        }

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_PENDING_VERIFIKASI) {
            return $pesanan->metode_pembayaran === 'qris' ? 'Menunggu QRIS' : 'Verifikasi';
        }

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_PENDING_PEMBAYARAN) {
            return $pesanan->metode_pembayaran === 'tunai' ? 'Menunggu Tunai' : 'Menunggu QRIS';
        }

        return $pesanan->metode_pembayaran === 'qris' ? 'Belum Bayar QRIS' : 'Belum Bayar';
    }

    private function streamCsv(Builder $base, array $filter, array $ringkasan): void
    {
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Laporan Penjualan - ' . Pengaturan::nilai('nama_resto')]);
        fputcsv($out, ['Periode', $filter['labelPeriode']]);
        fputcsv($out, ['Area', $filter['labelArea']]);
        fputcsv($out, ['Omzet Lunas', $ringkasan['omzet']]);
        fputcsv($out, ['Transaksi Lunas', $ringkasan['jumlah_lunas']]);
        fputcsv($out, ['QRIS Midtrans', $ringkasan['omzet_qris']]);
        fputcsv($out, ['Tunai Kasir', $ringkasan['omzet_tunai']]);
        fputcsv($out, []);
        fputcsv($out, ['Tanggal', 'Kode Pesanan', 'Meja', 'Area', 'Kasir', 'Metode', 'Status', 'Jumlah Item', 'Total Bayar']);

        (clone $base)
            ->with(['meja', 'kasir', 'detailPesanan'])
            ->withSum('detailPesanan as total_item', 'jumlah')
            ->orderBy('waktu_pesan')
            ->chunk(500, function ($daftar) use ($out) {
                foreach ($daftar as $pesanan) {
                    fputcsv($out, [
                        $this->safeCsv(optional($pesanan->waktu_pesan)->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-'),
                        $this->safeCsv('#' . $pesanan->kode_pesanan),
                        $this->safeCsv($pesanan->meja?->kode_meja ?? '-'),
                        $this->safeCsv($pesanan->meja?->area ?? '-'),
                        $this->safeCsv($pesanan->kasir?->nama ?? '-'),
                        $this->safeCsv($this->metodeLabel($pesanan)),
                        $this->safeCsv($this->statusLabel($pesanan)),
                        (int) $pesanan->total_item,
                        (float) $pesanan->total_bayar,
                    ]);
                }
            });

        fclose($out);
    }

    private function streamXls(Builder $base, array $filter, array $ringkasan): void
    {
        echo "\xEF\xBB\xBF";
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8">';
        echo '<style>table{border-collapse:collapse}th{background:#0B4D2B;color:#fff;font-weight:bold;border:1px solid #08381F;padding:6px}td{border:1px solid #C0C9BF;padding:6px}tr:nth-child(even){background:#F6F3EC}</style>';
        echo '</head><body><table>';
        echo '<tr><th colspan="9">LAPORAN PENJUALAN ' . e(strtoupper((string) Pengaturan::nilai('nama_resto'))) . '</th></tr>';
        echo '<tr><th colspan="9">PERIODE: ' . e($filter['labelPeriode']) . ' &middot; AREA: ' . e($filter['labelArea']) . '</th></tr>';
        echo '<tr><th colspan="2">OMZET LUNAS: Rp ' . number_format($ringkasan['omzet'], 0, ',', '.') . '</th><th colspan="2">TRANSAKSI LUNAS: ' . $ringkasan['jumlah_lunas'] . '</th><th colspan="2">QRIS: Rp ' . number_format($ringkasan['omzet_qris'], 0, ',', '.') . '</th><th colspan="3">TUNAI: Rp ' . number_format($ringkasan['omzet_tunai'], 0, ',', '.') . '</th></tr>';
        echo '<tr><th>TANGGAL</th><th>KODE</th><th>MEJA</th><th>AREA</th><th>KASIR</th><th>METODE</th><th>STATUS</th><th>ITEM</th><th>TOTAL BAYAR (Rp)</th></tr>';

        (clone $base)
            ->with(['meja', 'kasir', 'detailPesanan'])
            ->withSum('detailPesanan as total_item', 'jumlah')
            ->orderBy('waktu_pesan')
            ->chunk(500, function ($daftar) {
                foreach ($daftar as $pesanan) {
                    echo '<tr>';
                    echo '<td>' . e(optional($pesanan->waktu_pesan)->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-') . '</td>';
                    echo '<td>#' . e($pesanan->kode_pesanan) . '</td>';
                    echo '<td>' . e($pesanan->meja?->kode_meja ?? '-') . '</td>';
                    echo '<td>' . e($pesanan->meja?->area ?? '-') . '</td>';
                    echo '<td>' . e($pesanan->kasir?->nama ?? '-') . '</td>';
                    echo '<td>' . e($this->metodeLabel($pesanan)) . '</td>';
                    echo '<td>' . e($this->statusLabel($pesanan)) . '</td>';
                    echo '<td>' . (int) $pesanan->total_item . '</td>';
                    echo '<td>' . (float) $pesanan->total_bayar . '</td>';
                    echo '</tr>';
                }
            });

        echo '</table></body></html>';
    }

    private function safeCsv(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}
