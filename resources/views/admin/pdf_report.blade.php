<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Antrean Indibiz</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #181C20; margin: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #EE2E24; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #00509E; font-size: 16px; font-weight: bold; }
        .header p { margin: 3px 0; color: #555; font-size: 11px; }
        
        .summary-box { background-color: #f8f9fa; border: 1px solid #e0e3e8; border-radius: 6px; padding: 10px; margin-bottom: 15px; }
        .summary-title { font-weight: bold; font-size: 11px; color: #00509E; margin-bottom: 4px; text-transform: uppercase; }
        
        .charts-container { margin-bottom: 15px; page-break-inside: avoid; }
        .chart-box { border: 1px solid #e0e3e8; border-radius: 6px; padding: 12px; margin-bottom: 12px; background: #fff; text-align: center; }
        .chart-title { font-weight: bold; font-size: 11px; color: #181C20; margin-bottom: 8px; text-transform: uppercase; text-align: left; }
        
        .chart-image { max-width: 100%; height: auto; max-height: 250px; display: inline-block; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f1f4f9; font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .sub-text { font-size: 9px; color: #666; font-style: italic; }
        .total-box { margin-top: 15px; text-align: right; font-size: 12px; font-weight: bold; }
        .text-right { text-align: right; }
        
        .badge { font-weight: bold; padding: 2px 6px; border-radius: 4px; font-size: 9px; display: inline-block; }
        .badge-selesai { background-color: #d1fae5; color: #065f46; }
        .badge-batal { background-color: #fee2e2; color: #991b1b; }
        .badge-noshow { background-color: #f3e8ff; color: #6b21a8; }
        .badge-default { background-color: #fef3c7; color: #92400e; }

        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- WADAH FLAG KONFIGURASI DARI CONTROLLER (HTML AMAN UNTUK LINTER) -->
    <div id="pdf-config" 
         data-summary="{{ request('inc_summary', 1) }}" 
         data-charts="{{ request('inc_charts', 1) }}" 
         style="display: none;"></div>

    <!-- HEADER LAPORAN -->
    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN ANTREAN & TRANSAKSI</h2>
        <p>Periode Waktu: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <!-- OPSI 1: WADAH EXECUTIVE SUMMARY -->
    @if(request('inc_summary', 1))
    <div id="pdf-summary-container" class="summary-box" style="display: none;">
        <div class="summary-title">Executive Summary / Analisis Otomatis Operasional</div>
        <div id="pdf-summary-content"></div>
    </div>
    @endif

    <!-- OPSI 2: WADAH GRAFIK ANALITIK IMAGE -->
    @if(request('inc_charts', 1))
    <div id="pdf-charts-container" class="charts-container" style="display: none;">
        <div class="chart-box">
            <div class="chart-title">1. Tren Pendaftaran vs Layanan Selesai</div>
            <img id="pdf-line-img" class="chart-image" alt="Grafik Line Tidak Tersedia" />
        </div>
        <div class="chart-box">
            <div class="chart-title">2. Proporsi Kepadatan Kategori Layanan</div>
            <img id="pdf-pie-img" class="chart-image" alt="Pie Chart Tidak Tersedia" />
        </div>
    </div>
    @endif

    <!-- OPSI 3: TABEL DETAIL TIKET -->
    @if(request('inc_table', 1))
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Nomor Tiket</th>
                <th>Nama Pelanggan</th>
                <th>Kategori & Sub-Layanan</th>
                <th style="width: 105px;">Waktu Masuk</th>
                <th style="width: 65px;">Status</th>
                <th style="width: 85px;">Metode</th>
                <th class="text-right" style="width: 95px;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $index => $t)
            @php
                $badgeClass = match($t->status) {
                    'Selesai' => 'badge-selesai',
                    'Batal'   => 'badge-batal',
                    'No Show' => 'badge-noshow',
                    default   => 'badge-default',
                };
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $t->nomor_antrian }}</strong></td>
                <td>{{ $t->pelanggan->nama ?? '-' }} ({{ $t->pelanggan->no_hp ?? '-' }})</td>
                <td>
                    <div>{{ $t->layanan->nama_layanan ?? '-' }}</div>
                    <div class="sub-text">{{ $t->subLayanan->nama_sub_layanan ?? '-' }}</div>
                </td>
                <td>{{ \Carbon\Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                <td>
                    <span class="badge {{ $badgeClass }}">
                        {{ $t->status }}
                    </span>
                </td>
                <td>{{ $t->metode_pembayaran ?? 'Tanpa Transaksi' }}</td>
                <td class="text-right">Rp {{ number_format($t->nominal_pembayaran, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #777; padding: 12px;">Tidak ada data tiket antrean pada rentang periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <div class="total-box">
        TOTAL OMSET DITERIMA: Rp {{ number_format($totalOmset, 0, ',', '.') }}
    </div>

    <!-- SCRIPT TARIK DATA & PRINT (100% VANILLA JS BEBAS ERROR BLADE) -->
    <script>
        window.addEventListener('load', function() {
            // Ambil flag konfigurasi dari HTML DOM
            const configEl = document.getElementById('pdf-config');
            const incSummary = configEl ? configEl.getAttribute('data-summary') : '1';
            const incCharts = configEl ? configEl.getAttribute('data-charts') : '1';
            
            // 1. Ekstrak data teks Summary
            if (incSummary === '1') {
                const summaryHTML = localStorage.getItem('pdf_summary_data');
                if (summaryHTML && summaryHTML.trim() !== '') {
                    const containerEl = document.getElementById('pdf-summary-container');
                    const contentEl = document.getElementById('pdf-summary-content');
                    if (containerEl && contentEl) {
                        contentEl.innerHTML = summaryHTML;
                        containerEl.style.display = 'block';
                    }
                }
            }

            // 2. Ekstrak gambar Grafik
            if (incCharts === '1') {
                const lineImgData = localStorage.getItem('pdf_line_data');
                const pieImgData = localStorage.getItem('pdf_pie_data');
                let hasCharts = false;

                if (lineImgData && lineImgData.length > 50) {
                    const imgEl = document.getElementById('pdf-line-img');
                    if (imgEl) { imgEl.src = lineImgData; hasCharts = true; }
                }
                if (pieImgData && pieImgData.length > 50) {
                    const imgEl = document.getElementById('pdf-pie-img');
                    if (imgEl) { imgEl.src = pieImgData; hasCharts = true; }
                }

                if (hasCharts) {
                    const containerEl = document.getElementById('pdf-charts-container');
                    if (containerEl) { containerEl.style.display = 'block'; }
                }
            }

            // Beri Jeda render layout HTML/Image, lalu Otomatis Print
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>