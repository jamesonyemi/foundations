<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'fleet';

    /**
     * Each link can have many tags.
     *
     */
    public function fleetCategory()
    {
        return $this->belongsTo(FleetCategory::class);
    }

    public function fleetMake()
    {
        return $this->belongsTo(FleetMake::class);
    }

    public function fleetType()
    {
        return $this->belongsTo(FleetType::class);
    }

    public function procurements()
    {
        return $this->hasMany(ProcurementRequestItem::class);
    }




}
