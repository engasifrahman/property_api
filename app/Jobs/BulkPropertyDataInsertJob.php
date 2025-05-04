<?php

namespace App\Jobs;

use App\Models\Property;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BulkPropertyDataInsertJob implements ShouldQueue
{
    use Queueable, Batchable;

    private array $headers;
    private array $properties;

    /**
     * Create a new job instance.
     */
    public function __construct($headers, $properties)
    {
        $this->headers = $headers;
        $this->properties = $properties;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $processed_properties = [];
        foreach($this->properties as $key => $property) {
            // Typecast only if indexes exist
            $property[15] = isset($property[15]) ? (bool) $property[15] : true;
            $property[16] = isset($property[16]) ? (bool) $property[16] : true;

            if (count($this->headers) === count($property) && strlen($property[1]) <= 3) {
                $key_value_pair = array_combine($this->headers, $property);
                $processed_properties [] = $key_value_pair;
            }
        }

        Property::insert($processed_properties);
    }
}
