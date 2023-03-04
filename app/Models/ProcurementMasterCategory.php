<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementMasterCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'procurement_master_categories';

    /**
     * Each tag can have many suppliers.
     *
     */
/*    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }*/

    public function categories()
    {
        return $this->hasMany(ProcurementCategory::class);
    }
}
