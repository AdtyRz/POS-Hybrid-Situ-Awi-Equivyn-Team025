<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="realtime-version-url" content="{{ route('realtime.version') }}">
    <meta name="realtime-version" content="{{ \App\Support\Realtime::version() }}">
    <title>Buka Menu {{ $meja->kode_meja }} - Saung Situ Awi</title>
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
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=playfair-display:600,700|plus-jakarta-sans:400,500,600,700,800&display=swap"
        rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        <script>
            window.__CHANNELS__ = ['meja.{{ $meja->kode_meja }}'];
        </script>
        @vite('resources/js/reverb.js')
    @endif
    <style>
        @keyframes kodeSalah {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-7px); }
            40% { transform: translateX(7px); }
            60% { transform: translateX(-5px); }
            80% { transform: translateX(5px); }
        }

        @keyframes kodeBenar {
            0% { transform: scale(1); }
            45% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }

        .kode-salah {
            animation: kodeSalah 0.45s ease;
        }

        .kode-benar {
            animation: kodeBenar 0.5s ease;
        }
    </style>
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
    <div class="max-w-md mx-auto px-4 pt-8 pb-10"
        x-data="kunciApp({{ $panjangKode }}, @js($errors->has('kode_unik')), @js($errors->first('kode_unik') ?? ''))">
        <div class="text-center">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-forest-900 flex items-center justify-center shadow-lg">
                <svg class="w-10 h-10 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.6"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z" />
                </svg>
            </div>
            <p class="text-[11px] font-bold tracking-[0.2em] text-[#8a6d1f] mt-4">• SAUNG SITU AWI</p>
            <h1 class="font-display font-bold text-3xl text-forest-900 mt-1">Akses E-Menu</h1>
            <p class="text-stone-500 text-sm mt-2">Masukkan kode unik <span
                    class="font-bold text-forest-900">{{ $meja->kode_meja }}</span> yang tampil di layar meja Anda.</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 mt-6">
            <form x-ref="formKode" method="POST" action="{{ route('emenu.buka', $meja->kode_meja) }}"
                @submit.prevent="kirim()">
                @csrf
                <input type="hidden" name="kode_unik" :value="kode.join('')">
                <div class="flex justify-center gap-2 sm:gap-3" x-ref="wadahKode">
                    <template x-for="(digit, i) in kode" :key="i">
                        <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" data-kode-digit
                            :value="digit" @input="ketik(i, $event)" @keydown.backspace="hapus(i, $event)"
                            @focus="$event.target.select()" @paste="tempel($event)" :aria-label="'Digit kode ' + (i + 1)"
                            class="text-center text-3xl font-extrabold font-mono rounded-xl border-2 outline-none tracking-widest text-forest-900 caret-transparent selection:bg-transparent transition-all duration-200"
                            style="width: clamp(2.5rem, 12vw, 3.5rem); height: clamp(3.5rem, 16vw, 4rem);"
                            :class="salah
                                ? 'border-red-500 bg-red-50 text-red-700 kode-salah'
                                : (sukses
                                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 kode-benar'
                                    : (digit
                                        ? 'border-forest-600 bg-forest-100/60 text-forest-900'
                                        : 'border-stone-200 focus:border-forest-600 focus:ring-2 focus:ring-forest-600/20'))"
                            autocomplete="off">
                    </template>
                </div>

                <p x-show="pesanError" x-text="pesanError" aria-live="polite"
                    class="text-red-600 text-xs font-bold text-center mt-3"></p>

                <button type="submit" :disabled="memproses || sukses"
                    class="w-full mt-5 py-3.5 rounded-xl bg-forest-900 hover:bg-forest-600 disabled:cursor-wait disabled:opacity-80 text-white font-extrabold text-sm tracking-wider uppercase flex items-center justify-center gap-2 shadow-sm transition active:scale-95">
                    <svg x-show="!sukses" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="sukses" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="sukses ? 'Kode Benar' : (memproses ? 'Memeriksa...' : 'Buka Menu')"></span>
                </button>
            </form>
        </div>

        <a href="{{ route('emenu.index') }}"
            class="block text-center text-stone-400 text-xs font-bold mt-6 hover:text-stone-600">← Kembali ke
            Beranda</a>
        <p class="text-center text-stone-400 text-[11px] mt-6">Kode berubah setiap sesi. Tanyakan pelayan bila layar
            meja tidak menampilkan kode.</p>
    </div>

    <script>
        function kunciApp(panjang, salahAwal, pesanAwal) {
            return {
                panjang: panjang,
                kode: Array(panjang).fill(''),
                salah: salahAwal,
                sukses: false,
                memproses: false,
                pesanError: pesanAwal,
                init() {
                    this.$nextTick(() => this.focusKotak(0));
                    if (this.salah) {
                        this.getarkan();
                    }
                },
                daftarKotak() {
                    if (! this.$refs.wadahKode) {
                        return [];
                    }

                    return Array.from(this.$refs.wadahKode.querySelectorAll('input[data-kode-digit]'));
                },
                focusKotak(i) {
                    const el = this.daftarKotak()[i];
                    if (! el) return;

                    // Fokus harus dipanggil langsung di dalam event input agar
                    // keyboard ponsel tetap terbuka dan otomatis pindah kotak.
                    el.focus({ preventScroll: true });
                },
                bersihkanPesan() {
                    this.salah = false;
                    this.sukses = false;
                    this.pesanError = '';
                },
                ketik(i, event) {
                    const angka = (event.target.value || '').replace(/[^0-9]/g, '');
                    this.bersihkanPesan();
                    this.kode[i] = angka.slice(-1);
                    event.target.value = this.kode[i];
                    if (this.kode[i] && i < this.panjang - 1) {
                        this.focusKotak(i + 1);
                    }
                },
                hapus(i, event) {
                    this.bersihkanPesan();
                    if (!this.kode[i] && i > 0) {
                        event.preventDefault();
                        this.kode[i - 1] = '';
                        this.focusKotak(i - 1);
                    } else {
                        this.kode[i] = '';
                    }
                },
                tempel(event) {
                    const teks = (event.clipboardData || window.clipboardData).getData('text') || '';
                    const angka = teks.replace(/[^0-9]/g, '').slice(0, this.panjang);
                    if (!angka) return;
                    event.preventDefault();
                    this.bersihkanPesan();
                    this.kode = Array(this.panjang).fill('').map((_, i) => angka[i] || '');
                    this.focusKotak(Math.min(angka.length, this.panjang - 1));
                },
                getarkan() {
                    this.salah = false;
                    this.$nextTick(() => {
                        this.salah = true;
                    });
                },
                async kirim() {
                    if (this.memproses || this.sukses) return;

                    const kosong = this.kode.findIndex(d => !d);
                    if (kosong !== -1) {
                        this.salah = true;
                        this.pesanError = 'Lengkapi semua digit kode terlebih dahulu.';
                        this.getarkan();
                        this.focusKotak(kosong);
                        return;
                    }

                    this.memproses = true;
                    this.salah = false;
                    this.pesanError = '';

                    try {
                        const response = await fetch(this.$refs.formKode.action, {
                            method: 'POST',
                            body: new FormData(this.$refs.formKode),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        const data = await response.json().catch(() => ({}));

                        if (response.ok && data.redirect) {
                            this.sukses = true;
                            window.setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 650);
                            return;
                        }

                        this.salah = true;
                        this.pesanError = data.errors?.kode_unik?.[0] || data.message ||
                            'Kode tidak sesuai dengan yang tampil di layar meja.';
                        this.getarkan();
                        this.focusKotak(0);
                    } catch {
                        this.salah = true;
                        this.pesanError = 'Koneksi gagal. Periksa jaringan lalu coba lagi.';
                        this.getarkan();
                    } finally {
                        this.memproses = false;
                    }
                }
            };
        }
    </script>
    <script>
        window.addEventListener('realtime', () => window.realtimeReload(600));
    </script>
</body>

</html>
