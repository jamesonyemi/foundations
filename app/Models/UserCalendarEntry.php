<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserCalendarEntry extends Model
{


    // Don't forget to fill this array
    protected $fillable = [];
    protected $guarded = ['id'];



    public function calendar()
    {
        return $this->belongsTo(UserCalendar::class);
    }






}
