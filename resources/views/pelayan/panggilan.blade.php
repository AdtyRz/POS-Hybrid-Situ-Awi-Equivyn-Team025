<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Antrean Pelayan</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-[#FBF8F1] p-4">
<div class="max-w-3xl mx-auto">
<header class="flex items-center justify-between mb-6">
<div class="flex items-center gap-3">
<a href="#" class="flex items-center gap-2 text-[#6B6A63] hover:text-[#2B2A26] text-sm font-medium" onclick="history.back()">
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

<h1 class="font-display font-bold text-2xl text-[#052E1B] mb-2">Antrean Pelayan</h1>
<p class="text-stone-500 text-sm mb-6">Daftar panggilan meja yang menunggu respons</p>

@if ($panggilan->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-stone-200 shadow-sm p-8 text-center">
<svg class="w-16 h-16 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
<p class="font-bold text-lg text-stone-600">Tidak ada panggilan menunggu</p>
<p class="text-stone-400 text-sm mt-1">Semua panggilan sudah ditangani</p>
</div>
@else
<div class="space-y-3">
@foreach ($panggilan as $p)
<div class="bg-white rounded-2xl border border-stone-100 shadow-sm p-4">
<div class="flex items-start justify-between gap-4">
<div class="flex-1">
<p class="font-bold text-lg text-forest-900">Meja {{ $p->meja->kode_meja }} ({{ $p->meja->area }})</p>
<p class="text-stone-500 text-sm mt-1">Dipanggil: {{ $p->waktu_panggil->format('H:i:s') }} WIB</p>
<p class="text-stone-400 text-xs mt-0.5">Waktu tunggu: {{ $p->waktu_panggil->diffForHumans() }}</p>
</div>
<div class="flex items-center gap-2 shrink-0">
<form method="POST" action="{{ route('pelayan.panggilan.ubah', $p->id) }}">
@csrf
@method('PATCH')
<select name="status_panggilan" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-stone-200 focus:border-forest-500 focus:ring-2 focus:ring-forest-500/20 outline-none text-sm font-medium bg-white">
<option value="ditangani" {{ $p->status_panggilan == 'ditangani' ? 'selected' : '' }}>Ditangani</option>
<option value="selesai" {{ $p->status_panggilan == 'selesai' ? 'selected' : '' }}>Selesai</option>
</select>
</form>
</div>
</div>
</div>
@endforeach
</div>
@endif

<div class="mt-6 p-4 bg-white rounded-2xl border border-stone-100 shadow-sm">
<h2 class="font-bold text-forest-900 mb-3">Pesanan Siap Antar</h2>
@if ($siapAntar->isEmpty())
<p class="text-stone-400 text-sm text-center py-4">Tidak ada pesanan siap diantar</p>
@else
<div class="space-y-2">
@foreach ($siapAntar as $p)
<div class="flex items-center justify-between p-3 rounded-xl bg-forest-50 border border-forest-100">
<div>
<p class="font-bold text-forest-900">{{ $p->kode_pesanan }}</p>
<p class="text-stone-500 text-xs">Meja {{ $p->meja->kode_meja }} • {{ $p->detailPesanan->sum('jumlah') }} item</p>
</div>
<form method="POST" action="{{ route('pelayan.panggilan.ubah', $p->id) }}" class="shrink-0">
@csrf
@method('PATCH')
<input type="hidden" name="status_panggilan" value="selesai">
<button type="submit" class="px-3 py-1.5 rounded-lg bg-forest-600 hover:bg-forest-700 text-white text-xs font-bold transition">Selesai Antar</button>
</form>
</div>
@endforeach
</div>
@endif
</div>

<p class="text-center text-stone-400 text-xs mt-8">© 2026 Saung Situ Awi Ciwidey</p>
</div>
</body>
</html>