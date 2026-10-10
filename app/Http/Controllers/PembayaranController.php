<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function create(Request $request, string $kode): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        $jumlahBayar = (float) $request->old('jumlah_bayar', 0);
        $kembalian = 0;

        if ($jumlahBayar > 0) {
            $kembalian = max(0, $jumlahBayar - (float) $pesanan->total_bayar);
        }

        return view('kasir.bayar', compact('pesanan', 'kembalian'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_pesanan' => ['required', 'exists:pesanan,kode_pesanan'],
            'metode_pembayaran' => ['required', 'in:tunai,qris'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $data['kode_pesanan'])->firstOrFail();

        if ($pesanan->status_pembayaran === 'sudah_bayar') {
            return back()->withErrors(['kode_pesanan' => 'Pesanan ini sudah dibayar.']);
        }

        if ($data['jumlah_bayar'] < (float) $pesanan->total_bayar) {
            return back()->withErrors(['jumlah_bayar' => 'Uang yang dibayar kurang dari total tagihan.'])->withInput();
        }

        $kembalian = $data['jumlah_bayar'] - (float) $pesanan->total_bayar;

        DB::transaction(function () use ($pesanan, $data, $kembalian) {
            Pembayaran::create([
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_pembayaran' => 'lunas',
                'jumlah_bayar' => $data['jumlah_bayar'],
                'kembalian' => $kembalian,
            ]);

            $pesanan->update([
                'status_pembayaran' => 'sudah_bayar',
                'status_pesanan' => 'selesai',
                'metode_pembayaran' => $data['metode_pembayaran'],
            ]);

            $pesanan->meja()->update(['status_meja' => 'kosong']);
        });

        return redirect()->route('kasir.dashboard');
    }
}
