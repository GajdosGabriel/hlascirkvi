<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition()
    {
        return [
            'body' => $this->faker->paragraph,
            'user_id' => User::factory(),
            'commentable_id' => Post::factory(),
            'commentable_type' => Post::class,
        ];
    }
}
