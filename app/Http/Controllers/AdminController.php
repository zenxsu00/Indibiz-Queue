<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
use App\Models\Layanan;
use App\Models\SubLayanan;
use App\Models\User;
use App\Models\MasterMeja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'all');
        $layananId = $request->get('layanan_id');

        $startDate = null;
        $endDate = null;

        // Rentang Tanggal Utama
        switch ($period) {
            case 'today':
                $startDate = Carbon::today('Asia/Jakarta')->startOfDay();
                $endDate   = Carbon::today('Asia/Jakarta')->endOfDay();
                break;
            case 'wtd':
                $startDate = Carbon::now('Asia/Jakarta')->startOfWeek();
                $endDate   = Carbon::now('Asia/Jakarta')->endOfDay();
                break;
            case 'mtd':
                $startDate = Carbon::now('Asia/Jakarta')->startOfMonth();
                $endDate   = Carbon::now('Asia/Jakarta')->endOfDay();
                break;
            case 'last_30':
                $startDate = Carbon::now('Asia/Jakarta')->subDays(29)->startOfDay();
                $endDate   = Carbon::now('Asia/Jakarta')->endOfDay();
                break;
            case 'ytd':
                $startDate = Carbon::now('Asia/Jakarta')->startOfYear();
                $endDate   = Carbon::now('Asia/Jakarta')->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = Carbon::parse($request->start_date, 'Asia/Jakarta')->startOfDay();
                    $endDate   = Carbon::parse($request->end_date, 'Asia/Jakarta')->endOfDay();
                }
                break;
            case 'all':
            default:
                break;
        }

        $query = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs']);

        if ($startDate && $endDate) {
            $query->whereBetween('waktu_dibuat', [$startDate, $endDate]);
        }

        if ($request->filled('layanan_id')) {
            $query->where('layanan_id', $layananId);
        }

        /** @var \Illuminate\Database\Eloquent\Collection $allFilteredTickets */
        $allFilteredTickets = (clone $query)->orderBy('waktu_dibuat', 'desc')->get();
        $totalHariIni  = $allFilteredTickets->count();
        $menunggu      = $allFilteredTickets->where('status', 'Menunggu')->count();

        // -------------------------------------------------------------
        // RATA-RATA DURASI LAYANAN CS & WAKTU TUNGGU
        // -------------------------------------------------------------
        $tiketSelesaiFilter = $allFilteredTickets->where('status', 'Selesai')
            ->filter(fn($t) => !empty($t->waktu_mulai_konsul ?? $t->waktu_diproses) && !empty($t->waktu_selesai_konsul ?? $t->waktu_selesai));

        if ($tiketSelesaiFilter->count() > 0) {
            $totalDetikLayanan = 0;
            foreach ($tiketSelesaiFilter as $t) {
                $mulai = Carbon::parse($t->waktu_mulai_konsul ?? $t->waktu_diproses, 'Asia/Jakarta');
                $selesai = Carbon::parse($t->waktu_selesai_konsul ?? $t->waktu_selesai, 'Asia/Jakarta');
                $totalDetikLayanan += $mulai->diffInSeconds($selesai);
            }
            $avgDetikLayanan = round($totalDetikLayanan / $tiketSelesaiFilter->count());
            $mLayanan = floor($avgDetikLayanan / 60);
            $dLayanan = $avgDetikLayanan % 60;
            $avgDurasiLayananText = "{$mLayanan}m {$dLayanan}s";
        } else {
            $avgDurasiLayananText = "Belum Ada Data";
        }

        $tiketDipanggilFilter = $allFilteredTickets->whereIn('status', ['Diproses', 'Selesai'])
            ->filter(fn($t) => !empty($t->waktu_dibuat) && !empty($t->waktu_dipanggil ?? $t->waktu_diproses));

        if ($tiketDipanggilFilter->count() > 0) {
            $totalDetikTunggu = 0;
            foreach ($tiketDipanggilFilter as $td) {
                $dibuat = Carbon::parse($td->waktu_dibuat, 'Asia/Jakarta');
                $dipanggil = Carbon::parse($td->waktu_dipanggil ?? $td->waktu_diproses, 'Asia/Jakarta');
                $totalDetikTunggu += $dibuat->diffInSeconds($dipanggil);
            }
            $avgDetikTunggu = round($totalDetikTunggu / $tiketDipanggilFilter->count());
            $mTunggu = floor($avgDetikTunggu / 60);
            $dTunggu = $avgDetikTunggu % 60;
            $avgWaktuTungguText = "{$mTunggu}m {$dTunggu}s";
        } else {
            $avgWaktuTungguText = "Belum Ada Data";
        }

        $avgSla = $avgDurasiLayananText;

        // -------------------------------------------------------------
        // ANALISIS TREN OTOMATIS (PERIOD-OVER-PERIOD COMPARISON)
        // -------------------------------------------------------------
        $prevStartDate = null;
        $prevEndDate = null;
        $labelKomparasi = "Periode Sebelumnya";

        if ($startDate && $endDate) {
            $diffInDays = $startDate->diffInDays($endDate) + 1;
            $prevStartDate = (clone $startDate)->subDays($diffInDays);
            $prevEndDate = (clone $startDate)->subSecond();

            if ($period === 'today') $labelKomparasi = "Kemarin";
            elseif ($period === 'wtd') $labelKomparasi = "Minggu Lalu";
            elseif ($period === 'mtd') $labelKomparasi = "Bulan Lalu";
            elseif ($period === 'last_30') $labelKomparasi = "30 Hari Sebelumnya";
            elseif ($period === 'ytd') $labelKomparasi = "Tahun Lalu";
        }

        $totalLalu = 0;
        if ($prevStartDate && $prevEndDate) {
            $queryLalu = TiketAntrian::whereBetween('waktu_dibuat', [$prevStartDate, $prevEndDate]);
            if ($request->filled('layanan_id')) $queryLalu->where('layanan_id', $layananId);
            $totalLalu = $queryLalu->count();
        }

        $analisisOtomatis = [];
        if ($totalLalu == 0) {
            $analisisOtomatis['status_tiket'] = "Belum ada baseline data komparasi untuk {$labelKomparasi}. Total tiket saat ini adalah {$totalHariIni} tiket.";
            $analisisOtomatis['badge_tiket'] = "bg-gray-100 text-gray-700";
        } else {
            $selisih = $totalHariIni - $totalLalu;
            $persenDelta = round(($selisih / $totalLalu) * 100, 1);

            if (abs($persenDelta) <= 3) {
                $analisisOtomatis['status_tiket'] = "Volume antrean cenderung **STABIL** (fluktuasi {$persenDelta}% dibanding {$labelKomparasi}). Operasional berjalan konsisten.";
                $analisisOtomatis['badge_tiket'] = "bg-blue-100 text-blue-800";
            } elseif ($persenDelta > 3 && $persenDelta <= 20) {
                $analisisOtomatis['status_tiket'] = "Terjadi **PENINGKATAN MODERAT** sebesar **+{$persenDelta}%** ({$totalHariIni} vs {$totalLalu} tiket) dibanding {$labelKomparasi}.";
                $analisisOtomatis['badge_tiket'] = "bg-emerald-100 text-emerald-800";
            } elseif ($persenDelta > 20) {
                $analisisOtomatis['status_tiket'] = "Terjadi **LONJAKAN TINGGI** antrean sebesar **+{$persenDelta}%** dibanding {$labelKomparasi}. Disarankan penambahan petugas loket.";
                $analisisOtomatis['badge_tiket'] = "bg-emerald-200 text-emerald-900";
            } elseif ($persenDelta < -3 && $persenDelta >= -20) {
                $analisisOtomatis['status_tiket'] = "Terjadi **PENURUNAN MODERAT** sebesar **{$persenDelta}%** ({$totalHariIni} vs {$totalLalu} tiket) dibanding {$labelKomparasi}.";
                $analisisOtomatis['badge_tiket'] = "bg-amber-100 text-amber-800";
            } else {
                $analisisOtomatis['status_tiket'] = "Terjadi **PENURUNAN SIGNIFIKAN** sebesar **{$persenDelta}%** dibanding {$labelKomparasi}. Perlu peninjauan arus kedatangan pelanggan.";
                $analisisOtomatis['badge_tiket'] = "bg-rose-100 text-rose-800";
            }
        }

        // -------------------------------------------------------------
        // DATASET GRAFIK ANALITIK
        // -------------------------------------------------------------
        $allTickets = TiketAntrian::all();

        // Line Chart
        $dates30 = []; $total30 = []; $selesai30 = [];
        $p30 = Carbon::now('Asia/Jakarta')->subDays(29)->daysUntil(Carbon::now('Asia/Jakarta'));
        foreach ($p30 as $d) {
            $tgl = $d->format('Y-m-d');
            $dates30[] = $d->format('d M');
            $total30[] = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->format('Y-m-d') === $tgl)->count();
            $selesai30[] = $allTickets->filter(fn($t) => $t->status === 'Selesai' && Carbon::parse($t->waktu_dibuat)->format('Y-m-d') === $tgl)->count();
        }

        $datesWtd = []; $totalWtd = []; $selesaiWtd = [];
        $pWtd = Carbon::now('Asia/Jakarta')->startOfWeek()->daysUntil(Carbon::now('Asia/Jakarta'));
        foreach ($pWtd as $d) {
            $tgl = $d->format('Y-m-d');
            $datesWtd[] = $d->translatedFormat('D, d M');
            $totalWtd[] = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->format('Y-m-d') === $tgl)->count();
            $selesaiWtd[] = $allTickets->filter(fn($t) => $t->status === 'Selesai' && Carbon::parse($t->waktu_dibuat)->format('Y-m-d') === $tgl)->count();
        }

        $datesMtd = []; $totalMtd = []; $selesaiMtd = [];
        $pMtd = Carbon::now('Asia/Jakarta')->startOfMonth()->daysUntil(Carbon::now('Asia/Jakarta'));
        foreach ($pMtd as $d) {
            $tgl = $d->format('Y-m-d');
            $datesMtd[] = $d->format('d M');
            $totalMtd[] = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->format('Y-m-d') === $tgl)->count();
            $selesaiMtd[] = $allTickets->filter(fn($t) => $t->status === 'Selesai' && Carbon::parse($t->waktu_dibuat)->format('Y-m-d') === $tgl)->count();
        }

        $datesMtm = []; $totalMtm = []; $selesaiMtm = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now('Asia/Jakarta')->subMonths($i);
            $monthKey = $m->format('Y-m');
            $datesMtm[] = $m->translatedFormat('M Y');
            $totalMtm[] = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->format('Y-m') === $monthKey)->count();
            $selesaiMtm[] = $allTickets->filter(fn($t) => $t->status === 'Selesai' && Carbon::parse($t->waktu_dibuat)->format('Y-m') === $monthKey)->count();
        }

        // Doughnut / Pie Dataset - Status Ratio
        $statusRatioData = [
            'Selesai'  => $allFilteredTickets->where('status', 'Selesai')->count(),
            'Menunggu' => $allFilteredTickets->where('status', 'Menunggu')->count(),
            'Diproses' => $allFilteredTickets->where('status', 'Diproses')->count(),
            'Batal'    => $allFilteredTickets->where('status', 'Batal')->count(),
        ];

        // Rekap Bulanan Harian
        $satuBulanLalu = Carbon::now('Asia/Jakarta')->subDays(30)->startOfDay();
        $sekarang      = Carbon::now('Asia/Jakarta')->endOfDay();
        $tiketSatuBulan = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->between($satuBulanLalu, $sekarang));

        $historyBulanan = [];
        $periodRange = Carbon::parse($satuBulanLalu, 'Asia/Jakarta')->daysUntil($sekarang);
        
        foreach ($periodRange as $date) {
            $tgl = $date->format('Y-m-d');
            $tiketHari = $tiketSatuBulan->filter(fn($t) => Carbon::parse($t->waktu_dibuat, 'Asia/Jakarta')->format('Y-m-d') === $tgl);
            $tiketHariSelesai = $tiketHari->where('status', 'Selesai');

            $totalDetikHari = 0;
            $countSlaHari = 0;
            foreach ($tiketHariSelesai as $th) {
                $m = $th->waktu_mulai_konsul ?? $th->waktu_diproses;
                $s = $th->waktu_selesai_konsul ?? $th->waktu_selesai;
                if ($m && $s) {
                    $totalDetikHari += Carbon::parse($m, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($s, 'Asia/Jakarta'));
                    $countSlaHari++;
                }
            }
            $avgSlaHariText = "0m 0s";
            if ($countSlaHari > 0) {
                $avgDetikH = round($totalDetikHari / $countSlaHari);
                $mH = floor($avgDetikH / 60);
                $dH = $avgDetikH % 60;
                $avgSlaHariText = "{$mH}m {$dH}s";
            }

            $row = new stdClass();
            $row->tanggal      = $date->translatedFormat('d F Y');
            $row->total_tiket  = $tiketHari->count();
            $row->selesai      = $tiketHariSelesai->count();
            $row->batal        = $tiketHari->where('status', 'Batal')->count();
            $row->avg_sla      = $avgSlaHariText;
            $row->total_omset  = $tiketHariSelesai->sum('nominal_pembayaran');

            $historyBulanan[] = $row;
        }
        $historyBulanan = array_reverse($historyBulanan);

        $distribusiLayanan = Layanan::all()->map(function($layanan) use ($allFilteredTickets) {
            $item = new stdClass();
            $item->nama  = $layanan->nama_layanan;
            $item->total = $allFilteredTickets->where('layanan_id', $layanan->id)->count();
            return $item;
        })->sortByDesc('total');

        $usersCS = User::where('role', 'cs')->get();
        $mejaCs  = [];

        foreach ($usersCS as $cs) {
            $tiketAktif = TiketAntrian::with(['layanan', 'subLayanan'])
                ->where('user_id', $cs->id)
                ->where('status', 'Diproses')
                ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                ->first();

            $querySelesai = TiketAntrian::where('user_id', $cs->id)->where('status', 'Selesai');
            if ($startDate && $endDate) {
                $querySelesai->whereBetween('waktu_dibuat', [$startDate, $endDate]);
            }
            $totalSelesai = $querySelesai->count();

            $statusText = 'Offline';
            if ($cs->is_active) {
                $statusText = $tiketAktif ? 'Melayani Pelanggan' : 'Aktif';
            }

            $stafObj = new stdClass();
            $stafObj->id             = $cs->id;
            $stafObj->inisial        = strtoupper(substr($cs->nama_lengkap, 0, 2));
            $stafObj->nama           = $cs->nama_lengkap;
            $stafObj->nomor_meja     = str_pad((string)($cs->nomor_meja ?? 0), 2, '0', STR_PAD_LEFT);
            $stafObj->is_active      = (bool) $cs->is_active;
            $stafObj->status         = $statusText;
            $stafObj->tiket_aktif    = $tiketAktif ? $tiketAktif->nomor_antrian : '-';
            $stafObj->layanan_aktif  = $tiketAktif ? $tiketAktif->layanan->nama_layanan : '-';
            $stafObj->total_dilayani = $totalSelesai;

            $mejaCs[] = $stafObj;
        }

        $masterMejas = MasterMeja::orderBy('nomor_meja', 'asc')->get();
        $layanans   = Layanan::with('subLayanans')->get();
        $totalOmset = $allFilteredTickets->where('status', 'Selesai')->sum('nominal_pembayaran');

        $startDateOut = $startDate ? $startDate : Carbon::today('Asia/Jakarta')->startOfDay();
        $endDateOut   = $endDate ? $endDate : Carbon::today('Asia/Jakarta')->endOfDay();

        return view('admin.index', [
            'totalHariIni'         => $totalHariIni,
            'menunggu'             => $menunggu,
            'avgSla'               => $avgSla,
            'avgDurasiLayananText' => $avgDurasiLayananText,
            'avgWaktuTungguText'   => $avgWaktuTungguText,
            'totalOmset'           => $totalOmset,
            'distribusiLayanan'    => $distribusiLayanan,
            'statusRatioData'      => $statusRatioData,
            'analisisOtomatis'     => $analisisOtomatis,
            'mejaCs'               => $mejaCs,
            'usersCS'              => $usersCS,
            'masterMejas'          => $masterMejas,
            'allFilteredTickets'   => $allFilteredTickets,
            'chartDataSets'        => [
                'wtd'    => ['dates' => $datesWtd, 'total' => $totalWtd, 'selesai' => $selesaiWtd],
                'mtd'    => ['dates' => $datesMtd, 'total' => $totalMtd, 'selesai' => $selesaiMtd],
                'mtm'    => ['dates' => $datesMtm, 'total' => $totalMtm, 'selesai' => $selesaiMtm],
                'last30' => ['dates' => $dates30, 'total' => $total30, 'selesai' => $selesai30],
            ],
            'startDate'            => $startDateOut,
            'endDate'              => $endDateOut,
            'layananId'            => $layananId,
            'layanans'             => $layanans,
            'historyBulanan'       => $historyBulanan
        ]);
    }

    public function storeLayanan(Request $request)
    {
        $request->validate(['nama_layanan' => 'required|string|max:255']);
        Layanan::create(['nama_layanan' => $request->nama_layanan, 'is_active' => true]);
        return back()->with('success', 'Kategori Layanan Utama berhasil ditambahkan.');
    }

    public function destroyLayanan($id)
    {
        $layanan = Layanan::findOrFail($id);
        $layanan->delete();
        return back()->with('success', 'Kategori Layanan beserta seluruh sub-layanannya berhasil dihapus.');
    }

    public function storeSubLayanan(Request $request)
    {
        $request->validate([
            'layanan_id'       => 'required|exists:layanans,id',
            'nama_sub_layanan' => 'required|string|max:255'
        ]);
        SubLayanan::create([
            'layanan_id'       => $request->layanan_id,
            'nama_sub_layanan' => $request->nama_sub_layanan,
            'is_active'        => true
        ]);
        return back()->with('success', 'Sub-Layanan Sektoral berhasil ditambahkan.');
    }

    public function destroySubLayanan($id)
    {
        $sub = SubLayanan::findOrFail($id);
        $sub->delete();
        return back()->with('success', 'Sub-Layanan Sektoral berhasil dihapus.');
    }

    public function cetakPdf(Request $request)
    {
        $period = $request->get('period', 'all');
        $layananId = $request->get('layanan_id');

        $query = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs']);

        if ($period !== 'all' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date, 'Asia/Jakarta')->startOfDay();
            $endDate   = Carbon::parse($request->end_date, 'Asia/Jakarta')->endOfDay();
            $query->whereBetween('waktu_dibuat', [$startDate, $endDate]);
        } else {
            $startDate = Carbon::today('Asia/Jakarta')->startOfDay();
            $endDate   = Carbon::today('Asia/Jakarta')->endOfDay();
        }

        if ($request->filled('layanan_id')) {
            $query->where('layanan_id', $layananId);
        }

        $tickets    = $query->orderBy('waktu_dibuat', 'asc')->get();
        $totalOmset = $tickets->where('status', 'Selesai')->sum('nominal_pembayaran');

        // Komponen yang Dicentang
        $includeSummary = $request->has('inc_summary');
        $includeCharts  = $request->has('inc_charts');
        $includeTable   = $request->has('inc_table');

        // Data Base64 Grafik dari Canvas
        $chartLineBase64 = $request->input('chart_line_base64');
        $chartPieBase64  = $request->input('chart_pie_base64');

        return view('admin.pdf_report', compact(
            'tickets', 'startDate', 'endDate', 'totalOmset', 
            'includeSummary', 'includeCharts', 'includeTable', 
            'chartLineBase64', 'chartPieBase64'
        ));
    }

    public function exportCsv()
    {
        $fileName = 'rekap_antrean_indibiz_' . date('Y-m-d_H-i-s') . '.csv';
        $tickets  = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs'])->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Kode Tiket', 'Nomor Display', 'Nama Pelanggan', 'No HP', 'Email', 'No Indibiz', 'Layanan Utama', 'Sub Layanan', 'CS Melayani', 'Status', 'Metode Bayar', 'Nominal (Rp)', 'Waktu Dibuat'];

        $callback = function() use($tickets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket->id,
                    $ticket->kode_tiket ?? '-',
                    $ticket->nomor_antrian,
                    $ticket->pelanggan->nama ?? '-',
                    $ticket->pelanggan->no_hp ?? '-',
                    $ticket->pelanggan->email ?? '-',
                    $ticket->pelanggan->no_indibiz ?? '-',
                    $ticket->layanan->nama_layanan ?? '-',
                    $ticket->subLayanan->nama_sub_layanan ?? '-',
                    $ticket->cs->nama_lengkap ?? '-',
                    $ticket->status,
                    $ticket->metode_pembayaran ?? 'Tanpa Transaksi',
                    $ticket->nominal_pembayaran ?? 0,
                    $ticket->waktu_dibuat
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}