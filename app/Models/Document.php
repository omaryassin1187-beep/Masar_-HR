<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'owner_id',
        'owner_type',
        'file_name',
        'file_path',
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}
