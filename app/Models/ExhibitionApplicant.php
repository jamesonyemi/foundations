<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ExhibitionApplicant extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    protected $appends = ['full_name', 'full_name_email', 'email'];

    use Notifiable;

    public function interests()
    {
        return $this->belongsToMany(ExhibitionInterest::class);
    }


    public function getFullNameAttribute()
    {
        return "{$this->first_name}  {$this->last_name}";
    }

    public function getFullNameEmailAttribute()
    {
        return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name . ' (' . $this->email_address.')';
    }

    public function getEmailAttribute()
    {
        return $this->email_address ;
    }
}
