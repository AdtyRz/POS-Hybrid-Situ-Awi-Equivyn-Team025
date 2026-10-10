<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tambah Pesanan Manual</title>
</head>
<body>
<h1>Tambah Pesanan Manual</h1>
<a href="{{ route('kasir.dashboard') }}">Kembali ke Kasir</a>
<form method="POST" action="{{ route('kasir.pesanan.store') }}">
@csrf
<div>
<label>Meja</label>
<select name="meja_id" required>
@foreach ($meja as $m)
<option value="{{ $m->id }}">{{ $m->kode_meja }} ({{ $m->area }})</option>
@endforeach
</select>
</div>
<div>
<label>Metode Pembayaran</label>
<select name="metode_pembayaran">
<option value="tunai">Tunai</option>
<option value="qris">QRIS</option>
</select>
</div>
<div>
<label>Catatan</label>
<input type="text" name="catatan" maxlength="255" value="{{ old('catatan') }}">
</div>
<h2>Daftar Menu</h2>
@foreach ($menu as $m)
<div>
<span>{{ $m->nama_menu }} Rp {{ number_format($m->harga, 0, ',', '.') }} Stok {{ $m->stok_menu }}</span>
<input type="number" name="jumlah[{{ $m->id }}]" value="0" min="0" max="99">
</div>
@endforeach
<button type="submit">Simpan Pesanan</button>
</form>
</body>
</html>
