<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'sliders';

    protected $guarded  = ['id'];


    public function getImageAttribute()
    {
        $picture = $this->attributes['image'];
        return asset('uploads/slider') . '/' . $picture;
    }
}
