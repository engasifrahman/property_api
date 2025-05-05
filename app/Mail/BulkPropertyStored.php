<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkPropertyStored extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $batch_id;
    public string $batch_status;
    public int $total_batch_jobs;

    /**
     * Create a new message instance.
     *
     * @param  string|null $user
     * @param  mixed $batch_id
     * @return void
     */
    public function __construct(User $user, string|null $batch_id)
    {
        $this->user = $user;
        $this->batch_id = $batch_id;
        if ($batch_id) {
            $this->getBatchStatus($batch_id);
        } else{
            $this->total_batch_jobs = 0;
            $this->batch_status = '';
        }
    }

    /**
     * getBatchStatus
     *
     * @param  string $batch_id
     */
    private function getBatchStatus(string $batch_id)
    {
        $batch = Bus::findBatch($batch_id);

        if (!$batch) {
            $this->batch_status = 'Unknown';
            $this->total_batch_jobs = 0;
        }

        $this->total_batch_jobs = $batch->totalJobs;
        $this->batch_status = $batch->cancelled() ? 'cancelled' :
               ($batch->failedJobs > 0 ? 'completed with failures' :
               ($batch->finished() ? 'completed successfully' : 'processing'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bulk Property Stored',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.bulkPropertyStored',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
