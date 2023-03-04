<?php

namespace App\Models;

use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudyMaterial extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    protected $appends = ['file_url'];

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setDateOffAttribute($date)
    {
        $this->attributes['date_off'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getDateOffAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }

    public function document_type()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function student_group()
    {
        return $this->belongsTo(StudentGroup::class);
    }

    public function getFileUrlAttribute()
    {
        $file = $this->attributes['file'];
        return base_path('public/uploads/study_material') . '/' . $file;
    }
}
