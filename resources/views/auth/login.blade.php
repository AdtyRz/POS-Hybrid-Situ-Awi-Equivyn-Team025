<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Saung Situ Awi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        display: ['"Playfair Display"', 'serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=playfair-display:600,700|plus-jakarta-sans:400,500,600,700,800&display=swap"
        rel="stylesheet">
</head>

<body class="min-h-screen bg-[#fdf9ef] text-stone-800 antialiased font-sans">

    <div
        class="pointer-events-none fixed -top-40 -left-32 w-[420px] h-[420px] rounded-full bg-[#052E1B] opacity-20 blur-3xl">
    </div>
    <div
        class="pointer-events-none fixed -bottom-40 -right-32 w-[480px] h-[480px] rounded-full bg-[#fbe6bd] opacity-70 blur-3xl">
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-8">
        <div class="w-full max-w-[960px]">

            <div
                class="grid lg:grid-cols-[1.4fr_1fr] rounded-[1.75rem] relative overflow-hidden shadow-[0_25px_60px_-20px_rgba(11,59,44,0.3)] mt-4">

                <div class="relative overflow-hidden bg-[#f6f2e8] px-9 pt-[46px] pb-8 hidden lg:flex flex-col">
                    <svg class="absolute inset-0 w-full h-full text-[#eadcb4] opacity-80 pointer-events-none"
                        fill="none" stroke="currentColor" stroke-width="12" viewBox="0 0 560 582"
                        preserveAspectRatio="xMaxYMax slice" aria-hidden="true">
                        <path d="M520 150 C 610 230, 610 340, 520 410 C 470 450, 430 475, 360 520" />
                        <ellipse cx="430" cy="548" rx="150" ry="26" />
                    </svg>

                    <div class="relative flex flex-col flex-1">
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('asset/Logo.png') }}" alt="Logo Situ Awi" class=" h-12">
                            <div class="min-w-0">
                                <span
                                    class="inline-block bg-[#fde3b0] text-[#3b2e0a] text-[9px] font-bold tracking-[0.18em] rounded-full px-2.5 py-0.5">TRADISI
                                    SUNDA • PRESISI DIGITAL</span>
                                <h1 class="font-display font-bold text-[2rem] leading-tight text-[#0b3b2c] mt-1">Saung
                                    Situ Awi</h1>
                                <p class="text-stone-600 text-[11px] mt-0.5 truncate">Sistem Operasional Terintegrasi
                                    POS, Kitchen Display &amp; IoT Node…</p>
                            </div>
                        </div>

                        <div class="flex items-start justify-between mt-7 gap-4">
                            <div>
                                <h2 class="font-bold tracking-wide text-stone-800 text-[14px]">PANDUAN &amp; KEBIJAKAN
                                    OPERASIONAL</h2>
                                <p class="text-stone-600 text-[11.5px] mt-1 leading-relaxed max-w-[330px]">Tata tertib
                                    dan standar penggunaan sistem digital Saung Situ Awi Ciwidey.</p>
                            </div>
                            <span
                                class="flex items-center gap-1.5 bg-[#fde3b0] text-[#3b2e0a] rounded-full pl-2.5 pr-4 py-1.5 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path stroke-linejoin="round"
                                        d="M12 2l2.4 1.7 2.9-.2 1 2.8 2.4 1.7-.9 2.8.9 2.8-2.4 1.7-1 2.8-2.9-.2L12 22l-2.4-1.7-2.9.2-1-2.8L3.3 16l.9-2.8-.9-2.8 2.4-1.7 1-2.8 2.9.2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12l2.3 2.3 4.7-4.7" />
                                </svg>
                                <span class="text-[10px] font-bold leading-tight">SOP<br>Digital</span>
                            </span>
                        </div>

                        <div
                            class="bg-white rounded-xl border border-[#eee8da] shadow-[0_2px_10px_rgba(0,0,0,0.04)] p-4 mt-4 flex gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-[#0b3b2c] flex items-center justify-center">
                                <svg class="w-[18px] h-[18px] text-white" fill="none" stroke="currentColor"
                                    stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="4" y="10" width="16" height="10" rx="2" />
                                    <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    <circle cx="12" cy="15" r="1.6" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="font-bold text-[#0b3b2c] text-[12px]">Integritas Akun &amp; Sesi Kerja
                                    </h3>
                                    <span
                                        class="bg-[#e3f5e8] text-[#0b3b2c] text-[10px] font-semibold rounded px-2 py-0.5 shrink-0">Wajib
                                        Staf</span>
                                </div>
                                <p class="text-stone-600 text-[11.5px] mt-1 leading-relaxed">Setiap staf wajib login
                                    menggunakan ID akun masing-masing. Dilarang berbagi PIN operasional kasir atau
                                    stasiun KDS demi akurasi pelaporan shift.</p>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl border border-[#eee8da] shadow-[0_2px_10px_rgba(0,0,0,0.04)] p-4 mt-3 flex gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-[#7a5c0f] flex items-center justify-center">
                                <svg class="w-[18px] h-[18px] text-white" fill="none" stroke="currentColor"
                                    stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 9a8 8 0 0 1 14-3M20 15a8 8 0 0 1-14 3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 2v4h-4M6 22v-4h4" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="font-bold text-[#7a5c0f] text-[12px]">Sinkronisasi IoT &amp; Pesanan
                                        Real-time</h3>
                                    <span
                                        class="bg-[#fdebc8] text-[#7a5c0f] text-[10px] font-semibold rounded px-2 py-0.5 shrink-0">MQTT
                                        Live</span>
                                </div>
                                <p class="text-stone-600 text-[11.5px] mt-1 leading-relaxed">Seluruh pesanan tamu
                                    berbasis QR saung tersinkron otomatis ke KDS Dapur &amp; Bar via WebSocket &amp;
                                    MQTT Broker. Pastikan status koneksi selalu Online.</p>
                            </div>
                        </div>

                        <div
                            class=" relative z-10 bg-white rounded-xl border border-[#eee8da] shadow-[0_2px_10px_rgba(0,0,0,0.04)] p-4 mt-3 flex gap-3">
                            <div class="w-8 h-8 shrink-0 rounded-lg bg-[#2f6b50] flex items-center justify-center">
                                <svg class="w-[18px] h-[18px] text-white" fill="none" stroke="currentColor"
                                    stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 13a8 8 0 0 1 16 0" />
                                    <rect x="2" y="13" width="4" height="6" rx="1.5" />
                                    <rect x="18" y="13" width="4" height="6" rx="1.5" />
                                    <path stroke-linecap="round" d="M20 19a4 4 0 0 1-4 2h-2" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="font-bold text-[#2f6b50] text-[12px]">Prosedur Respon Panggilan Saung
                                    </h3>
                                    <span
                                        class="bg-[#e8e6de] text-stone-700 font-mono text-[10px] font-bold rounded px-2 py-0.5 shrink-0">Max
                                        2 Mnt</span>
                                </div>
                                <p class="text-stone-600 text-[11.5px] mt-1 leading-relaxed">Saat modul IoT Table Node
                                    berbunyi, pelayan/runner terdekat wajib merespon dan memverifikasi saung pemanggil
                                    dalam waktu maksimal 2 menit.</p>
                            </div>
                        </div>
                        <div
                            class="pointer-events-none absolute -bottom-40 -left-32 w-[234px] h-[234px] rounded-full bg-[#052E1B] opacity-15 blur-3xl">
                        </div>


                        <div
                            class="flex items-center justify-center mt-auto pt-7 text-[11.5px] relative overflow-hidden">
                            <span class="flex items-center gap-2 text-stone-700">
                                <svg class="w-4 h-4 text-[#0b3b2c]" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3zm-1.2 14.5-3.3-3.3 1.4-1.4 1.9 1.9 4.9-4.9 1.4 1.4-6.3 6.3z" />
                                </svg>
                                <span class="relative z-10 font-medium">Guard: Multi-Tenancy SaaS Perimeter</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white px-[38px] pt-[56px] pb-9 flex-col relative overflow-hidden flex">
                    <div
                        class="pointer-events-none absolute -top-12 -right-12 w-[224px] h-[224px] rounded-full bg-[#fbe6bd] opacity-70 blur-3xl">
                    </div>

                    <h2
                        class="relative z-10 font-bold tracking-tight text-[1.65rem] leading-tight text-stone-900 mt-5">
                        Otentikasi
                        Staf</h2>
                    <p class="relative z-10 text-stone-600 text-[11px] mt-1.5 leading-relaxed">Masukkan kredensial
                        terdaftar atau
                        pilih peran cepat di samping untuk masuk ke lingkungan kerja Saung Situ Awi.</p>

                    <form method="POST" action="{{ route('login') }}" class="mt-5">
                        @csrf
                        <label for="email" class="block font-semibold text-stone-800 text-[11px]">Email Akun / ID
                            Pegawai</label>
                        <div
                            class="flex items-center gap-3 bg-[#faf6ec] border border-transparent rounded-xl px-3.5 mt-2 focus-within:border-[#0b3b2c]">
                            <svg class="w-5 h-5 text-stone-500 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.6" viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <circle cx="8.5" cy="11" r="1.8" />
                                <path stroke-linecap="round"
                                    d="M5.5 16.5c.6-1.7 1.7-2.5 3-2.5s2.4.8 3 2.5M14 9.5h5M14 13h5" />
                            </svg>
                            <input id="email" type="email" name="email"
                                value="{{ old('email', 'admin@situawi.com') }}" required autocomplete="username"
                                class="w-full bg-transparent border-0 outline-none focus:ring-0 py-3 text-[13px] text-stone-800">
                        </div>
                        @error('email')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center justify-between mt-4">
                            <label for="password" class="font-semibold text-stone-800 text-[11px]">Kata Sandi</label>
                            <button type="button" onclick="togglePin()"
                                class="flex items-center gap-1 text-[#0b3b2c] font-bold text-[10px]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                    viewBox="0 0 24 24">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <span id="lihatPinTeks">Lihat PIN</span>
                            </button>
                        </div>
                        <div
                            class="flex items-center gap-3 bg-[#faf6ec] border border-transparent rounded-xl px-3.5 mt-2 focus-within:border-[#0b3b2c]">
                            <svg class="w-5 h-5 text-stone-500 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.6" viewBox="0 0 24 24">
                                <rect x="4" y="10" width="16" height="10" rx="2" />
                                <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3" />
                            </svg>
                            <input id="password" type="password" name="password"
                                required autocomplete="current-password"
                                placeholder="Masukkan password operasional"
                                class="w-full bg-transparent border-0 outline-none focus:ring-0 py-3 text-[13px] text-stone-800">
                        </div>
                        @error('password')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center justify-between mt-4 text-[11px]">
                            <label for="remember" class="flex items-center gap-2 text-stone-600">
                                <input id="remember" type="checkbox" name="remember" checked
                                    class="w-4 h-4 rounded border-[#0b3b2c] text-[#0b3b2c] accent-[#0b3b2c] focus:ring-[#0b3b2c]">
                                Ingat sesi di saung ini
                            </label>
                            <span class="font-bold text-[#0b3b2c]">Lupa PIN?</span>
                        </div>

                        <button type="submit"
                            class="w-full mt-5 bg-[#0a3a28] hover:bg-[#0e4a37] text-white font-semibold text-[13px] rounded-2xl h-[46px] flex items-center justify-center gap-2.5 shadow-[0_10px_20px_-8px_rgba(11,59,44,0.55)]">
                            Masuk ke Sistem Operasional
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </button>
                    </form>

                    <div class="flex items-center gap-2.5 bg-[#faf6ec] rounded-xl px-3.5 py-3 mt-auto pt-3">
                        <svg class="w-5 h-5 text-[#7a5c0f] shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3zm-1.2 14.5-3.3-3.3 1.4-1.4 1.9 1.9 4.9-4.9 1.4 1.4-6.3 6.3z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-stone-800 text-[12px] tracking-wide">Masuk ke Sistem Operasional
                            </p>
                        </div>
                        <span
                            class="bg-[#fde3b0] text-[#5a430a] text-[9px] font-extrabold rounded-md px-2 py-1 shrink-0">SITU
                            AWI</span>
                    </div>
                    <div class="h-0 lg:h-[0px]"></div>
                </div>
            </div>

            <div class="flex flex-col md:flex-center items-center justify-between gap-3 mt-5 px-2 md:px-16">
                <div class="text-center md:text-center text-[11px] leading-relaxed">
                    <p class="text-stone-700">© 2026 Resto &amp; Saung Lesehan Situ Awi Ciwidey</p>
                    <p class="text-stone-400">RPL SMK Budi Bakti Ciwidey &amp; Tim Equivyn</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        function togglePin() {
            var kolom = document.getElementById('password');
            var teks = document.getElementById('lihatPinTeks');
            if (kolom.type === 'password') {
                kolom.type = 'text';
                teks.textContent = 'Sembunyi';
            } else {
                kolom.type = 'password';
                teks.textContent = 'Lihat PIN';
            }
        }
    </script>
</body>

</html>
