<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratDisposisi extends Model
{
    use HasFactory;
    protected $fillable = [
        'surat_id',
        'user_id',
        'role_id',
        'menunggu_persetujuan_id',
        'status',
        'keterangan'
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getKepadaAttribute()
    {
        return $this->user->nama ?? $this->role->name;
    }

    public function riwayat_disposisis()
    {
        return $this->hasMany(RiwayatDisposisi::class);
    }

}
