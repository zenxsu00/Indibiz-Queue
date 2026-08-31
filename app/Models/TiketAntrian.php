<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiketAntrian extends Model
{
    // Aktifkan timestamps agar sinkron dengan $table->timestamps() di Migration
    public $timestamps = true; 

    protected $fillable = [
        'kode_tiket',
        'nomor_antrian',
        'pelanggan_id',
        'layanan_id',
        'user_id', 
        'keluhan_awal',
        'keluhan_final',
        'catatan_cs',          // <-- Ditambahkan di sini
        'metode_pembayaran',
        'nominal_pembayaran',
        'bukti_pembayaran',
        'status',
        'jumlah_dipanggil', 
        'waktu_dibuat',
        'waktu_diproses',
        'waktu_selesai'
    ];

    // Auto-casting string timestamp menjadi objek Carbon/Datetime
    protected function casts(): array
    {
        return [
            'waktu_dibuat' => 'datetime',
            'waktu_diproses' => 'datetime',
            'waktu_selesai' => 'datetime',
            'nominal_pembayaran' => 'float',
        ];
    }
    
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function cs()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}