<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $table = "payment_details";

    public function type()
    {
        return $this->hasOne(PaymentType::class, 'id', 'payment_type_id');
    }
}
