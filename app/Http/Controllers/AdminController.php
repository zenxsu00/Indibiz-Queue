<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
use App\Models\Layanan;
use App\Models\SubLayanan;
use App\Models\User;
use App\Models\MasterMeja;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use stdClass;

class AdminController extends Controller
{
    /**
     * Dashboard Utama Admin
     */
    public function index(Request $request): View
    {
        $period = $request->input('period', 'all');
        $layananId = $request->input('layanan_id');

        $startDate = null;
        $endDate = null;

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
                    $startDate = Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay();
                    $endDate   = Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay();
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
        $ditransfer    = $allFilteredTickets->where('status', 'Ditransfer')->count();
        $noShowCount   = $allFilteredTickets->where('status', 'No Show')->count();

        // -------------------------------------------------------------
        // DURASI CS & WAKTU TUNGGU
        // -------------------------------------------------------------
        $tiketSelesaiFilter = $allFilteredTickets->where('status', 'Selesai');

        if ($tiketSelesaiFilter->count() > 0) {
            $totalDetikLayanan = 0;
            $countValid = 0;
            foreach ($tiketSelesaiFilter as $t) {
                if (isset($t->waktu_layanan) && $t->waktu_layanan > 0) {
                    $totalDetikLayanan += $t->waktu_layanan;
                    $countValid++;
                } else {
                    $mulai = $t->waktu_mulai_konsul ?? $t->waktu_diproses;
                    $selesai = $t->waktu_selesai_konsul ?? $t->waktu_selesai;
                    if ($mulai && $selesai) {
                        $totalDetikLayanan += Carbon::parse($mulai, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($selesai, 'Asia/Jakarta'));
                        $countValid++;
                    }
                }
            }
            if ($countValid > 0) {
                $avgDetikLayanan = round($totalDetikLayanan / $countValid);
                $mLayanan = floor($avgDetikLayanan / 60);
                $dLayanan = $avgDetikLayanan % 60;
                $avgDurasiLayananText = "{$mLayanan}m {$dLayanan}s";
            } else {
                $avgDurasiLayananText = "0m 0s";
            }
        } else {
            $avgDurasiLayananText = "0m 0s";
        }

        $tiketDipanggilFilter = $allFilteredTickets->whereIn('status', ['Diproses', 'Selesai', 'No Show', 'Ditransfer']);

        if ($tiketDipanggilFilter->count() > 0) {
            $totalDetikTunggu = 0;
            $countValidTunggu = 0;
            foreach ($tiketDipanggilFilter as $td) {
                if (isset($td->waktu_tunggu) && $td->waktu_tunggu > 0) {
                    $totalDetikTunggu += $td->waktu_tunggu;
                    $countValidTunggu++;
                } else {
                    $dibuat = $td->waktu_dibuat;
                    $dipanggil = $td->waktu_dipanggil ?? $td->waktu_diproses;
                    if ($dibuat && $dipanggil) {
                        $totalDetikTunggu += Carbon::parse($dibuat, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($dipanggil, 'Asia/Jakarta'));
                        $countValidTunggu++;
                    }
                }
            }
            if ($countValidTunggu > 0) {
                $avgDetikTunggu = round($totalDetikTunggu / $countValidTunggu);
                $mTunggu = floor($avgDetikTunggu / 60);
                $dTunggu = $avgDetikTunggu % 60;
                $avgWaktuTungguText = "{$mTunggu}m {$dTunggu}s";
            } else {
                $avgWaktuTungguText = "0m 0s";
            }
        } else {
            $avgWaktuTungguText = "0m 0s";
        }

        $avgSla = $avgDurasiLayananText;

        // Rating
        $ratedTickets = $allFilteredTickets->filter(fn($t) => !empty($t->rating) && $t->rating > 0);
        $avgRating = $ratedTickets->count() > 0 ? round($ratedTickets->avg('rating'), 1) : 0;

        // -------------------------------------------------------------
        // ANALISIS TREN OTOMATIS
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
        // GRAFIK ANALITIK DATASET
        // -------------------------------------------------------------
        $allTickets = TiketAntrian::with('layanan')->get();

        $calcSlaMetrics = function($tickets) {
            $dipanggil = $tickets->whereIn('status', ['Diproses', 'Selesai', 'No Show', 'Ditransfer']);
            $totalDetikTunggu = 0; $countTunggu = 0;
            foreach ($dipanggil as $t) {
                if (isset($t->waktu_tunggu) && $t->waktu_tunggu > 0) {
                    $totalDetikTunggu += $t->waktu_tunggu; $countTunggu++;
                } else {
                    $m = $t->waktu_dibuat; $s = $t->waktu_dipanggil ?? $t->waktu_diproses;
                    if ($m && $s) {
                        $totalDetikTunggu += Carbon::parse($m, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($s, 'Asia/Jakarta'));
                        $countTunggu++;
                    }
                }
            }
            $avgTungguMenit = $countTunggu > 0 ? round(($totalDetikTunggu / $countTunggu) / 60, 1) : 0;

            $selesai = $tickets->where('status', 'Selesai');
            $totalDetikLayanan = 0; $countLayanan = 0;
            foreach ($selesai as $t) {
                if (isset($t->waktu_layanan) && $t->waktu_layanan > 0) {
                    $totalDetikLayanan += $t->waktu_layanan; $countLayanan++;
                } else {
                    $m = $t->waktu_mulai_konsul ?? $t->waktu_diproses; $s = $t->waktu_selesai_konsul ?? $t->waktu_selesai;
                    if ($m && $s) {
                        $totalDetikLayanan += Carbon::parse($m, 'Asia/Jakarta')->diffInSeconds(Carbon::parse($s, 'Asia/Jakarta'));
                        $countLayanan++;
                    }
                }
            }
            $avgLayananMenit = $countLayanan > 0 ? round(($totalDetikLayanan / $countLayanan) / 60, 1) : 0;

            return [$avgTungguMenit, $avgLayananMenit];
        };

        // 30 Hari Terakhir
        $dates30 = []; $total30 = []; $selesai30 = []; $avgTunggu30 = []; $avgLayanan30 = [];
        $p30 = Carbon::now('Asia/Jakarta')->subDays(29)->daysUntil(Carbon::now('Asia/Jakarta'));
        foreach ($p30 as $d) {
            $tgl = $d->format('Y-m-d');
            $dates30[] = $d->format('d M');
            $tks = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('Y-m-d') === $tgl);
            $total30[] = $tks->count();
            $selesai30[] = $tks->where('status', 'Selesai')->count();
            [$tunggu, $layanan] = $calcSlaMetrics($tks);
            $avgTunggu30[] = $tunggu;
            $avgLayanan30[] = $layanan;
        }

