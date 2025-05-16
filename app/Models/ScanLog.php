<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScanLog extends Model
{
    use HasFactory;

    protected $table = 'scan_logs';

    // Kolom yang boleh diisi massal (mass assignable)
    protected $fillable = [
        'scan_time',
        'user_id',
        'result',
    ];

    // Jika kamu ingin otomatis casting tipe data
    protected $casts = [
        'scan_time' => 'datetime',
    ];

    /**
     * Relasi ke User (opsional)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}