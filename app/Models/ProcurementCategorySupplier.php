<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementCategorySupplier extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'procurement_category_supplier';

    /**
     * Each tag can have many suppliers.
     *
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }
}
