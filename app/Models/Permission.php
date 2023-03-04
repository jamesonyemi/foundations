<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{


    public $timestamps = false;
    protected $table = "permissions";

    public function parent()
    {
        return $this->hasOne(Permission::class, 'id', 'parent_id');
    }
}
