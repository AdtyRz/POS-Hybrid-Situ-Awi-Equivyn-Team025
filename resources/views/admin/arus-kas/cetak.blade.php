<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas - {{ $labelPeriode }} - {{ $namaResto }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 12mm; }
        html, body { margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1C1C18; background: #fff; }
        .sheet { max-width: 186mm; margin: 0 auto; padding: 8mm; }
        .toolbar { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 14px; }
        .toolbar button, .toolbar a { border: 0; border-radius: 7px; padding: 8px 14px; font-size: 11px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
        .toolbar .print { background: #0B4D2B; color: #fff; }
        .toolbar .close { background: #E5E2DB; color: #1C1C18; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #0B4D2B; padding-bottom: 12px; }
        .brand h1 { font-size: 20px; margin: 0; letter-spacing: -0.02em; color: #00341A; }
        .brand p { margin: 4px 0 0; color: #707971; line-height: 1.5; }
        .title { text-align: right; }
        .title h2 { font-size: 17px; margin: 0; color: #0B4D2B; letter-spacing: -0.01em; }
        .title p { margin: 4px 0 0; color: #707971; line-height: 1.5; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 14px; }
        .card { border: 1px solid #D8D3C7; border-radius: 8px; padding: 9px 10px; }
        .card.primary { background: #0B4D2B; border-color: #0B4D2B; color: #fff; }
        .card.primary .label { color: #B1F1C2; }
        .card.primary .value { color: #fff; }
        .label { font-size: 8px; text-transform: uppercase; color: #707971; font-weight: bold; letter-spacing: 0.05em; }
        .value { font-size: 13px; font-weight: bold; margin-top: 5px; color: #00341A; }
        .value.negative { color: #93000A; }
        .note { font-size: 8.5px; color: #707971; margin-top: 4px; line-height: 1.4; }
        h3 { font-size: 11px; margin: 18px 0 7px; text-transform: uppercase; letter-spacing: 0.05em; color: #0B4D2B; border-bottom: 1px solid #D8D3C7; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #F0EEE7; color: #404941; font-size: 8px; text-transform: uppercase; letter-spacing: 0.04em; text-align: left; padding: 6px 7px; border: 1px solid #D8D3C7; }
        td { padding: 6px 7px; border: 1px solid #E5E2DB; font-size: 9px; vertical-align: top; }
        td.num, th.num { text-align: right; white-space: nowrap; }
        tfoot td { background: #F6F3EC; font-weight: bold; }
        .empty { text-align: center; color: #707971; padding: 14px; }
        .summary-line { display: flex; justify-content: flex-end; gap: 26px; margin-top: 10px; font-size: 9px; color: #404941; }
        .summary-line strong { color: #00341A; }
        .signature { display: flex; justify-content: space-between; margin-top: 34px; gap: 24px; }
        .signature div { width: 46%; text-align: center; color: #404941; }
        .signature .line { margin-top: 46px; border-top: 1px solid #707971; padding-top: 5px; font-weight: bold; }
        .method-table { width: auto; min-width: 60%; }
        .method-table td { border: 0; border-bottom: 1px solid #F0EEE7; }
        .method-table td.num { font-weight: bold; }
        @media print {
            .toolbar { display: none; }
            .sheet { padding: 0; max-width: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="toolbar">
        <button type="button" class="print" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('admin.arus-kas', ['start' => $mulaiQuery, 'end' => $selesaiQuery]) }}" class="close">Kembali</a>
    </div>

    <div class="header">
        <div class="brand">
            <h1>{{ $namaResto }}</h1>
            <p>
                {{ $alamatResto ?: 'Saung Lesehan Situ Awi' }}<br>
                @if ($teleponResto)
                    Telp. {{ $teleponResto }}
                @endif
            </p>
        </div>
        <div class="title">
            <h2>LAPORAN ARUS KAS</h2>
            <p>
                Periode: <strong>{{ $labelPeriode }}</strong><br>
                Dicetak: {{ $dicetakPada->format('d/m/Y H:i') }} WIB<br>
                Oleh: {{ $dicetakOleh }}
            </p>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <div class="label">Pemasukan Kas</div>
            <div class="value">Rp {{ number_format($ringkasan['pemasukan'], 0, ',', '.') }}</div>
            <div class="note">{{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }} transaksi lunas</div>
        </div>
        <div class="card">
            <div class="label">Pembelian Bahan</div>
            <div class="value">Rp {{ number_format($ringkasan['pembelian'], 0, ',', '.') }}</div>
            <div class="note">{{ number_format($ringkasan['jumlah_pembelian'], 0, ',', '.') }} catatan pembelian</div>
        </div>
        <div class="card">
            <div class="label">Pengeluaran Operasional</div>
            <div class="value">Rp {{ number_format($ringkasan['pengeluaran'], 0, ',', '.') }}</div>
            <div class="note">{{ number_format($ringkasan['jumlah_pengeluaran'], 0, ',', '.') }} catatan pengeluaran</div>
        </div>
        <div class="card primary">
            <div class="label">Arus Kas Bersih</div>
            <div class="value">Rp {{ number_format($ringkasan['arus_kas'], 0, ',', '.') }}</div>
            <div class="note">Pemasukan − Rp {{ number_format($ringkasan['total_keluar'], 0, ',', '.') }}</div>
        </div>
    </div>

    <h3>Rincian Pemasukan per Metode</h3>
    <table class="method-table">
        <thead>
            <tr>
                <th>Metode</th>
                <th class="num">Transaksi</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['tunai' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer'] as $kode => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="num">{{ number_format($ringkasan['metode'][$kode]['transaksi'], 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($ringkasan['metode'][$kode]['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Pemasukan</td>
                <td class="num">{{ number_format($ringkasan['jumlah_transaksi'], 0, ',', '.') }}</td>
                <td class="num">Rp {{ number_format($ringkasan['pemasukan'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Pembelian Bahan</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 58px;">Tanggal</th>
                <th style="width: 110px;">Supplier</th>
                <th>Keterangan</th>
                <th style="width: 90px;">Dicatat Oleh</th>
                <th class="num" style="width: 92px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pembelian as $baris)
                <tr>
                    <td>{{ $baris->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $baris->supplier ?: '-' }}</td>
                    <td>{{ $baris->keterangan ?: '-' }}</td>
                    <td>{{ $baris->pencatat?->nama ?? '-' }}</td>
                    <td class="num">Rp {{ number_format((float) $baris->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Belum ada pembelian bahan pada periode ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total Pembelian Bahan</td>
                <td class="num">Rp {{ number_format($ringkasan['pembelian'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Pengeluaran Operasional</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 58px;">Tanggal</th>
                <th style="width: 100px;">Kategori</th>
                <th>Keterangan</th>
                <th style="width: 90px;">Dicatat Oleh</th>
                <th class="num" style="width: 92px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengeluaran as $baris)
                <tr>
                    <td>{{ $baris->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $baris->labelKategori() }}</td>
                    <td>{{ $baris->keterangan }}</td>
                    <td>{{ $baris->pencatat?->nama ?? '-' }}</td>
                    <td class="num">Rp {{ number_format((float) $baris->jumlah, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">Belum ada pengeluaran operasional pada periode ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Total Pengeluaran Operasional</td>
                <td class="num">Rp {{ number_format($ringkasan['pengeluaran'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-line">
        <span>Pemasukan: <strong>Rp {{ number_format($ringkasan['pemasukan'], 0, ',', '.') }}</strong></span>
        <span>Kas Keluar: <strong>Rp {{ number_format($ringkasan['total_keluar'], 0, ',', '.') }}</strong></span>
        <span>Arus Kas Bersih: <strong>Rp {{ number_format($ringkasan['arus_kas'], 0, ',', '.') }}</strong></span>
    </div>

    <div class="signature">
        <div>
            <div>Dibuat oleh,</div>
            <div class="line">{{ $dicetakOleh }}</div>
        </div>
        <div>
            <div>Disetujui oleh,</div>
            <div class="line">Pemilik / Pengelola</div>
        </div>
    </div>
</div>
</body>
</html>
