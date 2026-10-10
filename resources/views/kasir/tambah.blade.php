<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Pesanan Manual</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#FBF8F1] p-4">
<div class="max-w-2xl mx-auto">
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

<h1 class="font-display font-bold text-2xl text-[#052E1B] mb-2">Tambah Pesanan Manual</h1>
<p class="text-stone-500 text-sm mb-6">Pilih meja dan menu untuk pesanan kasir</p>

<form method="POST" action="{{ route('kasir.pesanan.store') }}">
@csrf

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 mb-4">
<h2 class="font-bold text-forest-900 mb-4">Informasi Pesanan</h2>
<div class="grid gap-4 md:grid-cols-2 mb-4">
<div>
<label class="block text-sm font-medium text-stone-600 mb-1">Meja <span class="text-red-500">*</span></label>
<select name="meja_id" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none">
<option value="">Pilih Meja</option>
@foreach ($meja as $m)
<option value="{{ $m->id }}" {{ old('meja_id') == $m->id ? 'selected' : '' }}>{{ $m->kode_meja }} ({{ $m->area }}) - Kap. {{ $m->kapasitas }}</option>
@endforeach
</select>
</div>
<div>
<label class="block text-sm font-medium text-stone-600 mb-1">Metode Pembayaran</label>
<select name="metode_pembayaran" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none">
<option value="tunai" {{ old('metode_pembayaran') == 'tunai' ? 'selected' : '' }}>Tunai</option>
<option value="qris" {{ old('metode_pembayaran') == 'qris' ? 'selected' : '' }}>QRIS</option>
</select>
</div>
</div>
<div>
<label class="block text-sm font-medium text-stone-600 mb-1">Catatan</label>
<input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none" placeholder="Catatan khusus (opsional)">
</div>
</div>

<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-5 mb-4">
<h2 class="font-bold text-forest-900 mb-4">Daftar Menu</h2>
<div class="space-y-2">
@foreach ($menu as $m)
<div class="flex items-center justify-between p-3 rounded-xl bg-cream-50 border border-stone-100">
<div class="flex-1 min-w-0">
<p class="font-bold text-sm text-forest-900 truncate">{{ $m->nama_menu }}</p>
<div class="flex items-center gap-2 text-xs text-stone-500 mt-0.5">
<span>Rp{{ number_format($m->harga, 0, ',', '.') }}</span>
<span class="px-2 py-0.5 rounded bg-forest-100 text-forest-700">Stok: {{ $m->stok_menu }}</span>
</div>
</div>
<input type="number" name="jumlah[{{ $m->id }}]" value="0" min="0" max="99" class="w-20 px-3 py-2 rounded-lg border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-center font-mono" placeholder="0">
</div>
@endforeach
</div>
</div>

<button type="submit" class="w-full bg-forest-900 hover:bg-[#042616] text-white font-bold py-4 rounded-xl text-lg shadow-sm transition">
Simpan Pesanan
</button>
</form>

<p class="text-center text-stone-400 text-xs mt-6">© 2026 Saung Situ Awi Ciwidey</p>
</div>
</body>
</html>