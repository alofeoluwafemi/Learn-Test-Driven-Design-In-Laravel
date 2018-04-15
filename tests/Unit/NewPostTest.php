<?php

namespace Tests\Unit;

use App\Blog;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class NewPostTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function successfully_add_new_post()
    {
        $post = (factory(Blog::class)->make())->toArray();

        (new Blog())->addNewPost($post);

        $this->assertDatabaseHas('blogs',$post);
    }
}
