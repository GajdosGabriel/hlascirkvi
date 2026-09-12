<?php

namespace Database\Factories;

use App\Enums\PostSection;
use App\Models\Canal;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition()
    {
        // organization_id tu bolo natvrdo 1 — na čistej databáze taký kanál
        // neexistuje. slug dopĺňa Post::setTitleAttribute.
        return [
            'organization_id' => Canal::factory(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->paragraphs(5, true),
            'published_at' => now(),
            'section' => PostSection::Front,
        ];
    }

    /** Príspevok čakajúci vo fronte (App\Services\Buffer). */
    public function unpublished()
    {
        return $this->state(fn () => ['published_at' => null]);
    }

    public function section(PostSection $section)
    {
        return $this->state(fn () => ['section' => $section]);
    }
}
