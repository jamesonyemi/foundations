<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'procurement_categories';

    /**
     * Each tag can have many suppliers.
     *
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function children()
    {
        return $this->hasMany(ProcurementCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProcurementCategory::class, 'parent_id');
    }


    public function getFullTitleAttribute()
    {
        return "{$this->parent->title} - {$this->title}" ?? "{$this->title}";
    }

    public function masterCategory()
    {
        return $this->belongsTo(ProcurementMasterCategory::class, 'procurement_master_category_id');
    }
}
