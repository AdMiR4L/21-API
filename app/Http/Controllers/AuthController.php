<?php

namespace App\Http\Controllers;

use App\Events\UserForgotPassword;
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
            'email' => 'required',
            'password' => 'required',
        ]);

//        if (!Auth::attempt($request->only('email', 'password'))) {
//            return response()->json(['message' => 'Invalid credentials'], 401);
//        }

        $loginType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' :
            (is_numeric($request->email) ? 'phone' : 'nickname');


        if (!Auth::attempt([$loginType => $request->email, 'password' => $request->password])) {
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
            'local_id' => ['required', 'digits:10'],
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

    public function userVerify(Request $request)
    {
        $request->validate([
            'code' => 'required|integer',
        ]);
        $user = $request->user();
        if ($user->phone_register_code === $request->code){
            $user->update(['status' => 1]);
            return response()->json("شما با موفقیت وارد شدید", 200);
        }
        else  return response()->json("کد وارد شده نا معتبر است", 422);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/(09)[0-9]{9}/',
        ]);
        $user = User::query()->where('phone' , $request->phone)->first();
        if ($user){
            event(new UserForgotPassword($user));
            return response()->json("کد بازیابی ارسال شد", 200);
        }

        return response()->json(["message" => "کاربری با این شماره تلفن یافت نشد"], 422);
    }
    public function forgotPasswordSendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/(09)[0-9]{9}/',
            'code' => 'required|integer',
        ]);
        $user = User::query()->where('phone' , $request->phone)->first();
            if ($user && $user->forgot_password === $request->code){
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]);
            }
        return response()->json(["message" => "کد بازیابی وارد شده نادرست است"], 422);
    }
    public function forgotPasswordChange(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8|string',
        ]);
        $user = $request->user();
        if ($user){
            $user->update(["password" => Hash::make($request->password)]);
            return response()->json("کلمه عبور شما با موفقیت تغییر کرد", 200);
        }
    }


//    public function userVerify(Request $request)
//    {
//        // Validate the incoming request
//        $request->validate([
//            'code' => 'required|integer',
//        ]);
//
//        // Retrieve the authenticated user
//        $user = $request->user();
//
//        // Debugging: Log the incoming code and the stored code
//        \Log::info("Incoming code: " . $request->code);
//        \Log::info("Stored code: " . $user->phone_register_code);
//
//        // Check if the provided code matches the stored code
//        if ($user->phone_register_code === $request->code) {
//            // Debugging: Log before the update
//            \Log::info("Updating user status to 1 for user ID: " . $user->id);
//
//            // Update the user's status to 1
//            $user->update(['status' => 1]);
//
//            // Debugging: Log after the update
//            \Log::info("User status updated successfully");
//
//            // Return a successful response
//            return response()->json("شما با موفقیت وارد شدید", 200);
//        } else {
//            // Return an error response if the code is invalid
//            return response()->json("کد وارد شده نا معتبر است", 422);
//        }
//    }

    public function userSendCode(Request $request)
    {
        $user = $request->user();
        if ($user){
            event(new UserRegistered($user));
            return response()->json("کد تایید مجددا برای شما ارسال شد", 200);
        }
    }
}

