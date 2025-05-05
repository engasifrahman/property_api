<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Property;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use App\Mail\BulkPropertyStored;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Jobs\BulkpropertyDataProcessJob;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Notifications\BulkPropertyStoredNotification;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->stream(function () {
            Property::orderBy('id', 'desc')->chunk(500, function ($properties) use (&$first) {
                foreach ($properties as $property) {
                    $data = $property->toJson(); // Stream property as JSON
                    echo "data: " . $data . "\n\n";
                    ob_flush();
                    flush(); // Immediately push output
                }
            });

            // Send final "done" event
            echo "event: done\n";
            echo "data: Stream complete\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no' // For Nginx (disables buffering)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request)
    {
        Property::create($request->all());

        return response()->json(['message' => 'Data stored successfully'], 200);
    }

    public function bulkUpload(Request $request)
    {
        // Validate file input
        $request->validate([
            'file' => 'required|file|mimetypes:text/csv,application/gzip', // Adjust as needed
        ]);

        // Store file
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
        // Get the uploaded file
        $file = $request->file('file');
        $timestamp = now()->format('Ymd_His');
        $ext = $file->getClientOriginalExtension();
        $filename = "uploaded_to_store_{$timestamp}.{$ext}";
        $path = $file->storeAs('property', $filename);

        // (new BulkpropertyDataProcessJob())->handle();
        // dispatch(new BulkpropertyDataProcessJob());
        // BulkpropertyDataProcessJob::dispatch();
        // BulkpropertyDataProcessJob::dispatch()->delay(now()->addMinutes(10));

        $user = auth()->user();
        $batch_id = Bus::batch([
            new BulkpropertyDataProcessJob()
        ])
        ->before(function (Batch $batch) use($user) {
            // The batch has been created but no jobs have been added...
            // Send raw mail without template
            // $subject = 'Simple Test Email';
            // $body = 'This is a simple email sent directly without a Mailable class.';
            // Mail::raw($body, fn($message) => $message->to($user)->subject($subject));

            // Send instantly
            // Mail::to($user->email)->send(new BulkPropertyStored($user, $batch_id));

            // Send mail using queue
            // Mail::to($user->email)->queue(new BulkPropertyStored($user, $batch_id));

            // Send mail using queue including delay
            Mail::to($user->email)->later(now()->addSeconds(2), new BulkPropertyStored($user, $batch->id));
        })->progress(function (Batch $batch) {
            // A single job has completed successfully...
            Log::info(__FILE__ . __FUNCTION__ . '::' . 'bulkUpload has been proccessed ' . $batch->processedJobs() . 'jobs');
        })->then(function (Batch $batch) use($user) {
            // All jobs completed successfully...
            Log::info(__FILE__ . __FUNCTION__ . '::' . 'bulkUpload has been completed, batch-id:' . $batch->id);
            $user->notify((new BulkPropertyStoredNotification($batch->id))->delay(now()->addSeconds(5)));
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->dispatch()->id;


        Log::info(__FILE__ . __FUNCTION__ . '::' . $path);

        return response()->json([
            'message' => 'File uploaded successfully, process is ongoing!',
            'path' => $path ?? null,
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        return response()->json(['data' => $property], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $property->update($request->all());

        return response()->json(['message' => 'Data updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        if ($property) {
            $property->delete();
        } else {
            return response()->json(['message' => 'Delete operation has been failed, wrong id'], 400);
        }

        return response()->json(['message' => 'Data deleted successfully'], 200);
    }


    /**
     * Provide batch info based on id
     *
     * @param  string $id
     * @return JsonResponse
     */
    function batchInfo(string $id): JsonResponse
    {
        $batch = Bus::findBatch($id);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'name' => $batch->name,
            'totalJobs' => $batch->totalJobs,
            'pendingJobs' => $batch->pendingJobs,
            'failedJobs' => $batch->failedJobs,
            'processedJobs' => $batch->processedJobs(),
            'progress' => $batch->progress(), // Percentage
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ]);
    }
}
