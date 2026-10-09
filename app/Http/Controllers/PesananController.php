<?php

namespace App\Http\Controllers;

use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(): View
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan', 'pembayaran'])
            ->whereIn('status_pesanan', [
                Pesanan::STATUS_MENUNGGU,
                Pesanan::STATUS_DIPROSES,
                Pesanan::STATUS_SIAP,
                Pesanan::STATUS_DIANTAR,
            ])
            ->orderBy('waktu_pesan')
            ->get();

        return view('pesanan.index', compact('pesanan'));
    }

    public function create(): View
    {
        $meja = MejaMakan::where('status_meja', 'kosong')->orderBy('kode_meja')->get();
        $menu = Menu::with('kategori')
            ->where('stok_menu', '>', 0)
            ->orderBy('kategori_id')
            ->orderBy('nama_menu')
            ->get();

        return view('pesanan.create', compact('meja', 'menu'));
    }

    public function store(Request $request, PesananService $pesananService): RedirectResponse
    {
        $data = $request->validate([
            'meja_id' => ['required', 'integer', 'exists:meja_makan,id'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['required', 'integer', 'min:1', 'max:99'],
            'catatan' => ['nullable', 'array'],
            'catatan.*' => ['nullable', 'string', 'max:255'],
        ], [
            'meja_id.required' => 'Pilih meja dulu.',
            'jumlah.*.min' => 'Jumlah minimal 1.',
        ]);

        $meja = MejaMakan::findOrFail($data['meja_id']);

        if ($meja->status_meja !== 'kosong' || ! $meja->bisaDipakai()) {
            return back()->withErrors(['meja_id' => 'Meja ini masih ada pesanan berjalan.'])->withInput();
        }

        try {
            $pesanan = $pesananService->buat($meja, $data['jumlah'], $data['catatan'] ?? []);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['jumlah' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('sukses', 'Pesanan ' . $pesanan->kode_pesanan . ' berhasil dibuat.');
    }

    public function show(string $kode): View
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan.menu', 'pembayaran'])
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        return view('pesanan.show', compact('pesanan'));
    }

    public function ubahStatus(Request $request, string $kode): RedirectResponse
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)->firstOrFail();

        $data = $request->validate([
            'status_pesanan' => ['required', 'in:diantar,selesai'],
        ], [
            'status_pesanan.required' => 'Status pesanan wajib diisi.',
            'status_pesanan.in' => 'Status hanya boleh diantar atau selesai.',
        ]);

        $pesanan->update(['status_pesanan' => $data['status_pesanan']]);

        return back()->with('sukses', 'Status pesanan diubah jadi ' . $data['status_pesanan'] . '.');
    }
}