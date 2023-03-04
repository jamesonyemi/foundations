<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    protected $appends = ['remain'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function setSubjectIdAttribute($subject_id)
    {
        if ($subject_id) {
            $this->attributes['subject_id'] = $subject_id;
        } else {
            $this->attributes['subject_id'] = null;
        }
    }

    public function availableCopies()
    {
        $issued = GetBook::where('book_id', $this->attributes['id'])
            ->sum('get_books_count');
        $returned = ReturnBook::where('book_id', $this->attributes['id'])
            ->sum('return_books_count');
        return $this->attributes['quantity'] - ($issued - $returned);
    }

    public function getRemainAttribute()
    {
        return $this->availableCopies();
    }

    public function category()
    {
        return $this->belongsTo(Option::class, 'option_id_category');
    }

    public function setOptionIdCategoryAttribute($category_id)
    {
        if ($category_id) {
            $this->attributes['option_id_category'] = $category_id;
        } else {
            $this->attributes['option_id_category'] = null;
        }
    }

    public function borrowingPeriod()
    {
        return $this->belongsTo(Option::class, 'option_id_borrowing_period');
    }

    public function setOptionIdBorrowingPeriodAttribute($borrowing_period_id)
    {
        if ($borrowing_period_id) {
            $this->attributes['option_id_borrowing_period'] = $borrowing_period_id;
        } else {
            $this->attributes['option_id_borrowing_period'] = null;
        }
    }
}
