<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
use App\Models\MasterMeja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DisplayController extends Controller
{
    public function index(): View
    {
        return view('antrean.display');
    }

    public function getDataJson(Request $request)
    {
        try {
            $today = Carbon::today('Asia/Jakarta');

            // Ambil tiket yang sedang diproses
            $sedangDipanggil = TiketAntrian::with(['cs', 'layanan'])
                ->where('status', 'Diproses')
                ->whereDate('waktu_dibuat', $today)
                ->orderBy('waktu_dipanggil', 'desc')
                ->get();

            $antreanMenunggu = TiketAntrian::with(['layanan', 'pelanggan'])
                ->where('status', 'Menunggu')
                ->whereDate('waktu_dibuat', $today)
                ->oldest('id')
                ->take(5)
                ->get();

            // HITUNG RATA-RATA DURASI PELAYANAN HARI INI (dalam menit)
            $tiketSelesaiHariIni = TiketAntrian::where('status', 'Selesai')
                ->whereDate('waktu_dibuat', $today)
                ->whereNotNull('waktu_mulai_konsul')
                ->whereNotNull('waktu_selesai_konsul')
                ->get();

            $totalDurasiMenit = 0;
            $jumlahTiketSelesai = $tiketSelesaiHariIni->count();

            foreach ($tiketSelesaiHariIni as $t) {
                $mulai = Carbon::parse($t->waktu_mulai_konsul);
                $selesai = Carbon::parse($t->waktu_selesai_konsul);
                $totalDurasiMenit += $mulai->diffInMinutes($selesai);
            }

            // Jika belum ada data transaksi hari ini, set null/0 agar UI tidak menampilkan angka bohong
            $avgDurasiMenit = $jumlahTiketSelesai > 0 ? (int) round($totalDurasiMenit / $jumlahTiketSelesai) : null;

            // Ambil seluruh master meja yang tersedia
            $masterMeja = MasterMeja::where('is_available', true)->orderBy('nomor_meja', 'asc')->get();

            // Ambil CS yang sedang aktif/online menduduki meja
            $activeUsers = User::where('is_active', true)
                ->whereNotNull('nomor_meja')
                ->where('nomor_meja', '!=', 0)
                ->get()
                ->keyBy('nomor_meja');

            $mejaList = $masterMeja->map(function ($meja) use ($activeUsers, $sedangDipanggil) {
                $userCS = $activeUsers->get($meja->nomor_meja);
                $tiketAktif = $sedangDipanggil->first(function ($tiket) use ($meja) {
                    return $tiket->cs && $tiket->cs->nomor_meja == $meja->nomor_meja;
                });

                return [
                    'nomor_meja'        => $meja->nomor_meja,
                    'nama_meja'         => $meja->nama_meja,
                    'is_occupied'       => !is_null($userCS),
                    'nama_cs'           => $userCS ? $userCS->nama_lengkap : null,
                    'tiket_aktif'       => $tiketAktif ? $tiketAktif->nomor_antrian : null,
                    'nama_layanan'      => $tiketAktif && $tiketAktif->layanan ? $tiketAktif->layanan->nama_layanan : null,
                    'is_calling'        => !is_null($tiketAktif),
                    'waktu_diproses'    => $tiketAktif ? (string) $tiketAktif->waktu_diproses : null,
                    'waktu_mulai_konsul'=> $tiketAktif ? (string) ($tiketAktif->waktu_mulai_konsul ?? $tiketAktif->waktu_diproses) : null,
                    'waktu_dipanggil'   => $tiketAktif ? (string) ($tiketAktif->waktu_dipanggil ?? $tiketAktif->waktu_diproses) : null,
                ];
            });

            // HITUNG ESTIMASI PENUMPUKAN WAKTU TUNGGU UNTUK ANTREAN BERIKUTNYA
            $jumlahCSAktif = max(1, $activeUsers->count());
            $durasiAcuan = $avgDurasiMenit ?? 10; // Untuk estimasi jika antrean kosong/baru
            
            $listAntreanMenungguWithEstimasi = $antreanMenunggu->map(function ($item, $index) use ($durasiAcuan, $jumlahCSAktif) {
                $estimasiMenit = ceil(($index + 1) / $jumlahCSAktif) * $durasiAcuan;
                $item->estimasi_tunggu_menit = $estimasiMenit;
                return $item;
            });

            return response()->json([
                'sedangDipanggil' => $sedangDipanggil,
                'antreanMenunggu' => $listAntreanMenungguWithEstimasi,
                'mejaList'        => $mejaList,
                'avgDurasiMenit'  => $avgDurasiMenit,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function ttsElevenLabs(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:500',
        ]);

        $apiKey = env('ELEVENLABS_API_KEY');
        $voiceId = env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM');

        if (!$apiKey) {
            return response()->json(['error' => 'ElevenLabs API Key belum dikonfigurasi.'], 500);
        }

        $url = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}";

        $response = Http::withHeaders([
            'xi-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'audio/mpeg',
        ])->post($url, [
            'text' => $request->text,
            'model_id' => 'eleven_multilingual_v2',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
                'style' => 0.0,
                'use_speaker_boost' => true
            ]
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Gagal mengambil audio dari ElevenLabs.'], 500);
        }

        return response($response->body(), 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }
}