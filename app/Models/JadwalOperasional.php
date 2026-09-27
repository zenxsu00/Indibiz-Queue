<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalOperasional extends Model
{
    use HasFactory;

    protected $table = 'jadwal_operasionals';

    protected $fillable = [
        'hari_ke',
        'nama_hari',
        'is_buka',
        'jam_buka',
        'jam_tutup',
    ];
}