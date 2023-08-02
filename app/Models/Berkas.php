<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Berkas extends Model
{
    use HasFactory;
    protected $fillable = [
        'surat_id',
        'nama_berkas',
        'path',
        'isi_berkas',
        'keterangan',
        'mime_type',
        'size'
    ];
    
    public function storages()
    {
        return $this->belongsToMany(CloudStorage::class, 'berkas_storages', 'berkas_id', 'storage_id');
    }

    public function berkas_storages()
    {
        return $this->hasMany(BerkasStorage::class, 'berkas_id', 'id');
    }
}
