<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrayerFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph,
            'organization_id' => Organization::factory(),
        ];
    }
}
