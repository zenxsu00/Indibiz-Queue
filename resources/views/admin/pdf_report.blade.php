<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Antrean Indibiz</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #181C20; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #EE2E24; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; color: #00509E; font-size: 16px; font-weight: bold; }
        .header p { margin: 3px 0; color: #555; font-size: 11px; }
        .summary-box { background-color: #f8f9fa; border: 1px solid #e0e3e8; border-radius: 6px; padding: 10px; margin-bottom: 15px; }
        .summary-title { font-weight: bold; font-size: 11px; color: #00509E; margin-bottom: 4px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f1f4f9; font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .sub-text { font-size: 9px; color: #666; font-style: italic; }
        .total-box { margin-top: 15px; text-align: right; font-size: 12px; font-weight: bold; }
        .text-right { text-align: right; }
        
        /* CSS Badge Murni */
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

    @if(request('inc_summary') && isset($analisisOtomatis['status_tiket']))
    <div class="summary-box">
        <div class="summary-title">Executive Summary / Analisis Otomatis Operasional</div>
        <div>{!! preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $analisisOtomatis['status_tiket']) !!}</div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">Nomor Tiket</th>
                <th>Nama Pelanggan</th>
                <th>Kategori & Sub-Layanan</th>
                <th style="width: 110px;">Waktu Masuk</th>
                <th style="width: 70px;">Status</th>
                <th style="width: 90px;">Metode</th>
                <th class="text-right" style="width: 100px;">Nominal</th>
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

    <div class="total-box">
        TOTAL OMSET DITERIMA: Rp {{ number_format($totalOmset, 0, ',', '.') }}
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>