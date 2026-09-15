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

    <!-- WADAH FLAG KONFIGURASI DARI CONTROLLER -->
    <div id="pdf-config" 
         data-summary="{{ request('inc_summary', 1) }}" 
         data-charts="{{ request('inc_charts', 1) }}" 
         data-sla-charts="{{ request('inc_sla_charts', 1) }}"
         data-cs-chart="{{ request('inc_cs_chart', 1) }}"
         style="display: none;"></div>

    <!-- HEADER LAPORAN -->
    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN ANTREAN & TRANSAKSI</h2>
        <p>Periode Waktu: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <!-- OPSI 1: EXECUTIVE SUMMARY -->
    @if(request('inc_summary', 1))
    <div id="pdf-summary-container" class="summary-box" style="display: none;">
        <div class="summary-title">Executive Summary / Analisis Otomatis Operasional</div>
        <div id="pdf-summary-content"></div>
    </div>
    @endif

    <!-- OPSI 2: GRAFIK ANALITIK DASAR (LINE & PIE) -->
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

    <!-- OPSI 3: GRAFIK TREN WAKTU TUNGGU VS DURASI KONSUL -->
    @if(request('inc_sla_charts', 1))
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
    @if(request('inc_cs_chart', 1))
    <div id="pdf-cs-container" class="charts-container" style="display: none;">
        <div class="chart-box">
            <div class="chart-title">4. Performa Produktivitas Staf CS per Meja</div>
            <img id="pdf-cs-img" class="chart-image" style="max-height: 280px;" alt="CS Bar Chart Tidak Tersedia" />
        </div>
    </div>
    @endif

    <!-- OPSI 5: TABEL DETAIL TIKET -->
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

    <!-- SCRIPT TARIK DATA FOTO MEMORI BROWSER LALU PRINT (100% MURNI JS) -->
    <script>
        window.addEventListener('load', function() {
            const configEl = document.getElementById('pdf-config');
            const incSummary = configEl ? configEl.getAttribute('data-summary') : '1';
            const incCharts = configEl ? configEl.getAttribute('data-charts') : '1';
            const incSlaCharts = configEl ? configEl.getAttribute('data-sla-charts') : '1';
            const incCsChart = configEl ? configEl.getAttribute('data-cs-chart') : '1';
            
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

            // 2. Ekstrak Gambar Grafik Dasar (Line & Pie)
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

            // 3. Ekstrak Gambar Grafik SLA (Waktu Tunggu & Durasi)
            if (incSlaCharts === '1') {
                const isMerged = localStorage.getItem('pdf_sla_is_merged') === '1';
                const mergedData = localStorage.getItem('pdf_sla_merged_data');
                const tungguData = localStorage.getItem('pdf_sla_tunggu_data');
                const konsulData = localStorage.getItem('pdf_sla_konsul_data');
                const containerEl = document.getElementById('pdf-sla-container');

                if (containerEl) {
                    // Cek jika sedang memakai Mode Gabung
                    if (isMerged && mergedData && mergedData.length > 50) {
                        document.getElementById('pdf-sla-merged-img').src = mergedData;
                        document.getElementById('sla-merged-wrapper').style.display = 'block';
                        containerEl.style.display = 'block';
                    } 
                    // Cek jika sedang memakai Mode Pisah (Berdampingan)
                    else if (!isMerged && tungguData && konsulData && tungguData.length > 50) {
                        document.getElementById('pdf-sla-tunggu-img').src = tungguData;
                        document.getElementById('pdf-sla-konsul-img').src = konsulData;
                        document.getElementById('sla-separate-wrapper').style.display = 'flex';
                        containerEl.style.display = 'block';
                    }
                }
            }

            // 4. Ekstrak Gambar Grafik Performa Staf CS (Bar Chart)
            if (incCsChart === '1') {
                const csData = localStorage.getItem('pdf_cs_bar_data');
                if (csData && csData.length > 50) {
                    const imgEl = document.getElementById('pdf-cs-img');
                    if (imgEl) {
                        imgEl.src = csData;
                        document.getElementById('pdf-cs-container').style.display = 'block';
                    }
                }
            }

            // Beri Jeda 0.6 detik render layout HTML/Image ke PDF, lalu Otomatis Print
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>