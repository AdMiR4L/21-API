<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kavenegar;

class Send2FAMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            $receptor = $event->user->phone;
            $template = "registerVerification";
            $type = "sms";
            $token = $event->code;
            $token2 = "";
            $token3 = "";

            $result = Kavenegar::VerifyLookup($receptor, $token, $token2, $token3, $template, $type);
        } catch (ApiException $e) {
            echo $e->errorMessage();
        } catch (HttpException $e) {
            echo $e->errorMessage();
        }
    }
}
