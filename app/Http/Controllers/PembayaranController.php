<?php

namespace App\Http\Controllers;

use App\Events\SesiMejaBerubah;
use App\Events\StatusPesananBerubah;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Support\Realtime;
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

    public function cari(Request $request): View
    {
        $kode = $request->input('kode_pembayaran') ?? $request->input('kode_pesanan');

        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where(function ($q) use ($kode) {
                $q->where('kode_pembayaran', $kode)
                    ->orWhere('kode_pesanan', $kode);
            })
            ->first();

        if (! $pesanan) {
            return view('kasir.cari', ['error' => 'Pesanan tidak ditemukan', 'kode' => $kode]);
        }

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return view('kasir.cari', ['error' => 'Pesanan ini sudah dibayar', 'pesanan' => $pesanan]);
        }

        return view('kasir.cari', compact('pesanan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kode_pesanan' => ['required', 'exists:pesanan,kode_pesanan'],
            'metode_pembayaran' => ['required', 'in:tunai,qris'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'selesaikan_sesi' => ['nullable', 'boolean'],
        ]);

        $pesanan = Pesanan::where('kode_pesanan', $data['kode_pesanan'])->firstOrFail();

        if ($pesanan->status_pembayaran === Pesanan::PAYMENT_SUDAH_BAYAR) {
            return back()->withErrors(['kode_pesanan' => 'Pesanan ini sudah dibayar.']);
        }

        if ($pesanan->status_pesanan === Pesanan::STATUS_DIBATALKAN) {
            return back()->withErrors(['kode_pesanan' => 'Pesanan ini sudah dibatalkan.']);
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

            // Determine next status based on payment method
            if ($data['metode_pembayaran'] === 'tunai') {
                // Cash: After payment, order goes to kitchen
                $pesanan->update([
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'metode_pembayaran' => 'tunai',
                ]);
            } else {
                // QRIS: Already verified via callback, just mark as paid
                $pesanan->update([
                    'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
                    'status_pesanan' => Pesanan::STATUS_DIPROSES,
                    'metode_pembayaran' => 'qris',
                ]);
            }
        });

        Realtime::emit(new StatusPesananBerubah([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $pesanan->meja->kode_meja ?? null,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
        ]));

        // Bila kasir memilih "Selesaikan sesi" saat mencetak struk, tutup sesi meja
        // dan buat kode unik baru untuk sesi berikutnya.
        if ($request->boolean('selesaikan_sesi') && $pesanan->meja) {
            $meja = $pesanan->meja;
            SesiMeja::putuskanSesi($meja);

            Realtime::emit(new SesiMejaBerubah(
                $meja->kode_meja,
                $meja->id,
                'selesai',
            ));
        }

        return redirect()->route('kasir.dashboard');
    }
}
