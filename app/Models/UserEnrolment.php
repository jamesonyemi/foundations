<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEnrolment extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'user_enrolments';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
