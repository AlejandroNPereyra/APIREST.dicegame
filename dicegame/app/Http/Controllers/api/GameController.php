<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\User;

class GameController extends Controller
{

    public function gamesIndex ($id) {

        // Find the user
        $user = User::find($id);

        // Check if the user exists and has the 'gamer' role
        if (!$user || !$user->hasRole('gamer')) {

            return response()->json(['message' => 'User not found or not a gamer'], 404);

        }

        // Fetch all games played by the user
        $games = Game::where('user_id', $id)
        
        ->get()
        ->map(function ($games, $index) {

            return [

                'game_number' => $index +1,
                'game_id' => $games->id,
                'dice_A' => $games->dice_A,
                'dice_B' => $games->dice_B,
                'result' => $games->result

            ];
        
        });

        return response()->json(['Games Index for ' . $user->alias => $games]);
        
    }

    public function addGame ($id) {

        // Find the user
        $user = User::find($id);
     
        // Check if the user exists and has the 'gamer' role
        if (!$user || !$user->hasRole('gamer')) {
            return response()->json(['message' => 'User not found or not a gamer'], 404);
        }
     
        // Generate two random dice rolls
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
     
        // Create a new game with these dice rolls
        $game = Game::create([

            'user_id' => $id,
            'dice_A' => $dice1,
            'dice_B' => $dice2,

        ]);
     
        return response()->json(['message' => 'Game added successfully', 'game' => $game]);

    }

}