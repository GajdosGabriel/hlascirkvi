<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition()
    {
        // organization_id tu bolo natvrdo 1 — na čistej databáze taký kanál
        // neexistuje. slug dopĺňa Post::setTitleAttribute.
        return [
            'organization_id' => Organization::factory(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraphs(5, true),
            'published' => now(),
        ];
    }

    public function unpublished()
    {
        return $this->state(fn () => ['published' => null]);
    }
}