        // WTD
        $datesWtd = []; $totalWtd = []; $selesaiWtd = []; $avgTungguWtd = []; $avgLayananWtd = [];
        $pWtd = Carbon::now('Asia/Jakarta')->startOfWeek()->daysUntil(Carbon::now('Asia/Jakarta'));
        foreach ($pWtd as $d) {
            $tgl = $d->format('Y-m-d');
            $datesWtd[] = $d->translatedFormat('D, d M');
            $tks = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('Y-m-d') === $tgl);
            $totalWtd[] = $tks->count();
            $selesaiWtd[] = $tks->where('status', 'Selesai')->count();
            [$tunggu, $layanan] = $calcSlaMetrics($tks);
            $avgTungguWtd[] = $tunggu;
            $avgLayananWtd[] = $layanan;
        }

        // MTD
        $datesMtd = []; $totalMtd = []; $selesaiMtd = []; $avgTungguMtd = []; $avgLayananMtd = [];
        $pMtd = Carbon::now('Asia/Jakarta')->startOfMonth()->daysUntil(Carbon::now('Asia/Jakarta'));
        foreach ($pMtd as $d) {
            $tgl = $d->format('Y-m-d');
            $datesMtd[] = $d->format('d M');
            $tks = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('Y-m-d') === $tgl);
            $totalMtd[] = $tks->count();
            $selesaiMtd[] = $tks->where('status', 'Selesai')->count();
            [$tunggu, $layanan] = $calcSlaMetrics($tks);
            $avgTungguMtd[] = $tunggu;
            $avgLayananMtd[] = $layanan;
        }

        // MTM
        $datesMtm = []; $totalMtm = []; $selesaiMtm = []; $avgTungguMtm = []; $avgLayananMtm = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now('Asia/Jakarta')->subMonths($i);
            $monthKey = $m->format('Y-m');
            $datesMtm[] = $m->translatedFormat('M Y');
            $tks = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('Y-m') === $monthKey);
            $totalMtm[] = $tks->count();
            $selesaiMtm[] = $tks->where('status', 'Selesai')->count();
            [$tunggu, $layanan] = $calcSlaMetrics($tks);
            $avgTungguMtm[] = $tunggu;
            $avgLayananMtm[] = $layanan;
        }

        $chartDataSets = [
            'wtd'    => ['dates' => $datesWtd, 'total' => $totalWtd, 'selesai' => $selesaiWtd, 'avg_tunggu' => $avgTungguWtd, 'avg_layanan' => $avgLayananWtd],
            'mtd'    => ['dates' => $datesMtd, 'total' => $totalMtd, 'selesai' => $selesaiMtd, 'avg_tunggu' => $avgTungguMtd, 'avg_layanan' => $avgLayananMtd],
            'mtm'    => ['dates' => $datesMtm, 'total' => $totalMtm, 'selesai' => $selesaiMtm, 'avg_tunggu' => $avgTungguMtm, 'avg_layanan' => $avgLayananMtm],
            'last30' => ['dates' => $dates30, 'total' => $total30, 'selesai' => $selesai30, 'avg_tunggu' => $avgTunggu30, 'avg_layanan' => $avgLayanan30],
        ];

        // Status Ratio
        $statusRatioData = [
            'Selesai'    => $allFilteredTickets->where('status', 'Selesai')->count(),
            'Menunggu'   => $allFilteredTickets->where('status', 'Menunggu')->count(),
            'Diproses'   => $allFilteredTickets->where('status', 'Diproses')->count(),
            'Ditransfer' => $allFilteredTickets->where('status', 'Ditransfer')->count(),
            'No Show'    => $allFilteredTickets->where('status', 'No Show')->count(),
            'Batal'      => $allFilteredTickets->where('status', 'Batal')->count(),
        ];

        // -------------------------------------------------------------
        // REKAP RIWAYAT OPERASIONAL
        // -------------------------------------------------------------
        $historyBulanan = [];
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now('Asia/Jakarta')->subDays($i);
            $tglStr = $date->format('Y-m-d');

            $tks = $allTickets->filter(fn($t) => Carbon::parse($t->waktu_dibuat)->timezone('Asia/Jakarta')->format('Y-m-d') === $tglStr);

            $tiketMasuk      = $tks->count();
            $tiketDilayani   = $tks->whereIn('status', ['Diproses', 'Selesai', 'Ditransfer'])->count();
            $sudahDiproses   = $tks->where('status', 'Selesai')->count();
            $belumDiproses   = $tks->where('status', 'Menunggu')->count();
            $totalOmsetHari  = $tks->where('status', 'Selesai')->sum('nominal_pembayaran');

            $breakdownLayanan = [];
            foreach ($tks->groupBy('layanan_id') as $layId => $items) {
                $namaLayanan = $items->first()->layanan->nama_layanan ?? 'Layanan Umum';
                $breakdownLayanan[] = [
                    'nama'   => $namaLayanan,
                    'jumlah' => $items->count()
                ];
            }

            $historyBulanan[] = [
                'tanggal'           => $date->translatedFormat('d F Y'),
                'raw_date'          => $tglStr,
                'tiket_masuk'       => $tiketMasuk,
                'tiket_dilayani'    => $tiketDilayani,
                'sudah_diproses'    => $sudahDiproses,
                'belum_diproses'    => $belumDiproses,
                'breakdown_layanan' => $breakdownLayanan,
                'total_omset'       => $totalOmsetHari
            ];
        }

        // Distribusi Kategori Layanan
        $distribusiLayanan = Layanan::all()->map(function($layanan) use ($allFilteredTickets) {
            $item = new stdClass();
            $item->nama  = $layanan->nama_layanan;
            $item->total = $allFilteredTickets->where('layanan_id', $layanan->id)->count();
            return $item;
        })->sortByDesc('total')->values();

        // Staf CS & Meja (Diperbarui untuk mengakomodasi Admin)
        $allUsers = User::orderBy('role', 'asc')->get();
        $mejaCs  = [];

        foreach ($allUsers as $u) {
            $tiketAktif = TiketAntrian::with(['layanan', 'subLayanan'])
                ->where('user_id', $u->id)
                ->where('status', 'Diproses')
                ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                ->first();

            $querySelesai = TiketAntrian::where('user_id', $u->id)->where('status', 'Selesai');
            if ($startDate && $endDate) {
                $querySelesai->whereBetween('waktu_dibuat', [$startDate, $endDate]);
            }
            $totalSelesai = $querySelesai->count();

            $statusText = 'Offline';
            if ($u->is_active) {
                $statusText = $tiketAktif ? 'Melayani Pelanggan' : 'Aktif';
            }

            $stafObj = new stdClass();
            $stafObj->id             = $u->id;
            $stafObj->inisial        = strtoupper(substr($u->nama_lengkap, 0, 2));
            $stafObj->nama           = $u->nama_lengkap;
            $stafObj->nomor_meja     = str_pad((string)($u->nomor_meja ?? 0), 2, '0', STR_PAD_LEFT);
            $stafObj->is_active      = (bool) $u->is_active;
            $stafObj->status         = $statusText;
            $stafObj->role           = strtoupper($u->role);
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

        return view('admin.index', compact(
            'totalHariIni', 'menunggu', 'ditransfer', 'noShowCount', 'avgSla', 'avgDurasiLayananText', 
            'avgWaktuTungguText', 'avgRating', 'totalOmset', 'distribusiLayanan', 
            'statusRatioData', 'analisisOtomatis', 'mejaCs', 'allUsers', 
            'masterMejas', 'allFilteredTickets', 'chartDataSets', 
            'layananId', 'layanans', 'historyBulanan'
        ))->with([
            'startDate' => $startDateOut,
            'endDate'   => $endDateOut
        ]);
    }

    public function storeLayanan(Request $request)
    {
        $request->validate(['nama_layanan' => 'required|string|max:255']);
        Layanan::create(['nama_layanan' => $request->input('nama_layanan'), 'is_active' => true]);
        return back()->with('success', 'Kategori Layanan Utama berhasil ditambahkan.');
    }

    public function destroyLayanan(int $id)
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
            'layanan_id'       => $request->input('layanan_id'),
            'nama_sub_layanan' => $request->input('nama_sub_layanan'),
            'is_active'        => true
        ]);
        return back()->with('success', 'Sub-Layanan Sektoral berhasil ditambahkan.');
    }

    public function destroySubLayanan(int $id)
    {
        $sub = SubLayanan::findOrFail($id);
        $sub->delete();
        return back()->with('success', 'Sub-Layanan Sektoral berhasil dihapus.');
    }

    public function cetakPdf(Request $request): View
    {
        $period = $request->input('period', 'all');
        $layananId = $request->input('layanan_id');

        $query = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan', 'cs']);

        if ($period !== 'all' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'), 'Asia/Jakarta')->startOfDay();
            $endDate   = Carbon::parse($request->input('end_date'), 'Asia/Jakarta')->endOfDay();
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

        $includeSummary   = $request->boolean('inc_summary');
        $includeCharts    = $request->boolean('inc_charts');
        $includeSlaCharts = $request->boolean('inc_sla_charts');
        $includeCsChart   = $request->boolean('inc_cs_chart');
        $includeTable     = $request->boolean('inc_table');

        $chartLineBase64 = $request->input('chart_line_base64');
        $chartPieBase64  = $request->input('chart_pie_base64');

        return view('admin.pdf_report', compact(
            'tickets', 'startDate', 'endDate', 'totalOmset', 
            'includeSummary', 'includeCharts', 'includeSlaCharts', 'includeCsChart', 'includeTable', 
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

        $columns = [
            'ID', 'Kode Tiket', 'Nomor Display', 'Nama Pelanggan', 'No HP', 'Email', 'No Indibiz', 
            'Layanan Utama', 'Sub Layanan', 'CS Melayani', 'Status', 'Metode Bayar', 'Nominal (Rp)', 
            'Catatan CS', 'Rating Pelanggan', 'Feedback', 'Waktu Ambil Tiket', 'Waktu Dipanggil', 'Waktu Selesai', 
            'Waktu Tunggu (detik)', 'Waktu Layanan (detik)'
        ];

        $callback = function() use($tickets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tickets as $ticket) {
                $waktuAmbil     = $ticket->waktu_dibuat ? Carbon::parse($ticket->waktu_dibuat) : null;
                $waktuDipanggil = ($ticket->waktu_dipanggil ?? $ticket->waktu_diproses) ? Carbon::parse($ticket->waktu_dipanggil ?? $ticket->waktu_diproses) : null;
                $waktuSelesai   = ($ticket->waktu_selesai_konsul ?? $ticket->waktu_selesai) ? Carbon::parse($ticket->waktu_selesai_konsul ?? $ticket->waktu_selesai) : null;

                $durasiTungguDetik  = $ticket->waktu_tunggu ?? (($waktuAmbil && $waktuDipanggil) ? $waktuAmbil->diffInSeconds($waktuDipanggil) : 0);
                $durasiLayananDetik = $ticket->waktu_layanan ?? (($waktuDipanggil && $waktuSelesai) ? $waktuDipanggil->diffInSeconds($waktuSelesai) : 0);

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
                    $ticket->catatan_cs ?? $ticket->ringkasan_solusi ?? '-',
                    $ticket->rating ?? '-',
                    $ticket->feedback ?? '-',
                    $ticket->waktu_dibuat ? Carbon::parse($ticket->waktu_dibuat)->format('d/m/Y H:i:s') : '-',
                    $waktuDipanggil ? $waktuDipanggil->format('d/m/Y H:i:s') : '-',
                    $waktuSelesai ? $waktuSelesai->format('d/m/Y H:i:s') : '-',
                    $durasiTungguDetik,
                    $durasiLayananDetik
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // -------------------------------------------------------------
    // FUNGSI MANAJEMEN AKUN (TAMBAH, EDIT, HAPUS)
    // -------------------------------------------------------------
    public function storeStaff(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users',
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:admin,cs'
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'is_active'    => true,
            'nomor_meja'   => 0
        ]);

        return back()->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function updateStaff(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username,'.$id,
            'role'         => 'required|in:admin,cs'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'username'     => $request->username,
            'role'         => $request->role,
        ]);

        return back()->with('success', 'Detail profil akun berhasil diperbarui.');
    }

    public function updatePasswordStaff(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6'
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password akun berhasil diganti.');
    }

    public function toggleStaffStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'ditangguhkan (disable)';
        return back()->with('success', "Akun berhasil {$statusStr}.");
    }

    public function destroyStaff($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'Akun pengguna berhasil dihapus permanen.');
    }
}