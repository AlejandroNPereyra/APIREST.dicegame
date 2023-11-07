<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Traits\HasRoles;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void

    {
        $admin = User::create([

            'alias' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass'), // Replace 'password' with the admin's password

        ]);

        $admin->assignRole('admin');
    }
    
}