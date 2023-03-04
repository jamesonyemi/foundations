<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCategorySection extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];



    public function sections()
    {

        return $this->hasMany(Section::class);
    }

    public function courseCategory()
    {

        return $this->belongsTo(CourseCategory::class);
    }
}
