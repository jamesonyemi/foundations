<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class AccountType extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];


    public function accounts()
    {
        return $this->hasMany(Account::class, 'account_type_id');
    }

    public function getNameWithRangeAttribute()
    {
        return  $this->title. ' (' . $this->accounts()->first()->code . ' - ' . $this->accounts()->orderBy('code', 'DESC')->first()->code.')';
    }

    public function getNameWithBalanceAttribute()
    {
        return  $this->title. ' ' . $this->balance.'';
    }

    public function getNameWithRangewithBalanceAttribute()
    {
        return  $this->title. ' (' . $this->accounts()->first()->code . ' - ' . $this->accounts()->orderBy('code', 'DESC')->first()->code. ') || ' .$this->balance .'';
    }

    public function postedJournal()
    {

        return $this->hasManyThrough(GeneralLedger::class, Account::class, 'account_type_id', 'account_id');
    }

    public function getBalanceAttribute()
    {
        $bql = $this->postedJournal()->sum('amount');
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
        }

    }


}
