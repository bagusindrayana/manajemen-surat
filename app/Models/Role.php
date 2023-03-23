<?php

namespace App\Models;

use Bagusindrayana\LaravelFilter\Traits\LaravelFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as RolePermission;

class Role extends RolePermission
{   
    use LaravelFilter;
    protected $guarded  = [];
    protected $filterFields = [
        'name'
    ];
    public function group_permission()
    {
        return $this->belongsTo(GroupPermission::class);
    }

    public function parents()
    {
        return $this->belongsToMany(Role::class, 'role_parents', 'role_id', 'parent_id');
    }

    public function parent()
    {
        return $this->parents()->first();
    }
}
