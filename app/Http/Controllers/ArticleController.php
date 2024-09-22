<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::query()->latest()->limit(3)->get();
        return response()->json(["articles" => $articles]);
    }
    public function archive(Request $request)
    {
        $articles = Article::query()->byCategory($request->category)->latest()->paginate(6);
        $categories = Category::query()->get();
        return response()->json(["articles" => $articles, "categories" => $categories]);
    }

    public function article(Request $request, $slug)
    {
        if ($request->bearerToken())
            $user = Auth::guard('sanctum')->user();
        else
            $user = null;

        $article = Article::with('category')
            ->where('slug' , $slug)
            ->first();


        $like = null;
         if ($user)
             $like = Like::query()
                 ->where("likeable_id" , $article->id)
                 ->where("likeable_type" , Article::class)
                 ->where("user_id" , $user->id)
                 ->first();


        return response()->json(["article" => $article, "like" => $like ]);
    }
    public function like(Request $request)
    {
        $request->validate([
            'article_id' => 'required|integer',
        ]);

        $article = Article::findOrFail($request->article_id); // findOrFail ensures a valid article
        $user = $request->user();

        $like = Like::where('likeable_id', $article->id)
            ->where('likeable_type', Article::class)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            // Unlike the article by setting status to 0
            if ($like->status == 1) {
                $like->update(['status' => 0]);
                $article->update(['like' => $article->like - 1]);
                return response()->json(false); // Unliked
            } else {
                // Reactivate the like
                $like->update(['status' => 1]);
                $article->update(['like' => $article->like + 1]);
                return response()->json(true); // Liked again
            }
        } else {
            // Create a new like
            Like::create([
                'user_id' => $user->id,
                'likeable_id' => $article->id,
                'likeable_type' => Article::class,
                'status' => 1, // Active like
            ]);

            $article->update(['like' => $article->like + 1]);

            return response()->json(true); // Liked
        }
    }




    public function comments(Request $request)
    {
        $request->validate([
            'article_id' => 'required|integer',
        ]);
        $article = Article::with([
            'category',
            'comments' => function($q) {
                $q->where('status', 1)
                    ->where('parent_id', null)
                    ->with('user'); // Eager load the user for comments
            },
            'comments.replies' => function($q) {
                $q->where('status', 1)
                    ->with('user'); // Eager load the user for replies
            }
        ])->find($request->article_id);
        $comments = $article->comments;
         return response()->json($comments);
    }
    public function commentAdd(Request $request)
    {
        $request->validate([
            'article_id' => 'required|integer',
            'description' => 'required|string|min:5',
        ]);
        $article = Article::query()->find($request->article_id);
        $comment = new Comment();
        $comment->description = $request->description;
        $comment->user_id = $request->user()->id;
        $comment->status = 1;


        $article->comments()->save($comment);
    }

}
