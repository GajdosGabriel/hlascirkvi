<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Role musia byť prvé — UserObserver::created volá assignRole('user')
        // a bez existujúcej role hodí RoleDoesNotExist.
        $this->call(RolesSeeder::class);

        User::factory(10)->create();
        Post::factory(10)->create();
    }
}
