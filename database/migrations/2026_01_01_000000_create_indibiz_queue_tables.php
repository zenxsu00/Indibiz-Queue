<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // 1. TABEL MASTER & AUTH
        // ==========================================
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 100);
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'cs'])->default('cs');
            $table->integer('nomor_meja')->nullable();
            $table->boolean('is_active')->default(false);
            $table->datetime('last_seen_at')->nullable();
            $table->enum('status_kerja', ['aktif', 'cuti', 'izin', 'ditangguhkan'])->default('aktif');
            $table->string('keterangan_status', 150)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('master_mejas', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_meja')->unique();
            $table->string('nama_meja', 100);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->string('no_hp', 15)->unique(); // Limit no hp max 15
            $table->string('email', 100)->nullable();
            $table->string('no_indibiz', 12)->nullable(); // STRICT: 12 Karakter sesuai review
            $table->string('nama', 100);
            $table->string('alamat', 255)->nullable(); // Diubah dari text ke varchar 255 agar hemat storage
            $table->timestamps();
        });

        Schema::create('layanans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_layanan', 10);
            $table->string('nama_layanan', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sub_layanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->string('nama_sub_layanan', 150);
            $table->string('deskripsi', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ==========================================
        // 2. TABEL PENGATURAN & PENJADWALAN (BARU)
        // ==========================================

        // Tabel untuk mengatur hari operasional dan jam kerja CS (Menggantikan deteksi login CS manual)
        Schema::create('jadwal_operasionals', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('hari_ke')->unique(); // 1 = Senin, 7 = Minggu
            $table->string('nama_hari', 10);
            $table->boolean('is_buka')->default(true); // Checklist hari aktif
            $table->time('jam_buka')->default('08:00:00');
            $table->time('jam_tutup')->default('16:00:00');
            $table->timestamps();
        });

        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan', 150);
            $table->boolean('is_nasional')->default(true);
            $table->timestamps();
        });

        // Tabel untuk Pengaturan Dinamis Admin (Seperti Waktu Edit Tiket CS)
        Schema::create('pengaturan_sistems', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 50)->unique(); // cth: 'batas_waktu_edit_tiket'
            $table->string('nilai', 255); // cth: '2' (jam)
            $table->string('deskripsi', 255)->nullable();
            $table->timestamps();
        });

        // ==========================================
        // 3. TABEL TRANSAKSI & LOG
        // ==========================================

        Schema::create('tiket_antrians', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tiket', 30)->nullable(); // Limit
            $table->string('nomor_antrian', 15); // Limit
            
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->foreignId('sub_layanan_id')->nullable()->constrained('sub_layanans')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('keluhan_awal');
            $table->text('keluhan_final')->nullable();
            $table->text('catatan_cs')->nullable();
            
            $table->string('metode_pembayaran', 50)->default('Tanpa Transaksi');
            $table->decimal('nominal_pembayaran', 12, 2)->default(0);
            $table->string('bukti_pembayaran', 255)->nullable();
            $table->string('no_referensi_qris', 100)->nullable();
            
            $table->enum('status', ['Menunggu', 'Diproses', 'Selesai', 'Batal'])->default('Menunggu');
            $table->boolean('is_curated')->default(0);
            $table->integer('jumlah_dipanggil')->default(0);
            
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamp('waktu_diproses')->nullable();
            $table->timestamp('waktu_mulai_konsul')->nullable();
            $table->timestamp('waktu_selesai_konsul')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('cs_active_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamp('jam_mulai')->nullable();
            $table->timestamp('jam_selesai')->nullable();
            $table->integer('durasi_menit')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Hapus tabel dengan urutan terbalik dari penciptaan (Transaksi dulu, baru Master)
        Schema::dropIfExists('cs_active_logs');
        Schema::dropIfExists('tiket_antrians');
        Schema::dropIfExists('pengaturan_sistems');
        Schema::dropIfExists('hari_liburs');
        Schema::dropIfExists('jadwal_operasionals');
        Schema::dropIfExists('sub_layanans');
        Schema::dropIfExists('layanans');
        Schema::dropIfExists('pelanggans');
        Schema::dropIfExists('master_mejas');
        Schema::dropIfExists('users');
    }
};