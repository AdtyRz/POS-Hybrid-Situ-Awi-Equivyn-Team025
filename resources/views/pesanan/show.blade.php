<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pesanan->kode_pesanan }} - Saung Situ Awi</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-3xl mx-auto px-4 py-6">

@if (session('sukses'))
<div class="bg-emerald-100 text-emerald-900 rounded-xl px-4 py-3 text-sm">{{ session('sukses') }}</div>
@endif

<div class="mt-4 bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
<div class="flex items-start justify-between flex-wrap gap-3">
<div>
<h1 class="font-mono text-xl font-bold text-[#0b3b2c]">{{ $pesanan->kode_pesanan }}</h1>
<p class="text-sm text-stone-500 mt-1">Meja {{ $pesanan->meja->kode_meja }} &middot; {{ $pesanan->waktu_pesan->format('d/m/Y H:i') }}</p>
</div>
<div class="text-right space-y-1">
<span class="block text-xs font-semibold rounded-full px-2 py-1 bg-stone-100 text-stone-700">{{ $pesanan->status_pesanan }}</span>
<span class="block text-xs font-semibold rounded-full px-2 py-1
@if ($pesanan->status_pembayaran === 'sudah_bayar') bg-emerald-100 text-emerald-800
@else bg-amber-100 text-amber-800 @endif">{{ $pesanan->status_pembayaran }}</span>
</div>
</div>

<h2 class="mt-5 font-semibold">Rincian</h2>
<table class="mt-2 w-full text-sm">
<thead>
<tr class="text-left text-stone-500 border-b">
<th class="py-2">Menu</th>
<th class="py-2">Jumlah</th>
<th class="py-2 text-right">Subtotal</th>
<th class="py-2 text-left">Status</th>
</tr>
</thead>
<tbody>
@foreach ($pesanan->detailPesanan as $d)
<tr class="border-b last:border-0">
<td class="py-2">{{ $d->menu->nama_menu }}
@if ($d->catatan)<span class="block text-xs text-stone-400">Catatan: {{ $d->catatan }}</span>@endif
</td>
<td class="py-2">{{ $d->jumlah }}</td>
<td class="py-2 text-right">Rp {{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
<td class="py-2">{{ $d->status_item }}</td>
</tr>
@endforeach
</tbody>
</table>

<div class="mt-4 text-right">
<p class="text-sm text-stone-500">Total</p>
<p class="text-2xl font-bold text-[#0b3b2c]">Rp {{ number_format((float) $pesanan->total_bayar, 0, ',', '.') }}</p>
</div>
</div>

<div class="mt-4 bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
<h2 class="font-semibold">Pembayaran</h2>
@if ($pesanan->pembayaran)
<p class="text-sm mt-2">{{ strtoupper($pesanan->pembayaran->metode_pembayaran) }} &middot;
<span class="text-emerald-700 font-semibold">{{ $pesanan->pembayaran->status_pembayaran }}</span></p>
<p class="text-sm">Dibayar Rp {{ number_format((float) $pesanan->pembayaran->jumlah_bayar, 0, ',', '.') }},
kembalian Rp {{ number_format((float) $pesanan->pembayaran->kembalian, 0, ',', '.') }}</p>
@else
<p class="text-sm text-stone-500 mt-2">Belum dibayar.</p>
@endif
</div>

<div class="mt-4 flex flex-wrap gap-3">
@if ($pesanan->status_pembayaran === 'belum_bayar')
<a href="{{ route('kasir.pembayaran.create', $pesanan->kode_pesanan) }}" class="bg-[#0b3b2c] hover:bg-[#0e4a37] text-white font-semibold rounded-xl px-4 py-2">Bayar Sekarang</a>
@endif

@if (in_array($pesanan->status_pesanan, ['siap', 'diantar'], true))
<form method="POST" action="{{ route('pesanan.status', $pesanan->kode_pesanan) }}">
@csrf
@method('PATCH')
<input type="hidden" name="status_pesanan" value="diantar">
<button type="submit" class="rounded-xl border border-stone-300 px-4 py-2 font-semibold">Tandai Diantar</button>
</form>
@endif

<a href="{{ route('kasir.dashboard') }}" class="rounded-xl border border-stone-300 px-4 py-2 font-semibold">Kembali</a>
</div>

</div>
</body>
</html>