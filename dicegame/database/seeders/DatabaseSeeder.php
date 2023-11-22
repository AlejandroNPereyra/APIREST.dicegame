<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */

    public function run(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(AdminUserSeeder::class);

        // You can also use the factory to create multiple gamers
        \App\Models\User::factory(9)->create();
        \App\Models\Game::factory(500)->create();
    }
    
}