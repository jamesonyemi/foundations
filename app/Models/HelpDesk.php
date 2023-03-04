<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HelpDesk extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $fillable = [
        'employee_id',
        'company_year_id',
        'help_desk_number',
        'title',
        'description',
        'help_desk_status_id',
        'help_desk_category_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function employeeAttentions()
    {
        return $this->belongsToMany(Employee::class)->withTimeStamps();
    }

    public function employeeAttentionIds()
    {
        return $this->belongsToMany(Employee::class);
    }


    public function school()
    {
        return $this->belongsTo(Company::class);
    }

    public function assigned_employee()
    {
        return $this->belongsTo(Employee::class, 'employee_asigned_to_id');
    }


    public function category()
    {
        return $this->belongsTo(HelpDeskCategory::class, 'help_desk_category_id');
    }
}
