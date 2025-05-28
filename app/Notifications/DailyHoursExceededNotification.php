<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyHoursExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The total hours logged for the day.
     *
     * @var float
     */
    private float $totalHours;

    /**
     * The date for which hours were logged.
     *
     * @var string
     */
    private string $date;

    /**
     * Create a new notification instance.
     */
    public function __construct(float $totalHours, string $date)
    {
        $this->totalHours = $totalHours;
        $this->date = $date;
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
        $formattedDate = Carbon::parse($this->date)->format('F j, Y');
        $hoursFormatted = number_format($this->totalHours, 2);

        return (new MailMessage)
            ->subject("Daily Hours Exceeded - {$formattedDate}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You've logged {$hoursFormatted} hours on {$formattedDate}.")
            ->line("This is just a friendly reminder that you've exceeded the 8-hour daily threshold.")
            ->line("Well done on your productivity today!")
            ->line("Remember to take breaks and maintain a healthy work-life balance.")
            ->action('View Time Logs', url('/'))
            ->salutation("Best regards,\nFreelance Time Tracker Team");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'total_hours' => $this->totalHours,
            'date' => $this->date,
        ];
    }
}
