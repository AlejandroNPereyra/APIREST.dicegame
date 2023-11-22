<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Passport;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;


class UserController extends Controller
{
    
    public function register (Request $request) {

        // Validation rules
        $validator = Validator::make($request->all(), [
            'alias' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // If alias is empty, set it to "anonymous"
        $alias = $request->input('alias') ?: 'anonymous';

        // Check if the alias "anonymous" is already in use
        if (User::where('alias', 'anonymous')->count() > 0) {
            $uniqueId = User::where('alias', 'anonymous')->max('id') + 1;
            $alias = "anonymous{$uniqueId}";
        }

        if (User::where('alias', $alias)->exists()) {
            return response()->json(['error' => 'Alias already exists'], 422);
        }

        // Create a new user
        $user = User::create([
            'alias' => $alias,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign the 'gamer' role to the user
        $user->assignRole('gamer');

        // Issue an access token using Passport
        Passport::actingAs($user);

        event(new Registered($user));

        return response()->json(['message' => 'User registered successfully']);

    }

    public function login (Request $request) {

        // Validation rules
        $validator = Validator::make($request->all(), [

            'email' => 'required|string|email',
            'password' => ['required', 'string', 'min:8', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],

        ]);

        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {

            return response()->json(['message' => 'Unauthorized'], 401);

        }

        $user = $request->user();

        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json(['Alias' => $user->alias, 'token' => $token]);

    }

    public function logout () {

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        if ($user->token()) {

            $user->token()->revoke();

        }

        return response()->json([

            'message' => 'User Logged out successfully'

        ], 200);

    }

    public function updateAlias (Request $request) {

        // Validation rules
        $validator = Validator::make($request->all(), [
            
            'new_alias' => 'required|string|max:255',

        ]);
    
        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 422);

        }

        // Authenticate the user
        $user = Auth::guard('api')->user();

        // Update the alias
        User::where('id', $user->id)->update(['alias' => $request->new_alias]);
    
        return response()->json(['message' => 'Alias updated successfully']);

    }

    private function getUsersWithSuccessPercentage() {

        // Authenticate the user
        Auth::guard('api')->user(); 

        return User::whereHas('roles', function ($query) {

            $query->where('name', 'gamer');

        })

        ->withCount(['games as games_played'])
        ->withCount(['games as games_won' => function ($query) {

            $query->where('result', 7);

        }])

        ->get()
        ->each(function ($user) {

            $user->success_percentage = ($user->games_played > 0) 
                ? round(($user->games_won / $user->games_played) * 100, 1)
                : 0;

        });

    }

    public function gamersIndex () {       

        $users = $this->getUsersWithSuccessPercentage()

        ->map(function ($user) {

            return [

                'alias' => $user->alias,
                'email' => $user->email,
                'success_percentage' => $user->success_percentage

            ];

        });

        // Calculate the total success percentage of all registered gamers
        $totalSuccessPercentage = $users->avg('success_percentage');
    
        return response()->json(['Total (All Gamers) Success Percentage' => round($totalSuccessPercentage, 1), 'Gamers' => $users]);

    }

        public function rankingIndex () {

        $users = $this->getUsersWithSuccessPercentage()

        ->sortByDesc('success_percentage')
        ->values()
        ->map(function ($user, $index) {

            return [

                'rank' => $index + 1,
                'alias' => $user->alias,
                'success_percentage' => $user->success_percentage

            ];

        });

        return response()->json(['Gamers Ranking' => $users]);
       
    }

    public function highestRank () {

        $response = $this->rankingIndex();
        $users = json_decode($response->content(), true)['Gamers Ranking'];
        $highestRankGamer = $users[0];
        return response()->json(['luckiest_gamer' => $highestRankGamer]);

    }

    public function lowestRank () {

        $response = $this->rankingIndex();
        $users = json_decode($response->content(), true)['Gamers Ranking'];
        $lowestRankGamer = end($users);
        return response()->json(['lowest_rank_gamer' => $lowestRankGamer]);

    }

}