<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function region()
    {
        return $this->belongsTo(Region::class);
    }


    public function clients()
    {

        return $this->hasMany(Client::class);
    }
}
