<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pembayaran Pesanan</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#FBF8F1] p-4">
<div class="max-w-md mx-auto">
<header class="flex items-center justify-between mb-6">
<div class="flex items-center gap-3">
<a href="{{ route('kasir.dashboard') }}" class="flex items-center gap-2 text-[#6B6A63] hover:text-[#2B2A26] text-sm font-medium">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
Kembali
</a>
</div>
<form method="POST" action="{{ route('logout') }}" class="hidden md:block">
@csrf
<button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FDF5E6] hover:bg-[#F5ECD8] border border-[#E0B24E]/40 hover:border-[#E0B24E] active:scale-95 transition text-xs font-bold text-[#7A5C0F] whitespace-nowrap">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
<span>Keluar</span>
</button>
</form>
</header>

<h1 class="font-display font-bold text-2xl text-[#052E1B] mb-2">Pembayaran {{ $pesanan->kode_pesanan }}</h1>
<p class="text-stone-500 text-sm mb-6">Meja {{ $pesanan->meja->kode_meja }} ({{ $pesanan->meja->area }})</p>

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-6">
<h2 class="font-bold text-forest-900 mb-4">Rincian Pesanan</h2>
<div class="divide-y divide-stone-100">
@foreach ($pesanan->detailPesanan as $d)
<div class="flex items-center justify-between py-3">
<div>
<p class="font-bold text-sm">{{ $d->jumlah }}× {{ $d->menu->nama_menu }}</p>
@if ($d->catatan)
<p class="text-stone-400 text-xs mt-0.5">{{ $d->catatan }}</p>
@endif
</div>
<p class="font-bold text-sm text-forest-900">Rp{{ number_format($d->subtotal, 0, ',', '.') }}</p>
</div>
@endforeach
</div>
<div class="flex items-center justify-between border-t border-dashed border-stone-200 pt-4 mt-4">
<p class="font-bold text-lg">Total</p>
<p class="font-extrabold text-2xl text-forest-600">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
</div>
</div>

<form method="POST" action="{{ route('kasir.pembayaran.store') }}">
@csrf
<input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-4">
<h2 class="font-bold text-forest-900 mb-4">Metode Pembayaran</h2>
<div class="grid grid-cols-2 gap-3">
<label class="relative cursor-pointer">
<input type="radio" name="metode_pembayaran" value="tunai" class="sr-only peer" required {{ old('metode_pembayaran', 'tunai') == 'tunai' ? 'checked' : '' }}>
<div class="p-4 rounded-xl border-2 {{ old('metode_pembayaran', 'tunai') == 'tunai' ? 'border-forest-500 bg-forest-50' : 'border-stone-200' }} text-center peer-checked:border-forest-500 peer-checked:bg-forest-50 transition">
<svg class="w-8 h-8 mx-auto text-forest-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
<p class="font-bold text-forest-900">Tunai</p>
</div>
</label>
<label class="relative cursor-pointer">
<input type="radio" name="metode_pembayaran" value="qris" class="sr-only peer" {{ old('metode_pembayaran') == 'qris' ? 'checked' : '' }}>
<div class="p-4 rounded-xl border-2 {{ old('metode_pembayaran') == 'qris' ? 'border-gold-500 bg-gold-50' : 'border-stone-200' }} text-center peer-checked:border-gold-500 peer-checked:bg-gold-50 transition">
<svg class="w-8 h-8 mx-auto text-gold-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path stroke-linecap="round" d="M9 9h6M9 15h6"/></svg>
<p class="font-bold text-gold-600">QRIS</p>
</div>
</label>
</div>
</div>

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-6 mb-4">
<h2 class="font-bold text-forest-900 mb-4">Jumlah Bayar</h2>
<div>
<label class="block text-sm font-medium text-stone-600 mb-2">Uang yang dibayar</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">Rp</span>
<input type="number" name="jumlah_bayar" min="0" step="500" value="{{ old('jumlah_bayar', $pesanan->total_bayar) }}" required class="w-full pl-8 pr-4 py-3 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-lg font-mono" placeholder="0">
</div>
@error('jumlah_bayar')
<p class="text-red-600 text-sm mt-1">{{ $message }}</p>
@enderror
</div>
<p class="text-stone-500 text-xs mt-2">Total tagihan: <span class="font-bold text-forest-900">Rp{{ number_format($pesanan->total_bayar, 0, ',', '.') }}</span></p>
</div>

<button type="submit" class="w-full bg-forest-900 hover:bg-[#042616] text-white font-bold py-4 rounded-xl text-lg shadow-sm transition">
Konfirmasi Pembayaran
</button>
</form>

<p class="text-center text-stone-400 text-xs mt-6">© 2026 Saung Situ Awi Ciwidey</p>
</div>
</body>
</html>