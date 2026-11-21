<?php

namespace App\Services;

use App\Events\MenuDiperbarui;
use App\Models\Keranjang;
use App\Models\KeranjangItem;
use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\SesiMeja;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class KeranjangService
{
    public const MENIT_KEDALUWARSA = 30;

    /**
     * Keranjang aktif milik satu sesi dan perangkat.
     */
    public function aktif(SesiMeja $sesi, string $perangkatId): ?Keranjang
    {
        $keranjang = Keranjang::query()
            ->with(['items.menu'])
            ->where('sesi_meja_id', $sesi->id)
            ->where('perangkat_id', $perangkatId)
            ->first();

        if (! $keranjang) {
            return null;
        }

        if ($keranjang->kedaluwarsa()) {
            $this->lepas($keranjang);

            return null;
        }

        return $keranjang;
    }

    /**
     * Tetapkan jumlah absolut satu menu pada keranjang.
     *
     * Stok menu dan stok bahan langsung dipotong ketika jumlah bertambah.
     * Pengurangan jumlah atau jumlah 0 akan mengembalikan stok.
     */
    public function setItem(
        MejaMakan $meja,
        SesiMeja $sesi,
        string $perangkatId,
        int $menuId,
        int $jumlah,
        ?string $catatan = null
    ): ?Keranjang {
        $jumlah = max(0, min(99, $jumlah));

        $keranjang = DB::transaction(function () use ($meja, $sesi, $perangkatId, $menuId, $jumlah, $catatan) {
            $menu = Menu::query()->whereKey($menuId)->lockForUpdate()->first();

            if (! $menu || ! $menu->is_aktif) {
                throw new RuntimeException('Menu yang dipilih sedang tidak tersedia.');
            }

            $keranjang = Keranjang::query()
                ->where('sesi_meja_id', $sesi->id)
                ->where('perangkat_id', $perangkatId)
                ->lockForUpdate()
                ->first();

            if ($keranjang && $keranjang->kedaluwarsa()) {
                $this->lepasIsi($keranjang);
                $keranjang = null;
            }

            if (! $keranjang) {
                $keranjang = Keranjang::create([
                    'meja_id' => $meja->id,
                    'sesi_meja_id' => $sesi->id,
                    'perangkat_id' => $perangkatId,
                    'expires_at' => now()->addMinutes(self::MENIT_KEDALUWARSA),
                ]);
            } else {
                $keranjang->update([
                    'expires_at' => now()->addMinutes(self::MENIT_KEDALUWARSA),
                ]);
            }

            $item = KeranjangItem::query()
                ->where('keranjang_id', $keranjang->id)
                ->where('menu_id', $menu->id)
                ->lockForUpdate()
                ->first();

            $lama = $item?->jumlah ?? 0;
            $selisih = $jumlah - $lama;

            if ($selisih > 0 && (int) $menu->stok_menu < $selisih) {
                throw new RuntimeException(
                    'Stok '.$menu->nama_menu.' tersisa '.max(0, (int) $menu->stok_menu)
                    .' porsi, tidak cukup untuk permintaan ini.'
                );
            }

            if ($selisih !== 0) {
                $menu->update([
                    'stok_menu' => (int) $menu->stok_menu - $selisih,
                ]);
            }

            if ($jumlah <= 0) {
                if ($item) {
                    app(BahanStokService::class)->gantiReservasiKeranjang($item, $menu, 0, auth()->id());
                    $item->delete();
                }
            } else {
                if ($item) {
                    $item->update([
                        'jumlah' => $jumlah,
                        'catatan' => $catatan ?? $item->catatan,
                    ]);
                } else {
                    $item = KeranjangItem::create([
                        'keranjang_id' => $keranjang->id,
                        'menu_id' => $menu->id,
                        'jumlah' => $jumlah,
                        'catatan' => $catatan,
                    ]);
                }

                app(BahanStokService::class)->gantiReservasiKeranjang($item, $menu, $jumlah, auth()->id());
            }

            if ($keranjang->items()->count() === 0) {
                $keranjang->delete();
                $keranjang = null;
            }

            DB::afterCommit(fn () => Realtime::emit(new MenuDiperbarui('stok')));

            return $keranjang;
        });

        return $keranjang?->fresh(['items.menu']);
    }

    /**
     * Lepas satu keranjang dan kembalikan seluruh stoknya.
     */
    public function lepas(Keranjang $keranjang): void
    {
        DB::transaction(function () use ($keranjang) {
            $terkunci = Keranjang::query()->whereKey($keranjang->id)->lockForUpdate()->first();

            if (! $terkunci) {
                return;
            }

            $this->lepasIsi($terkunci);

            DB::afterCommit(fn () => Realtime::emit(new MenuDiperbarui('stok')));
        });
    }

    /**
     * Bersihkan keranjang yang sudah melewati batas waktu.
     */
    public function bersihkanKedaluwarsa(?int $sesiMejaId = null): int
    {
        $query = Keranjang::query()->where('expires_at', '<=', now());

        if ($sesiMejaId !== null) {
            $query->where('sesi_meja_id', $sesiMejaId);
        }

        $jumlah = 0;

        $query->chunkById(100, function ($daftar) use (&$jumlah) {
            foreach ($daftar as $keranjang) {
                if (! $keranjang->kedaluwarsa()) {
                    continue;
                }

                $this->lepas($keranjang);
                $jumlah++;
            }
        });

        return $jumlah;
    }

    public function lepasUntukSesi(int $sesiMejaId): void
    {
        Keranjang::query()
            ->where('sesi_meja_id', $sesiMejaId)
            ->get()
            ->each(fn (Keranjang $keranjang) => $this->lepas($keranjang));
    }

    public function lepasUntukSesiPerangkat(int $sesiMejaId, string $perangkatId): void
    {
        Keranjang::query()
            ->where('sesi_meja_id', $sesiMejaId)
            ->where('perangkat_id', $perangkatId)
            ->get()
            ->each(fn (Keranjang $keranjang) => $this->lepas($keranjang));
    }

    /**
     * Hapus keranjang setelah checkout tanpa mengembalikan stok.
     *
     * Reservasi menu dan bahan sudah dikonversi menjadi penjualan.
     */
    public function hapusSetelahCheckout(Keranjang $keranjang): void
    {
        DB::transaction(function () use ($keranjang) {
            $terkunci = Keranjang::query()->whereKey($keranjang->id)->lockForUpdate()->first();

            if (! $terkunci) {
                return;
            }

            $terkunci->items()->delete();
            $terkunci->delete();
        });
    }

    /**
     * Ringkasan keranjang untuk dikirim ke JavaScript pelanggan.
     */
    public function payload(Keranjang $keranjang, ?Menu $menu = null): array
    {
        $keranjang->loadMissing(['items.menu']);

        $items = $keranjang->items
            ->filter(fn (KeranjangItem $item) => $item->menu !== null)
            ->map(function (KeranjangItem $item) {
                $stok = max(0, (int) $item->menu->stok_menu + (int) $item->jumlah);

                return [
                    'id' => $item->menu_id,
                    'name' => $item->menu->nama_menu,
                    'price' => (float) $item->menu->harga,
                    'qty' => (int) $item->jumlah,
                    'stock' => $stok,
                    'image' => $item->menu->foto_menu ? Storage::disk()->url($item->menu->foto_menu) : '',
                    'note' => (string) ($item->catatan ?? ''),
                ];
            })
            ->values()
            ->all();

        $subtotal = collect($items)->sum(fn (array $item) => $item['price'] * $item['qty']);

        $payload = [
            'items' => $items,
            'count' => collect($items)->sum('qty'),
            'subtotal' => $subtotal,
            'expires_at' => $keranjang->expires_at?->toIso8601String(),
        ];

        if ($menu !== null) {
            $milikSendiri = collect($items)->firstWhere('id', $menu->id);
            $stok = max(0, (int) $menu->stok_menu + (int) ($milikSendiri['qty'] ?? 0));

            $payload['menu'] = [
                'id' => $menu->id,
                'stock' => $stok,
                'isSoldOut' => $stok <= 0,
            ];
        }

        return $payload;
    }

    /**
     * Lepas isi keranjang dan kembalikan stok menu serta bahan.
     *
     * Harus dipanggil di dalam transaksi yang sudah mengunci keranjang.
     */
    private function lepasIsi(Keranjang $keranjang): void
    {
        $items = $keranjang->items()->get();

        foreach ($items as $item) {
            $menu = Menu::query()->whereKey($item->menu_id)->lockForUpdate()->first();

            if ($menu) {
                $menu->increment('stok_menu', (int) $item->jumlah);
            }
        }

        app(BahanStokService::class)->lepasReservasiKeranjang($keranjang);

        $keranjang->items()->delete();
        $keranjang->delete();
    }
}
