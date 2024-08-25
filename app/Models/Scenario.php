<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scenario extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'characters',
        'photo_id',
    ];
    public function characters()
    {
        return $this->belongsToMany(Character::class, 'character_scenario')
            ->withPivot('count')
            ->withTimestamps();
    }
    public function games()
    {
        return $this->hasMany(Game::class, 'game_scenario');
    }
}
