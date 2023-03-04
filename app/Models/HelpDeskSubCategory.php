<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpDeskSubCategory extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function category()
    {
        return $this->belongsTo(HelpDeskCategory::class);
    }

    public function help_desks()
    {
        return $this->hasMany(HelpDesk::class);
    }


}
