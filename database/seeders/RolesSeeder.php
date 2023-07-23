<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allPermissions = Permission::pluck('id')->toArray();
        $newRoles = [
            [
                'name' => 'Admin',
                'description' => 'Administrator',
                'permissions' => $allPermissions,
            ],
            [
                'name' => 'Sekretaris',
                'description' => 'Sekretaris',
                'permissions' => [9,10,11,12,13,14,15],
            ],
            [
                'name' => 'Kasi Pemerintahan',
                'description' => 'Kepala Seksi (Kasi) Pemerintahan',
                'permissions' => [],
            ],
            [
                'name' => 'Seksi Trantib',
                'description' => 'Kepala Seksi Ketentraman dan Ketertiban',
                'permissions' => [],
            ],
            [
                'name' => 'kasi ekobang',
                'description' => 'Kepala Seksi Ekonomi dan Pembangunan',
                'permissions' => [],
            ],
            [
                'name'=> 'Kasi Kesejahteraan',
                'description' => 'Kepala seksi kesejahteraan',
                'permissions' => [],
            ],
            [
                'name'=>'Lurah',
                'description' => 'Lurah',
                'permissions' => [9,10,11,12,13,14,16],
            ],
        ];

        foreach ($newRoles as $key => $value) {
            $role = \Spatie\Permission\Models\Role::create([
                'name' => $value['name'],
                'description' => $value['description'],
            ]);
            $role->syncPermissions($value['permissions']);
        }

        
    }
}
