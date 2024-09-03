<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsersVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public $successStatus = 200;
    public function login(Request $request)
    {
        $user = User::query()->where("email", $request->email)->first();
        return $request;


//        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
//            $user = Auth::user();
//            $success['token'] =  $user->createToken('MyLaravelApp')->accessToken;
//            $success['userId'] = $user->id;
//            return response()->json(['success' => $success], $this->successStatus);
//        }
//        else{
//            return response()->json(['error'=>'Unauthorised'], 401);
//        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);



        $fieldNames = array(
            'first_name'      => 'First name',
            'last_name'       => 'Last name',
            'email'           => 'Email',
            'password'        => 'Password',
        );

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $user = new User;
        $user->first_name   =   strip_tags($request->first_name);
        $user->last_name    =   strip_tags($request->last_name);
        $user->email        =   $request->email;
        $user->password     =   bcrypt($request->password);
        $user->status       =   'Active';
        // $formattedPhone        = str_replace('+' . $request->carrier_code, "", $request->formatted_phone);
        //$user->phone           = !empty($request->phone) ? preg_replace("/[\s-]+/", "", $formattedPhone) : NULL;
        // $user->default_country = isset($request->default_country) ? $request->default_country : NULL;
        // $user->carrier_code    = isset($request->carrier_code) ? $request->carrier_code : NULL;
        // $user->formatted_phone = isset($request->formatted_phone) ? $request->formatted_phone : NULL;
        $user->save();


        //$user_verification  = new UsersVerification;
        //$user_verification->user_id  =   $user->id;
        //$user_verification->save();

        //$this->wallet($user->id);
        //$email_controller->welcome_email($user);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $success['token'] =  $user->createToken('MyLaravelApp')->accessToken;
            $success['first_name'] =  $user->first_name;
            $success['last_name'] =  $user->last_name;
            return response()->json(['success'=>$success], $this-> successStatus);
        } else {
            return response()->json(['error'=>'Please fill first name and last name'], 401);
        }

    }


    public function userDetails()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }
}
