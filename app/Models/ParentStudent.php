<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentStudent extends Model
{
    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id_parent');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'id', 'user_id_student');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id_student');
    }
}
