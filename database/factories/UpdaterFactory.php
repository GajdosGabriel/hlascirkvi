<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UpdaterFactory extends Factory
{
    public function definition()
    {
        $title = $this->faker->unique()->word;

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'type' => 'post',
        ];
    }
}
