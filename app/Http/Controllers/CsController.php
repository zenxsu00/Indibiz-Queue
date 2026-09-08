<?php

namespace App\Http\Controllers;

use App\Models\TiketAntrian;
use App\Models\User;
use App\Models\MasterMeja;
use App\Models\Layanan;
use App\Models\SubLayanan;
use App\Models\CsActiveLog;
use App\Events\TiketDipanggil;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

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

    private function resetUserState(User $user)
    {
        $this->closeActiveLog($user->id);
        $user->is_active = false;
        $user->nomor_meja = null;
        $user->save();
        session()->forget('meja_terpilih');
    }

    private function openActiveLog($userId)
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $openLog = CsActiveLog::where('user_id', $userId)
            ->where('tanggal', $today)
            ->whereNull('jam_selesai')
            ->first();

        if (!$openLog) {
            CsActiveLog::create([
                'user_id'    => $userId,
                'tanggal'    => $today,
                'jam_mulai'  => $now,
                'jam_selesai' => null,
                'durasi_menit' => 0,
            ]);
        }
    }

    private function closeActiveLog($userId)
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $openLog = CsActiveLog::where('user_id', $userId)
            ->where('tanggal', $today)
            ->whereNull('jam_selesai')
            ->first();

        if ($openLog) {
            $jamMulai = Carbon::parse($openLog->jam_mulai);
            $durasiMenit = $jamMulai->diffInMinutes($now);

            $openLog->update([
                'jam_selesai'  => $now,
                'durasi_menit' => $durasiMenit,
            ]);
        }
    }

    private function safeBroadcast(?TiketAntrian $tiket)
    {
        try {
            if ($tiket) {
                broadcast(new TiketDipanggil($tiket))->toOthers();
            }
        } catch (Throwable $e) {
            logger()->error("Broadcast Error: " . $e->getMessage());
        }
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
        $user->nomor_meja = $request->nomor_meja;
        $user->is_active  = true;
        $user->save();

        session(['meja_terpilih' => $request->nomor_meja]);
        $this->openActiveLog($user->id);

        return redirect()->route('cs.index')->with('success', "Berhasil masuk ke Loket M{$request->nomor_meja}");
    }

    public function index(Request $request)
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
            $this->openActiveLog($user->id);
        }

        $antreanMenunggu = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan'])
                            ->whereDate('waktu_dibuat', $hariIni)
                            ->where('status', 'Menunggu')
                            ->orderBy('waktu_dibuat', 'asc')
                            ->get();

        $antreanAktif = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan'])
                            ->whereDate('waktu_dibuat', $hariIni)
                            ->where('status', 'Diproses')
                            ->where('user_id', $user->id)
                            ->first();

        $layanans = Layanan::with(['subLayanans' => function($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get();

        return view('cs.index', compact(
            'antreanMenunggu', 
            'antreanAktif', 
            'isSpectator', 
            'nomorMejaTerpilih',
            'layanans'
        ));
    }

    public function historyPage(Request $request)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Silakan pilih meja loket terlebih dahulu.');
        }

        /** @var User $user */
        $user = User::find(Auth::id());
        $nomorMejaTerpilih = $user->nomor_meja ?? session('meja_terpilih');
        $isSpectator = (empty($nomorMejaTerpilih) || $nomorMejaTerpilih == 0) && $user->role === 'admin';

        $searchQuery = $request->input('search');
        $period      = $request->input('period', 'all');
        $isCurated   = $request->input('is_curated', 'all');
        $startDate   = $request->input('start_date');
        $endDate     = $request->input('end_date');
        
        $query = TiketAntrian::with(['pelanggan', 'layanan', 'subLayanan'])
                    ->where('status', 'Selesai');

        $now = Carbon::now('Asia/Jakarta');

        // Filter berdasarkan Waktu
        switch ($period) {
            case 'today':
                $query->whereDate('waktu_selesai', $now->toDateString());
                break;

            case 'mtd':
                $query->whereBetween('waktu_selesai', [$now->copy()->startOfMonth(), $now->copy()->endOfDay()]);
                break;

            case 'last_30':
                $query->whereBetween('waktu_selesai', [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay()]);
                break;

            case 'ytd':
                $query->whereBetween('waktu_selesai', [$now->copy()->startOfYear(), $now->copy()->endOfDay()]);
                break;

            case 'custom':
                if ($startDate) {
                    $query->whereDate('waktu_selesai', '>=', $startDate);
                }
                if ($endDate) {
                    $query->whereDate('waktu_selesai', '<=', $endDate);
                }
                break;

            case 'all':
            default:
                break;
        }

        // Filter berdasarkan Status Kurasi (Sudah vs Belum)
        if ($isCurated === '1') {
            $query->where('is_curated', true);
        } elseif ($isCurated === '0') {
            $query->where(function($q) {
                $q->where('is_curated', false)->orWhereNull('is_curated');
            });
        }

        if ($searchQuery) {
            $query->where(function($q) use ($searchQuery) {
                $q->where('nomor_antrian', 'like', "%{$searchQuery}%")
                  ->orWhereHas('pelanggan', function ($qp) use ($searchQuery) {
                      $qp->where('no_hp', 'like', "%{$searchQuery}%")
                        ->orWhere('email', 'like', "%{$searchQuery}%")
                        ->orWhere('no_indibiz', 'like', "%{$searchQuery}%")
                        ->orWhere('nama', 'like', "%{$searchQuery}%");
                  });
            });
        }

        $riwayatTiket = $query->orderBy('waktu_selesai', 'desc')->paginate(15);
        $layanans = Layanan::with(['subLayanans' => function($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get();

        return view('cs.history', compact('riwayatTiket', 'searchQuery', 'period', 'isCurated', 'startDate', 'endDate', 'isSpectator', 'nomorMejaTerpilih', 'layanans'));
    }

    public function panggilSelanjutnya()
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        /** @var User $user */
        $user = Auth::user();

        $cekAktif = TiketAntrian::where('status', 'Diproses')->where('user_id', $user->id)->first();
        if ($cekAktif) {
            return back()->with('error', 'Selesaikan tiket ' . $cekAktif->nomor_antrian . ' terlebih dahulu!');
        }

        DB::beginTransaction();
        try {
            $tiket = TiketAntrian::whereDate('waktu_dibuat', Carbon::today('Asia/Jakarta'))
                        ->where('status', 'Menunggu')
                        ->orderBy('waktu_dibuat', 'asc')
                        ->lockForUpdate()
                        ->first();

            if ($tiket) {
                $now = Carbon::now('Asia/Jakarta');
                $tiket->status = 'Diproses';
                $tiket->user_id = $user->id;
                $tiket->waktu_diproses = $now;
                $tiket->waktu_dipanggil = $now;
                $tiket->waktu_mulai_konsul = $now;
                $tiket->jumlah_dipanggil = ($tiket->jumlah_dipanggil ?? 0) + 1;
                $tiket->save();

                DB::commit();

                $this->safeBroadcast($tiket);
                return back()->with('success', "Memanggil antrean {$tiket->nomor_antrian}");
            }

            DB::commit();
            return back()->with('error', 'Tidak ada antrean menunggu saat ini.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memanggil antrean: ' . $e->getMessage());
        }
    }

    public function panggilSpesifik(int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        /** @var User $user */
        $user = Auth::user();

        $cekAktif = TiketAntrian::where('status', 'Diproses')->where('user_id', $user->id)->first();
        if ($cekAktif) {
            return back()->with('error', 'Selesaikan tiket aktif terlebih dahulu!');
        }

        DB::beginTransaction();
        try {
            $tiket = TiketAntrian::where('id', $id)->lockForUpdate()->firstOrFail();
            
            if ($tiket->status == 'Menunggu') {
                $now = Carbon::now('Asia/Jakarta');
                $tiket->status = 'Diproses';
                $tiket->user_id = $user->id;
                $tiket->waktu_diproses = $now;
                $tiket->waktu_dipanggil = $now;
                $tiket->waktu_mulai_konsul = $now;
                $tiket->jumlah_dipanggil = ($tiket->jumlah_dipanggil ?? 0) + 1;
                $tiket->save();

                DB::commit();

                $this->safeBroadcast($tiket);
                return back()->with('success', "Memanggil antrean spesifik.");
            }

            DB::commit();
            return back()->with('error', 'Tiket sudah tidak tersedia atau sedang diproses oleh CS lain.');
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

        /** @var User $user */
        $user = Auth::user();
        $tiket = TiketAntrian::findOrFail($id);

        if (($tiket->jumlah_dipanggil ?? 1) >= 2) {
            $tiket->update([
                'status'  => 'Batal',
                'user_id' => $user->id
            ]);

            $this->safeBroadcast($tiket);
            return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} dibatalkan karena tidak hadir 2x.");
        }

        $tiket->update([
            'status'       => 'Menunggu',
            'user_id'      => null,
            'waktu_dibuat' => Carbon::now('Asia/Jakarta')
        ]);

        $this->safeBroadcast($tiket);
        return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} dipindahkan ke urutan antrean paling belakang untuk panggilan ke-2.");
    }

    public function selesaikanTiket(Request $request, int $id)
    {
        if (!$this->checkValidMeja()) {
            return redirect()->route('cs.select-meja')->with('error', 'Meja loket Anda telah dihapus oleh Admin.');
        }

        /** @var User $user */
        $user = Auth::user();
        $tiket = TiketAntrian::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        
        $request->validate([
            'nama_pelanggan'     => 'nullable|string|max:255',
            'email_pelanggan'    => 'nullable|email|max:255',
            'no_indibiz'         => 'nullable|string|max:255',
            'layanan_id'         => 'nullable|exists:layanans,id',
            'sub_layanan_id'     => 'nullable|exists:sub_layanans,id',
            'keluhan_final'      => 'nullable|string',
            'catatan_cs'         => 'nullable|string',
            'metode_pembayaran'  => 'nullable|string',
            'nominal_pembayaran' => 'nullable|numeric|min:0',
            'bukti_pembayaran'   => 'nullable|string|max:100',
        ]);

        if ($tiket->pelanggan) {
            $tiket->pelanggan->update([
                'nama'       => $request->nama_pelanggan ?? $tiket->pelanggan->nama,
                'email'      => $request->email_pelanggan,
                'no_indibiz' => $request->no_indibiz,
            ]);
        }

        $now = Carbon::now('Asia/Jakarta');
        $tiket->update([
            'layanan_id'           => $request->layanan_id ?? $tiket->layanan_id,
            'sub_layanan_id'       => $request->sub_layanan_id ?: null,
            'status'               => 'Selesai',
            'keluhan_final'        => $request->keluhan_final,
            'catatan_cs'           => $request->catatan_cs,
            'metode_pembayaran'    => $request->metode_pembayaran ?? 'Tanpa Transaksi',
            'nominal_pembayaran'   => $request->nominal_pembayaran ?? 0,
            'bukti_pembayaran'     => $request->bukti_pembayaran,
            'waktu_selesai'        => $now,
            'waktu_selesai_konsul' => $now,
            'is_curated'           => true,
        ]);

        $this->safeBroadcast($tiket);
        return redirect()->route('cs.index')->with('success', "Tiket {$tiket->nomor_antrian} berhasil diselesaikan.");
    }

    public function updateKurasi(Request $request, int $id)
    {
        $request->validate([
            'nama_pelanggan'  => 'nullable|string|max:255',
            'email_pelanggan' => 'nullable|email|max:255',
            'no_indibiz'      => 'nullable|string|max:255',
            'layanan_id'      => 'nullable|exists:layanans,id',
            'sub_layanan_id'  => 'nullable|exists:sub_layanans,id',
            'keluhan_final'   => 'nullable|string',
            'catatan_cs'      => 'nullable|string',
            'is_curated'      => 'required',
        ]);

        $tiket = TiketAntrian::findOrFail($id);
        
        if ($tiket->pelanggan) {
            $tiket->pelanggan->update([
                'nama'       => $request->nama_pelanggan ?? $tiket->pelanggan->nama,
                'email'      => $request->email_pelanggan,
                'no_indibiz' => $request->no_indibiz,
            ]);
        }

        $tiket->update([
            'layanan_id'     => $request->layanan_id ?? $tiket->layanan_id,
            'sub_layanan_id' => $request->sub_layanan_id ?: null,
            'keluhan_final'  => $request->keluhan_final,
            'catatan_cs'     => $request->catatan_cs,
            'is_curated'     => (bool) $request->is_curated,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => "Data tiket {$tiket->nomor_antrian} berhasil diperbarui."]);
        }

        return back()->with('success', "Data tiket {$tiket->nomor_antrian} berhasil diperbarui.");
    }

    public function leaveConsole()
    {
        /** @var User $user */
        $user = Auth::user();
        $this->closeActiveLog($user->id);
        $user->is_active = false;
        $user->nomor_meja = null;
        $user->save();

        session()->forget('meja_terpilih');

        return redirect()->route('admin.dashboard');
    }
}