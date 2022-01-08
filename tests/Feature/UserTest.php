<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login_form()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_duplication(){
        $user1 = User::make([
            'first_name' => 'Gabriel',
            'last_name' => 'Gajdoš',
            'email' => 'gajdosgabo@gmail.com',
        ]);

        $user2 = User::make([
            'first_name' => 'Gabrie2',
            'last_name' => 'Gajdoš2',
            'email' => '2gajdosgabo@gmail.com',
        ]);

        $this->assertTrue($user1->first_name != $user2->first_name);
    }

    public function test_delete_user(){
        $user = User::factory()->count(1)->make();

        $user = User::first();
    }
}
