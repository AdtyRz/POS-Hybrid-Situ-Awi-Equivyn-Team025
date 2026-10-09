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
    public function create(string $kode): View
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        if ($pesanan->status_pembayaran === 'sudah_bayar') {
            abort(409, 'Pesanan ini sudah lunas.');
        }

        return view('pembayaran.create', compact('pesanan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_pesanan' => ['required', 'string', 'exists:pesanan,kode_pesanan'],
            'metode_pembayaran' => ['required', 'in:tunai,qris'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ], [
            'kode_pesanan.required' => 'Kode pesanan wajib diisi.',
            'metode_pembayaran.in' => 'Metode pembayaran hanya tunai atau qris.',
            'jumlah_bayar.min' => 'Jumlah bayar tidak boleh negatif.',
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $data['kode_pesanan'])
            ->with('meja')
            ->firstOrFail();

        if ($pesanan->status_pembayaran === 'sudah_bayar') {
            return back()->withErrors([
                'jumlah_bayar' => 'Pesanan ini sudah lunas.',
            ]);
        }

        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return back()->withErrors([
                'jumlah_bayar' => 'Pesanan dibatalkan tidak bisa dibayar.',
            ]);
        }

        $tagihan = (float) $pesanan->total_bayar;
        $dibayar = (float) $data['jumlah_bayar'];

        if ($dibayar < $tagihan) {
            return back()->withErrors([
                'jumlah_bayar' => 'Uang yang dibayar kurang dari tagihan Rp ' . number_format($tagihan, 0, ',', '.'),
            ])->withInput();
        }

        DB::transaction(function () use ($pesanan, $data, $dibayar, $tagihan) {
            Pembayaran::create([
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_pembayaran' => 'lunas',
                'jumlah_bayar' => $dibayar,
                'kembalian' => $dibayar - $tagihan,
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);

            $pesanan->update([
                'status_pesanan' => Pesanan::STATUS_SELESAI,
                'status_pembayaran' => 'sudah_bayar',
                'metode_pembayaran' => $data['metode_pembayaran'],
            ]);

            $pesanan->meja->update(['status_meja' => 'kosong']);
        });

        return redirect()
            ->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('sukses', 'Pembayaran pesanan ' . $pesanan->kode_pesanan . ' berhasil.');
    }
}