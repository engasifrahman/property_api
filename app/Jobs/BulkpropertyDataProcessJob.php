<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\BulkPropertyStored;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\BulkPropertyDataInsertJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkpropertyDataProcessJob implements ShouldQueue
{
    use Queueable;

    private User $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info(__FILE__ . __FUNCTION__ . '::' . 'Starting BulkpropertyDataProcessJob');

        $files = collect(Storage::disk('local')->files('property'));
        $latestFile = $files
        ->map(function ($file) {
            return [
                'path' => $file,
                'timestamp' => Storage::disk('local')->lastModified($file),
            ];
        })
        ->sortByDesc('timestamp')
        ->first();

        if ($latestFile) {
            $filePath = $latestFile['path'];
            $fullFilePath = storage_path("app/{$filePath}");
        } else {
            return;
        }

        $mimeType = Storage::disk('local')->mimeType($filePath);
        $fileOpener = $mimeType == 'text/csv' ? 'fopen' : 'gzopen';
        $handle = $fileOpener($fullFilePath, 'r');

        if ($handle === false) {
            Log::error(__FILE__ . __FUNCTION__ . ':: Failed to open uploaded file');
        }

        $jobs = [];
        $headers = null;
        $chunkSize = 1000; // Optional: Insert 1000 at a time
        $properties = [];
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            if (!$headers) {
                $headers = $row; // First row is headers
                continue;
            }

            $properties[] = $row;

            // Optional: Process in propertieses to optimize DB insert
            if (count($properties) >= $chunkSize) {
                $jobs[] = new BulkPropertyDataInsertJob($headers, $properties);
                $properties = []; // Reset batch
            }
        }
        $fileCloser = $mimeType == 'text/csv' ? 'fclose' : 'gzclose';
        $fileCloser($handle);

        if (count($properties) > 0) {
            $jobs[] = new BulkPropertyDataInsertJob($headers, $properties);
        }

        Log::info(__FILE__ . __FUNCTION__ . ':: Total jobs in the batch is ' . count($jobs));

        // 🔥 Now create and dispatch the batch
        if (!empty($jobs)) {
            $batch_id = Bus::batch($jobs)->dispatch()->id;
        }

        // Send raw mail without template
        // $subject = 'Simple Test Email';
        // $body = 'This is a simple email sent directly without a Mailable class.';
        // Mail::raw($body, fn($message) => $message->to($this->user)->subject($subject));

        // Send instantly
        // Mail::to($this->user->email)->send(new BulkPropertyStored($this->user, $batch_id));

        // Send mail using queue
        Mail::to($this->user->email)->queue(new BulkPropertyStored($this->user, $batch_id));
    }
}
