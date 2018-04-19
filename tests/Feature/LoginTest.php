<?php

namespace Tests\Feature\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class LoginTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }
    
    public function testRequiresEmailAndLogin()
    {
        $this->json('POST', 'api/login')
                ->assertStatus(422);
        /*->assertJson([
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ]);*/
    }
    
    
    public function testUserLoginsSuccessfully()
    {
        $user = factory(User::class)->create([
                'email' => 'testlogin@user.com',
                'password' => bcrypt('toptal123'),
            ]);
    
        $payload = ['email' => 'testlogin@user.com', 'password' => 'toptal123'];
    
        $this->json('POST', 'api/login', $payload)
                ->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                        'api_token',
                    ],
                ]);
    }
}
