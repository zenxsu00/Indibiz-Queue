// Variable Global Chart Instance
var globalQueueChart = null;
var globalPieChart = null;
var globalSlaMergedChart = null;
var globalSlaTungguChart = null;
var globalSlaKonsulChart = null;
var globalBarChart = null;

// Helper Kustomisasi Cetak PDF
function togglePdfCustomDates(val) {
    const container = document.getElementById('pdf_custom_dates_container');
    if (!container) return;
    if (val === 'custom') {
        container.classList.remove('hidden');
        container.classList.add('grid');
    } else {
        container.classList.add('hidden');
        container.classList.remove('grid');
    }
}

function preparePdfSubmit(event) {
    const periodSelect = document.getElementById('pdf_period_select');
    const period = periodSelect ? periodSelect.value : 'mtd';
    const startInput = document.querySelector('input[name="start_date"]');
    const endInput = document.querySelector('input[name="end_date"]');
    
    if (period !== 'custom') {
        const today = new Date();
        let startDate = new Date();
        
        if (period === 'wtd') {
            const day = startDate.getDay() || 7;
            if (day !== 1) startDate.setHours(-24 * (day - 1));
        } else if (period === 'mtd') {
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (period === 'last_30') {
            startDate.setDate(today.getDate() - 30);
        } else if (period === 'ytd') {
            startDate = new Date(today.getFullYear(), 0, 1);
        }

        const formatDate = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        if(startInput) startInput.value = formatDate(startDate);
        if(endInput) endInput.value = formatDate(today);
    }

    const summarySrc = document.getElementById('summary-content-source');
    localStorage.setItem('pdf_summary_data', summarySrc ? summarySrc.innerHTML : '');

    const lineCanvas = document.getElementById('queueChart');
    const pieCanvas = document.getElementById('categoryPieChart');
    const slaMerged = document.getElementById('slaMergedChart');
    const slaTunggu = document.getElementById('slaTungguSeparateChart');
    const slaKonsul = document.getElementById('slaKonsulSeparateChart');
    const csBar = document.getElementById('csBarChart');

    localStorage.setItem('pdf_line_data', lineCanvas ? lineCanvas.toDataURL('image/png') : '');
    localStorage.setItem('pdf_pie_data', pieCanvas ? pieCanvas.toDataURL('image/png') : '');
    
    localStorage.setItem('pdf_sla_merged_data', slaMerged ? slaMerged.toDataURL('image/png') : '');
    localStorage.setItem('pdf_sla_tunggu_data', slaTunggu ? slaTunggu.toDataURL('image/png') : '');
    localStorage.setItem('pdf_sla_konsul_data', slaKonsul ? slaKonsul.toDataURL('image/png') : '');
    localStorage.setItem('pdf_cs_bar_data', csBar ? csBar.toDataURL('image/png') : '');

    const isMergedVisible = slaMerged && slaMerged.offsetParent !== null;
    localStorage.setItem('pdf_sla_is_merged', isMergedVisible ? '1' : '0');

    return true;
}

// Inisialisasi Komponen Alpine JS via Alpine Event Listener
document.addEventListener('alpine:init', () => {

    // Alpine Component: Grafik & Analitik
    Alpine.data('chartFilterComponent', () => ({
        chartPeriod: (typeof serverPeriod !== 'undefined' && serverPeriod === 'custom') ? 'custom' : 'last30',
        isMerged: true,
        allDataSets: {},
        rawCsData: [],
        hiddenAccounts: [],
        
        init() {
            this.$nextTick(() => {
                const canvasLine = document.getElementById('queueChart');
                if (canvasLine && canvasLine.dataset.chartSets) {
                    try {
                        this.allDataSets = JSON.parse(canvasLine.dataset.chartSets);
                        const initialPeriod = this.chartPeriod === 'custom' ? 'last30' : this.chartPeriod;
                        this.renderLineChart(initialPeriod);
                        this.renderSlaCharts(initialPeriod);
                    } catch (e) { console.error('Gagal Parse Chart Data:', e); }
                }
                this.renderPieChart();
                this.renderBarChart();
            });
        },

        switchChartPeriod(period) {
            this.chartPeriod = period;
            if (period !== 'custom') {
                this.renderLineChart(period);
                this.renderSlaCharts(period);
            }
        },

        toggleSlaMode(mergedStatus) {
            this.isMerged = mergedStatus;
            this.$nextTick(() => {
                const currentPeriod = this.chartPeriod === 'custom' ? 'last30' : this.chartPeriod;
                this.renderSlaCharts(currentPeriod);
            });
        },

        renderLineChart(periodKey) {
            const dataSet = this.allDataSets[periodKey] || this.allDataSets['last30'] || this.allDataSets['all'];
            const canvas = document.getElementById('queueChart');
            if (!canvas || !dataSet) return;

            const ctx = canvas.getContext('2d');
            if (globalQueueChart) globalQueueChart.destroy();

            globalQueueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dataSet.dates || [],
                    datasets: [
                        { label: 'Total Tiket Masuk', data: dataSet.total || [], borderColor: '#00509E', backgroundColor: 'rgba(0, 80, 158, 0.1)', fill: true, tension: 0.3 },
                        { label: 'Layanan Selesai', data: dataSet.selesai || [], borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
            });
        },

        renderSlaCharts(periodKey) {
            const activeKey = periodKey || (this.chartPeriod === 'custom' ? 'last30' : this.chartPeriod);
            const dataSet = this.allDataSets[activeKey] || this.allDataSets['last30'] || this.allDataSets['all'];
            if (!dataSet) return;

            if (this.isMerged) {
                const canvas = document.getElementById('slaMergedChart');
                if (!canvas) return;

                if (globalSlaMergedChart) globalSlaMergedChart.destroy();

                globalSlaMergedChart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dataSet.dates || [],
                        datasets: [
                            { label: 'Avg Waktu Tunggu (Menit)', data: dataSet.avg_tunggu || [], borderColor: '#3B82F6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.3 },
                            { label: 'Avg Durasi Konsul CS (Menit)', data: dataSet.avg_layanan || [], borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.3 }
                        ]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'Waktu (Menit)' } } }
                    }
                });
            } else {
                const canvasTunggu = document.getElementById('slaTungguSeparateChart');
                const canvasKonsul = document.getElementById('slaKonsulSeparateChart');

                if (canvasTunggu) {
                    if (globalSlaTungguChart) globalSlaTungguChart.destroy();
                    globalSlaTungguChart = new Chart(canvasTunggu.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: dataSet.dates || [],
                            datasets: [{ label: 'Waktu Tunggu', data: dataSet.avg_tunggu || [], borderColor: '#3B82F6', backgroundColor: 'rgba(59, 130, 246, 0.2)', fill: true, tension: 0.3 }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                    });
                }

                if (canvasKonsul) {
                    if (globalSlaKonsulChart) globalSlaKonsulChart.destroy();
                    globalSlaKonsulChart = new Chart(canvasKonsul.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: dataSet.dates || [],
                            datasets: [{ label: 'Durasi Konsul CS', data: dataSet.avg_layanan || [], borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.2)', fill: true, tension: 0.3 }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                    });
                }
            }
        },

        renderPieChart() {
            const canvas = document.getElementById('categoryPieChart');
            if (!canvas || !canvas.dataset.dist) return;

            try {
                let raw = JSON.parse(canvas.dataset.dist);
                if (!Array.isArray(raw)) raw = Object.values(raw);

                const labels = raw.map(i => i.nama);
                const data = raw.map(i => i.total);

                if (globalPieChart) globalPieChart.destroy();

                globalPieChart = new Chart(canvas.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: ['#00509E', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#64748B']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });
            } catch (e) { console.error('Gagal Render Pie Chart:', e); }
        },

        renderBarChart() {
            const canvas = document.getElementById('csBarChart');
            if (!canvas || !canvas.dataset.cs) return;

            try {
                if (this.rawCsData.length === 0) {
                    this.rawCsData = JSON.parse(canvas.dataset.cs);
                }

                const filteredData = this.rawCsData.filter(i => {
                    return !this.hiddenAccounts.includes(i.id.toString());
                });

                const labels = filteredData.map(i => i.nama + (i.nomor_meja > 0 ? ' (M' + i.nomor_meja + ')' : ''));
                const data = filteredData.map(i => i.total_dilayani);

                if (globalBarChart) globalBarChart.destroy();

                globalBarChart = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Tiket Berhasil Diselesaikan',
                            data: data,
                            backgroundColor: '#8B5CF6', 
                            borderRadius: 6, 
                            borderSkipped: false,
                            barPercentage: 0.6
                        }]
                    },
                    options: { 
                        indexAxis: 'y', 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        plugins: { 
                            legend: { display: false }
                        },
                        scales: {
                            x: { grid: { display: false, drawBorder: false } },
                            y: { grid: { color: '#F3F4F6', drawBorder: false } }
                        }
                    }
                });
            } catch (e) { console.error('Gagal Render Bar Chart:', e); }
        }
    }));

    // Alpine Component: Filter Tabel Riwayat
    Alpine.data('historyFilterComponent', () => ({
        daysLimit: '30',
        hideEmpty: false,
        sortField: 'raw_date',
        sortOrder: 'desc',
        rawHistory: [],
        
        init() {
            const elem = document.getElementById('history-data-container');
            if (elem && elem.dataset.history) {
                try { 
                    this.rawHistory = JSON.parse(elem.dataset.history); 
                } catch (e) { 
                    console.error('Gagal Parse Data Riwayat:', e);
                    this.rawHistory = []; 
                }
            }
        },
        
        get filteredHistory() {
            if (!Array.isArray(this.rawHistory) || this.rawHistory.length === 0) return [];

            let data = [...this.rawHistory];
            if (this.daysLimit !== 'all') data = data.slice(0, parseInt(this.daysLimit));
            if (this.hideEmpty) data = data.filter(i => i.tiket_masuk > 0);

            var self = this;
            data.sort((a, b) => {
                let valA = a[self.sortField] ?? '';
                let valB = b[self.sortField] ?? '';
                return self.sortOrder === 'asc' ? (valA > valB ? 1 : -1) : (valA < valB ? 1 : -1);
            });
            return data;
        }
    }));
});

// Event Listener DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    const pdfSelect = document.getElementById('pdf_period_select');
    if (pdfSelect) togglePdfCustomDates(pdfSelect.value);
});