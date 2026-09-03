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
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $role = strtolower($user->role ?? '');

            if ($role === 'admin' || $user->username === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // Jika user sudah memiliki nomor meja, langsung lempar ke CS Desk
            if ($user->nomor_meja) {
                return redirect()->route('cs.index');
            }

            // Jika belum punya meja, paksa ke halaman pilih meja (tidak bisa di login page lagi)
            return redirect()->route('cs.select-meja');
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

        // Cek dulu apakah username sedang aktif di perangkat lain
        $userCheck = User::where('username', $credentials['username'])->first();

        if ($userCheck && $userCheck->role === 'cs' && $userCheck->is_active) {
            return back()->with('error', 'Akun ' . $userCheck->username . ' sedang aktif/login di perangkat lain. Silakan keluar terlebih dahulu.');
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            session()->forget('url.intended');

            /** @var \App\Models\User $user */
            $user     = Auth::user();
            $userRole = strtolower($user->role ?? '');

            // PROTEKSI STATUS KERJA
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

            if ($loginType === 'admin') {
                if ($userRole === 'cs' && strtolower($user->username) !== 'admin') {
                    Auth::logout();
                    return back()->with('error', 'Akses Ditolak! Akun CS tidak diizinkan masuk melalui Portal Admin.');
                }
                
                $user->update(['is_active' => true]);
                return redirect()->route('admin.dashboard');
            }

            // Tandai user langsung AKTIF saat login berhasil
            $user->update(['is_active' => true]);

            // Jika user sudah punya meja tersimpan dari sesi sebelumnya, langsung masuk CS Desk
            if ($user->nomor_meja && MasterMeja::where('nomor_meja', $user->nomor_meja)->where('is_available', true)->exists()) {
                session(['meja_terpilih' => $user->nomor_meja]);
                return redirect()->route('cs.index');
            }

            return redirect()->route('cs.select-meja');
        }

        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    public function showSelectMeja()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Auto-Jump: Jika CS sudah punya meja aktif di database, tidak perlu pilih meja lagi
        if ($user->nomor_meja && MasterMeja::where('nomor_meja', $user->nomor_meja)->where('is_available', true)->exists()) {
            return redirect()->route('cs.index');
        }

        $masterMejas = MasterMeja::where('is_available', true)->get();

        $mejaTerpakai = User::where('role', 'cs')
            ->where('is_active', true)
            ->whereNotNull('nomor_meja')
            ->where('id', '!=', $user->id)
            ->pluck('nomor_meja')
            ->toArray();

        return view('cs.select_meja', compact('masterMejas', 'mejaTerpakai'));
    }

    public function processSelectMeja(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

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

        $isUsed = User::where('is_active', true)
            ->where('nomor_meja', $request->nomor_meja)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($isUsed) {
            return back()->with('error', 'Maaf! Meja tersebut sedang digunakan oleh CS lain. Silakan pilih meja lain.');
        }

        // Simpan meja secara permanen di database user
        $user->update([
            'nomor_meja' => $request->nomor_meja,
            'is_active'  => true,
        ]);

        session(['meja_terpilih' => $request->nomor_meja]);

        return redirect()->route('cs.index');
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            // HANYA reset is_active & nomor_meja saat benar-benar menekan tombol Logout
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