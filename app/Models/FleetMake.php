<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetMake extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each tag can have many suppliers.
     *
     */


    public function fleets()
    {
        return $this->hasMany(Fleet::class);
    }



}
