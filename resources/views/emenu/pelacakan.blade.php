<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pelacakan &amp; Status Pesanan - Saung Situ Awi</title>
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
<body class="min-h-screen bg-cream-50 text-stone-800 antialiased font-sans">
<div class="max-w-md mx-auto px-4 pt-4 pb-8">
<div class="flex items-center justify-between">
<a href="{{ route('emenu.meja', $meja->id) }}" class="w-9 h-9 flex items-center justify-center" aria-label="Kembali">
<svg class="w-6 h-6 text-stone-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
</a>
<div class="text-center">
<p class="text-[11px] font-bold tracking-widest text-[#8a6d1f]">• SAUNG SITU AWI</p>
<p class="font-bold text-lg leading-tight">Pelacakan &amp; Status Pesanan</p>
</div>
<div class="flex items-center gap-2">
<span class="bg-white border border-stone-200 rounded-full px-3 py-1.5 text-xs font-bold whitespace-nowrap">{{ $meja->kode_meja }}</span>
<div class="w-9 h-9 rounded-full bg-forest-900 flex items-center justify-center">
<svg class="w-5 h-5 text-gold-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 8h1a3 3 0 0 1 0 6h-1M4 8h14v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8z"/></svg>
</div>
</div>
</div>

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4 mt-4">
<p class="text-[11px] font-bold tracking-widest text-[#8a6d1f]">RIWAYAT SESI MEJA</p>
<p class="text-stone-500 text-xs mt-1">Daftar pesanan yang tersimpan pada sesi perangkat ini selama sesi masih aktif.</p>
</div>

@if ($daftarPesanan->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-stone-200 shadow-sm p-6 mt-3 text-center">
<svg class="w-10 h-10 mx-auto text-stone-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
<p class="font-bold text-sm mt-3">Belum ada pesanan pada sesi ini</p>
<p class="text-stone-500 text-xs mt-1">Silakan pilih menu terlebih dahulu untuk mulai memesan.</p>
<a href="{{ route('emenu.meja', $meja->id) }}" class="inline-block mt-4 bg-forest-900 text-white font-bold rounded-full px-6 py-2.5 text-sm">Lihat Katalog Menu</a>
</div>
@else
<div class="space-y-3 mt-3">
@foreach ($daftarPesanan as $pesanan)
@php
$tahap = $tahapStatus[$pesanan->status_pesanan] ?? 0;
$gaya = match ($pesanan->status_pesanan) {
'diproses' => 'bg-gold-50 text-[#7a5c0f]',
'siap' => 'bg-amber-100 text-amber-700',
'diantar' => 'bg-sky-100 text-sky-700',
'selesai' => 'bg-forest-100 text-forest-900',
'dibatalkan' => 'bg-red-100 text-red-700',
default => 'bg-stone-100 text-stone-600',
};
$jumlahItem = $pesanan->detailPesanan->sum('jumlah');
@endphp
<a href="{{ route('emenu.status', $pesanan->kode_pesanan) }}" class="block bg-white rounded-2xl border border-stone-100 shadow-sm p-4 active:scale-[0.99] transition">
<div class="flex items-start justify-between gap-3">
<div>
<p class="font-mono font-bold text-sm">#{{ $pesanan->kode_pesanan }}</p>
<p class="text-stone-500 text-xs mt-0.5">{{ $pesanan->waktu_pesan->format('d M Y, H.i') }} WIB • {{ $jumlahItem }} item</p>
</div>
<span class="{{ $gaya }} text-[11px] font-bold rounded-full px-3 py-1 whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $pesanan->status_pesanan)) }}</span>
</div>
<div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-3 mt-3">
<div class="flex items-center gap-2">
<span class="w-1.5 h-1.5 rounded-full {{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'bg-forest-600' : 'bg-stone-300' }}"></span>
<p class="text-stone-500 text-xs">{{ $pesanan->status_pembayaran === 'sudah_bayar' ? 'Sudah Dibayar' : 'Menunggu Pembayaran' }}</p>
</div>
<p class="font-extrabold text-forest-600">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
</div>
</a>
@endforeach
</div>
@endif

<a href="{{ route('emenu.meja', $meja->id) }}" class="block text-center w-full mt-4 bg-white border border-forest-900 text-forest-900 font-bold rounded-full py-3">Kembali ke Katalog</a>
</div>
</body>
</html>
