<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    // Mengizinkan kolom ini untuk diisi secara massal
    protected $fillable = [
        'no_hp', 
        'nama', 
        'alamat'
    ];
}