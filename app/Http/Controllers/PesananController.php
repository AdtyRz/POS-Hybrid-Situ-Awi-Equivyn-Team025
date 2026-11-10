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
        $data = $this->bangunDataKasir($request->query('meja'));

        return view('kasir.dashboard.index', array_merge($data, $this->pengaturanStruk()));
    }




    public function state(Request $request): JsonResponse
    {
        $data = $this->bangunDataKasir($request->query('meja'));

        return response()
            ->json([
                'saungs' => $data['saungsJson'],
                'iotAlert' => $data['iotAlertJson'],
                'selectedSaungId' => $data['selectedSaungId'],
                'serverTime' => now('Asia/Jakarta')->format('H:i:s'),
                'struk' => $this->pengaturanStruk(),
            ])
            ->header('Cache-Control', 'no-store, max-age=0');
    }




    /**
     * Pengaturan struk dipakai saat render awal dan saat polling realtime,
     * supaya perubahan admin langsung terpakai tanpa reload halaman kasir.
     */
    private function pengaturanStruk(): array
    {
        $pengaturan = Pengaturan::query()->pluck('nilai', 'kunci');
        $nilai = function (string $kunci, mixed $default = null) use ($pengaturan): mixed {
            $hasil = $pengaturan[$kunci] ?? null;

            return $hasil !== null
                ? $hasil
                : ($default ?? Pengaturan::DEFAULTS[$kunci] ?? null);
        };

        return [
            'printLogo' => filter_var($nilai('print_logo', '1'), FILTER_VALIDATE_BOOLEAN),
            'logoSrc' => Pengaturan::mediaUrl((string) $nilai('logo_path', '')),
            'receiptHeader' => $nilai('receipt_header', 'Saung Situ Awi - Ciwidey'),
            'receiptFooter' => $nilai('struk_footer', 'Hatur Nuhun Parantos Sumping'),
            'restoAddress' => $nilai('alamat_resto', 'Jl. Raya Situ Awi Km. 4, Ciwidey, Kabupaten Bandung'),
            'restoPhone' => $nilai('telepon_resto', '0812-3456-7890'),
            'paperSize' => $nilai('paper_size', '80mm') === '58mm' ? '58mm' : '80mm',
        ];
    }




    private function bangunDataKasir(?string $mejaDipilih = null): array
    {
        $mejaSemua = MejaMakan::orderBy('kode_meja')->get();



        $aktif = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
            ->orderBy('waktu_pesan')
            ->get()
            ->groupBy('meja_id');



        $paidOrders = Pesanan::with(['detailPesanan.menu'])
            ->whereIn('meja_id', $mejaSemua->pluck('id'))
            ->where('status_pembayaran', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->orderBy('waktu_pesan')
            ->get()
            ->groupBy('meja_id');

        // Prioritaskan sesi aktif agar struk hanya memuat pesanan sesi berjalan,
        // lalu fallback ke sesi terbaru untuk meja yang sedang kosong.
        $sesiTerakhir = SesiMeja::query()
            ->whereIn('meja_id', $mejaSemua->pluck('id'))
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get()
            ->unique('meja_id')
            ->keyBy('meja_id');

        $tunggu = LogPanggilPelayan::where('status_panggilan', 'menunggu')
            ->pluck('meja_id')
            ->toArray();

        $saungsJson = $mejaSemua->map(function ($m) use ($aktif, $tunggu, $paidOrders, $sesiTerakhir) {
            $sesi = $sesiTerakhir->get($m->id);
            $sesiAktif = $sesi !== null && $sesi->status === 'aktif';

            $dasar = [
                'id' => $m->kode_meja,
                'dbId' => $m->id,
                'cluster' => explode('-', $m->kode_meja)[0],
                'name' => 'SAUNG ' . $m->kode_meja,
                'tableName' => $m->area . ' ' . $m->kode_meja,
                'capacity' => $m->area . ' Kap. ' . $m->kapasitas . ' org',
                'status' => 'empty',
                'paymentType' => null,
                'paymentStatus' => null,
                'orderStatus' => null,
                'hasAlert' => in_array($m->id, $tunggu),
                'sessionActive' => $sesiAktif,
                'sessionCode' => $sesiAktif ? $sesi->kode_unik : null,
                'sessionOccupied' => false,
                'sessionOrderCount' => 0,
                'sessionTotal' => 0,
                'sessionUnpaidCount' => 0,
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
                'orders' => [],
                'orderCount' => 0,
                'unpaidOrderCount' => 0,
                'allOrdersPaid' => false,
                'receiptItems' => [],
                'receiptOrders' => [],
                'receiptOrderIds' => [],
                'receiptOrderId' => null,
                'receiptSubtotal' => 0,
                'receiptTotal' => 0,
                'receiptPaymentType' => null,
                'hasReceiptOrders' => false,
            ];

            $receiptOrders = $paidOrders->get($m->id, collect());

            if ($sesiTerakhir->has($m->id)) {
                $mulaiSesi = $sesiTerakhir->get($m->id)->dibuka_pada;

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
                'nama_pelanggan' => $pesanan->nama_pelanggan ?: 'Tamu',
                'waktu_pesan' => $pesanan->waktu_pesan?->timezone('Asia/Jakarta')->format('H:i'),
                'total_bayar' => (float) $pesanan->total_bayar,
                'metode_pembayaran' => $pesanan->metode_pembayaran,
                'subtotal' => (float) $pesanan->detailPesanan->sum('subtotal'),
                'printed' => $pesanan->printed_at !== null,
                'items' => $pesanan->detailPesanan->map(fn ($detail) => [
                    'name' => $detail->menu?->nama_menu ?? 'Menu',
                    'qty' => (int) $detail->jumlah,
                    'price' => (float) $detail->harga_satuan,
                ])->values(),
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
            $dasar['hasReceiptOrders'] = $receiptOrders->isNotEmpty();

            $orders = $aktif->get($m->id, collect())
                ->map(fn ($pesanan) => $this->payloadKasir($pesanan))
                ->values();

            $dasar['orders'] = $orders;
            $dasar['orderCount'] = $orders->count();
            $dasar['unpaidOrderCount'] = $orders->filter(
                fn ($order) => $order['status'] !== 'paid'
            )->count();




            $pesananSesi = collect();

            if ($sesiAktif) {
                $pesananSesi = $aktif->get($m->id, collect())
                    ->merge($paidOrders->get($m->id, collect()))
                    ->filter(fn ($pesanan) => $pesanan->waktu_pesan !== null
                        && $pesanan->waktu_pesan->greaterThanOrEqualTo($sesi->dibuka_pada))
                    ->unique('id')
                    ->sortBy('waktu_pesan')
                    ->values();

                $dasar['sessionOccupied'] = $pesananSesi->isNotEmpty();
                $dasar['sessionOrderCount'] = $pesananSesi->count();
                $dasar['sessionTotal'] = (float) $pesananSesi->sum('total_bayar');
                $dasar['sessionUnpaidCount'] = $pesananSesi->filter(
                    fn ($pesanan) => $pesanan->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR
                )->count();
            }

            $dasar['allOrdersPaid'] = $dasar['sessionOccupied']
                ? $dasar['sessionUnpaidCount'] === 0
                : ($orders->isNotEmpty() && $dasar['unpaidOrderCount'] === 0);

            if ($orders->isEmpty()) {
                if ($pesananSesi->isEmpty()) {
                    return $dasar;
                }

                $primarySesi = $pesananSesi->last();
                $payloadSesi = $this->payloadKasir($primarySesi);
                $metodeSesi = $pesananSesi->pluck('metode_pembayaran')->filter()->unique();

                $dasar['status'] = $dasar['allOrdersPaid']
                    ? 'paid'
                    : ($pesananSesi->contains(
                        fn ($pesanan) => $pesanan->status_pembayaran === Pesanan::PAYMENT_PENDING_VERIFIKASI
                    ) ? 'waiting_qris' : 'waiting_cash');
                $dasar['paymentType'] = $metodeSesi->count() > 1
                    ? 'campuran'
                    : $metodeSesi->first();
                $dasar['paymentStatus'] = $dasar['allOrdersPaid']
                    ? Pesanan::PAYMENT_SUDAH_BAYAR
                    : $payloadSesi['paymentStatus'];
                $dasar['orderStatus'] = $payloadSesi['orderStatus'];
                $dasar['orderId'] = $payloadSesi['orderId'];
                $dasar['kodePesanan'] = $payloadSesi['kodePesanan'];
                $dasar['customer'] = $payloadSesi['customer'];
                $dasar['qrTime'] = $payloadSesi['qrTime'];
                $dasar['elapsedTime'] = $payloadSesi['elapsedTime'];
                $dasar['itemCount'] = (int) $pesananSesi->sum(
                    fn ($pesanan) => $pesanan->detailPesanan->sum('jumlah')
                );
                $dasar['totalPortions'] = $dasar['itemCount'];
                $dasar['subtotal'] = (float) $pesananSesi->sum(
                    fn ($pesanan) => $pesanan->detailPesanan->sum('subtotal')
                );
                $dasar['total'] = $dasar['sessionTotal'];
                $dasar['items'] = $payloadSesi['items'];
                $dasar['unpaidOrderCount'] = $dasar['sessionUnpaidCount'];

                return $dasar;
            }

            $primary = $this->pilihOrderKasir($orders);

            $dasar['status'] = $this->statusGabunganKasir($orders);
            $dasar['paymentType'] = $dasar['allOrdersPaid']
                ? ($orders->pluck('paymentType')->filter()->unique()->count() > 1
                    ? 'campuran'
                    : $primary['paymentType'])
                : $primary['paymentType'];
            $dasar['paymentStatus'] = $primary['paymentStatus'];
            $dasar['orderStatus'] = $primary['orderStatus'];
            $dasar['orderId'] = $primary['orderId'];
            $dasar['kodePesanan'] = $primary['kodePesanan'];
            $dasar['customer'] = $primary['customer'] ?? ('Tamu ' . $m->kode_meja);
            $dasar['qrTime'] = $primary['qrTime'];
            $dasar['elapsedTime'] = $orders->first()['elapsedTime'] ?? $primary['elapsedTime'];
            $dasar['itemCount'] = (int) $orders->sum('itemCount');
            $dasar['totalPortions'] = (int) $orders->sum('totalPortions');
            $dasar['subtotal'] = (float) $orders->sum('subtotal');
            $dasar['total'] = (float) $orders->sum('total');
            $dasar['items'] = $primary['items'];

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


        if ($mejaDipilih && $saungsJson->contains('id', $mejaDipilih)) {
            $selectedSaungId = $mejaDipilih;
        }

        return compact('saungsJson', 'iotAlertJson', 'selectedSaungId');
    }




    private function payloadKasir(Pesanan $pesanan): array
    {
        $detail = $pesanan->detailPesanan;
        $waktu = $pesanan->waktu_pesan;
        $status = $this->statusKasirPesanan($pesanan);

        $selisihMenit = $waktu
            ? intdiv(max(0, now()->getTimestamp() - $waktu->getTimestamp()), 60)
            : null;

        if ($selisihMenit === null) {
            $elapsed = '-';
        } elseif ($selisihMenit < 1) {
            $elapsed = 'Baru saja';
        } elseif ($selisihMenit < 60) {
            $elapsed = $selisihMenit . ' mnt lalu';
        } elseif ($selisihMenit < 1440) {
            $elapsed = intdiv($selisihMenit, 60) . ' jam lalu';
        } else {
            $elapsed = intdiv($selisihMenit, 1440) . ' hari lalu';
        }

        return [
            'kodePesanan' => $pesanan->kode_pesanan,
            'orderId' => $pesanan->kode_pesanan,
            'status' => $status,
            'paymentType' => $pesanan->metode_pembayaran,
            'paymentStatus' => $pesanan->status_pembayaran,
            'orderStatus' => $pesanan->status_pesanan,
            'customer' => $pesanan->nama_pelanggan ?: 'Tamu ' . ($pesanan->meja?->kode_meja ?? '-'),
            'qrTime' => $waktu?->timezone('Asia/Jakarta')->format('H:i') ?? '-',
            'elapsedTime' => $elapsed,
            'itemCount' => (int) $detail->sum('jumlah'),
            'totalPortions' => (int) $detail->sum('jumlah'),
            'subtotal' => (float) $detail->sum('subtotal'),
            'total' => (float) $pesanan->total_bayar,
            'items' => $detail->map(fn ($item) => [
                'name' => $item->menu?->nama_menu ?? 'Menu',
                'qty' => (int) $item->jumlah,
                'price' => (float) $item->harga_satuan,
                'note' => $item->catatan ? 'Catatan: ' . $item->catatan : 'Tanpa catatan',
                'noteColor' => 'text-[#6B6A63]',
            ])->values(),
        ];
    }

    private function statusKasirPesanan(Pesanan $pesanan): string
    {
        $statusMap = [
            Pesanan::STATUS_MENUNGGU => 'waiting_cash',
            Pesanan::STATUS_MENUNGGU_VERIFIKASI => 'waiting_qris',
            Pesanan::STATUS_MENUNGGU_PEMBAYARAN => 'waiting_cash',
            Pesanan::STATUS_DIPROSES => 'preparing',
            Pesanan::STATUS_SIAP => 'ready',
            Pesanan::STATUS_DIANTAR => 'delivering',
            Pesanan::STATUS_SELESAI => 'completed',
            Pesanan::STATUS_DIBATALKAN => 'cancelled',
        ];

        $status = $statusMap[$pesanan->status_pesanan] ?? 'empty';

        $masihMenunggu = in_array($pesanan->status_pesanan, [
            Pesanan::STATUS_MENUNGGU,
            Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            Pesanan::STATUS_MENUNGGU_VERIFIKASI,
        ], true);

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR && ! $masihMenunggu) {
            return 'paid';
        }

        return $status;
    }

    private function pilihOrderKasir($orders): array
    {
        return $orders->reverse()->first(fn ($order) => $order['status'] === 'waiting_cash')
            ?? $orders->reverse()->first(fn ($order) => $order['status'] === 'waiting_qris')
            ?? $orders->reverse()->first(fn ($order) => $order['status'] !== 'paid')
            ?? $orders->last();
    }

    private function statusGabunganKasir($orders): string
    {
        if ($orders->contains(fn ($order) => $order['status'] === 'waiting_cash')) {
            return 'waiting_cash';
        }

        if ($orders->contains(fn ($order) => $order['status'] === 'waiting_qris')) {
            return 'waiting_qris';
        }




        $belumLunas = $orders->filter(fn ($order) => $order['status'] !== 'paid');

        if ($belumLunas->isNotEmpty()) {
            return $belumLunas->last()['status'] ?? 'empty';
        }

        return 'paid';
    }

    public function create(): View
    {
        $meja = MejaMakan::where('status_meja', 'kosong')->orderBy('kode_meja')->get();
        $menu = Menu::where('is_aktif', true)->where('stok_menu', '>', 0)->orderBy('nama_menu')->get();

        return view('kasir.pesanan.tambah', compact('meja', 'menu'));
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

        return view('shared.pesanan.show', compact('pesanan'));
    }

    public function ubahStatus(Request $request, string $kode): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:menunggu,diproses,siap,diantar,selesai,dibatalkan'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        if (
            $request->user()?->role === 'pelayan'
            && filled($request->user()->area)
            && $pesanan->meja?->area !== $request->user()->area
        ) {
            $pesan = 'Pesanan ini berada di luar area tugas Anda.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $pesan], 403);
            }

            abort(403, $pesan);
        }




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



        if ($data['status_pesanan'] === Pesanan::STATUS_DIPROSES
            && in_array($pesanan->status_pesanan, [
                Pesanan::STATUS_MENUNGGU_VERIFIKASI,
                Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            ], true)
            && $pesanan->status_pembayaran !== Pesanan::PAYMENT_SUDAH_BAYAR) {
            $pesan = 'Pesanan belum lunas. Selesaikan pembayaran sebelum mengirim ke dapur/bar.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $pesan], 422);
            }

            return back()->withErrors(['status_pesanan' => $pesan]);
        }

        if ($data['status_pesanan'] === 'dibatalkan') {
            if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
                $pesan = 'Pesanan yang sudah lunas tidak dapat dibatalkan.';

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => $pesan], 422);
                }

                return back()->withErrors(['status_pesanan' => $pesan]);
            }

            try {
                app(PesananService::class)->batalkan(
                    $pesanan,
                    auth()->id(),
                    $data['alasan'] ?? 'Pesanan dibatalkan.',
                );
            } catch (\RuntimeException $exception) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => $exception->getMessage()], 422);
                }

                return back()->withErrors(['status_pesanan' => $exception->getMessage()]);
            }
        } else {
            DB::transaction(function () use ($pesanan, $data) {
                $pesanan->update(['status_pesanan' => $data['status_pesanan']]);
            });
        }





        SesiMeja::sinkronkanStatusMeja($pesanan->meja_id);

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja->kode_meja ?? null,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));


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
