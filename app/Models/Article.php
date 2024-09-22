<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $fillable =
        [
            'like',
            'status',
            'user_id',
            'parent_id',
        ];


    public function category()
    {
        return $this->belongsTo(Category::class, "category_id");
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
    public function scopeByCategory($query , $category)
    {
        if (is_null($category))
            return $query;
        $find = Category::query()->where("slug", $category)->first();
        $query->where("category_id" ,$find->id);
        return $query;
    }
    public function getImageAttribute()
    {
        $image = Photo::query()->find($this->photo_id);
        return $image->path;
    }
}
