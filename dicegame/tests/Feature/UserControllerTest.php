<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp (): void {

        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'RolesSeeder']);
        Artisan::call('passport:install');

    }

    public function testRegister () {

        // Arrange
        $userData = [
            'alias' => $this->faker->userName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'usereP@ss123',
        ];

        // Act
        $response = $this->postJson('/api/players', $userData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['message' => 'User registered successfully']);

    }

    public function testLogin () {

        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('gamerP@ss123'),
        ]);

        $credentials = ['email' => $user->email, 'password' => 'gamerP@ss123'];

        // Act
        $response = $this->postJson('/api/login', $credentials);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['Alias', 'token']);

    }

    public function testLogout () {

        // Create a user
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user, 'api')->postJson('/api/logout');

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['message' => 'User Logged out successfully']);

    }

    public function testUpdateAlias () {

        // Create a user
        $user = User::factory()->create();

        // Assign the gamer role to the user
        $user->assignRole('gamer'); 

        $newAlias = 'newalias';

        // Act
        $response = $this->actingAs($user, 'api')->putJson('/api/players/{$user->id}', ['new_alias' => $newAlias]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Alias updated successfully']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'alias' => $newAlias]);

    }

    public function testGamersIndex () {

        // Create a user
        $user = User::factory()->create();

        // Assign the admin role to the user
        $user->assignRole('admin'); 

        // Act
        $response = $this->actingAs($user, 'api')->getJson('/api/players/');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['Total (All Gamers) Success Percentage', 'Gamers']);

    }

    public function testRankingIndex () {
        
        // Create a user
        $user = User::factory()->create();

        // Assign the admin role to the user
        $user->assignRole('admin'); 

        // Act
        $response = $this->actingAs($user, 'api')->getJson('/api/players/ranking');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['Gamers Ranking']);

    }

    public function testHighestRank () {

        // Create a user
        $user = User::factory()->create();

        // Assign the admin role to the user
        $user->assignRole('admin'); 

        // Act
        $response = $this->actingAs($user, 'api')->getJson('/api/players/ranking/winner');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['luckiest_gamer']);

    }

    public function testLowestRank () {

        // Create a user
        $user = User::factory()->create();

        // Assign the admin role to the user
        $user->assignRole('admin'); 

        // Act
        $response = $this->actingAs($user, 'api')->getJson('/api/players/ranking/loser');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['lowest_rank_gamer']);

    }
    
}