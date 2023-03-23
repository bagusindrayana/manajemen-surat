<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatDisposisi extends Model
{
    use HasFactory;
    protected $fillable = [
        'surat_disposisi_id',
        'status',
        'keterangan'
    ];
}
