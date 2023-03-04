<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectAnswer extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function subject_question()
    {
        return $this->belongsTo(SubjectQuestion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
