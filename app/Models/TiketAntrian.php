<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiketAntrian extends Model
{
    use HasFactory;

    protected $table = 'tiket_antrians';

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
        'status',
        'metode_pembayaran',
        'nominal_pembayaran',
        'bukti_pembayaran',
        'waktu_dibuat',
        'waktu_diproses',
        'waktu_dipanggil',
        'waktu_mulai_konsul',
        'waktu_selesai',
        'waktu_selesai_konsul',
        'jumlah_dipanggil',
        'is_curated',
    ];

    protected $casts = [
        'waktu_dibuat'         => 'datetime',
        'waktu_diproses'       => 'datetime',
        'waktu_dipanggil'      => 'datetime',
        'waktu_mulai_konsul'   => 'datetime',
        'waktu_selesai'        => 'datetime',
        'waktu_selesai_konsul' => 'datetime',
        'is_curated'           => 'boolean',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function subLayanan(): BelongsTo
    {
        return $this->belongsTo(SubLayanan::class, 'sub_layanan_id');
    }

    public function cs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}