<?php

namespace Tests\Feature;

use App\Blog;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDeletePostTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function an_authenticated_user_can_successfully_delete_its_own_post()
    {
        $this->actingAs(factory(User::class)->create());

        factory(Blog::class,5)->create();

        $this->delete("post/delete/3");

        $this->assertEquals(Blog::count(),4);
    }
}
