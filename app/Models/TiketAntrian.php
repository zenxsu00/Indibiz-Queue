<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiketAntrian extends Model
{
    public $timestamps = true; 

    protected $fillable = [
        'kode_tiket',
        'nomor_antrian',
        'pelanggan_id',
        'layanan_id',
        'user_id', 
        'keluhan_awal',
        'keluhan_final',
        'catatan_cs',
        'metode_pembayaran',
        'nominal_pembayaran',
        'bukti_pembayaran',
        'status',
        'jumlah_dipanggil', 
        'waktu_dibuat',
        'waktu_diproses',
        'waktu_selesai'
    ];

    // HAPUS CASTING 'datetime' AGAR TIDAK TERJADI DOUBLE TIMEZONE CONVERSION
    protected function casts(): array
    {
        return [
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