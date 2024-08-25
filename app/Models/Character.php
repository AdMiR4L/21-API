<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'nickname',
        'description',
        'side',
        'photo_id',
    ];

    public function scenarios()
    {
        return $this->belongsToMany(Scenario::class, 'character_scenario')
            ->withPivot('count')
            ->withTimestamps();
    }
    public function history()
    {
        return $this->belongsTo(History::class, 'character_id');
    }


}
