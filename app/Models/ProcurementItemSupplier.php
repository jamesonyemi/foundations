<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementItemSupplier extends Model
{
    //

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'procurement_item_supplier';

    /**
     * Each link can have many tags.
     *
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    public function procurementItem()
    {
        return $this->belongsTo(ProcurementItem::class);
    }
}
