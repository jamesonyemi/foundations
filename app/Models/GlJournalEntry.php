<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlJournalEntry extends Model
{
    protected $table = "gl_journal_entries";

    public function parent()
    {
        return $this->hasOne(GlAccount::class, 'id', 'parent_id');
    }
    public function gl_account()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_id');
    }
    public function next_of_kin()
    {
        return $this->hasMany(ClientNextOfKin::class, 'client_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(LoanComment::class, 'loan_id', 'id')->orderBy('created_at', 'desc');;
    }

    public function transactions()
    {
        return $this->hasMany(LoanTransaction::class, 'loan_id', 'id')->orderBy('date', 'asc');;
    }

    public function payments()
    {
        return $this->hasMany(LoanRepayment::class, 'loan_id', 'id')->orderBy('collection_date', 'asc');;
    }

    public function collateral()
    {
        return $this->hasMany(Collateral::class, 'loan_id', 'id');
    }

    public function guarantors()
    {
        return $this->hasMany(LoanGuarantor::class, 'loan_id', 'id');
    }

    public function borrower()
    {
        return $this->hasOne(Borrower::class, 'id', 'borrower_id');
    }

    public function loan_product()
    {
        return $this->hasOne(LoanProduct::class, 'id', 'loan_product_id');
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function office()
    {
        return $this->hasOne(Office::class, 'id', 'office_id');
    }

    public function staff()
    {
        return $this->hasOne(User::class, 'id', 'staff_id');
    }
}
