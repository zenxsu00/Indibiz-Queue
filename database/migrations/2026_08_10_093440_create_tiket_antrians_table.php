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
        Schema::create('tiket_antrians', function (Blueprint $table) {
            $table->id();
            
            // Kolom kode_tiket untuk pendataan Admin/CSV (format: DDMMYYYY-KODE-NOMORURUT)
            $table->string('kode_tiket')->nullable();
            
            $table->string('nomor_antrian'); // Untuk display Pelanggan & CS (format: KODE-NOMORURUT)
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->foreignId('layanan_id')->constrained('layanans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // CS yang melayani
            
            $table->text('keluhan_awal');
            $table->text('keluhan_final')->nullable(); // Dibuat opsional
            $table->text('catatan_cs')->nullable(); // Catatan / Note hasil konsultasi dari CS
            
            // PENDATAAN TRANSAKSI / PEMBAYARAN LOKET
            $table->string('metode_pembayaran')->nullable()->default('Tanpa Transaksi');
            $table->decimal('nominal_pembayaran', 12, 2)->default(0);
            $table->string('bukti_pembayaran')->nullable(); // Kode/Reff QRIS
            
            $table->enum('status', ['Menunggu', 'Diproses', 'Selesai', 'Batal'])->default('Menunggu');
            $table->integer('jumlah_dipanggil')->default(0); // Untuk 2-Strike Rule Missed Call
            
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diproses')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps(); // Standard created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiket_antrians');
    }
};