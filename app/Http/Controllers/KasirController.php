<?php

namespace App\Http\Controllers;

use App\Events\SesiMejaBerubah;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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

        return view('kasir.sesi.index', compact('meja', 'sesiAktif'));
    }

    /**
     * Selesaikan sesi meja (cara manual kasir): semua pesanan yang sudah
     * dibayar ditandai selesai, meja dikosongkan, lalu kode unik baru
     * di-generate untuk sesi baru. Sesi dengan tagihan belum lunas ditolak.
     */
    public function akhiriSession(Request $request, $meja): RedirectResponse
    {
        if (is_numeric($meja)) {
            $mejaMakan = MejaMakan::findOrFail($meja);
        } else {
            $mejaMakan = MejaMakan::where('kode_meja', $meja)->firstOrFail();
        }

        try {
            SesiMeja::putuskanSesi($mejaMakan);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        Realtime::emit(new SesiMejaBerubah(
            $mejaMakan->kode_meja,
            $mejaMakan->id,
            'selesai',
        ));

        return back();
    }

    /**
     * Kunci terminal kasir. Status akun di admin berubah menjadi standby,
     * tetapi sesi login tetap hidup.
     */
    public function lock(Request $request): RedirectResponse
    {
        $request->user()?->update(['status_operasional' => 'standby']);

        $request->session()->put('kasir_locked', true);
        $request->session()->put('kasir_locked_at', now()->toIso8601String());

        return redirect()->route('kasir.locked');
    }

    /**
     * Halaman kunci tidak menampilkan detail transaksi. Hanya jumlah
     * notifikasi yang boleh terlihat oleh orang lain.
     */
    public function locked(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('kasir_locked')) {
            return redirect()->route('kasir.dashboard');
        }

        return view('kasir.sesi.kunci', [
            'notifikasi' => $this->hitungNotifikasiTerkunci($request),
            'namaKasir' => $request->user()?->nama ?? 'Kasir',
        ]);
    }

    /**
     * Buka kunci hanya dengan password akun kasir yang sedang login.
     */
    public function unlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($data['password'], (string) $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password akun kasir salah.',
            ]);
        }

        $request->session()->forget(['kasir_locked', 'kasir_locked_at']);
        $request->user()->update(['status_operasional' => 'aktif']);

        return redirect()
            ->route('kasir.dashboard')
            ->with('sukses', 'Terminal berhasil dibuka. Selamat bekerja.');
    }

    public function notifikasiTerkunci(Request $request): JsonResponse
    {
        return response()
            ->json($this->hitungNotifikasiTerkunci($request))
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    /**
     * @return array{pesanan_baru: int, panggilan: int, belum_bayar: int}
     */
    private function hitungNotifikasiTerkunci(Request $request): array
    {
        $sejak = $request->session()->get('kasir_locked_at');
        $sejak = $sejak ? Carbon::parse($sejak) : null;

        $pesananBaru = Pesanan::query()
            ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->when($sejak, fn ($query) => $query->where('created_at', '>=', $sejak))
            ->count();

        $belumBayar = Pesanan::query()
            ->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->where('status_pembayaran', '!=', Pesanan::PAYMENT_SUDAH_BAYAR)
            ->count();

        return [
            'pesanan_baru' => $pesananBaru,
            'panggilan' => LogPanggilPelayan::where('status_panggilan', 'menunggu')->count(),
            'belum_bayar' => $belumBayar,
        ];
    }
}
