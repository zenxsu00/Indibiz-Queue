<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'keterangan',
        'is_nasional',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'is_nasional' => 'boolean',
    ];
}