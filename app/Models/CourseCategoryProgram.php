<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCategoryProgram extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];



    public function program()
    {

        return $this->hasMany(Direction::class);
    }

    public function courseCategory()
    {

        return $this->belongsTo(CourseCategory::class);
    }
}
