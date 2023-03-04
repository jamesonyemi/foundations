<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SafetyTipComment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function safety_tip()
    {
        return $this->belongsTo(SafetyTip::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }


}
