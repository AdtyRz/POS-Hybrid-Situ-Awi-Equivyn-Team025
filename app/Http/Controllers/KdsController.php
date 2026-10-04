<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function dapur()
    {
        return response()->json($this->ambilAntrean('dapur'), 200);
    }

    public function bar()
    {
        return response()->json($this->ambilAntrean('bar'), 200);
    }

    public function ubahStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status_item' => ['required', 'in:diproses,siap'],
        ], [
            'status_item.required' => 'Status item wajib diisi.',
            'status_item.in' => 'Status item harus diproses atau siap.',
        ]);

        $detail = DetailPesanan::findOrFail($id);

        $detail->update(['status_item' => $data['status_item']]);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Status item diperbarui.',
            'data' => $detail,
        ], 200);
    }

    public function daftarPesanan()
    {
        $pesanan = Pesanan::with('meja')
            ->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap diantar'])
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
            ->whereIn('status_item', ['menunggu', 'diproses'])
            ->orderBy('waktu_pesan')
            ->get();

        return [
            'status' => 'sukses',
            'target_kds' => $targetKds,
            'total' => $item->count(),
            'data' => $item,
        ];
    }
}