<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class GameControllerTest extends TestCase
{

    use RefreshDatabase;

    public function setUp(): void {

        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesSeeder']);

    }

    public function testGamesIndex () {

        // Create a user
        $user = User::factory()->create();

        // Assign the gamer role to the user
        $user->assignRole('gamer');

        // Create some games for the user
        Game::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user, 'api')->getJson("/api/players/{$user->id}/games");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'Alias',
            'Success Percentage',
            'Games Index'
        ]);

    }

    public function testGamePlay () {

        // Create a user
        $user = User::factory()->create();

        // Assign the gamer role to the user
        $user->assignRole('gamer');

        // Act
        $response = $this->actingAs($user, 'api')->postJson("/api/players/{$user->id}/games");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'Message',
            'Alias',
            'Dice_A',
            'Dice_B',
            'Outcome'
        ]);

    }

    public function testDeleteGames () {

        // Create a user
        $user = User::factory()->create();

        // Assign the gamer role to the user
        $user->assignRole('gamer');

        // Create some games for the user
        Game::factory()->count(3)->create(['user_id' => $user->id]);

        // Act
        $response = $this->actingAs($user, 'api')->deleteJson("/api/players/{$user->id}/games");

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
                'message' => "All {$user->alias} games deleted successfully"
        ]);
        
    }

}