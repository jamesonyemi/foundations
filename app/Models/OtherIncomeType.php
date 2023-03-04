<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherIncomeType extends Model
{
    protected $table = "other_income_types";

    public function gl_account_asset()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_asset_id');
    }

    public function gl_account_income()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_income_id');
    }
}
