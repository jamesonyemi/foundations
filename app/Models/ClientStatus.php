<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientStatus extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function clients()
    {

        return $this->hasMany(Client::class);
    }
}
