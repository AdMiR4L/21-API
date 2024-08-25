<?php

namespace App\Listeners;

use App\Events\SendUserCharacterWithSMS;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Kavenegar;

class SendCharacter
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
    public function handle(SendUserCharacterWithSMS $event): void
    {
        try {
            $receptor = $event->user->phone;
            $template = "sendRole";
            $type = "sms";
            $token = $event->character;
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
