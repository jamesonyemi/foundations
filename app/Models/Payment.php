<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Payment extends Model
{
    use SoftDeletes;
    use Billable;

    protected $dates = [ 'deleted_at', 'trial_ends_at', 'subscription_ends_at' ];

    protected $guarded = array ( 'id' );

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function student()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function administrator()
    {
        return $this->belongsTo(User::class, 'officer_user_id', 'id');
    }

    public function academicyear()
    {
        return $this->belongsTo(CompanyYear::class, 'company_year_id', 'id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id');
    }
}
