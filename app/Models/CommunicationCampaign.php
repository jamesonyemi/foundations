<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationCampaign extends Model
{
    protected $table = "communication_campaigns";

    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'created_by_id');
    }
}
