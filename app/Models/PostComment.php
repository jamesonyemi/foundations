<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }

    public function mentionedUsers()
    {
        preg_match_all('/@([w-]+)/', $this->comment, $matches);
        return $matches[1];
    }

    public function path()
    {
        return $this->post->path() . '#reply-' . $this->id;
    }

}
