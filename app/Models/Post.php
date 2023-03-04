<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function comments()
    {
        return $this->hasMany(PostComment::class)->orderBy('id', 'Desc');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function mentionedUsers()
    {
        preg_match_all('/@([w-]+)/', $this->post, $matches);
        return $matches[1];
    }

    public function path()
    {
        return $this->path() . '#reply-' . $this->id;
    }


    public function addPost($post)
    {
        $reply = $this->create($post);

        /*event(new ThreadReceivedNewReply($post));*/

        return $post;
    }

}
