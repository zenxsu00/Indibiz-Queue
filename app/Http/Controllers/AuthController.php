<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MasterMeja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = strtolower($user->role ?? '');

            if ($role === 'admin' || $user->username === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if (!$user->nomor_meja) {
                return redirect()->route('cs.select-meja');
            }

            return redirect()->route('cs.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginType = strtolower($request->input('login_type', 'cs'));

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            session()->forget('url.intended');

            /** @var \App\Models\User $user */
            $user     = Auth::user();
            $userRole = strtolower($user->role ?? '');

            // PROTEKSI STATUS KERJA: Cek Cuti / Izin / Ditangguhkan
            if ($userRole === 'cs' && $user->status_kerja !== 'aktif') {
                Auth::logout();
                $pesan = match($user->status_kerja) {
                    'cuti'         => 'Akun Anda sedang dalam masa CUTI. Silakan hubungi Admin.',
                    'izin'         => 'Akun Anda sedang berstatus IZIN. Silakan hubungi Admin.',
                    'ditangguhkan' => 'Akun Anda sedang DITANGGUHKAN/SUSPEND. Akses ditolak.',
                    default        => 'Akun Anda tidak aktif saat ini.',
                };
                return back()->with('error', $pesan);
            }

            // JIKA LOGIN VIA TAB SUPER ADMIN
            if ($loginType === 'admin') {
                if ($userRole === 'cs' && strtolower($user->username) !== 'admin') {
                    Auth::logout();
                    return back()->with('error', 'Akses Ditolak! Akun CS tidak diizinkan masuk melalui Portal Admin.');
                }
                
                $user->update(['is_active' => true]);
                return redirect()->route('admin.dashboard');
            }

            // JIKA LOGIN VIA TAB CS
            return redirect()->route('cs.select-meja');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    /**
     * Tampilan Halaman / Modal Pilih Slot Meja CS
     */
    public function showSelectMeja()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Ambil semua master meja yang diizinkan Admin
        $masterMejas = MasterMeja::where('is_available', true)->get();

        // Ambil nomor meja yang sedang dipakai oleh CS lain yang ONLINE
        $mejaTerpakai = User::where('role', 'cs')
            ->where('is_active', true)
            ->whereNotNull('nomor_meja')
            ->pluck('nomor_meja')
            ->toArray();

        return view('cs.select_meja', compact('masterMejas', 'mejaTerpakai'));
    }

    /**
     * Memproses Meja yang Dipilih oleh CS / Admin
     */
    public function processSelectMeja(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // JIKA ADMIN MEMILIH UNTUK MASUK MODE SPECTATOR TANPA MEJA
        if ($request->has('mode_spectator') && strtolower($user->role) === 'admin') {
            $user->update([
                'nomor_meja' => null,
                'is_active'  => true,
            ]);
            return redirect()->route('cs.index');
        }

        $request->validate([
            'nomor_meja' => 'required|integer|exists:master_mejas,nomor_meja',
        ]);

        // Cek kembali apakah meja tersebut mendadak terpakai oleh CS lain
        $isUsed = User::where('role', 'cs')
            ->where('is_active', true)
            ->where('nomor_meja', $request->nomor_meja)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($isUsed) {
            return back()->with('error', 'Maaf! Meja tersebut baru saja dipilih oleh CS lain. Silakan pilih meja lain.');
        }

        // Simpan Meja dan Aktifkan Status User
        $user->update([
            'nomor_meja' => $request->nomor_meja,
            'is_active'  => true,
        ]);

        return redirect()->route('cs.index');
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $user->update([
                'is_active'  => false,
                'nomor_meja' => null,
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}