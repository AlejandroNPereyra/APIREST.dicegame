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

        // Check if the alias already exists

        // $originalAlias = $alias;
        // $counter = 1;

        // while (User::where('alias', $alias)->exists()) {

        //     $counter++;
        //     $alias = $originalAlias . $counter;

        // }

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

}