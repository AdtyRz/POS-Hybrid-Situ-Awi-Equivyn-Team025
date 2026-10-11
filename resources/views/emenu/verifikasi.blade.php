<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verifikasi Pembayaran QRIS - Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
theme: {
extend: {
colors: {
forest: { 900: '#052E1B', 600: '#0B4D2B', 100: '#D9EBE0' },
gold: { 500: '#E0B24E', 50: '#FBF3DD' },
cream: { 50: '#FBF8F1' }
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
<link href="https://fonts.bunny.net/css?family=playfair-display:600,700|plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans flex flex-col">
<div class="max-w-md mx-auto px-4 pt-10 pb-10 flex-1 flex flex-col items-center justify-center">
<div class="w-full max-w-md">
<div class="text-center mb-8">
<div class="w-24 h-24 mx-auto rounded-3xl bg-forest-900 flex items-center justify-center shadow-lg mb-4">
<svg class="w-12 h-12 text-gold-500 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
</div>
<p class="text-[11px] font-bold tracking-[0.2em] text-[#8a6d1f] mt-4">TRADISI SUNDA • PRESISI DIGITAL</p>
<h1 class="font-display font-bold text-3xl text-forest-900 mt-1">Saung Situ Awi</h1>
</div>

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6 text-center">
<svg class="w-16 h-16 mx-auto text-gold-500 animate-spin" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
<h2 class="font-display font-bold text-2xl text-forest-900 mt-4">Menunggu Verifikasi QRIS</h2>
<p class="text-stone-500 text-sm mt-2">Pembayaran Anda sedang diproses. Halaman ini akan otomatis berpindah setelah pembayaran berhasil diverifikasi.</p>
<div class="mt-6 p-4 bg-cream-50 rounded-xl border border-stone-100">
<p class="font-bold text-forest-900">Kode Pesanan: <span class="font-mono">{{ $pesanan->kode_pesanan }}</span></p>
<p class="text-stone-500 text-sm mt-1">Total: Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
</div>
</div>

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
<h3 class="font-bold text-forest-900 mb-4">Instruksi Pembayaran QRIS</h3>
<div class="space-y-3 text-sm text-stone-600">
<div class="flex items-center gap-3">
<span class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">1</span>
<span>Buka aplikasi e-wallet/banking (GoPay, OVO, Dana, ShopeePay, BCA Mobile, dll)</span>
</div>
<div class="flex items-center gap-3">
<span class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">2</span>
<span>Pilih fitur "Bayar QRIS" atau "Scan QR"</span>
</div>
<div class="flex items-center gap-3">
<span class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">3</span>
<span>Arahkan kamera ke QR Code di meja atau tampil di layar kasir</span>
</div>
<div class="flex items-center gap-3">
<span class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">4</span>
<span>Konfirmasi pembayaran di aplikasi Anda</span>
</div>
<div class="flex items-center gap-3">
<span class="w-8 h-8 rounded-full bg-forest-100 text-forest-600 flex items-center justify-center font-bold">5</span>
<span>Halaman ini akan otomatis berpindah ke halaman status pesanan</span>
</div>
</div>
</div>

<p class="text-center text-stone-400 text-xs mt-8">© 2026 Saung Situ Awi Ciwidey</p>
</div>
</div>

<script>
let checkCount = 0;
const maxChecks = 30;
const orderCode = "{{ $pesanan->kode_pesanan }}";

function checkPaymentStatus() {
checkCount++;
fetch(`/emenu/status/${orderCode}`)
.then(response => response.text())
.then(html => {
const parser = new DOMParser();
const doc = parser.parseFromString(html, 'text/html');
const statusElement = doc.querySelector('[data-status]');
if (statusElement) {
const status = statusElement.getAttribute('data-status');
if (status === 'diproses' || status === 'siap' || status === 'diantar' || status === 'selesai') {
window.location.href = `/status/${orderCode}`;
return;
}
}
if (checkCount < maxChecks) {
setTimeout(checkPaymentStatus, 3000);
}
})
.catch(() => {
if (checkCount < maxChecks) {
setTimeout(checkPaymentStatus, 3000);
}
});
}

checkPaymentStatus();
</script>
</body>
</html>