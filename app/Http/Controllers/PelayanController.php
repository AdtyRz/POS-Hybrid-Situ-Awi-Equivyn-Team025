<?php

namespace App\Http\Controllers;

use App\Events\PanggilanPelayan;
use App\Events\PesananBaru;
use App\Events\SesiMejaBerubah;
use App\Models\Kategori;
use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use App\Services\PesananService;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PelayanController extends Controller
{
    private function getPelayanArea(): ?string
    {
        return auth()->user()->area;
    }

    private function getMejaByArea(): Collection
    {
        $area = $this->getPelayanArea();

        if (! $area) {
            return MejaMakan::orderBy('kode_meja')->get();
        }

        return MejaMakan::where('area', $area)->orderBy('kode_meja')->get();
    }

    private function filterByArea($query)
    {
        $area = $this->getPelayanArea();

        if ($area) {
            return $query->whereHas('meja', fn ($q) => $q->where('area', $area));
        }

        return $query;
    }

    private function labelArea(): string
    {
        return $this->getPelayanArea() ?: 'Semua Area Saung';
    }

    private function pastikanAreaMeja(MejaMakan $meja): void
    {
        $area = $this->getPelayanArea();

        if ($area && $meja->area !== $area) {
            abort(403, 'Meja ini berada di luar area tugas Anda.');
        }
    }

    private function infoShift(): array
    {
        $sekarang = Carbon::now('Asia/Jakarta');
        $jam = $sekarang->format('H:i');

        $shift = match (true) {
            $jam >= '08:00' && $jam < '12:00' => [
                'nama' => 'Shift Pagi',
                'mulai' => '08:00:00',
                'selesai' => '12:00:00',
            ],
            $jam >= '12:00' && $jam < '18:00' => [
                'nama' => 'Shift Siang',
                'mulai' => '12:00:00',
                'selesai' => '18:00:00',
            ],
            $jam >= '18:00' => [
                'nama' => 'Shift Malam',
                'mulai' => '18:00:00',
                'selesai' => '23:59:59',
            ],
            default => [
                'nama' => 'Luar Shift',
                'mulai' => '00:00:00',
                'selesai' => '08:00:00',
            ],
        };

        $selesai = $sekarang->copy()->setTimeFromTimeString($shift['selesai']);
        $sisaMenit = max(0, (int) $sekarang->diffInMinutes($selesai, false));
        $sisaJam = intdiv($sisaMenit, 60);
        $sisaSisaMenit = $sisaMenit % 60;

        return [
            'nama' => $shift['nama'],
            'mulai' => substr($shift['mulai'], 0, 5),
            'selesai' => substr($shift['selesai'], 0, 5),
            'label' => $shift['nama'].' ('.substr($shift['mulai'], 0, 5).' - '.substr($shift['selesai'], 0, 5).' WIB)',
            'sisa_menit' => $sisaMenit,
            'sisa_label' => $sisaMenit > 0
                ? ($sisaJam > 0 ? 'Sisa '.$sisaJam.' Jam '.$sisaSisaMenit.' Mnt' : 'Sisa '.$sisaSisaMenit.' Mnt')
                : 'Shift Selesai',
        ];
    }

    private function infoPerangkat(Collection $meja): array
    {
        $total = $meja->count();
        $terhubung = $meja->filter(fn ($m) => filled($m->id_device))->count();

        return [
            'total' => $total,
            'terhubung' => $terhubung,
            'label' => 'IoT '.$terhubung.'/'.$total.' Meja',
            'ringkas' => $terhubung.'/'.$total.' Node',
        ];
    }

    private function dataNavigasi(): array
    {
        $panggilan = $this->filterByArea(
            LogPanggilPelayan::query()
        )->where('status_panggilan', 'menunggu')->count();

        $siap = $this->filterByArea(
            Pesanan::query()
        )->where('status_pesanan', Pesanan::STATUS_SIAP)->count();

        return [
            'navPanggilanCount' => $panggilan,
            'navReadyCount' => $siap,
        ];
    }

    private function panggilanMenunggu(): Collection
    {
        return $this->filterByArea(
            LogPanggilPelayan::with(['meja', 'pelayan'])
        )->where('status_panggilan', 'menunggu')
            ->orderBy('waktu_panggil')
            ->get();
    }

    private function pesananAktif(): Collection
    {
        return $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->whereNotIn('status_pesanan', [Pesanan::STATUS_SELESAI, Pesanan::STATUS_DIBATALKAN])
            ->orderBy('waktu_pesan')
            ->get();
    }

    private function sesiAktif(Collection $meja): Collection
    {
        return SesiMeja::query()
            ->whereIn('meja_id', $meja->pluck('id'))
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('meja_id');
    }

    private function waktuRelatif($waktu): string
    {
        if (! $waktu) {
            return '-';
        }

        $detik = max(0, now()->getTimestamp() - $waktu->getTimestamp());

        if ($detik < 60) {
            return 'Baru saja';
        }

        $menit = intdiv($detik, 60);

        if ($menit < 60) {
            return $menit.' mnt lalu';
        }

        $jam = intdiv($menit, 60);

        if ($jam < 24) {
            return $jam.' jam lalu';
        }

        return intdiv($jam, 24).' hari lalu';
    }

    private function payloadPanggilan(LogPanggilPelayan $log): array
    {
        $meja = $log->meja;
        $device = filled($meja?->id_device) ? $meja->id_device : 'Tombol meja';

        return [
            'id' => $log->id,
            'meja_id' => $log->meja_id,
            'kode_meja' => $meja?->kode_meja ?? '-',
            'area' => $meja?->area ?? '-',
            'nama_saung' => 'Saung '.($meja?->kode_meja ?? '-'),
            'status' => $log->status_panggilan,
            'waktu_panggil' => $log->waktu_panggil?->toIso8601String(),
            'waktu_label' => $log->waktu_panggil?->timezone('Asia/Jakarta')->format('H:i') ?? '-',
            'time_ago' => $this->waktuRelatif($log->waktu_panggil),
            'elapsed_seconds' => $log->waktu_panggil ? max(0, now()->getTimestamp() - $log->waktu_panggil->getTimestamp()) : 0,
            'device' => $device,
            'pelayan' => $log->pelayan?->nama,
        ];
    }

    private function payloadPesanan(Pesanan $pesanan): array
    {
        $detail = $pesanan->detailPesanan;
        $targets = $detail
            ->map(fn ($item) => $item->menu?->kategori?->target_kds ?? 'dapur')
            ->unique()
            ->values();
        $hasDapur = $targets->contains('dapur');
        $hasBar = $targets->contains('bar');
        $source = $hasDapur && $hasBar ? 'campuran' : ($hasBar ? 'bar' : 'dapur');
        $sourceLabel = match ($source) {
            'bar' => 'Bar Counter',
            'campuran' => 'Dapur & Bar',
            default => 'Dapur Utama',
        };
        $items = $detail->map(function ($item) {
            $statusLabel = match ($item->status_item) {
                'siap' => 'Siap',
                'diantar' => 'Diantar',
                'diproses' => 'Diproses',
                default => 'Menunggu',
            };

            return [
                'id' => $item->id,
                'menu_id' => $item->menu_id,
                'nama' => $item->menu?->nama_menu ?? 'Menu',
                'kategori' => $item->menu?->kategori?->nama_kategori ?? '-',
                'target_kds' => $item->menu?->kategori?->target_kds ?? 'dapur',
                'jumlah' => (int) $item->jumlah,
                'catatan' => $item->catatan,
                'status_item' => $item->status_item,
                'status_label' => $statusLabel,
                'subtotal' => (float) $item->subtotal,
            ];
        })->values();
        $statusLabel = match ($pesanan->status_pesanan) {
            Pesanan::STATUS_MENUNGGU_VERIFIKASI => 'Verifikasi Bayar',
            Pesanan::STATUS_MENUNGGU_PEMBAYARAN => 'Bayar di Kasir',
            Pesanan::STATUS_DIPROSES => 'Diproses',
            Pesanan::STATUS_SIAP => 'Siap Antar',
            Pesanan::STATUS_DIANTAR => 'Diantar',
            Pesanan::STATUS_SELESAI => 'Selesai',
            Pesanan::STATUS_DIBATALKAN => 'Dibatalkan',
            default => 'Menunggu',
        };

        return [
            'id' => $pesanan->id,
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => [
                'id' => $pesanan->meja?->id,
                'kode_meja' => $pesanan->meja?->kode_meja ?? '-',
                'area' => $pesanan->meja?->area ?? '-',
                'kapasitas' => $pesanan->meja?->kapasitas,
            ],
            'customer' => $pesanan->nama_pelanggan ?: 'Tamu',
            'status_pesanan' => $pesanan->status_pesanan,
            'status_label' => $statusLabel,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'sumber' => $source,
            'sumber_label' => $sourceLabel,
            'has_dapur' => $hasDapur,
            'has_bar' => $hasBar,
            'filter_tags' => collect([$hasDapur ? 'kitchen' : null, $hasBar ? 'bar' : null])->filter()->values()->all(),
            'items' => $items->all(),
            'item_count' => $items->count(),
            'total_qty' => (int) $detail->sum('jumlah'),
            'total' => (float) $pesanan->total_bayar,
            'total_format' => 'Rp '.number_format((float) $pesanan->total_bayar, 0, ',', '.'),
            'catatan' => $pesanan->catatan,
            'waktu_pesan' => $pesanan->waktu_pesan?->toIso8601String(),
            'waktu_label' => $pesanan->waktu_pesan?->timezone('Asia/Jakarta')->format('H:i') ?? '-',
            'time_ago' => $this->waktuRelatif($pesanan->waktu_pesan),
            'main_item' => $items->first()
                ? $items->first()['jumlah'].'x '.$items->first()['nama']
                : 'Belum ada item',
            'side_items' => $items->count() > 1 ? '+'.($items->count() - 1).' item lain' : '',
            'note' => $items->pluck('catatan')->filter()->unique()->implode('; ') ?: 'Tanpa catatan',
        ];
    }

    private function payloadMeja(MejaMakan $meja, Collection $panggilan, Collection $pesanan, ?SesiMeja $sesi): array
    {
        $panggilanMeja = $panggilan->first(fn ($item) => (int) $item->meja_id === (int) $meja->id);
        $pesananMeja = $pesanan
            ->filter(fn ($item) => (int) $item->meja_id === (int) $meja->id)
            ->sortBy('waktu_pesan')
            ->values();
        $orders = $pesananMeja->map(fn ($item) => $this->payloadPesanan($item))->values();
        $items = $orders->flatMap(fn ($item) => $item['items'])->values();
        $totalQty = (int) $items->sum('jumlah');
        $readyQty = (int) $items
            ->filter(fn ($item) => in_array($item['status_item'], ['siap', 'diantar'], true))
            ->sum('jumlah');
        $progress = $totalQty > 0 ? (int) round($readyQty / $totalQty * 100) : 0;
        $total = (float) $pesananMeja->sum(fn ($item) => (float) $item->total_bayar);
        $primary = $pesananMeja->first(fn ($item) => $item->status_pesanan === Pesanan::STATUS_SIAP)
            ?? $pesananMeja->first(fn ($item) => $item->status_pesanan === Pesanan::STATUS_DIANTAR)
            ?? $pesananMeja->first(fn ($item) => $item->status_pesanan === Pesanan::STATUS_DIPROSES)
            ?? $pesananMeja->last();
        $customer = $pesananMeja
            ->map(fn ($item) => $item->nama_pelanggan)
            ->filter()
            ->first();

        $status = 'tersedia';
        $statusLabel = 'Tersedia';
        $statusColor = 'text-[#00341A]';
        $statusDot = 'bg-[#0B4D2B]';
        $cardClass = 'bg-[#F6F3EC] border-[#E5E2DB]/70';
        $borderClass = 'border-[#E5E2DB]';

        if ($panggilanMeja) {
            $status = 'panggilan';
            $statusLabel = 'PANGGIL!';
            $statusColor = 'text-[#93000A]';
            $statusDot = 'bg-[#BA1A1A] animate-ping';
            $cardClass = 'bg-white border-[#BA1A1A]/30 ring-1 ring-[#BA1A1A]';
            $borderClass = 'border-[#BA1A1A]/30';
        } elseif ($pesananMeja->contains('status_pesanan', Pesanan::STATUS_SIAP)) {
            $status = 'siap_antar';
            $statusLabel = 'Siap Antar';
            $statusColor = 'text-[#7A5900]';
            $statusDot = 'bg-[#7A5900]';
            $cardClass = 'bg-white border-[#FECE66]/70';
            $borderClass = 'border-[#FECE66]/70';
        } elseif ($pesananMeja->contains('status_pesanan', Pesanan::STATUS_DIANTAR)) {
            $status = 'diantar';
            $statusLabel = 'Diantar';
            $statusColor = 'text-[#7A5900]';
            $statusDot = 'bg-[#FECE66]';
            $cardClass = 'bg-white border-[#F0EEE7]';
            $borderClass = 'border-[#F0EEE7]';
        } elseif ($pesananMeja->contains('status_pesanan', Pesanan::STATUS_DIPROSES)) {
            $status = 'diproses';
            $statusLabel = 'Diproses';
            $statusColor = 'text-[#7A5900]';
            $statusDot = 'bg-[#7A5900]';
            $cardClass = 'bg-white border-[#F0EEE7]';
            $borderClass = 'border-[#F0EEE7]';
        } elseif ($pesananMeja->contains('status_pesanan', Pesanan::STATUS_MENUNGGU_VERIFIKASI)) {
            $status = 'verifikasi';
            $statusLabel = 'Verifikasi Bayar';
            $statusColor = 'text-[#7A5900]';
            $statusDot = 'bg-[#FECE66]';
            $cardClass = 'bg-white border-[#F0EEE7]';
            $borderClass = 'border-[#F0EEE7]';
        } elseif ($pesananMeja->contains('status_pesanan', Pesanan::STATUS_MENUNGGU_PEMBAYARAN)) {
            $status = 'pembayaran';
            $statusLabel = 'Bayar di Kasir';
            $statusColor = 'text-[#7A5900]';
            $statusDot = 'bg-[#FECE66]';
            $cardClass = 'bg-white border-[#F0EEE7]';
            $borderClass = 'border-[#F0EEE7]';
        } elseif ($pesananMeja->isNotEmpty()) {
            $status = 'menunggu';
            $statusLabel = 'Menunggu';
            $statusColor = 'text-[#00341A]';
            $statusDot = 'bg-[#0B4D2B]';
            $cardClass = 'bg-white border-[#F0EEE7]';
            $borderClass = 'border-[#F0EEE7]';
        } elseif ($sesi) {
            $status = 'sesi';
            $statusLabel = 'Sesi Aktif';
            $statusColor = 'text-[#00341A]';
            $statusDot = 'bg-[#0B4D2B]';
            $cardClass = 'bg-white border-[#F0EEE7]';
            $borderClass = 'border-[#F0EEE7]';
        }

        $billInfo = $pesananMeja->isEmpty()
            ? 'Kapasitas '.$meja->kapasitas.' Orang'
            : 'Bill #'.$pesananMeja->pluck('kode_pesanan')->take(2)->implode(', ')
                .($pesananMeja->count() > 2 ? ' +'.($pesananMeja->count() - 2) : '')
                .' • '.($customer ?: ($meja->kapasitas.' Tamu'))
                .' • Rp '.number_format($total, 0, ',', '.');
        $itemsSummary = $orders
            ->flatMap(fn ($item) => $item['items'])
            ->take(2)
            ->map(fn ($item) => $item['jumlah'].'x '.$item['nama'])
            ->implode(', ');
        $sisaItem = max(0, $items->count() - 2);
        $primaryPayload = $primary ? $this->payloadPesanan($primary) : null;
        $waktu = $panggilanMeja?->waktu_panggil ?? $primary?->waktu_pesan;

        return [
            'id' => $meja->id,
            'kode_meja' => $meja->kode_meja,
            'number' => (int) preg_replace('/\D+/', '', $meja->kode_meja),
            'area' => $meja->area,
            'kapasitas' => (int) $meja->kapasitas,
            'is_calling' => (bool) $panggilanMeja,
            'status' => $status,
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'status_dot' => $statusDot,
            'card_class' => $cardClass,
            'border_class' => $borderClass,
            'bill_info' => $billInfo,
            'time_ago' => $this->waktuRelatif($waktu),
            'waktu_iso' => $waktu?->toIso8601String(),
            'customer' => $customer,
            'order_count' => $orders->count(),
            'orders' => $orders->all(),
            'primary_order' => $primaryPayload,
            'items' => $items->all(),
            'item_count' => $items->count(),
            'total_qty' => $totalQty,
            'items_summary' => $itemsSummary,
            'sisa_item' => $sisaItem,
            'total' => $total,
            'total_format' => 'Rp '.number_format($total, 0, ',', '.'),
            'progress' => $progress,
            'progress_label' => $progress.'% Selesai',
            'source' => $primaryPayload['sumber'] ?? null,
            'source_label' => $primaryPayload['sumber_label'] ?? 'Belum ada pesanan',
            'call' => $panggilanMeja ? $this->payloadPanggilan($panggilanMeja) : null,
            'session_code' => $sesi?->kode_unik,
            'session_active' => (bool) $sesi,
            'qr_url' => route('emenu.meja', $meja->kode_meja),
            'device' => filled($meja->id_device) ? $meja->id_device : 'QR Meja',
            'has_device' => filled($meja->id_device),
        ];
    }

    private function snapshotMeja(): array
    {
        $meja = $this->getMejaByArea();
        $panggilan = $this->panggilanMenunggu();
        $pesanan = $this->pesananAktif();
        $sesi = $this->sesiAktif($meja);

        $tables = $meja
            ->map(fn ($item) => $this->payloadMeja($item, $panggilan, $pesanan, $sesi->get($item->id)))
            ->values();

        $clusters = $meja
            ->groupBy('area')
            ->map(function ($group, $area) use ($tables) {
                return [
                    'key' => Str::slug($area),
                    'name' => $area,
                    'count' => $group->count(),
                    'tables' => $tables->filter(fn ($item) => $item['area'] === $area)->values()->all(),
                ];
            })
            ->values();

        $terisi = $tables->filter(fn ($item) => $item['status'] !== 'tersedia')->count();

        return [
            'meja' => $meja,
            'panggilan' => $panggilan,
            'pesanan' => $pesanan,
            'tables' => $tables,
            'clusters' => $clusters,
            'total_meja' => $meja->count(),
            'terisi' => $terisi,
            'tersedia' => $meja->count() - $terisi,
            'panggilan_count' => $panggilan->count(),
            'device' => $this->infoPerangkat($meja),
        ];
    }

    private function statistikHariIni(): array
    {
        $hariIni = today();
        $panggilanHariIni = $this->filterByArea(LogPanggilPelayan::query())
            ->whereDate('waktu_panggil', $hariIni);
        $totalPanggilan = (clone $panggilanHariIni)->count();
        $panggilanSelesai = $this->filterByArea(
            LogPanggilPelayan::query()->where('status_panggilan', 'selesai')
        )->whereDate('waktu_selesai', $hariIni)->get();
        $orderHariIni = $this->filterByArea(Pesanan::query())->whereDate('waktu_pesan', $hariIni);
        $ordersDiantar = $this->filterByArea(
            Pesanan::query()->whereIn('status_pesanan', [Pesanan::STATUS_DIANTAR, Pesanan::STATUS_SELESAI])
        )->whereDate('updated_at', $hariIni)->count();
        $ordersSelesai = $this->filterByArea(
            Pesanan::query()->where('status_pesanan', Pesanan::STATUS_SELESAI)
        )->whereDate('updated_at', $hariIni)->count();
        $avgDetik = $panggilanSelesai->count() > 0
            ? (int) round($panggilanSelesai->avg(fn ($log) => $log->waktu_panggil->diffInSeconds($log->waktu_selesai)))
            : 0;

        return [
            'total_panggilan' => $totalPanggilan,
            'calls_answered' => $panggilanSelesai->count(),
            'orders_delivered' => $ordersDiantar,
            'orders_finished' => $ordersSelesai,
            'total_orders' => (clone $orderHariIni)->count(),
            'avg_response_detik' => $avgDetik,
            'avg_response_menit' => $avgDetik > 0 ? round($avgDetik / 60, 1) : 0,
            'persen_panggilan' => $totalPanggilan > 0
                ? (int) round($panggilanSelesai->count() / $totalPanggilan * 100)
                : 0,
        ];
    }

    private function menuPelayan(): Collection
    {
        return Kategori::query()
            ->untukMenu()
            ->whereIn('target_kds', ['dapur', 'bar'])
            ->whereHas('menu', fn ($query) => $query->where('is_aktif', true)->where('stok_menu', '>', 0))
            ->with(['menu' => fn ($query) => $query
                ->where('is_aktif', true)
                ->where('stok_menu', '>', 0)
                ->orderBy('nama_menu')])
            ->orderBy('nama_kategori')
            ->get()
            ->map(fn ($kategori) => [
                'id' => $kategori->id,
                'nama' => $kategori->nama_kategori,
                'target_kds' => $kategori->target_kds,
                'menus' => $kategori->menu->map(fn ($menu) => [
                    'id' => $menu->id,
                    'nama' => $menu->nama_menu,
                    'harga' => (float) $menu->harga,
                    'harga_format' => 'Rp '.number_format((float) $menu->harga, 0, ',', '.'),
                    'stok' => (int) $menu->stok_menu,
                    'target_kds' => $kategori->target_kds,
                    'kategori' => $kategori->nama_kategori,
                ])->values()->all(),
            ])
            ->filter(fn ($kategori) => count($kategori['menus']) > 0)
            ->values();
    }

    private function logAktivitasHariIni(int $limit = 10): Collection
    {
        $panggilan = $this->filterByArea(
            LogPanggilPelayan::with(['meja', 'pelayan'])->whereDate('waktu_panggil', today())
        )->get()->map(function ($log) {
            return [
                'jenis' => 'Panggilan',
                'judul' => 'Panggilan '.($log->meja?->kode_meja ?? '-'),
                'keterangan' => $log->status_panggilan === 'selesai'
                    ? 'Ditangani '.($log->pelayan?->nama ?? 'pelayan')
                    : 'Status '.$log->status_panggilan,
                'status' => $log->status_panggilan,
                'waktu' => ($log->waktu_selesai ?? $log->waktu_panggil)?->timezone('Asia/Jakarta')->format('H:i').' WIB',
                'timestamp' => ($log->waktu_selesai ?? $log->waktu_panggil)?->getTimestamp() ?? 0,
            ];
        });

        $pesanan = $this->filterByArea(
            Pesanan::with('meja')->whereDate('waktu_pesan', today())
        )->get()->map(function ($order) {
            return [
                'jenis' => 'Pesanan',
                'judul' => 'Pesanan '.$order->kode_pesanan,
                'keterangan' => 'Saung '.($order->meja?->kode_meja ?? '-').' • '.$order->status_pesanan,
                'status' => $order->status_pesanan,
                'waktu' => $order->updated_at?->timezone('Asia/Jakarta')->format('H:i').' WIB',
                'timestamp' => $order->updated_at?->getTimestamp() ?? 0,
            ];
        });

        return $panggilan
            ->concat($pesanan)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();
    }

    public function notifikasiPanggilan(): View
    {
        $snapshot = $this->snapshotMeja();
        $statistik = $this->statistikHariIni();
        $siapAntar = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->where('status_pesanan', Pesanan::STATUS_SIAP)
            ->orderBy('waktu_pesan')
            ->get();
        $siapAntarFormatted = $siapAntar->map(fn ($pesanan) => $this->payloadPesanan($pesanan))->values();
        $urgentCall = $snapshot['panggilan']->first()
            ? $this->payloadPanggilan($snapshot['panggilan']->first())
            : null;
        $saungStatus = $snapshot['tables']->map(fn ($table) => [
            'kode_meja' => $table['kode_meja'],
            'status' => $table['status_label'],
        ])->values();
        $shift = $this->infoShift();

        return view('pelayan.notifikasi-panggilan-pelayan.index', array_merge(
            $this->dataNavigasi(),
            compact(
                'statistik',
                'siapAntar',
                'siapAntarFormatted',
                'urgentCall',
                'saungStatus',
                'shift'
            ),
            [
                'panggilan' => $snapshot['panggilan'],
                'currentArea' => $this->labelArea(),
                'device' => $snapshot['device'],
            ]
        ));
    }

    public function tabAntar(): View
    {
        $siapAntar = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->where('status_pesanan', Pesanan::STATUS_SIAP)
            ->orderBy('waktu_pesan')
            ->get();
        $pesananDiantar = $this->filterByArea(
            Pesanan::with(['meja', 'detailPesanan.menu.kategori'])
        )->where('status_pesanan', Pesanan::STATUS_DIANTAR)
            ->orderBy('waktu_pesan')
            ->get();
        $siapAntarFormatted = $siapAntar->map(fn ($pesanan) => $this->payloadPesanan($pesanan))->values();
        $deliveringOrders = $pesananDiantar->map(fn ($pesanan) => $this->payloadPesanan($pesanan))->values();
        $statistik = $this->statistikHariIni();
        $meja = $this->getMejaByArea();

        return view('pelayan.tab-antar.index', array_merge(
            $this->dataNavigasi(),
            compact(
                'siapAntar',
                'pesananDiantar',
                'siapAntarFormatted',
                'deliveringOrders',
                'statistik'
            ),
            [
                'readyCount' => $siapAntar->count(),
                'kitchenCount' => $siapAntarFormatted->filter(fn ($order) => $order['has_dapur'])->count(),
                'barCount' => $siapAntarFormatted->filter(fn ($order) => $order['has_bar'])->count(),
                'deliveringCount' => $pesananDiantar->count(),
                'finishedCount' => $statistik['orders_finished'],
                'currentArea' => $this->labelArea(),
                'device' => $this->infoPerangkat($meja),
                'shift' => $this->infoShift(),
            ]
        ));
    }

    public function tabSaung(): View
    {
        $snapshot = $this->snapshotMeja();
        $menu = $this->menuPelayan();
        $shift = $this->infoShift();

        return view('pelayan.tab-saung.index', array_merge(
            $this->dataNavigasi(),
            compact('menu', 'shift'),
            [
                'meja' => $snapshot['meja'],
                'clusters' => $snapshot['clusters'],
                'tables' => $snapshot['tables'],
                'totalMeja' => $snapshot['total_meja'],
                'terisiCount' => $snapshot['terisi'],
                'tersediaCount' => $snapshot['tersedia'],
                'panggilanCount' => $snapshot['panggilan_count'],
                'currentArea' => $this->labelArea(),
                'device' => $snapshot['device'],
            ]
        ));
    }

    public function tabShift(): View
    {
        $statistik = $this->statistikHariIni();
        $snapshot = $this->snapshotMeja();
        $menu = $this->menuPelayan();
        $logAktivitas = $this->logAktivitasHariIni();
        $shift = $this->infoShift();

        return view('pelayan.tab-shift.index', array_merge(
            $this->dataNavigasi(),
            compact('statistik', 'logAktivitas', 'shift', 'menu'),
            [
                'totalMeja' => $snapshot['total_meja'],
                'terisiCount' => $snapshot['terisi'],
                'device' => $snapshot['device'],
                'currentArea' => $this->labelArea(),
            ]
        ));
    }

    public function simpanPesanan(Request $request, PesananService $service): JsonResponse
    {
        $data = $request->validate([
            'meja_id' => ['required', 'exists:meja_makan,id'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['nullable', 'integer', 'min:0', 'max:99'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'catatan_menu' => ['nullable', 'array'],
            'catatan_menu.*' => ['nullable', 'string', 'max:255'],
        ]);

        $meja = MejaMakan::findOrFail($data['meja_id']);
        $this->pastikanAreaMeja($meja);

        $dipilih = collect($data['jumlah'])->filter(fn ($jumlah) => (int) $jumlah > 0);

        if ($dipilih->isEmpty()) {
            return response()->json(['message' => 'Pilih minimal satu menu.'], 422);
        }

        $catatanMenu = collect($data['catatan_menu'] ?? [])
            ->mapWithKeys(fn ($catatan, $menuId) => [(int) $menuId => $catatan])
            ->filter(fn ($catatan) => filled($catatan))
            ->all();
        $sesi = SesiMeja::query()
            ->where('meja_id', $meja->id)
            ->where('status', 'aktif')
            ->latest('id')
            ->first();

        try {
            $pesanan = $service->buat(
                $meja,
                $dipilih->all(),
                $catatanMenu,
                [
                    'asal' => 'kasir',
                    'metode' => 'tunai',
                    'kasir_id' => auth()->id(),
                    'catatan' => $data['catatan'] ?? null,
                    'nama_pelanggan' => $sesi?->nama_pelanggan,
                ],
            );
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        Realtime::emit(new PesananBaru([
            'kode_pesanan' => $pesanan->kode_pesanan,
            'meja' => $meja->kode_meja,
            'meja_id' => $meja->id,
            'status_pesanan' => $pesanan->status_pesanan,
            'status_pembayaran' => $pesanan->status_pembayaran,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'total_bayar' => (float) $pesanan->total_bayar,
        ]));

        return response()->json([
            'status' => 'ok',
            'message' => 'Pesanan tambahan berhasil dikirim ke dapur dan bar.',
            'pesanan' => [
                'kode_pesanan' => $pesanan->kode_pesanan,
                'meja' => $meja->kode_meja,
                'total' => (float) $pesanan->total_bayar,
            ],
        ], 201)->header('Cache-Control', 'no-store, max-age=0');
    }

    public function bukaMeja(string $meja): JsonResponse
    {
        $mejaMakan = MejaMakan::where('kode_meja', $meja)->first()
            ?? MejaMakan::findOrFail($meja);
        $this->pastikanAreaMeja($mejaMakan);

        $sesi = SesiMeja::aktifUntuk($mejaMakan);

        Realtime::emit(new SesiMejaBerubah($mejaMakan->kode_meja, $mejaMakan->id, 'dibuka'));

        return response()->json([
            'status' => 'ok',
            'meja' => $mejaMakan->kode_meja,
            'area' => $mejaMakan->area,
            'kode_unik' => $sesi->kode_unik,
            'qr_url' => route('emenu.meja', $mejaMakan->kode_meja),
        ])->header('Cache-Control', 'no-store, max-age=0');
    }

    public function ubahStatus(Request $request, $id): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status_panggilan' => ['required', 'in:menunggu,ditangani,selesai'],
        ]);

        $log = LogPanggilPelayan::with('meja')->findOrFail($id);
        $this->pastikanAreaMeja($log->meja);

        $isi = ['status_panggilan' => $data['status_panggilan']];

        if ($request->user()) {
            $isi['pelayan_id'] = $request->user()->id;
        }

        $isi['waktu_selesai'] = $data['status_panggilan'] === 'selesai' ? now() : null;

        $log->update($isi);

        Realtime::emit(new PanggilanPelayan([
            'id' => $log->id,
            'meja_id' => $log->meja_id,
            'meja' => $log->meja?->kode_meja,
            'area' => $log->meja?->area,
            'status_panggilan' => $log->status_panggilan,
        ]));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Status panggilan diperbarui',
                'panggilan' => [
                    'id' => $log->id,
                    'status_panggilan' => $data['status_panggilan'],
                ],
            ]);
        }

        return back();
    }
}
