<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pembayaran {{ $pesanan->kode_pesanan }} - Kasir</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f5f3ec] text-stone-800">
<div class="max-w-xl mx-auto px-4 py-6">

<h1 class="text-2xl font-bold text-[#0b3b2c]">Pembayaran</h1>
<p class="text-sm text-stone-500 mt-1 font-mono">{{ $pesanan->kode_pesanan }} &middot; Meja {{ $pesanan->meja->kode_meja }}</p>

@if ($errors->any())
<div class="mt-4 bg-red-100 text-red-900 rounded-xl px-4 py-3 text-sm space-y-1">
@foreach ($errors->all() as $pesan)
<p>{{ $pesan }}</p>
@endforeach
</div>
@endif

<div class="mt-5 bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
<p class="text-sm text-stone-500">Total tagihan</p>
<p class="text-3xl font-bold text-[#0b3b2c]">Rp {{ number_format((float) $pesanan->total_bayar, 0, ',', '.') }}</p>

<form method="POST" action="{{ route('kasir.pembayaran.store') }}" class="mt-5 space-y-4">
@csrf
<input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">

<div>
<label for="metode_pembayaran" class="block font-semibold text-sm">Metode</label>
<select name="metode_pembayaran" id="metode_pembayaran" required
class="mt-2 w-full rounded-xl border-stone-300 border px-3 py-2">
<option value="tunai">Tunai</option>
<option value="qris">QRIS</option>
</select>
</div>

<div>
<label for="jumlah_bayar" class="block font-semibold text-sm">Uang diterima</label>
<input type="number" name="jumlah_bayar" id="jumlah_bayar" step="0.01" min="0" required
value="{{ old('jumlah_bayar', number_format((float) $pesanan->total_bayar, 0, '', '.')) }}"
class="mt-2 w-full rounded-xl border-stone-300 border px-3 py-2">
<p class="text-xs text-stone-500 mt-1">Kalau pembayaran tunai, isi lebih besar untuk melihat kembalian.</p>
</div>

<div>
<label for="transaction_id" class="block font-semibold text-sm">Nomor transaksi QRIS</label>
<input type="text" name="transaction_id" id="transaction_id" maxlength="100" value="{{ old('transaction_id') }}"
class="mt-2 w-full rounded-xl border-stone-300 border px-3 py-2">
<p class="text-xs text-stone-500 mt-1">Kosongkan kalau tidak memakai QRIS.</p>
</div>

<div class="flex gap-3">
<button type="submit" class="bg-[#0b3b2c] hover:bg-[#0e4a37] text-white font-semibold rounded-xl px-5 py-2.5">Terima Pembayaran</button>
<a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}" class="rounded-xl border border-stone-300 px-5 py-2.5 font-semibold">Batal</a>
</div>
</form>
</div>

</div>
</body>
</html>