<?php

namespace Database\Factories;

use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    public function definition()
    {
        // slug dopĺňa Organization::setTitleAttribute
        return [
            'title' => $this->faker->unique()->company,
            'village_id' => Village::factory(),
            'published' => 1,
        ];
    }

    public function unpublished()
    {
        return $this->state(fn () => ['published' => 0]);
    }
}
