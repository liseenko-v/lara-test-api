<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Article;

class ArticleTest extends TestCase
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
    
    public function testsArticlesAreCreatedCorrectly()
    {
        $user = factory(User::class)->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];
        $payload = [
                'title' => 'Lorem',
                'body' => 'Ipsum',
            ];
    
        $this->json('POST', '/api/articles', $payload, $headers)
                ->assertStatus(201)
                ->assertJson(['id' => 1, 'title' => 'Lorem', 'body' => 'Ipsum']);
    }
    
    public function testsArticlesAreUpdatedCorrectly()
    {
        $user = factory(User::class)->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];
        $article = factory(Article::class)->create([
            'title' => 'First Article',
            'body' => 'First Body',
        ]);

        $payload = [
            'title' => 'Lorem',
            'body' => 'Ipsum',
        ];

        $response = $this->json('PUT', '/api/articles/' . $article->id, $payload, $headers)
            ->assertStatus(200)
            ->assertJson([
                'id' => 1,
                'title' => 'Lorem',
                'body' => 'Ipsum'
            ]);
    }
    
    public function testsArtilcesAreDeletedCorrectly()
    {
        $user = factory(User::class)->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];
        $article = factory(Article::class)->create([
            'title' => 'First Article',
            'body' => 'First Body',
        ]);

        $this->json('DELETE', '/api/articles/' . $article->id, [], $headers)
            ->assertStatus(204);
    }
    
    public function testArticlesAreListedCorrectly()
    {
        factory(Article::class)->create([
            'title' => 'First Article',
            'body' => 'First Body'
        ]);

        factory(Article::class)->create([
            'title' => 'Second Article',
            'body' => 'Second Body'
        ]);

        $user = factory(User::class)->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];

        $response = $this->json('GET', '/api/articles', [], $headers)
            ->assertStatus(200)
            ->assertJson([
                [ 'title' => 'First Article', 'body' => 'First Body' ],
                [ 'title' => 'Second Article', 'body' => 'Second Body' ]
            ])
            ->assertJsonStructure([
                '*' => ['id', 'body', 'title', 'created_at', 'updated_at'],
            ]);
    }

    public function testArticlesCanShowUnauthorizedUser()
    {
        $response = $this->json('get', 'api/articles')->assertStatus(401);
    }

    public function testArticlesCanShowAuthorizedUser()
    {
        $user = factory(User::class)->create();
        $token = $user->generateToken();
        $headers = ['Authorization' => "Bearer $token"];
        $response = $this->json('get', 'api/articles', [], $headers)->assertStatus(200);
    }

    public function testArticlesAddGetUpdateDelete()
    {
        factory(User::class)->create(['email'=>'test@test.ru']);
        $payload = ['email' => 'test@test.ru', 'password' => 'secret'];
        $response = $this->json('post', 'api/login', $payload);
        
        $response->assertStatus(200);

        $token = $response->original['data']['api_token'];
        $headers = ['Authorization' => "Bearer $token"];
        $this->json('get', 'api/articles', [], $headers)->assertStatus(200);

        $payload = ['title' => 'First Article', 'body' => 'First test'];
        $this->json('post', 'api/articles', $payload, $headers)->assertStatus(201);

        $this->json('get', 'api/articles/1', [], $headers)->assertStatus(200);
       
        $payload_update = ['title' => 'First change'];
        $this->json('put', 'api/articles/1', $payload_update, $headers)->assertStatus(200);

        $this->json('get', 'api/articles/1', [], $headers)->assertStatus(200)->assertJson(['title' => 'First change', 'body' => 'First test']);

        $this->json('delete', 'api/articles/1', [], $headers)->assertStatus(204);

        $this->json('get', 'api/articles/1', [], $headers)->assertStatus(404);

        $this->json('post', 'api/logout')->assertStatus(200);
    }
}