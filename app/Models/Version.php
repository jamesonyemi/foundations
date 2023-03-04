<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Version extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public $table = 'version';
}
