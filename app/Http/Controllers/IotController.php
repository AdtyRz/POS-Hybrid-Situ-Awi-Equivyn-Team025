<?php

namespace App\Http\Controllers;

use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pesanan;
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

        $siapAntar = Pesanan::with('meja')
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        return view('pelayan.panggilan', compact('panggilan', 'siapAntar'));
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
