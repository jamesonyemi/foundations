<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class ItemNumberSeries extends Model
{
    /*use SoftDeletes;*/
    protected $guarded = ['id'];
    protected $table = 'item_number_series';

}
