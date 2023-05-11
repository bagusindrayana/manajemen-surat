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

    public function berkas()
    {
        return $this->belongsTo(Berkas::class);
    }

    public function storage()
    {
        return $this->belongsTo(CloudStorage::class, 'storage_id');
    }

    public function getLinkAttribute()
    {
        $link = "";
        switch ($this->storage->type) {
            case 'google':
                $link = "https://drive.google.com/file/d/".$this->path."/view?usp=sharing";
                break;
            
            default:
                # code...
                break;
        }
    }
}
