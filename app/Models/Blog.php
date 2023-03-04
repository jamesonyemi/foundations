<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{

    use SoftDeletes;
    use Sluggable;
    use Taggable;

    protected $dates = ['deleted_at'];

    protected $table = 'blogs';

    protected $guarded = ['id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute()
    {
        $image = $this->attributes['image'];
        if (!is_null($image)) {
            return asset('uploads/blog/' . $image);
        }

        return null;
    }
}
