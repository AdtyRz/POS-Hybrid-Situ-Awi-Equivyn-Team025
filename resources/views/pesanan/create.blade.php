<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Buat Pesanan - Kasir Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-3xl mx-auto px-4 py-6">

<h1 class="text-2xl font-bold text-[#0b3b2c]">Buat Pesanan</h1>
<p class="text-sm text-stone-500 mt-1">Pilih meja, isi jumlah menu, lalu kirim ke dapur.</p>

@if ($errors->any())
<div class="mt-4 bg-red-100 text-red-900 rounded-xl px-4 py-3 text-sm space-y-1">
@foreach ($errors->all() as $pesan)
<p>{{ $pesan }}</p>
@endforeach
</div>
@endif

<form method="POST" action="{{ route('kasir.pesanan.store') }}" class="mt-5 space-y-5">
@csrf

<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<label for="meja_id" class="block font-semibold text-sm">Meja</label>
<select name="meja_id" id="meja_id" required
class="mt-2 w-full rounded-xl border-stone-300 border px-3 py-2">
<option value="">-- pilih meja --</option>
@foreach ($meja as $m)
<option value="{{ $m->id }}" @selected(old('meja_id') == $m->id)>{{ $m->kode_meja }} ({{ $m->area }})</option>
@endforeach
</select>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<p class="font-semibold text-sm">Menu</p>
@if ($menu->isEmpty())
<p class="text-sm text-stone-500 mt-2">Tidak ada menu yang stoknya tersedia.</p>
@else
<table class="mt-3 w-full text-sm">
<thead>
<tr class="text-left text-stone-500 border-b">
<th class="py-2">Menu</th>
<th class="py-2">Harga</th>
<th class="py-2 w-28">Jumlah</th>
</tr>
</thead>
<tbody>
@foreach ($menu as $item)
<tr class="border-b last:border-0">
<td class="py-2">{{ $item->nama_menu }} <span class="text-xs text-stone-400">({{ $item->kategori->nama_kategori ?? '-' }})</span></td>
<td class="py-2">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</td>
<td class="py-2">
<input type="number" name="jumlah[{{ $item->id }}]" min="0" max="99" value="{{ old('jumlah.' . $item->id, 0) }}"
class="w-24 rounded-lg border-stone-300 border px-2 py-1">
</td>
</tr>
@endforeach
</tbody>
</table>
@endif
</div>

<div class="flex gap-3">
<button type="submit" class="bg-[#0b3b2c] hover:bg-[#0e4a37] text-white font-semibold rounded-xl px-5 py-2.5">Kirim ke Dapur</button>
<a href="{{ route('kasir.dashboard') }}" class="rounded-xl border border-stone-300 px-5 py-2.5 font-semibold">Batal</a>
</div>
</form>

</div>
</body>
</html>