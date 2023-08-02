<?php

namespace App\Models;

use Bagusindrayana\LaravelFilter\Traits\LaravelFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontakNotifikasi extends Model
{
    use HasFactory,LaravelFilter;
    protected $fillable = [
        'user_id',
        'kontak',
        'type',
    ];
}
