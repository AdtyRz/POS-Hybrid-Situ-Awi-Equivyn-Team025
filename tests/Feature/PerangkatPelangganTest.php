<?php

namespace Tests\Feature;

use App\Models\MejaMakan;
use App\Models\SesiMeja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PerangkatPelangganTest extends TestCase
{
    use RefreshDatabase;

    private const PERANGKAT_SATU = 'perangkat-satu-uji-0001';

    private const PERANGKAT_DUA = 'perangkat-dua-uji-0002';

    private function siapkanMeja(): MejaMakan
    {
        return MejaMakan::create([
            'kode_meja' => 'LB-88',
            'area' => 'Lesehan Bawah',
            'kapasitas' => 4,
            'qr_token' => 'token-lb-88-abcdefghijklmnop',
            'status_meja' => 'kosong',
        ]);
    }

    private function klaimPerangkat(SesiMeja $sesi, string $id = self::PERANGKAT_SATU, ?Carbon $aktifPada = null): void
    {
        $sesi->forceFill([
            'perangkat_id' => $id,
            'perangkat_label' => 'Chrome di Android',
            'perangkat_aktif_pada' => $aktifPada ?? now(),
            'takeover_status' => null,
            'takeover_perangkat_id' => null,
            'takeover_perangkat_label' => null,
            'takeover_diminta_pada' => null,
        ])->save();
    }

    private function sebagai(string $perangkat, array $tambahan = []): self
    {
        $this->flushSession();

        return $this->withSession(array_merge([
            'perangkat_pelanggan' => $perangkat,
        ], $tambahan));
    }

    private function mintaAmbilAlih(SesiMeja $sesi, string $perangkat = self::PERANGKAT_DUA, ?Carbon $dimintaPada = null): void
    {
        $sesi->forceFill([
            'takeover_status' => 'menunggu',
            'takeover_perangkat_id' => $perangkat,
            'takeover_perangkat_label' => 'Safari di iPhone',
            'takeover_diminta_pada' => $dimintaPada ?? now(),
        ])->save();
    }

    public function test_perangkat_kedua_dengan_kode_benar_mendapat_popup_konflik_tanpa_mengambil_sesi(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);

        $response = $this->sebagai(self::PERANGKAT_DUA)
            ->post(route('emenu.masuk'), ['kode_unik' => $sesi->kode_unik]);

        $response->assertRedirect(route('emenu.index'))
            ->assertSessionHas('konflik_perangkat')
            ->assertSessionMissing('sesi_meja_' . $meja->id);

        $konflik = session('konflik_perangkat');

        $this->assertSame($meja->kode_meja, $konflik['kode_meja']);
        $this->assertSame(route('emenu.perangkat.minta', $meja->kode_meja), $konflik['minta_url']);
        $this->assertSame(self::PERANGKAT_SATU, $sesi->fresh()->perangkat_id);
    }

    public function test_perangkat_kedua_ditolak_saat_membuka_kode_lewat_json(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);

        $this->sebagai(self::PERANGKAT_DUA)
            ->postJson(route('emenu.buka', $meja->kode_meja), ['kode_unik' => $sesi->kode_unik])
            ->assertStatus(409)
            ->assertJsonPath('konflik.kode_meja', $meja->kode_meja)
            ->assertJsonPath('errors.kode_unik.0', 'Sesi meja ini sedang dipakai di perangkat lain.')
            ->assertSessionMissing('sesi_meja_' . $meja->id);
    }

    public function test_permintaan_ambil_alih_tanpa_konflik_terlebih_dahulu_ditolak(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);

        $this->sebagai(self::PERANGKAT_DUA)
            ->postJson(route('emenu.perangkat.minta', $meja->kode_meja))
            ->assertStatus(403)
            ->assertJsonPath('message', 'Sesi tidak valid. Masukkan kembali kode meja lalu coba lagi.');

        $this->assertNull($sesi->fresh()->takeover_status);
    }

    public function test_alur_terima_ambil_alih_memindahkan_sesi_dan_mengeluarkan_perangkat_lama(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);

        $this->sebagai(self::PERANGKAT_DUA)
            ->post(route('emenu.masuk'), ['kode_unik' => $sesi->kode_unik])
            ->assertSessionHas('konflik_perangkat');

        $this->postJson(route('emenu.perangkat.minta', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('status', 'menunggu');

        $sesi->refresh();
        $this->assertSame('menunggu', $sesi->takeover_status);
        $this->assertSame(self::PERANGKAT_DUA, $sesi->takeover_perangkat_id);

        $this->sebagai(self::PERANGKAT_SATU, ['sesi_meja_' . $meja->id => $sesi->id])
            ->getJson(route('emenu.perangkat.status', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('peran', 'aktif')
            ->assertJsonPath('takeover.status', 'menunggu');

        $this->postJson(route('emenu.perangkat.respon', $meja->kode_meja), ['keputusan' => 'terima'])
            ->assertOk()
            ->assertJsonPath('status', 'disetujui')
            ->assertJsonPath('redirect', route('emenu.kunci', $meja->kode_meja))
            ->assertSessionMissing('sesi_meja_' . $meja->id)
            ->assertSessionMissing('riwayat_meja_' . $meja->id);

        $sesi->refresh();
        $this->assertSame(self::PERANGKAT_DUA, $sesi->perangkat_id);
        $this->assertSame('disetujui', $sesi->takeover_status);

        $this->sebagai(self::PERANGKAT_DUA)
            ->getJson(route('emenu.perangkat.status', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('peran', 'aktif')
            ->assertJsonPath('redirect', route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);

        $this->sebagai(self::PERANGKAT_SATU, ['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $meja->kode_meja))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja))
            ->assertSessionHasErrors('kode_unik');
    }

    public function test_tolak_ambil_alih_mempertahankan_sesi_di_perangkat_lama(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);
        $this->mintaAmbilAlih($sesi);

        $this->sebagai(self::PERANGKAT_SATU, ['sesi_meja_' . $meja->id => $sesi->id])
            ->postJson(route('emenu.perangkat.respon', $meja->kode_meja), ['keputusan' => 'tolak'])
            ->assertOk()
            ->assertJsonPath('status', 'ditolak');

        $sesi->refresh();
        $this->assertSame('ditolak', $sesi->takeover_status);
        $this->assertSame(self::PERANGKAT_SATU, $sesi->perangkat_id);
        $this->assertSame($sesi->id, session('sesi_meja_' . $meja->id));

        $this->sebagai(self::PERANGKAT_DUA)
            ->getJson(route('emenu.perangkat.status', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('peran', 'pemohon')
            ->assertJsonPath('takeover.status', 'ditolak');

        $this->sebagai(self::PERANGKAT_SATU, ['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $meja->kode_meja))
            ->assertOk();
    }

    public function test_permintaan_ambil_alih_kedaluwarsa_setelah_60_detik(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);
        $this->mintaAmbilAlih($sesi, self::PERANGKAT_DUA, now()->subSeconds(61));

        $this->sebagai(self::PERANGKAT_DUA)
            ->getJson(route('emenu.perangkat.status', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('takeover.status', 'kedaluwarsa');

        $this->sebagai(self::PERANGKAT_SATU, ['sesi_meja_' . $meja->id => $sesi->id])
            ->postJson(route('emenu.perangkat.respon', $meja->kode_meja), ['keputusan' => 'terima'])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Permintaan sudah tidak berlaku.');

        $this->assertSame(self::PERANGKAT_SATU, $sesi->fresh()->perangkat_id);
    }

    public function test_batalkan_permintaan_ambil_alih_menghapus_permintaan_menunggu(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);
        $this->mintaAmbilAlih($sesi);

        $this->sebagai(self::PERANGKAT_DUA)
            ->postJson(route('emenu.perangkat.batal', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('status', 'dibatalkan');

        $sesi->refresh();
        $this->assertNull($sesi->takeover_status);
        $this->assertNull($sesi->takeover_perangkat_id);
        $this->assertSame(self::PERANGKAT_SATU, $sesi->perangkat_id);
    }

    public function test_permintaan_ambil_alih_langsung_disetujui_jika_tidak_ada_perangkat_aktif(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);

        $this->sebagai(self::PERANGKAT_DUA, [
            'izin_ambil_alih_' . $meja->id => [
                'sesi_id' => $sesi->id,
                'waktu' => now()->timestamp,
            ],
        ])
            ->postJson(route('emenu.perangkat.minta', $meja->kode_meja))
            ->assertOk()
            ->assertJsonPath('status', 'disetujui')
            ->assertJsonPath('redirect', route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);

        $this->assertSame(self::PERANGKAT_DUA, $sesi->fresh()->perangkat_id);
    }

    public function test_keluar_melepas_klaim_perangkat_sehingga_perangkat_lain_bisa_masuk(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);

        $this->sebagai(self::PERANGKAT_SATU, ['sesi_meja_' . $meja->id => $sesi->id])
            ->post(route('emenu.keluar'))
            ->assertRedirect(route('emenu.index'));

        $this->assertNull($sesi->fresh()->perangkat_id);

        $this->sebagai(self::PERANGKAT_DUA)
            ->post(route('emenu.masuk'), ['kode_unik' => $sesi->kode_unik])
            ->assertRedirect(route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);

        $this->assertSame(self::PERANGKAT_DUA, $sesi->fresh()->perangkat_id);
    }

    public function test_perangkat_lama_dianggap_kedaluwarsa_setelah_lima_menit_tanpa_kontak(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi, self::PERANGKAT_SATU, now()->subMinutes(6));

        $this->sebagai(self::PERANGKAT_DUA)
            ->post(route('emenu.masuk'), ['kode_unik' => $sesi->kode_unik])
            ->assertRedirect(route('emenu.meja', $meja->kode_meja))
            ->assertSessionHas('sesi_meja_' . $meja->id, $sesi->id);

        $this->assertSame(self::PERANGKAT_DUA, $sesi->fresh()->perangkat_id);
    }

    public function test_sesi_yang_dibuka_dengan_identitas_perangkat_lain_tidak_bisa_melihat_menu(): void
    {
        $meja = $this->siapkanMeja();
        $sesi = SesiMeja::aktifUntuk($meja);
        $this->klaimPerangkat($sesi);

        $this->sebagai(self::PERANGKAT_DUA, ['sesi_meja_' . $meja->id => $sesi->id])
            ->get(route('emenu.meja', $meja->kode_meja))
            ->assertRedirect(route('emenu.kunci', $meja->kode_meja))
            ->assertSessionHasErrors('kode_unik');

        $this->assertSame(self::PERANGKAT_SATU, $sesi->fresh()->perangkat_id);
    }
}
