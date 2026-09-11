<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterMeja extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_meja',
        'nama_meja',
        'is_available',
    ];

    protected $casts = [
        'nomor_meja'   => 'integer',
        'is_available' => 'boolean',
    ];
}