<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DisplayController extends Controller
{
    /**
     * Menampilkan Halaman Utama Monitor TV Antrean
     */
    public function index(): View
    {
        return view('antrean.display');
    }

    /**
     * Mengirim Data JSON untuk AJAX Poling di Layar TV
     */
    public function getDataJson(Request $request)
    {
        $today = Carbon::today('Asia/Jakarta');

        // 1. Ambil antrean yang sedang aktif dipanggil (status 'Diproses') urutkan dari yang terbaru dipanggil
        $sedangDipanggil = TiketAntrian::with(['cs', 'layanan'])
            ->where('status', 'Diproses')
            ->whereDate('waktu_dibuat', $today)
            ->latest('updated_at')
            ->get();

        // 2. Ambil antrean berikutnya yang masih 'Menunggu' (maksimal 5 antrean teratas)
        $antreanMenunggu = TiketAntrian::with(['layanan'])
            ->where('status', 'Menunggu')
            ->whereDate('waktu_dibuat', $today)
            ->oldest('id')
            ->take(5)
            ->get();

        return response()->json([
            'sedangDipanggil' => $sedangDipanggil,
            'antreanMenunggu' => $antreanMenunggu,
        ]);
    }
}