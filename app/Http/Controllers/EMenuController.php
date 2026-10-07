<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EMenuController extends Controller
{
    public function beranda(): View
    {
        $urutan = [
            'terisi' => 1,
            'kosong' => 2,
            'menunggu_kasir' => 3,
        ];

        $meja = MejaMakan::all(['id', 'kode_meja', 'area', 'kapasitas', 'status_meja'])
            ->sortBy(fn ($item) => ($urutan[$item->status_meja] ?? 99) * 1000 + ord(substr($item->kode_meja, 0, 1)))
            ->values();

        return view('emenu.beranda', compact('meja'));
    }

    public function menu()
    {
        $menu = Menu::where('stok_menu', '>', 0)
            ->with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama_menu')
            ->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $menu->count(),
            'data' => $menu,
        ], 200);
    }

    public function daftarMeja()
    {
        $meja = MejaMakan::where('status_meja', 'kosong')
            ->orderBy('kode_meja')
            ->get(['id', 'kode_meja', 'area', 'kapasitas', 'status_meja']);

        return response()->json([
            'status' => 'sukses',
            'total' => $meja->count(),
            'data' => $meja,
        ], 200);
    }

    public function aplikasi(string $meja): View
    {
        $mejaMakan = $this->cariMeja($meja);

        $menuJson = Menu::orderBy('nama_menu')->get()->map(function ($menu) {
            return [
                'id' => $menu->id,
                'name' => $menu->nama_menu,
                'price' => (float) $menu->harga,
                'desc' => $menu->deskripsi ?? '',
                'category' => $menu->kategori_id,
                'image' => $menu->foto_menu ? asset('storage/' . $menu->foto_menu) : '',
                'isSoldOut' => $menu->stok_menu <= 0,
                'badge' => $menu->stok_menu <= 0 ? 'Stok Habis' : '',
                'badgeType' => $menu->stok_menu <= 0 ? 'soldout' : '',
            ];
        })->values();

        $kategoriJson = collect([['id' => 'semua', 'name' => 'Semua']])->concat(
            Kategori::orderBy('id')->get()->map(function ($kategori) {
                return ['id' => $kategori->id, 'name' => $kategori->nama_kategori];
            })
        )->values();

        $inisial = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $mejaMakan->kode_meja), 0, 2));

        return view('pelanggan.index', [
            'meja' => $mejaMakan,
            'menuJson' => $menuJson,
            'kategoriJson' => $kategoriJson,
            'orderRef' => '#' . $inisial . '-' . $mejaMakan->id . '04',
        ]);
    }

    public function checkout(Request $request, string $meja, PesananService $pesananService): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        $data = $request->validate([
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['integer', 'min:0', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
            'metode' => ['required', 'in:tunai,qris'],
        ]);

        $dipilih = collect($data['jumlah'])->filter(fn ($jumlah) => $jumlah > 0)->all();

        if ($dipilih === []) {
            return back()->withErrors(['jumlah' => 'Pilih minimal satu menu.'])->withInput();
        }

        try {
            $pesanan = $pesananService->buat($mejaMakan, $dipilih, $data['catatan'] ?? []);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['jumlah' => $e->getMessage()])->withInput();
        }

        if ($pesanan->meja && $pesanan->session_code) {
            session(['session_' . $pesanan->meja->id => $pesanan->session_code]);
        }

        return redirect()->route('emenu.status', $pesanan->kode_pesanan);
    }

    public function status(Request $request, string $kode_pesanan): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where('kode_pesanan', $kode_pesanan)
            ->firstOrFail();

        if ($pesanan->meja && $pesanan->session_code) {
            session(['session_' . $pesanan->meja->id => $pesanan->session_code]);
        }

        $tahap = [
            'menunggu' => 1,
            'diproses' => 2,
            'siap' => 3,
            'diantar' => 4,
            'selesai' => 5,
        ][$pesanan->status_pesanan] ?? 0;

        return view('emenu.status', compact('pesanan', 'tahap'));
    }

    public function panggil(string $meja): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        $sudahAda = LogPanggilPelayan::where('meja_id', $mejaMakan->id)
            ->whereIn('status_panggilan', ['menunggu', 'ditangani'])
            ->exists();

        if ($sudahAda) {
            return back()->with('panggilan_terkirim', true)
                ->with('pesan', 'Meja ini sudah punya panggilan yang belum selesai.');
        }

        LogPanggilPelayan::create([
            'meja_id' => $mejaMakan->id,
            'status_panggilan' => 'menunggu',
            'waktu_panggil' => now(),
        ]);

        return back()->with('panggilan_terkirim', true)
            ->with('pesan', 'Panggilan pelayan berhasil dikirim.');
    }

    private function cariMeja(string $meja): MejaMakan
    {
        if (is_numeric($meja)) {
            return MejaMakan::findOrFail($meja);
        }

        return MejaMakan::where('kode_meja', $meja)->firstOrFail();
    }
}
