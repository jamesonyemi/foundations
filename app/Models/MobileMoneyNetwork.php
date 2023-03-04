<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileMoneyNetwork extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];



    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

}
