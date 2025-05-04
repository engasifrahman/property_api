<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateFakePropertyCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:fakePropertyCsv {filename=fake_data.csv} {--count=10000000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake data and export it to a CSV file.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $count = $this->option('count');
        $faker = Faker::create();
        $rowsPerChunk = 500;

        $this->info("Generating {$count} records of fake data...");

        $fullPath = storage_path('app/' . $filename);

        // Open file for writing
        $handle = fopen($fullPath, 'w');

        if ($handle === false) {
            throw new \Exception('Failed to open file.');
        }

        for ($i = 0; $i < $count; $i++) {
            $item = [
                'name' => $faker->company,
                'bill_country_code' => $faker->countryCode,
                'description' => $faker->text(),
                'address_line_1' => $faker->streetAddress,
                'address_line_2' => $faker->secondaryAddress,
                'address_line_3' => $faker->citySuffix,
                'latitude' => $faker->latitude(-90, 90),
                'longitude' => $faker->longitude(-180, 180),
                'google_place_id' => $faker->uuid,
                'city' => $faker->city,
                'state' => $faker->state,
                'country' => $faker->country,
                'zip_code' => $faker->postcode,
                'star_rating' => $faker->numberBetween(0, 5),
                'property_type' => $faker->randomElement(['hotel', 'resort', 'guesthouse', 'bnb']),
                'is_active' => $faker->boolean,
                'is_deleted' => $faker->boolean,
            ];

            if($i == 0) {
                $headers = array_keys($item);

                fputcsv($handle, $headers);
            }

            fputcsv($handle, $item);

            // Flush every 500 rows
            if (($i + 1) % $rowsPerChunk == 0) {
                $this->info('Total ' . ($i + 1) . ' fake data has been generated. Writing last ' . $rowsPerChunk . ' to CSV...');

                fflush($handle); // Force write to disk
            }
        }

        // Final close
        fclose($handle);

        $this->info("Data successfully written to " . storage_path('app/' . $filename));

        return Command::SUCCESS;
    }
}
