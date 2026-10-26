<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Analitik - {{ isset($labelPeriode) ? $labelPeriode : $hariIni->format('d M Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1C1C18; }
        h1 { font-size: 16px; margin: 0; }
        h2 { font-size: 12px; margin: 16px 0 8px 0; }
        .sub { color: #707971; margin-top: 4px; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
        .card { border: 1px solid #E5E2DB; border-radius: 10px; padding: 10px; }
        .label { font-size: 9px; text-transform: uppercase; color: #707971; font-weight: bold; }
        .value { font-size: 14px; font-weight: bold; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 9px; text-transform: uppercase; color: #fff; background: #0B4D2B; padding: 6px 8px; }
        td { border-top: 1px solid #F0EEE7; padding: 6px 8px; }
        tr:nth-child(even) td { background: #F6F3EC; }
        .right { text-align: right; }
    </style>
</head>

<body>
    <h1>Laporan Analitik Saung Situ Awi</h1>
    <div class="sub">Periode: {{ isset($labelPeriode) ? $labelPeriode : $hariIni->format('d M Y') }}</div>

    <div class="grid" style="margin-top:12px;">
        <div class="card">
            <div class="label">Pendapatan Hari Ini</div>
            <div class="value">Rp {{ number_format($pendapatan, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Total Pesanan</div>
            <div class="value">{{ $pesananHariIni }} Pesanan</div>
        </div>
        <div class="card">
            <div class="label">QRIS</div>
            <div class="value">{{ $qrisHariIni }} Transaksi</div>
        </div>
        <div class="card">
            <div class="label">Tunai</div>
            <div class="value">{{ $tunaiHariIni }} Transaksi</div>
        </div>
    </div>

    <h2>5 Menu Terlaris Hari Ini</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Menu</th>
                <th class="right">Porsi</th>
                <th class="right">Omzet</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terlaris as $i => $laris)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $laris->menu->nama_menu ?? '-' }}</td>
                <td class="right">{{ $laris->porsi }}</td>
                <td class="right">Rp {{ number_format($laris->omzet, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">Belum ada penjualan hari ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Log Transaksi Terkini</h2>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Meja</th>
                <th>Kasir</th>
                <th>Waktu</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logAudit as $log)
            <tr>
                <td>#{{ $log->kode_pesanan }}</td>
                <td>{{ $log->meja ? $log->meja->area.' ('.$log->meja->kode_meja.')' : '-' }}</td>
                <td>{{ $log->kasir ? $log->kasir->nama : '-' }}</td>
                <td>{{ optional($log->waktu_pesan)->timezone('Asia/Jakarta')->format('H:i') }} WIB</td>
                <td class="right">Rp {{ number_format($log->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">Belum ada transaksi hari ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>

</html>
