<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selamat Datang - Saung Situ Awi</title>
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
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>

<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
    <div class="max-w-md mx-auto px-4 pt-8 pb-12">
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto rounded-3xl bg-forest-900 flex items-center justify-center shadow-lg mb-4">
                <svg class="w-12 h-12 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 8h1a3 3 0 0 1 0 6h-1M4 8h14v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z" />
                    <path stroke-linecap="round" d="M7 2v2M11 2v2M15 2v2" />
                </svg>
            </div>
            <p class="text-[11px] font-bold tracking-[0.2em] text-[#8a6d1f]">TRADISI SUNDA • PRESISI DIGITAL</p>
            <h1 class="font-display font-bold text-5xl text-forest-900 mt-2">Saung Situ Awi</h1>
            <p class="text-stone-500 text-base mt-3 max-w-lg mx-auto">Resto & saung lesehan tepi danau Ciwidey. Pesan
                dari meja, bayar tunai atau QRIS.</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
            <p class="text-[11px] font-bold tracking-widest text-[#8a6d1f] mb-4">CARA PEMESANAN</p>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-forest-900 flex items-center justify-center">
                        <span class="text-gold-500 font-bold text-xl">1</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="font-bold text-forest-900">Scan QR Code di Meja</p>
                        <p class="text-stone-500 text-sm mt-1">Setiap meja punya QR code unik. Arahkan kamera ke QR di
                            meja/LCD untuk langsung buka menu.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-forest-900 flex items-center justify-center">
                        <span class="text-gold-500 font-bold text-xl">2</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="font-bold text-forest-900">Pilih & Pesan Menu</p>
                        <p class="text-stone-500 text-sm mt-1">Tambah item ke bon, atur jumlah, catatan (mis. pedas
                            level 2), lalu checkout.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-forest-900 flex items-center justify-center">
                        <span class="text-gold-500 font-bold text-xl">3</span>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="font-bold text-forest-900">Bayar & Lacak</p>
                        <p class="text-stone-500 text-sm mt-1">Tunai di kasir atau QRIS. Status pesanan update realtime
                            ke LCD meja & HP kamu.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-forest-900 text-white rounded-3xl p-6 shadow-xl mb-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 shrink-0 rounded-2xl bg-white/10 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2M4 12h16" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-lg">Sudah di Meja?</p>
                    <p class="text-white/70 text-sm mt-0.5">Scan QR meja atau ketik kode dari LCD untuk langsung pesan.
                    </p>
                </div>
                <button type="button" id="tombolKamera" onclick="bukaKamera()"
                    class="bg-gold-500 hover:bg-[#d0a23f] text-forest-900 font-bold text-sm rounded-full px-6 py-3 shrink-0">Buka
                    Kamera & Scan</button>
            </div>
            <div id="areaKamera" class="hidden">
                <div id="pembaca" class="rounded-2xl overflow-hidden mb-3"></div>
                <button type="button" onclick="tutupKamera()"
                    class="w-full bg-white/10 text-white font-bold rounded-full py-3">Tutup Kamera</button>
            </div>
            <div class="flex gap-2 mt-4">
                <input id="kodeManual" type="text" placeholder="Ketik kode meja dari LCD (cth: LB-01, LA-05, KA-12)"
                    class="flex-1 bg-white/10 border border-white/20 rounded-full px-5 py-3 text-sm placeholder:text-white/40 outline-none focus:border-gold-500 focus:ring-2 focus:ring-gold-500/50">
                <button type="button" onclick="kirimManual()"
                    class="bg-white text-forest-900 font-bold text-sm rounded-full px-7 py-3 shrink-0">Buka
                    Menu</button>
            </div>
            <p id="pesanKode" class="hidden text-gold-500 text-sm font-bold mt-3 text-center">Kode tidak dikenal.
                Periksa QR/LCD di meja Anda.</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5">
            <p class="text-[11px] font-bold tracking-widest text-[#8a6d1f] mb-3">KODE MEJA DARI LCD</p>
            <p class="text-stone-600 text-sm mb-3">Di setiap saung tersedia layar LCD yang menampilkan kode meja
                (format: <span class="font-mono text-forest-900">LB-01</span>, <span
                    class="font-mono text-forest-900">LA-05</span>, <span
                    class="font-mono text-forest-900">KA-12</span>). Kode ini sama dengan yang ada di QR code meja.</p>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-cream-50 rounded-xl p-4 text-center border border-stone-100">
                    <p class="font-mono text-forest-900 text-lg font-bold">LB-01</p>
                    <p class="text-stone-500 text-xs mt-1">Saung Lesehan Blok B</p>
                </div>
                <div class="bg-cream-50 rounded-xl p-4 text-center border border-stone-100">
                    <p class="font-mono text-forest-900 text-lg font-bold">LA-05</p>
                    <p class="text-stone-500 text-xs mt-1">Saung Lesehan Blok A</p>
                </div>
                <div class="bg-cream-50 rounded-xl p-4 text-center border border-stone-100">
                    <p class="font-mono text-forest-900 text-lg font-bold">KA-12</p>
                    <p class="text-stone-500 text-xs mt-1">Saung Kursi Blok A</p>
                </div>
            </div>
            <p class="text-stone-500 text-xs mt-4 text-center">Format: <span
                    class="font-mono text-forest-900">[Blok]-[Nomor]</span> &nbsp;•&nbsp; Blok: LB, LA, KA</p>
        </div>

        <p class="text-center text-stone-400 text-xs mt-8">© 2026 Saung Situ Awi Ciwidey • RPL SMK Budi Bakti & Tim
            Equivyn</p>
    </div>
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

        function bukaKamera() {
            document.getElementById('areaKamera').classList.remove('hidden');
            document.getElementById('tombolKamera').classList.add('hidden');
            pemindai = new Html5Qrcode('pembaca');
            pemindai.start({
                facingMode: 'environment'
            }, {
                fps: 10,
                qrbox: 250
            }, function(hasil) {
                var tujuan = tujuanDari(hasil);
                if (tujuan) {
                    tutupKamera();
                    window.location.href = tujuan;
                }
            }, function(err) {
                 });
        }

        function tutupKamera() {
            if (pemindai) {
                pemindai.stop().catch(function() {});
                pemindai = null;
            }
            document.getElementById('areaKamera').classList.add('hidden');
            document.getElementById('tombolKamera').classList.remove('hidden');
        }

        function kirimManual() {
            var nilai = document.getElementById('kodeManual').value;
            var tujuan = tujuanDari(nilai);
            if (tujuan) {
                window.location.href = tujuan;
                return;
            }
            document.getElementById('pesanKode').classList.remove('hidden');
            setTimeout(function() {
                document.getElementById('pesanKode').classList.add('hidden');
            }, 3000);
        }
    </script>
</body>

</html>
