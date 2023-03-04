<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client_status()
    {
        return $this->belongsTo(ClientStatus::class);
    }


    public function client_compplaints()
    {

        return $this->hasMany(ClientComplaint::class);
    }
}
