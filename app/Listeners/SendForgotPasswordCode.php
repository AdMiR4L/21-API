<?php

namespace App\Listeners;

use App\Events\UserForgotPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kavenegar;

class SendForgotPasswordCode
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(UserForgotPassword $event): void
    {
        try {
            $receptor = $event->user->phone;
            $template = "resetPassword";
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
