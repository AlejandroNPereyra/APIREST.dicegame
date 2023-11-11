<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void

    {

        // Create an admin role
        Role::create(['name' => 'admin']);

        // Create a gamer role
        Role::create(['name' => 'gamer']);

    }

}