<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    public function avatar()
    {
        return $this->belongsTo(Photo::class, 'user_id');
    }
}
