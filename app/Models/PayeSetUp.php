<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class PayeSetUp extends Model
{
    /*use SoftDeletes;*/
    protected $guarded = ['id'];
    protected $table = 'paye_setups';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tax_law()
    {
        return $this->belongsTo(PrTaxLaw::class, 'pr_tax_law_id');
    }
}
