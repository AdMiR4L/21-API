<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendRolesNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $character;

    public function __construct(User $user, Game $game)
    {
        $this->user = $user;
        $this->character = $game->getCharacterName($user);
    }

    public function broadcastOn(): array
    {
        // Broadcast on a private channel specific to the user
        return [
            new PrivateChannel('notifications.' . $this->user->id),
        ];
    }

    // This method can be used to pass data to the frontend
    public function broadcastWith(): array
    {
        return [
            'message' => $this->character,
        ];
    }
}
