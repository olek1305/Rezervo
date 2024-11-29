<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationsDeletedNotification extends Notification
{
    use Queueable;

    public $doctorName;
    public $specialization;

    /**
     * Create a new notification instance.
     */
    public function __construct($doctorName, $specialization)
    {
        $this->doctorName = $doctorName;
        $this->specialization = $specialization;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Anulowane rezerwacje')
            ->line("Rezerwacje z lekarzem {$this->doctorName} ({$this->specialization}) zostały anulowane.")
            ->line('Przepraszamy za wszelkie niedogodności.')
            ->line('Dziękujemy za korzystanie z naszego serwisu.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => "Rezerwacje z lekarzem {$this->doctorName} ({$this->specialization}) zostały anulowane.",
        ];
    }
}
