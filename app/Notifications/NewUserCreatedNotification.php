<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCreatedNotification extends Notification
{
    use Queueable;

    public $userDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct($userDetails)
    {
        $this->userDetails = $userDetails;
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
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Account Created')
            ->greeting('Hello!')
            ->line('A new account has been created with the following details:')
            ->line('Name: ' . $this->userDetails['name'])
            ->line('Email: ' . $this->userDetails['email'])
            ->line('Specialization: ' . ($this->userDetails['specialization'] ?? 'Not Set'))
            ->line('Thank you for joining us!');
    }
}
