<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Antrean Pelayan</title>
</head>
<body>
<h1>Panggilan Meja</h1>
@foreach ($panggilan as $p)
<div>
<p>Meja {{ $p->meja->kode_meja }} {{ $p->waktu_panggil->format('H:i') }}</p>
<form method="POST" action="{{ route('pelayan.panggilan.ubah', $p->id) }}">
@csrf
@method('PATCH')
<select name="status_panggilan">
<option value="ditangani">Ditangani</option>
<option value="selesai">Selesai</option>
</select>
<button type="submit">Simpan</button>
</form>
</div>
@endforeach
<h1>Pesanan Siap Antar</h1>
@foreach ($siapAntar as $p)
<p>{{ $p->kode_pesanan }} Meja {{ $p->meja->kode_meja }}</p>
@endforeach
</body>
</html>
