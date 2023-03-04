<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];



    public function districts()
    {

        return $this->hasMany(Client::class);
    }
}
