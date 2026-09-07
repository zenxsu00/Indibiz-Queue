<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiketAntrian extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_tiket',
        'nomor_antrian',
        'pelanggan_id',
        'layanan_id',
        'sub_layanan_id',
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
        'waktu_selesai',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function subLayanan()
    {
        return $this->belongsTo(SubLayanan::class, 'sub_layanan_id');
    }

    public function cs()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}