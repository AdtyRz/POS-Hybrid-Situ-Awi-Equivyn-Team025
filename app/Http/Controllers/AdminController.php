<?php

namespace App\Http\Controllers;

use App\Events\MenuDiperbarui;
use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Models\User;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    private function resolveAnalitikPeriode(Request $request): array
    {
        $periode = (string) $request->query('periode', 'hari_ini');
        $hariIni = Carbon::now('Asia/Jakarta');

        if (! in_array($periode, ['hari_ini', 'mingguan', 'bulanan', 'kustom'], true)) {
            $periode = 'hari_ini';
        }

        if ($periode === 'mingguan') {
            $start = $hariIni->copy()->subDays(6)->startOfDay();
            $end = $hariIni->copy()->endOfDay();
        } elseif ($periode === 'bulanan') {
            $start = $hariIni->copy()->subDays(29)->startOfDay();
            $end = $hariIni->copy()->endOfDay();
        } elseif ($periode === 'kustom') {
            $start = $this->parseTanggal($request->query('start')) ?? $hariIni->copy();
            $end = $this->parseTanggal($request->query('end')) ?? $hariIni->copy();

            if ($end->lt($start)) {
                [$start, $end] = [$end, $start];
            }

            $start = $start->copy()->startOfDay();
            $end = $end->copy()->endOfDay();
        } else {
            $start = $hariIni->copy()->startOfDay();
            $end = $hariIni->copy()->endOfDay();
        }

        $label = match ($periode) {
            'mingguan' => '7 Hari Terakhir',
            'bulanan' => '30 Hari Terakhir',
            'kustom' => $start->isSameDay($end)
                ? $start->format('d M Y')
                : $start->format('d M') . ' - ' . $end->format('d M Y'),
            default => 'Hari Ini',
        };

        return [$periode, $start, $end, $label];
    }

    private function parseTanggal(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value, 'Asia/Jakarta');
        } catch (\Throwable) {
            return null;
        }
    }

    private function scopeTanggalLokal($query, string $mulai, string $selesai)
    {
        return $query->whereRaw(
            "(waktu_pesan AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Jakarta')::date between ? and ?",
            [$mulai, $selesai]
        );
    }

    public function dashboard(Request $request): View
    {
        [$periode, $start, $end, $labelPeriode] = $this->resolveAnalitikPeriode($request);

        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $jumlahHari = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $pembandingEnd = $start->copy()->subDay()->endOfDay();
        $pembandingStart = $pembandingEnd->copy()->subDays($jumlahHari - 1)->startOfDay();

        $pendapatan = (float) $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');

        $pendapatanKemarin = (float) $this->scopeTanggalLokal(Pesanan::query(), $pembandingStart->toDateString(), $pembandingEnd->toDateString())
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');

        $pesananHariIni = $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->whereNotIn('status_pesanan', ['dibatalkan'])
            ->count();
        $pesananTotal = $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->count();

        $mejaTerisi = MejaMakan::where('status_meja', 'terisi')->count();
        $mejaTotal = MejaMakan::count();
        $stokMenipis = Menu::where('is_aktif', true)->where('stok_menu', '<=', 5)->count();

        $menuMakananTersedia = Menu::where('is_aktif', true)->where('stok_menu', '>', 0)
            ->whereHas('kategori', function ($q) {
                $q->where('target_kds', 'dapur');
            })
            ->count();
        $menuMinumanTersedia = Menu::where('is_aktif', true)->where('stok_menu', '>', 0)
            ->whereHas('kategori', function ($q) {
                $q->where('target_kds', 'bar');
            })
            ->count();
        $menuTersediaTotal = $menuMakananTersedia + $menuMinumanTersedia;

        $sesiAktif = SesiMeja::aktif()->count();

        $rataMenit = DetailPesanan::where('status_item', 'siap')
            ->whereBetween(DB::raw('DATE(detail_pesanan.updated_at)'), [$startDate, $endDate])
            ->join('pesanan', 'pesanan.id', '=', 'detail_pesanan.pesanan_id')
            ->avg(DB::raw('EXTRACT(EPOCH FROM (detail_pesanan.updated_at - pesanan.waktu_pesan)) / 60'));

        $qrisHariIni = $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->where('metode_pembayaran', 'qris')
            ->where('status_pembayaran', 'sudah_bayar')
            ->count();
        $tunaiHariIni = $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->where(function ($query) {
                $query->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran');
            })
            ->where('status_pembayaran', 'sudah_bayar')
            ->count();

        $omzetQris = (float) $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->where('metode_pembayaran', 'qris')
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');
        $omzetTunai = (float) $this->scopeTanggalLokal(Pesanan::query(), $startDate, $endDate)
            ->where(function ($query) {
                $query->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran');
            })
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');

        $putaran = $this->scopeTanggalLokal(
            Pesanan::selectRaw('meja_id, AVG(EXTRACT(EPOCH FROM (updated_at - waktu_pesan)) / 60) as menit'),
            $startDate,
            $endDate
        )
            ->where('status_pesanan', 'selesai')
            ->with('meja')
            ->groupBy('meja_id')
            ->orderBy('menit')
            ->first();

        $terlaris = DetailPesanan::selectRaw('menu_id, SUM(jumlah) as porsi, SUM(subtotal) as omzet')
            ->whereHas('pesanan', function ($query) use ($startDate, $endDate) {
                $this->scopeTanggalLokal($query, $startDate, $endDate)
                    ->whereNotIn('status_pesanan', ['dibatalkan']);
            })
            ->with('menu')
            ->groupBy('menu_id')
            ->orderByDesc('porsi')
            ->limit(5)
            ->get();

        $grafik = [];
        $hariIniLokal = Carbon::now('Asia/Jakarta')->startOfDay();
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = $hariIniLokal->copy()->subDays($i);
            $total = (float) $this->scopeTanggalLokal(Pesanan::query(), $tanggal->toDateString(), $tanggal->toDateString())
                ->where('status_pembayaran', 'sudah_bayar')
                ->sum('total_bayar');
            $jumlahPesanan = $this->scopeTanggalLokal(Pesanan::query(), $tanggal->toDateString(), $tanggal->toDateString())
                ->whereNotIn('status_pesanan', ['dibatalkan'])
                ->count();
            $grafik[] = [
                'label' => $tanggal->format('d M'),
                'total' => $total,
                'jumlah' => $jumlahPesanan,
            ];
        }

        $logAudit = $this->scopeTanggalLokal(
            Pesanan::with(['meja', 'kasir', 'detailPesanan.menu']),
            $startDate,
            $endDate
        )
            ->orderByDesc('waktu_pesan')
            ->limit(4)
            ->get()
            ->map(function ($pesanan) {
                $batal = $pesanan->status_pesanan === 'dibatalkan';
                $items = $pesanan->detailPesanan->map(fn ($d) => [
                    'nama' => $d->menu?->nama_menu ?? 'Menu',
                    'jumlah' => (int) $d->jumlah,
                ])->values();
                $status = $batal
                    ? 'Dibatalkan'
                    : match ($pesanan->status_pembayaran) {
                        Pesanan::PAYMENT_SUDAH_BAYAR => 'Lunas',
                        Pesanan::PAYMENT_PENDING_VERIFIKASI => 'Verifikasi',
                        Pesanan::PAYMENT_PENDING_PEMBAYARAN => 'Menunggu',
                        default => 'Belum Lunas',
                    };

                return [
                    'kode' => '#' . $pesanan->kode_pesanan,
                    'meja' => $pesanan->meja ? ($pesanan->meja->area . ' (' . $pesanan->meja->kode_meja . ')') : '-',
                    'items' => $items,
                    'total' => $batal ? 0.0 : (float) $pesanan->total_bayar,
                    'status' => $status,
                    'is_void' => $batal,
                    'waktu' => $pesanan->waktu_pesan ? $pesanan->waktu_pesan->copy()->setTimezone('Asia/Jakarta')->format('H:i') : '-',
                ];
            });

        return view('admin.analitik-laporan.index', compact(
            'pendapatan', 'pendapatanKemarin', 'pesananHariIni', 'pesananTotal',
            'mejaTerisi', 'mejaTotal', 'stokMenipis', 'grafik',
            'rataMenit', 'qrisHariIni', 'tunaiHariIni', 'omzetQris', 'omzetTunai', 'terlaris',
            'putaran', 'menuMakananTersedia', 'menuMinumanTersedia', 'menuTersediaTotal', 'sesiAktif', 'logAudit',
            'periode', 'labelPeriode', 'startDate', 'endDate'
        ));
    }

    public function cetakAnalitikPdf(Request $request): View
    {
        [$periode, $start, $end] = $this->resolveAnalitikPeriode($request);
        $hariIni = Carbon::now('Asia/Jakarta');

        $pendapatan = $this->scopeTanggalLokal(Pesanan::query(), $start->toDateString(), $end->toDateString())->where('status_pembayaran', 'sudah_bayar')->sum('total_bayar');
        $pesananHariIni = $this->scopeTanggalLokal(Pesanan::query(), $start->toDateString(), $end->toDateString())->whereNotIn('status_pesanan', ['dibatalkan'])->count();
        $qrisHariIni = $this->scopeTanggalLokal(Pesanan::query(), $start->toDateString(), $end->toDateString())->where('metode_pembayaran', 'qris')->whereNotIn('status_pesanan', ['dibatalkan'])->count();
        $tunaiHariIni = $this->scopeTanggalLokal(Pesanan::query(), $start->toDateString(), $end->toDateString())->where(function ($q) { $q->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran'); })->whereNotIn('status_pesanan', ['dibatalkan'])->count();
        $terlaris = DetailPesanan::selectRaw('menu_id, SUM(jumlah) as porsi, SUM(subtotal) as omzet')
            ->whereHas('pesanan', function ($q) use ($start, $end) { $this->scopeTanggalLokal($q, $start->toDateString(), $end->toDateString())->whereNotIn('status_pesanan', ['dibatalkan']); })
            ->with('menu')
            ->groupBy('menu_id')
            ->orderByDesc('porsi')
            ->limit(5)
            ->get();
        $logAudit = $this->scopeTanggalLokal(Pesanan::with(['meja', 'kasir']), $start->toDateString(), $end->toDateString())->whereNotIn('status_pesanan', ['dibatalkan'])->orderByDesc('waktu_pesan')->limit(15)->get();

        $labelPeriode = match ($periode) {
            'mingguan' => '7 Hari Terakhir (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')',
            'bulanan' => '30 Hari Terakhir (' . $start->format('d M Y') . ')',
            'kustom' => 'Kustom (' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . ')',
            default => 'Harian (' . $hariIni->format('d M Y') . ')',
        };

        return view('admin.analitik-laporan.cetak', compact('hariIni', 'pendapatan', 'pesananHariIni', 'qrisHariIni', 'tunaiHariIni', 'terlaris', 'logAudit', 'labelPeriode', 'start', 'end'));
    }

    public function eksporAnalitikXlsx(Request $request): StreamedResponse
    {
        [$periode, $start, $end] = $this->resolveAnalitikPeriode($request);
        $hariIni = Carbon::now('Asia/Jakarta');
        $filename = 'laporan-analitik-' . match ($periode) {
            'mingguan' => 'mingguan-' . $start->format('Y-m-d'),
            'bulanan' => 'bulanan-' . $start->format('Y-m'),
            'kustom' => 'kustom-' . $start->format('Ymd') . '-' . $end->format('Ymd'),
            default => 'harian-' . $hariIni->format('Y-m-d'),
        } . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($start, $end, $periode) {
            echo "\xEF\xBB\xBF";
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"><style>table{border-collapse:collapse}th{background:#0B4D2B;color:#fff;font-weight:bold;border:1px solid #08381F;padding:6px}td{border:1px solid #C0C9BF;padding:6px}tr:nth-child(even){background:#F6F3EC}</style></head>';
            echo '<body>';
            echo '<table>';
            echo '<tr><th colspan="8">LAPORAN ANALITIK SAUNG SITU AWI - ' . strtoupper(str_replace('_', ' ', $periode)) . '</th></tr>';
            echo '<tr><th colspan="8">PERIODE: ' . $start->format('d M Y') . ' - ' . $end->format('d M Y') . '</th></tr>';
            echo '<tr><th>TANGGAL</th><th>KODE PESANAN</th><th>MEJA</th><th>KASIR</th><th>METODE PEMBAYARAN</th><th>STATUS PEMBAYARAN</th><th>TOTAL BAYAR (Rp)</th><th>WAKTU</th></tr>';

            $this->scopeTanggalLokal(Pesanan::with(['meja', 'kasir']), $start->toDateString(), $end->toDateString())->orderBy('waktu_pesan')->chunk(500, function ($pesananList) use ($end) {
                foreach ($pesananList as $p) {
                    $metode = $p->metode_pembayaran === 'qris' ? 'QRIS' : 'TUNAI';
                    echo '<tr>';
                    echo '<td>' . optional($p->waktu_pesan)->timezone('Asia/Jakarta')->format('d/m/Y') . '</td>';
                    echo '<td>#' . $p->kode_pesanan . '</td>';
                    echo '<td>' . ($p->meja ? $p->meja->kode_meja . ' (' . $p->meja->area . ')' : '-') . '</td>';
                    echo '<td>' . ($p->kasir ? $p->kasir->nama : '-') . '</td>';
                    echo '<td>' . $metode . '</td>';
                    echo '<td>' . strtoupper(str_replace('_', ' ', $p->status_pembayaran)) . '</td>';
                    echo '<td>' . (int) $p->total_bayar . '</td>';
                    echo '<td>' . optional($p->waktu_pesan)->timezone('Asia/Jakarta')->format('H:i:s') . '</td>';
                    echo '</tr>';
                }
            });

            echo '</table>';
            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    public function index(): View
    {
        $transaksi = Pesanan::with(['meja', 'kasir', 'detailPesanan.menu'])
            ->orderByDesc('waktu_pesan')
            ->limit(50)
            ->get()
            ->map(function ($pesanan) {
                $batal = $pesanan->status_pesanan === 'dibatalkan';
                $namaMenu = $pesanan->detailPesanan->take(2)->map(fn ($d) => $d->menu->nama_menu)->join(', ');
                $jumlahItem = $pesanan->detailPesanan->sum('jumlah');

                return [
                    'id' => '#' . $pesanan->kode_pesanan,
                    'time' => $pesanan->waktu_pesan->timezone('Asia/Jakarta')->format('H.i') . ' WIB',
                    'table' => $pesanan->meja->area . ' (' . $pesanan->meja->kode_meja . ')',
                    'items' => $jumlahItem . ' Item (' . $namaMenu . ')',
                    'method' => $batal ? 'VOID Kasir' : ($pesanan->metode_pembayaran === 'qris' ? 'QRIS Midtrans' : 'Tunai Kasir'),
                    'subtotal' => (float) $pesanan->total_bayar,
                    'total' => (float) $pesanan->total_bayar,
                    'cashier' => $pesanan->kasir ? $pesanan->kasir->nama : '-',
                    'status' => $batal ? 'void' : 'valid',
                    'statusText' => $batal ? 'VOID Approved' : 'Match/Valid',
                ];
            })->values();

        return view('admin.laporan-penjualan.index', ['transaksiJson' => $transaksi]);
    }

    private function menuPayload(): array
    {
        $menuJson = Menu::with('kategori')->orderBy('nama_menu')->get()->map(function ($menu) {
            $stok = (int) $menu->stok_menu;
            $aktif = (bool) $menu->is_aktif;

            if (! $aktif) {
                $status = 'nonaktif';
                $statusText = 'Nonaktif';
                $stockStatus = 'normal';
            } elseif ($stok <= 0) {
                $status = 'habis';
                $statusText = 'Habis';
                $stockStatus = 'habis';
            } elseif ($stok <= 5) {
                $status = 'hampir_habis';
                $statusText = 'Stok Menipis';
                $stockStatus = 'kritis';
            } else {
                $status = 'tersedia';
                $statusText = 'Tersedia Live';
                $stockStatus = 'normal';
            }

            return [
                'id' => $menu->id,
                'sku' => $menu->slug,
                'name' => $menu->nama_menu,
                'description' => $menu->deskripsi ?? '',
                'badge' => $menu->kategori->nama_kategori,
                'price' => (float) $menu->harga,
                'photo' => $menu->foto_menu ? asset('storage/' . $menu->foto_menu) : '',
                'category' => 'k' . $menu->kategori_id,
                'kategoriId' => $menu->kategori_id,
                'kds' => $menu->kategori->target_kds === 'dapur' ? 'KDS Dapur (Makanan)' : 'KDS Bar (Minuman)',
                'kdsType' => $menu->kategori->target_kds,
                'stock' => $stok,
                'stockQty' => $stok . ' Porsi',
                'stockStatus' => $stockStatus,
                'status' => $status,
                'statusText' => $statusText,
                'is_aktif' => $aktif,
                'available' => $aktif && $stok > 0,
                'enabled' => $aktif && $stok > 0,
            ];
        })->values();

        $kategoriJson = Kategori::untukMenu()->orderBy('id')->get()->map(function ($kategori) {
            return ['id' => 'k' . $kategori->id, 'name' => $kategori->nama_kategori];
        })->values();

        return [
            'menus' => $menuJson,
            'categories' => $kategoriJson,
            'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
        ];
    }

    public function daftarMenu(): View
    {
        $payload = $this->menuPayload();
        $menuJson = $payload['menus'];
        $kategoriJson = $payload['categories'];
        $kategoriForm = Kategori::untukMenu()->orderBy('nama_kategori')->get();

        return view('admin.master-menu.index', compact('menuJson', 'kategoriJson', 'kategoriForm'));
    }

    public function dataMenu(): JsonResponse
    {
        return response()->json($this->menuPayload());
    }

    private function aturanFotoMenu(): array
    {
        return [
            'nullable',
            'file',
            'image',
            'mimetypes:image/jpeg,image/png,image/webp',
            'mimes:jpg,jpeg,png,webp',
            'extensions:jpg,jpeg,png,webp',
            'max:3072',
        ];
    }

    private function simpanFotoMenu(Request $request): ?string
    {
        if (! $request->hasFile('foto_menu')) {
            return null;
        }

        $file = $request->file('foto_menu');
        $ekstensi = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if (! $ekstensi) {
            throw ValidationException::withMessages([
                'foto_menu' => 'Format foto menu tidak didukung. Gunakan JPG, PNG, atau WEBP.',
            ]);
        }

        return $file->storeAs('menu', str()->random(40) . '.' . $ekstensi, 'public');
    }

    public function simpanMenu(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_menu' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', Rule::exists('kategori', 'id')->where('jenis', 'menu')],
            'harga' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stok_menu' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'foto_menu' => $this->aturanFotoMenu(),
        ], [
            'foto_menu.image' => 'Foto menu harus berupa gambar yang valid.',
            'foto_menu.mimetypes' => 'Foto menu harus berformat JPG, PNG, atau WEBP.',
            'foto_menu.mimes' => 'Foto menu harus berformat JPG, PNG, atau WEBP.',
            'foto_menu.extensions' => 'Ekstensi foto menu harus JPG, JPEG, PNG, atau WEBP.',
            'foto_menu.max' => 'Ukuran foto menu maksimal 3 MB.',
            'foto_menu.uploaded' => 'Foto menu gagal diunggah. Maksimal 3 MB.',
            'harga.max' => 'Harga menu terlalu besar. Maksimal Rp 9.999.999.999.',
        ]);

        $data['slug'] = str()->slug($data['nama_menu']) . '-' . strtolower(str()->random(4));
        $data['foto_menu'] = $this->simpanFotoMenu($request);

        try {
            Menu::create($data);
        } catch (\Throwable $e) {
            if ($data['foto_menu']) {
                Storage::disk('public')->delete($data['foto_menu']);
            }

            throw $e;
        }

        Realtime::emit(new MenuDiperbarui('menu'));

        return back()->with('sukses', 'Menu baru berhasil didaftarkan ke sistem!');
    }

    public function ubahMenu(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'nama_menu' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', Rule::exists('kategori', 'id')->where('jenis', 'menu')],
            'harga' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stok_menu' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'foto_menu' => $this->aturanFotoMenu(),
        ], [
            'foto_menu.image' => 'Foto menu harus berupa gambar yang valid.',
            'foto_menu.mimetypes' => 'Foto menu harus berformat JPG, PNG, atau WEBP.',
            'foto_menu.mimes' => 'Foto menu harus berformat JPG, PNG, atau WEBP.',
            'foto_menu.extensions' => 'Ekstensi foto menu harus JPG, JPEG, PNG, atau WEBP.',
            'foto_menu.max' => 'Ukuran foto menu maksimal 3 MB.',
            'foto_menu.uploaded' => 'Foto menu gagal diunggah. Maksimal 3 MB.',
            'harga.max' => 'Harga menu terlalu besar. Maksimal Rp 9.999.999.999.',
        ]);

        $fotoLama = $menu->foto_menu;
        $fotoBaru = $this->simpanFotoMenu($request);

        if ($fotoBaru) {
            $data['foto_menu'] = $fotoBaru;
        }

        try {
            $menu->update($data);
        } catch (\Throwable $e) {
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru);
            }

            throw $e;
        }

        if ($fotoBaru && $fotoLama) {
            Storage::disk('public')->delete($fotoLama);
        }

        Realtime::emit(new MenuDiperbarui('menu'));

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Menu berhasil diperbarui.']);
        }

        return back()->with('sukses', 'Menu berhasil diperbarui!');
    }

    public function ubahKetersediaanMenu(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'is_aktif' => ['required', 'boolean'],
        ]);

        $menu = Menu::findOrFail($id);
        $menu->update(['is_aktif' => $data['is_aktif']]);

        Realtime::emit(new MenuDiperbarui('ketersediaan'));

        return response()->json([
            'message' => $data['is_aktif']
                ? $menu->nama_menu . ' diaktifkan di e-menu.'
                : $menu->nama_menu . ' dinonaktifkan dari e-menu.',
            'menu' => [
                'id' => $menu->id,
                'is_aktif' => (bool) $menu->is_aktif,
                'stok_menu' => (int) $menu->stok_menu,
            ],
        ]);
    }

    public function hapusMenu(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $menu = Menu::findOrFail($id);

        if ($menu->detailPesanan()->exists()) {
            $pesan = 'Menu sudah dipakai transaksi, tidak bisa dihapus.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $pesan], 422);
            }

            return back()->withErrors(['menu' => $pesan]);
        }

        $foto = $menu->foto_menu;
        $menu->delete();

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }

        Realtime::emit(new MenuDiperbarui('menu'));

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Menu berhasil dihapus.']);
        }

        return back()->with('sukses', 'Menu berhasil dihapus!');
    }

    public function daftarKategori(): View
    {
        return $this->daftarMenu();
    }

    public function simpanKategori(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:60',
                Rule::unique('kategori', 'nama_kategori')->where('jenis', 'menu'),
            ],
            'target_kds' => ['required', 'in:dapur,bar'],
            'deskripsi' => ['nullable', 'string', 'max:191'],
        ]);

        Kategori::create($data + ['jenis' => 'menu']);

        return back()->with('sukses', 'Kategori baru berhasil ditambahkan!');
    }

    public function daftarMeja(): View
    {
        $daftarMeja = MejaMakan::orderBy('kode_meja')->get()->map(function ($meja) {
            $bill = Pesanan::with('detailPesanan')
                ->where('meja_id', $meja->id)
                ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
                ->latest('waktu_pesan')
                ->first();

            $meja->adaPanggilan = LogPanggilPelayan::where('meja_id', $meja->id)
                ->where('status_panggilan', 'menunggu')
                ->exists();
            $meja->status_meja = $bill ? 'terisi' : 'kosong';
            $meja->billKode = $bill ? $bill->kode_pesanan : '';
            $meja->billTotal = $bill ? (float) $bill->total_bayar : 0;
            $meja->itemCount = $bill ? $bill->detailPesanan->sum('jumlah') : 0;

            return $meja;
        });

        $detailJson = $daftarMeja->mapWithKeys(function ($meja) {
            $bill = Pesanan::with('detailPesanan.menu')
                ->where('meja_id', $meja->id)
                ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
                ->latest('waktu_pesan')
                ->first();

            $panggilanAktif = LogPanggilPelayan::where('meja_id', $meja->id)
                ->where('status_panggilan', 'menunggu')
                ->latest('waktu_panggil')
                ->first();

            $sesi = SesiMeja::aktifUntuk($meja);

            return [$meja->kode_meja => [
                'area' => $meja->area,
                'kapasitas' => $meja->kapasitas,
                'status' => $meja->status_meja,
                'panggilan' => $meja->adaPanggilan,
                'waktuPanggil' => $panggilanAktif && $panggilanAktif->waktu_panggil
                    ? $panggilanAktif->waktu_panggil->diffForHumans()
                    : null,
                'billKode' => $meja->billKode,
                'billTotal' => $meja->billTotal,
                'ip' => $meja->id_device ?? '-',
                'token' => $sesi->kode_unik,
                'url' => url('/meja/' . $meja->kode_meja),
                'items' => $bill ? $bill->detailPesanan->map(function ($detail) {
                    return [
                        'nama' => $detail->menu->nama_menu,
                        'jumlah' => $detail->jumlah,
                        'subtotal' => (float) $detail->subtotal,
                    ];
                })->values() : [],
            ]];
        });

        $mejaPertama = $daftarMeja->first() ? $daftarMeja->first()->kode_meja : null;

        $areaOptions = Kategori::untukArea()->orderBy('nama_kategori')->pluck('nama_kategori')
            ->merge($daftarMeja->pluck('area'))
            ->filter()
            ->unique()
            ->values();

        $kategoriList = Kategori::untukArea()->orderBy('nama_kategori')->get()->map(fn ($k) => [
            'id' => $k->id,
            'nama' => $k->nama_kategori,
            'jumlah_meja' => $daftarMeja->where('area', $k->nama_kategori)->count(),
        ])->values();

        $totalMeja = $daftarMeja->count();
        $terisi = $daftarMeja->where('status_meja', 'terisi')->count();
        $buzzerAktif = $daftarMeja->where('adaPanggilan', true)->count();

        $statistik = [
            'total_meja' => $totalMeja,
            'total_kapasitas' => (int) $daftarMeja->sum('kapasitas'),
            'jumlah_area' => $areaOptions->count(),
            'terisi' => $terisi,
            'tersedia' => $totalMeja - $terisi,
            'persen_terisi' => $totalMeja > 0 ? (int) round($terisi / $totalMeja * 100) : 0,
            'buzzer_aktif' => $buzzerAktif,
            'perangkat_terhubung' => $daftarMeja->filter(fn ($m) => filled($m->id_device))->count(),
            'qr_aktif' => SesiMeja::where('status', 'aktif')->distinct('meja_id')->count('meja_id'),
        ];

        return view('admin.master-meja.index', compact('daftarMeja', 'detailJson', 'mejaPertama', 'areaOptions', 'kategoriList', 'statistik'));
    }

    public function panggilPelayan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10'],
        ]);

        $meja = MejaMakan::where('kode_meja', $validated['kode_meja'])->firstOrFail();

        $panggilan = LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
        ]);

        Realtime::emit(new \App\Events\PanggilanPelayan([
            'id' => $panggilan->id,
            'meja_id' => $meja->id,
            'meja' => $meja->kode_meja,
            'area' => $meja->area,
            'status_panggilan' => 'menunggu',
        ]));

        return redirect()->route('admin.master-meja')->with('success', 'Panggilan pelayan untuk saung ' . $meja->kode_meja . ' terkirim.');
    }

    public function selesaikanPanggilan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10'],
        ]);

        $meja = MejaMakan::where('kode_meja', $validated['kode_meja'])->firstOrFail();

        LogPanggilPelayan::where('meja_id', $meja->id)
            ->where('status_panggilan', 'menunggu')
            ->update([
                'status_panggilan' => 'selesai',
                'waktu_selesai' => now(),
            ]);

        return redirect()->route('admin.master-meja')->with('success', 'Panggilan saung ' . $meja->kode_meja . ' diselesaikan.');
    }

    public function tambahArea(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_area' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori', 'nama_kategori')->where('jenis', 'area'),
            ],
        ]);

        Kategori::create([
            'jenis' => 'area',
            'nama_kategori' => $validated['nama_area'],
            'target_kds' => null,
        ]);

        return redirect()->route('admin.master-meja')->with('success', 'Kategori area berhasil ditambahkan.');
    }

    public function regenerasiToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10'],
        ]);

        $meja = MejaMakan::where('kode_meja', $validated['kode_meja'])->firstOrFail();
        $sesi = SesiMeja::aktifUntuk($meja);
        $sesi->update(['kode_unik' => SesiMeja::kodeUnikBaru($sesi->kode_unik)]);

        return redirect()->route('admin.master-meja')->with('success', 'Token saung ' . $meja->kode_meja . ' berhasil diregenerasi.');
    }

    public function cetakQrMeja(): \Illuminate\Http\Response
    {
        $meja = MejaMakan::orderBy('kode_meja')->get()->map(function ($m) {
            $m->kode_akses = SesiMeja::aktifUntuk($m)->kode_unik;
            $m->url_meja = url('/meja/' . $m->kode_meja);
            return $m;
        });

        $html = view('admin.master-meja.cetak-qr', compact('meja'))->render();
        return response($html, 200)->header('Content-Type', 'text/html');
    }

    public function simpanMeja(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10'],
            'kode_meja_lama' => ['nullable', 'string', 'max:10'],
            'area' => ['required', 'string', 'max:50'],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:20'],
            'id_device' => ['nullable', 'string', 'max:20'],
        ]);

        $kodeLama = $request->input('kode_meja_lama');

        if ($kodeLama) {
            $meja = MejaMakan::where('kode_meja', $kodeLama)->firstOrFail();
            if ($validated['kode_meja'] !== $kodeLama && MejaMakan::where('kode_meja', $validated['kode_meja'])->exists()) {
                return redirect()->route('admin.master-meja')->with('error', 'Kode meja sudah digunakan.');
            }
            $meja->kode_meja = $validated['kode_meja'];
            $meja->area = $validated['area'];
            $meja->kapasitas = $validated['kapasitas'] ?? $meja->kapasitas;
            $meja->id_device = $validated['id_device'] ?? $meja->id_device;
            if (!$meja->qr_token) {
                $meja->qr_token = strtoupper('QR-' . $meja->kode_meja . substr(str()->random(6), 0, 6));
            }
            $meja->save();
            return redirect()->route('admin.master-meja')->with('success', 'Meja berhasil diperbarui.');
        }

        if (MejaMakan::where('kode_meja', $validated['kode_meja'])->exists()) {
            return redirect()->route('admin.master-meja')->with('error', 'Kode meja sudah digunakan.');
        }

        MejaMakan::create([
            'kode_meja' => $validated['kode_meja'],
            'area' => $validated['area'],
            'kapasitas' => $validated['kapasitas'] ?? 6,
            'id_device' => $validated['id_device'] ?? null,
            'qr_token' => strtoupper('QR-' . $validated['kode_meja'] . substr(str()->random(6), 0, 6)),
            'status_meja' => 'kosong',
        ]);

        return redirect()->route('admin.master-meja')->with('success', 'Unit saung baru berhasil ditambahkan.');
    }

    public function hapusMeja(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10'],
        ]);

        $meja = MejaMakan::where('kode_meja', $validated['kode_meja'])->firstOrFail();

        if ($meja->pesanan()->exists()) {
            return redirect()->route('admin.master-meja')
                ->with('error', 'Meja ' . $meja->kode_meja . ' tidak bisa dihapus karena memiliki riwayat pesanan.');
        }

        $kode = $meja->kode_meja;

        $meja->logPanggil()->delete();
        $meja->delete();

        return redirect()->route('admin.master-meja')
            ->with('success', 'Meja ' . $kode . ' berhasil dihapus.');
    }

    public function hapusArea(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_area' => ['required', 'string', 'max:100'],
        ]);

        $kategori = Kategori::untukArea()
            ->where('nama_kategori', $validated['nama_area'])
            ->firstOrFail();

        $jumlahMeja = MejaMakan::where('area', $kategori->nama_kategori)->count();

        if ($jumlahMeja > 0) {
            return redirect()->route('admin.master-meja')
                ->with('error', 'Kategori ' . $kategori->nama_kategori . ' masih dipakai oleh ' . $jumlahMeja . ' meja.');
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()->route('admin.master-meja')
            ->with('success', 'Kategori ' . $nama . ' berhasil dihapus.');
    }

    public function daftarAkun(): View
    {
        $labelPeran = [
            'admin' => 'Admin',
            'kasir' => 'Kasir',
            'koki' => 'Koki',
            'barista' => 'Barista',
            'pelayan' => 'Pelayan',
        ];

        $lencana = [
            'admin' => 'owner',
            'kasir' => 'kasir',
            'koki' => 'kitchen',
            'barista' => 'kitchen',
            'pelayan' => 'runner',
        ];

        $latar = [
            'admin' => 'bg-[#0B4D2B] text-white',
            'kasir' => 'bg-[#B1F1C2] text-[#00341A]',
            'koki' => 'bg-[#CBEF89] text-[#131F00]',
            'barista' => 'bg-[#B0D270] text-[#364E00]',
            'pelayan' => 'bg-[#E5E2DB] text-[#1C1C18]',
        ];

        $pengguna = User::query()->orderBy('id')->get();
        $sekarang = now()->timestamp;
        $batasSesi = $sekarang - ((int) config('session.lifetime', 120) * 60);
        $batasOnline = $sekarang - 900;

        $sesiTerakhir = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $batasSesi)
            ->selectRaw('user_id, MAX(last_activity) as last_activity')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $penggunaOnline = $pengguna->filter(function (User $user) use ($sesiTerakhir, $batasOnline) {
            $sesi = $sesiTerakhir->get($user->id);

            return $user->is_aktif && $sesi && (int) $sesi->last_activity >= $batasOnline;
        });

        $ringkasanOnline = collect([
            'kasir' => 'Kasir',
            'koki' => 'Dapur',
            'barista' => 'Bar',
            'pelayan' => 'Mobile',
            'admin' => 'Admin',
        ])->map(function (string $label, string $role) use ($penggunaOnline) {
            $jumlah = $penggunaOnline->where('role', $role)->count();

            return $jumlah > 0 ? $jumlah . ' ' . $label : null;
        })->filter()->implode(', ');

        $jumlahAdminAktif = $pengguna
            ->where('role', 'admin')
            ->where('is_aktif', true)
            ->count();
        $jumlahAdmin = $pengguna->where('role', 'admin')->count();

        $stasiun = [
            'admin' => 'Konsol Owner / Admin',
            'kasir' => 'POS Kasir',
            'koki' => 'KDS Dapur',
            'barista' => 'KDS Bar',
            'pelayan' => 'Mobile POS • Floor',
        ];

        $stafJson = $pengguna->map(function (User $user) use ($lencana, $latar, $labelPeran, $sesiTerakhir, $batasOnline, $jumlahAdminAktif, $jumlahAdmin, $stasiun) {
            $kata = preg_split('/\s+/', trim($user->nama));
            $inisial = strtoupper(substr($kata[0] ?? '?', 0, 1) . (isset($kata[1]) ? substr(end($kata), 0, 1) : ''));
            $sesi = $sesiTerakhir->get($user->id);
            $online = $user->is_aktif && $sesi && (int) $sesi->last_activity >= $batasOnline;
            $standby = $user->is_aktif && $user->status_operasional === 'standby';
            $status = ! $user->is_aktif ? 'offline' : ($standby ? 'standby' : ($online ? 'online' : 'siap'));
            $statusText = ! $user->is_aktif
                ? 'Nonaktif'
                : ($standby ? 'Standby' : ($online ? 'Online' : 'Siap'));
            $area = $user->area ?: 'Semua Area';

            return [
                'id' => $user->id,
                'name' => $user->nama,
                'roleKey' => $user->role,
                'role' => $labelPeran[$user->role] ?? ucfirst($user->role),
                'roleBadge' => $lencana[$user->role] ?? 'runner',
                'email' => $user->email ?? '-',
                'phone' => $user->no_telp ?: '-',
                'area' => $area,
                'title' => ($labelPeran[$user->role] ?? ucfirst($user->role)) . ' • ' . $area,
                'device' => ($stasiun[$user->role] ?? 'Stasiun Staf') . ' • ' . $area,
                'status' => $status,
                'statusText' => $statusText,
                'isActive' => (bool) $user->is_aktif,
                'isSelf' => auth()->id() === $user->id,
                'isLastAdmin' => $user->role === 'admin'
                    && ($jumlahAdmin <= 1 || ($user->is_aktif && $jumlahAdminAktif <= 1)),
                'lastActive' => $sesi ? Carbon::createFromTimestamp((int) $sesi->last_activity)->timezone('Asia/Jakarta')->diffForHumans() : 'Belum ada sesi',
                'lastActiveTime' => $sesi ? Carbon::createFromTimestamp((int) $sesi->last_activity)->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB' : '-',
                'lastActiveTs' => $sesi ? (int) $sesi->last_activity : null,
                'avatar' => $inisial,
                'avatarBg' => $latar[$user->role] ?? 'bg-[#E5E2DB] text-[#1C1C18]',
            ];
        })->values();

        $jumlahPeran = collect($labelPeran)->mapWithKeys(
            fn ($label, $kunci) => [$kunci => $pengguna->where('role', $kunci)->count()]
        );

        $statistik = [
            'total' => $pengguna->count(),
            'aktif' => $pengguna->where('is_aktif', true)->count(),
            'nonaktif' => $pengguna->where('is_aktif', false)->count(),
            'online' => $penggunaOnline->count(),
            'ringkasan_online' => $ringkasanOnline !== '' ? $ringkasanOnline : 'Belum ada sesi staf aktif',
            'hash_driver' => (string) config('hashing.driver', 'bcrypt'),
        ];

        $sesiJson = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $batasSesi)
            ->orderByDesc('last_activity')
            ->get(['user_id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(function ($sesi) use ($pengguna, $labelPeran) {
                $user = $pengguna->firstWhere('id', $sesi->user_id);
                $agen = (string) $sesi->user_agent;
                $peramban = match (true) {
                    str_contains($agen, 'Edg/') => 'Microsoft Edge',
                    str_contains($agen, 'Chrome/') => 'Chrome',
                    str_contains($agen, 'Firefox/') => 'Firefox',
                    str_contains($agen, 'Safari/') => 'Safari',
                    default => 'Peramban',
                };
                $platform = match (true) {
                    str_contains($agen, 'Android') => 'Android',
                    str_contains($agen, 'iPhone') || str_contains($agen, 'iPad') => 'iOS',
                    str_contains($agen, 'Windows') => 'Windows',
                    str_contains($agen, 'Mac OS') => 'macOS',
                    str_contains($agen, 'Linux') => 'Linux',
                    default => 'Perangkat',
                };

                return [
                    'nama' => $user?->nama ?? 'Pengguna',
                    'peran' => $labelPeran[$user?->role] ?? 'Staf',
                    'ip' => $sesi->ip_address ?: '-',
                    'perangkat' => $peramban . ' • ' . $platform,
                    'terakhir' => Carbon::createFromTimestamp((int) $sesi->last_activity)->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB',
                    'terakhirTs' => (int) $sesi->last_activity,
                ];
            })
            ->values();

        return view('admin.manajemen-akun.index', compact(
            'stafJson',
            'labelPeran',
            'jumlahPeran',
            'statistik',
            'sesiJson'
        ));
    }

    public function simpanAkun(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,kasir,koki,barista,pelayan'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'area' => ['nullable', 'string', 'max:50'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_aktif'] = true;

        User::create($data);

        return back()->with('sukses', 'Akun staf baru berhasil didaftarkan.');
    }

    public function ubahAkun(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,kasir,koki,barista,pelayan'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'area' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if ($user->role === 'admin'
            && $user->is_aktif
            && $data['role'] !== 'admin'
            && $this->jumlahAdminAktif() <= 1) {
            return back()
                ->withErrors(['role' => 'Peran admin terakhir tidak dapat diubah sebelum ada admin aktif lain.'])
                ->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return back()->with('sukses', 'Data akun ' . $user->nama . ' berhasil diperbarui.');
    }

    public function ubahStatusAkun(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id() && $user->is_aktif) {
            return back()->withErrors(['akun' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan.']);
        }

        if ($user->role === 'admin' && $user->is_aktif && $this->jumlahAdminAktif() <= 1) {
            return back()->withErrors(['akun' => 'Admin aktif terakhir tidak dapat dinonaktifkan.']);
        }

        $user->update(['is_aktif' => ! $user->is_aktif]);

        return back()->with('sukses', 'Status akun ' . $user->nama . ' berhasil diubah menjadi ' . ($user->is_aktif ? 'aktif' : 'nonaktif') . '.');
    }

    public function hapusAkun(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['akun' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
        }

        if ($user->role === 'admin'
            && (User::query()->where('role', 'admin')->count() <= 1
                || ($user->is_aktif && $this->jumlahAdminAktif() <= 1))) {
            return back()->withErrors(['akun' => 'Admin terakhir tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($user) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)
                ->delete();

            if ($user->email) {
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            }

            $user->delete();
        });

        return back()->with('sukses', 'Akun ' . $user->nama . ' berhasil dihapus.');
    }

    private function jumlahAdminAktif(): int
    {
        return User::query()
            ->where('role', 'admin')
            ->where('is_aktif', true)
            ->count();
    }
}
