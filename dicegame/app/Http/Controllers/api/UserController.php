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

class UserController extends Controller
{
    
    public function register (Request $request) {

        // Validation rules
        $validator = Validator::make($request->all(), [

            'alias' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',

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
            'password' => 'required|string',

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

        return response()->json(['token' => $token]);

    }

        public function logout () {

        /** @var \App\Models\User $user **/
        $user = Auth::user();

        $token = $user->token();
        $token->revoke();

        return response()->json([

            'message' => 'User Logged out'

        ], 200);

    }

    public function updateAlias (Request $request) {

        // Validation rules
        $validator = Validator::make($request->all(), [

            'id' => 'required|integer',
            'new_alias' => 'required|string|max:255',

        ]);
    
        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 422);

        }
    
        // Find the user
        $user = User::find($request->id);

        // Check if the user has the 'gamer' role
        if (!$user->hasRole('gamer')) {

            return response()->json(['message' => 'Unauthorized'], 403);

        }
    
        if (!$user) {

            return response()->json(['message' => 'User not found'], 404);

        }
    
        // Update the alias
        $user->alias = $request->new_alias;
        $user->save();
    
        return response()->json(['message' => 'Alias updated successfully']);

    }

    /* public function gamersIndex () {

        // The whereHas method add customized constraints to a relationship query
        $users = User::whereHas('roles', function ($query) {

            $query->where('name', 'gamer'); // checks if a user has a role where the name is 'gamer'

        })
        
        ->withCount(['games as games_played']) // adds a games_played attribute to each User model instance
        ->withCount(['games as games_won' => function ($query) { // adds a games_won attribute
            $query->where('result', 7);

        }])

        ->get()
        ->each(function ($user) {
            $user->success_percentage = ($user->games_played > 0) 
                ? round(($user->games_won / $user->games_played) * 100, 1)
                : 0; // calculates the success percentage for each user and adds it 
                // as a success_percentage attribute to the User model instance.
                //  If the user hasn't played any games (i.e., games_played is 0), 
                // the success percentage is set to 0 to avoid division by zero.
        })
        
        ->map(function ($user) {
            return [
                'alias' => $user->alias,
                'email' => $user->email,
                'success_percentage' => $user->success_percentage
            ];
        });

        return response()->json(['users' => $users]);
    } */ 

    private function getUsersWithSuccessPercentage() {

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
    
        return response()->json(['users' => $users]);

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

        return response()->json(['users' => $users]);
       
    }

    public function highestRank () {

        $response = $this->rankingIndex();
        $users = json_decode($response->content(), true)['users'];
        $highestRankingGamer = $users[0];
        return response()->json(['luckiest_gamer' => $highestRankingGamer]);

    }

    public function lowestRank () {

        $response = $this->rankingIndex();
        $users = json_decode($response->content(), true)['users'];
        $lowestRankingGamer = end($users);
        return response()->json(['lowest_ranking_gamer' => $lowestRankingGamer]);

    }

}