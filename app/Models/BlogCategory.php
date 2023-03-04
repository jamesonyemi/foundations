<?php

namespace App\Models;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'blog_categories';

    protected $guarded = ['id'];

    protected $appends = ['number_of_blog'];

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }

    public function getNumberOfBlogAttribute()
    {
        return $this->blog->count();
    }
}
