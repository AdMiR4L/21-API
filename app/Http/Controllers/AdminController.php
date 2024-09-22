<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $user->name = $request->name;
        $user->family = $request->family;
        $user->local_id = $request->local_id;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->nickname = $request->nickname;
        $user->role = $request->role;
        $user->post_code = $request->post_code;
        $user->birth_date = $request->birth_date;
        $user->address = $request->address;
        $user->description = $request->description;
        $user->save();
        return response()->json('کاربر با موفقیت بروزرسانی شد', 200);
    }
    public function password(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::query()->find($id);
        $user->update( ['password' => Hash::make($request['password'])]);

    }

    public function questions()
    {
        $questions = Question::query()->latest()->paginate(10);
        return response()->json($questions);
    }

    public function questionAdd(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:3',
            'description' => 'required|string|min:10',
        ]);
        $question = new Question();
        $question->title = $request->title;
        $question->description = $request->description;
        $question->save();
        return response()->json('آیتم با موفقیت افزوده شد', 200);
    }
}
