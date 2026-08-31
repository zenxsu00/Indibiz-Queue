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
use Illuminate\Support\Facades\RateLimiter; // <-- PROTEKSI RATE LIMITING

class TiketController extends Controller
{
    /**
     * Menampilkan Halaman Utama Pengambilan Tiket
     */
    public function index(): View
    {
        // Cek jika ada minimal 1 petugas (Admin atau CS) yang sedang login/aktif
        $isOperational = User::where('is_active', true)->exists();
        $layanans = Layanan::where('is_active', true)->get();

        return view('antrean.index', compact('isOperational', 'layanans'));
    }

    /**
     * Memproses Pengambilan Tiket Antrean Baru
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. PROTEKSI BACKEND RATE LIMITER: Maksimal 1 request per 5 detik dari IP device yang sama
        $executed = RateLimiter::attempt(
            'ambil-tiket-ip:' . $request->ip(),
            $perMinute = 1,
            function() {
                // Callback kosong
            },
            $decaySeconds = 5 // Cooldown 5 detik
        );

        if (!$executed) {
            return back()->with('error', 'Harap tunggu 5 detik sebelum mengambil tiket antrean kembali.');
        }

        // 2. Proteksi Backend: Cek keberadaan petugas aktif (Admin / CS)
        $isOperational = User::where('is_active', true)->exists();
        if (!$isOperational) {
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

        $urutanHariIni = TiketAntrian::where('layanan_id', $layanan->id)
            ->whereDate('waktu_dibuat', $today)
            ->count() + 1;

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
            $sisaAntrean = TiketAntrian::where('layanan_id', $tiket->layanan_id)
                ->where('status', 'Menunggu')
                ->where('id', '<', $tiket->id)
                ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                ->count();
        }

        return view('antrean.tiket', compact('tiket', 'sisaAntrean'));
    }
}