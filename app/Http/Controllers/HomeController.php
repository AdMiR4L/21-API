<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Game;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

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



    public function leaderboard()
    {
        try {
            $month = Carbon::now()->subMonth();
            $week = Carbon::now()->subWeek();
            $endDate = Carbon::now();

            $players = User::query()
                ->whereBetween('updated_at', [$month, $endDate])
                ->orderBy('score', 'desc')
                ->where("role" , "User")
                ->take(10)
                ->get();

            $champ = User::query()
                ->whereBetween('updated_at', [$week, $endDate])
                ->orderBy('score', 'desc')
                ->where("role" , "User")
                ->first();

            return response()->json([
                'players' => $players,
                'champion' => $champ
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function gamesLeaderboard(Request $request)
    {
        if ($request->bearerToken())
            $user = Auth::guard('sanctum')->user();
         else
            $user = null;


        $players = User::query()
            ->orderBy('score', 'desc')
            ->where("role" , "User")
            ->take(50)
            ->get();

        $self = null;
        $selfRank = null;

        if ($user) {
            $self = User::find($user->id);
            $selfScore = $self->score;
            $selfRank = User::query()
                    ->where('score', '>', $selfScore)
                    ->count() + 1; // Rank is 1 + the number of users with a higher score
        }
        return response()->json([
            'players' => $players,
            'self' => $user,
            'self_rank' => $selfRank
        ]);
    }

    public function questions()
    {
        $questions = Question::query()->latest()->limit(3)->get();
        return response()->json($questions);
    }

}
