<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyTip extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function comments()
    {
        return $this->hasMany(SafetyTipComment::class)->orderBy('id', 'Desc');
    }


}
