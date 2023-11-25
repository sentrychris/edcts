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

    public function toApi(): APIPayload
    {
        return new APIPayload('login_successfus', [
            'member_ID' => 'SBCR12125442',
            'recipient' => 'c.rowles@sentrybay.com',
            "user" => [
                "firstname" => "Peter",
                "lastname" => "Simms"
            ],
            "url" => "http://cyber-life-protection.local/activate-account"
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
