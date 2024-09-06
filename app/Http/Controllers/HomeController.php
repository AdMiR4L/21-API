<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->search;
        $user = User::query()
            ->name($search)
            ->nickname($search)
            ->get();
        $game = Game::query()->where("id")->get();
        $article = Article::query()
            ->where("title", 'like' , "%".$search."%")
            //->orWhere("title", $search)
            ->get();
        return response()->json(['articles' => $article, 'users' => $user, 'games' => $game]);
    }

    public function nickname(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|min:3|unique:users|regex:/^(?!.*[_-]{2})[a-zA-Z0-9][a-zA-Z0-9_-]{1,18}[a-zA-Z0-9]$/',
        ]);
        return response()->json("نام کاربری مجاز است");

    }

    public function leaderboard()
    {
        $month = Carbon::now()->subMonth();
        $week = Carbon::now()->subWeek();
        $endDate = Carbon::now();

        $players = User::query()
            ->whereBetween('created_at', [$month, $endDate])
            ->orderBy('score', 'desc')
            ->take(10)
            ->get();  // Select necessary columns
        $champ =  User::query()
            ->whereBetween('created_at', [$week, $endDate])
            ->orderBy('score', 'desc')
            ->first();
        return response()->json($players, $champ);
    }
}
