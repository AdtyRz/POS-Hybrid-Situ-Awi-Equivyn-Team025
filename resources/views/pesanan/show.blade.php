<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Pesanan</title>
</head>
<body>
<h1>Pesanan {{ $pesanan->kode_pesanan }}</h1>
<p>Meja {{ $pesanan->meja->kode_meja }} Status {{ $pesanan->status_pesanan }} Bayar {{ $pesanan->status_pembayaran }}</p>
@foreach ($pesanan->detailPesanan as $d)
<p>{{ $d->jumlah }} x {{ $d->menu->nama_menu }} Rp {{ number_format($d->subtotal, 0, ',', '.') }}</p>
@endforeach
<p>Total Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</p>
<form method="POST" action="{{ route('pesanan.status', $pesanan->kode_pesanan) }}">
@csrf
@method('PATCH')
<label>Ubah Status</label>
<select name="status_pesanan">
<option value="diproses">Diproses</option>
<option value="siap">Siap</option>
<option value="diantar">Diantar</option>
<option value="selesai">Selesai</option>
<option value="dibatalkan">Dibatalkan</option>
</select>
<button type="submit">Simpan Status</button>
</form>
</body>
</html>
