<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentItem extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $appends = ['item_name_price','item_sub_price'];

    protected $guarded = ['id'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
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
        return ($this->option->value * $this->attributes['quantity']).' '.Settings::get('currency');
    }
}
