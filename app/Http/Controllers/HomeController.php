<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Game;
use App\Models\User;
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
}
