<?php

namespace Tests\Unit;

use App\Blog;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeletePostTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function successfully_delete_post()
    {
        factory(Blog::class)->create();

        $model = new Blog();

        $deleted = $model->find(1)->deletePost();

        $this->assertTrue($deleted);
    }
}
