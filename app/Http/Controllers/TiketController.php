<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\TiketAntrian;
use App\Models\JadwalOperasional;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class TiketController extends Controller
{
    /**
     * Memeriksa apakah operasional buka berdasarkan Jadwal Operasional Sistem
     */
    private function checkIsOperational(): array
    {
        $now = Carbon::now('Asia/Jakarta');
        $hariKe = $now->dayOfWeekIso; // 1 = Senin, 7 = Minggu

        $jadwalHariIni = JadwalOperasional::where('hari_ke', $hariKe)->first();

        if (!$jadwalHariIni || !$jadwalHariIni->is_buka) {
            return [
                'is_buka' => false,
                'pesan'   => 'Hari ini (' . $now->translatedFormat('l') . ') operasional antrean sedang libur / tidak beroperasi.'
            ];
        }

        $jamSekarang = $now->format('H:i:s');
        $jamBuka     = $jadwalHariIni->jam_buka;
        $jamTutup    = $jadwalHariIni->jam_tutup;

        if ($jamSekarang < $jamBuka || $jamSekarang > $jamTutup) {
            return [
                'is_buka' => false,
                'pesan'   => 'Layanan antrean hari ini beroperasi pukul ' . Carbon::parse($jamBuka)->format('H:i') . ' - ' . Carbon::parse($jamTutup)->format('H:i') . ' WIB.'
            ];
        }

        return [
            'is_buka' => true,
            'pesan'   => ''
        ];
    }

    /**
     * Menampilkan Halaman Utama Pengambilan Tiket
     */
    public function index(): View
    {
        $checkStatus   = $this->checkIsOperational();
        $isOperational = $checkStatus['is_buka'];
        $pesanTutup    = $checkStatus['pesan'];

        $layanans = Layanan::where('is_active', true)->get();

        return view('antrean.index', compact('isOperational', 'pesanTutup', 'layanans'));
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
            function() {},
            $decaySeconds = 5
        );

        if (!$executed) {
            return back()->with('error', 'Harap tunggu 5 detik sebelum mengambil tiket antrean kembali.');
        }

        // 2. Proteksi Operasional: Pengecekan Jadwal Sistem
        $checkStatus = $this->checkIsOperational();
        if (!$checkStatus['is_buka']) {
            return back()->with('error', 'Maaf, ' . $checkStatus['pesan']);
        }

        // 3. Validasi Input Form (Strict Max Length & Limit No Indibiz Max 12)
        $request->validate([
            'nama'         => 'required|string|max:100',
            'no_hp'        => 'required|string|max:15',
            'no_indibiz'   => 'nullable|string|max:12', // PENAMBAHAN: LIMIT INDIBIZ 12 KARAKTER
            'layanan_id'   => 'required|exists:layanans,id',
            'keluhan_awal' => 'required|string|max:500',
            'alamat'       => 'nullable|string|max:255',
        ], [
            'no_indibiz.max' => 'Nomor Indibiz tidak boleh melebihi 12 karakter.',
            'nama.max'       => 'Nama maksimal 100 karakter.',
            'no_hp.max'      => 'Nomor HP maksimal 15 karakter.'
        ]);

        return DB::transaction(function () use ($request) {
            $pelanggan = Pelanggan::firstOrCreate(
                ['no_hp' => $request->no_hp],
                [
                    'nama'       => $request->nama,
                    'no_indibiz' => $request->no_indibiz,
                    'alamat'     => $request->alamat,
                ]
            );

            $pelanggan->update([
                'nama'       => $request->nama,
                'no_indibiz' => $request->no_indibiz ?? $pelanggan->no_indibiz,
                'alamat'     => $request->alamat ?? $pelanggan->alamat,
            ]);

            $layanan = Layanan::findOrFail($request->layanan_id);
            $today   = Carbon::today('Asia/Jakarta');

            // LOCK FOR UPDATE MENCEGAH DUPLIKASI NOMOR ANTREAN BENTROK
            $urutanHariIni = TiketAntrian::whereDate('waktu_dibuat', $today)
                ->lockForUpdate()
                ->count() + 1;

            $nomorUrut    = str_pad((string)$urutanHariIni, 3, '0', STR_PAD_LEFT);
            $nomorAntrian = $layanan->kode_layanan . '-' . $nomorUrut;
            $kodeTiket    = $today->format('dmY') . '-' . $layanan->kode_layanan . '-' . $nomorUrut;

            $tiket = TiketAntrian::create([
                'kode_tiket'    => $kodeTiket,
                'nomor_antrian' => $nomorAntrian,
                'pelanggan_id'  => $pelanggan->id,
                'layanan_id'    => $layanan->id,
                'keluhan_awal'  => $request->keluhan_awal,
                'status'        => 'Menunggu',
                'waktu_dibuat'  => Carbon::now('Asia/Jakarta'),
            ]);

            return redirect()->route('antrean.tiket', ['id' => $tiket->id]);
        });
    }

    /**
     * Menampilkan Status Live Tiket Pelanggan
     */
    public function showTiket(int|string $id): View
    {
        $tiket = TiketAntrian::with(['pelanggan', 'layanan', 'cs'])->findOrFail($id);

        $sisaAntrean = 0;
        if ($tiket->status === 'Menunggu') {
            $sisaAntrean = TiketAntrian::where('status', 'Menunggu')
                ->where('id', '<', $tiket->id)
                ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                ->count();
        }

        return view('antrean.tiket', compact('tiket', 'sisaAntrean'));
    }
}