<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function courseCategory()
    {
        return $this->belongsTo(CourseCategory::class);
    }



    public function comments()
    {
        return $this->hasMany(CourseComment::class)->orderByDesc('id');
    }



    public function stakeHolders()
    {
        return $this->belongsToMany(Employee::class)->withTimeStamps();
    }


    public function stakeHolderIds()
    {
        return $this->belongsToMany(Employee::class);
    }




}
