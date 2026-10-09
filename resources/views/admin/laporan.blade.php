<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan - Admin Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-5xl mx-auto px-4 py-6">

<h1 class="text-2xl font-bold text-[#0b3b2c]">Laporan Restaurant</h1>
<p class="text-sm text-stone-500 mt-1">Ringkasan penjualan, meja terpakai, dan kondisi stok.</p>

<div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
@foreach ([
    'Total Pesanan' => number_format($laporan['jumlah_pesanan'], 0, ',', '.'),
    'Pesanan Lunas' => number_format($laporan['jumlah_pesanan_lunas'], 0, ',', '.'),
    'Meja Terisi' => number_format($laporan['jumlah_meja_terisi'], 0, ',', '.'),
    'Menu Stok Habis' => number_format($laporan['menu_stok_habis'], 0, ',', '.'),
] as $judul => $nilai)
<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<p class="text-xs text-stone-500 uppercase tracking-wide">{{ $judul }}</p>
<p class="text-2xl font-bold text-[#0b3b2c] mt-1">{{ $nilai }}</p>
</div>
@endforeach
</div>

<div class="mt-3 grid gap-3 sm:grid-cols-2">
<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<p class="text-xs text-stone-500 uppercase tracking-wide">Total Pendapatan</p>
<p class="text-2xl font-bold text-[#0b3b2c] mt-1">Rp {{ number_format($laporan['total_pendapatan'], 0, ',', '.') }}</p>
</div>
<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<p class="text-xs text-stone-500 uppercase tracking-wide">Pendapatan Hari Ini</p>
<p class="text-2xl font-bold text-[#0b3b2c] mt-1">Rp {{ number_format($laporan['pendapatan_hari_ini'], 0, ',', '.') }}</p>
</div>
</div>

<div class="mt-5 grid gap-4 lg:grid-cols-2">
<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<h2 class="font-semibold">Menu Terlaris</h2>
<table class="mt-3 w-full text-sm">
<tbody>
@forelse ($menuTerlaris as $m)
<tr class="border-b last:border-0">
<td class="py-2">{{ $m->nama_menu }}</td>
<td class="py-2 text-right text-stone-500">{{ $m->detail_pesanan_count }}x</td>
</tr>
@empty
<tr><td class="py-2 text-stone-500">Belum ada penjualan.</td></tr>
@endforelse
</tbody>
</table>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<h2 class="font-semibold">Stok Menipis</h2>
<table class="mt-3 w-full text-sm">
<tbody>
@forelse ($stokMenipis as $m)
<tr class="border-b last:border-0">
<td class="py-2">{{ $m->nama_menu }}</td>
<td class="py-2 text-right {{ $m->stok_menu <= 0 ? 'text-red-600 font-semibold' : 'text-stone-500' }}">{{ $m->stok_menu }}</td>
</tr>
@empty
<tr><td class="py-2 text-stone-500">Semua stok aman.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

</div>
</body>
</html>