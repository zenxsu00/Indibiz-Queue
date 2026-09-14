<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Antrean & Analitik Indibiz</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #181C20; margin: 15px; }
        .header { text-align: center; border-bottom: 2.5px solid #EE2E24; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; color: #00509E; font-size: 15px; text-transform: uppercase; }
        .header p { margin: 2px 0; color: #555; font-weight: bold; }
        
        .section-title { font-size: 11px; font-weight: bold; color: #00509E; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .summary-box { background-color: #f8f9fa; border: 1px solid #e0e3e8; border-left: 4px solid #00509E; padding: 8px; margin-bottom: 12px; border-radius: 4px; }
        .summary-box p { margin: 0; font-size: 10.5px; line-height: 1.4; }
        
        .chart-grid { width: 100%; margin-bottom: 15px; }
        .chart-grid td { width: 50%; vertical-align: top; text-align: center; padding: 4px; }
        .chart-img { width: 100%; max-height: 200px; object-fit: contain; border: 1px solid #eee; border-radius: 4px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data-table th, table.data-table td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        table.data-table th { background-color: #f1f4f9; font-size: 9px; text-transform: uppercase; }
        .sub-text { font-size: 8.5px; color: #666; font-style: italic; }
        .total-box { margin-top: 12px; text-align: right; font-size: 11px; font-weight: bold; color: #00509E; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN EKSEKUTIF ANTREAN</h2>
        <p>Periode Tanggal: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    @if($includeSummary)
        <div class="summary-box">
            <strong>RINGKASAN EKSEKUTIF & INSIGHT OTOMATIS:</strong>
            <p>
                Dokumen ini menampilkan rekapitulasi operasional antrean layanan Indibiz. Pada periode ini, total antrean tercatat sebanyak <strong>{{ number_format($tickets->count()) }} tiket</strong> dengan total omset diterima sebesar <strong>Rp {{ number_format($totalOmset, 0, ',', '.') }}</strong>.
            </p>
        </div>
    @endif

    @if($includeCharts && ($chartLineBase64 || $chartPieBase64))
        <div class="section-title">VISUALISASI GRAFIK ANALITIK</div>
        <table class="chart-grid">
            <tr>
                @if($chartLineBase64)
                    <td>
                        <strong style="font-size: 9px; display: block; margin-bottom: 3px;">Grafik Tren Antrean Masuk vs Selesai</strong>
                        <img src="{{ $chartLineBase64 }}" class="chart-img" alt="Line Chart">
                    </td>
                @endif
                @if($chartPieBase64)
                    <td>
                        <strong style="font-size: 9px; display: block; margin-bottom: 3px;">Proporsi Kepadatan Kategori Layanan</strong>
                        <img src="{{ $chartPieBase64 }}" class="chart-img" alt="Pie Chart">
                    </td>
                @endif
            </tr>
        </table>
    @endif

    @if($includeTable)
        <div class="section-title">RINCIAN DATA TRANSAKSI TIKET</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>Nomor Tiket</th>
                    <th>Nama Pelanggan</th>
                    <th>Kategori & Sub-Layanan</th>
                    <th>Waktu Masuk</th>
                    <th>Status</th>
                    <th>Metode</th>
                    <th style="text-align: right;">Nominal</th>
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
                    <td colspan="8" style="text-align: center; padding: 10px; color: #888;">Tidak ada data antrean pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="total-box">
            TOTAL REKAPITULASI OMSET: Rp {{ number_format($totalOmset, 0, ',', '.') }}
        </div>
    @endif

</body>
</html>