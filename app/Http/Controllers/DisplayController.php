<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
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
        $today = Carbon::today('Asia/Jakarta');

        $sedangDipanggil = TiketAntrian::with(['cs', 'layanan'])
            ->where('status', 'Diproses')
            ->whereDate('waktu_dibuat', $today)
            ->latest('updated_at')
            ->get();

        $antreanMenunggu = TiketAntrian::with(['layanan', 'pelanggan'])
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

    /**
     * Endpoint Proxy TTS ElevenLabs AI
     */
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