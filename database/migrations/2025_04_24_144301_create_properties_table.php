<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bill_country_code', 3);
            $table->text('description')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2');
            $table->string('address_line_3');
            $table->decimal('latitude', 10, 7)->nullable(); // e.g., 37.7749295
            $table->decimal('longitude', 10, 7)->nullable(); // e.g., -122.4194155
            $table->string('google_place_id')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->string('zip_code')->nullable();
            $table->integer('star_rating')->default(0); // rating from 1 to 5
            $table->string('property_type')->default('hotel'); // hotel, resort, guesthouse, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
