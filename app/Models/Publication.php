<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];



    public function publicationCategory()
    {
        return $this->belongsTo(PublicationCategory::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }



    public function comments()
    {
        return $this->hasMany(PublicationComment::class)->orderByDesc('id');
    }



    public function getPictureAttribute()
    {
        @$picture = $this->attributes['picture'];
        if (empty(@$picture)) {
            return asset('uploads/avatar/avatar/m.png');
        }

        return asset('uploads/publications') . '/' . @$picture;
    }



    public function getThumbnailAttribute()
    {
        @$picture = $this->attributes['picture'];
        if (empty(@$picture)) {
            return asset('uploads/avatar/avatar/m.png');
        }

        return asset('uploads/publications') . '/' . @ 'thumb_'.$picture;
    }


}
