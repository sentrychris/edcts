<?php

namespace App\Notifications;

use App\Mail\Transport\APIPayload;
use App\Models\System;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepartureNotification extends Notification
{
    use Queueable;

    private System $system;

    /**
     * Create a new notification instance.
     */
    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toApi(object $notifiable): APIPayload
    {
        $name = explode(' ', $notifiable->name);
        return new APIPayload('login_successful', [
            'action_type' => 'login_successful',
            'member_ID' => $this->system->id64,
            'recipient' => $notifiable->email,
            "user" => [
                "firstname" => $name[0],
                "lastname" => $name[1]
            ],
            "url" => 'http://cyber-life-protection.local/activate-account?id='.$this->system->slug
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
