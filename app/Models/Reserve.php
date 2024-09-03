<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{

    protected $fillable = [
        'status',
    ];
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function order()
    {
        return $this->belongsToMany(Order::class);
    }
}
