<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class ApplicationToken extends Model
{

    protected $guarded = ['id'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'used_by_applicant_id');
    }


    public function School()
    {
        return $this->belongsTo(School::class, 'shs_company_id');
    }


}
