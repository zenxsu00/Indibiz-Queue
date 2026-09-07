<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubLayanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'layanan_id',
        'nama_sub_layanan',
        'is_active',
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function tiketAntrians()
    {
        return $this->hasMany(TiketAntrian::class, 'sub_layanan_id');
    }
}