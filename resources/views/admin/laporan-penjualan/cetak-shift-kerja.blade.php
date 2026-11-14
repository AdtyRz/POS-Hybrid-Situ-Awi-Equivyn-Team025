<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Shift Kerja - {{ $labelPeriode }} - {{ $namaResto }}</title>
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
        .center { text-align: center; }
        .muted { color: #707971; }
        .badge { display: inline-block; border-radius: 999px; padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .badge.aktif { background: #DFF5E4; color: #0B4D2B; }
        .badge.selesai { background: #F0EEE7; color: #404941; }
        .badge.kedaluwarsa { background: #FEF0C7; color: #7A5900; }
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
                <h2>Laporan Shift Kerja</h2>
                <p>{{ $labelPeriode }}<br>Log Login &amp; Logout Staf</p>
            </div>
        </div>

        <div class="meta">
            <span>Total Sesi: <strong>{{ number_format($ringkasanShift['total'], 0, ',', '.') }}</strong></span>
            <span>Staf Terlibat: <strong>{{ number_format($ringkasanShift['staf'], 0, ',', '.') }}</strong></span>
            <span>Sesi Aktif: <strong>{{ number_format($ringkasanShift['aktif'], 0, ',', '.') }}</strong></span>
            <span>Total Durasi: <strong>{{ \App\Services\ShiftService::durasiTeks($ringkasanShift['durasi']) }}</strong></span>
            <span>Dicetak: <strong>{{ $dicetakPada->format('d/m/Y H:i') }} WIB</strong></span>
            @if ($cari !== '')
                <span>Pencarian: <strong>{{ $cari }}</strong></span>
            @endif
        </div>

        <div class="grid">
            <div class="card">
                <div class="label">Total Sesi Shift</div>
                <div class="value">{{ number_format($ringkasanShift['total'], 0, ',', '.') }}</div>
                <div class="subvalue">Login staf pada periode ini</div>
            </div>
            <div class="card">
                <div class="label">Staf Terlibat</div>
                <div class="value">{{ number_format($ringkasanShift['staf'], 0, ',', '.') }}</div>
                <div class="subvalue">Orang dengan sesi tercatat</div>
            </div>
            <div class="card">
                <div class="label">Sesi Berjalan</div>
                <div class="value">{{ number_format($ringkasanShift['aktif'], 0, ',', '.') }}</div>
                <div class="subvalue">Belum logout saat dicetak</div>
            </div>
            <div class="card">
                <div class="label">Akumulasi Durasi</div>
                <div class="value">{{ \App\Services\ShiftService::durasiTeks($ringkasanShift['durasi']) }}</div>
                <div class="subvalue">Seluruh sesi staf</div>
            </div>
        </div>

        <h3>Rincian Sesi Staf</h3>

        @if ($riwayatShift->isEmpty())
            <div class="empty">Belum ada riwayat shift untuk filter ini.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Staf</th>
                        <th>Peran</th>
                        <th>Shift</th>
                        <th>Jadwal</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Durasi</th>
                        <th class="center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riwayatShift as $baris)
                        @php
                            $aktif = $baris->status === 'aktif' && $baris->selesai_pada === null;
                            $durasi = $aktif
                                ? max(0, (int) $baris->mulai_pada->diffInSeconds(now()))
                                : max(0, (int) $baris->durasi_detik);
                            $statusClass = $aktif ? 'aktif' : ($baris->status === 'kedaluwarsa' ? 'kedaluwarsa' : 'selesai');
                            $statusText = $aktif ? 'Aktif' : ($baris->status === 'kedaluwarsa' ? 'Sesi berakhir' : 'Selesai');
                            $jadwal = $baris->jadwal_mulai && $baris->jadwal_selesai
                                ? substr((string) $baris->jadwal_mulai, 0, 5) . ' - ' . substr((string) $baris->jadwal_selesai, 0, 5) . ' WIB'
                                : '-';
                            $kehadiran = $baris->keterlambatan();
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $baris->user?->nama ?? 'Staf' }}</strong><br>
                                <span class="muted">{{ $baris->ip_address ?: '-' }}</span>
                            </td>
                            <td>{{ \App\Services\ShiftService::ROLES[$baris->role] ?? ucfirst((string) $baris->role) }}</td>
                            <td>
                                {{ $baris->nama_shift ?: 'Luar Shift' }}
                                @if ($kehadiran['kelas'] !== 'luar')
                                    <br><span class="muted">{{ $kehadiran['label'] }}</span>
                                @endif
                            </td>
                            <td>{{ $jadwal }}</td>
                            <td>{{ $baris->mulai_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</td>
                            <td>
                                @if ($baris->selesai_pada)
                                    {{ $baris->selesai_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
                                @else
                                    <span class="muted">Belum logout</span>
                                @endif
                            </td>
                            <td>{{ \App\Services\ShiftService::durasiTeks($durasi) }}</td>
                            <td class="center"><span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="right"><strong>Total Durasi Kerja</strong></td>
                        <td colspan="2"><strong>{{ \App\Services\ShiftService::durasiTeks($ringkasanShift['durasi']) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="footer">
            <div>
                <p>Durasi dihitung dari waktu login sampai logout. Sesi yang tidak logout akan ditutup otomatis sesuai batas sesi.</p>
                <p>Laporan ini mencatat aktivitas shift pelayan, dapur, bar, dan kasir.</p>
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
