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

        return view('pelayan.notifikasi-panggilan-pelayan.index', compact('panggilan', 'siapAntar', 'panggilanSelesai', 'pesananDiantar', 'avgRespon', 'currentArea'));
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