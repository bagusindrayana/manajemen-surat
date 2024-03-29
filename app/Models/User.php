<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Bagusindrayana\LaravelFilter\Traits\LaravelFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,LaravelFilter,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        'email',
        'no_telp',
    ];

    protected $filterFields = [
        'nama',
        'username',
        'password',
        'email',
        'no_telp',
        [
            'kontak_notifikasis'=>[
                'kontak'
            ]
        ]
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    //overide username
    public function username()
    {
        return 'username';
    }

    public function user_logs()
    {
        return $this->hasMany(UserLog::class);
    }

    public function kontak_notifikasis()
    {
        return $this->hasMany(KontakNotifikasi::class);
    }

    public function my_cloud_storages()
    {
        return $this->hasMany(CloudStorage::class)->where('personal',true);
    }

}
