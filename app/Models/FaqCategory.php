<?php

namespace App\Models;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaqCategory extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'faq_categories';

    protected $guarded = ['id'];

    public function faqs()
    {
        return $this->hasMany(Faq::class);
    }
}
