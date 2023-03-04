<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class WaecSubjectGrade extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function school()
    {
        return $this->belongsTo(User::class, 'company_id');
    }


    public function applicationType()
    {
        return $this->belongsTo(ApplicationType::class, 'application_type_id');
    }
}
