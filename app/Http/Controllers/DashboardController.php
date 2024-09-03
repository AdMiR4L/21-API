<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nickname' => 'required|string|min:3|unique:users|regex:/^(?!.*[_-]{2})[a-zA-Z0-9][a-zA-Z0-9_-]{1,18}[a-zA-Z0-9]$/',
            'local_id' => 'nullable|digits:10',
            'address' => 'nullable|string|min:10',
            'post_code' => 'nullable|digits:10',
            'description' => 'nullable|string',
        ]);

        if ($user->local_id === null)
            $user->local_id = $request->input('local_id');


//        if ($user->city_id === null){
//            $request->validate(['city' => ['required']]);
//            $user->city_id = $request->input('city');
//        }
        if ($user->birth_date === null)
            $user->birth_date = $request->input('birth_date');

        if ($user->address === null)
            $user->address = $request->input('address');

        if ($user->nickname === null)
            $user->nickname = $request->input('nickname');


        if ($user->post_code === null)
            $user->post_code = $request->input('post_code');


        $user->description = $request->input('description');
        $user->club = $request->input('club');

        $user->save();

        return response()->json( 'پروفایل شما با موفقیت بروزرسانی شد');

    }
}
