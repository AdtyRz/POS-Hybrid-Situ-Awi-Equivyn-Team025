<?php

namespace App\Http\Controllers;

use App\Events\PesananBaru;
use App\Events\StatusPesananBerubah;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;
use App\Services\PesananService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $aktif = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
            ->orderByDesc('waktu_pesan')
            ->get()
            ->keyBy('meja_id');

        $mejaSemua = MejaMakan::orderBy('kode_meja')->get();

        $paidUnprinted = Pesanan::with(['detailPesanan.menu'])
            ->whereIn('meja_id', $mejaSemua->pluck('id'))
            ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->whereNull('printed_at')
            ->orderBy('waktu_pesan')
            ->get()
            ->groupBy('meja_id');

        $sesiAktif = SesiMeja::query()
            ->whereIn('meja_id', $mejaSemua->pluck('id'))
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('meja_id');

        $tunggu = LogPanggilPelayan::where('status_panggilan', 'menunggu')
            ->pluck('meja_id')
            ->toArray();

        $saungsJson = $mejaSemua->map(function ($m) use ($aktif, $tunggu, $paidUnprinted, $sesiAktif) {
            $dasar = [
                'id' => $m->kode_meja,
                'dbId' => $m->id,
                'cluster' => explode('-', $m->kode_meja)[0],
                'name' => 'SAUNG ' . $m->kode_meja,
                'tableName' => $m->area . ' ' . $m->kode_meja,
                'capacity' => $m->area . ' Kap. ' . $m->kapasitas . ' org',
                'status' => 'empty',
                'paymentType' => null,
                'hasAlert' => in_array($m->id, $tunggu),
                'orderId' => '-',
                'kodePesanan' => null,
                'customer' => '-',
                'qrTime' => '-',
                'elapsedTime' => '-',
                'itemCount' => 0,
                'totalPortions' => 0,
                'subtotal' => 0,
                'total' => 0,
                'items' => [],
                'receiptItems' => [],
                'receiptOrders' => [],
                'receiptOrderIds' => [],
                'receiptOrderId' => null,
                'receiptSubtotal' => 0,
                'receiptTotal' => 0,
                'receiptPaymentType' => null,
                'hasUnprintedPaid' => false,
            ];

            $receiptOrders = $paidUnprinted->get($m->id, collect());

            if ($sesiAktif->has($m->id)) {
                $mulaiSesi = $sesiAktif->get($m->id)->dibuka_pada;

                $receiptOrders = $receiptOrders->filter(
                    fn ($pesanan) => $pesanan->waktu_pesan === null
                        || $pesanan->waktu_pesan->greaterThanOrEqualTo($mulaiSesi)
                );
            }

            $receiptItems = $receiptOrders->flatMap(function ($pesanan) {
                return $pesanan->detailPesanan->map(fn ($detail) => [
                    'name' => $detail->menu?->nama_menu ?? 'Menu',
                    'qty' => (int) $detail->jumlah,
                    'price' => (float) $detail->harga_satuan,
                    'note' => $detail->catatan ? 'Catatan: ' . $detail->catatan : 'Tanpa catatan',
                    'noteColor' => 'text-[#6B6A63]',
                ]);
            })->values();

            $receiptOrderIds = $receiptOrders->pluck('kode_pesanan')->values();

            $dasar['receiptItems'] = $receiptItems;
            $dasar['receiptOrders'] = $receiptOrders->map(fn ($pesanan) => [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'waktu_pesan' => $pesanan->waktu_pesan?->timezone('Asia/Jakarta')->format('H:i'),
                'total_bayar' => (float) $pesanan->total_bayar,
                'metode_pembayaran' => $pesanan->metode_pembayaran,
            ])->values();
            $dasar['receiptOrderIds'] = $receiptOrderIds;
            $dasar['receiptOrderId'] = $receiptOrderIds->implode(' / ');
            $dasar['receiptSubtotal'] = (float) $receiptOrders->sum(
                fn ($pesanan) => $pesanan->detailPesanan->sum('subtotal')
            );
            $dasar['receiptTotal'] = (float) $receiptOrders->sum('total_bayar');
            $dasar['receiptPaymentType'] = $receiptOrders
                ->pluck('metode_pembayaran')
                ->filter()
                ->unique()
                ->implode(' / ');
            $dasar['hasUnprintedPaid'] = $receiptOrders->isNotEmpty();

            $p = $aktif->get($m->id);

            if (! $p) {
                return $dasar;
            }

            $subtotal = (float) $p->detailPesanan->sum('subtotal');
            $total = (float) $p->total_bayar;

            $statusMap = [
                Pesanan::STATUS_MENUNGGU             => 'waiting_cash',
                Pesanan::STATUS_MENUNGGU_VERIFIKASI  => 'waiting_qris',
                Pesanan::STATUS_MENUNGGU_PEMBAYARAN  => 'waiting_cash',
                Pesanan::STATUS_DIPROSES             => 'preparing',
                Pesanan::STATUS_SIAP                 => 'ready',
                Pesanan::STATUS_DIANTAR              => 'delivering',
                Pesanan::STATUS_SELESAI              => 'completed',
                Pesanan::STATUS_DIBATALKAN           => 'cancelled',
            ];

            $alpineStatus = $statusMap[$p->status_pesanan] ?? 'empty';

            // Bug C FIX: Jika pesanan sudah dibayar (status_pembayaran = sudah_bayar)
            // dan bukan lagi menunggu pembayaran, kategorikan sebagai 'paid' di kasir
            // agar saung masuk ke filter "Sudah Lunas" dan panel kanan ter-lock.
            $sudahBayar    = $p->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR;
            $masihMenunggu = in_array($p->status_pesanan, [
                Pesanan::STATUS_MENUNGGU,
                Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
                Pesanan::STATUS_MENUNGGU_VERIFIKASI,
            ]);
            if ($sudahBayar && ! $masihMenunggu) {
                $alpineStatus = 'paid';
            }

            $dasar['status']      = $alpineStatus;
            $dasar['paymentType'] = $p->metode_pembayaran;
            $dasar['paymentStatus'] = $p->status_pembayaran;
            $dasar['orderStatus'] = $p->status_pesanan;
            $dasar['orderId'] = $p->kode_pesanan;
            $dasar['kodePesanan'] = $p->kode_pesanan;
            $dasar['customer'] = 'Tamu ' . $m->kode_meja;
            $dasar['qrTime'] = $p->waktu_pesan->timezone('Asia/Jakarta')->format('H:i');
            $selisihMenit = intdiv(max(0, now()->getTimestamp() - $p->waktu_pesan->getTimestamp()), 60);

            if ($selisihMenit < 1) {
                $dasar['elapsedTime'] = 'Baru saja';
            } elseif ($selisihMenit < 60) {
                $dasar['elapsedTime'] = $selisihMenit . ' mnt lalu';
            } elseif ($selisihMenit < 1440) {
                $dasar['elapsedTime'] = intdiv($selisihMenit, 60) . ' jam lalu';
            } else {
                $dasar['elapsedTime'] = intdiv($selisihMenit, 1440) . ' hari lalu';
            }
            $dasar['itemCount'] = (int) $p->detailPesanan->sum('jumlah');
            $dasar['totalPortions'] = (int) $p->detailPesanan->sum('jumlah');
            $dasar['subtotal'] = $subtotal;
            $dasar['total'] = $total;
            $dasar['items'] = $p->detailPesanan->map(function ($d) {
                return [
                    'name' => $d->menu->nama_menu,
                    'qty' => (int) $d->jumlah,
                    'price' => (float) $d->harga_satuan,
                    'note' => $d->catatan ? 'Catatan: ' . $d->catatan : 'Tanpa catatan',
                    'noteColor' => 'text-[#6B6A63]',
                ];
            })->values();

            return $dasar;
        })->values();

        $panggil = LogPanggilPelayan::with('meja')
            ->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->first();

        if ($panggil) {
            $iotAlertJson = [
                'active' => true,
                'id' => $panggil->id,
                'saungId' => $panggil->meja->kode_meja,
                'title' => 'PANGGILAN PELAYAN AKTIF: SAUNG ' . $panggil->meja->kode_meja . ' (' . $panggil->meja->area . ')',
                'subtitle' => 'Panggilan masuk (' . $panggil->waktu_panggil->timezone('Asia/Jakarta')->format('H:i:s') . ') Tamu butuh bantuan pelayan',
            ];
        } else {
            $iotAlertJson = [
                'active' => false,
                'id' => null,
                'saungId' => null,
                'title' => '',
                'subtitle' => '',
            ];
        }

        $statusPriority = [
            'waiting_qris' => 1,
            'waiting_cash' => 2,
            'preparing' => 3,
            'ready' => 4,
            'delivering' => 5,
            'completed' => 6,
            'cancelled' => 7,
            'empty' => 8,
        ];

        $pertama = $saungsJson->sortBy(fn ($s) => $statusPriority[$s['status']] ?? 99)->first();
        $selectedSaungId = $pertama ? $pertama['id'] : null;

        // Bila kasir baru menyelesaikan pembayaran, kembalikan fokus ke meja tersebut.
        $mejaDipilih = $request->query('meja');

        if ($mejaDipilih && $saungsJson->contains('id', $mejaDipilih)) {
            $selectedSaungId = $mejaDipilih;
        }

        $printLogo = filter_var(Pengaturan::nilai('print_logo'), FILTER_VALIDATE_BOOLEAN);
        $logoSrc = Pengaturan::logoUrl();
        $receiptHeader = Pengaturan::nilai('receipt_header', 'Saung Situ Awi - Ciwidey');
        $receiptFooter = Pengaturan::nilai('struk_footer', 'Hatur Nuhun Parantos Sumping');
        $restoAddress = Pengaturan::nilai('alamat_resto', 'Jl. Raya Situ Awi Km. 4, Ciwidey, Kabupaten Bandung');
        $restoPhone = Pengaturan::nilai('telepon_resto', '0812-3456-7890');

        return view('kasir.index', compact(
            'saungsJson',
            'iotAlertJson',
            'selectedSaungId',
            'printLogo',
            'logoSrc',
            'receiptHeader',
            'receiptFooter',
            'restoAddress',
            'restoPhone',
        ));
    }

    public function create(): View
    {
        $meja = MejaMakan::where('status_meja', 'kosong')->orderBy('kode_meja')->get();
        $menu = Menu::where('is_aktif', true)->where('stok_menu', '>', 0)->orderBy('nama_menu')->get();

        return view('kasir.tambah', compact('meja', 'menu'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'meja_id' => ['required', 'exists:meja_makan,id'],
            'metode_pembayaran' => ['nullable', 'in:tunai,qris'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $dipilih = collect($data['jumlah'])->filter(fn ($j) => $j > 0);

        if ($dipilih->isEmpty()) {
            return back()->withErrors(['jumlah' => 'Pilih minimal satu menu.'])->withInput();
        }

        $meja = MejaMakan::findOrFail($data['meja_id']);

        try {
            $pesanan = app(PesananService::class)->buat(
                $meja,
                $dipilih->all(),
                [],
                [
                    'asal' => 'kasir',
                    'metode' => $data['metode_pembayaran'] ?? 'tunai',
                    'kasir_id' => auth()->id(),
                    'catatan' => $data['catatan'] ?? null,
                ],
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['jumlah' => $exception->getMessage()])->withInput();
        }

        Realtime::emit(new PesananBaru([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $meja->kode_meja,
            'meja_id' => $meja->id,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'total_bayar' => (float) $pesanan->total_bayar,
        ]));

        return redirect()->route('kasir.pembayaran.create', $pesanan->kode_pesanan);
    }

    public function show(string $kode): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja', 'pembayaran'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        return view('pesanan.show', compact('pesanan'));
    }

    public function ubahStatus(Request $request, string $kode): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:menunggu,diproses,siap,diantar,selesai,dibatalkan'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        // Bug 3 FIX: Tambahkan semua status yang didefinisikan di model
        // Status menunggu_pembayaran & menunggu_verifikasi berasal dari e-menu checkout
        // Tanpa ini, PATCH ke /pesanan/{kode}/status crash dengan undefined array key
        $alur = [
            'menunggu'              => ['diproses', 'dibatalkan'],
            'menunggu_pembayaran'   => ['diproses', 'dibatalkan'],
            'menunggu_verifikasi'   => ['diproses', 'dibatalkan'],
            'diproses'              => ['siap', 'dibatalkan'],
            'siap'                  => ['diantar', 'dibatalkan'],
            'diantar'               => ['selesai', 'dibatalkan'],
            'selesai'               => [],
            'dibatalkan'            => [],
        ];

        if (! in_array($data['status_pesanan'], $alur[$pesanan->status_pesanan])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Perpindahan status tidak diizinkan.'], 422);
            }
            return back()->withErrors(['status_pesanan' => 'Perpindahan status tidak diizinkan.']);
        }

        if ($data['status_pesanan'] === 'dibatalkan') {
            app(PesananService::class)->batalkan(
                $pesanan,
                auth()->id(),
                $data['alasan'] ?? 'Pesanan dibatalkan.',
            );
        } else {
            DB::transaction(function () use ($pesanan, $data) {
                $pesanan->update(['status_pesanan' => $data['status_pesanan']]);
            });
        }

        $masihAdaPesananAktif = Pesanan::query()
            ->where('meja_id', $pesanan->meja_id)
            ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->exists();

        if (! $masihAdaPesananAktif) {
            $pesanan->meja()->update(['status_meja' => 'kosong']);
        }

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja->kode_meja ?? null,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));
        // Bug 2 FIX: Return JSON untuk AJAX request (fetch dari Alpine tab-antar)
        // Return redirect untuk form biasa (kasir dashboard)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'ok',
                'message' => 'Status pesanan diperbarui',
                'pesanan' => [
                    'kode_pesanan'   => $pesanan->kode_pesanan,
                    'status_pesanan' => $data['status_pesanan'],
                ],
            ]);
        }

        return back();
    }
}
