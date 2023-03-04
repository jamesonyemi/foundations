<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{

    protected $table = "offices";

    public function parent()
    {
        return $this->hasOne(Office::class, 'id', 'parent_id');
    }


    public function charges()
    {
        return $this->hasMany(LoanCharge::class, 'loan_id', 'id');
    }



    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class, 'loan_id', 'id')->orderBy('date', 'asc');;
    }



    public function collateral()
    {
        return $this->hasMany(Collateral::class, 'loan_id', 'id');
    }




    public function loan_product()
    {
        return $this->hasOne(LoanProduct::class, 'id', 'loan_product_id');
    }


    public function loan_officer()
    {
        return $this->hasOne(User::class, 'id', 'loan_officer_id');
    }
}
