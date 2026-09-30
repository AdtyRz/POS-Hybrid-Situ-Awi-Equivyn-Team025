<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    // API 1: Fungsi untuk mengambil data master (Kategori, Menu, Meja)
    public function getMasterData()
    {
        // Ambil data kategori beserta menu yang ada di dalamnya
        $kategoriDenganMenu = Kategori::with('menu')->get();

        // Ambil data meja makan
        $mejaMakan = MejaMakan::all();

        // Kembalikan dalam bentuk format JSON
        return response()->json([
            'success' => true,
            'message' => 'Data master kasir berhasil diambil',
            'data' => [
                'kategori_menu' => $kategoriDenganMenu,
                'meja_makan' => $mejaMakan
            ]
        ], 200);
    }

    // API 2: Menerima dan menyimpan pesanan baru dari Kasir
    public function buatPesanan(Request $request)
    {
        // 1. Validasi data yang dikirim dari Frontend Kasir
        $request->validate([
            'meja_makan_id' => 'required|exists:meja_makan,id',
            'nama_pelanggan' => 'required|string|max:255',
            'items' => 'required|array', // Daftar menu yang dipesan
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric'
        ]);

        try {
            // Gunakan DB Transaction: Kalau ada 1 tabel gagal simpan, semuanya dibatalkan
            DB::beginTransaction();

            // 2. Hitung total harga keseluruhan
            $totalHarga = 0;
            foreach ($request->items as $item) {
                $totalHarga += $item['harga'] * $item['jumlah'];
            }

            // 3. Simpan ke tabel `pesanan` (Tabel Induk)
            $pesanan = Pesanan::create([
                'meja_makan_id' => $request->meja_makan_id,
                'nama_pelanggan' => $request->nama_pelanggan,
                'total_harga' => $totalHarga,
                'status_pesanan' => 'pending',
                'status_pembayaran' => 'belum_bayar'
            ]);

            // 4. Simpan ke tabel `detail_pesanan` (Tabel Anak/Rincian)
            foreach ($request->items as $item) {
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga'],
                    'subtotal' => $item['harga'] * $item['jumlah']
                ]);
            }

            DB::commit(); // Simpan permanen ke database!

            // TODO: Nanti kita letakkan kode Broadcast Reverb (WebSocket) di sini

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat dan diteruskan ke dapur!',
                'data' => $pesanan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika terjadi error

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}
