<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Customer extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
