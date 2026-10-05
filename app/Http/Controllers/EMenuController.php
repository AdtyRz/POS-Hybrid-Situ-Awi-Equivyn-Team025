<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\StokMutasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EMenuController extends Controller
{
    public function beranda(): View
    {
        return view('emenu.beranda');
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

    public function checkout(Request $request, string $meja): RedirectResponse
    {
        $mejaMakan = $this->cariMeja($meja);

        $data = $request->validate([
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['integer', 'min:0', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
            'metode' => ['required', 'in:tunai,qris'],
        ]);

        $dipilih = collect($data['jumlah'])->filter(fn ($jumlah) => $jumlah > 0);

        if ($dipilih->isEmpty()) {
            return back()->withErrors(['jumlah' => 'Pilih minimal satu menu.'])->withInput();
        }

        $daftarMenu = Menu::whereIn('id', $dipilih->keys())->get()->keyBy('id');

        foreach ($dipilih as $id => $jumlah) {
            if (! isset($daftarMenu[$id]) || $daftarMenu[$id]->stok_menu < $jumlah) {
                $nama = isset($daftarMenu[$id]) ? $daftarMenu[$id]->nama_menu : 'menu';
                return back()->withErrors(['jumlah' => 'Stok ' . $nama . ' tidak cukup.'])->withInput();
            }
        }

        $pesanan = DB::transaction(function () use ($mejaMakan, $daftarMenu, $dipilih, $data) {
            $subtotal = 0;

            foreach ($dipilih as $id => $jumlah) {
                $subtotal += $daftarMenu[$id]->harga * $jumlah;
            }

            $pesanan = Pesanan::create([
                'kode_pesanan' => 'SW-' . now()->format('YmdHis') . '-' . $mejaMakan->id,
                'meja_id' => $mejaMakan->id,
                'total_bayar' => $subtotal + round($subtotal * 0.1),
                'metode_pembayaran' => $data['metode'],
            ]);

            foreach ($dipilih as $id => $jumlah) {
                $menu = $daftarMenu[$id];

                $pesanan->detailPesanan()->create([
                    'menu_id' => $menu->id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $menu->harga * $jumlah,
                    'catatan' => $data['catatan'][$id] ?? null,
                ]);

                $menu->decrement('stok_menu', $jumlah);

                StokMutasi::create([
                    'menu_id' => $menu->id,
                    'pesanan_id' => $pesanan->id,
                    'jenis' => 'penjualan',
                    'jumlah_delta' => -$jumlah,
                    'stok_akhir' => $menu->fresh()->stok_menu,
                ]);
            }

            $mejaMakan->update(['status_meja' => 'terisi']);

            return $pesanan;
        });

        return redirect()->route('emenu.status', $pesanan->kode_pesanan);
    }

    public function status(string $kode_pesanan): View
    {
        $pesanan = Pesanan::with(['detailPesanan.menu', 'meja'])
            ->where('kode_pesanan', $kode_pesanan)
            ->firstOrFail();

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
