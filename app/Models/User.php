<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\History;
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'family',
        'email',
        'phone',
        'local_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function games()
    {
        return $this->hasMany(Game::class, 'god_id');
    }
    public function avatar()
    {
        return $this->hasMany(Photo::class, 'user_id');
    }
    public function getPhoneRegisterCode()
    {
        if ($this->phone_register_code !== NULL && Carbon::parse($this->phone_register_code_expired_at) > Carbon::now())
            return $this->phone_register_code;
        else {
            $phone_register_code = rand(1000, 9999);
            $this->update([
                'phone_register_code' => $phone_register_code,
                'phone_register_code_expired_at' => Carbon::now()->addMinutes(3)
            ]);

            return $phone_register_code;
        }
    }
    public function getResetPasswordCode()
    {
        if ($this->forgot_password !== NULL && Carbon::parse($this->forgot_password_expired_at) > Carbon::now())
            return $this->phone_register_code;
        else {
            $forgot_password = rand(1000, 9999);
            $this->update([
                'forgot_password' => $forgot_password,
                'forgot_password_expired_at' => Carbon::now()->addMinutes(3)
            ]);

            return $forgot_password;
        }
    }

    public function history()
    {
        return $this->hasMany(History::class);
    }

//    protected $uploads = '/images/avatars/';
//    public function getImageAttribute()
//    {
//        $avatar = Photo::query()->findOrFail($this->photo_id);
//        return $this->uploads.$avatar->path;
//    }
}
