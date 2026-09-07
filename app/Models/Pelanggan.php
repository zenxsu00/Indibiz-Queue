<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_hp',
        'email',
        'no_indibiz',
        'nama',
        'alamat',
    ];

    public function tiketAntrians()
    {
        return $this->hasMany(TiketAntrian::class, 'pelanggan_id');
    }
}