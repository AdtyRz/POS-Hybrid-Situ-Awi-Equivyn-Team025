<?php

namespace Tests\Feature;

use App\Models\MejaMakan;
use App\Models\Pembayaran;
use App\Models\PembelianBahan;
use App\Models\Pengaturan;
use App\Models\Pengeluaran;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LaporanKeuanganTest extends TestCase
{
    use RefreshDatabase;

    private function meja(string $kode = 'LB-90'): MejaMakan
    {
        return MejaMakan::firstOrCreate(
            ['kode_meja' => $kode],
            [
                'area' => 'Lesehan Bawah',
                'kapasitas' => 4,
                'qr_token' => 'token-'.strtolower($kode),
                'status_meja' => 'kosong',
            ]
        );
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function pesananLunas(string $kode, float $total, string $metode, string $waktuBayar): Pesanan
    {
        $pesanan = Pesanan::create([
            'kode_pesanan' => $kode,
            'meja_id' => $this->meja()->id,
            'total_bayar' => $total,
            'status_pesanan' => Pesanan::STATUS_SELESAI,
            'status_pembayaran' => Pesanan::PAYMENT_SUDAH_BAYAR,
            'metode_pembayaran' => $metode,
            'waktu_pesan' => Carbon::parse($waktuBayar, 'Asia/Jakarta')->utc(),
        ]);

        Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran' => $metode,
            'status_pembayaran' => 'lunas',
            'jumlah_bayar' => $total,
            'waktu_bayar' => Carbon::parse($waktuBayar, 'Asia/Jakarta')->utc(),
        ]);

        return $pesanan;
    }

    public function test_ringkasan_arus_kas_menghitung_pemasukan_lunas_dan_seluruh_kas_keluar(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));
        Pengaturan::simpanBanyak(['nama_resto' => 'Saung Situ Awi']);

        $this->pesananLunas('AWI-AK-TUNAI', 100000, 'tunai', '2026-11-10 09:00:00');
        $this->pesananLunas('AWI-AK-QRIS', 50000, 'qris', '2026-11-10 09:30:00');

        $dibatalkan = $this->pesananLunas('AWI-AK-BATAL', 40000, 'transfer', '2026-11-10 09:15:00');
        $dibatalkan->update(['status_pesanan' => Pesanan::STATUS_DIBATALKAN]);

        $belumBayar = Pesanan::create([
            'kode_pesanan' => 'AWI-AK-BELUM',
            'meja_id' => $this->meja('LB-91')->id,
            'total_bayar' => 70000,
            'status_pesanan' => Pesanan::STATUS_DIPROSES,
            'status_pembayaran' => Pesanan::PAYMENT_BELUM_BAYAR,
            'metode_pembayaran' => 'tunai',
        ]);

        Pembayaran::create([
            'pesanan_id' => $belumBayar->id,
            'metode_pembayaran' => 'tunai',
            'status_pembayaran' => 'menunggu',
            'jumlah_bayar' => 70000,
            'waktu_bayar' => Carbon::parse('2026-11-10 09:45:00', 'Asia/Jakarta')->utc(),
        ]);

        PembelianBahan::create([
            'tanggal' => '2026-11-10',
            'supplier' => 'Pasar Induk',
            'keterangan' => 'Beras dan sayur',
            'total' => 30000,
        ]);

        PembelianBahan::create([
            'tanggal' => '2026-11-09',
            'supplier' => 'Supplier Lama',
            'keterangan' => 'Di luar periode',
            'total' => 700000,
        ]);

        Pengeluaran::create([
            'tanggal' => '2026-11-10',
            'kategori' => 'listrik_air',
            'keterangan' => 'Token listrik',
            'jumlah' => 20000,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.arus-kas', ['start' => '2026-11-10', 'end' => '2026-11-10']));

        $response->assertOk()
            ->assertSee('Rp 150.000')
            ->assertSee('Rp 30.000')
            ->assertSee('Rp 20.000')
            ->assertSee('Rp 100.000')
            ->assertSee('Beras dan sayur')
            ->assertSee('Token listrik')
            ->assertSee('Pasar Induk')
            ->assertDontSee('Rp 40.000')
            ->assertDontSee('Rp 70.000')
            ->assertDontSee('Di luar periode');
    }

    public function test_filter_periode_membatasi_pembelian_dan_pengeluaran(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));

        PembelianBahan::create([
            'tanggal' => '2026-11-01',
            'supplier' => 'Awal Bulan',
            'keterangan' => 'Pembelian awal',
            'total' => 10000,
        ]);

        PembelianBahan::create([
            'tanggal' => '2026-10-31',
            'supplier' => 'Bulan Lalu',
            'keterangan' => 'Pembelian lama',
            'total' => 20000,
        ]);

        Pengeluaran::create([
            'tanggal' => '2026-11-05',
            'kategori' => 'gaji',
            'keterangan' => 'Gaji staf November',
            'jumlah' => 50000,
        ]);

        Pengeluaran::create([
            'tanggal' => '2026-10-15',
            'kategori' => 'sewa',
            'keterangan' => 'Sewa bulan lalu',
            'jumlah' => 60000,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.arus-kas', ['start' => '2026-11-01', 'end' => '2026-11-10']))
            ->assertOk()
            ->assertSee('Pembelian awal')
            ->assertSee('Gaji staf November')
            ->assertDontSee('Pembelian lama')
            ->assertDontSee('Sewa bulan lalu');
    }

    public function test_admin_dapat_menambah_mengubah_dan_menghapus_pembelian_bahan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.arus-kas.pembelian.simpan'), [
            'tanggal' => '2026-11-10',
            'supplier' => 'Pasar Induk',
            'keterangan' => 'Beras 25 kg',
            'total' => 325000,
        ])->assertRedirect()->assertSessionHas('sukses');

        $pembelian = PembelianBahan::firstOrFail();
        $this->assertSame('Pasar Induk', $pembelian->supplier);
        $this->assertSame($admin->id, $pembelian->created_by);
        $this->assertSame('325000.00', (string) $pembelian->total);

        $this->actingAs($admin)->put(route('admin.arus-kas.pembelian.ubah', $pembelian), [
            'tanggal' => '2026-11-11',
            'supplier' => 'Pasar Baru',
            'keterangan' => 'Beras 50 kg',
            'total' => 640000,
        ])->assertRedirect()->assertSessionHas('sukses');

        $pembelian->refresh();
        $this->assertSame('Pasar Baru', $pembelian->supplier);
        $this->assertSame('2026-11-11', $pembelian->tanggal->toDateString());

        $this->actingAs($admin)
            ->delete(route('admin.arus-kas.pembelian.hapus', $pembelian))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('pembelian_bahan', ['id' => $pembelian->id]);
    }

    public function test_validasi_pembelian_bahan_menolak_total_kosong_dan_nol(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.arus-kas.pembelian.simpan'), [
            'tanggal' => '2026-11-10',
            'supplier' => 'Pasar Induk',
            'keterangan' => 'Tanpa total',
        ])->assertSessionHasErrors('total');

        $this->actingAs($admin)->post(route('admin.arus-kas.pembelian.simpan'), [
            'tanggal' => '2026-11-10',
            'supplier' => 'Pasar Induk',
            'keterangan' => 'Total nol',
            'total' => 0,
        ])->assertSessionHasErrors('total');

        $this->assertDatabaseCount('pembelian_bahan', 0);
    }

    public function test_admin_dapat_menambah_mengubah_dan_menghapus_pengeluaran(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.arus-kas.pengeluaran.simpan'), [
            'tanggal' => '2026-11-10',
            'kategori' => 'listrik_air',
            'keterangan' => 'Token listrik',
            'jumlah' => 150000,
        ])->assertRedirect()->assertSessionHas('sukses');

        $pengeluaran = Pengeluaran::firstOrFail();
        $this->assertSame('listrik_air', $pengeluaran->kategori);
        $this->assertSame('Listrik & Air', $pengeluaran->labelKategori());
        $this->assertSame($admin->id, $pengeluaran->created_by);

        $this->actingAs($admin)->put(route('admin.arus-kas.pengeluaran.ubah', $pengeluaran), [
            'tanggal' => '2026-11-12',
            'kategori' => 'perawatan',
            'keterangan' => 'Servis kompor',
            'jumlah' => 275000,
        ])->assertRedirect()->assertSessionHas('sukses');

        $pengeluaran->refresh();
        $this->assertSame('perawatan', $pengeluaran->kategori);
        $this->assertSame('2026-11-12', $pengeluaran->tanggal->toDateString());

        $this->actingAs($admin)
            ->delete(route('admin.arus-kas.pengeluaran.hapus', $pengeluaran))
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('pengeluaran', ['id' => $pengeluaran->id]);
    }

    public function test_validasi_pengeluaran_menolak_kategori_tidak_dikenal_dan_keterangan_kosong(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.arus-kas.pengeluaran.simpan'), [
            'tanggal' => '2026-11-10',
            'kategori' => 'hongkong',
            'keterangan' => '',
            'jumlah' => 10000,
        ])->assertSessionHasErrors(['kategori', 'keterangan']);

        $this->assertDatabaseCount('pengeluaran', 0);
    }

    public function test_halaman_cetak_arus_kas_menampilkan_ringkasan_dan_rincian(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));
        Pengaturan::simpanBanyak([
            'nama_resto' => 'Saung Situ Awi',
            'alamat_resto' => 'Jl. Situ Awi No. 1',
            'telepon_resto' => '08123456789',
        ]);

        $this->pesananLunas('AWI-CETAK-AK', 80000, 'tunai', '2026-11-10 08:00:00');

        PembelianBahan::create([
            'tanggal' => '2026-11-10',
            'supplier' => 'Pasar Cetak',
            'keterangan' => 'Sayuran segar',
            'total' => 15000,
        ]);

        Pengeluaran::create([
            'tanggal' => '2026-11-10',
            'kategori' => 'transportasi',
            'keterangan' => 'Bensin operasional',
            'jumlah' => 5000,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.arus-kas.cetak', ['start' => '2026-11-10', 'end' => '2026-11-10']))
            ->assertOk()
            ->assertSee('LAPORAN ARUS KAS')
            ->assertSee('Saung Situ Awi')
            ->assertSee('Pasar Cetak')
            ->assertSee('Bensin operasional')
            ->assertSee('Rp 80.000')
            ->assertSee('Rp 60.000')
            ->assertSee('Cetak / Simpan PDF');
    }

    public function test_ekspor_arus_kas_csv_dan_xls_berisi_ringkasan_dan_rincian(): void
    {
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00', 'Asia/Jakarta'));
        Pengaturan::simpanBanyak(['nama_resto' => 'Saung Situ Awi']);

        $this->pesananLunas('AWI-EKSPOR-AK', 90000, 'qris', '2026-11-10 08:00:00');

        PembelianBahan::create([
            'tanggal' => '2026-11-10',
            'supplier' => 'Supplier Ekspor',
            'keterangan' => 'Bumbu dapur',
            'total' => 25000,
        ]);

        Pengeluaran::create([
            'tanggal' => '2026-11-10',
            'kategori' => 'perlengkapan',
            'keterangan' => 'Gas elpiji',
            'jumlah' => 35000,
        ]);

        $admin = $this->admin();

        $csv = $this->actingAs($admin)
            ->get(route('admin.arus-kas.ekspor', ['format' => 'csv', 'start' => '2026-11-10', 'end' => '2026-11-10']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Laporan Arus Kas', $csv);
        $this->assertStringContainsString('Arus Kas Bersih', $csv);
        $this->assertStringContainsString('Supplier Ekspor', $csv);
        $this->assertStringContainsString('Bumbu dapur', $csv);
        $this->assertStringContainsString('Perlengkapan', $csv);
        $this->assertStringContainsString('Gas elpiji', $csv);

        $xls = $this->actingAs($admin)
            ->get(route('admin.arus-kas.ekspor', ['format' => 'xls', 'start' => '2026-11-10', 'end' => '2026-11-10']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('LAPORAN ARUS KAS', $xls);
        $this->assertStringContainsString('Supplier Ekspor', $xls);
        $this->assertStringContainsString('Bumbu dapur', $xls);
        $this->assertStringContainsString('Perlengkapan', $xls);
        $this->assertStringContainsString('Gas elpiji', $xls);
    }

    public function test_pegawai_non_admin_tidak_bisa_mengakses_arus_kas(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->get(route('admin.arus-kas'))->assertStatus(403);

        $this->actingAs($kasir)->post(route('admin.arus-kas.pembelian.simpan'), [
            'tanggal' => '2026-11-10',
            'total' => 10000,
        ])->assertStatus(403);

        $this->assertDatabaseCount('pembelian_bahan', 0);
    }
}
