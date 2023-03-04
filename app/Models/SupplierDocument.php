<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierDocument extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    /**
     * Each link can have many tags.
     *
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

}
