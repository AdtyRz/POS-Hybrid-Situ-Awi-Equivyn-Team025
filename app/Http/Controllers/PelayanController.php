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

class PelayanController extends Controller
{
    private function getPelayanArea(): ?string
    {
        return auth()->user()->area;
    }

    private function getMejaByArea(): \Illuminate\Database\Eloquent\Collection
    {
        $area = $this->getPelayanArea();
        if (! $area) {
            return MejaMakan::orderBy('kode_meja')->get();
        }
        return MejaMakan::where('area', $area)->orderBy('kode_meja')->get();
    }

    private function filterByArea($query)
    {
        $area = $this->getPelayanArea();
        if ($area) {
            return $query->whereHas('meja', fn($q) => $q->where('area', $area));
        }
        return $query;
    }

    public function notifikasiPanggilan(): View
    {
        $panggilan = $this->filterByArea(
            LogPanggilPelayan::with('meja')
        )->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();

        $siapAntar = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu'])
        )->where('status_pesanan', 'siap')
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

        $currentArea = $this->getPelayanArea() ?? 'Area Bawah (Situ Danau)';

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
                    : '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0 3.332.477 4.5 1.253"/></svg>',
                'timeAgo' => $p->waktu_pesan->diffForHumans(),
                'actionText' => $isBar ? 'Ambil & Antar ke ' . $p->meja->kode_meja : 'Tandai Selesai Diantarkan',
                'delivered' => false
            ];
        });

        $mejaArea = $this->getMejaByArea();
        $saungStatus = $mejaArea->map(function ($m) use ($panggilan) {
            $panggilanMeja = $panggilan->where('meja_id', $m->id)->first();
            if ($panggilanMeja) {
                return [
                    'code' => $m->kode_meja,
                    'dotColor' => 'bg-[#BA1A1A] animate-ping',
                    'status' => 'PANGGIL!',
                    'statusColor' => 'text-[#BA1A1A]',
                    'borderClass' => 'border-[#BA1A1A]/30 bg-[#FFDAD6]/60',
                ];
            }
            return [
                'code' => $m->kode_meja,
                'dotColor' => 'bg-[#213200]',
                'status' => 'Makan (' . $m->kapasitas . 'p)',
                'statusColor' => 'text-[#213200]',
                'borderClass' => 'border border-[#F0EEE7]',
            ];
        });

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
        $siapAntar = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        $pesananDiantar = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->where('status_pesanan', 'diantar')
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

        $selesaiCount = $this->filterByArea(
            Pesanan::where('status_pesanan', 'selesai')
        )->whereDate('waktu_pesan', today())
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
                'waktu_pesan' => $p->waktu_pesan->toISOString(),
                'timeAgo' => $p->waktu_pesan->diffForHumans(),
            ];
        });

        $readyCount = $siapAntar->count();
        $kitchenCount = $siapAntar->filter(fn($p) => ($p->detailPesanan->first()->menu->kategori->target_kds ?? 'dapur') === 'dapur')->count();
        $barCount = $siapAntar->filter(fn($p) => ($p->detailPesanan->first()->menu->kategori->target_kds ?? 'dapur') === 'bar')->count();
        $deliveringCount = $pesananDiantar->count();
        $finishedCount = $this->filterByArea(
            Pesanan::where('status_pesanan', 'selesai')
        )->whereDate('waktu_pesan', today())->count();

        $runnerArea = auth()->user()->area ?? 'Danau';

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
            'finishedCount',
            'runnerArea'
        ));
    }

    public function tabSaung(): View
    {
        $meja = $this->getMejaByArea();

        $panggilanAktif = $this->filterByArea(
            LogPanggilPelayan::with('meja')
        )->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();

        // Bug 1 FIX: Tambahkan eager-load 'detailPesanan.menu.kategori'
        // Sebelumnya hanya with(['meja', 'detailPesanan']) — tanpa menu & kategori,
        // sehingga $pesananMeja->total_bayar dan relasi menu crash saat diakses di closure.
        $pesananAktif = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap', 'diantar'])
            ->orderBy('waktu_pesan')
            ->get();

        // Build clusters matching frontend Alpine.js expected format
        $clusters = $meja->groupBy(function ($m) {
            return substr($m->kode_meja, 0, 2);
        })->map(function ($group, $prefix) use ($panggilanAktif, $pesananAktif) {
            $areaName = match($prefix) {
                'LB' => 'Lesehan Bawah',
                'LA' => 'Lesehan Atas',
                'KA' => 'Kursi Atas',
                default => $prefix
            };

            // Bug 2 FIX: Hitung count panggilan di cluster ini untuk key 'count'
            // yang dibutuhkan Alpine (x-text="cluster.count + ' Saung'")
            $clusterPanggilanCount = $group->filter(function ($m) use ($panggilanAktif) {
                return $panggilanAktif->where('meja_id', $m->id)->isNotEmpty();
            })->count();

            return [
                'key'   => strtolower($prefix),     // Alpine pakai lowercase: 'lb', 'la', 'ka'
                'name'  => $areaName,
                'count' => $group->count(),          // Bug 2 FIX: key 'count' yang hilang
                'color' => match($prefix) {          // Bug 2 FIX: key 'color' yang hilang
                    'LB'    => 'bg-[#0B4D2B]',
                    'LA'    => 'bg-[#7A5900]',
                    'KA'    => 'bg-[#404941]',
                    default => 'bg-[#213200]',
                },
                'meja'  => $group->map(function ($m) use ($panggilanAktif, $pesananAktif) {
                    $call       = $panggilanAktif->where('meja_id', $m->id)->first();
                    $isCalling  = (bool) $call;

                    $pesananMeja = $pesananAktif->where('meja_id', $m->id)->first();
                    $hasPesanan  = (bool) $pesananMeja;

                    $statusColor      = 'bg-[#213200]';
                    $statusLabel      = 'Tersedia';
                    $statusColorClass = 'text-[#213200]';
                    $billInfo         = '';
                    $requestNote      = null;
                    $timeAgo          = '';
                    $statusForTemplate = 'tersedia';

                    if ($isCalling) {
                        $statusColor       = 'bg-[#BA1A1A]';
                        $statusLabel       = 'PANGGIL!';
                        $statusColorClass  = 'text-[#BA1A1A]';
                        $statusForTemplate = 'panggilan';
                        $timeAgo           = $call->waktu_panggil->diffForHumans();
                        $requestNote       = 'Tamu butuh bantuan';
                    } elseif ($hasPesanan) {
                        $pesananStatus = $pesananMeja->status_pesanan;
                        if (in_array($pesananStatus, ['siap', 'diantar'])) {
                            $statusColor       = 'bg-[#0B4D2B]';
                            $statusLabel       = 'Siap Antar';
                            $statusColorClass  = 'text-[#0B4D2B]';
                            $statusForTemplate = 'siap_antar';
                        } elseif ($pesananStatus === 'diproses') {
                            $statusColor       = 'bg-[#7A5900]';
                            $statusLabel       = 'Masak (' . $pesananMeja->waktu_pesan->diffForHumans() . ')';
                            $statusColorClass  = 'text-[#7A5900]';
                            $statusForTemplate = 'masak';
                        } elseif ($pesananStatus === 'menunggu') {
                            $statusColor       = 'bg-[#213200]';
                            $statusLabel       = 'Makan';
                            $statusColorClass  = 'text-[#213200]';
                            $statusForTemplate = 'makan';
                        }
                        $timeAgo  = $pesananMeja->waktu_pesan->diffForHumans();
                        // Bug 3 FIX: null-safe operator (??) karena total_bayar bisa null
                        // jika pesanan belum dihitung / masih diproses
                        $billInfo = 'Rp' . number_format($pesananMeja->total_bayar ?? 0, 0, ',', '.');
                    }

                    return [
                        'id'             => $m->id,
                        'code'           => $m->kode_meja,
                        'number'         => (int) filter_var($m->kode_meja, FILTER_SANITIZE_NUMBER_INT),
                        'name'           => $m->kode_meja,
                        'isCalling'      => $isCalling,
                        'statusColor'    => $statusColor,
                        // Bug 4 FIX: Hapus key 'status' yang duplikat — PHP hanya simpan
                        // yang terakhir, menyebabkan nilai tidak konsisten
                        'status'         => $statusForTemplate,
                        'statusLabel'    => $statusLabel,
                        'billInfo'       => $billInfo,
                        'timeAgo'        => $timeAgo,
                        'requestNote'    => $requestNote,
                        'statusColorClass' => $statusColorClass,
                        'kapasitas'      => $m->kapasitas,
                        'area'           => $m->area,
                        'kode_meja'      => $m->kode_meja,
                    ];
                })->values(),
            ];
        })->values();

        $totalMeja      = $meja->count();
        $terisiCount    = $meja->where('status_meja', 'terisi')->count();
        $tersediaCount  = $meja->where('status_meja', 'kosong')->count();
        $panggilanCount = $panggilanAktif->count();
        $currentArea    = $this->getPelayanArea() ?? 'Tepi Danau (LB-01 – LB-05)';

        return view('pelayan.tab-saung.index', compact(
            'meja',
            'panggilanAktif',
            'pesananAktif',
            'clusters',
            'totalMeja',
            'terisiCount',
            'tersediaCount',
            'panggilanCount',
            'currentArea'
        ));
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

    public function ubahStatus(Request $request, $id): RedirectResponse|JsonResponse
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

        // Return JSON untuk AJAX request (fetch dari Alpine.js)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'ok',
                'message' => 'Status panggilan diperbarui',
                'panggilan' => [
                    'id'               => $log->id,
                    'status_panggilan' => $data['status_panggilan'],
                ],
            ]);
        }

        return back();
    }
}
