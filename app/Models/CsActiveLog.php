<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsActiveLog extends Model
{
    use HasFactory;

    protected $table = 'cs_active_logs';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
    ];

    public function cs()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}