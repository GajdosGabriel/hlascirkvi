<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImageFactory extends Factory
{
    public function definition()
    {
        $name = $this->faker->unique()->slug(2) . '.jpg';

        return [
            'fileable_id' => Post::factory(),
            'fileable_type' => Post::class,
            'name' => $name,
            'org_name' => $name,
            'url' => 'posts/2026/09/' . $name,
            'thumb' => 'posts/2026/09/thumb-' . $name,
        ];
    }
}
