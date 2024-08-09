<?php

namespace App\Http\Controllers;

use App\Events\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request)
    {
        // Validate the incoming request data

        $request->validate([
            'first_name' => ['required', 'string', 'max:120', 'regex:/^[\x{0600}-\x{06FF}\s]+$/u'],
            'last_name' => ['required', 'string', 'max:120', 'regex:/^[\x{0600}-\x{06FF}\s]+$/u'],
            'local_id' => ['required', 'digits', 'max:120'],
            'phone' => ['required', 'regex:/(09)[0-9]{9}/', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create a new user instance and hash the password
        $user =  User::create([
            'name' => $request['first_name'],
            'family' => $request['last_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
            'local_id' => $request['local_id'],
            'password' => Hash::make($request['password']),
        ]);

        // Log the user in and create an authentication token
        $token = $user->createToken('auth_token')->plainTextToken;
        event(new UserRegistered($user));

        // Return the token and a success message
        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'message' => 'User registered successfully!',
        ]);

    }

    public function user(Request $request)
    {
        $user = Auth::user();
        return response()->json($user);
    }
}

