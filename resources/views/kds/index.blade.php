<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>KDS {{ ucfirst($target) }} - Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-4xl mx-auto px-4 py-6">

<div class="flex items-center justify-between flex-wrap gap-3">
<h1 class="text-2xl font-bold text-[#0b3b2c]">Antrean {{ ucfirst($target) }}</h1>
<a href="{{ $target === 'dapur' ? route('kds.bar') : route('kds.dapur') }}" class="text-sm font-semibold underline text-[#0b3b2c]">
Lihat {{ $target === 'dapur' ? 'Bar' : 'Dapur' }}
</a>
</div>

<p class="text-sm text-stone-500 mt-1">Pesanan di bawah otomatis berubah statusnya saat kamu menekan tombol.</p>

<div class="mt-5 grid gap-3 sm:grid-cols-2">
@forelse ($item as $d)
<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="font-mono font-bold text-sm">{{ $d->pesanan->kode_pesanan }}</p>
<p class="text-sm">Meja {{ $d->pesanan->meja->kode_meja }}</p>
<p class="text-sm text-stone-500">{{ $d->pesanan->waktu_pesan->format('H:i') }}</p>
</div>
<span class="text-xs font-semibold rounded-full px-2 py-1 bg-stone-100 text-stone-700">{{ $d->status_item }}</span>
</div>

<p class="mt-3 font-semibold">{{ $d->menu->nama_menu }} x{{ $d->jumlah }}</p>
@if ($d->catatan)<p class="text-xs text-amber-700 mt-1">Catatan: {{ $d->catatan }}</p>@endif

<div class="mt-3 flex flex-wrap gap-2">
@foreach (['diproses', 'siap', 'diantar'] as $statusBaru)
@if ($statusBaru !== $d->status_item)
<form method="POST" action="{{ route($target === 'dapur' ? 'kds.dapur.ubah' : 'kds.bar.ubah', $d->id) }}">
@csrf
@method('PATCH')
<input type="hidden" name="status_item" value="{{ $statusBaru }}">
<button type="submit" class="text-sm font-semibold rounded-lg border border-stone-300 px-3 py-1.5">{{ $statusBaru }}</button>
</form>
@endif
@endforeach
</div>
</div>
@empty
<p class="bg-white rounded-2xl border border-stone-200 p-6 text-center text-stone-500 sm:col-span-2">Antrean kosong.</p>
@endforelse
</div>

</div>
</body>
</html>