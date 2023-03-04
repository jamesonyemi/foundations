<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserCalendar extends Model
{


    // Don't forget to fill this array
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $table = 'user_calendar';



    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function entries()
    {
        return $this->hasMany(UserCalendarEntry::class);
    }



}
