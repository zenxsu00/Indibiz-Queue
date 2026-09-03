<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Attributes yang boleh diisi secara mass-assignment.
     */
    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'role',
        'nomor_meja',
        'is_active',
        'status_kerja',
        'keterangan_status',
        'last_seen_at', // Didaftarkan agar Heartbeat Ping tersimpan
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'is_active'    => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }
}