<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function index(Request $request, string $target = 'dapur')
    {
        $antrean = $this->ambilAntrean($target);

        if ($request->expectsJson()) {
            return response()->json($antrean, 200);
        }

        return view('kds.index', [
            'target' => $target,
            'item' => $antrean['data'],
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'status_item' => ['required', 'in:diproses,siap,diantar'],
        ], [
            'status_item.required' => 'Status item wajib diisi.',
            'status_item.in' => 'Status item harus diproses, siap, atau diantar.',
        ]);

        $detail = DetailPesanan::with('pesanan')->findOrFail($id);

        if ($detail->pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            abort(409, 'Pesanan ini sudah dibatalkan.');
        }

        $detail->update(['status_item' => $data['status_item']]);

        $this->sinkronStatusPesanan($detail->pesanan_id);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Status item diperbarui.',
            'data' => $detail->fresh(),
        ], 200);
    }

    public function daftarPesanan()
    {
        $pesanan = Pesanan::with('meja')
            ->whereIn('status_pesanan', [
                Pesanan::STATUS_MENUNGGU,
                Pesanan::STATUS_DIPROSES,
                Pesanan::STATUS_SIAP,
                Pesanan::STATUS_DIANTAR,
            ])
            ->orderBy('waktu_pesan')
            ->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $pesanan->count(),
            'data' => $pesanan,
        ], 200);
    }

    private function ambilAntrean($targetKds)
    {
        $item = DetailPesanan::with(['menu.kategori', 'pesanan.meja'])
            ->whereHas('menu.kategori', function ($query) use ($targetKds) {
                $query->where('target_kds', $targetKds);
            })
            ->whereHas('pesanan', function ($query) {
                $query->whereIn('status_pesanan', [
                    Pesanan::STATUS_MENUNGGU,
                    Pesanan::STATUS_DIPROSES,
                    Pesanan::STATUS_SIAP,
                ]);
            })
            ->whereIn('status_item', ['menunggu', 'diproses', 'siap'])
            ->orderBy('pesanan_id')
            ->get();

        return [
            'status' => 'sukses',
            'target_kds' => $targetKds,
            'total' => $item->count(),
            'data' => $item,
        ];
    }

    private function sinkronStatusPesanan(int $pesananId): void
    {
        $pesanan = Pesanan::with('detailPesanan')->find($pesananId);

        if (! $pesanan) {
            return;
        }

        $statusItem = $pesanan->detailPesanan->pluck('status_item');

        if ($statusItem->isEmpty()) {
            return;
        }

        if ($statusItem->contains('diantar')) {
            $statusBaru = Pesanan::STATUS_DIANTAR;
        } elseif ($statusItem->every(fn ($item) => $item === 'siap')) {
            $statusBaru = Pesanan::STATUS_SIAP;
        } elseif ($statusItem->contains(fn ($item) => $item !== 'menunggu')) {
            $statusBaru = Pesanan::STATUS_DIPROSES;
        } else {
            $statusBaru = Pesanan::STATUS_MENUNGGU;
        }

        if ($pesanan->status_pesanan !== $statusBaru) {
            $pesanan->update(['status_pesanan' => $statusBaru]);
        }
    }
}