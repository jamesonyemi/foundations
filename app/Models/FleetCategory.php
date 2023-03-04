<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'fleet_categories';

    /**
     * Each tag can have many suppliers.
     *
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function fleets()
    {
        return $this->hasMany(Fleet::class);
    }

    public function children()
    {
        return $this->hasMany(FleetCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(FleetCategory::class, 'parent_id');
    }


    public function getFullTitleAttribute()
    {
        return "{$this->parent->title} - {$this->title}" ?? "{$this->title}";
    }

}
