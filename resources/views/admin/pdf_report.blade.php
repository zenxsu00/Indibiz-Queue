<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Antrean Indibiz</title>
    <!-- Ganti CDN ke versi Unpkg yang lebih stabil untuk window.print -->
    <script src="https://unpkg.com/chart.js@4.4.1/dist/chart.umd.js"></script>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #181C20; margin: 20px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #EE2E24; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #00509E; font-size: 16px; font-weight: bold; }
        .header p { margin: 3px 0; color: #555; font-size: 11px; }
        
        .summary-box { background-color: #f8f9fa; border: 1px solid #e0e3e8; border-radius: 6px; padding: 10px; margin-bottom: 15px; }
        .summary-title { font-weight: bold; font-size: 11px; color: #00509E; margin-bottom: 4px; text-transform: uppercase; }
        
        .charts-container { margin-bottom: 15px; page-break-inside: avoid; }
        .chart-box { border: 1px solid #e0e3e8; border-radius: 6px; padding: 12px; margin-bottom: 12px; background: #fff; }
        .chart-title { font-weight: bold; font-size: 11px; color: #181C20; margin-bottom: 8px; text-transform: uppercase; }
        
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

    <div class="header">
        <h2>INDIBIZ SERVICE DESK - LAPORAN ANTREAN & TRANSAKSI</h2>
        <p>Periode Waktu: {{ $startDate->format('d/m/Y') }} s/d {{ $endDate->format('d/m/Y') }}</p>
    </div>

    @if(request('inc_summary', 1) && isset($analisisOtomatis['status_tiket']))
    <div class="summary-box">
        <div class="summary-title">Executive Summary / Analisis Otomatis Operasional</div>
        <div>{!! preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $analisisOtomatis['status_tiket']) !!}</div>
    </div>
    @endif

    @if(request('inc_charts', 1))
    <div class="charts-container">
        <div class="chart-box">
            <div class="chart-title">1. Tren Pendaftaran vs Layanan Selesai</div>
            <div style="height: 220px; width: 100%; position: relative;">
                <canvas id="pdfLineChart"></canvas>
            </div>
        </div>
        <div class="chart-box">
            <div class="chart-title">2. Proporsi Kepadatan Kategori Layanan</div>
            <div style="height: 220px; width: 100%; position: relative;">
                <canvas id="pdfPieChart"></canvas>
            </div>
        </div>
    </div>
    @endif

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

    <!-- WADAH DATA BLADE (MENCEGAH ERROR LINTER JS) -->
    <div id="pdf-data-container" 
         class="hidden no-print" 
         style="display: none;"
         data-inc-charts="{{ request('inc_charts', 1) ? 'true' : 'false' }}"
         data-period="{{ request('period', 'mtd') }}"
         data-chart-sets="{{ json_encode($chartDataSets ?? []) }}"
         data-dist="{{ json_encode($distribusiLayanan ?? []) }}">
    </div>

    <!-- SCRIPT JS MURNI (BEBAS DARI ERROR DECORATOR VSCODE) -->
    <script>
        window.addEventListener('load', function() {
            const dataContainer = document.getElementById('pdf-data-container');
            
            if (dataContainer && dataContainer.dataset.incCharts === 'true') {
                try {
                    const chartDataSets = JSON.parse(dataContainer.dataset.chartSets || '{}');
                    const periodKey = dataContainer.dataset.period || 'mtd';
                    
                    let dataSet = chartDataSets[periodKey] || chartDataSets['mtd'] || chartDataSets['last30'];
                    if (!dataSet && Object.keys(chartDataSets).length > 0) {
                        dataSet = chartDataSets[Object.keys(chartDataSets)[0]];
                    }

                    // 1. RENDER LINE CHART
                    const lineCanvas = document.getElementById('pdfLineChart');
                    if (lineCanvas && dataSet) {
                        new Chart(lineCanvas.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: dataSet.dates || [],
                                datasets: [
                                    { label: 'Total Tiket Masuk', data: dataSet.total || [], borderColor: '#00509E', backgroundColor: 'rgba(0, 80, 158, 0.1)', fill: true, tension: 0.3 },
                                    { label: 'Layanan Selesai', data: dataSet.selesai || [], borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3 }
                                ]
                            },
                            options: { responsive: true, maintainAspectRatio: false, animation: false, plugins: { legend: { position: 'top' } } }
                        });
                    }

                    // 2. RENDER PIE CHART
                    const pieCanvas = document.getElementById('pdfPieChart');
                    if (pieCanvas) {
                        let rawDist = JSON.parse(dataContainer.dataset.dist || '[]');
                        if (!Array.isArray(rawDist)) rawDist = Object.values(rawDist);
                        
                        const labels = rawDist.map(i => i.nama || 'Lainnya');
                        const data = rawDist.map(i => i.total || 0);

                        new Chart(pieCanvas.getContext('2d'), {
                            type: 'pie',
                            data: {
                                labels: labels.length ? labels : ['Tanpa Data'],
                                datasets: [{
                                    data: data.length ? data : [1],
                                    backgroundColor: ['#00509E', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#64748B']
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, animation: false, plugins: { legend: { position: 'right' } } }
                        });
                    }
                } catch (err) {
                    console.error("Gagal merender chart PDF:", err);
                }
            }

            // MEMBERIKAN DELAY MEMASTIKAN RENDERING SELESAI SEBELUM DI-PRINT
            setTimeout(function() {
                window.print();
            }, 1000);
        });
    </script>
</body>
</html>