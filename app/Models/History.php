<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $table = 'histories';

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

}
