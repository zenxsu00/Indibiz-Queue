<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TABEL USERS (Ditambahkan status_kerja dan keterangan)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'cs'])->default('cs');
            $table->integer('nomor_meja')->nullable();
            $table->boolean('is_active')->default(false);
            // Tambahan Fitur Manajemen CS
            $table->enum('status_kerja', ['aktif', 'cuti', 'izin', 'ditangguhkan'])->default('aktif');
            $table->string('keterangan_status')->nullable(); // Alasan cuti/izin/penangguhan
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. TABEL MASTER MEJA LOKET (Dikelola oleh Admin)
        Schema::create('master_mejas', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_meja')->unique();
            $table->string('nama_meja'); // Contoh: "Meja 01 - General CS"
            $table->boolean('is_available')->default(true); // Status apakah meja aktif dipasang
            $table->timestamps();
        });

        // 3. TABEL HARI LIBUR / KALENDER OPERASIONAL
        Schema::create('hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan'); // Contoh: "Hari Raya Idul Fitri" atau "Libur Regional"
            $table->boolean('is_nasional')->default(true);
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hari_liburs');
        Schema::dropIfExists('master_mejas');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};