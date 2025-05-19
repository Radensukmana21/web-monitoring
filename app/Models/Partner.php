<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    public function incomingFiles()
    {
        return $this->hasMany(IncomingFile::class);
    }
    protected $fillable = ['name', 'region_id'];
}
