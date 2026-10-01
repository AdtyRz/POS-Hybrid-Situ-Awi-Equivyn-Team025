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

    public function getMasterData()
    {

        $kategoriDenganMenu = Kategori::with('menu')->get();

        $mejaMakan = MejaMakan::all();

        return response()->json([
            'success' => true,
            'message' => 'Data master kasir berhasil diambil',
            'data' => [
                'kategori_menu' => $kategoriDenganMenu,
                'meja_makan' => $mejaMakan
            ]
        ], 200);
    }

    public function buatPesanan(Request $request)
    {

        $request->validate([
            'meja_makan_id' => 'required|exists:meja_makan,id',
            'nama_pelanggan' => 'required|string|max:255',
            'items' => 'required|array',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric'
        ]);

        try {

            DB::beginTransaction();

            $totalHarga = 0;
            foreach ($request->items as $item) {
                $totalHarga += $item['harga'] * $item['jumlah'];
            }

            $pesanan = Pesanan::create([
                'meja_makan_id' => $request->meja_makan_id,
                'nama_pelanggan' => $request->nama_pelanggan,
                'total_harga' => $totalHarga,
                'status_pesanan' => 'pending',
                'status_pembayaran' => 'belum_bayar'
            ]);

            foreach ($request->items as $item) {
                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga'],
                    'subtotal' => $item['harga'] * $item['jumlah']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat dan diteruskan ke dapur!',
                'data' => $pesanan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}
