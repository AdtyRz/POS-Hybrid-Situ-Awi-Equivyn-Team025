<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pembayaran Pesanan</title>
</head>
<body>
<h1>Pembayaran {{ $pesanan->kode_pesanan }}</h1>
<a href="{{ route('kasir.dashboard') }}">Kembali ke Kasir</a>
<p>Meja {{ $pesanan->meja->kode_meja }} ({{ $pesanan->meja->area }})</p>
<h2>Rincian</h2>
@foreach ($pesanan->detailPesanan as $d)
<p>{{ $d->jumlah }} x {{ $d->menu->nama_menu }} Rp {{ number_format($d->subtotal, 0, ',', '.') }}</p>
@endforeach
<p>Total Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
<p>Kembalian Rp {{ number_format($kembalian, 0, ',', '.') }}</p>
<form method="POST" action="{{ route('kasir.pembayaran.store') }}">
@csrf
<input type="hidden" name="kode_pesanan" value="{{ $pesanan->kode_pesanan }}">
<div>
<label>Metode Pembayaran</label>
<select name="metode_pembayaran">
<option value="tunai">Tunai</option>
<option value="qris">QRIS</option>
</select>
</div>
<div>
<label>Jumlah Bayar</label>
<input type="number" name="jumlah_bayar" min="0" step="500" value="{{ old('jumlah_bayar') }}" required>
</div>
<button type="submit">Simpan Pembayaran</button>
</form>
</body>
</html>
