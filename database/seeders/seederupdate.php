<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan sementara pengecekan foreign key agar tidak error saat insert data berelasi
        Schema::disableForeignKeyConstraints();

        // Kosongkan tabel sebelum diisi ulang
        DB::table('cs_active_logs')->truncate();
        DB::table('tiket_antrians')->truncate();
        DB::table('sub_layanans')->truncate();
        DB::table('layanans')->truncate();
        DB::table('pelanggans')->truncate();
        DB::table('master_mejas')->truncate();
        DB::table('users')->truncate();
        DB::table('jadwal_operasionals')->truncate();
        DB::table('pengaturan_sistems')->truncate();

        // 1. SEEDER USERS
        DB::table('users')->insert([
            ['id' => 1, 'nama_lengkap' => 'Super Administrator', 'username' => 'admin', 'password' => '$2y$12$FEbCNBZlJf2kUwhu4XWo1uWq3v0.xlGNGG5VRR68l/EBBDr0McCC.', 'role' => 'admin', 'nomor_meja' => null, 'is_active' => 0, 'created_at' => '2026-08-28 00:43:00', 'updated_at' => '2026-09-22 09:33:12'],
            ['id' => 2, 'nama_lengkap' => 'cs_meja01', 'username' => 'cs_meja1', 'password' => '$2y$12$sHB3czlnmjKDeiGofyzHjuflElu3NFw7TDp7goCx8SjSmTKLj1laa', 'role' => 'cs', 'nomor_meja' => null, 'is_active' => 0, 'created_at' => '2026-08-28 00:43:00', 'updated_at' => '2026-09-22 09:09:40'],
            ['id' => 3, 'nama_lengkap' => 'CS Indibiz Meja 02', 'username' => 'cs_meja2', 'password' => '$2y$12$Cso/E6ap5WucN6jmcotxtOjw7k61VU8bJ9EQ9wHcaF9UMpM/2Hw3e', 'role' => 'cs', 'nomor_meja' => null, 'is_active' => 1, 'created_at' => '2026-08-28 00:43:01', 'updated_at' => '2026-09-22 04:12:43'],
            ['id' => 6, 'nama_lengkap' => 'tester', 'username' => 'test', 'password' => '$2y$12$Uv/usro5eyanUA1oo31BzeyM4JkDNmYtF3QBki832BRkFUNhkx5wi', 'role' => 'cs', 'nomor_meja' => 0, 'is_active' => 1, 'created_at' => '2026-09-21 06:11:04', 'updated_at' => '2026-09-21 06:11:04'],
        ]);

        // 2. SEEDER MASTER MEJA
        DB::table('master_mejas')->insert([
            ['id' => 1, 'nomor_meja' => 1, 'nama_meja' => 'Loket Meja 01', 'is_available' => 1, 'created_at' => '2026-08-28 00:43:00', 'updated_at' => '2026-09-22 03:38:13'],
            ['id' => 2, 'nomor_meja' => 2, 'nama_meja' => 'Loket Meja 02', 'is_available' => 1, 'created_at' => '2026-08-28 00:43:00', 'updated_at' => '2026-08-28 00:43:00'],
        ]);

        // 3. SEEDER PELANGGAN
        DB::table('pelanggans')->insert([
            ['id' => 1, 'no_hp' => '089612910788', 'email' => 'mhzgr7@gmail.com', 'no_indibiz' => '1234', 'nama' => 'Hizkia Giri', 'alamat' => 'test', 'created_at' => '2026-09-11 08:23:50', 'updated_at' => '2026-09-22 03:47:10'],
            ['id' => 2, 'no_hp' => '08888', 'email' => 'mhzgr7@gmail.com', 'no_indibiz' => '712311654212', 'nama' => 'budi', 'alamat' => 'bandung', 'created_at' => '2026-09-22 09:04:29', 'updated_at' => '2026-09-22 09:08:04'],
        ]);

        // 4. SEEDER LAYANAN UTAMA
        DB::table('layanans')->insert([
            ['id' => 1, 'kode_layanan' => 'A', 'nama_layanan' => 'Pasang Baru (Sales / Registrasi)', 'is_active' => 1],
            ['id' => 2, 'kode_layanan' => 'B', 'nama_layanan' => 'Aktivasi Solusi Digital (Aplikasi)', 'is_active' => 1],
            ['id' => 3, 'kode_layanan' => 'C', 'nama_layanan' => 'Layanan Ekosistem Sektoral', 'is_active' => 1],
            ['id' => 4, 'kode_layanan' => 'D', 'nama_layanan' => 'Pengaduan Gangguan Teknis', 'is_active' => 1],
            ['id' => 5, 'kode_layanan' => 'E', 'nama_layanan' => 'Administrasi & Tagihan', 'is_active' => 1],
        ]);

        // 5. SEEDER SUB-LAYANAN
        DB::table('sub_layanans')->insert([
            ['id' => 1, 'layanan_id' => 4, 'nama_sub_layanan' => 'Putus-putus / Lambat', 'is_active' => 1],
            ['id' => 2, 'layanan_id' => 4, 'nama_sub_layanan' => 'Lambat / High Latency', 'is_active' => 1],
            ['id' => 3, 'layanan_id' => 4, 'nama_sub_layanan' => 'Gak Bisa Buka Web Spesifik', 'is_active' => 1],
            ['id' => 4, 'layanan_id' => 4, 'nama_sub_layanan' => 'Minta Petugas Datang ke Lokasi', 'is_active' => 1],
            ['id' => 5, 'layanan_id' => 4, 'nama_sub_layanan' => 'Mati Total (Los Red)', 'is_active' => 1],
            ['id' => 6, 'layanan_id' => 4, 'nama_sub_layanan' => 'Ganti Password / SSID WiFi', 'is_active' => 1],
            ['id' => 7, 'layanan_id' => 5, 'nama_sub_layanan' => 'Pembayaran Tunggakan / Bulanan', 'is_active' => 1],
            ['id' => 8, 'layanan_id' => 5, 'nama_sub_layanan' => 'Perubahan Paket / Upgrade-Downgrade', 'is_active' => 1],
            ['id' => 9, 'layanan_id' => 5, 'nama_sub_layanan' => 'Buka Isolir (Unisol)', 'is_active' => 1],
            ['id' => 10, 'layanan_id' => 5, 'nama_sub_layanan' => 'Cetak Ulang Faktur / Tagihan', 'is_active' => 1],
            ['id' => 11, 'layanan_id' => 1, 'nama_sub_layanan' => 'Internet High Speed / Astinet', 'is_active' => 1],
            ['id' => 12, 'layanan_id' => 1, 'nama_sub_layanan' => 'Indibiz Pay / QRIS Merchant', 'is_active' => 1],
            ['id' => 13, 'layanan_id' => 2, 'nama_sub_layanan' => 'Aktivasi Omni Channel / OCA', 'is_active' => 1],
            ['id' => 14, 'layanan_id' => 2, 'nama_sub_layanan' => 'Aktivasi Kasir Digital / Netpos', 'is_active' => 1],
            ['id' => 15, 'layanan_id' => 3, 'nama_sub_layanan' => 'Indibiz Sekolah / Digital School', 'is_active' => 1],
            ['id' => 16, 'layanan_id' => 3, 'nama_sub_layanan' => 'Indibiz Hotel & Hospitality', 'is_active' => 1],
            ['id' => 17, 'layanan_id' => 3, 'nama_sub_layanan' => 'Indibiz Health / SIMRS & Klinik', 'is_active' => 1],
            ['id' => 18, 'layanan_id' => 3, 'nama_sub_layanan' => 'Indibiz Ruko & Retail Digital', 'is_active' => 1],
            ['id' => 19, 'layanan_id' => 3, 'nama_sub_layanan' => 'Indibiz Finance / BPR & Koperasi', 'is_active' => 1],
        ]);

        // 6. SEEDER TIKET ANTRIAN
        DB::table('tiket_antrians')->insert([
            ['id' => 1, 'kode_tiket' => '11092026-A-001', 'nomor_antrian' => 'A-001', 'pelanggan_id' => 1, 'layanan_id' => 1, 'sub_layanan_id' => null, 'user_id' => 1, 'keluhan_awal' => 'test', 'status' => 'Selesai', 'is_curated' => 1, 'waktu_dibuat' => '2026-09-11 08:24:34'],
            ['id' => 2, 'kode_tiket' => '11092026-A-002', 'nomor_antrian' => 'A-002', 'pelanggan_id' => 1, 'layanan_id' => 1, 'sub_layanan_id' => null, 'user_id' => 1, 'keluhan_awal' => 'tset', 'status' => 'Selesai', 'is_curated' => 1, 'waktu_dibuat' => '2026-09-11 08:29:21'],
            ['id' => 4, 'kode_tiket' => '16092026-A-001', 'nomor_antrian' => 'A-001', 'pelanggan_id' => 1, 'layanan_id' => 1, 'sub_layanan_id' => 11, 'user_id' => 2, 'keluhan_awal' => 'test', 'keluhan_final' => 'test', 'catatan_cs' => 'Telah dilakukan edukasi fitur dan penggunaan aplikasi Indibiz kepada pelanggan.', 'status' => 'Selesai', 'is_curated' => 1, 'waktu_dibuat' => '2026-09-16 07:11:36'],
            ['id' => 12, 'kode_tiket' => '22092026-D-003', 'nomor_antrian' => 'D-003', 'pelanggan_id' => 2, 'layanan_id' => 4, 'sub_layanan_id' => 1, 'user_id' => 2, 'keluhan_awal' => 'jadi gini', 'keluhan_final' => 'sibuatkan ticket inc12222 gangguan los merah', 'catatan_cs' => 'eskalasi ke teknisi ujungberung', 'status' => 'Selesai', 'is_curated' => 1, 'waktu_dibuat' => '2026-09-22 09:04:29'],
        ]);

        // 7. SEEDER CS ACTIVE LOGS (Sample beberapa data dari SQL)
        DB::table('cs_active_logs')->insert([
            ['id' => 1, 'user_id' => 3, 'tanggal' => '2026-09-11', 'jam_mulai' => '2026-09-11 07:00:25', 'jam_selesai' => '2026-09-21 03:37:47', 'durasi_menit' => 14197],
            ['id' => 11, 'user_id' => 1, 'tanggal' => '2026-09-18', 'jam_mulai' => '2026-09-18 07:26:14', 'jam_selesai' => '2026-09-18 08:16:00', 'durasi_menit' => 50],
            ['id' => 19, 'user_id' => 1, 'tanggal' => '2026-09-22', 'jam_mulai' => '2026-09-22 04:31:42', 'jam_selesai' => '2026-09-22 08:00:31', 'durasi_menit' => 209],
        ]);

        // ==============================================================
        // DATA BARU: PENJADWALAN OPERASIONAL & PENGATURAN SISTEM
        // ==============================================================
        
        DB::table('jadwal_operasionals')->insert([
            ['hari_ke' => 1, 'nama_hari' => 'Senin', 'is_buka' => 1, 'jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00'],
            ['hari_ke' => 2, 'nama_hari' => 'Selasa', 'is_buka' => 1, 'jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00'],
            ['hari_ke' => 3, 'nama_hari' => 'Rabu', 'is_buka' => 1, 'jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00'],
            ['hari_ke' => 4, 'nama_hari' => 'Kamis', 'is_buka' => 1, 'jam_buka' => '08:00:00', 'jam_tutup' => '16:00:00'],
            ['hari_ke' => 5, 'nama_hari' => 'Jumat', 'is_buka' => 1, 'jam_buka' => '08:00:00', 'jam_tutup' => '16:30:00'],
            ['hari_ke' => 6, 'nama_hari' => 'Sabtu', 'is_buka' => 0, 'jam_buka' => '08:00:00', 'jam_tutup' => '12:00:00'], // Default Libur
            ['hari_ke' => 7, 'nama_hari' => 'Minggu', 'is_buka' => 0, 'jam_buka' => '08:00:00', 'jam_tutup' => '12:00:00'], // Default Libur
        ]);

        DB::table('pengaturan_sistems')->insert([
            [
                'kunci' => 'batas_waktu_edit_tiket',
                'nilai' => '2', // 2 Jam (Sesuai Review)
                'deskripsi' => 'Batas waktu CS (dalam jam) untuk dapat mengedit tiket setelah tiket berstatus Selesai.'
            ]
        ]);

        // Nyalakan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();
    }
}