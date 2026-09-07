<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Antrean Indibiz</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #181C20; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #EE2E24; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #00509E; font-size: 16px; }
        .header p { margin: 2px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f1f4f9; font-size: 10px; text-transform: uppercase; }
        .total-box { margin-top: 15px; text-align: right; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN ANTREAN & TRANSAKSI</h2>
        <p>Periode: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Tiket</th>
                <th>Nama Pelanggan</th>
                <th>Layanan</th>
                <th>Waktu Masuk</th>
                <th>Status</th>
                <th>Metode</th>
                <th style="text-align: right;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $index => $t)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $t->nomor_antrian }}</strong></td>
                <td>{{ $t->pelanggan->nama }} ({{ $t->pelanggan->no_hp }})</td>
                <td>{{ $t->layanan->nama_layanan }}</td>
                <td>{{ \Carbon\Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                <td>{{ $t->status }}</td>
                <td>{{ $t->metode_pembayaran ?? 'Tanpa Transaksi' }}</td>
                <td style="text-align: right;">Rp {{ number_format($t->nominal_pembayaran, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        TOTAL OMSET DITERIMA: Rp {{ number_format($totalOmset, 0, ',', '.') }}
    </div>

</body>
</html>