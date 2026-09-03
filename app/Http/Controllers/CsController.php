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
        $user->nomor_meja = null;
        $user->save();
        session()->forget('meja_terpilih');
    }

    public function pingHeartbeat()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user) {
            $user->update([
                'is_active'    => true,
                'last_seen_at' => Carbon::now('Asia/Jakarta')
            ]);
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['status' => 'unauthorized'], 401);
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
        $user->nomor_meja   = $request->nomor_meja;
        $user->is_active    = true;
        $user->last_seen_at = Carbon::now('Asia/Jakarta');
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

        if (!$isSpectator) {
            $user->is_active    = true;
            $user->last_seen_at = Carbon::now('Asia/Jakarta');
            $user->save();
        }

        $antreanMenunggu = TiketAntrian::with(['pelanggan', 'layanan'])
                            ->whereDate('waktu_dibuat', $hariIni)
                            ->where('status', 'Menunggu')
                            ->orderBy('id', 'asc')
                            ->get();

        $antreanAktif = TiketAntrian::with(['pelanggan', 'layanan'])
                            ->whereDate('waktu_dibuat', $hariIni)
                            ->where('status', 'Diproses')
                            ->where('user_id', $user->id)
                            ->first();

        return view('cs.index', compact('antreanMenunggu', 'antreanAktif', 'isSpectator', 'nomorMejaTerpilih'));
    }

    public function panggilSelanjutnya()
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda tidak valid.');
        }

        $user = Auth::user();

        // Cek jika CS masih punya tiket aktif
        $cekAktif = TiketAntrian::where('status', 'Diproses')
            ->where('user_id', $user->id)
            ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
            ->first();

        if ($cekAktif) {
            return back()->with('error', 'Selesaikan tiket ' . $cekAktif->nomor_antrian . ' terlebih dahulu!');
        }

        // Ambil tiket antrean teratas hari ini
        $tiket = TiketAntrian::whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                    ->where('status', 'Menunggu')
                    ->orderBy('id', 'asc')
                    ->first();

        if ($tiket) {
            // Atomic Update untuk mencegah bentrokan pemanggilan tanpa menimbulkan kuncian deadlock
            $updated = TiketAntrian::where('id', $tiket->id)
                ->where('status', 'Menunggu')
                ->update([
                    'status'           => 'Diproses',
                    'user_id'          => $user->id,
                    'waktu_diproses'   => Carbon::now('Asia/Jakarta'),
                    'jumlah_dipanggil' => DB::raw('jumlah_dipanggil + 1')
                ]);

            if ($updated) {
                return back()->with('success', "Memanggil antrean {$tiket->nomor_antrian}");
            }
        }

        return back()->with('error', 'Tidak ada antrean menunggu saat ini.');
    }

    public function panggilSpesifik(int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda tidak valid.');
        }

        $user = Auth::user();

        $cekAktif = TiketAntrian::where('status', 'Diproses')
            ->where('user_id', $user->id)
            ->whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
            ->first();

        if ($cekAktif) {
            return back()->with('error', 'Selesaikan tiket aktif terlebih dahulu!');
        }

        $updated = TiketAntrian::where('id', $id)
            ->where('status', 'Menunggu')
            ->update([
                'status'           => 'Diproses',
                'user_id'          => $user->id,
                'waktu_diproses'   => Carbon::now('Asia/Jakarta'),
                'jumlah_dipanggil' => DB::raw('jumlah_dipanggil + 1')
            ]);

        if ($updated) {
            return back()->with('success', "Memanggil antrean spesifik.");
        }

        return back()->with('error', 'Tiket sudah tidak tersedia atau sedang diproses oleh CS lain.');
    }

    public function batalAtauKembalikan(int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda tidak valid.');
        }

        $user = Auth::user();
        $tiket = TiketAntrian::findOrFail($id);

        $tiket->increment('jumlah_dipanggil');

        if ($tiket->jumlah_dipanggil >= 2) {
            $tiket->update([
                'status'  => 'Batal',
                'user_id' => $user->id
            ]);
            return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} dibatalkan karena tidak hadir 2x.");
        }

        $tiket->update([
            'status'       => 'Menunggu',
            'user_id'      => null,
            'waktu_dibuat' => Carbon::now('Asia/Jakarta')
        ]);

        return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} dipindahkan ke urutan antrean paling belakang.");
    }

    public function selesaikanTiket(Request $request, int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda tidak valid.');
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