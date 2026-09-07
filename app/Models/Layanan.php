<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_layanan',
        'nama_layanan',
        'is_active',
    ];

    public function subLayanans()
    {
        return $this->hasMany(SubLayanan::class, 'layanan_id');
    }

    public function tiketAntrians()
    {
        return $this->hasMany(TiketAntrian::class, 'layanan_id');
    }
}