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

        $siapAntar = Pesanan::with('meja')
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        return view('pelayan.notifikasi-panggilan-pelayan.index', compact('panggilan', 'siapAntar'));
    }

    public function tabAntar(): View
    {
        $pesananSiap = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'siap')
            ->orderBy('waktu_pesan')
            ->get();

        $pesananDiantar = Pesanan::with(['meja', 'detailPesanan.menu'])
            ->where('status_pesanan', 'diantar')
            ->orderBy('waktu_pesan')
            ->get();

        return view('pelayan.tab-antar.index', compact('pesananSiap', 'pesananDiantar'));
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
