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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $hariIni = today();

        $pendapatan = Pesanan::whereDate('waktu_pesan', $hariIni)
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');

        $kemarin = today()->subDay();
        $pendapatanKemarin = Pesanan::whereDate('waktu_pesan', $kemarin)
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');

        $pesananHariIni = Pesanan::whereDate('waktu_pesan', $hariIni)
            ->whereNotIn('status_pesanan', ['dibatalkan'])
            ->count();
        $pesananTotal = Pesanan::whereDate('waktu_pesan', $hariIni)
            ->whereNotIn('status_pesanan', ['dibatalkan'])
            ->count();

        $mejaTerisi = MejaMakan::where('status_meja', 'terisi')->count();
        $mejaTotal = MejaMakan::count();
        $stokMenipis = Menu::where('stok_menu', '<=', 5)->count();

        $menuMakananTersedia = Menu::where('stok_menu', '>', 0)
            ->whereHas('kategori', function ($q) {
                $q->where('target_kds', 'dapur');
            })
            ->count();
        $menuMinumanTersedia = Menu::where('stok_menu', '>', 0)
            ->whereHas('kategori', function ($q) {
                $q->where('target_kds', 'bar');
            })
            ->count();
        $menuTersediaTotal = $menuMakananTersedia + $menuMinumanTersedia;

        $sesiAktif = SesiMeja::aktif()->count();

        $rataMenit = DetailPesanan::where('status_item', 'siap')
            ->whereDate('detail_pesanan.updated_at', $hariIni)
            ->join('pesanan', 'pesanan.id', '=', 'detail_pesanan.pesanan_id')
            ->avg(DB::raw('EXTRACT(EPOCH FROM (detail_pesanan.updated_at - pesanan.waktu_pesan)) / 60'));

        $qrisHariIni = Pesanan::whereDate('waktu_pesan', $hariIni)
            ->where('metode_pembayaran', 'qris')
            ->whereNotIn('status_pesanan', ['dibatalkan'])
            ->count();
        $tunaiHariIni = Pesanan::whereDate('waktu_pesan', $hariIni)
            ->where(function ($query) {
                $query->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran');
            })
            ->whereNotIn('status_pesanan', ['dibatalkan'])
            ->count();

        $omzetQris = (float) Pesanan::whereDate('waktu_pesan', $hariIni)
            ->where('metode_pembayaran', 'qris')
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');
        $omzetTunai = (float) Pesanan::whereDate('waktu_pesan', $hariIni)
            ->where(function ($query) {
                $query->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran');
            })
            ->where('status_pembayaran', 'sudah_bayar')
            ->sum('total_bayar');

        $putaran = Pesanan::selectRaw('meja_id, AVG(EXTRACT(EPOCH FROM (updated_at - waktu_pesan)) / 60) as menit')
            ->whereDate('waktu_pesan', $hariIni)
            ->where('status_pesanan', 'selesai')
            ->with('meja')
            ->groupBy('meja_id')
            ->orderBy('menit')
            ->first();

        $terlaris = DetailPesanan::selectRaw('menu_id, SUM(jumlah) as porsi, SUM(subtotal) as omzet')
            ->whereHas('pesanan', function ($query) use ($hariIni) {
                $query->whereDate('waktu_pesan', $hariIni)->whereNotIn('status_pesanan', ['dibatalkan']);
            })
            ->with('menu')
            ->groupBy('menu_id')
            ->orderByDesc('porsi')
            ->limit(5)
            ->get();

        $grafik = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = today()->subDays($i);
            $total = (float) Pesanan::whereDate('waktu_pesan', $tanggal)
                ->where('status_pembayaran', 'sudah_bayar')
                ->sum('total_bayar');
            $jumlahPesanan = Pesanan::whereDate('waktu_pesan', $tanggal)
                ->whereNotIn('status_pesanan', ['dibatalkan'])
                ->count();
            $grafik[] = [
                'label' => $tanggal->format('d M'),
                'total' => $total,
                'jumlah' => $jumlahPesanan,
            ];
        }
        $grafik = array_reverse($grafik);

        $logAudit = Pesanan::with(['meja', 'kasir', 'detailPesanan.menu'])
            ->whereNotIn('status_pesanan', ['dibatalkan'])
            ->orderByDesc('waktu_pesan')
            ->limit(4)
            ->get()
            ->map(function ($pesanan) {
                $namaMenu = $pesanan->detailPesanan->take(2)->map(fn ($d) => $d->menu->nama_menu)->filter()->join(', ');
                $jumlahItem = $pesanan->detailPesanan->sum('jumlah');
                $metode = $pesanan->metode_pembayaran === 'qris' ? 'QRIS Lunas' : ($pesanan->status_pembayaran === 'sudah_bayar' ? 'Tunai Kasir Lunas' : 'Menunggu Pembayaran');

                return [
                    'kode' => '#' . $pesanan->kode_pesanan,
                    'meja' => $pesanan->meja ? ($pesanan->meja->area . ' (' . $pesanan->meja->kode_meja . ')') : '-',
                    'detail' => ($pesanan->kasir ? $pesanan->kasir->nama : 'Tamu') . ' • ' . $jumlahItem . ' Item' . ($namaMenu ? ' (' . $namaMenu . ')' : ''),
                    'total' => (float) $pesanan->total_bayar,
                    'metode' => $metode,
                    'waktu' => $pesanan->waktu_pesan ? $pesanan->waktu_pesan->format('H:i') : '-',
                ];
            });

        return view('admin.analitik-laporan.index', compact(
            'pendapatan', 'pendapatanKemarin', 'pesananHariIni', 'pesananTotal',
            'mejaTerisi', 'mejaTotal', 'stokMenipis', 'grafik',
            'rataMenit', 'qrisHariIni', 'tunaiHariIni', 'omzetQris', 'omzetTunai', 'terlaris',
            'putaran', 'menuMakananTersedia', 'menuMinumanTersedia', 'menuTersediaTotal', 'sesiAktif', 'logAudit'
        ));
    }

    public function cetakAnalitikPdf(Request $request): View
    {
        $periode = $request->get('periode', 'hari_ini');
        $hariIni = today();

        if ($periode === 'mingguan') {
            $start = today()->startOfWeek();
            $end = today()->endOfWeek();
        } elseif ($periode === 'bulanan') {
            $start = today()->startOfMonth();
            $end = today()->endOfMonth();
        } else {
            $start = $hariIni;
            $end = $hariIni;
        }

        $pendapatan = Pesanan::whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->where('status_pembayaran', 'sudah_bayar')->sum('total_bayar');
        $pesananHariIni = Pesanan::whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->whereNotIn('status_pesanan', ['dibatalkan'])->count();
        $qrisHariIni = Pesanan::whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->where('metode_pembayaran', 'qris')->whereNotIn('status_pesanan', ['dibatalkan'])->count();
        $tunaiHariIni = Pesanan::whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->where(function ($q) { $q->where('metode_pembayaran', 'tunai')->orWhereNull('metode_pembayaran'); })->whereNotIn('status_pesanan', ['dibatalkan'])->count();
        $terlaris = DetailPesanan::selectRaw('menu_id, SUM(jumlah) as porsi, SUM(subtotal) as omzet')
            ->whereHas('pesanan', function ($q) use ($start, $end) { $q->whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->whereNotIn('status_pesanan', ['dibatalkan']); })
            ->with('menu')
            ->groupBy('menu_id')
            ->orderByDesc('porsi')
            ->limit(5)
            ->get();
        $logAudit = Pesanan::with(['meja', 'kasir'])->whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->whereNotIn('status_pesanan', ['dibatalkan'])->orderByDesc('waktu_pesan')->limit(15)->get();

        $labelPeriode = $periode === 'mingguan' ? 'Mingguan (' . $start->format('d M') . ' - ' . $end->format('d M Y') . ')' : ($periode === 'bulanan' ? 'Bulanan (' . $start->format('M Y') . ')' : 'Harian (' . $hariIni->format('d M Y') . ')');

        return view('admin.analitik-laporan.cetak', compact('hariIni', 'pendapatan', 'pesananHariIni', 'qrisHariIni', 'tunaiHariIni', 'terlaris', 'logAudit', 'labelPeriode', 'start', 'end'));
    }

    public function eksporAnalitikXlsx(Request $request): StreamedResponse
    {
        $periode = $request->get('periode', 'hari_ini');
        $hariIni = today();

        if ($periode === 'mingguan') {
            $start = today()->startOfWeek();
            $end = today()->endOfWeek();
            $filename = 'laporan-analitik-mingguan-' . $start->format('Y-m-d') . '.xls';
        } elseif ($periode === 'bulanan') {
            $start = today()->startOfMonth();
            $end = today()->endOfMonth();
            $filename = 'laporan-analitik-bulanan-' . $start->format('Y-m') . '.xls';
        } else {
            $start = $hariIni;
            $end = $hariIni;
            $filename = 'laporan-analitik-harian-' . $hariIni->format('Y-m-d') . '.xls';
        }

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

            Pesanan::with(['meja', 'kasir'])->whereBetween(DB::raw('DATE(waktu_pesan)'), [$start->toDateString(), $end->toDateString()])->orderBy('waktu_pesan')->chunk(500, function ($pesananList) use ($end) {
                foreach ($pesananList as $p) {
                    $metode = $p->metode_pembayaran === 'qris' ? 'QRIS' : 'TUNAI';
                    echo '<tr>';
                    echo '<td>' . optional($p->waktu_pesan)->format('d/m/Y') . '</td>';
                    echo '<td>#' . $p->kode_pesanan . '</td>';
                    echo '<td>' . ($p->meja ? $p->meja->kode_meja . ' (' . $p->meja->area . ')' : '-') . '</td>';
                    echo '<td>' . ($p->kasir ? $p->kasir->nama : '-') . '</td>';
                    echo '<td>' . $metode . '</td>';
                    echo '<td>' . strtoupper(str_replace('_', ' ', $p->status_pembayaran)) . '</td>';
                    echo '<td>' . (int) $p->total_bayar . '</td>';
                    echo '<td>' . optional($p->waktu_pesan)->format('H:i:s') . '</td>';
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
                    'time' => $pesanan->waktu_pesan->format('H.i') . ' WIB',
                    'table' => $pesanan->meja->area . ' (' . $pesanan->meja->kode_meja . ')',
                    'items' => $jumlahItem . ' Item (' . $namaMenu . ')',
                    'method' => $batal ? 'VOID Kasir' : ($pesanan->metode_pembayaran === 'qris' ? 'QRIS Midtrans' : 'Tunai Kasir'),
                    'subtotal' => (int) round($pesanan->total_bayar / 1.1),
                    'pb1' => (int) ($pesanan->total_bayar - round($pesanan->total_bayar / 1.1)),
                    'total' => (float) $pesanan->total_bayar,
                    'cashier' => $pesanan->kasir ? $pesanan->kasir->nama : '-',
                    'status' => $batal ? 'void' : 'valid',
                    'statusText' => $batal ? 'VOID Approved' : 'Match/Valid',
                ];
            })->values();

        return view('admin.laporan-penjualan.index', ['transaksiJson' => $transaksi]);
    }

    public function daftarMenu(): View
    {
        $menuJson = Menu::with('kategori')->orderBy('nama_menu')->get()->map(function ($menu) {
            $stok = $menu->stok_menu;

            if ($stok <= 0) {
                $status = 'habis';
                $statusText = 'HABIS / Nonaktif';
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
                'badge' => $menu->kategori->nama_kategori,
                'price' => (float) $menu->harga,
                'hpp' => 0,
                'category' => 'k' . $menu->kategori_id,
                'kds' => $menu->kategori->target_kds === 'dapur' ? 'KDS Dapur (Makanan)' : 'KDS Bar (Minuman)',
                'kdsType' => $menu->kategori->target_kds,
                'stockQty' => $stok . ' Porsi',
                'stockNote' => '',
                'stockStatus' => $stockStatus,
                'status' => $status,
                'statusText' => $statusText,
                'enabled' => $stok > 0,
            ];
        })->values();

        $kategoriJson = Kategori::orderBy('id')->get()->map(function ($kategori) {
            return ['id' => 'k' . $kategori->id, 'name' => $kategori->nama_kategori];
        })->values();

        $kategoriForm = Kategori::orderBy('nama_kategori')->get();

        return view('admin.master-menu.index', compact('menuJson', 'kategoriJson', 'kategoriForm'));
    }

    public function simpanMenu(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_menu' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok_menu' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $data['slug'] = str()->slug($data['nama_menu']) . '-' . strtolower(str()->random(4));

        Menu::create($data);

        Realtime::emit(new MenuDiperbarui('menu'));

        return back()->with('sukses', 'Menu baru berhasil didaftarkan ke sistem!');
    }

    public function ubahMenu(Request $request, string $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'nama_menu' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok_menu' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $menu->update($data);

        Realtime::emit(new MenuDiperbarui('menu'));

        return back()->with('sukses', 'Menu berhasil diperbarui!');
    }

    public function hapusMenu(string $id): RedirectResponse
    {
        $menu = Menu::findOrFail($id);

        if ($menu->detailPesanan()->exists()) {
            return back()->withErrors(['menu' => 'Menu sudah dipakai transaksi, tidak bisa dihapus.']);
        }

        $menu->delete();

        Realtime::emit(new MenuDiperbarui('menu'));

        return back()->with('sukses', 'Menu berhasil dihapus!');
    }

    public function daftarKategori(): View
    {
        return $this->daftarMenu();
    }

    public function simpanKategori(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:60', 'unique:kategori,nama_kategori'],
            'target_kds' => ['required', 'in:dapur,bar'],
            'deskripsi' => ['nullable', 'string', 'max:191'],
        ]);

        Kategori::create($data);

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

            return [$meja->kode_meja => [
                'area' => $meja->area,
                'kapasitas' => $meja->kapasitas,
                'status' => $meja->status_meja,
                'panggilan' => $meja->adaPanggilan,
                'billKode' => $meja->billKode,
                'billTotal' => $meja->billTotal,
                'ip' => $meja->id_device ?? '-',
                'token' => $meja->qr_token,
                'items' => $bill ? $bill->detailPesanan->map(function ($detail) {
                    return [
                        'nama' => $detail->menu->nama_menu,
                        'jumlah' => $detail->jumlah,
                        'subtotal' => (float) $detail->subtotal,
                    ];
                })->values() : [],
            ]];
        });

        $mejaPertama = $daftarMeja->first()->kode_meja;

        return view('admin.master-meja.index', compact('daftarMeja', 'detailJson', 'mejaPertama'));
    }

    public function simpanMeja(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10', 'unique:meja_makan,kode_meja'],
            'area' => ['required', 'in:Lesehan Bawah,Lesehan Atas,Kursi Atas'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:20'],
            'id_device' => ['nullable', 'string', 'max:20', 'unique:meja_makan,id_device'],
        ]);

        $data['qr_token'] = strtoupper('QR-' . $data['kode_meja'] . substr(str()->random(8), 0, 8));

        MejaMakan::create($data);

        return back()->with('sukses', 'Unit saung baru berhasil ditambahkan!');
    }

    public function daftarAkun(): View
    {
        $lencana = [
            'admin' => 'owner',
            'kasir' => 'kasir',
            'koki' => 'kitchen',
            'barista' => 'kitchen',
            'pelayan' => 'runner',
            'pelanggan' => 'runner',
        ];

        $latar = [
            'admin' => 'bg-[#0B4D2B] text-white',
            'kasir' => 'bg-[#B1F1C2] text-[#00341A]',
            'koki' => 'bg-[#CBEF89] text-[#131F00]',
            'barista' => 'bg-[#B0D270] text-[#364E00]',
            'pelayan' => 'bg-[#E5E2DB] text-[#1C1C18]',
            'pelanggan' => 'bg-[#E5E2DB] text-[#1C1C18]',
        ];

        $stafJson = User::orderBy('id')->get()->map(function ($user) use ($lencana, $latar) {
            $kata = explode(' ', trim($user->nama));
            $inisial = strtoupper(substr($kata[0], 0, 1) . (isset($kata[1]) ? substr(end($kata), 0, 1) : ''));

            return [
                'id' => $user->id,
                'name' => $user->nama,
                'title' => ucfirst($user->role),
                'roleKey' => $user->role,
                'role' => ucfirst($user->role),
                'roleBadge' => $lencana[$user->role] ?? 'runner',
                'email' => $user->email ?? '-',
                'device' => $user->no_telp ?? '-',
                'status' => $user->is_aktif ? 'online' : 'offline',
                'statusText' => $user->is_aktif ? 'Aktif' : 'Nonaktif',
                'lastActive' => '-',
                'lastActiveTime' => '',
                'avatar' => $inisial,
                'avatarBg' => $latar[$user->role] ?? 'bg-[#E5E2DB] text-[#1C1C18]',
            ];
        })->values();

        return view('admin.manajemen-akun.index', compact('stafJson'));
    }

    public function simpanAkun(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,kasir,koki,barista,pelayan,pelanggan'],
            'no_telp' => ['nullable', 'string', 'max:20'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_aktif'] = true;

        User::create($data);

        return back()->with('sukses', 'Akun staf baru berhasil didaftarkan!');
    }

    public function ubahStatusAkun(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $user->update(['is_aktif' => ! $user->is_aktif]);

        return back()->with('sukses', 'Status akun berhasil diubah!');
    }
}
