<?php

namespace App\Models;

use Bagusindrayana\LaravelFilter\Traits\LaravelFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    use HasFactory,LaravelFilter;
    

    protected $fillable = [
        'user_id',
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'sifat',
        'status',
        'isi'
    ];

    protected $filterFields = [
        [
            'user'=>[
                'nama'
            ]
        ],
        'nomor_surat',
        'tanggal_surat',
        'perihal',
        'sifat',
        'status'
    ];

    function user(){
        return $this->belongsTo(User::class);
    }

    function disposisis(){
        return $this->hasMany(SuratDisposisi::class, 'surat_id');
    }

    function berkas(){
        return $this->hasMany(Berkas::class, 'surat_id');
    }
}
