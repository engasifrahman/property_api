<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Jobs\BulkPropertyDataInsertJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkpropertyDataProcessJob implements ShouldQueue
{
    use Queueable, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

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
            // Add more jobs to the current batch
            $this->batch()->add($jobs);
        }
    }
}
