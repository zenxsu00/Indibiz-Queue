<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubLayanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'layanan_id',
        'nama_sub_layanan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }

    public function tiketAntrians(): HasMany
    {
        return $this->hasMany(TiketAntrian::class, 'sub_layanan_id');
    }
}