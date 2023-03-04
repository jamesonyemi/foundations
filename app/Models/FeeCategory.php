<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCategory extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];


    public function school()
    {
        return $this->belongsTo(School::class, 'company_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }


    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }


    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function feesPeriod()
    {
        return $this->belongsTo(FeesPeriod::class, 'fees_period_id');
    }

    public function getTitleWithSchoolWithCurrencyAttribute()
    {
        return  $this->title. ' (' . $this->section->title . ') - (' .$this->currency->name .')';
    }

    public function getTitleWithSchoolWithCurrencyWithAmountAttribute()
    {
        return  $this->title. ' (' . $this->section->title . ') - (' .$this->currency->name . ') - (' .$this->amount  .')';
    }

    public function getTitleWithAmountAttribute()
    {
        return  $this->title. ' (' . $this->amount . ')';
    }

    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_id');
    }

}
