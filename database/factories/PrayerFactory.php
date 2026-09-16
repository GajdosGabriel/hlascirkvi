<?php

namespace Database\Factories;

use App\Models\Canal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Prayer> */
class PrayerFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph,
            'canal_id' => Canal::factory(),
        ];
    }
}
