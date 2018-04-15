<?php

namespace App\Http\Controllers;

use App\Blog;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function create()
    {
        return view('welcome');
    }

    public function store(Request $request,Blog $blog)
    {
        $blog->addNewPost($request->all());

        return response(200);
    }

    public function destroy(Blog $blog)
    {
        $blog->deletePost();

        return response(200);
    }
}
