<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{


    protected $guarded = ['id'];

    protected $table = 'options';

    public $timestamps = false;

    public function school()
    {
        return $this->belongsTo(Company::class);
    }
}
