<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankBranch extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }



    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

}
