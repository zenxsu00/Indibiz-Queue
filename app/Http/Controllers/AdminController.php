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
    /**
     * Display admin dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Filter Tanggal untuk Tab Operations (Default hari ini dalam Asia/Jakarta)
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date, 'Asia/Jakarta')->startOfDay() 
            : Carbon::today('Asia/Jakarta')->startOfDay();
            
        $endDate   = $request->end_date 
            ? Carbon::parse($request->end_date, 'Asia/Jakarta')->endOfDay() 
            : Carbon::today('Asia/Jakarta')->endOfDay();
            
        $layananId = $request->layanan_id;

        // Query Filtered untuk Tab Operations (Dengan Relasi Tambahan SubLayanan)
        $query = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs'])
            ->whereBetween('waktu_dibuat', [$startDate, $endDate]);

        if ($layananId) {
            $query->where('layanan_id', $layananId);
        }

        $allFilteredTickets = (clone $query)->orderBy('waktu_dibuat', 'desc')->get();
        $totalHariIni  = $allFilteredTickets->count();
        $menunggu      = $allFilteredTickets->where('status', 'Menunggu')->count();

        // -------------------------------------------------------------
        // PERHITUNGAN KHUSUS JANGKA WAKTU 1 BULAN (30 HARI TERAKHIR)
        // -------------------------------------------------------------
        $satuBulanLalu = Carbon::now('Asia/Jakarta')->subDays(30)->startOfDay();
        $sekarang      = Carbon::now('Asia/Jakarta')->endOfDay();

        $tiketSatuBulan = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs'])
            ->whereBetween('waktu_dibuat', [$satuBulanLalu, $sekarang])
            ->get();
        
        // 1. Total Omset Loket (1 Bulan)
        $totalOmsetBulanIni = $tiketSatuBulan->where('status', 'Selesai')->sum('nominal_pembayaran');

        // 2. Rata-Rata SLA (1 Bulan)
        $tiketSelesaiBulan = $tiketSatuBulan->where('status', 'Selesai')
            ->filter(fn($t) => $t->waktu_diproses && $t->waktu_selesai);

        if ($tiketSelesaiBulan->count() > 0) {
            $totalDetikBulan = 0;
            foreach ($tiketSelesaiBulan as $t) {
                $totalDetikBulan += Carbon::parse($t->waktu_diproses, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($t->waktu_selesai, 'Asia/Jakarta'));
            }
            $avgDetikBulan = round($totalDetikBulan / $tiketSelesaiBulan->count());
            $menitBulan    = floor($avgDetikBulan / 60);
            $detikBulan    = $avgDetikBulan % 60;
            $avgSla        = "{$menitBulan}m {$detikBulan}s";
        } else {
            $avgSla = "0m 0s";
        }

        // 3. Tab Riwayat Bulanan (Tabel Per Hari selama 30 Hari Terakhir)
        $historyBulanan = [];
        $period = Carbon::parse($satuBulanLalu, 'Asia/Jakarta')->daysUntil($sekarang);
        
        foreach ($period as $date) {
            $tgl = $date->format('Y-m-d');
            $tiketHari = $tiketSatuBulan->filter(fn($t) => Carbon::parse($t->waktu_dibuat, 'Asia/Jakarta')->format('Y-m-d') === $tgl);
            $tiketHariSelesai = $tiketHari->where('status', 'Selesai');

            // Hitung SLA per hari
            $totalDetikHari = 0;
            $countSlaHari = 0;
            foreach ($tiketHariSelesai as $th) {
                if ($th->waktu_diproses && $th->waktu_selesai) {
                    $totalDetikHari += Carbon::parse($th->waktu_diproses, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($th->waktu_selesai, 'Asia/Jakarta'));
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

        // Data Grafik Analitik Tren
        $chartDates   = [];
        $chartTotal   = [];
        $chartSelesai = [];

        foreach ($period as $date) {
            $formattedDate  = $date->format('Y-m-d');
            $chartDates[]   = $date->format('d M');
            $chartTotal[]   = $tiketSatuBulan->filter(fn($t) => Carbon::parse($t->waktu_dibuat, 'Asia/Jakarta')->format('Y-m-d') === $formattedDate)->count();
            $chartSelesai[] = $tiketSatuBulan->filter(fn($t) => $t->status === 'Selesai' && Carbon::parse($t->waktu_dibuat, 'Asia/Jakarta')->format('Y-m-d') === $formattedDate)->count();
        }

        // Distribusi Layanan
        $distribusiLayanan = Layanan::all()->map(function($layanan) use ($allFilteredTickets) {
            $item = new stdClass();
            $item->nama  = $layanan->nama_layanan;
            $item->total = $allFilteredTickets->where('layanan_id', $layanan->id)->count();
            return $item;
        })->sortByDesc('total');

        // -------------------------------------------------------------
        // STAFF MONITOR DINAMIS REAL-TIME MEJA CS (EKSPLISIT stdClass)
        // -------------------------------------------------------------
        $usersCS = User::where('role', 'cs')->get();
        $mejaCs  = [];

        foreach ($usersCS as $cs) {
            $tiketAktif = TiketAntrian::with(['layanan', 'subLayanan'])
                ->where('user_id', $cs->id)
                ->where('status', 'Diproses')
                ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                ->first();

            $totalSelesai = TiketAntrian::where('user_id', $cs->id)
                ->where('status', 'Selesai')
                ->whereBetween('waktu_dibuat', [$startDate, $endDate])
                ->count();

            // Penentuan Status Teks
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

        // Ambil Data Master Meja Fisik untuk Tampilan Katalog
        $masterMejas = MasterMeja::orderBy('nomor_meja', 'asc')->get();

        $layanans   = Layanan::with('subLayanans')->get();
        $totalOmset = $totalOmsetBulanIni;

        return view('admin.index', compact(
            'totalHariIni', 'menunggu', 'avgSla', 'totalOmset', 'distribusiLayanan', 'mejaCs', 'usersCS', 'masterMejas',
            'allFilteredTickets', 'chartDates', 'chartTotal', 'chartSelesai',
            'startDate', 'endDate', 'layananId', 'layanans', 'historyBulanan'
        ));
    }

    public function cetakPdf(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date, 'Asia/Jakarta')->startOfDay() 
            : Carbon::today('Asia/Jakarta')->startOfDay();
            
        $endDate   = $request->end_date 
            ? Carbon::parse($request->end_date, 'Asia/Jakarta')->endOfDay() 
            : Carbon::today('Asia/Jakarta')->endOfDay();
            
        $layananId = $request->layanan_id;

        $query = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs'])
            ->whereBetween('waktu_dibuat', [$startDate, $endDate]);

        if ($layananId) {
            $query->where('layanan_id', $layananId);
        }

        $tickets    = $query->orderBy('waktu_dibuat', 'asc')->get();
        $totalOmset = $tickets->sum('nominal_pembayaran');

        return view('admin.pdf_report', compact('tickets', 'startDate', 'endDate', 'totalOmset'));
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