<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Enrol extends Model
{
    protected $table = 'enrol';


    public function course()
    {
        return $this->belongsTo(Subject::class, 'courseid', 'id');
    }
}
