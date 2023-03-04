<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $appends = ['item_name_price','item_sub_price'];

    protected $guarded = ['id'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
    public function getItemNamePriceAttribute()
    {
        return $this->option->title.' - '.$this->option->value.' '.Settings::get('currency');
    }
    public function getItemSubPriceAttribute()
    {
        return ((is_numeric($this->option->value)?$this->option->value:0) * $this->attributes['quantity']).' '.Settings::get('currency');
    }
}
