<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each tag can have many suppliers.
     *
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function children()
    {
        return $this->hasMany(ProcurementCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProcurementCategory::class, 'parent_id');
    }

}
