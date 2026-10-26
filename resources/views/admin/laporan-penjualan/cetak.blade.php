<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan - {{ $labelPeriode }} - {{ $namaResto }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1C1C18; margin: 24px; }
        h1 { font-size: 18px; margin: 0; letter-spacing: -0.02em; }
        h2 { font-size: 11px; margin: 18px 0 7px; text-transform: uppercase; letter-spacing: 0.05em; }
        .sub { color: #707971; margin-top: 4px; font-size: 10px; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #0B4D2B; padding-bottom: 14px; }
        .header-right { text-align: right; color: #404941; line-height: 1.6; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 14px; }
        .card { border: 1px solid #D8D3C7; border-radius: 8px; padding: 9px 10px; }
        .label { font-size: 8px; text-transform: uppercase; color: #707971; font-weight: bold; letter-spacing: 0.05em; }
        .value { font-size: 13px; font-weight: bold; margin-top: 5px; color: #00341A; }
        .summary { display: flex; gap: 18px; flex-wrap: wrap; margin-top: 4px; font-size: 9px; color: #404941; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 8px; text-transform: uppercase; color: #fff; background: #0B4D2B; padding: 6px 7px; letter-spacing: 0.03em; }
        td { border-top: 1px solid #EEEAE0; padding: 5px 7px; vertical-align: top; }
        tr:nth-child(even) td { background: #F7F4ED; }
        .right { text-align: right; }
        .muted { color: #707971; }
        .badge { font-size: 8px; font-weight: bold; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #D8D3C7; display: flex; justify-content: space-between; gap: 24px; color: #707971; font-size: 9px; }
        .signature { width: 180px; text-align: center; }
        .signature-space { height: 52px; }
        @media print {
            body { margin: 10mm; }
            .no-print { display: none; }
            tr { break-inside: avoid; }
            h2 { break-after: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Laporan Penjualan &amp; Rekonsiliasi</h1>
            <div class="sub">{{ $namaResto }}</div>
            <div class="sub">{{ $alamatResto }}</div>
            <div class="sub">{{ $teleponResto }}</div>
        </div>
        <div class="header-right">
            <strong>Periode:</strong> {{ $labelPeriode }}<br>
            <strong>Area:</strong> {{ $labelArea }}<br>
            <strong>Dicetak:</strong> {{ $dicetakPada->format('d M Y H:i') }} WIB<br>
            <strong>Oleh:</strong> {{ $dicetakOleh }}
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="label">Omzet Lunas</div>
            <div class="value">Rp {{ number_format($ringkasan['omzet'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">QRIS Midtrans</div>
            <div class="value">Rp {{ number_format($ringkasan['omzet_qris'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Tunai Kasir</div>
            <div class="value">Rp {{ number_format($ringkasan['omzet_tunai'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Transaksi Lunas</div>
            <div class="value">{{ number_format($ringkasan['jumlah_lunas'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="summary">
        <span><strong>Total transaksi:</strong> {{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }}</span>
        <span><strong>Belum lunas:</strong> {{ number_format($ringkasan['belum_lunas'], 0, ',', '.') }}</span>
        <span><strong>Dibatalkan:</strong> {{ number_format($ringkasan['void'], 0, ',', '.') }}</span>
        <span><strong>Rata-rata:</strong> Rp {{ number_format($ringkasan['rata_rata'], 0, ',', '.') }}</span>
    </div>

    <h2>Rincian Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Kode</th>
                <th>Meja / Area</th>
                <th>Item</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Kasir</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksi as $pesanan)
                @php
                    $dibatalkan = $pesanan->status_pesanan === \App\Models\Pesanan::STATUS_DIBATALKAN;
                    $lunas = $pesanan->status_pembayaran === \App\Models\Pesanan::PAYMENT_SUDAH_BAYAR && ! $dibatalkan;
                    $qris = $pesanan->metode_pembayaran === 'qris';
                    $labelMetode = $dibatalkan
                        ? 'VOID Kasir'
                        : ($qris ? 'QRIS Midtrans' : ($pesanan->metode_pembayaran === 'tunai' || $lunas ? 'Tunai Kasir' : '-'));
                    $labelStatus = match (true) {
                        $dibatalkan => 'Dibatalkan',
                        $lunas => 'Lunas',
                        $pesanan->status_pembayaran === \App\Models\Pesanan::PAYMENT_PENDING_VERIFIKASI => 'Verifikasi',
                        $pesanan->status_pembayaran === \App\Models\Pesanan::PAYMENT_PENDING_PEMBAYARAN => 'Menunggu QRIS',
                        default => 'Belum Bayar',
                    };
                    $namaItem = $pesanan->detailPesanan->take(2)->map(fn ($detail) => $detail->menu?->nama_menu ?? 'Menu')->implode(', ');
                    $jumlahItem = (int) ($pesanan->total_item ?? $pesanan->detailPesanan->sum('jumlah'));
                @endphp
                <tr>
                    <td>{{ optional($pesanan->waktu_pesan)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                    <td>#{{ $pesanan->kode_pesanan }}</td>
                    <td>{{ $pesanan->meja?->kode_meja ?? '-' }}<br><span class="muted">{{ $pesanan->meja?->area ?? '-' }}</span></td>
                    <td>{{ $namaItem ?: '-' }}<br><span class="muted">{{ $jumlahItem }} item</span></td>
                    <td>{{ $labelMetode }}</td>
                    <td>{{ $labelStatus }}</td>
                    <td>{{ $pesanan->kasir?->nama ?? '-' }}</td>
                    <td class="right">Rp {{ number_format((float) $pesanan->total_bayar, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada transaksi pada periode dan filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>
            <p>Rekap QRIS berasal dari status pembayaran Midtrans. Rekap tunai berasal dari pencatatan kasir.</p>
            <p>Laporan dibuat otomatis oleh Sistem Saung Situ Awi.</p>
        </div>
        <div class="signature">
            <p>Disiapkan oleh,</p>
            <div class="signature-space"></div>
            <p><strong>{{ $dicetakOleh }}</strong></p>
        </div>
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
