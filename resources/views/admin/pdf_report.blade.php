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

    <!-- HEADER LAPORAN -->
    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN ANTREAN & TRANSAKSI</h2>
        <p>Periode Waktu: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <!-- OPSI 1: EXECUTIVE SUMMARY -->
    @if($includeSummary)
    <div id="pdf-summary-container" class="summary-box">
        <div class="summary-title">Executive Summary / Analisis Otomatis Operasional</div>
        <div id="pdf-summary-content"></div>
    </div>
    @endif

    <!-- OPSI 2: GRAFIK ANALITIK DASAR (LINE & PIE) -->
    @if($includeCharts)
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

    <!-- OPSI 3: GRAFIK TREN WAKTU TUNGGU VS DURASI KONSUL -->
    @if($includeSlaCharts)
    <div id="pdf-sla-container" class="charts-container" style="display: none;">
        <div class="chart-box">
            <div class="chart-title">3. Tren Waktu Tunggu vs Durasi Konsul CS</div>
            <!-- Wadah Mode Gabung -->
            <div id="sla-merged-wrapper" style="display: none;">
                <img id="pdf-sla-merged-img" class="chart-image" alt="SLA Merged Chart" />
            </div>
            <!-- Wadah Mode Pisah (Berdampingan Kiri Kanan) -->
            <div id="sla-separate-wrapper" style="display: none; justify-content: space-between; gap: 10px;">
                <div style="flex: 1; border: 1px solid #e0e3e8; border-radius: 6px; padding: 10px; background: #eff6ff;">
                    <div style="font-size: 9px; font-weight: bold; margin-bottom: 5px; color: #1d4ed8; text-transform: uppercase;">Rata-Rata Waktu Tunggu (Menit)</div>
                    <img id="pdf-sla-tunggu-img" class="chart-image" style="width: 100%; height: auto;" alt="SLA Tunggu" />
                </div>
                <div style="flex: 1; border: 1px solid #e0e3e8; border-radius: 6px; padding: 10px; background: #ecfdf5;">
                    <div style="font-size: 9px; font-weight: bold; margin-bottom: 5px; color: #047857; text-transform: uppercase;">Rata-Rata Durasi Konsul (Menit)</div>
                    <img id="pdf-sla-konsul-img" class="chart-image" style="width: 100%; height: auto;" alt="SLA Konsul" />
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- OPSI 4: GRAFIK PERFORMA STAF CS -->
    @if($includeCsChart)
    <div id="pdf-cs-container" class="charts-container" style="display: none;">
        <div class="chart-box">
            <div class="chart-title">4. Performa Produktivitas Staf CS per Meja</div>
            <img id="pdf-cs-img" class="chart-image" style="max-height: 280px;" alt="CS Bar Chart Tidak Tersedia" />
        </div>
    </div>
    @endif

    <!-- OPSI 5: TABEL DETAIL TIKET -->
    @if($includeTable)
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
                <td>{{ $t->pelanggan?->nama ?? '-' }} ({{ $t->pelanggan?->no_hp ?? '-' }})</td>
                <td>
                    <div>{{ $t->layanan?->nama_layanan ?? '-' }}</div>
                    <div class="sub-text">{{ $t->subLayanan?->nama_sub_layanan ?? '-' }}</div>
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

    <!-- SCRIPT TERTUTUP SAFE-LINTER -->
    <script>
        window.addEventListener('load', function () {
            function getData(key) {
                var val = localStorage.getItem(key);
                return (val && val.length > 50) ? val : null;
            }

            // 1. EXECUTIVE SUMMARY
            var summaryContainer = document.getElementById('pdf-summary-container');
            var summaryContent = document.getElementById('pdf-summary-content');
            if (summaryContainer && summaryContent) {
                var summaryHTML = localStorage.getItem('pdf_summary_data');
                if (summaryHTML && summaryHTML.trim() !== '') {
                    summaryContent.innerHTML = summaryHTML;
                    summaryContainer.style.display = 'block';
                }
            }

            // 2. GRAFIK LINE & PIE
            var chartsContainer = document.getElementById('pdf-charts-container');
            if (chartsContainer) {
                var lineData = getData('pdf_line_data');
                var pieData = getData('pdf_pie_data');
                var hasCharts = false;

                if (lineData) {
                    var lineImg = document.getElementById('pdf-line-img');
                    if (lineImg) lineImg.src = lineData;
                    hasCharts = true;
                }
                if (pieData) {
                    var pieImg = document.getElementById('pdf-pie-img');
                    if (pieImg) pieImg.src = pieData;
                    hasCharts = true;
                }
                if (hasCharts) {
                    chartsContainer.style.display = 'block';
                }
            }

            // 3. GRAFIK SLA (WAKTU TUNGGU VS DURASI KONSUL)
            var slaContainer = document.getElementById('pdf-sla-container');
            if (slaContainer) {
                var isMerged = localStorage.getItem('pdf_sla_is_merged') === '1';
                var mergedData = getData('pdf_sla_merged_data');
                var tungguData = getData('pdf_sla_tunggu_data');
                var konsulData = getData('pdf_sla_konsul_data');

                if (isMerged && mergedData) {
                    var mergedImg = document.getElementById('pdf-sla-merged-img');
                    var mergedWrapper = document.getElementById('sla-merged-wrapper');
                    if (mergedImg && mergedWrapper) {
                        mergedImg.src = mergedData;
                        mergedWrapper.style.display = 'block';
                        slaContainer.style.display = 'block';
                    }
                } else if (!isMerged && tungguData && konsulData) {
                    var tungguImg = document.getElementById('pdf-sla-tunggu-img');
                    var konsulImg = document.getElementById('pdf-sla-konsul-img');
                    var separateWrapper = document.getElementById('sla-separate-wrapper');
                    if (tungguImg && konsulImg && separateWrapper) {
                        tungguImg.src = tungguData;
                        konsulImg.src = konsulData;
                        separateWrapper.style.display = 'flex';
                        slaContainer.style.display = 'block';
                    }
                }
            }

            // 4. GRAFIK PERFORMA CS
            var csContainer = document.getElementById('pdf-cs-container');
            if (csContainer) {
                var csData = getData('pdf_cs_bar_data');
                if (csData) {
                    var csImg = document.getElementById('pdf-cs-img');
                    if (csImg) {
                        csImg.src = csData;
                        csContainer.style.display = 'block';
                    }
                }
            }

            setTimeout(function () {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>