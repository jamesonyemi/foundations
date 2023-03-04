<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementPlan extends Model
{
    //

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function procurementItem()
    {
        return $this->belongsTo(ProcurementItem::class);
    }
}
