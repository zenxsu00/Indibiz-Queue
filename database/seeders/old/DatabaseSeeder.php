<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Layanan;
use App\Models\SubLayanan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Master Meja Loket
        DB::table('master_mejas')->insert([
            ['nomor_meja' => 1, 'nama_meja' => 'Loket Meja 01', 'is_available' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nomor_meja' => 2, 'nama_meja' => 'Loket Meja 02', 'is_available' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Users (Admin & CS)
        User::create([
            'nama_lengkap' => 'Super Administrator',
            'username'     => 'admin',
            'password'     => Hash::make('password123'),
            'role'         => 'admin',
            'status_kerja' => 'aktif',
        ]);

        User::create([
            'nama_lengkap' => 'CS Indibiz Meja 1',
            'username'     => 'cs_meja1',
            'password'     => Hash::make('password123'),
            'role'         => 'cs',
            'status_kerja' => 'aktif',
        ]);

        // 3. Layanan Utama & Sub-Layanan Indibiz
        $dataLayanan = [
            [
                'kode' => 'A',
                'nama' => 'Pasang Baru (Sales / Registrasi)',
                'subs' => ['Internet High Speed / Astinet', 'Indibiz Pay / QRIS Merchant']
            ],
            [
                'kode' => 'B',
                'nama' => 'Aktivasi Solusi Digital (Aplikasi)',
                'subs' => ['Aktivasi Omni Channel / OCA', 'Aktivasi Kasir Digital / Netpos']
            ],
            [
                'kode' => 'C',
                'nama' => 'Layanan Ekosistem Sektoral',
                'subs' => ['Ekosistem Sekolah / Digitalschool', 'Ekosistem Hotel & Health']
            ],
            [
                'kode' => 'D',
                'nama' => 'Pengaduan Gangguan Teknis',
                'subs' => ['Gangguan Jaringan / FTTH Down', 'Kendala Aplikasi / Software Support']
            ],
            [
                'kode' => 'E',
                'nama' => 'Administrasi & Tagihan',
                'subs' => ['Cetak Ulang Faktur / Tagihan', 'Perubahan Data / Upgrade Package']
            ],
        ];

        foreach ($dataLayanan as $item) {
            $layanan = Layanan::create([
                'kode_layanan' => $item['kode'],
                'nama_layanan' => $item['nama'],
                'is_active'    => true,
            ]);

            foreach ($item['subs'] as $subNama) {
                SubLayanan::create([
                    'layanan_id'       => $layanan->id,
                    'nama_sub_layanan' => $subNama,
                    'is_active'        => true,
                ]);
            }
        }
    }
}