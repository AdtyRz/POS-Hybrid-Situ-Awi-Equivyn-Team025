<?php

namespace App\Http\Controllers;

use App\Models\LogPanggilPelayan;
use App\Models\MejaMakan;
use App\Models\Pengaturan;
use App\Models\SesiMeja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $profil = collect(Pengaturan::DEFAULTS)
            ->mapWithKeys(fn ($default, $kunci) => [$kunci => Pengaturan::nilai($kunci)])
            ->all();

        $daftarMeja = MejaMakan::query()
            ->orderBy('kode_meja')
            ->get(['id', 'kode_meja', 'area', 'kapasitas', 'id_device', 'status_meja']);

        $panggilanAktif = LogPanggilPelayan::query()
            ->where('status_panggilan', 'menunggu')
            ->count();

        $logTerbaru = LogPanggilPelayan::query()
            ->with('meja')
            ->latest('waktu_panggil')
            ->limit(6)
            ->get();

        $mejaDipanggil = LogPanggilPelayan::query()
            ->where('status_panggilan', 'menunggu')
            ->pluck('meja_id')
            ->all();
        $daftarMeja->each(fn (MejaMakan $meja) => $meja->panggilan_aktif = in_array($meja->id, $mejaDipanggil, true));

        $sesiAktif = SesiMeja::query()->where('status', 'aktif')->count();

        $midtrans = [
            'mode' => config('midtrans.is_production') ? 'Production' : 'Sandbox',
            'merchant_id' => config('midtrans.merchant_id') ?: 'Belum diatur',
            'client_key' => $this->samarkan(config('midtrans.client_key')),
            'server_key_terpasang' => filled(config('midtrans.server_key')),
            'notification_url' => route('midtrans.notification'),
            'siap' => filled(config('midtrans.merchant_id'))
                && filled(config('midtrans.client_key'))
                && filled(config('midtrans.server_key')),
        ];

        return view('admin.pengaturan-sistem.index', [
            'profil' => $profil,
            'logoSrc' => Pengaturan::logoUrl(),
            'midtrans' => $midtrans,
            'diagnostik' => $this->diagnostikData($daftarMeja, $sesiAktif),
            'daftarMeja' => $daftarMeja,
            'panggilanAktif' => $panggilanAktif,
            'logTerbaru' => $logTerbaru,
            'sesiAktif' => $sesiAktif,
            'perangkatTerpasang' => $daftarMeja->whereNotNull('id_device')->count(),
            'mejaTerdaftar' => $daftarMeja->count(),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        foreach (['auto_cutter', 'print_logo', 'print_qr', 'session_auto_close', 'service_charge_enabled', 'backup_cloud', 'midtrans_auto_cancel'] as $kunci) {
            $request->merge([$kunci => $request->boolean($kunci)]);
        }

        $data = $request->validate([
            'nama_resto' => ['required', 'string', 'max:100'],
            'alamat_resto' => ['nullable', 'string', 'max:255'],
            'telepon_resto' => ['nullable', 'string', 'max:30'],
            'email_resto' => ['nullable', 'email', 'max:120'],
            'struk_footer' => ['nullable', 'string', 'max:120'],
            'qris_expiry_minutes' => ['required', 'integer', 'min:5', 'max:60'],
            'buzzer_duration' => ['nullable', 'integer', 'min:1', 'max:10'],
            'anti_spam_cooldown' => ['nullable', 'integer', 'min:1', 'max:60'],
            'lcd_message' => ['nullable', 'string', 'max:16'],
            'encryption_level' => ['nullable', 'string', 'max:80'],
            'session_auto_close' => ['nullable', 'boolean'],
            'session_expiry_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'printer_ip' => ['nullable', 'string', 'max:45'],
            'printer_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'paper_size' => ['nullable', 'in:58mm,80mm'],
            'auto_cutter' => ['nullable', 'boolean'],
            'print_logo' => ['nullable', 'boolean'],
            'print_qr' => ['nullable', 'boolean'],
            'receipt_header' => ['nullable', 'string', 'max:120'],
            'service_charge_enabled' => ['nullable', 'boolean'],
            'service_charge_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rounding_rule' => ['nullable', 'in:none,100,500,1000'],
            'backup_frequency' => ['nullable', 'in:harian,mingguan,bulanan'],
            'backup_retention' => ['nullable', 'integer', 'min:1', 'max:90'],
            'backup_cloud' => ['nullable', 'boolean'],
            'midtrans_auto_cancel' => ['nullable', 'boolean'],
        ]);

        Pengaturan::simpanBanyak($data);

        return redirect()
            ->route('admin.pengaturan-sistem')
            ->with('sukses', 'Pengaturan sistem berhasil disimpan.');
    }

    public function unggahLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => [
                'required',
                'file',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'mimes:jpg,jpeg,png,webp',
                'extensions:jpg,jpeg,png,webp',
                'max:3072',
            ],
        ], [
            'logo.required' => 'Pilih berkas logo terlebih dahulu.',
            'logo.image' => 'Logo harus berupa gambar JPG, PNG, atau WEBP.',
            'logo.mimes' => 'Format logo harus JPG, JPEG, PNG, atau WEBP.',
            'logo.extensions' => 'Ekstensi logo harus JPG, JPEG, PNG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 3 MB.',
        ]);

        $berkas = $request->file('logo');
        $nama = 'logo-' . now('Asia/Jakarta')->format('YmdHis') . '-' . Str::lower(Str::random(6)) . '.' . strtolower($berkas->getClientOriginalExtension());
        $direktori = public_path('asset/resto');

        if (! is_dir($direktori)) {
            mkdir($direktori, 0755, true);
        }

        $berkas->move($direktori, $nama);
        $lama = Pengaturan::nilai('logo_path', '');

        Pengaturan::simpanBanyak(['logo_path' => 'asset/resto/' . $nama]);

        if (is_string($lama) && str_starts_with($lama, 'asset/resto/')) {
            $berkasLama = public_path($lama);

            if (is_file($berkasLama) && $berkasLama !== $direktori . DIRECTORY_SEPARATOR . $nama) {
                @unlink($berkasLama);
            }
        }

        return redirect()
            ->route('admin.pengaturan-sistem')
            ->with('sukses', 'Logo restoran berhasil diperbarui dan akan dipakai pada struk.');
    }

    public function diagnostik(): JsonResponse
    {
        return response()->json($this->diagnostikData());
    }

    public function bersihkanCache(): JsonResponse
    {
        try {
            Artisan::call('optimize:clear');

            return response()->json([
                'berhasil' => true,
                'pesan' => 'Cache aplikasi berhasil dibersihkan.',
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'berhasil' => false,
                'pesan' => 'Cache gagal dibersihkan. Periksa izin tulis direktori storage.',
            ], 500);
        }
    }

    public function unduhCadangan(): StreamedResponse
    {
        $tabel = [
            'users',
            'meja_makan',
            'kategori',
            'menu',
            'pesanan',
            'detail_pesanan',
            'pembayaran',
            'sesi_meja',
            'log_panggil_pelayan',
            'stok_mutasi',
            'pengaturan',
        ];

        $cadangan = [
            'aplikasi' => config('app.name'),
            'dibuat_pada' => now('Asia/Jakarta')->toIso8601String(),
            'database' => DB::connection()->getDatabaseName(),
            'tabel' => [],
        ];

        foreach ($tabel as $nama) {
            if (! Schema::hasTable($nama)) {
                continue;
            }

            $cadangan['tabel'][$nama] = DB::table($nama)
                ->orderBy('id')
                ->get()
                ->map(fn ($baris) => (array) $baris)
                ->all();
        }

        $json = json_encode($cadangan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $namaFile = 'situawi-cadangan-' . now('Asia/Jakarta')->format('Ymd-His') . '.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $namaFile, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    private function diagnostikData(?Collection $daftarMeja = null, ?int $sesiAktif = null): array
    {
        $database = [
            'terhubung' => false,
            'driver' => DB::connection()->getDriverName(),
            'nama' => DB::connection()->getDatabaseName(),
            'latensi' => null,
            'ukuran' => null,
            'jumlah_tabel' => null,
        ];

        try {
            $mulai = microtime(true);
            DB::select('select 1');
            $database['terhubung'] = true;
            $database['latensi'] = round((microtime(true) - $mulai) * 1000, 2);

            if ($database['driver'] === 'pgsql') {
                $ukuran = DB::selectOne('select pg_size_pretty(pg_database_size(current_database())) as ukuran');
                $database['ukuran'] = $ukuran->ukuran ?? null;
            }

            $database['jumlah_tabel'] = count(Schema::getTables());
        } catch (\Throwable $exception) {
            report($exception);
        }

        $reverbHost = (string) config('broadcasting.connections.reverb.options.host', 'localhost');
        $reverbPort = (int) config('broadcasting.connections.reverb.options.port', 8080);
        $reverbAktif = $this->cekSocket($reverbHost, $reverbPort);

        $storageTertaut = is_link(public_path('storage')) && file_exists(public_path('storage'));

        $daftarMeja ??= MejaMakan::query()->get(['id', 'kode_meja', 'id_device']);
        $sesiAktif ??= SesiMeja::query()->where('status', 'aktif')->count();
        $panggilanAktif = LogPanggilPelayan::query()->where('status_panggilan', 'menunggu')->count();

        return [
            'database' => $database,
            'reverb' => [
                'aktif' => $reverbAktif,
                'host' => $reverbHost,
                'port' => $reverbPort,
                'koneksi' => (string) config('broadcasting.default'),
            ],
            'midtrans' => [
                'siap' => filled(config('midtrans.merchant_id'))
                    && filled(config('midtrans.client_key'))
                    && filled(config('midtrans.server_key')),
                'mode' => config('midtrans.is_production') ? 'Production' : 'Sandbox',
                'merchant_id' => config('midtrans.merchant_id') ?: null,
            ],
            'storage' => [
                'tertaut' => $storageTertaut,
                'path' => public_path('storage'),
            ],
            'iot' => [
                'meja_terdaftar' => $daftarMeja->count(),
                'perangkat_terpasang' => $daftarMeja->whereNotNull('id_device')->count(),
                'sesi_aktif' => $sesiAktif,
                'panggilan_aktif' => $panggilanAktif,
                'polling_detik' => 30,
                'timeout_detik' => 8,
                'endpoint' => url('/api/iot/status-meja'),
                'transport' => 'WiFi + HTTP REST',
            ],
            'printer' => [
                'ip' => Pengaturan::nilai('printer_ip') ?: null,
                'port' => (int) Pengaturan::nilai('printer_port', 9100),
                'kertas' => Pengaturan::nilai('paper_size', '80mm'),
            ],
            'aplikasi' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'environment' => app()->environment(),
                'timezone' => config('app.timezone'),
                'waktu_server' => now('Asia/Jakarta')->toIso8601String(),
            ],
            'diperiksa_pada' => now('Asia/Jakarta')->toIso8601String(),
        ];
    }

    private function cekSocket(string $host, int $port): bool
    {
        $koneksi = @fsockopen($host, $port, $kodeError, $pesanError, 0.4);

        if (! $koneksi) {
            return false;
        }

        fclose($koneksi);

        return true;
    }

    private function samarkan(?string $nilai): string
    {
        if (blank($nilai)) {
            return 'Belum diatur';
        }

        $panjang = strlen($nilai);

        if ($panjang <= 8) {
            return str_repeat('•', $panjang);
        }

        return substr($nilai, 0, 4) . str_repeat('•', max(4, $panjang - 8)) . substr($nilai, -4);
    }
}
