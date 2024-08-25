<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'capacity',
        'clock',
        'extra_capacity',
        'available_capacity',
        'status',
        'meta_description',
        'meta_keywords',
        'photo_id',
    ];
    public function god()
    {
        return $this->belongsTo(User::class, 'god_id');
    }
    public function scenario()
    {
        return $this->belongsTo(Scenario::class, 'game_scenario');
    }
    public function history()
    {
        return $this->hasMany(History::class, "game_id");
    }
    public function order()
    {
        return $this->hasMany(Order::class, "game_id");
    }

    public function getCharacterName($user)
    {
        $history = History::query()
            ->where('game_id', $this->id)
            ->where('user_id', $user->id)
            ->first();

        $character = Character::query()->find($history->character_id);
        return $character->name;
    }
}
