<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlClosure extends Model
{
    protected $table = "gl_closures";

    public function office()
    {
        return $this->hasOne(Office::class, 'id', 'office_id');
    }

    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'created_by_id');
    }


}
