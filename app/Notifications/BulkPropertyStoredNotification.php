<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkPropertyStoredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $batch_id)
    {
        // $this->onConnection('redis');
        // $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @return array<string, \Illuminate\Support\Carbon>
     */
    // public function withDelay(object $notifiable): array
    // {
    //     return [
    //         'mail' => now()->addMinutes(5),
    //         'sms' => now()->addMinutes(10),
    //     ];
    // }

    /**
     * Determine which connections should be used for each notification channel.
     *
     * @return array<string, string>
     */
    // public function viaConnections(): array
    // {
    //     return [
    //         'mail' => 'redis',
    //         'database' => 'sync',
    //     ];
    // }

    /**
     * Determine if the notification should be sent.
     */
    // public function shouldSend(object $notifiable, string $channel): bool
    // {
    //     return $this->invoice->isPaid();
    // }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'mail-queue',
            'slack' => 'slack-queue',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            // ->mailer('postmark')
            // ->from('barrett@example.com', 'Barrett Blair')
            // ->subject('Notification Subject')
            // ->error()
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Bulk property update has been completed.')
            ->action('Check Status', url('/api/v1/batch-info/' . $this->batch_id))
            ->line('Thank you for using our application!');
            // ->attach('/path/to/file');
            // ->attach('/path/to/file', [
            //     'as' => 'name.pdf',
            //     'mime' => 'application/pdf',
            // ]);
            // ->attachMany([
            //     '/path/to/forge.svg',
            //     '/path/to/vapor.svg' => [
            //         'as' => 'Logo.svg',
            //         'mime' => 'image/svg+xml',
            //     ],
            // ]);
            // ->attachData($this->pdf, 'name.pdf', [
            //     'mime' => 'application/pdf',
            // ]);
            // ->tag('upvote')
            // ->metadata('comment_id', $this->comment->id);
            // ->markdown('mail.invoice.paid', ['url' => $url]);
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): Mailable
    // {
    //     return (new InvoicePaidMailable($this->invoice))
    //         ->to($notifiable->email)
    //         ->attachFromStorage('/path/to/file');
    // }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'name' => $notifiable->name,
            'batch_id' => $this->batch_id,
        ];
    }
}
