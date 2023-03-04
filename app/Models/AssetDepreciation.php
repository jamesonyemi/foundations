<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    protected $table = "asset_depreciation";

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function asset()
    {
        return $this->hasOne(Asset::class, 'id', 'asset_id');
    }
}
