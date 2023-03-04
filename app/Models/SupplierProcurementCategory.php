<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierProcurementCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'supplier_procurement_category';

    /**
     * Each tag can have many suppliers.
     *
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }
}
