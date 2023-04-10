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

    public function getDisposisiBerikutnyaAttribute()
    {
        $disposisi_berikutnya = [];
        // $cek = SuratDisposisi::where('surat_id', $this->id)->where(function($w){
        //     $w->where(function($wu){
        //         $wu->where('user_id', auth()->user()->id)->where('role_id','!=',0);
        //     })->orWhere(function($wr){
        //         $wr->whereIn('role_id', auth()->user()->roles->pluck('id')->toArray())->where('user_id',0);
        //     });
        // })->first();
        $disposisi_berikutnya = SuratDisposisi::where('surat_id', $this->id)->whereIn('menunggu_persetujuan_id', auth()->user()->roles->pluck('id')->toArray())->get();
        return $disposisi_berikutnya;
    }
}
