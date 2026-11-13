<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saung Situ Awi - Resto & Saung Lesehan Ciwidey</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/app.css')
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            forest: {
                                900: '#052E1B',
                                600: '#0B4D2B',
                                100: '#D9EBE0'
                            },
                            gold: {
                                500: '#E0B24E',
                                50: '#FBF3DD'
                            },
                            cream: {
                                50: '#FBF8F1'
                            }
                        },
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            display: ['"Playfair Display"', 'Georgia', 'serif']
                        }
                    }
                }
            };
        </script>
    @endif
    <link href="{{ asset('fonts/fonts.css') }}" rel="stylesheet">
<script defer src="{{ asset('vendor/html5-qrcode.min.js') }}"></script>
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-4 pt-6 pb-10">
        <div class="text-center">
            <img src="{{ asset('asset/Logo.png') }}" alt="Logo" class="mx-auto md:w-24 h-auto">

            <p class="text-[11px] font-bold tracking-[0.2em] text-[#8a6d1f] mt-4">TRADISI SUNDA • PRESISI DIGITAL</p>
            <h1 class="font-display font-bold text-4xl md:text-5xl text-forest-900 mt-1">Saung Situ Awi</h1>
            <p class="text-stone-500 text-sm md:text-base mt-2 max-w-xl mx-auto">Resto & saung lesehan tepi danau
                Ciwidey. Pesan dari meja, bayar tunai atau QRIS.</p>
            <div class="flex flex-wrap justify-center gap-2 mt-3 text-xs font-bold">
                <span class="bg-forest-900 text-white rounded-full px-3 py-1">Buka 09.00 – 21.00</span>
                <span class="bg-white border border-stone-200 text-stone-600 rounded-full px-3 py-1">Ciwidey,
                    Bandung</span>
                <span class="bg-white border border-stone-200 text-stone-600 rounded-full px-3 py-1">Lesehan &
                    Kursi</span>
            </div>
        </div>
        <div class="grid md:grid-cols-3 gap-3 mt-8">
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-forest-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2M4 12h16" />
                    </svg>
                </div>
                <p class="font-extrabold text-forest-900 mt-3">1. Scan QR / Kode Kunci</p>
                <p class="text-stone-500 text-xs mt-1">Pindai QR meja atau masukkan kode kunci login dari LCD.</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-forest-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h15l-1.5 9h-12zM6 6L5 3H2" />
                        <circle cx="9" cy="20" r="1.5" />
                        <circle cx="17" cy="20" r="1.5" />
                    </svg>
                </div>
                <p class="font-extrabold text-forest-900 mt-3">2. Pilih Menu</p>
                <p class="text-stone-500 text-xs mt-1">Tambah ke bon, atur jumlah dan catatan.</p>
            </div>
            <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-forest-900 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <rect x="3" y="6" width="18" height="13" rx="2" />
                        <path stroke-linecap="round" d="M3 10h18M7 15h4" />
                    </svg>
                </div>
                <p class="font-extrabold text-forest-900 mt-3">3. Bayar</p>
                <p class="text-stone-500 text-xs mt-1">Tunai di kasir atau QRIS dari meja.</p>
            </div>
        </div>
        <div class="bg-forest-900 text-white rounded-3xl p-5 mt-6 shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 shrink-0 rounded-2xl bg-white/10 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2M4 12h16" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold">Sudah di Meja?</p>
                    <p class="text-white/70 text-xs mt-0.5">Scan QR meja atau masukkan kode kunci login untuk langsung
                        pesan.</p>
                </div>
                <button type="button" id="tombolKamera" onclick="bukaKamera()"
                    class="bg-gold-500 hover:bg-[#d0a23f] text-forest-900 font-bold text-sm rounded-full px-5 py-2.5 shrink-0">Scan</button>
            </div>
            <div id="areaKamera" class="hidden mt-4">
                <div id="pembaca" class="rounded-2xl overflow-hidden"></div>
                <p id="pesanKamera" class="hidden mt-2 bg-white/10 text-amber-200 text-xs font-bold rounded-xl px-3 py-2"
                    aria-live="polite"></p>
                <button type="button" onclick="tutupKamera()"
                    class="w-full mt-2 bg-white/10 text-white text-sm font-bold rounded-full py-2">Tutup Kamera</button>
            </div>
            <form method="POST" action="{{ route('emenu.masuk') }}" class="flex gap-2 mt-4">
                @csrf
                <input id="kodeKunci" name="kode_unik" type="text" inputmode="numeric" pattern="[0-9]{4,6}"
                    maxlength="6" autocomplete="off" required value="{{ old('kode_unik') }}"
                    aria-label="Kode kunci login meja"
                    placeholder="Ketik kode kunci login (cth: 4821)"
                    class="flex-1 bg-white/10 border border-white/20 rounded-full px-4 py-2.5 text-sm placeholder:text-white/40 outline-none focus:border-gold-500">
                <button type="submit"
                    class="bg-white text-forest-900 font-bold text-sm rounded-full px-5 py-2.5 shrink-0">Buka
                    Menu</button>
            </form>
            <p class="text-white/60 text-[11px] mt-2">Kode kunci 4–6 digit tampil di LCD meja atau QR.</p>
            @error('kode_unik')
                <p class="text-gold-500 text-xs font-bold mt-2">{{ $message }}</p>
            @enderror
        </div>
        <p class="text-center text-stone-400 text-xs mt-8">© 2026 Saung Situ Awi Ciwidey • RPL SMK Budi Bakti & Tim
            Equivyn</p>
    </div>
    @php($konflikPerangkat = session('konflik_perangkat'))

    @if ($konflikPerangkat)
        <div id="modalKonflikPerangkat"
            class="fixed inset-0 z-50 flex items-center justify-center bg-forest-900/80 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-3xl border border-forest-100 bg-white p-6 text-center shadow-2xl">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gold-50 text-gold-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="mt-4 font-display text-xl font-bold text-forest-900">Sesi Sedang Dipakai</h2>
                <p class="mt-2 text-xs leading-relaxed text-stone-500">
                    Sesi meja <span class="font-bold text-forest-900">{{ $konflikPerangkat['kode_meja'] }}</span>
                    sedang aktif di
                    <span class="font-bold text-forest-900">{{ $konflikPerangkat['label'] }}</span>.
                    Tekan <span class="font-bold">Tutup Sesi Lama</span> untuk meminta perangkat lama menutup sesinya.
                </p>

                <div id="aksiKonflik" class="mt-5 space-y-2">
                    <button type="button" id="tombolTutupSesiLama"
                        class="w-full rounded-xl bg-forest-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-forest-600 active:scale-95 disabled:cursor-wait disabled:opacity-70">
                        Tutup Sesi Lama
                    </button>
                    <button type="button" id="tombolBatalKonflik"
                        class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm font-bold text-stone-600 transition hover:bg-stone-50 active:scale-95">
                        Batal
                    </button>
                </div>

                <div id="statusKonflik" class="mt-5 hidden">
                    <div class="flex items-center justify-center gap-2 text-sm font-bold text-forest-700">
                        <svg class="h-5 w-5 animate-spin" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <span>Menunggu konfirmasi perangkat lama...</span>
                    </div>
                    <button type="button" id="tombolBatalMenunggu"
                        class="mt-3 text-xs font-bold text-stone-400 underline hover:text-stone-600">Batalkan</button>
                </div>

                <p id="pesanKonflik" class="mt-4 hidden rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                    aria-live="polite"></p>
            </div>
        </div>

        <script>
            (function() {
                var konflik = @json($konflikPerangkat);
                var modal = document.getElementById('modalKonflikPerangkat');
                var aksi = document.getElementById('aksiKonflik');
                var statusBox = document.getElementById('statusKonflik');
                var pesanEl = document.getElementById('pesanKonflik');
                var tombolTutup = document.getElementById('tombolTutupSesiLama');
                var tombolBatal = document.getElementById('tombolBatalKonflik');
                var tombolBatalMenunggu = document.getElementById('tombolBatalMenunggu');
                var csrf = document.querySelector('meta[name="csrf-token"]').content;
                var timer = null;
                var batas = null;

                function tampilPesan(teks) {
                    pesanEl.textContent = teks || '';
                    pesanEl.classList.toggle('hidden', !teks);
                }

                function berhenti() {
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                    if (batas) {
                        clearTimeout(batas);
                        batas = null;
                    }
                }

                function ke(url) {
                    berhenti();
                    window.location.href = url;
                }

                function kembaliKeAksi(pesan) {
                    berhenti();
                    statusBox.classList.add('hidden');
                    aksi.classList.remove('hidden');
                    tombolTutup.disabled = false;
                    tombolBatal.disabled = false;
                    tampilPesan(pesan);
                }

                async function cekStatus() {
                    try {
                        var response = await fetch(konflik.status_url, {
                            headers: {
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (! response.ok) {
                            return;
                        }

                        var data = await response.json();

                        if (data.redirect) {
                            return ke(data.redirect);
                        }

                        if (data.peran === 'aktif' && data.takeover && data.takeover.status === 'disetujui') {
                            return ke('/meja/' + encodeURIComponent(konflik.kode_meja));
                        }

                        var status = data.takeover ? data.takeover.status : null;

                        if (status === 'disetujui') {
                            return ke('/meja/' + encodeURIComponent(konflik.kode_meja));
                        }

                        if (status === 'ditolak') {
                            kembaliKeAksi('Perangkat lama menolak permintaan. Sesi tetap aktif di perangkat lama.');
                            return;
                        }

                        if (status === 'kedaluwarsa') {
                            kembaliKeAksi('Perangkat lama tidak merespons. Coba lagi atau minta bantuan pelayan.');
                        }
                    } catch (error) {
                        // Jaringan sedang bermasalah; polling berikutnya akan mencoba lagi.
                    }
                }

                tombolTutup.addEventListener('click', async function() {
                    tombolTutup.disabled = true;
                    tombolBatal.disabled = true;
                    tampilPesan('');

                    try {
                        var response = await fetch(konflik.minta_url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            credentials: 'same-origin'
                        });
                        var data = await response.json().catch(function() {
                            return {};
                        });

                        if (data.redirect) {
                            return ke(data.redirect);
                        }

                        if (data.status === 'menunggu' || data.status === 'disetujui') {
                            aksi.classList.add('hidden');
                            statusBox.classList.remove('hidden');
                            timer = window.setInterval(cekStatus, 2000);
                            batas = window.setTimeout(function() {
                                kembaliKeAksi('Perangkat lama tidak merespons. Coba lagi atau minta bantuan pelayan.');
                            }, 65000);
                            cekStatus();
                            return;
                        }

                        tombolTutup.disabled = false;
                        tombolBatal.disabled = false;
                        tampilPesan(data.message || 'Permintaan gagal. Coba lagi.');
                    } catch (error) {
                        tombolTutup.disabled = false;
                        tombolBatal.disabled = false;
                        tampilPesan('Koneksi gagal. Periksa jaringan lalu coba lagi.');
                    }
                });

                tombolBatal.addEventListener('click', function() {
                    berhenti();
                    modal.remove();
                });

                tombolBatalMenunggu.addEventListener('click', async function() {
                    berhenti();
                    try {
                        await fetch(konflik.batal_url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            credentials: 'same-origin'
                        });
                    } catch (error) {
                        // Tidak masalah; permintaan akan kedaluwarsa sendiri.
                    }
                    modal.remove();
                });
            })();
        </script>
    @endif

    <script>
        var pemindai = null;

        function tujuanDari(teks) {
            var bersih = (teks || '').trim();
            var cocok = bersih.match(/\/meja\/([A-Za-z0-9-]+)/);
            if (cocok) {
                return '/meja/' + cocok[1];
            }
            if (/^[0-9]+$/.test(bersih)) {
                return '/meja/' + bersih;
            }
            if (/^[A-Za-z]{2}-[0-9]+$/i.test(bersih)) {
                return '/meja/' + bersih.toUpperCase();
            }
            return '';
        }

        function tampilPesanKamera(teks) {
            var el = document.getElementById('pesanKamera');
            if (!el) return;
            el.textContent = teks || '';
            el.classList.toggle('hidden', !teks);
        }

        function bukaKamera() {
            document.getElementById('areaKamera').classList.remove('hidden');
            document.getElementById('tombolKamera').classList.add('hidden');
            tampilPesanKamera('');

            if (!window.isSecureContext) {
                tampilPesanKamera('Kamera hanya bisa dibuka lewat HTTPS atau localhost. Akses halaman ini memakai alamat https://...');
                return;
            }

            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                tampilPesanKamera('Browser ini tidak mendukung akses kamera. Gunakan Chrome atau Safari versi terbaru.');
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                tampilPesanKamera('Pemindai QR belum termuat. Muat ulang halaman lalu coba lagi.');
                return;
            }

            if (pemindai) {
                return;
            }

            pemindai = new Html5Qrcode('pembaca');
            pemindai.start({
                facingMode: 'environment'
            }, {
                fps: 10,
                qrbox: 220
            }, function(hasil) {
                var tujuan = tujuanDari(hasil);
                if (tujuan) {
                    tutupKamera();
                    window.location.href = tujuan;
                }
            }).catch(function(err) {
                pemindai = null;
                var pesan = String((err && (err.message || err.name)) || '');
                if (/NotAllowed|Permission|denied/i.test(pesan)) {
                    tampilPesanKamera('Izin kamera ditolak. Aktifkan izin kamera di pengaturan browser lalu coba lagi.');
                } else if (/NotFound|DevicesNotFound|device/i.test(pesan)) {
                    tampilPesanKamera('Kamera tidak ditemukan. Pastikan perangkat memiliki kamera.');
                } else {
                    tampilPesanKamera('Kamera gagal dibuka. Pastikan halaman diakses lewat HTTPS dan izin kamera diberikan.');
                }
            });
        }

        function tutupKamera() {
            var aktif = pemindai;
            pemindai = null;

            if (aktif) {
                try {
                    var proses = aktif.stop();
                    if (proses && typeof proses.catch === 'function') {
                        proses.catch(function() {});
                    }
                } catch (err) {
                    // Kamera mungkin belum sempat aktif; tidak perlu menghentikan halaman.
                }
            }

            document.getElementById('areaKamera').classList.add('hidden');
            document.getElementById('tombolKamera').classList.remove('hidden');
            tampilPesanKamera('');
        }

    </script>
</body>

</html>
