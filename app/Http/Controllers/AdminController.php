<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'status' => 'sukses',
            'jumlah_menu' => Menu::count(),
            'jumlah_kategori' => Kategori::count(),
            'jumlah_meja' => MejaMakan::count(),
            'jumlah_user' => User::count(),
            'menu_stok_habis' => Menu::whereColumn('stok_menu', '<=', 0)->count(),
        ], 200);
    }

    public function index(Request $request)
    {
        $laporan = $this->laporan();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'sukses',
                'data' => $laporan,
            ], 200);
        }

        return view('admin.laporan', [
            'laporan' => $laporan,
            'menuTerlaris' => Menu::withCount('detailPesanan')
                ->orderByDesc('detail_pesanan_count')
                ->limit(10)
                ->get(),
            'stokMenipis' => Menu::whereColumn('stok_menu', '<=', 5)
                ->orderBy('stok_menu')
                ->get(),
        ]);
    }

    private function laporan(): array
    {
        $pesananLunas = Pesanan::where('status_pembayaran', 'sudah_bayar');

        return [
            'jumlah_pesanan' => Pesanan::count(),
            'jumlah_pesanan_lunas' => (clone $pesananLunas)->count(),
            'total_pendapatan' => (float) (clone $pesananLunas)->sum('total_bayar'),
            'pendapatan_hari_ini' => (float) (clone $pesananLunas)
                ->whereDate('waktu_pesan', today())
                ->sum('total_bayar'),
            'jumlah_meja_terisi' => MejaMakan::where('status_meja', 'terisi')->count(),
            'jumlah_menu' => Menu::count(),
            'menu_stok_habis' => Menu::whereColumn('stok_menu', '<=', 0)->count(),
        ];
    }

    public function daftarMenu()
    {
        $menu = Menu::with('kategori')->orderBy('nama_menu')->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $menu->count(),
            'data' => $menu,
        ], 200);
    }

    public function simpanMenu(Request $request)
    {
        $data = $request->validate([
            'kategori_id' => ['required', 'exists:kategori,id'],
            'nama_menu' => ['required', 'string', 'max:120', 'unique:menu,nama_menu'],
            'deskripsi' => ['nullable', 'string', 'max:191'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok_menu' => ['required', 'integer', 'min:0'],
        ], [
            'kategori_id.required' => 'Kategori wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak ada.',
            'nama_menu.required' => 'Nama menu wajib diisi.',
            'nama_menu.unique' => 'Menu dengan nama itu sudah ada.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.integer' => 'Harga harus angka bulat tanpa titik atau koma.',
            'stok_menu.required' => 'Stok wajib diisi.',
            'stok_menu.min' => 'Stok tidak boleh minus.',
        ]);

        $data['slug'] = Str::slug($data['nama_menu']) . '-' . substr(Str::random(4), 0, 4);

        $menu = Menu::create($data);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu,
        ], 201);
    }

    public function ubahMenu(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $data = $request->validate([
            'kategori_id' => ['required', 'exists:kategori,id'],
            'nama_menu' => ['required', 'string', 'max:120', 'unique:menu,nama_menu,' . $menu->id],
            'deskripsi' => ['nullable', 'string', 'max:191'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok_menu' => ['required', 'integer', 'min:0'],
        ], [
            'nama_menu.unique' => 'Menu dengan nama itu sudah ada.',
            'harga.integer' => 'Harga harus angka bulat tanpa titik atau koma.',
            'stok_menu.min' => 'Stok tidak boleh minus.',
        ]);

        if ($data['nama_menu'] !== $menu->nama_menu) {
            $data['slug'] = Str::slug($data['nama_menu']) . '-' . substr(Str::random(4), 0, 4);
        }

        $menu->update($data);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Menu berhasil diperbarui.',
            'data' => $menu,
        ], 200);
    }

    public function hapusMenu($id)
    {
        $menu = Menu::findOrFail($id);

        if ($menu->detailPesanan()->count() > 0) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Menu ini sudah pernah dipesan, tidak bisa dihapus.',
            ], 409);
        }

        $menu->delete();

        return response()->json([
            'status' => 'sukses',
            'message' => 'Menu berhasil dihapus.',
        ], 200);
    }

    public function daftarKategori()
    {
        $kategori = Kategori::withCount('menu')->orderBy('nama_kategori')->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $kategori->count(),
            'data' => $kategori,
        ], 200);
    }

    public function simpanKategori(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:60', 'unique:kategori,nama_kategori'],
            'target_kds' => ['required', 'in:dapur,bar'],
            'deskripsi' => ['nullable', 'string', 'max:191'],
        ], [
            'nama_kategori.unique' => 'Kategori dengan nama itu sudah ada.',
            'target_kds.required' => 'Target KDS wajib dipilih.',
            'target_kds.in' => 'Target KDS harus dapur atau bar.',
        ]);

        $kategori = Kategori::create($data);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $kategori,
        ], 201);
    }

    public function daftarMeja()
    {
        $meja = MejaMakan::orderBy('kode_meja')->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $meja->count(),
            'data' => $meja,
        ], 200);
    }

    public function simpanMeja(Request $request)
    {
        $data = $request->validate([
            'kode_meja' => ['required', 'string', 'max:10', 'unique:meja_makan,kode_meja'],
            'area' => ['required', 'in:Lesehan Bawah,Lesehan Atas,Kursi Atas'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'kode_meja.unique' => 'Kode meja sudah dipakai.',
            'area.in' => 'Area harus Lesehan Bawah, Lesehan Atas, atau Kursi Atas.',
            'kapasitas.min' => 'Kapasitas minimal 1 orang.',
            'kapasitas.max' => 'Kapasitas maksimal 20 orang.',
        ]);

        $data['qr_token'] = 'QR-' . strtoupper(Str::random(12));
        $data['status_meja'] = 'kosong';

        $meja = MejaMakan::create($data);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Meja berhasil ditambahkan.',
            'data' => $meja,
        ], 201);
    }

    public function daftarAkun()
    {
        $akun = User::orderBy('nama')->get();

        return response()->json([
            'status' => 'sukses',
            'total' => $akun->count(),
            'data' => $akun->makeHidden(['password', 'remember_token']),
        ], 200);
    }

    public function simpanAkun(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,kasir,koki,barista,pelayan,pelanggan'],
            'no_telp' => ['nullable', 'string', 'max:20'],
        ], [
            'email.unique' => 'Email itu sudah dipakai.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.in' => 'Role tidak dikenal.',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_aktif'] = true;

        $akun = User::create($data);

        return response()->json([
            'status' => 'sukses',
            'message' => 'Akun berhasil dibuat.',
            'data' => $akun->makeHidden(['password', 'remember_token']),
        ], 201);
    }

    public function ubahStatusAkun(Request $request, $id)
    {
        $data = $request->validate([
            'is_aktif' => ['required', 'boolean'],
        ]);

        $akun = User::findOrFail($id);

        if ($akun->id === $request->user()->id) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Tidak bisa menonaktifkan akun sendiri.',
            ], 422);
        }

        $akun->update(['is_aktif' => $data['is_aktif']]);

        return response()->json([
            'status' => 'sukses',
            'message' => $akun->is_aktif ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.',
            'data' => $akun->makeHidden(['password', 'remember_token']),
        ], 200);
    }
}