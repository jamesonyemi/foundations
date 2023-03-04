<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class ModelRoles extends Model
{
    protected $table = 'fx_model_has_roles';

    protected $fillable = [
        'role_id', 'model_type', 'model_id',
    ];
}
