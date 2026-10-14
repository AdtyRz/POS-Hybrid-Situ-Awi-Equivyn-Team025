<?php

namespace App\Http\Controllers;

use App\Events\SesiMejaBerubah;
use App\Models\MejaMakan;
use App\Models\SesiMeja;
use App\Support\Realtime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function daftarMejaSession(): View
    {
        $meja = MejaMakan::with(['pesananAktif.detailPesanan.menu'])
            ->orderBy('kode_meja')
            ->get();

        // Sertakan kode unik sesi aktif tiap meja untuk ditampilkan/diinformasikan kasir.
        $sesiAktif = $meja->mapWithKeys(function ($m) {
            return [$m->id => SesiMeja::aktifUntuk($m)];
        });

        return view('kasir.session', compact('meja', 'sesiAktif'));
    }

    /**
     * Selesaikan sesi meja (cara manual kasir): semua pesanan aktif ditandai
     * selesai, meja dikosongkan, lalu kode unik baru di-generate untuk sesi baru.
     */
    public function akhiriSession(Request $request, $meja): RedirectResponse
    {
        if (is_numeric($meja)) {
            $mejaMakan = MejaMakan::findOrFail($meja);
        } else {
            $mejaMakan = MejaMakan::where('kode_meja', $meja)->firstOrFail();
        }

        SesiMeja::putuskanSesi($mejaMakan);

        Realtime::emit(new SesiMejaBerubah(
            $mejaMakan->kode_meja,
            $mejaMakan->id,
            'selesai',
        ));

        return back();
    }
}
