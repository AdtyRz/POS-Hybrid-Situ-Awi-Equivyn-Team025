<?php

namespace Database\Seeders;

use App\Models\MejaMakan;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RiwayatPesananSeeder extends Seeder
{
    private const ZONA = 'Asia/Jakarta';

    private const HARI_KE_BELAKANG = 365;

    /** @var object */
    private $faker;

    /** @var array<int, array{id:int, nama:string, harga:float, berat:float}> */
    private array $menuMakanan = [];

    /** @var array<int, array{id:int, nama:string, harga:float, berat:float}> */
    private array $menuMinuman = [];

    /** @var array<int, array{id:int, nama:string, harga:float, berat:float}> */
    private array $menuCamilan = [];

    /** @var array<int, array{id:int, kode:string, kapasitas:int, berat:float}> */
    private array $meja = [];

    /** @var array<int, array{id:int, role:string}> */
    private array $kasir = [];

    /** @var array<string, true> */
    private array $kodeDipakai = [];

    /** @var array<int, string> */
    private array $namaPelanggan = [];

    public function run(): void
    {
        $this->faker = fake('id_ID');
        $this->muatData();

        if ($this->menuMakanan === [] && $this->menuMinuman === [] && $this->menuCamilan === []) {
            $this->command?->warn('Riwayat pesanan dilewati: belum ada menu aktif.');

            return;
        }

        if ($this->meja === []) {
            $this->command?->warn('Riwayat pesanan dilewati: belum ada meja.');

            return;
        }

        $sekarang = Carbon::now(self::ZONA);
        $mulai = $sekarang->copy()->subDays(self::HARI_KE_BELAKANG)->startOfDay();
        $selesai = $sekarang->copy()->subDay()->endOfDay();

        if ($mulai->greaterThan($selesai)) {
            $this->command?->warn('Riwayat pesanan dilewati: rentang tanggal tidak valid.');

            return;
        }

        $jumlahHarian = $this->jumlahPesananPerTanggal($mulai, $selesai);
        $mulaiTopUp = $selesai->copy()->subDays(6)->startOfDay();

        $totalPesanan = 0;
        $totalDetail = 0;
        $totalPembayaran = 0;
        $hariDilewati = 0;
        $hari = $mulai->copy();

        while ($hari->lessThanOrEqualTo($selesai)) {
            $tanggal = $hari->toDateString();
            $jumlahAda = $jumlahHarian[$tanggal] ?? 0;

            if ($hari->greaterThanOrEqualTo($mulaiTopUp)) {
                $kekurangan = $this->targetHarian($hari) - $jumlahAda;

                if ($kekurangan <= 0) {
                    $hariDilewati++;
                    $hari->addDay();

                    continue;
                }

                $hasil = DB::transaction(fn () => $this->buatHari($hari, $kekurangan));
            } else {
                if ($jumlahAda > 0) {
                    $hariDilewati++;
                    $hari->addDay();

                    continue;
                }

                $hasil = DB::transaction(fn () => $this->buatHari($hari));
            }

            $totalPesanan += $hasil['pesanan'];
            $totalDetail += $hasil['detail'];
            $totalPembayaran += $hasil['pembayaran'];

            if ((int) $hari->diffInDays($mulai) % 30 === 0) {
                $this->command?->info(sprintf(
                    'Riwayat pesanan %s: %s pesanan...',
                    $hari->format('d M Y'),
                    number_format($totalPesanan, 0, ',', '.')
                ));
            }

            $hari->addDay();
        }

        $this->command?->info(sprintf(
            'Riwayat pesanan selesai: %s pesanan, %s detail, %s pembayaran (%s s/d %s).',
            number_format($totalPesanan, 0, ',', '.'),
            number_format($totalDetail, 0, ',', '.'),
            number_format($totalPembayaran, 0, ',', '.'),
            $mulai->format('d M Y'),
            $selesai->format('d M Y')
        ));

        if ($hariDilewati > 0) {
            $this->command?->info(number_format($hariDilewati, 0, ',', '.').' tanggal dilewati karena sudah memiliki pesanan.');
        }
    }

    private function muatData(): void
    {
        $menus = Menu::query()
            ->with('kategori:id,target_kds')
            ->where('is_aktif', true)
            ->get();

        foreach ($menus as $menu) {
            $nama = mb_strtolower($menu->nama_menu);
            $target = $menu->kategori?->target_kds;
            $item = [
                'id' => (int) $menu->id,
                'nama' => $menu->nama_menu,
                'harga' => (float) $menu->harga,
                'berat' => $this->bobotMenu($nama),
            ];

            if ($target === 'bar') {
                $this->menuMinuman[] = $item;
            } elseif (Str::contains($nama, ['pisang', 'roti', 'tahu', 'spaghetty', 'cheese'])) {
                $this->menuCamilan[] = $item;
            } else {
                $this->menuMakanan[] = $item;
            }
        }

        if ($this->menuMinuman === []) {
            $this->menuMinuman = $this->menuMakanan;
        }

        if ($this->menuMakanan === []) {
            $this->menuMakanan = $this->menuCamilan;
        }

        if ($this->menuCamilan === []) {
            $this->menuCamilan = $this->menuMakanan;
        }

        $this->meja = MejaMakan::query()
            ->orderBy('id')
            ->get(['id', 'kode_meja', 'kapasitas'])
            ->map(fn (MejaMakan $meja) => [
                'id' => (int) $meja->id,
                'kode' => $meja->kode_meja,
                'kapasitas' => (int) $meja->kapasitas,
                'berat' => 1 + max(0, (int) $meja->kapasitas - 4) * 0.15,
            ])
            ->values()
            ->all();

        $this->kasir = User::query()
            ->whereIn('role', ['kasir', 'admin'])
            ->where('is_aktif', true)
            ->orderBy('id')
            ->get(['id', 'role'])
            ->map(fn (User $user) => ['id' => (int) $user->id, 'role' => $user->role])
            ->values()
            ->all();

        $this->kodeDipakai = array_fill_keys(
            Pesanan::query()->pluck('kode_pesanan')->all(),
            true
        );

        for ($i = 0; $i < 28; $i++) {
            $this->namaPelanggan[] = $this->faker->name();
        }
    }

    /**
     * @return array{pesanan:int, detail:int, pembayaran:int}
     */
    private function buatHari(Carbon $hari, ?int $jumlah = null): array
    {
        $jumlah ??= $this->jumlahPesananHariItu($hari);

        if ($jumlah === 0) {
            return ['pesanan' => 0, 'detail' => 0, 'pembayaran' => 0];
        }

        $daftarWaktu = [];

        for ($i = 0; $i < $jumlah; $i++) {
            $daftarWaktu[] = $this->waktuPesanan($hari);
        }

        usort($daftarWaktu, fn (Carbon $a, Carbon $b) => $a->getTimestamp() <=> $b->getTimestamp());

        $terakhirPerMeja = [];
        $hasil = ['pesanan' => 0, 'detail' => 0, 'pembayaran' => 0];

        foreach ($daftarWaktu as $waktu) {
            $meja = $this->pilihMeja($waktu, $terakhirPerMeja);
            $terakhirPerMeja[$meja['id']] = $waktu->copy();

            $baris = $this->buatPesanan($waktu, $meja);

            $hasil['pesanan']++;
            $hasil['detail'] += $baris['detail'];
            $hasil['pembayaran'] += $baris['pembayaran'];
        }

        return $hasil;
    }

    private function jumlahPesananHariItu(Carbon $hari): int
    {
        // Tutup sesekali, plus variasi ramai/sepi yang tidak konsisten.
        if ($this->acak() < 0.04) {
            return 0;
        }

        $dasarMingguan = match ((int) $hari->dayOfWeek) {
            0 => 21, // Minggu
            1 => 10, // Senin
            2 => 12, // Selasa
            3 => 13, // Rabu
            4 => 14, // Kamis
            5 => 18, // Jumat
            6 => 25, // Sabtu
        };

        $musim = match ((int) $hari->month) {
            2 => 0.85,
            6, 7, 12 => 1.2,
            default => 1.0,
        };

        $faktor = 0.65 + $this->acak() * 0.9;
        $undian = $this->acak();

        if ($undian < 0.06) {
            $faktor *= 1.8;
        } elseif ($undian < 0.16) {
            $faktor *= 1.35;
        } elseif ($undian < 0.30) {
            $faktor *= 0.65;
        }

        return max(0, min(45, (int) round($dasarMingguan * $musim * $faktor)));
    }

    private function waktuPesanan(Carbon $hari): Carbon
    {
        $sesi = $this->pilihBobot([
            ['bobot' => 35, 'nilai' => ['11:00', '14:00']],
            ['bobot' => 50, 'nilai' => ['17:30', '21:30']],
            ['bobot' => 10, 'nilai' => ['14:00', '17:00']],
            ['bobot' => 5, 'nilai' => ['08:00', '11:00']],
        ]);

        $awal = $hari->copy()->setTimeFromTimeString($sesi[0]);
        $akhir = $hari->copy()->setTimeFromTimeString($sesi[1]);

        // Distribusi segitiga: lebih ramai di tengah rentang jam.
        $posisi = ($this->acak() + $this->acak()) / 2;
        $menit = (int) round($awal->diffInMinutes($akhir) * $posisi);

        return $awal->addMinutes($menit)->addSeconds(random_int(0, 59));
    }

    /**
     * @param  array<int, Carbon>  $terakhirPerMeja
     * @return array{id:int, kode:string, kapasitas:int, berat:float}
     */
    private function pilihMeja(Carbon $waktu, array $terakhirPerMeja): array
    {
        $kandidat = $this->meja;

        for ($coba = 0; $coba < 12; $coba++) {
            $meja = $this->pilihBobot(array_map(
                fn (array $item) => ['bobot' => $item['berat'], 'nilai' => $item],
                $kandidat
            ));

            $terakhir = $terakhirPerMeja[$meja['id']] ?? null;

            if ($terakhir === null || $waktu->getTimestamp() - $terakhir->getTimestamp() >= 35 * 60) {
                return $meja;
            }
        }

        return $this->pilihBobot(array_map(
            fn (array $item) => ['bobot' => $item['berat'], 'nilai' => $item],
            $this->meja
        ));
    }

    /**
     * @return array{detail:int, pembayaran:int}
     */
    private function buatPesanan(Carbon $waktu, array $meja): array
    {
        $item = $this->pilihItem();

        if ($item === []) {
            return ['detail' => 0, 'pembayaran' => 0];
        }

        [$statusPesanan, $statusPembayaran, $metode] = $this->statusPesanan($waktu);

        $rincian = [];
        $total = 0;

        foreach ($item as $baris) {
            $harga = $this->hargaHistoris($baris['harga'], $waktu);
            $subtotal = $harga * $baris['jumlah'];
            $total += $subtotal;

            $rincian[] = [
                'menu_id' => $baris['id'],
                'jumlah' => $baris['jumlah'],
                'harga_satuan' => $harga,
                'subtotal' => $subtotal,
                'catatan' => $this->acak() < 0.06
                    ? $this->pilihNilai([
                        'Pedas sedang',
                        'Tanpa sambal',
                        'Es sedikit',
                        'Nasi tambah',
                        'Bungkus',
                        'Jangan pedas',
                    ])
                    : null,
            ];
        }

        if ($total <= 0) {
            return ['detail' => 0, 'pembayaran' => 0];
        }

        $kode = $this->kodePesanan($waktu, $meja['id']);
        $dibatalkan = $statusPesanan === Pesanan::STATUS_DIBATALKAN;
        $lunas = $statusPembayaran === Pesanan::PAYMENT_SUDAH_BAYAR;
        $waktuUtc = $waktu->copy()->utc();

        $waktuBayar = $lunas
            ? $waktu->copy()->addMinutes($metode === 'qris' ? random_int(1, 12) : random_int(5, 45))
            : null;

        if ($dibatalkan) {
            $waktuSelesai = $waktu->copy()->addMinutes(random_int(4, 40));
        } else {
            $waktuSelesai = $waktu->copy()->addMinutes(random_int(25, 115));

            if ($waktuBayar !== null && $waktuSelesai->lessThan($waktuBayar)) {
                $waktuSelesai = $waktuBayar->copy()->addMinutes(random_int(1, 5));
            }
        }

        $waktuSelesai = $waktuSelesai->utc();

        $kasirId = $this->pilihKasir($metode, $statusPesanan);

        $pesananId = DB::table('pesanan')->insertGetId([
            'kode_pesanan' => $kode,
            'kode_pembayaran' => $kode,
            'meja_id' => $meja['id'],
            'pelanggan_id' => null,
            'kasir_id' => $kasirId,
            'total_bayar' => $total,
            'status_pesanan' => $statusPesanan,
            'status_pembayaran' => $statusPembayaran,
            'metode_pembayaran' => $metode,
            'catatan' => $this->acak() < 0.07
                ? $this->pilihNilai([
                    'Acara keluarga',
                    'Pesanan ulang tahun',
                    'Tolong dipisah',
                    'Ada tamu tambahan',
                    'Pesanan dibungkus',
                ])
                : null,
            'waktu_pesan' => $waktuUtc->toDateTimeString(),
            'void_by' => $dibatalkan ? $kasirId : null,
            'void_at' => $dibatalkan ? $waktuSelesai->toDateTimeString() : null,
            'alasan_void' => $dibatalkan
                ? $this->pilihNilai([
                    'Tamu membatalkan pesanan.',
                    'Salah input pesanan.',
                    'Menu habis.',
                    'Tamu pindah meja.',
                    'Pesanan tidak jadi diproses.',
                ])
                : null,
            'printed_at' => $lunas && $this->acak() < 0.85
                ? $waktuBayar->copy()->addMinutes(random_int(0, 4))->utc()->toDateTimeString()
                : null,
            'created_at' => $waktuUtc->toDateTimeString(),
            'updated_at' => $waktuSelesai->toDateTimeString(),
            'nama_pelanggan' => $this->namaPelanggan(),
        ]);

        $detail = [];

        foreach ($rincian as $baris) {
            $detail[] = [
                'pesanan_id' => $pesananId,
                'menu_id' => $baris['menu_id'],
                'jumlah' => $baris['jumlah'],
                'harga_satuan' => $baris['harga_satuan'],
                'subtotal' => $baris['subtotal'],
                'catatan' => $baris['catatan'],
                'status_item' => $dibatalkan
                    ? $this->pilihNilai(['menunggu', 'diproses', 'siap'])
                    : 'diantar',
                'created_at' => $waktuUtc->toDateTimeString(),
                'updated_at' => $waktuSelesai->toDateTimeString(),
            ];
        }

        DB::table('detail_pesanan')->insert($detail);

        $jumlahPembayaran = 0;

        if ($lunas && $waktuBayar !== null) {
            $jumlahBayar = $total;
            $kembalian = 0;

            if ($metode === 'tunai') {
                $pembulatan = $this->pilihNilai([1000, 5000, 10000, 50000]);
                $jumlahBayar = (int) (ceil($total / $pembulatan) * $pembulatan);

                if ($this->acak() < 0.08) {
                    $jumlahBayar += 5000;
                }

                $kembalian = max(0, $jumlahBayar - $total);
            }

            DB::table('pembayaran')->insert([
                'pesanan_id' => $pesananId,
                'metode_pembayaran' => $metode,
                'status_pembayaran' => 'lunas',
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'transaction_id' => $metode === 'qris' ? 'qris-'.Str::uuid() : null,
                'order_id' => $metode === 'qris' ? $kode : null,
                'waktu_bayar' => $waktuBayar->copy()->utc()->toDateTimeString(),
                'created_at' => $waktuBayar->copy()->utc()->toDateTimeString(),
                'updated_at' => $waktuBayar->copy()->utc()->toDateTimeString(),
            ]);

            $jumlahPembayaran = 1;
        }

        return ['detail' => count($detail), 'pembayaran' => $jumlahPembayaran];
    }

    /**
     * @return array{0:string, 1:string, 2:string}
     */
    private function statusPesanan(Carbon $waktu): array
    {
        $undian = random_int(1, 1000);

        if ($undian <= 920) {
            return [
                Pesanan::STATUS_SELESAI,
                Pesanan::PAYMENT_SUDAH_BAYAR,
                $this->pilihMetode($waktu),
            ];
        }

        if ($undian <= 960) {
            $statusBayar = $this->pilihBobot([
                ['bobot' => 70, 'nilai' => Pesanan::PAYMENT_BELUM_BAYAR],
                ['bobot' => 20, 'nilai' => Pesanan::PAYMENT_PENDING_PEMBAYARAN],
                ['bobot' => 10, 'nilai' => Pesanan::PAYMENT_PENDING_VERIFIKASI],
            ]);

            return [
                Pesanan::STATUS_DIBATALKAN,
                $statusBayar,
                $statusBayar === Pesanan::PAYMENT_PENDING_VERIFIKASI ? 'qris' : $this->pilihMetode($waktu),
            ];
        }

        if ($undian <= 985) {
            return [
                Pesanan::STATUS_SELESAI,
                Pesanan::PAYMENT_BELUM_BAYAR,
                $this->pilihMetode($waktu),
            ];
        }

        return [
            Pesanan::STATUS_SELESAI,
            Pesanan::PAYMENT_PENDING_PEMBAYARAN,
            $this->pilihMetode($waktu),
        ];
    }

    private function pilihMetode(Carbon $waktu): string
    {
        // QRIS makin sering dipakai menjelang periode terbaru.
        $bulanLalu = max(0, min(12, (int) $waktu->diffInMonths(Carbon::now(self::ZONA))));
        $peluangQris = 0.20 + ((12 - $bulanLalu) / 12) * 0.22;

        return $this->acak() < $peluangQris ? 'qris' : 'tunai';
    }

    private function pilihKasir(string $metode, string $statusPesanan): ?int
    {
        if ($this->kasir === []) {
            return null;
        }

        if ($metode === 'qris' && $statusPesanan !== Pesanan::STATUS_DIBATALKAN && $this->acak() < 0.85) {
            return null;
        }

        $kandidat = array_values(array_filter(
            $this->kasir,
            fn (array $user) => $user['role'] === 'kasir'
        ));

        if ($kandidat === [] || $this->acak() < 0.12) {
            $kandidat = $this->kasir;
        }

        return $this->pilihNilai(array_column($kandidat, 'id'));
    }

    private function namaPelanggan(): ?string
    {
        if ($this->acak() < 0.18) {
            return null;
        }

        if ($this->namaPelanggan !== [] && $this->acak() < 0.22) {
            return $this->pilihNilai($this->namaPelanggan);
        }

        return $this->faker->name();
    }

    /**
     * @return array<int, array{id:int, nama:string, harga:float, jumlah:int}>
     */
    private function pilihItem(): array
    {
        $mode = $this->pilihBobot([
            ['bobot' => 58, 'nilai' => 'makan'],
            ['bobot' => 24, 'nilai' => 'campur'],
            ['bobot' => 11, 'nilai' => 'minum'],
            ['bobot' => 7, 'nilai' => 'camilan'],
        ]);

        $jumlahJenis = $this->pilihBobot([
            ['bobot' => 14, 'nilai' => 1],
            ['bobot' => 30, 'nilai' => 2],
            ['bobot' => 30, 'nilai' => 3],
            ['bobot' => 16, 'nilai' => 4],
            ['bobot' => 7, 'nilai' => 5],
            ['bobot' => 3, 'nilai' => 6],
        ]);

        $pilihan = [];

        if ($mode === 'makan') {
            $pilihan = $this->ambilAcak($this->menuMakanan, $jumlahJenis);

            if ($this->acak() < 0.12) {
                $pilihan = array_merge($pilihan, $this->ambilAcak($this->menuMinuman, 1));
            }
        } elseif ($mode === 'campur') {
            $pilihan = array_merge(
                $this->ambilAcak($this->menuMakanan, max(1, $jumlahJenis - 1)),
                $this->ambilAcak($this->menuMinuman, 1)
            );

            if ($this->acak() < 0.25) {
                $pilihan = array_merge($pilihan, $this->ambilAcak($this->menuCamilan, 1));
            }
        } elseif ($mode === 'minum') {
            $pilihan = $this->ambilAcak($this->menuMinuman, $jumlahJenis);

            if ($this->acak() < 0.18) {
                $pilihan = array_merge($pilihan, $this->ambilAcak($this->menuCamilan, 1));
            }
        } else {
            $pilihan = $this->ambilAcak($this->menuCamilan, $jumlahJenis);

            if ($this->acak() < 0.30) {
                $pilihan = array_merge($pilihan, $this->ambilAcak($this->menuMinuman, 1));
            }
        }

        $hasil = [];

        foreach ($pilihan as $menu) {
            $hasil[] = [
                'id' => $menu['id'],
                'nama' => $menu['nama'],
                'harga' => $menu['harga'],
                'jumlah' => $this->jumlahItem($menu['nama']),
            ];
        }

        return $hasil;
    }

    /**
     * @param  array<int, array{id:int, nama:string, harga:float, berat:float}>  $menu
     * @return array<int, array{id:int, nama:string, harga:float, berat:float}>
     */
    private function ambilAcak(array $menu, int $jumlah): array
    {
        $sisa = $menu;
        $hasil = [];

        for ($i = 0; $i < $jumlah && $sisa !== []; $i++) {
            $pilihan = $this->pilihBobot(array_map(
                fn (array $item) => ['bobot' => $item['berat'], 'nilai' => $item],
                $sisa
            ));

            $hasil[] = $pilihan;
            $sisa = array_values(array_filter(
                $sisa,
                fn (array $item) => $item['id'] !== $pilihan['id']
            ));
        }

        return $hasil;
    }

    private function jumlahItem(string $nama): int
    {
        $nama = mb_strtolower($nama);

        if (Str::contains($nama, 'paket')) {
            return 1;
        }

        $undian = $this->acak();

        if (Str::contains($nama, ['coffee', 'ice coffee']) && $undian < 0.22) {
            return 2;
        }

        if (Str::contains($nama, ['nasi', 'ayam', 'pepes', 'baso']) && $undian < 0.12) {
            return 2;
        }

        if ($undian < 0.06) {
            return 3;
        }

        if ($undian < 0.18) {
            return 2;
        }

        return 1;
    }

    private function hargaHistoris(float $harga, Carbon $waktu): int
    {
        $bulanLalu = max(0, min(12, (int) $waktu->diffInMonths(Carbon::now(self::ZONA))));
        $faktor = 1 - ($bulanLalu / 12) * 0.065;
        $faktor *= 0.97 + $this->acak() * 0.06;

        return max(500, (int) (round(($harga * $faktor) / 500) * 500));
    }

    private function kodePesanan(Carbon $waktu, int $mejaId): string
    {
        $dasar = 'SW-'.$waktu->copy()->utc()->format('YmdHis').'-'.$mejaId;
        $kode = $dasar;
        $urut = 1;

        while (isset($this->kodeDipakai[$kode])) {
            $kode = $dasar.'-'.$urut;

            if (++$urut > 20) {
                $kode = $dasar.'-'.Str::upper(Str::random(3));
                break;
            }
        }

        $this->kodeDipakai[$kode] = true;

        return $kode;
    }

    private function bobotMenu(string $nama): float
    {
        if (Str::contains($nama, 'paket')) {
            return 1.0;
        }

        if (Str::contains($nama, 'nasi')) {
            return 5.0;
        }

        if (Str::contains($nama, ['coffee', 'ice coffee'])) {
            return 4.5;
        }

        if (Str::contains($nama, ['ayam', 'pepes', 'baso', 'gepuk', 'jengkol'])) {
            return 3.5;
        }

        if (Str::contains($nama, ['pisang', 'roti', 'tahu', 'spaghetty', 'cheese'])) {
            return 3.0;
        }

        if (Str::contains($nama, ['sayur', 'kangkung', 'nasi putih'])) {
            return 1.8;
        }

        return 2.5;
    }

    /**
     * @return array<string, int>
     */
    private function jumlahPesananPerTanggal(Carbon $mulai, Carbon $selesai): array
    {
        return Pesanan::query()
            ->whereBetween('waktu_pesan', [$mulai->copy()->utc(), $selesai->copy()->utc()])
            ->pluck('waktu_pesan')
            ->map(fn ($waktu) => Carbon::parse($waktu)->timezone(self::ZONA)->toDateString())
            ->countBy()
            ->all();
    }

    private function targetHarian(Carbon $hari): int
    {
        // Pola ramai-sepi bergantian untuk 7 hari terakhir supaya grafik
        // mingguan naik-turun, bukan membentuk satu bukit datar.
        // Indeks 0 = kemarin (H-1), indeks 6 = H-7. Hari ini tidak diisi
        // karena transaksi hari berjalan masih bertambah.
        $selesai = Carbon::now(self::ZONA)->subDay()->startOfDay();
        $mundur = (int) round($hari->copy()->startOfDay()->diffInDays($selesai));
        $pola = [10, 33, 36, 17, 38, 11, 16];

        return max(0, $pola[$mundur] ?? 0);
    }

    /**
     * @template T
     *
     * @param  array<int, array{bobot:float|int, nilai:T}>  $opsi
     * @return T
     */
    private function pilihBobot(array $opsi)
    {
        $total = array_sum(array_column($opsi, 'bobot'));
        $undian = $this->acak() * $total;

        foreach ($opsi as $item) {
            $undian -= $item['bobot'];

            if ($undian <= 0) {
                return $item['nilai'];
            }
        }

        return $opsi[array_key_last($opsi)]['nilai'];
    }

    /**
     * @template T
     *
     * @param  array<int, T>  $nilai
     * @return T
     */
    private function pilihNilai(array $nilai)
    {
        return $nilai[array_rand($nilai)];
    }

    private function acak(): float
    {
        return mt_rand() / mt_getrandmax();
    }
}
