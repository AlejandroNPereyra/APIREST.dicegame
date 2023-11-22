<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

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
            'password' => bcrypt('adminP@ss123'), // Replace 'password' with the admin's password

        ]);

        $admin->assignRole('admin');
    }
    
}