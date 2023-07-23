<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'nama'=>'Admin',
            'username'=>'admin',
            'password'=>bcrypt('password'),
        ]);

        //assgin role id 1
        $user->assignRole(1);

        $sekretaris = User::create([
            'nama'=>'Sekretaris',
            'username'=>'sekretaris',
            'password'=>bcrypt('password'),
        ]);

        //assgin role id 2
        $sekretaris->assignRole(2);

        $lurah = User::create([
            'nama'=>'Lurah',
            'username'=>'lurah',
            'password'=>bcrypt('password'),
        ]);

        //assgin role id 7
        $lurah->assignRole(7);
    }
}
