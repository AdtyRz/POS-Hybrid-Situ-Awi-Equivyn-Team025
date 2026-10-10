<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Session Meja</title>
</head>
<body>
<h1>Session Meja</h1>
<a href="{{ route('kasir.dashboard') }}">Kembali ke Kasir</a>
@foreach ($meja as $m)
<div>
<p>{{ $m->kode_meja }} ({{ $m->area }}) Status {{ $m->status_meja }}</p>
@foreach ($m->pesananAktif as $p)
<p>{{ $p->kode_pesanan }} Rp {{ number_format($p->total_bayar, 0, ',', '.') }} {{ $p->status_pesanan }}</p>
@endforeach
@if ($m->pesananAktif->count() > 0)
<form method="POST" action="{{ route('kasir.session.tutup', $m->kode_meja) }}">
@csrf
@method('PATCH')
<button type="submit">Tutup Session</button>
</form>
@endif
</div>
@endforeach
</body>
</html>
