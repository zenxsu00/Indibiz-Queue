<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
use App\Models\User;
use App\Models\MasterMeja;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CsController extends Controller
{
    private function checkValidMeja()
    {
        /** @var User $user */
        $user = User::find(Auth::id());

        $nomorMeja = $user->nomor_meja ?? session('meja_terpilih');
        $isSpectator = (empty($nomorMeja) || $nomorMeja == 0) && $user->role === 'admin';

        if ($isSpectator) {
            return true;
        }

        if (empty($nomorMeja) || $nomorMeja == 0) {
            $this->resetUserState($user);
            return false;
        }

        $mejaValid = MasterMeja::where('nomor_meja', $nomorMeja)
                        ->where('is_available', true)
                        ->exists();

        if (!$mejaValid) {
            $this->resetUserState($user);
            return false;
        }

        return true;
    }

    private function resetUserState($user)
    {
        // Tetap biarkan is_active = true agar akun terikat bahwa sedang login, hanya kosongkan mejanya
        $user->nomor_meja = null;
        $user->save();
        session()->forget('meja_terpilih');
    }

    public function selectMeja()
    {
        $masterMejas = MasterMeja::where('is_available', true)->orderBy('nomor_meja', 'asc')->get();
        
        $mejaTerpakai = User::where('is_active', true)
            ->whereNotNull('nomor_meja')
            ->where('nomor_meja', '!=', 0)
            ->where('id', '!=', Auth::id())
            ->pluck('nomor_meja')
            ->toArray();

        return view('cs.select_meja', compact('masterMejas', 'mejaTerpakai'));
    }

    public function setMeja(Request $request)
    {
        $request->validate([
            'nomor_meja' => 'required|integer|gt:0',
        ]);

        // Cek apakah meja sedang dipakai CS lain yang sedang aktif
        $mejaSedangDipakai = User::where('is_active', true)
            ->where('nomor_meja', $request->nomor_meja)
            ->where('id', '!=', Auth::id())
            ->exists();

        if ($mejaSedangDipakai) {
            return redirect()->route('cs.select-meja')->with('error', "Meja M{$request->nomor_meja} sedang digunakan oleh petugas CS lain.");
        }

        $meja = MasterMeja::where('nomor_meja', $request->nomor_meja)->where('is_available', true)->first();
        if (!$meja) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja yang dipilih tidak tersedia atau telah dihapus.');
        }

        /** @var User $user */
        $user = Auth::user();
        $user->nomor_meja = $request->nomor_meja;
        $user->is_active  = true;
        $user->save();

        session(['meja_terpilih' => $request->nomor_meja]);

        return redirect()->route('cs.index')->with('success', "Berhasil masuk ke Loket M{$request->nomor_meja}");
    }

    public function index()
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus atau dinonaktifkan oleh Admin. Silakan pilih meja lain.');
        }

        $hariIni = Carbon::today('Asia/Jakarta');
        /** @var User $user */
        $user = User::find(Auth::id());

        $nomorMejaTerpilih = $user->nomor_meja ?? session('meja_terpilih');
        $isSpectator = (empty($nomorMejaTerpilih) || $nomorMejaTerpilih == 0) && $user->role === 'admin';

        if (!$isSpectator && !$user->is_active) {
            $user->is_active = true;
            $user->save();
        }

        $antreanMenunggu = TiketAntrian::with(['pelanggan', 'layanan'])
                            ->whereDate('waktu_dibuat', $hariIni)
                            ->where('status', 'Menunggu')
                            ->orderBy('waktu_dibuat', 'asc')
                            ->get();

        $antreanAktif = TiketAntrian::with(['pelanggan', 'layanan'])
                            ->whereDate('waktu_dibuat', $hariIni)
                            ->where('status', 'Diproses')
                            ->where('user_id', $user->id)
                            ->first();

        return view('cs.index', compact('antreanMenunggu', 'antreanAktif', 'isSpectator', 'nomorMejaTerpilih'));
    }

    // PEMBANGGILAN AMAN DENGAN DATABASE LOCKING (Mencegah CS 2 Terkunci)
    public function panggilSelanjutnya()
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        $user = Auth::user();

        // Cek jika CS masih punya tiket aktif
        $cekAktif = TiketAntrian::where('status', 'Diproses')->where('user_id', $user->id)->first();
        if ($cekAktif) {
            return back()->with('error', 'Selesaikan tiket aktif terlebih dahulu!');
        }

        DB::beginTransaction();
        try {
            // Ambil antrean teratas dan kunci baris data (lockForUpdate)
            $tiket = TiketAntrian::whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                        ->where('status', 'Menunggu')
                        ->orderBy('waktu_dibuat', 'asc')
                        ->lockForUpdate()
                        ->first();

            if ($tiket) {
                $tiket->status = 'Diproses';
                $tiket->user_id = $user->id;
                $tiket->waktu_diproses = Carbon::now('Asia/Jakarta');
                $tiket->jumlah_dipanggil = ($tiket->jumlah_dipanggil ?? 0) + 1;
                $tiket->save();
            }

            DB::commit();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memanggil antrean: ' . $e->getMessage());
        }
    }

    // PEMBANGGILAN SPESIFIK
    public function panggilSpesifik(int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        $user = Auth::user();

        $cekAktif = TiketAntrian::where('status', 'Diproses')->where('user_id', $user->id)->first();
        if ($cekAktif) {
            return back()->with('error', 'Selesaikan tiket aktif terlebih dahulu!');
        }

        DB::beginTransaction();
        try {
            $tiket = TiketAntrian::where('id', $id)->lockForUpdate()->firstOrFail();
            
            if ($tiket->status == 'Menunggu') {
                $tiket->status = 'Diproses';
                $tiket->user_id = $user->id;
                $tiket->waktu_diproses = Carbon::now('Asia/Jakarta');
                $tiket->jumlah_dipanggil = ($tiket->jumlah_dipanggil ?? 0) + 1;
                $tiket->save();
            }

            DB::commit();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memanggil tiket.');
        }
    }

    public function batalAtauKembalikan(int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        $user = Auth::user();
        $tiket = TiketAntrian::findOrFail($id);

        $tiket->increment('jumlah_dipanggil');

        if ($tiket->jumlah_dipanggil >= 2) {
            $tiket->update([
                'status' => 'Batal',
                'user_id' => $user->id
            ]);
            return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} dibatalkan karena tidak hadir 2x.");
        }

        $tiket->update([
            'status' => 'Menunggu',
            'user_id' => null,
            'waktu_dibuat' => Carbon::now('Asia/Jakarta')
        ]);

        return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} dipindahkan ke urutan antrean paling belakang.");
    }

    public function selesaikanTiket(Request $request, int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        $user = Auth::user();
        $tiket = TiketAntrian::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        
        $request->validate([
            'keluhan_final'      => 'nullable|string',
            'catatan_cs'         => 'nullable|string',
            'metode_pembayaran'  => 'nullable|string',
            'nominal_pembayaran' => 'nullable|numeric|min:0',
            'bukti_pembayaran'   => 'nullable|string|max:100',
        ]);

        $tiket->update([
            'status'             => 'Selesai',
            'keluhan_final'      => $request->keluhan_final,
            'catatan_cs'         => $request->catatan_cs,
            'metode_pembayaran'  => $request->metode_pembayaran ?? 'Tanpa Transaksi',
            'nominal_pembayaran' => $request->nominal_pembayaran ?? 0,
            'bukti_pembayaran'   => $request->bukti_pembayaran,
            'waktu_selesai'      => Carbon::now('Asia/Jakarta'),
        ]);

        return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} berhasil diselesaikan.");
    }

    public function leaveConsole()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->is_active = false;
        $user->nomor_meja = null;
        $user->save();

        session()->forget('meja_terpilih');

        return redirect()->route('admin.dashboard');
    }
}