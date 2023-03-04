<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalFirm extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
/*    public function legalCaseCategory()
    {
        return $this->belongsTo(LegalCaseCategory::class);
    }*/

    public function legalCases()
    {
        return $this->hasMany(LegalCase::class);
    }

}
