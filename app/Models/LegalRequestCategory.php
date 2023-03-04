<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalRequestCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each tag can have many suppliers.
     *
     */


    public function legalRequests()
    {
        return $this->hasMany(LegalRequest::class);
    }




}
