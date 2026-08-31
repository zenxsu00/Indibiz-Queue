use App\Models\Antrean;

Route::get('/antrean-aktif', function () {
    $antrean = Antrean::where('status', 'dipanggil')->first();
    
    if (!$antrean) {
        return response()->json(['success' => false]);
    }

    return response()->json([
        'success' => true,
        'nomor_antrian' => $antrean->nomor_antrian,
        // Kirim waktu dalam format ISO 8601 (UTC)
        'waktu_dibuat_iso' => \Carbon\Carbon::parse($antrean->waktu_dibuat)->toIso8601String(),
    ]);
});