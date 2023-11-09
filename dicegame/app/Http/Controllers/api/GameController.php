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
        ->map(function ($games) {

            return [

                'game_id' => $games->id,
                'dice_A' => $games->dice_A,
                'dice_B' => $games->dice_B,
                'result' => $games->result

            ];
        
        });

        return response()->json(['Games Index for ' . $user->alias => $games]);
        
    }

}