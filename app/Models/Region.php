<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    public function incomingFiles()
    {
        return $this->hasMany(\App\Models\IncomingFile::class);
    }
    protected $fillable = ['name'];
}
