<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Session;

class Account extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

   /* protected $appends = ['balance'];*/

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setJournalDateAttribute($date)
    {
        if ($date!=null && $date!="") {
            $this->attributes['journal_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
        }
    }

    public function getJournalDateAttribute($journal_date)
    {
        if ($journal_date == "0000-00-00" || $journal_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($journal_date));
        }
    }

    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function type()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function getAccountWithNumberAttribute()
    {
        return  $this->title. ' (' . $this->code .')';
    }



    public function getAccountWithBalanceAttribute()
    {
        return  $this->title. ' ' . $this->balance .'';
    }

    public function getAccountWithNumberWithBalanceAttribute()
    {
        return  $this->title. ' (' . $this->code . ') - (' .$this->balance .')';
    }

    public function journal()
    {
        return $this->hasMany(Journal::class, 'account_id');
    }

    public function postedJournal()
    {
        return $this->hasMany(GeneralLedger::class, 'account_id')->orderBy('id', 'DESC');
    }

    public function getBalanceAttribute()
    {

        $transactions = \App\Helpers\GeneralHelper::gl_account_balance($this->id);
        $balance = 0;

        if (!empty($transactions)) {
            if ($this->type == "Asset" || $this->type == "Expense") {
                $balance = $transactions->debit - $transactions->credit;
            }
            if ($this->type == "Liability" || $this->type == "Income" || $this->type == "Equity") {
                $balance = $transactions->credit - $transactions->debit;
            }
        }
        return number_format($balance, 2);
        /*$bql = $this->postedJournal()->sum('amount');
        if ($bql < 0){
            $bql = $bql * -1;
            $bql = number_format($bql,2);
            $newbql = '('. $bql.')';


            return $newbql;
        }

        elseif ($bql > 0){
            $newbql = $bql;

            return number_format($newbql,2);
        }

        else {
            return number_format($bql, 2);
        }*/

    }


}
