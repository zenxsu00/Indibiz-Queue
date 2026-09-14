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
        .sub-text { font-size: 9px; color: #666; font-style: italic; }
        .total-box { margin-top: 15px; text-align: right; font-size: 12px; font-weight: bold; }
        .summary-box { background: #f8f9fa; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .chart-container { text-align: center; margin: 15px 0; }
        .chart-img { max-width: 45%; height: auto; display: inline-block; margin: 0 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN EKSEKUTIF ANTREAN</h2>
        <p>Periode Tanggal: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    @if(!empty($includeSummary))
    <div class="summary-box">
        <strong>RINGKASAN EKSEKUTIF & INSIGHT OTOMATIS:</strong><br>
        Dokumen ini menampilkan rekapitulasi operasional antrean layanan Indibiz. Pada periode ini, total antrean tercatat sebanyak <strong>{{ number_format($tickets->count()) }} tiket</strong> dengan total omset diterima sebesar <strong>Rp {{ number_format($totalOmset, 0, ',', '.') }}</strong>.
    </div>
    @endif

    @if(!empty($includeCharts) && (!empty($chartLineBase64) || !empty($chartPieBase64)))
    <h4 style="color: #00509E; margin-bottom: 5px;">VISUALISASI GRAFIK ANALITIK</h4>
    <div class="chart-container">
        @if(!empty($chartLineBase64))
            <img src="{{ $chartLineBase64 }}" class="chart-img" alt="Grafik Tren Antrean">
        @endif
        @if(!empty($chartPieBase64))
            <img src="{{ $chartPieBase64 }}" class="chart-img" alt="Proporsi Kepadatan Layanan">
        @endif
    </div>
    @endif

    @if(!empty($includeTable))
    <h4 style="color: #00509E; margin-bottom: 5px;">RINCIAN DATA TRANSAKSI TIKET</h4>
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NOMOR TIKET</th>
                <th>NAMA PELANGGAN</th>
                <th>KATEGORI & SUB-LAYANAN</th>
                <th>WAKTU MASUK</th>
                <th>STATUS</th>
                <th>METODE</th>
                <th style="text-align: right;">NOMINAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $index => $t)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $t->nomor_antrian }}</strong></td>
                <td>{{ $t->pelanggan->nama ?? '-' }} ({{ $t->pelanggan->no_hp ?? '-' }})</td>
                <td>
                    <div>{{ $t->layanan->nama_layanan ?? '-' }}</div>
                    <div class="sub-text">{{ $t->subLayanan->nama_sub_layanan ?? '-' }}</div>
                </td>
                <td>{{ \Carbon\Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                <td>{{ $t->status }}</td>
                <td>{{ $t->metode_pembayaran ?? 'Tanpa Transaksi' }}</td>
                <td style="text-align: right;">Rp {{ number_format($t->nominal_pembayaran, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 15px; color: #888;">
                    Tidak ada data antrean pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <div class="total-box">
        TOTAL REKAPITULASI OMSET: Rp {{ number_format($totalOmset, 0, ',', '.') }}
    </div>

</body>
</html>