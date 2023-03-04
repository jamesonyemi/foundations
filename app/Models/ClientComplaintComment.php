<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientComplaintComment extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];


    public function client_complaint()
    {
        return $this->belongsTo(ClientComplaint::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }


}
