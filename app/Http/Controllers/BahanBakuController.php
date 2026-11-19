<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanStokMutasi;
use App\Models\Menu;
use App\Models\ResepMenu;
use App\Services\BahanStokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BahanBakuController extends Controller
{
    public function index(): View
    {
        $bahan = BahanBaku::query()->orderBy('nama')->get();
        $menu = Menu::query()->with(['resep.bahan', 'kategori'])->orderBy('nama_menu')->get();

        $bahanJson = $bahan->map(fn (BahanBaku $baris) => [
            'id' => $baris->id,
            'nama' => $baris->nama,
            'satuan' => $baris->satuan,
            'stok' => (float) $baris->stok,
            'stok_minimum' => (float) $baris->stok_minimum,
            'harga_rata_rata' => (float) $baris->harga_rata_rata,
            'is_aktif' => (bool) $baris->is_aktif,
        ])->values();

        $menuJson = $menu->map(fn (Menu $baris) => [
            'id' => $baris->id,
            'nama' => $baris->nama_menu,
            'harga' => (float) $baris->harga,
            'resep' => $baris->resep->map(fn (ResepMenu $resep) => [
                'bahan_baku_id' => (int) $resep->bahan_baku_id,
                'jumlah' => (float) $resep->jumlah,
            ])->values(),
        ])->values();

        $ringkasan = [
            'total_bahan' => $bahan->count(),
            'nilai_stok' => $bahan->sum(fn (BahanBaku $baris) => $baris->nilaiStok()),
            'menipis' => $bahan->filter(fn (BahanBaku $baris) => $baris->is_aktif && $baris->menipis())->count(),
            'menu_beresep' => $menu->filter(fn (Menu $baris) => $baris->resep->isNotEmpty())->count(),
        ];

        return view('admin.bahan-baku.index', [
            'bahan' => $bahan,
            'menu' => $menu,
            'bahanJson' => $bahanJson,
            'menuJson' => $menuJson,
            'ringkasan' => $ringkasan,
        ]);
    }

    public function simpan(Request $request, BahanStokService $stokService): RedirectResponse
    {
        $data = $this->validasiBahan($request);
        $stokAwal = (float) ($request->validate([
            'stok_awal' => ['nullable', 'numeric', 'min:0', 'max:99999999999.999'],
        ])['stok_awal'] ?? 0);

        $bahan = BahanBaku::create([
            'nama' => $data['nama'],
            'satuan' => $data['satuan'],
            'stok_minimum' => $data['stok_minimum'] ?? 0,
            'is_aktif' => $data['is_aktif'] ?? true,
            'stok' => 0,
            'harga_rata_rata' => 0,
        ]);

        if ($stokAwal > 0) {
            $stokService->sesuaikanStok($bahan, $stokAwal, 'Stok awal bahan baku', $request->user()?->id);
        }

        return back()->with('sukses', 'Bahan baku berhasil ditambahkan.');
    }

    public function ubah(Request $request, BahanBaku $bahan): RedirectResponse
    {
        $data = $this->validasiBahan($request, $bahan->id);

        $bahan->update([
            'nama' => $data['nama'],
            'satuan' => $data['satuan'],
            'stok_minimum' => $data['stok_minimum'] ?? 0,
            'is_aktif' => $data['is_aktif'] ?? true,
        ]);

        return back()->with('sukses', 'Bahan baku berhasil diperbarui.');
    }

    public function hapus(BahanBaku $bahan): RedirectResponse
    {
        $dipakai = $bahan->pembelianDetail()->exists()
            || $bahan->resep()->exists()
            || $bahan->mutasi()->exists();

        if ($dipakai) {
            return back()->with('error', 'Bahan baku sudah punya riwayat stok atau resep, tidak bisa dihapus.');
        }

        $bahan->delete();

        return back()->with('sukses', 'Bahan baku berhasil dihapus.');
    }

    public function sesuaikanStok(Request $request, BahanBaku $bahan, BahanStokService $stokService): RedirectResponse
    {
        $data = $request->validate([
            'stok_baru' => ['required', 'numeric', 'min:0', 'max:99999999999.999'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'stok_baru.required' => 'Stok baru wajib diisi.',
            'stok_baru.numeric' => 'Stok baru harus berupa angka.',
            'stok_baru.min' => 'Stok baru tidak boleh negatif.',
        ]);

        $stokService->sesuaikanStok(
            $bahan,
            (float) $data['stok_baru'],
            $data['keterangan'] ?? null,
            $request->user()?->id
        );

        return back()->with('sukses', 'Stok '.$bahan->nama.' berhasil disesuaikan.');
    }

    public function mutasi(BahanBaku $bahan): JsonResponse
    {
        $rows = BahanStokMutasi::query()
            ->where('bahan_baku_id', $bahan->id)
            ->with('pencatat')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(60)
            ->get()
            ->map(fn (BahanStokMutasi $baris) => [
                'id' => $baris->id,
                'jenis' => $baris->jenis,
                'jumlah_delta' => (float) $baris->jumlah_delta,
                'stok_akhir' => (float) $baris->stok_akhir,
                'keterangan' => (string) ($baris->keterangan ?? ''),
                'pencatat' => $baris->pencatat?->nama ?? '-',
                'waktu' => $baris->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-',
            ]);

        return response()->json([
            'bahan' => [
                'id' => $bahan->id,
                'nama' => $bahan->nama,
                'satuan' => $bahan->satuan,
                'stok' => (float) $bahan->stok,
            ],
            'mutasi' => $rows,
        ]);
    }

    public function simpanResep(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.bahan_baku_id' => ['required', 'integer', 'exists:bahan_baku,id'],
            'items.*.jumlah' => ['required', 'numeric', 'min:0.001', 'max:99999999.999'],
        ], [
            'items.*.bahan_baku_id.required' => 'Bahan pada resep wajib dipilih.',
            'items.*.bahan_baku_id.exists' => 'Bahan pada resep tidak ditemukan.',
            'items.*.jumlah.required' => 'Jumlah bahan pada resep wajib diisi.',
            'items.*.jumlah.min' => 'Jumlah bahan pada resep harus lebih dari nol.',
        ]);

        $items = collect($data['items'] ?? [])
            ->groupBy('bahan_baku_id')
            ->map(fn ($baris, $bahanId) => [
                'bahan_baku_id' => (int) $bahanId,
                'jumlah' => (float) $baris->sum('jumlah'),
            ])
            ->values();

        DB::transaction(function () use ($menu, $items) {
            $terkunci = Menu::query()->whereKey($menu->id)->lockForUpdate()->firstOrFail();
            ResepMenu::query()->where('menu_id', $terkunci->id)->delete();

            foreach ($items as $baris) {
                ResepMenu::create([
                    'menu_id' => $terkunci->id,
                    'bahan_baku_id' => $baris['bahan_baku_id'],
                    'jumlah' => $baris['jumlah'],
                ]);
            }
        });

        return back()->with('sukses', 'Resep '.$menu->nama_menu.' berhasil disimpan.');
    }

    private function validasiBahan(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:120', 'unique:bahan_baku,nama'.($id ? ','.$id : '')],
            'satuan' => ['required', 'string', 'max:20'],
            'stok_minimum' => ['nullable', 'numeric', 'min:0', 'max:99999999999.999'],
            'is_aktif' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama bahan wajib diisi.',
            'nama.unique' => 'Nama bahan sudah terdaftar.',
            'satuan.required' => 'Satuan wajib diisi.',
            'stok_minimum.numeric' => 'Stok minimum harus berupa angka.',
            'stok_minimum.min' => 'Stok minimum tidak boleh negatif.',
        ]);
    }
}
