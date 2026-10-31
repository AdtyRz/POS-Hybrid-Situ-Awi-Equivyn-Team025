<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Saung - Saung Situ Awi</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:Inter,Arial,sans-serif}
        body{background:#fff;padding:24px}
        h1{font-size:18px;margin-bottom:16px;text-align:center}
        .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .card{border:1px solid #cfd8d2;border-radius:12px;padding:16px;text-align:center;break-inside:avoid}
        .kode{font-weight:700;margin-bottom:8px;font-size:14px}
        .qr{width:140px;height:140px;margin:0 auto;display:flex;align-items:center;justify-content:center}
        .qr canvas{width:100%!important;height:100%!important}
        .url{font-size:10px;color:#1f4d2e;word-break:break-all;margin-top:8px}
        .kode4{font-size:16px;font-weight:700;letter-spacing:4px;margin-top:6px;color:#00341A}
        @media print{body{padding:12px}.grid{gap:12px}}
    </style>
    @vite('resources/js/qr.js')
</head>
<body>
    <h1>Cetak Semua QR Saung - Saung Situ Awi</h1>
    <div class="grid">
        @foreach($meja as $m)
        <div class="card">
            <div class="kode">{{ $m->kode_meja }}</div>
            <div class="qr">
                <canvas data-qr="{{ $m->url_meja }}" width="140" height="140"></canvas>
            </div>
            <div class="url">{{ $m->url_meja }}</div>
            <div class="kode4">Kode masuk: {{ $m->kode_akses }}</div>
        </div>
        @endforeach
    </div>
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
</body>
</html>
