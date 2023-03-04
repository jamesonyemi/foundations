<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementItem extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'procurement_items';

    /**
     * Each link can have many tags.
     *
     */
    public function procurementCategory()
    {
        return $this->belongsTo(ProcurementCategory::class);
    }

    public function procurements()
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }


    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function itemPrice($supplier)
    {
        return ProcurementItemSupplier::where('procurement_item_id', $this->id)->where('supplier_id', $supplier)->first();
    }
}
