<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\BahanStokMutasi;
use App\Models\PembelianBahan;
use App\Models\PembelianBahanDetail;
use App\Models\Pesanan;
use App\Models\ResepMenu;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BahanStokService
{
    public function buatPembelian(array $data, array $items, ?int $penggunaId = null): PembelianBahan
    {
        return DB::transaction(function () use ($data, $items, $penggunaId) {
            $data['created_by'] = $penggunaId;
            $pembelian = PembelianBahan::create($data);

            $this->tulisDetailPembelian($pembelian, $items, $penggunaId);

            return $pembelian->fresh(['detail.bahan', 'pencatat']);
        });
    }

    public function perbaruiPembelian(PembelianBahan $pembelian, array $data, array $items, ?int $penggunaId = null): PembelianBahan
    {
        return DB::transaction(function () use ($pembelian, $data, $items, $penggunaId) {
            $terkunci = PembelianBahan::query()->whereKey($pembelian->id)->lockForUpdate()->firstOrFail();

            $lama = $this->grupItem(
                $terkunci->detail()->get(['bahan_baku_id', 'jumlah', 'harga_satuan'])->toArray()
            );
            $baru = $this->grupItem($items);

            $this->pastikanStokCukupUntukPerubahan($lama, $baru);

            BahanStokMutasi::query()->where('pembelian_bahan_id', $terkunci->id)->delete();
            $terkunci->detail()->delete();

            $terkunci->update($data);

            $this->tulisDetailPembelian($terkunci, $items, $penggunaId);

            // Bahan yang dihapus dari rincian tidak ikut tersinkron oleh
            // tulisDetailPembelian karena tidak ada lagi di daftar item baru.
            $this->sinkronkanBahan(array_diff(array_keys($lama), array_keys($baru)));

            return $terkunci->fresh(['detail.bahan', 'pencatat']);
        });
    }

    public function hapusPembelian(PembelianBahan $pembelian): void
    {
        DB::transaction(function () use ($pembelian) {
            $terkunci = PembelianBahan::query()->whereKey($pembelian->id)->lockForUpdate()->firstOrFail();

            $detail = $this->grupItem(
                $terkunci->detail()->get(['bahan_baku_id', 'jumlah', 'harga_satuan'])->toArray()
            );

            foreach ($detail as $bahanId => $baris) {
                $bahan = BahanBaku::query()->whereKey($bahanId)->lockForUpdate()->first();

                if ($bahan && (float) $bahan->stok - $baris['jumlah'] < -0.0005) {
                    throw new RuntimeException(
                        'Pembelian tidak bisa dihapus karena stok '.$bahan->nama
                        .' sudah terpakai. Sesuaikan stok terlebih dahulu.'
                    );
                }
            }

            BahanStokMutasi::query()->where('pembelian_bahan_id', $terkunci->id)->delete();
            $terkunci->detail()->delete();
            $terkunci->delete();

            $this->sinkronkanBahan(array_keys($detail));
        });
    }

    public function sesuaikanStok(BahanBaku $bahan, float $stokBaru, ?string $keterangan, ?int $penggunaId = null): void
    {
        DB::transaction(function () use ($bahan, $stokBaru, $keterangan, $penggunaId) {
            $terkunci = BahanBaku::query()->whereKey($bahan->id)->lockForUpdate()->firstOrFail();
            $delta = $stokBaru - (float) $terkunci->stok;

            if (abs($delta) < 0.0005) {
                return;
            }

            BahanStokMutasi::create([
                'bahan_baku_id' => $terkunci->id,
                'jenis' => 'penyesuaian',
                'jumlah_delta' => $delta,
                'stok_akhir' => $stokBaru,
                'keterangan' => $keterangan ?: 'Penyesuaian stok manual',
                'created_by' => $penggunaId,
                'created_at' => now(),
            ]);

            $terkunci->update(['stok' => $stokBaru]);
        });
    }

    public function potongSaatPenjualan(Pesanan $pesanan, ?int $penggunaId, string $keterangan): void
    {
        $sudahDiproses = BahanStokMutasi::query()
            ->where('pesanan_id', $pesanan->id)
            ->where('jenis', 'penjualan')
            ->exists();

        if ($sudahDiproses) {
            return;
        }

        $detail = $pesanan->detailPesanan()->get(['menu_id', 'jumlah']);

        if ($detail->isEmpty()) {
            return;
        }

        $resep = ResepMenu::query()
            ->whereIn('menu_id', $detail->pluck('menu_id')->unique()->all())
            ->get(['menu_id', 'bahan_baku_id', 'jumlah'])
            ->groupBy('menu_id');

        if ($resep->isEmpty()) {
            return;
        }

        $kebutuhan = [];

        foreach ($detail as $item) {
            foreach ($resep->get($item->menu_id, []) as $baris) {
                $bahanId = (int) $baris->bahan_baku_id;
                $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0)
                    + ((float) $baris->jumlah * (int) $item->jumlah);
            }
        }

        if ($kebutuhan === []) {
            return;
        }

        ksort($kebutuhan);

        $daftarBahan = BahanBaku::query()
            ->whereIn('id', array_keys($kebutuhan))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($kebutuhan as $bahanId => $butuh) {
            $bahan = $daftarBahan->get($bahanId);

            if (! $bahan || $butuh <= 0) {
                continue;
            }

            if ((float) $bahan->stok + 0.0005 < $butuh) {
                throw new RuntimeException(
                    'Stok bahan '.$bahan->nama.' tersisa '.$this->formatJumlah((float) $bahan->stok)
                    .' '.$bahan->satuan.', tidak cukup untuk pesanan ini.'
                );
            }
        }

        foreach ($kebutuhan as $bahanId => $butuh) {
            $bahan = $daftarBahan->get($bahanId);

            if (! $bahan || $butuh <= 0) {
                continue;
            }

            $stokAkhir = (float) $bahan->stok - $butuh;

            $bahan->update(['stok' => $stokAkhir]);

            BahanStokMutasi::create([
                'bahan_baku_id' => $bahan->id,
                'pesanan_id' => $pesanan->id,
                'jenis' => 'penjualan',
                'jumlah_delta' => -$butuh,
                'stok_akhir' => $stokAkhir,
                'keterangan' => $keterangan.$pesanan->kode_pesanan,
                'created_by' => $penggunaId,
                'created_at' => now(),
            ]);
        }
    }

    public function kembalikanSaatPembatalan(Pesanan $pesanan, ?int $penggunaId, string $alasan): void
    {
        $sudahDikembalikan = BahanStokMutasi::query()
            ->where('pesanan_id', $pesanan->id)
            ->where('jenis', 'pembatalan')
            ->exists();

        if ($sudahDikembalikan) {
            return;
        }

        $pemakaian = BahanStokMutasi::query()
            ->where('pesanan_id', $pesanan->id)
            ->where('jenis', 'penjualan')
            ->get(['bahan_baku_id', 'jumlah_delta']);

        if ($pemakaian->isEmpty()) {
            return;
        }

        $kembali = [];

        foreach ($pemakaian as $baris) {
            $bahanId = (int) $baris->bahan_baku_id;
            $kembali[$bahanId] = ($kembali[$bahanId] ?? 0) + abs((float) $baris->jumlah_delta);
        }

        ksort($kembali);

        $daftarBahan = BahanBaku::query()
            ->whereIn('id', array_keys($kembali))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($kembali as $bahanId => $jumlah) {
            $bahan = $daftarBahan->get($bahanId);

            if (! $bahan || $jumlah <= 0) {
                continue;
            }

            $stokAkhir = (float) $bahan->stok + $jumlah;

            $bahan->update(['stok' => $stokAkhir]);

            BahanStokMutasi::create([
                'bahan_baku_id' => $bahan->id,
                'pesanan_id' => $pesanan->id,
                'jenis' => 'pembatalan',
                'jumlah_delta' => $jumlah,
                'stok_akhir' => $stokAkhir,
                'keterangan' => $alasan,
                'created_by' => $penggunaId,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<int, array{bahan_baku_id?: mixed, jumlah?: mixed, harga_satuan?: mixed}>  $items
     * @return array<int, array{jumlah: float, nilai: float, harga_satuan: float}>
     */
    private function grupItem(array $items): array
    {
        $grup = [];

        foreach ($items as $item) {
            $bahanId = (int) ($item['bahan_baku_id'] ?? 0);
            $jumlah = (float) ($item['jumlah'] ?? 0);
            $harga = (float) ($item['harga_satuan'] ?? 0);

            if ($bahanId <= 0 || $jumlah <= 0) {
                continue;
            }

            $grup[$bahanId] ??= ['jumlah' => 0.0, 'nilai' => 0.0, 'harga_satuan' => 0.0];
            $grup[$bahanId]['jumlah'] += $jumlah;
            $grup[$bahanId]['nilai'] += $jumlah * $harga;
        }

        foreach ($grup as $bahanId => $baris) {
            $grup[$bahanId]['harga_satuan'] = $baris['jumlah'] > 0
                ? $baris['nilai'] / $baris['jumlah']
                : 0.0;
        }

        return $grup;
    }

    private function tulisDetailPembelian(PembelianBahan $pembelian, array $items, ?int $penggunaId): void
    {
        $grup = $this->grupItem($items);
        $total = 0.0;

        foreach ($grup as $bahanId => $baris) {
            $subtotal = round($baris['jumlah'] * $baris['harga_satuan'], 2);
            $total += $subtotal;

            PembelianBahanDetail::create([
                'pembelian_bahan_id' => $pembelian->id,
                'bahan_baku_id' => $bahanId,
                'jumlah' => $baris['jumlah'],
                'harga_satuan' => $baris['harga_satuan'],
                'subtotal' => $subtotal,
            ]);

            BahanStokMutasi::create([
                'bahan_baku_id' => $bahanId,
                'pembelian_bahan_id' => $pembelian->id,
                'jenis' => 'pembelian',
                'jumlah_delta' => $baris['jumlah'],
                'stok_akhir' => 0,
                'keterangan' => 'Pembelian '.($pembelian->supplier ?: 'bahan baku'),
                'created_by' => $penggunaId,
                'created_at' => $pembelian->created_at ?? now(),
            ]);
        }

        if ($grup !== []) {
            $pembelian->update(['total' => $total]);
        }

        $this->sinkronkanBahan(array_keys($grup));
    }

    private function pastikanStokCukupUntukPerubahan(array $lama, array $baru): void
    {
        $semua = array_unique(array_merge(array_keys($lama), array_keys($baru)));
        sort($semua);

        foreach ($semua as $bahanId) {
            $selisih = ($baru[$bahanId]['jumlah'] ?? 0) - ($lama[$bahanId]['jumlah'] ?? 0);

            if ($selisih >= -0.0005) {
                continue;
            }

            $bahan = BahanBaku::query()->whereKey($bahanId)->lockForUpdate()->first();

            if ($bahan && (float) $bahan->stok + $selisih < -0.0005) {
                throw new RuntimeException(
                    'Perubahan pembelian membuat stok '.$bahan->nama.' menjadi negatif. '
                    .'Sesuaikan stok terlebih dahulu.'
                );
            }
        }
    }

    private function sinkronkanBahan(array $bahanIds): void
    {
        $bahanIds = array_values(array_unique(array_map('intval', $bahanIds)));
        sort($bahanIds);

        foreach ($bahanIds as $bahanId) {
            if ($bahanId <= 0) {
                continue;
            }

            $bahan = BahanBaku::query()->whereKey($bahanId)->lockForUpdate()->first();

            if (! $bahan) {
                continue;
            }

            $saldo = 0.0;

            BahanStokMutasi::query()
                ->where('bahan_baku_id', $bahanId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->each(function (BahanStokMutasi $mutasi) use (&$saldo) {
                    $saldo += (float) $mutasi->jumlah_delta;

                    if (abs((float) $mutasi->stok_akhir - $saldo) > 0.0005) {
                        $mutasi->update(['stok_akhir' => $saldo]);
                    }
                });

            $agregat = PembelianBahanDetail::query()
                ->where('bahan_baku_id', $bahanId)
                ->selectRaw('COALESCE(SUM(jumlah), 0) as total_jumlah, COALESCE(SUM(subtotal), 0) as total_nilai')
                ->first();

            $totalJumlah = (float) ($agregat->total_jumlah ?? 0);
            $totalNilai = (float) ($agregat->total_nilai ?? 0);

            $bahan->update([
                'stok' => $saldo,
                'harga_rata_rata' => $totalJumlah > 0 ? $totalNilai / $totalJumlah : 0,
            ]);
        }
    }

    private function formatJumlah(float $jumlah): string
    {
        return rtrim(rtrim(number_format($jumlah, 3, ',', '.'), '0'), ',');
    }
}
