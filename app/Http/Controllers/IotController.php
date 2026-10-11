<?php

namespace App\Http\Controllers;

use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IotController extends Controller
{
    public function antrean(): View
    {
        $panggilan = LogPanggilPelayan::with('meja')
            ->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();

        $siapAntar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        return view('pelayan.panggilan', compact('panggilan', 'siapAntar'));
    }

    public function tabAntar(): View
    {
        $panggilan = LogPanggilPelayan::with('meja')
            ->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();

        $siapAntar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        $diantar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'diantar')
            ->orderBy('waktu_pesan')
            ->get();

        $selesaiCount = Pesanan::where('status_pesanan', 'selesai')
            ->whereDate('waktu_pesan', today())
            ->count();

        return view('pelayan.tab-antar.index', compact('panggilan', 'siapAntar', 'diantar', 'selesaiCount'));
    }

    public function tabSaung(): View
    {
        $meja = MejaMakan::with(['pesananAktif.detailPesanan.menu', 'logPanggil' => function ($q) {
            $q->where('status_panggilan', 'menunggu')->latest();
        }])->orderBy('kode_meja')->get();

        $panggilanAktif = LogPanggilPelayan::with('meja')
            ->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();

        $totalMeja = $meja->count();
        $terisiCount = $meja->filter(fn($m) => $m->status_meja === 'terisi')->count();
        $tersediaCount = $meja->filter(fn($m) => $m->status_meja === 'tersedia' || $m->status_meja === 'kosong')->count();
        $panggilanCount = $panggilanAktif->count();

        $clusters = [
            [
                'key' => 'danau',
                'name' => 'Cluster A • Tepi Danau',
                'color' => 'bg-[#00341A]',
                'count' => 5,
                'meja' => $meja->filter(fn($m) => in_array(substr($m->kode_meja, 0, 2), ['LB']))->values()->map(function ($m) {
                    $panggilan = $m->logPanggil->first();
                    $pesananAktif = $m->pesananAktif->first();
                    $isCalling = $panggilan && $panggilan->status_panggilan === 'menunggu';
                    $status = 'tersedia';
                    $statusColor = 'bg-[#B1F1C2] text-[#00210E]';
                    $borderClass = 'bg-[#F6F3EC] border border-[#E5E2DB]';
                    $statusText = 'Tersedia';
                    $timeAgo = '';
                    $requestNote = null;
                    $orderSummary = '';
                    $itemCount = 0;
                    $cookingProgress = 0;
                    $cookingItem = '';
                    $puckInfo = '';

                    if ($pesananAktif) {
                        if ($pesananAktif->status_pesanan === 'siap' || $pesananAktif->status_pesanan === 'diantar') {
                            $status = 'siap_antar';
                            $statusColor = 'bg-[#FFDEA1] text-[#5C4300]';
                            $borderClass = 'bg-white border border-[#F0EEE7]';
                            $statusText = 'Siap Antar';
                            $orderSummary = $pesananAktif->detailPesanan->sum('jumlah') . ' Item';
                            $timeAgo = $pesananAktif->waktu_pesan->diffForHumans();
                        } elseif ($pesananAktif->status_pesanan === 'diproses') {
                            $status = 'makan';
                            $statusColor = 'text-[#00341A]';
                            $puckInfo = 'Puck LCD: "Selamat Menikmati"';
                            $timeAgo = $pesananAktif->waktu_pesan->diffForHumans();
                        } elseif ($pesananAktif->status_pesanan === 'menunggu') {
                            $status = 'masak';
                            $statusColor = 'bg-[#7A5900]';
                            $borderClass = 'bg-white border border-[#F0EEE7]';
                            $statusText = 'Masak Dapur';
                            $cookingProgress = rand(40, 90);
                            $cookingItem = 'Gurame Bakar Cobek & Karedok';
                            $timeAgo = $pesananAktif->waktu_pesan->diffForHumans();
                        }
                    }

                    if ($isCalling) {
                        $status = 'calling';
                        $statusColor = 'bg-[#BA1A1A]';
                        $borderClass = 'border-[#BA1A1A]/30 bg-[#FFDAD6]/60';
                        $statusText = 'PANGGIL!';
                        $requestNote = 'Minta sendok ekstra & sambal terasi dadak';
                        $timeAgo = $panggilan->waktu_panggil->diffForHumans();
                    }

                    return [
                        'id' => $m->id,
                        'code' => $m->kode_meja,
                        'number' => substr($m->kode_meja, 3),
                        'name' => $m->kode_meja . ' ' . $m->area,
                        'status' => $status,
                        'statusColor' => $statusColor,
                        'borderClass' => $borderClass,
                        'statusText' => $statusText,
                        'timeAgo' => $timeAgo,
                        'isCalling' => $isCalling,
                        'requestNote' => $requestNote,
                        'orderSummary' => $orderSummary,
                        'itemCount' => $itemCount,
                        'cookingProgress' => $cookingProgress,
                        'cookingItem' => $cookingItem,
                        'puckInfo' => $puckInfo,
                    ];
                }),
            ],
            [
                'key' => 'balong',
                'name' => 'Cluster B • Balong Indah',
                'color' => 'bg-[#7A5900]',
                'count' => 2,
                'meja' => $meja->filter(fn($m) => in_array(substr($m->kode_meja, 0, 2), ['LA']))->values()->map(function ($m) {
                    $pesananAktif = $m->pesananAktif->first();
                    return [
                        'id' => $m->id,
                        'code' => $m->kode_meja,
                        'number' => 'A' . substr($m->kode_meja, 3),
                        'name' => $m->kode_meja . ' ' . $m->area,
                        'status' => $pesananAktif ? 'diantar' : 'tersedia',
                        'statusColor' => $pesananAktif ? 'text-[#2E6A45]' : 'bg-[#B1F1C2] text-[#00210E]',
                        'borderClass' => $pesananAktif ? 'bg-white border border-[#F0EEE7]' : 'bg-[#F6F3EC] border border-[#E5E2DB]',
                        'statusText' => $pesananAktif ? 'Diantar' : 'Tersedia',
                        'timeAgo' => $pesananAktif ? $pesananAktif->waktu_pesan->diffForHumans() : '',
                        'isCalling' => false,
                        'orderSummary' => $pesananAktif ? $pesananAktif->detailPesanan->sum('jumlah') . ' Item' : '',
                        'itemCount' => $pesananAktif ? $pesananAktif->detailPesanan->sum('jumlah') : 0,
                    ];
                }),
            ],
            [
                'key' => 'kayu',
                'name' => 'Cluster C • Meja Kursi Kayu',
                'color' => 'bg-[#707971]',
                'count' => 4,
                'meja' => $meja->filter(fn($m) => in_array(substr($m->kode_meja, 0, 2), ['KA']))->values()->map(function ($m) {
                    $pesananAktif = $m->pesananAktif->first();
                    $status = $pesananAktif ? 'makan' : 'tersedia';
                    return [
                        'id' => $m->id,
                        'code' => $m->kode_meja,
                        'number' => substr($m->kode_meja, 3),
                        'name' => 'KA-' . substr($m->kode_meja, 3),
                        'status' => $status,
                        'statusColor' => $status === 'tersedia' ? 'bg-[#B1F1C2] text-[#00210E]' : 'text-[#0B4D2B]',
                        'borderClass' => $status === 'tersedia' ? 'bg-[#F6F3EC] border border-[#E5E2DB]' : 'bg-white border border-[#F0EEE7]',
                        'statusText' => $status === 'tersedia' ? 'Tersedia' : 'Santap Makan',
                        'timeAgo' => $pesananAktif ? $pesananAktif->waktu_pesan->diffForHumans() : '',
                        'isCalling' => false,
                        'billInfo' => $pesananAktif ? 'Bill #' . $pesananAktif->kode_pesanan . ' • ' . $pesananAktif->detailPesanan->sum('jumlah') . ' Tamu' : 'Kapasitas ' . $m->kapasitas . ' Org',
                        'orderSummary' => $pesananAktif ? $pesananAktif->detailPesanan->sum('jumlah') . ' Item' : '',
                        'puckInfo' => $status === 'makan' ? 'Puck LCD: "Selamat Menikmati"' : '',
                    ];
                }),
            ],
        ];

        $currentArea = 'Tepi Danau (LB-01 – LB-05)';
        $panggilanCount = $panggilanAktif->count();
        $terisiCount = $meja->filter(fn($m) => $m->status_meja === 'terisi')->count();
        $tersediaCount = $meja->filter(fn($m) => $m->status_meja !== 'terisi')->count();
        $totalMeja = $meja->count();

        return view('pelayan.tab-saung.index', compact('meja', 'panggilanAktif', 'clusters', 'currentArea', 'panggilanCount', 'terisiCount', 'tersediaCount', 'totalMeja'));
    }

    public function tabShift(): View
    {
        $meja = MejaMakan::with(['pesananAktif.detailPesanan.menu', 'logPanggil' => function ($q) {
            $q->where('status_panggilan', 'menunggu')->latest();
        }])->orderBy('kode_meja')->get();

        $panggilanSelesai = LogPanggilPelayan::where('status_panggilan', 'selesai')
            ->whereDate('waktu_panggil', today())
            ->count();

        $pesananDiantar = Pesanan::where('status_pesanan', 'selesai')
            ->whereDate('waktu_pesan', today())
            ->count();

        $avgRespon = LogPanggilPelayan::where('status_panggilan', 'selesai')
            ->whereDate('waktu_panggil', today())
            ->whereNotNull('waktu_selesai')
            ->get()
            ->avg(function ($log) {
                return $log->waktu_panggil->diffInMinutes($log->waktu_selesai);
            });

        $shiftRemaining = '3 Jam 28 Mnt';

        return view('pelayan.tab-shift.index', compact('meja', 'panggilanSelesai', 'pesananDiantar', 'avgRespon', 'shiftRemaining'));
    }

    public function notifikasiPanggilan(): View
    {
        $panggilan = LogPanggilPelayan::with('meja')
            ->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();

        $siapAntar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        $panggilanSelesai = LogPanggilPelayan::where('status_panggilan', 'selesai')
            ->whereDate('waktu_panggil', today())
            ->count();

        $pesananDiantar = Pesanan::where('status_pesanan', 'selesai')
            ->whereDate('waktu_pesan', today())
            ->count();

        $avgRespon = LogPanggilPelayan::where('status_panggilan', 'selesai')
            ->whereDate('waktu_panggil', today())
            ->whereNotNull('waktu_selesai')
            ->get()
            ->avg(function ($log) {
                return $log->waktu_panggil->diffInMinutes($log->waktu_selesai);
            });

        $currentArea = 'Area Bawah (Situ Danau)';

        $siapAntarFormatted = $siapAntar->map(function ($p) {
            $firstItem = $p->detailPesanan->first();
            $isBar = $firstItem && $firstItem->menu->kategori->target_kds === 'bar';
            return [
                'id' => $p->id,
                'kode_pesanan' => $p->kode_pesanan,
                'meja' => ['kode_meja' => $p->meja->kode_meja, 'area' => $p->meja->area],
                'source' => $isBar ? 'bar' : 'dapur',
                'sourceLabel' => $isBar ? 'Bar Counter Beverage' : 'Meja Pass Dapur Utama',
                'mainItem' => $firstItem ? $firstItem->jumlah . 'x ' . $firstItem->menu->nama_menu : 'Item',
                'sideItems' => $p->detailPesanan->count() > 1 ? '+' . ($p->detailPesanan->count() - 1) . ' item' : '',
                'note' => $firstItem && $firstItem->catatan ? 'Catatan: ' . $firstItem->catatan : 'Tanpa catatan',
                'icon' => $isBar ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path stroke-linecap="round" d="M9 9h6M9 15h6"/></svg>' : '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253"/></svg>',
                'timeAgo' => $p->waktu_pesan->diffForHumans(),
                'actionText' => $isBar ? 'Ambil & Antar ke ' . $p->meja->kode_meja : 'Tandai Selesai Diantarkan',
                'delivered' => false
            ];
        });

        $saungStatus = [
    ['code' => 'LB-01', 'dotColor' => 'bg-[#213200]', 'status' => 'Makan (4p)', 'statusColor' => 'text-[#213200]', 'borderClass' => 'border border-[#F0EEE7]'],
    ['code' => 'LB-02', 'dotColor' => 'bg-[#BA1A1A] animate-ping', 'status' => 'PANGGIL!', 'statusColor' => 'text-[#BA1A1A]', 'borderClass' => 'border-[#BA1A1A]/30 bg-[#FFDAD6]/60'],
    ['code' => 'LB-03', 'dotColor' => 'bg-[#707971]', 'status' => 'Siap Lap', 'statusColor' => 'text-[#404941]', 'borderClass' => 'border-[#E5E2DB] bg-white/60 opacity-60'],
    ['code' => 'LB-04', 'dotColor' => 'bg-[#7A5900]', 'status' => 'Masak (14m)', 'statusColor' => 'text-[#7A5900]', 'borderClass' => 'border border-[#F0EEE7]'],
    ['code' => 'LB-05', 'dotColor' => 'bg-[#0B4D2B]', 'status' => 'Siap Antar', 'statusColor' => 'text-[#00341A]', 'borderClass' => 'border border-[#F0EEE7]'],
    ['code' => 'LA-01', 'dotColor' => 'bg-[#2E6A45]', 'status' => 'Diantar', 'statusColor' => 'text-[#404941]', 'borderClass' => 'border border-[#F0EEE7]'],
];

return view('pelayan.notifikasi-panggilan-pelayan.index', compact('panggilan', 'siapAntar', 'siapAntarFormatted', 'panggilanSelesai', 'pesananDiantar', 'avgRespon', 'currentArea', 'saungStatus'));
    }

    public function ubahStatus(Request $request, $id): RedirectResponse
    {
        $data = $request->validate([
            'status_panggilan' => ['required', 'in:menunggu,ditangani,selesai'],
        ]);

        $log = LogPanggilPelayan::findOrFail($id);

        $isi = ['status_panggilan' => $data['status_panggilan']];

        if ($request->user()) {
            $isi['pelayan_id'] = $request->user()->id;
        }

        if ($data['status_panggilan'] === 'selesai') {
            $isi['waktu_selesai'] = now();
        }

        $log->update($isi);

        return back();
    }

    public function panggilPelayan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kode_meja' => ['required', 'string'],
        ]);

        $meja = MejaMakan::where('kode_meja', $data['kode_meja'])->first();

        if (! $meja) {
            return response()->json(['status' => 'error', 'message' => 'Meja tidak ditemukan'], 404);
        }

        $log = LogPanggilPelayan::create([
            'meja_id' => $meja->id,
            'status_panggilan' => 'menunggu',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Panggilan dari meja berhasil dikirim ke server.',
            'data' => $log,
        ], 200);
    }
}