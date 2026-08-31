<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MasterMeja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffManagementController extends Controller
{
    // --- 1. MANAJEMEN AKUN CS ---
    
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users,username',
            'password'     => 'required|string|min:6',
        ]);

        User::create([
            'nama_lengkap'  => $request->nama_lengkap,
            'username'      => strtolower($request->username),
            'password'      => Hash::make($request->password),
            'role'          => 'cs',
            'status_kerja'  => 'aktif',
            'is_active'     => false,
        ]);

        return back()->with('success', 'Akun Petugas CS baru berhasil ditambahkan!');
    }

    public function updateStatusUser(Request $request, int|string $id)
    {
        $request->validate([
            'status_kerja'      => 'required|in:aktif,cuti,izin,ditangguhkan',
            'keterangan_status' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        
        if ($request->status_kerja !== 'aktif') {
            $user->is_active  = false;
            $user->nomor_meja = null;
        }

        $user->status_kerja      = $request->status_kerja;
        $user->keterangan_status = $request->keterangan_status;
        $user->save();

        return back()->with('success', 'Status operasional petugas ' . $user->nama_lengkap . ' berhasil diperbarui!');
    }

    public function destroyUser(int|string $id)
    {
        $user = User::findOrFail($id);
        if ($user->role === 'admin') {
            return back()->with('error', 'Akun Super Admin tidak dapat dihapus!');
        }

        $user->delete();
        return back()->with('success', 'Akun CS berhasil dihapus dari sistem!');
    }


    // --- 2. MANAJEMEN SLOT MEJA LOKET ---

    public function storeMeja(Request $request)
    {
        $request->validate([
            'nomor_meja' => 'required|integer|unique:master_mejas,nomor_meja',
            'nama_meja'  => 'required|string|max:255',
        ]);

        MasterMeja::create([
            'nomor_meja'   => $request->nomor_meja,
            'nama_meja'    => $request->nama_meja,
            'is_available' => true,
        ]);

        return back()->with('success', 'Slot Loket Meja baru berhasil ditambahkan!');
    }

    public function toggleMeja(int|string $id)
    {
        $meja = MasterMeja::findOrFail($id);
        $meja->is_available = !$meja->is_available;
        $meja->save();

        // Jika meja dinonaktifkan, tendang user yang sedang menggunakan meja tersebut agar otomatis Offline
        if (!$meja->is_available) {
            User::where('nomor_meja', $meja->nomor_meja)->update([
                'nomor_meja' => null,
                'is_active'  => false
            ]);
        }

        $statusStr = $meja->is_available ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Slot Meja M{$meja->nomor_meja} berhasil {$statusStr}.");
    }

    public function destroyMeja(int|string $id)
    {
        $meja = MasterMeja::findOrFail($id);
        $nomorMeja = $meja->nomor_meja;

        // Reset semua CS / Admin yang sedang duduk di meja ini agar otomatis Offline & tidak terbaca Loket M00
        User::where('nomor_meja', $nomorMeja)->update([
            'nomor_meja' => null,
            'is_active'  => false
        ]);

        $meja->delete();

        return back()->with('success', "Slot Loket Meja M{$nomorMeja} berhasil dihapus!");
    }
}