<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementRequestItem extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function procurement_request()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }


    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    public function item()
    {
        return $this->belongsTo(ProcurementItem::class, 'procurement_item_id');
    }
}
