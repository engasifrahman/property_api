<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Hotel',
            'bill_country_code' => $this->faker->countryCode, // ISO 3166-1 alpha-2 (you can customize to alpha-3 if needed)
            'description' => $this->faker->paragraph,
            'address_line_1' => $this->faker->streetAddress,
            'address_line_2' => $this->faker->secondaryAddress,
            'address_line_3' => $this->faker->buildingNumber,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'google_place_id' => $this->faker->uuid,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => $this->faker->country,
            'zip_code' => $this->faker->postcode,
            'star_rating' => $this->faker->numberBetween(1, 5),
            'property_type' => $this->faker->randomElement(['hotel', 'resort', 'guesthouse', 'bnb']),
            'is_active' => $this->faker->boolean(90), // 90% chance of true
            'is_deleted' => false,
        ];
    }
}
