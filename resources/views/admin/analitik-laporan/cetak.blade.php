<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Analitik {{ $labelPeriode }} - {{ $start->format('d M Y') }} s/d {{ $end->format('d M Y') }}</title>
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
        .total td { font-weight: bold; background: #F0EEE7; }
        .right { text-align: right; }
    </style>
</head>

<body>
    <h1>Laporan Analitik Saung Situ Awi</h1>
    <div class="sub">Jenis laporan: {{ $labelPeriode }}</div>
    <div class="sub">Periode: {{ $start->format('d M Y') }} &ndash; {{ $end->format('d M Y') }} ({{ $jumlahHari }} hari)</div>

    <div class="grid" style="margin-top:12px;">
        <div class="card">
            <div class="label">Pendapatan Periode</div>
            <div class="value">Rp {{ number_format($pendapatan, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Total Pesanan</div>
            <div class="value">{{ number_format($pesananHariIni, 0, ',', '.') }} Pesanan</div>
        </div>
        <div class="card">
            <div class="label">QRIS (Lunas)</div>
            <div class="value">{{ number_format($qrisHariIni, 0, ',', '.') }} Transaksi</div>
        </div>
        <div class="card">
            <div class="label">Tunai (Lunas)</div>
            <div class="value">{{ number_format($tunaiHariIni, 0, ',', '.') }} Transaksi</div>
        </div>
    </div>

    <h2>Rincian Harian</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="right">Pesanan</th>
                <th class="right">QRIS</th>
                <th class="right">Tunai</th>
                <th class="right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ringkasan as $hari)
            <tr>
                <td>{{ $hari['label_lengkap'] }}</td>
                <td class="right">{{ number_format($hari['jumlah'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($hari['qris'], 0, ',', '.') }}</td>
                <td class="right">{{ number_format($hari['tunai'], 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($hari['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td>Total</td>
                <td class="right">{{ number_format($pesananHariIni, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($qrisHariIni, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($tunaiHariIni, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($pendapatan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h2>5 Menu Terlaris Periode</h2>
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
                <td colspan="4">Belum ada penjualan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Log Transaksi Terbaru</h2>
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
                <td>{{ optional($log->waktu_pesan)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
                <td class="right">Rp {{ number_format($log->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5">Belum ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="sub" style="margin-top:16px; font-size:9px;">
        Dicetak: {{ $hariIni->format('d M Y H:i') }} WIB
    </div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>

</html>
