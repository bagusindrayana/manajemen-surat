<?php

namespace Database\Seeders;

use App\Models\GroupPermission;
use Illuminate\Database\Seeder;


class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'Manage Role'=>[
                'View Role',
                'Create Role',
                'Read Role',
                'Update Role',
                'Delete Role',
            ],
            'Manage User'=>[
                'View User',
                'Create User',
                'Read User',
                'Update User',
                'Delete User',
            ],
            'Manage Surat'=>[
                'View Surat',
                'Create Surat',
                'Read Surat',
                'Update Surat',
                'Delete Surat',
                'Approve Surat',
                'Disposition Surat',
            ],
            'Manage Cloud Storage'=>[
                'View Cloud Storage',
                'Create Cloud Storage',
                'Read Cloud Storage',
                'Update Cloud Storage',
                'Delete Cloud Storage',
            ],
        ];
        
        foreach ($permissions as $key => $value) {
            $group = GroupPermission::create(['name'=>$key]);
            foreach ($value as $key => $value) {
                $group->permissions()->create(['name'=>$value]);
            }
        }

        

    }
}
