<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Applicant_school extends Model
{

    protected $guarded = ['id'];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function School()
    {
        return $this->belongsTo(ShsSchool::class, 'shs_company_id');
    }

    public function qualification()
    {
        return $this->belongsTo(Qualification::class, 'qualification_id');
    }
}
