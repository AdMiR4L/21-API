<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::query()->orderBy('id', 'desc')->paginate(10);
        return response()->json($users, 200);
    }
    public function user($id)
    {
        $user = User::query()->find($id);
        return response()->json($user, 200);
    }
    public function userUpdate(Request $request, $id)
    {
        $user = User::query()->find($id);
        $user->grade = $request->grade;
        $user->save();
        return response()->json('کاربر با موفقیت بروزرسانی شد', 200);
    }
}
