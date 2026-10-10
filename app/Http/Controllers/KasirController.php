<?php

namespace App\Http\Controllers;

use App\Models\MejaMakan;
use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function daftarMejaSession(): View
    {
        $meja = MejaMakan::with(['pesananAktif.detailPesanan.menu'])
            ->orderBy('kode_meja')
            ->get();

        return view('kasir.session', compact('meja'));
    }

    public function akhiriSession(Request $request, $meja): RedirectResponse
    {
        if (is_numeric($meja)) {
            $mejaMakan = MejaMakan::findOrFail($meja);
        } else {
            $mejaMakan = MejaMakan::where('kode_meja', $meja)->firstOrFail();
        }

        DB::transaction(function () use ($mejaMakan) {
            Pesanan::where('meja_id', $mejaMakan->id)
                ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
                ->update([
                    'status_pesanan' => 'selesai',
                    'status_pembayaran' => 'sudah_bayar',
                ]);

            $mejaMakan->update(['status_meja' => 'kosong']);
        });

        return back();
    }
}
