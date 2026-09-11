<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_layanan',
        'nama_layanan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subLayanans(): HasMany
    {
        return $this->hasMany(SubLayanan::class, 'layanan_id');
    }

    public function tiketAntrians(): HasMany
    {
        return $this->hasMany(TiketAntrian::class, 'layanan_id');
    }
}