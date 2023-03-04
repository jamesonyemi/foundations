<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementSubCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function category()
    {
        return $this->belongsTo(ProcurementCategory::class);
    }

    public function procuements()
    {
        return $this->hasMany(Procurement::class);
    }


}
