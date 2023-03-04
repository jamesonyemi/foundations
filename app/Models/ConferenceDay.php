<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class ConferenceDay extends Model
{
    use SoftDeletes;

    protected $guarded = array('id');

    protected $dates = ['deleted_at'];

    public function sessions()
    {
        return $this->hasMany(ConferenceSession::class);
    }
}
