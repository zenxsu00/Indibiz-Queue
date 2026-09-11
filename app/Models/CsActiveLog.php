<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'tanggal'     => 'date',
        'jam_mulai'   => 'datetime',
        'jam_selesai' => 'datetime',
        'durasi_menit'=> 'integer',
    ];

    public function cs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}