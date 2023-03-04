<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomUserField extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function values()
    {
        return $this->hasMany(CustomUserFieldValue::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
