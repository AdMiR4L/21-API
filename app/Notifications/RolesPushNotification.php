<?php
//
//namespace App\Notifications;
//
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Notifications\Messages\MailMessage;
//use Illuminate\Notifications\Notification;
//
//class RolesPushNotification extends Notification
//{
//    use Queueable;
//
//    /**
//     * Create a new notification instance.
//     */
//    public function __construct()
//    {
//        //
//    }
//
//    /**
//     * Get the notification's delivery channels.
//     *
//     * @return array<int, string>
//     */
//    public function via(object $notifiable): array
//    {
//        return ['mail'];
//    }
//
//    /**
//     * Get the mail representation of the notification.
//     */
//    public function toMail(object $notifiable): MailMessage
//    {
//        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
//    }
//
//    /**
//     * Get the array representation of the notification.
//     *
//     * @return array<string, mixed>
//     */
//    public function toArray(object $notifiable): array
//    {
//        return [
//            //
//        ];
//    }
//}


namespace App\Notifications;

use App\Models\Game;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RolesPushNotification extends Notification
{
    use Queueable;
    public $user;
    public $character;

    public function __construct(User $user, Game $game)
    {
        $this->user = $user;
        $this->character = $game->getCharacterName($user);
    }

    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => $this->character,
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->character,
        ];
    }
}
