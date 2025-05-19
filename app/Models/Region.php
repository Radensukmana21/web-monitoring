<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
     public function partners()
    {
        return $this->hasMany(Partner::class);
    }
     public function incomingFiles()
    {
        return $this->hasManyThrough(IncomingFile::class, Partner::class);
    }
    protected $fillable = ['name'];
}
