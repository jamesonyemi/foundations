<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomUserFieldValue extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function custom_field()
    {
        return $this->belongsTo(CustomUserField::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
