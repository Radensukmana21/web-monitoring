<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename', 'path', 'region_id', 'partner_id', 'detected_at'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    protected $casts = [
        'detected_at' => 'datetime',
    ];
}

