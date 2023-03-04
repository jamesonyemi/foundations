<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineExamUserAnswer extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    protected $appends = ['sum_points', 'users'];

    public function online_exam()
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function online_exam_question()
    {
        return $this->belongsTo(OnlineExamQuestion::class);
    }

    public function online_exam_answer()
    {
        return $this->belongsTo(OnlineExamAnswer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSumPointsAttribute()
    {
        $points = $this->where('online_exam_id', $this->attributes['online_exam_id'])
            ->where('user_id', $this->attributes['user_id'])
            ->sum('points');
        return $points;
    }
}
