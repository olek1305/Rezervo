<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCreatedNotification extends Notification
{
    use Queueable;

    public $reservationDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $reservationDetails)
    {
        $this->reservationDetails = $reservationDetails;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }


    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Reservation Created')
            ->line("You have successfully booked a reservation with
                Dr. {$this->reservationDetails['doctor_name']} ({$this->reservationDetails['specialization']}).")
            ->line("Date: {$this->reservationDetails['date']}")
            ->line("Time: {$this->reservationDetails['time']}")
            ->line('Thank you for using our service.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => "Your reservation with Dr. {$this->reservationDetails['doctor_name']} ({$this->reservationDetails['specialization']}) on {$this->reservationDetails['date']} at {$this->reservationDetails['time']} has been successfully created.",
            'doctor_name' => $this->reservationDetails['doctor_name'],
            'specialization' => $this->reservationDetails['specialization'],
            'date' => $this->reservationDetails['date'],
            'time' => $this->reservationDetails['time'],
        ];
    }
}
