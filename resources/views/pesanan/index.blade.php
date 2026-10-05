<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Pesanan - Kasir Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-5xl mx-auto px-4 py-6">

<div class="flex items-center justify-between flex-wrap gap-3">
<h1 class="text-2xl font-bold text-[#0b3b2c]">Pesanan Berjalan</h1>
<a href="{{ route('kasir.pesanan.create') }}" class="bg-[#0b3b2c] hover:bg-[#0e4a37] text-white font-semibold rounded-xl px-4 py-2">+ Pesanan Baru</a>
</div>

@if (session('sukses'))
<div class="mt-4 bg-emerald-100 text-emerald-900 rounded-xl px-4 py-3 text-sm">{{ session('sukses') }}</div>
@endif

<div class="mt-5 space-y-3">
@forelse ($pesanan as $p)
<div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
<div class="flex items-start justify-between flex-wrap gap-3">
<div>
<p class="font-mono font-bold text-[#0b3b2c]">{{ $p->kode_pesanan }}</p>
<p class="text-sm text-stone-500">Meja {{ $p->meja->kode_meja }} &middot; {{ $p->detailPesanan->count() }} item &middot; {{ $p->waktu_pesan->format('H:i') }}</p>
</div>
<div class="text-right">
<p class="font-bold">Rp {{ number_format((float) $p->total_bayar, 0, ',', '.') }}</p>
<span class="text-xs font-semibold rounded-full px-2 py-1
@if ($p->status_pesanan === 'menunggu') bg-amber-100 text-amber-800
@elseif ($p->status_pesanan === 'diproses') bg-blue-100 text-blue-800
@elseif ($p->status_pesanan === 'siap') bg-indigo-100 text-indigo-800
@elseif ($p->status_pesanan === 'diantar') bg-cyan-100 text-cyan-800
@else bg-emerald-100 text-emerald-800 @endif">{{ $p->status_pesanan }}</span>
</div>
</div>

<div class="mt-3 flex flex-wrap gap-2">
<a href="{{ route('pesanan.show', $p->kode_pesanan) }}" class="text-sm font-semibold text-[#0b3b2c] underline">Detail</a>
@if ($p->status_pembayaran === 'belum_bayar')
<a href="{{ route('kasir.pembayaran.create', $p->kode_pesanan) }}" class="text-sm font-semibold text-[#b0781c] underline">Bayar</a>
@else
<span class="text-sm text-emerald-700 font-semibold">Lunas</span>
@endif
</div>
</div>
@empty
<p class="bg-white rounded-2xl border border-stone-200 p-6 text-center text-stone-500">Belum ada pesanan berjalan.</p>
@endforelse
</div>

</div>
</body>
</html>