<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCaseComment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function legalCase()
    {
        return $this->belongsTo(LegalCase::class);
    }

   public function employee()
    {
        return $this->belongsTo(Employee::class);
    }



}
