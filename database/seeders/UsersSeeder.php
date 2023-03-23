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
    }
}
