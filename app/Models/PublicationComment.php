<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicationComment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function comments()
    {
        return $this->hasMany(PublicationComment::class, 'parent_id');
    }

}
