<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Passport;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;


class GameController extends Controller
{

    public function gamesIndex ($id) {

        // Authenticate the user
        $user = Auth::guard('api')->user();

        // Fetch all games played by the user
        $games = Game::where('user_id', $id)
        
        ->get()
        ->map(function ($games, $index) {

            $message = $games->result == 7 ? 'You won!' : 'You lose!';

            return [

                'game_number' => $index +1,
                'game_id' => $games->id,
                'dice_A' => $games->dice_A,
                'dice_B' => $games->dice_B,
                'result' => $games->result,
                'outcome' => $message

            ];
        
        });

        // Calculate the success percentage
        $totalGames = $games->count();
        $wins = $games->where('result', 7)->count();
        $successPercentage = $totalGames > 0 ? round(($wins / $totalGames) * 100, 1) : 0;

        return response()->json(['Alias' => $user->alias, 'Success Percentage' => $successPercentage, 'Games Index' => $games]);
        
    }

    public function gamePlay ($id) {

        // Authenticate the user
        $user = Auth::guard('api')->user();

        // Generate two random dice rolls
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
     
        // Create a new game with these dice rolls
        $game = Game::create([

            'user_id' => $id,
            'dice_A' => $dice1,
            'dice_B' => $dice2,

        ]);


        // Check the sum of the dice rolls
        $sum = $dice1 + $dice2;
        $outcome = $sum == 7 ? 'You won!' : 'You lose!';
     
        return response()->json([

            'Message' => 'Game on!', 
            'Alias' => $user->alias, 
            'Dice_A' => $dice1, 
            'Dice_B' => $dice2, 
            'Outcome' => $outcome

        ]);

     
        return response()->json(['message' => 'Game registered successfully', 'game' => $game]);

    }

    public function deleteGames ($id) {

        // Authenticate the user
        $user = Auth::guard('api')->user();
    
        // Fetch all games played by the user
        $games = Game::where('user_id', $id)->get();
    
        // Delete the games
        foreach ($games as $game) {

            $game->delete();

        }
    
        return response()->json(['message' => 'All '.$user->alias.' games deleted successfully']);

    }

}