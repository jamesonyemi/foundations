<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'suppliers';

    /**
     * Each link can have many tags.
     *
     */
    public function procurementCategories()
    {
        return $this->belongsToMany(ProcurementCategory::class)->withTimeStamps();
    }

    public function procurementCategoryIds()
    {
        return $this->belongsToMany(ProcurementCategory::class);
    }

    public function items()
    {
        return $this->belongsToMany(ProcurementItem::class)->withTimeStamps();
    }

    public function documents()
    {
        return $this->hasMany(SupplierDocument::class);
    }

    public function itemPrice($item)
    {
        return ProcurementItemSupplier::where('supplier_id', $this->id)->where('procurement_item_id', $item)->first();
    }
}
