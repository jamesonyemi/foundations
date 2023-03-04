<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetOperation extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each tag can have many suppliers.
     *
     */



    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }

    public function driver_employee()
    {
        return $this->belongsTo(Employee::class, 'driver_employee_id');
    }



}
