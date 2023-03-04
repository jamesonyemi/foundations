<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeIdeaCampaignDocument extends Model
{
    //
    /*use SoftDeletes;*/

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function employeeIdeaCampaign()
    {
        return $this->belongsTo(EmployeeIdeaCampaign::class);
    }

}
