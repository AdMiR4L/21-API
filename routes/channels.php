<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('private-notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId; // Allow access only to the user
});

