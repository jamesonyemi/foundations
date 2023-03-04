<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    protected $table = "asset_types";


    public function assets()
    {
        return $this->hasMany(Asset::class, 'asset_type_id', 'id');
    }

    public function gl_account_asset()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_asset_id');
    }

    public function gl_account_expense()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_expense_id');
    }

    public function gl_account_fixed_asset()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_fixed_asset_id');
    }

    public function gl_account_contra_asset()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_contra_asset_id');
    }

    public function gl_account_liability()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_liability_id');
    }

    public function gl_account_income()
    {
        return $this->hasOne(GlAccount::class, 'id', 'gl_account_income_id');
    }
}
