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
        'isi',
        'pemeriksa_id'
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
        return $this->belongsTo(User::class)->withDefault([
            'nama'=>'User Di Hapus'
        ]);
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
        $disposisi_berikutnya = SuratDisposisi::where('surat_id', $this->id)->whereIn('menunggu_persetujuan_id', auth()->user()->roles->pluck('id')->toArray())->get();
        return $disposisi_berikutnya;
    }

    function pemeriksa() {
        return $this->belongsTo(User::class, 'pemeriksa_id')->withDefault([
            'nama'=>'User Di Hapus'
        ]);
    }
}
