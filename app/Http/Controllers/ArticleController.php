<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::query()->latest()->limit(3)->get();
        return response()->json(["articles" => $articles]);
    }
}
