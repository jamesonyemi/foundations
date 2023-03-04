<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectComponent extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each tag can have many suppliers.
     *
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }


    public function project_artisan()
    {
        return $this->belongsTo(ProjectArtisan::class);
    }


}
