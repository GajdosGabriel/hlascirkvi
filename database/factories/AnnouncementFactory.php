<?php

namespace Database\Factories;

use App\Enums\AnnouncementPlacement;
use App\Enums\AnnouncementVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Announcement> */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'placement'   => AnnouncementPlacement::Top,
            'variant'     => AnnouncementVariant::Info,
            'title'       => $this->faker->sentence(4),
            'body'        => $this->faker->sentence(10),
            'active'      => true,
            'dismissible' => false,
            'sort_order'  => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => false]);
    }

    /** Oznam, ktorého okno zobrazovania sa už skončilo. */
    public function expired(): static
    {
        return $this->state(fn () => [
            'published_from'  => now()->subMonth(),
            'published_until' => now()->subDay(),
        ]);
    }

    /** Oznam pripravený na neskôr. */
    public function scheduled(): static
    {
        return $this->state(fn () => ['published_from' => now()->addDay()]);
    }
}
