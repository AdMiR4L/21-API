<?php

namespace App\Providers;

use App\Events\SendUserCharacterWithSMS;
use App\Events\UserForgotPassword;
use App\Events\UserRegistered;
use App\Listeners\Send2FAMessage;
use App\Listeners\SendCharacter;
use App\Listeners\SendForgotPasswordCode;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    protected $listen = [
        UserRegistered::class => [
            Send2FAMessage::class,
        ],
        SendUserCharacterWithSMS::class => [
            SendCharacter::class,
        ],
        UserForgotPassword::class => [
            SendForgotPasswordCode::class,
        ],
    ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
