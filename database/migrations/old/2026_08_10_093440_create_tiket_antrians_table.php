<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Sub-Layanan Indibiz
        Schema::create('sub_layanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->string('nama_sub_layanan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabel Pelanggan Tambahan (Profiling)
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->string('email')->nullable()->after('no_hp');
            $table->string('no_indibiz')->nullable()->after('email');
        });

        // Tabel Tiket Antrian (Lengkap)
        Schema::create('tiket_antrians', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tiket')->nullable();
            $table->string('nomor_antrian');
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->foreignId('sub_layanan_id')->nullable()->constrained('sub_layanans')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('keluhan_awal');
            $table->text('keluhan_final')->nullable();
            $table->text('catatan_cs')->nullable();
            
            $table->string('metode_pembayaran')->nullable()->default('Tanpa Transaksi');
            $table->decimal('nominal_pembayaran', 12, 2)->default(0);
            $table->string('bukti_pembayaran')->nullable();
            
            $table->enum('status', ['Menunggu', 'Diproses', 'Selesai', 'Batal'])->default('Menunggu');
            $table->boolean('is_curated')->default(0)->nullable()->comment('0: Perlu Lapangan/Belum, 1: Selesai/Dikurasi');
            $table->integer('jumlah_dipanggil')->default(0);
            
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diproses')->nullable();
            $table->timestamp('waktu_dipanggil')->nullable();
            $table->timestamp('waktu_mulai_konsul')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamp('waktu_selesai_konsul')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_antrians');
        
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->dropColumn(['email', 'no_indibiz']);
        });

        Schema::dropIfExists('sub_layanans');
    }
};