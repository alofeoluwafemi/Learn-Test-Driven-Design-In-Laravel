<?php

namespace Tests\Feature;

use App\Blog;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreatePostTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function a_logged_user_can_create_blog_post()
    {
        $response = $this->get('post/create');

        $response->assertSuccessful();

        $user = factory(User::class)->create();

        $this->actingAs($user);

        $data = (factory(Blog::class)->make(['user_id' => $user->id]))->toArray();

        $this->post('post/create',$data);

        $this->assertDatabaseHas('blogs',$data);
    }
}
