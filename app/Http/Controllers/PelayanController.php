<?php

namespace App\Http\Controllers;

use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelayanController extends Controller
{
    public function notifikasiPanggilan(): View
    {
        $panggilan = LogPanggilPelayan::with('meja')
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderBy('waktu_panggil')
            ->get();

        $siapAntar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        $callsAnswered = LogPanggilPelayan::where('status_panggilan', 'selesai')
            ->whereDate('waktu_panggil', today())
            ->count();

        $ordersDelivered = Pesanan::where('status_pesanan', 'selesai')
            ->whereDate('waktu_pesan', today())
            ->count();

        $avgResponse = LogPanggilPelayan::where('status_panggilan', 'selesai')
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
                'icon' => $isBar
                    ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path stroke-linecap="round" d="M9 9h6M9 15h6"/></svg>'
                    : '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477 4.5 1.253"/></svg>',
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

        return view('pelayan.notifikasi-panggilan-pelayan.index', compact(
            'panggilan',
            'siapAntar',
            'siapAntarFormatted',
            'callsAnswered',
            'ordersDelivered',
            'avgResponse',
            'currentArea',
            'saungStatus'
        ));
    }

    public function tabAntar(): View
    {
        $siapAntar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        $pesananDiantar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'diantar')
            ->orderBy('waktu_pesan')
            ->get();

        $deliveringRunners = $pesananDiantar->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => 'Runner ' . substr($p->meja->kode_meja, 0, 2),
                'initials' => strtoupper(substr($p->meja->kode_meja, 0, 2)),
                'currentTask' => $p->meja->kode_meja . ' (' . $p->detailPesanan->sum('jumlah') . 'x ' . $p->detailPesanan->first()->menu->nama_menu . ')',
                'elapsedTime' => $p->waktu_pesan->diffForHumans()
            ];
        });

        $selesaiCount = Pesanan::where('status_pesanan', 'selesai')
            ->whereDate('waktu_pesan', today())
            ->count();

        $siapAntarFormatted = $siapAntar->map(function ($p) {
            $firstItem = $p->detailPesanan->first();
            $isBar = $firstItem && $firstItem->menu->kategori->target_kds === 'bar';
            return [
                'id' => $p->id,
                'kode_pesanan' => $p->kode_pesanan,
                'meja' => ['kode_meja' => $p->meja->kode_meja, 'area' => $p->meja->area],
                'detailPesanan' => $p->detailPesanan->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'menu' => ['nama_menu' => $d->menu->nama_menu, 'kategori' => ['target_kds' => $d->menu->kategori->target_kds ?? 'dapur']],
                        'jumlah' => $d->jumlah,
                        'catatan' => $d->catatan
                    ];
                }),
                'waktu_pesan' => $p->waktu_pesan->toISOString()
            ];
        });

        $readyCount = $siapAntar->count();
        $kitchenCount = $siapAntar->filter(fn($p) => ($p->detailPesanan->first()->menu->kategori->target_kds ?? 'dapur') === 'dapur')->count();
        $barCount = $siapAntar->filter(fn($p) => ($p->detailPesanan->first()->menu->kategori->target_kds ?? 'dapur') === 'bar')->count();
        $deliveringCount = $pesananDiantar->count();
        $finishedCount = Pesanan::where('status_pesanan', 'selesai')->whereDate('waktu_pesan', today())->count();

        return view('pelayan.tab-antar.index', compact(
            'siapAntar',
            'pesananDiantar',
            'deliveringRunners',
            'selesaiCount',
            'siapAntarFormatted',
            'readyCount',
            'kitchenCount',
            'barCount',
            'deliveringCount',
            'finishedCount'
        ));
    }

    public function tabSaung(): View
    {
        $meja = MejaMakan::orderBy('kode_meja')->get();

        $panggilanAktif = LogPanggilPelayan::with('meja')
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->orderBy('waktu_panggil')
            ->get();

        $pesananAktif = Pesanan::with(['meja', 'detailPesanan'])
            ->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap', 'diantar'])
            ->orderBy('waktu_pesan')
            ->get();

        return view('pelayan.tab-saung.index', compact('meja', 'panggilanAktif', 'pesananAktif'));
    }

    public function tabShift(): View
    {
        $pelayan = auth()->user();
        
        $shiftAktif = LogPanggilPelayan::where('pelayan_id', $pelayan->id)
            ->whereDate('waktu_panggil', today())
            ->count();

        $panggilanDitangani = LogPanggilPelayan::where('pelayan_id', $pelayan->id)
            ->where('status_panggilan', 'selesai')
            ->whereDate('waktu_selesai', today())
            ->count();

        return view('pelayan.tab-shift.index', compact('shiftAktif', 'panggilanDitangani'));
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
}
