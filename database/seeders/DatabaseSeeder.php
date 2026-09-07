<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Layanan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Master Meja Loket (Default 2 Loket Pertama)
        DB::table('master_mejas')->insert([
            [
                'nomor_meja'   => 1,
                'nama_meja'    => 'Loket Meja 01',
                'is_available' => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nomor_meja'   => 2,
                'nama_meja'    => 'Loket Meja 02',
                'is_available' => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        // 2. Membuat Akun Super Admin (Role disesuaikan ke 'admin')
        User::create([
            'nama_lengkap' => 'Super Administrator',
            'username'     => 'admin',
            'password'     => Hash::make('password123'),
            'role'         => 'admin',
            'nomor_meja'   => null,
            'is_active'    => false,
            'status_kerja' => 'aktif',
        ]);

        // 3. Membuat Akun Customer Service (CS) Meja 1
        User::create([
            'nama_lengkap' => 'CS Indibiz Meja 1',
            'username'     => 'cs_meja1',
            'password'     => Hash::make('password123'),
            'role'         => 'cs',
            'nomor_meja'   => null, // Set null karena slot meja dipilih CS saat login
            'is_active'    => false,
            'status_kerja' => 'aktif',
        ]);

        // 4. Membuat Akun Customer Service (CS) Meja 2
        User::create([
            'nama_lengkap' => 'CS Indibiz Meja 2',
            'username'     => 'cs_meja2',
            'password'     => Hash::make('password123'),
            'role'         => 'cs',
            'nomor_meja'   => null, // Set null karena slot meja dipilih CS saat login
            'is_active'    => false,
            'status_kerja' => 'aktif',
        ]);

        // 5. Membuat Daftar Layanan Baru (Sesuai Update Indibiz)
        $layanan = [
            [
                'kode_layanan' => 'A',
                'nama_layanan' => 'Pasang Baru (Sales / Registrasi)',
                'is_active'    => true,
            ],
            [
                'kode_layanan' => 'B',
                'nama_layanan' => 'Aktivasi Solusi Digital (Aplikasi)',
                'is_active'    => true,
            ],
            [
                'kode_layanan' => 'C',
                'nama_layanan' => 'Layanan Ekosistem Sektoral',
                'is_active'    => true,
            ],
            [
                'kode_layanan' => 'D',
                'nama_layanan' => 'Pengaduan Gangguan Teknis',
                'is_active'    => true,
            ],
            [
                'kode_layanan' => 'E',
                'nama_layanan' => 'Administrasi & Tagihan',
                'is_active'    => true,
            ],
        ];

        foreach ($layanan as $item) {
            Layanan::create($item);
        }
    }
}