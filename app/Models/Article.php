<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    //
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('article', function (Builder $builder) {
            /*$school = Company::find(session('current_company'));;*//*
            if (session('current_company_sector') > 0) {
                $builder->where('sector_id', session('current_company_sector'));
            }

            else {
                $builder->where('.kras.company_id', session('current_company'));;
            }*/

            @$school= Company::find(session('current_company'));;

            if (isset($school))
            {
                if ($school->stand_alone == 1) {
                    $builder->whereHas('employee', function ($q) {
                        $q->where('employees.company_id', session('current_company'));;
                    });
                }
                else {
                    $builder->whereHas('employee.school.sector', function ($q) use($school) {
                        $q->where('sectors.group_id', $school->sector->group_id);
                    });

                }

            }

        });


    }



    public function comments()
    {
        return $this->hasMany(ArticleComment::class)->orderByDesc('id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function artcle_category()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }



    public function getPictureAttribute()
    {
        @$picture = $this->attributes['image'];
        if (empty(@$picture)) {
            return asset('uploads/avatar/avatar/m.png');
        }

        return asset('uploads/news') . '/' . @$picture;
    }



    public function getThumbnailAttribute()
    {
        @$picture = $this->attributes['image'];
        if (empty(@$picture)) {
            return asset('uploads/avatar/avatar/m.png');
        }

        return asset('uploads/news') . '/' . @ 'thumb_'.$picture;
    }

}
