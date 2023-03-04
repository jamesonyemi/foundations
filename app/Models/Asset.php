<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = "assets";

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function type()
    {
        return $this->hasOne(AssetType::class, 'id', 'asset_type_id');
    }

    public function office()
    {
        return $this->hasOne(Office::class, 'id', 'office_id');
    }

    public function depreciation()
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id', 'id')->orderBy('year', 'asc');
    }
}
