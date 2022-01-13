<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title =$this->faker->sentence(4);

        return [
            'organization_id' => 1,
            'title' => $title,
            'slug' => Str::slug($title), 
            'body' => $this->faker->paragraphs(5, true),
        ];
    }
}
