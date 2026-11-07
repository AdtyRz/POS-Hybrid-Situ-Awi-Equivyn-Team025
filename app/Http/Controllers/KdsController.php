<?php

namespace App\Http\Controllers;

use App\Events\StatusPesananBerubah;
use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Services\ShiftService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KdsController extends Controller
{
    public function index(Request $request): View
    {
        $target = $request->route('target', 'dapur');

        if ($target === 'bar') {
            $judul = 'Bar';
            $subJudul = 'Beverage Display System';
            $lencana = 'KDS BAR — MINUMAN & KOPI';
            $tampilan = 'BARISTA VIEW';
            $kataMulai = 'Racik';
            $kataSiap = 'Minuman';
            $kataRunner = 'Bar';
            $stasiunNama = 'Bar & Racikan';
            $stasiunSub = 'STATION POS 02 • KHUSUS MINUMAN';
            $stasiunUtama = 'Bar Utama';
            $stasiun = 'Bar';
        } else {
            $target = 'dapur';
            $judul = 'Dapur';
            $subJudul = 'Kitchen Display System';
            $lencana = 'KDS DAPUR — MAKANAN & BAKARAN';
            $tampilan = 'KOKI VIEW';
            $kataMulai = 'Masak';
            $kataSiap = 'Makanan';
            $kataRunner = 'Dapur';
            $stasiunNama = 'Hot Kitchen & Bakaran';
            $stasiunSub = 'STATION POS 01 • KHUSUS MAKANAN';
            $stasiunUtama = 'Dapur Utama';
            $stasiun = 'Dapur';
        }

        $state = $this->stateData($target);
        $labelShift = ShiftService::ringkasan()['label'];

        return view('kds.index', compact(
            'target', 'judul', 'subJudul', 'lencana', 'tampilan',
            'kataMulai', 'kataSiap', 'kataRunner',
            'stasiunNama', 'stasiunSub', 'stasiunUtama', 'stasiun',
            'labelShift'
        ) + [
            'tiketJson' => $state['tiketJson'],
            'selesaiHariIni' => $state['selesaiHariIni'],
            'rataRataMasakTeks' => $state['rataRataMasakTeks'],
        ]);
    }

    public function state(Request $request): JsonResponse
    {
        $target = $request->route('target', 'dapur') === 'bar' ? 'bar' : 'dapur';
        $state = $this->stateData($target);

        return response()->json([
            'target' => $target,
            'tickets' => $state['tiketJson'],
            'completedPortions' => $state['selesaiHariIni'],
            'averageCookText' => $state['rataRataMasakTeks'],
            'labelShift' => ShiftService::ringkasan()['label'],
            'updatedAt' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, max-age=0');
    }

    private function stateData(string $target, bool $lengkap = true): array
    {
        $tiket = DetailPesanan::with(['menu', 'pesanan.meja'])
            ->whereHas('menu.kategori', function ($query) use ($target) {
                $query->where('target_kds', $target);
            })
            ->whereIn('status_item', ['menunggu', 'diproses', 'siap'])
            ->whereHas('pesanan', function ($query) {



                $query->whereIn('status_pesanan', ['menunggu', 'diproses', 'siap']);
            })
            ->orderBy('created_at')
            ->get()
            ->groupBy('pesanan_id');

        $tiketJson = $tiket->map(function ($baris, $pesananId) {
            $pesanan = $baris->first()->pesanan;
            $status = $this->statusTiket($baris);




            if ($status === 'siap') {
                $waktuSelesai = $baris->max('updated_at');
                $detik = $waktuSelesai
                    ? max(0, (int) $pesanan->waktu_pesan->diffInSeconds($waktuSelesai))
                    : 0;
            } else {
                $detik = (int) now()->diffInSeconds($pesanan->waktu_pesan);
            }

            return [
                'dbId' => $pesanan->id,
                'id' => '#' . $pesanan->kode_pesanan,
                'saung' => 'SAUNG ' . $pesanan->meja->kode_meja,
                'customer' => $pesanan->nama_pelanggan ?: 'Tamu ' . $pesanan->meja->kode_meja,
                'status' => $status,
                'seconds' => $detik,
                'frozen' => $status === 'siap',
                'isOverdue' => $detik >= 900 && $status !== 'siap',
                'passBarMessage' => 'Di Meja Pass Bar: Menunggu pelayan mengantar ke Saung ' . $pesanan->meja->kode_meja . '.',
                'items' => $baris->map(function ($detail) {
                    return [
                        'dbId' => $detail->id,
                        'name' => $detail->menu->nama_menu,
                        'qty' => $detail->jumlah . 'x',
                        'status' => $detail->status_item,
                        'started' => $detail->status_item !== 'menunggu',
                        'checked' => $detail->status_item === 'siap',
                        'note' => $detail->catatan ? 'Catatan: ' . $detail->catatan : null,
                        'noteType' => $detail->catatan ? (str_contains(strtolower($detail->catatan), 'pedas') ? 'warning' : 'info') : null,
                    ];
                })->values(),
            ];
        })->values();

        if (! $lengkap) {
            return [
                'tiketJson' => $tiketJson,
            ];
        }

        $selesaiHariIni = DetailPesanan::where('status_item', 'siap')
            ->whereDate('updated_at', today())
            ->whereHas('menu.kategori', function ($query) use ($target) {
                $query->where('target_kds', $target);
            })
            ->sum('jumlah');




        $pesananSelesaiHariIni = DetailPesanan::with('pesanan')
            ->whereHas('menu.kategori', function ($query) use ($target) {
                $query->where('target_kds', $target);
            })
            ->where('status_item', 'siap')
            ->whereDate('updated_at', today())
            ->get()
            ->groupBy('pesanan_id');

        $durasiMasakList = $pesananSelesaiHariIni->map(function ($items) {
            $pesanan = $items->first()->pesanan;
            if (! $pesanan || ! $pesanan->waktu_pesan || ! $pesanan->waktu_pesan->isToday()) {
                return null;
            }
            $waktuSelesai = $items->max('updated_at');
            if (! $waktuSelesai) {
                return null;
            }
            return max(0, (int) $pesanan->waktu_pesan->diffInSeconds($waktuSelesai));
        })->filter(fn ($d) => $d !== null && $d > 0)->values();

        $rataRataMasakMenit = $durasiMasakList->isNotEmpty()
            ? max(1, (int) round($durasiMasakList->avg() / 60))
            : 0;

        return [
            'tiketJson' => $tiketJson,
            'selesaiHariIni' => (int) $selesaiHariIni,
            'rataRataMasakTeks' => $rataRataMasakMenit > 0 ? $rataRataMasakMenit . ' Mnt' : '— Mnt',
        ];
    }

    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $target = $request->route('target', 'dapur');

        $data = $request->validate([
            'aksi' => ['required', 'in:mulai,item,runner'],
            'item_id' => ['required_if:aksi,item', 'nullable', 'integer', 'exists:detail_pesanan,id'],
        ]);

        $statusPayload = null;

        DB::transaction(function () use ($target, $id, $data, &$statusPayload) {
            $pesanan = Pesanan::with('meja')->findOrFail($id);

            if ($data['aksi'] === 'runner') {
                $semuaItemStasiun = DetailPesanan::where('pesanan_id', $pesanan->id)
                    ->whereHas('menu.kategori', function ($query) use ($target) {
                        $query->where('target_kds', $target);
                    })
                    ->get();

                if ($semuaItemStasiun->isEmpty() || $semuaItemStasiun->contains(
                    fn ($item) => $item->status_item !== 'siap'
                )) {
                    throw ValidationException::withMessages([
                        'aksi' => 'Panggil runner hanya bisa dilakukan setelah semua item stasiun ini selesai.',
                    ]);
                }

                $statusPesananBaru = $this->statusPesanan($pesanan);

                if ($pesanan->status_pesanan !== $statusPesananBaru) {
                    DB::table('pesanan')
                        ->where('id', $pesanan->id)
                        ->update([
                            'status_pesanan' => $statusPesananBaru,
                            'updated_at' => now(),
                        ]);

                    $pesanan->status_pesanan = $statusPesananBaru;

                    $statusPayload = [
                        'kode_pesanan' => $pesanan->kode_pesanan,
                        'meja' => $pesanan->meja->kode_meja ?? null,
                        'status_pesanan' => $pesanan->status_pesanan,
                        'status_pembayaran' => $pesanan->status_pembayaran,
                        'metode_pembayaran' => $pesanan->metode_pembayaran,
                    ];
                }

                return;
            }

            if ($data['aksi'] === 'item') {
                $detail = DetailPesanan::where('id', $data['item_id'])
                    ->where('pesanan_id', $pesanan->id)
                    ->whereHas('menu.kategori', function ($query) use ($target) {
                        $query->where('target_kds', $target);
                    })
                    ->firstOrFail();

                if ($detail->status_item === 'menunggu') {
                    throw ValidationException::withMessages([
                        'item_id' => 'Tekan Mulai ' . ($target === 'bar' ? 'Racik' : 'Masak') . ' terlebih dahulu sebelum menandai item selesai.',
                    ]);
                }

                $statusItemBaru = $detail->status_item === 'siap' ? 'diproses' : 'siap';

                DB::table('detail_pesanan')
                    ->where('id', $detail->id)
                    ->update([
                        'status_item' => $statusItemBaru,
                        'updated_at' => now(),
                    ]);
            } else {
                DetailPesanan::where('pesanan_id', $pesanan->id)
                    ->whereHas('menu.kategori', function ($query) use ($target) {
                        $query->where('target_kds', $target);
                    })
                    ->where('status_item', 'menunggu')
                    ->update(['status_item' => 'diproses']);
            }

            $statusPesananBaru = $this->statusPesanan($pesanan);





            if ($statusPesananBaru === Pesanan::STATUS_SIAP
                && $pesanan->status_pesanan !== Pesanan::STATUS_SIAP) {
                $statusPesananBaru = Pesanan::STATUS_DIPROSES;
            }

            if ($pesanan->status_pesanan !== $statusPesananBaru) {


                DB::table('pesanan')
                    ->where('id', $pesanan->id)
                    ->update([
                        'status_pesanan' => $statusPesananBaru,
                        'updated_at' => now(),
                    ]);
            }

            $pesanan->status_pesanan = $statusPesananBaru;

            $statusPayload = [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'meja' => $pesanan->meja->kode_meja ?? null,
                'status_pesanan' => $pesanan->status_pesanan,
                'status_pembayaran' => $pesanan->status_pembayaran,
                'metode_pembayaran' => $pesanan->metode_pembayaran,
            ];
        });

        if ($statusPayload) {
            Realtime::emit(new StatusPesananBerubah($statusPayload));
        }

        if ($request->expectsJson() || $request->ajax()) {
            $target = $target === 'bar' ? 'bar' : 'dapur';
            $state = $this->stateData($target, false);

            return response()->json([
                'target' => $target,
                'tickets' => $state['tiketJson'],
                'updatedAt' => now()->toIso8601String(),
            ])->header('Cache-Control', 'no-store, max-age=0');
        }

        return back();
    }

    private function statusTiket($baris): string
    {
        if ($baris->every(fn ($detail) => $detail->status_item === 'siap')) {
            return 'siap';
        }

        if ($baris->contains(fn ($detail) => $detail->status_item !== 'menunggu')) {
            return 'diproses';
        }

        return 'menunggu';
    }

    private function statusPesanan(Pesanan $pesanan): string
    {




        $allStatus = DetailPesanan::where('pesanan_id', $pesanan->id)
            ->pluck('status_item');

        if ($allStatus->every(fn ($item) => $item === 'siap')) {
            return 'siap';
        }

        if ($allStatus->contains(fn ($item) => $item !== 'menunggu')) {
            return 'diproses';
        }

        return 'menunggu';
    }
}
