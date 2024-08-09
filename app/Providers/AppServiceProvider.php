<?php

namespace App\Providers;

use App\Events\UserRegistered;
use App\Listeners\Send2FAMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    protected $listen = [
        UserRegistered::class => [
            Send2FAMessage::class,
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
