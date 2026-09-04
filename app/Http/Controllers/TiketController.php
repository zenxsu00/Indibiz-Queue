<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\TiketAntrian;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;

class TiketController extends Controller
{
    /**
     * Memeriksa apakah ada CS yang sedang aktif dan bertugas di slot meja
     */
    private function checkIsOperational(): bool
    {
        return User::where('is_active', true)
            ->whereNotNull('nomor_meja')
            ->where('nomor_meja', '!=', 0)
            ->exists();
    }

    /**
     * Menampilkan Halaman Utama Pengambilan Tiket
     */
    public function index(): View
    {
        $isOperational = $this->checkIsOperational();
        $layanans = Layanan::where('is_active', true)->get();

        return view('antrean.index', compact('isOperational', 'layanans'));
    }

    /**
     * Memproses Pengambilan Tiket Antrean Baru
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. RATE LIMITER: Maksimal 1 request per 5 detik dari IP device yang sama
        $executed = RateLimiter::attempt(
            'ambil-tiket-ip:' . $request->ip(),
            $perMinute = 1,
            function() {
                // Callback kosong
            },
            $decaySeconds = 5
        );

        if (!$executed) {
            return back()->with('error', 'Harap tunggu 5 detik sebelum mengambil tiket antrean kembali.');
        }

        // 2. Proteksi Operasional: Wajib ada CS aktif di meja
        if (!$this->checkIsOperational()) {
            return back()->with('error', 'Maaf, loket layanan saat ini sedang tutup / tidak beroperasi.');
        }

        $request->validate([
            'nama'         => 'required|string|max:255',
            'no_hp'        => 'required|string|max:20',
            'layanan_id'   => 'required|exists:layanans,id',
            'keluhan_awal' => 'required|string',
            'alamat'       => 'nullable|string',
        ]);

        $pelanggan = Pelanggan::firstOrCreate(
            ['no_hp' => $request->no_hp],
            [
                'nama'   => $request->nama,
                'alamat' => $request->alamat,
            ]
        );

        $pelanggan->update([
            'nama'   => $request->nama,
            'alamat' => $request->alamat ?? $pelanggan->alamat,
        ]);

        $layanan = Layanan::findOrFail($request->layanan_id);
        $today = Carbon::today('Asia/Jakarta');

        // HITUNG GLOBAL URUTAN HARI INI TANPA FILTER LAYANAN_ID
        $urutanHariIni = TiketAntrian::whereDate('waktu_dibuat', $today)->count() + 1;

        $nomorUrut = str_pad((string)$urutanHariIni, 3, '0', STR_PAD_LEFT);
        $nomorAntrian = $layanan->kode_layanan . '-' . $nomorUrut;

        $kodeTiket = $today->format('dmY') . '-' . $layanan->kode_layanan . '-' . $nomorUrut;

        $tiket = TiketAntrian::create([
            'kode_tiket'     => $kodeTiket,
            'nomor_antrian'  => $nomorAntrian,
            'pelanggan_id'   => $pelanggan->id,
            'layanan_id'     => $layanan->id,
            'keluhan_awal'   => $request->keluhan_awal,
            'status'         => 'Menunggu',
            'waktu_dibuat'   => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('antrean.tiket', ['id' => $tiket->id]);
    }

    /**
     * Menampilkan Status Live Tiket Pelanggan
     */
    public function showTiket(int|string $id): View
    {
        $tiket = TiketAntrian::with(['pelanggan', 'layanan', 'cs'])->findOrFail($id);

        $sisaAntrean = 0;
        if ($tiket->status === 'Menunggu') {
            // Sisa antrean dihitung berdasarkan urutan global sebelum tiket ini
            $sisaAntrean = TiketAntrian::where('status', 'Menunggu')
                ->where('id', '<', $tiket->id)
                ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                ->count();
        }

        return view('antrean.tiket', compact('tiket', 'sisaAntrean'));
    }
}