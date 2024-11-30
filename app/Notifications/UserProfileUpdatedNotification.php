<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserProfileUpdatedNotification extends Notification
{
    use Queueable;

    public $updatedDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct($updatedDetails)
    {
        $this->updatedDetails = $updatedDetails;
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
            ->subject('Profile Updated')
            ->greeting('Hello!')
            ->line('Your profile has been updated with the following details:')
            ->line('Name: ' . $this->updatedDetails['name'])
            ->line('Email: ' . $this->updatedDetails['email'])
            ->line('Specialization: ' . ($this->updatedDetails['specialization'] ?? 'Not Set'))
            ->line('If you did not make these changes, please contact support.');
    }
}
