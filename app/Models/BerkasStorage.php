<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasStorage extends Model
{
    use HasFactory;

    protected $fillable = [
        'berkas_id',
        'storage_id',
        'path',
    ];
}
