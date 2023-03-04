<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = "expenses";

    public function office()
    {
        return $this->hasOne(Office::class, 'id', 'office_id');
    }


    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'created_by_id');
    }

    public function type()
    {
        return $this->hasOne(ExpenseType::class, 'id', 'expense_type_id');
    }
}
