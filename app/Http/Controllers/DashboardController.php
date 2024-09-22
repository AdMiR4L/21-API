<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Order;
use App\Models\Photo;
use App\Models\User;
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

    public function avatar(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048', // max size 2MB
        ]);

        // Get the original file name with extension
        $originalName = $request->file('file')->getClientOriginalName();

        // Generate a unique name for the stored file (to avoid collisions)
        $generatedName = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();

        // Store the image in 'public/uploads' and get the path
        $path = $request->file('file')->storeAs('uploads', $generatedName, 'public');

        // Generate the full URL for the stored image
        $imageUrl = asset('storage/' . $path);

        // Save the image details in the database
        $photo = new Photo();
        $photo->path = $imageUrl; // store the full URL
        $photo->model = User::class; // store the full URL
        $photo->name = $generatedName; // generated name for the file
        $photo->original = $originalName; // original name with extension
        $photo->user_id = $request->user()->id; // assuming the user is authenticated
        $photo->save();

        User::query()->find($request->user()->id)
            ->update(["photo_id" => $photo->id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded and saved successfully',
            'image_url' => $imageUrl,
        ]);
    }
    public function nickname(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|min:3|unique:users|regex:/^(?!.*[_-]{2})[a-zA-Z0-9][a-zA-Z0-9_-]{1,18}[a-zA-Z0-9]$/',
        ]);
        return response()->json("نام کاربری مجاز است");

    }

    public function transactions(Request $request)
    {
        $user = $request->user();
        $orders = Order::with("game")->where("user_id", $user->id)->latest()->paginate(10);
        return response()->json([$orders]);
    }
    public function history(Request $request)
    {
        $user = $request->user();
        $orders = History::with(["game.scenario", "character"])->where("user_id", $user->id)->latest()->paginate(10);
        return response()->json([$orders]);
    }
}
