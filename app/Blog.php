<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = ['user_id','title','body'];

    public function addNewPost($data)
    {
        return $this->create($data);
    }

    public function deletePost()
    {
        return $this->delete();
    }
}