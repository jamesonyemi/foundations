<?php

namespace App\Models;

use App\Helpers\CustomFormUserFields;
use Carbon\Carbon;
use App\Helpers\Settings;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Sentinel;
use Laravel\Sanctum\HasApiTokens;

class  User extends EloquentUser implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use HasApiTokens, Authenticatable, Authorizable, CanResetPassword, Notifiable, SoftDeletes;

    /*protected $dates = ['deleted_at'];*/

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'];

    protected $guarded = ['id'];

    protected $fillable = ['email','email2', 'password', 'permissions', 'first_name', 'middle_name', 'last_name', 'address', 'address_line2', 'address_line3', 'picture', 'mobile'
        , 'mobile2', 'phone', 'gender', 'birth_date', 'birth_city', 'about_me', 'get_sms', 'title', 'maiden_name', 'height', 'weight', 'home_town', 'spouse_name', 'mother_name', 'father_name'];

    protected $appends = ['full_name', 'picture', 'custom_fields','full_name_email', 'age'];

    public function date_format()
    {
        return Settings::get('date_format');
    }

    public function setBirthDateAttribute($date)
    {
        if ($date!=null && $date!="") {
            $this->attributes['birth_date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
        }
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->attributes['birth_date'])->age;
    }

    public function getBirthDateAttribute($birth_date)
    {
        if ($birth_date == "0000-00-00" || $birth_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($birth_date));
        }
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->middle_name} {$this->last_name}";
    }

    public function getFullNameEmailAttribute()
    {
        return $this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name . ' (' . $this->email.')';
    }

    public function getGenderAttribute()
    {
        @$gender = $this->attributes['gender'];
        return @(($gender == 0) ? 'Female' : 'Male');

    }

    public function getCustomFieldsAttribute()
    {
        return CustomFormUserFields::getCustomUserFieldValueList($this->id);
    }

    public function getPictureAttribute()
    {
        @$picture = $this->attributes['picture'];
        @$gender = $this->attributes['gender'];
        if (empty(@$picture)) {
            return asset('uploads/avatar/avatar') . (($gender == 0) ? 'f' : 'm') . '.png';
        }

        return asset('uploads/avatar') . '/' . @$picture;
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'user_id_receiver');
    }

    public function smsMessages()
    {
        return $this->hasMany(SmsMessage::class, 'user_id')->orderBy('id', 'DESC');
    }

    public function parents()
    {
        return $this->hasMany(ParentStudent::class, 'user_id_student', 'id');
    }

    public function reserved_books()
    {
        return $this->hasMany(BookUser::class, 'user_id', 'id');
    }

    public function get_books()
    {
        return $this->hasMany(GetBook::class, 'user_id', 'id');
    }
    public function return_books()
    {
        return $this->hasMany(ReturnBook::class, 'user_id', 'id');
    }

    public function visitor()
    {
        return $this->hasMany(Visitor::class, 'user_id');
    }



    public function employee()
    {
        return $this->hasOne(Employee::class);
    }


    public function student()
    {
        return $this->belongsTo(Student::class, 'id');
    }

    public function moodleCourses()
    {
        return $this->hasMany(UserEnrollment::class, 'userid', 'id');
    }

    public function applicant()
    {
        return $this->hasMany(Applicant::class, 'user_id');
    }

    public function parent_student()
    {
        return $this->hasMany(ParentStudent::class, 'user_id_student');
    }

    public function student_parent()
    {
        return $this->hasMany(ParentStudent::class, 'user_id_parent');
    }

    public function school_teacher()
    {
        return $this->hasOne(TeacherSchool::class, 'user_id');
    }

    public function ticketUser()
    {
        return $this->hasOne(TicketUser::class, 'id');
    }

    public function school_admin()
    {
        return $this->hasOne(SchoolAdmin::class, 'user_id');
    }


    public function invoice()
    {

        return $this->hasMany(Invoice::class, 'user_id');
                    /*->where('company_year_id','=',$this->currentyear()->id)
                    ->where('semester_id','=',$this->currentsemister()->id);*/
    }



    public function authorized($permission = null)
    {
        return @array_key_exists($permission, $this->permissions);
    }

    public function routeNotificationForSMS()
    {
        return $string = str_replace(' ', '', $this->mobile); // where phone is a cloumn in your users table;

    }

    public function routeNotificationForNexmo($notification)
    {
        return $this->mobile;
    }
}
