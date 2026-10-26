<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap {{ $namaShift }} - {{ $tanggalShift }} - {{ $namaResto }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 12mm; }
        html, body { margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1C1C18; background: #fff; }
        .sheet { max-width: 186mm; margin: 0 auto; padding: 8mm; }
        .toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 14px; }
        .toolbar button { border: 0; border-radius: 7px; padding: 8px 14px; font-size: 11px; font-weight: 700; cursor: pointer; }
        .toolbar .print { background: #0B4D2B; color: #fff; }
        .toolbar .close { background: #E5E2DB; color: #1C1C18; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #0B4D2B; padding-bottom: 12px; }
        .brand h1 { font-size: 20px; margin: 0; letter-spacing: -0.02em; color: #00341A; }
        .brand p { margin: 4px 0 0; color: #707971; line-height: 1.5; }
        .title { text-align: right; }
        .title h2 { font-size: 17px; margin: 0; color: #0B4D2B; text-transform: uppercase; letter-spacing: 0.04em; }
        .title p { margin: 5px 0 0; color: #404941; line-height: 1.6; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 12px; padding: 9px 11px; background: #F6F3EC; border: 1px solid #D8D3C7; border-radius: 6px; }
        .meta span { color: #404941; }
        .meta strong { color: #1C1C18; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 14px; }
        .card { border: 1px solid #D8D3C7; border-radius: 7px; padding: 9px 10px; }
        .label { font-size: 8px; text-transform: uppercase; color: #707971; font-weight: bold; letter-spacing: 0.05em; }
        .value { font-size: 14px; font-weight: bold; margin-top: 5px; color: #00341A; }
        .subvalue { font-size: 9px; color: #707971; margin-top: 3px; }
        h3 { font-size: 11px; margin: 18px 0 7px; text-transform: uppercase; letter-spacing: 0.05em; color: #00341A; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #E5E2DB; padding: 6px 5px; text-align: left; vertical-align: top; }
        th { background: #00341A; color: #fff; font-size: 8px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 0; }
        td { font-size: 9px; }
        tbody tr:nth-child(even) { background: #FBF9F4; }
        .right { text-align: right; white-space: nowrap; }
        .muted { color: #707971; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .badge.lunas { background: #DFF5E4; color: #0B4D2B; }
        .badge.pending { background: #FEF0C7; color: #7A5900; }
        .badge.void { background: #FEE4E2; color: #B42318; }
        .footer { display: flex; justify-content: space-between; gap: 24px; margin-top: 22px; padding-top: 12px; border-top: 1px solid #D8D3C7; }
        .footer p { margin: 0 0 4px; color: #404941; line-height: 1.5; }
        .signature { text-align: center; min-width: 110px; }
        .signature-space { height: 48px; }
        .empty { padding: 18px; text-align: center; color: #707971; border: 1px dashed #C0C9BF; border-radius: 6px; }
        @media print {
            body { font-size: 9px; }
            .sheet { max-width: none; padding: 0; }
            .toolbar { display: none; }
            .card, .meta { break-inside: avoid; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="toolbar">
            <button type="button" class="print" onclick="window.print()">Cetak Sekarang</button>
            <button type="button" class="close" onclick="window.close()">Tutup</button>
        </div>

        <div class="header">
            <div class="brand">
                <h1>{{ $namaResto }}</h1>
                <p>{{ $alamatResto }}<br>Telp: {{ $teleponResto }}</p>
            </div>
            <div class="title">
                <h2>Rekap {{ $namaShift }}</h2>
                <p>{{ $tanggalShift }}<br>{{ $jamShift }}</p>
            </div>
        </div>

        <div class="meta">
            <span>Area: <strong>{{ $labelArea }}</strong></span>
            <span>Transaksi: <strong>{{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }}</strong></span>
            <span>Lunas: <strong>{{ number_format($ringkasan['jumlah_lunas'], 0, ',', '.') }}</strong></span>
            <span>Dicetak: <strong>{{ $dicetakPada->format('d/m/Y H:i') }} WIB</strong></span>
            @if ($cari !== '')
                <span>Pencarian: <strong>{{ $cari }}</strong></span>
            @endif
        </div>

        <div class="grid">
            <div class="card">
                <div class="label">Omzet Lunas</div>
                <div class="value">Rp {{ number_format($ringkasan['omzet'], 0, ',', '.') }}</div>
                <div class="subvalue">{{ $ringkasan['persen_lunas'] }}% dari transaksi</div>
            </div>
            <div class="card">
                <div class="label">QRIS Midtrans</div>
                <div class="value">Rp {{ number_format($ringkasan['omzet_qris'], 0, ',', '.') }}</div>
                <div class="subvalue">{{ number_format($ringkasan['jumlah_qris'], 0, ',', '.') }} transaksi</div>
            </div>
            <div class="card">
                <div class="label">Tunai Kasir</div>
                <div class="value">Rp {{ number_format($ringkasan['omzet_tunai'], 0, ',', '.') }}</div>
                <div class="subvalue">{{ number_format($ringkasan['jumlah_tunai'], 0, ',', '.') }} transaksi</div>
            </div>
            <div class="card">
                <div class="label">Belum Lunas / Void</div>
                <div class="value">{{ number_format($ringkasan['belum_lunas'], 0, ',', '.') }} / {{ number_format($ringkasan['void'], 0, ',', '.') }}</div>
                <div class="subvalue">Rata-rata lunas: Rp {{ number_format($ringkasan['rata_rata'], 0, ',', '.') }}</div>
            </div>
        </div>

        <h3>Rincian Transaksi Shift</h3>

        @if ($transaksi->isEmpty())
            <div class="empty">Belum ada transaksi pada {{ $namaShift }} ({{ $jamShift }}) untuk filter ini.</div>
        @else
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
                    @foreach ($transaksi as $pesanan)
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
                                $pesanan->status_pembayaran === \App\Models\Pesanan::PAYMENT_PENDING_PEMBAYARAN => $pesanan->metode_pembayaran === 'tunai' ? 'Menunggu Tunai' : 'Menunggu QRIS',
                                default => 'Belum Bayar',
                            };
                            $namaItem = $pesanan->detailPesanan->take(2)->map(fn ($detail) => $detail->menu?->nama_menu ?? 'Menu')->implode(', ');
                            $jumlahItem = (int) ($pesanan->total_item ?? $pesanan->detailPesanan->sum('jumlah'));
                            $statusClass = $dibatalkan ? 'void' : ($lunas ? 'lunas' : 'pending');
                        @endphp
                        <tr>
                            <td>{{ optional($pesanan->waktu_pesan)->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>#{{ $pesanan->kode_pesanan }}</td>
                            <td>
                                {{ $pesanan->meja?->kode_meja ?? '-' }}<br>
                                <span class="muted">{{ $pesanan->meja?->area ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $namaItem ?: '-' }}<br>
                                <span class="muted">{{ $jumlahItem }} item</span>
                            </td>
                            <td>{{ $labelMetode }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $labelStatus }}</span></td>
                            <td>{{ $pesanan->kasir?->nama ?? '-' }}</td>
                            <td class="right">Rp {{ number_format((float) $pesanan->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="right"><strong>Total Omzet Lunas</strong></td>
                        <td class="right"><strong>Rp {{ number_format($ringkasan['omzet'], 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="footer">
            <div>
                <p>Rekap QRIS berasal dari status pembayaran Midtrans. Rekap tunai berasal dari pencatatan kasir.</p>
                <p>Transaksi yang belum lunas atau void tetap ditampilkan untuk kebutuhan rekonsiliasi.</p>
                <p>Dicetak oleh {{ $dicetakOleh }} melalui Sistem Saung Situ Awi.</p>
            </div>
            <div class="signature">
                <p>Disiapkan oleh,</p>
                <div class="signature-space"></div>
                <p><strong>{{ $dicetakOleh }}</strong></p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
