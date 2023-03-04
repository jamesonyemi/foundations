<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{

    use SoftDeletes;
    use Sluggable;

    protected $dates = ['deleted_at'];

    protected $table = 'faqs';

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
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }
    public function author()
    {
        return $this->belongsTo(User::class);
    }
}
