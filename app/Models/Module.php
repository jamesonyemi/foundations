<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{


    public $timestamps = false;
    protected $table = "modules";

    public function parent()
    {
        return $this->hasOne(Module::class, 'id', 'parent_id');
    }
}
