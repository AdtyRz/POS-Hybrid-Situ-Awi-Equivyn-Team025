<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
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
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
    <div class="max-w-md mx-auto px-4 pt-8 pb-10" x-data="kunciApp({{ $panjangKode }})">
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
            <form method="POST" action="{{ route('emenu.buka', $meja->kode_meja) }}" @submit="persiapkan()">
                @csrf
                <input type="hidden" name="kode_unik" :value="kode.join('')">
                <div class="flex justify-center gap-3">
                    <template x-for="(digit, i) in kode" :key="i">
                        <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" x-ref="kotak"
                            :value="digit" @input="ketik(i, $event)" @keydown.backspace="hapus(i, $event)"
                            @paste="tempel($event)"
                            class="w-14 h-16 text-center text-3xl font-extrabold font-mono rounded-xl border-2 border-stone-200 focus:border-forest-600 focus:ring-2 focus:ring-forest-600/20 outline-none text-forest-900 tracking-widest"
                            autocomplete="off">
                    </template>
                </div>

                @error('kode_unik')
                    <p class="text-red-600 text-xs font-bold text-center mt-3">{{ $message }}</p>
                @enderror

                <button type="submit"
                    class="w-full mt-5 py-3.5 rounded-xl bg-forest-900 hover:bg-forest-600 text-white font-extrabold text-sm tracking-wider uppercase flex items-center justify-center gap-2 shadow-sm transition active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Buka Menu
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
        function kunciApp(panjang) {
            return {
                panjang: panjang,
                kode: Array(panjang).fill(''),
                init() {
                    this.focusKotak(0);
                },
                focusKotak(i) {
                    this.$nextTick(() => {
                        const el = this.$refs.kotak && this.$refs.kotak[i];
                        if (el) el.focus();
                    });
                },
                ketik(i, event) {
                    const angka = (event.target.value || '').replace(/[^0-9]/g, '');
                    this.kode[i] = angka.slice(-1);
                    event.target.value = this.kode[i];
                    if (this.kode[i] && i < this.panjang - 1) {
                        this.focusKotak(i + 1);
                    }
                },
                hapus(i, event) {
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
                    this.kode = Array(this.panjang).fill('').map((_, i) => angka[i] || '');
                    this.focusKotak(Math.min(angka.length, this.panjang - 1));
                },
                persiapkan() {
                    const kosong = this.kode.findIndex(d => !d);
                    this.focusKotak(kosong === -1 ? this.panjang - 1 : kosong);
                }
            };
        }
    </script>
    <script>
        window.addEventListener('realtime', () => window.realtimeReload(600));
    </script>
</body>

</html>
