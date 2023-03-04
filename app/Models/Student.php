<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Session;

class Student extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Department::class, 'section_id');
    }

    public function behavior()
    {
        return $this->belongsToMany(Behavior::class)->withTimestamps();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function studentsgroups()
    {
        return $this->belongsToMany(StudentGroup::class)->withTimestamps();
    }

    public function bed()
    {
        return $this->hasOne(DormitoryBed::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'company_id');
    }


    public function programme()
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function session()
    {
        return $this->belongsTo(\App\Models\Session::class, 'session_id');
    }

    public function academicyear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function graduationYear()
    {
        return $this->belongsTo(GraduationYear::class, 'graduation_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function upgrade()
    {
        return $this->hasOne(StudentUpgrade::class);
    }


    public function active()
    {
        return $this->hasMany(StudentStatus::class);
    }

    public function graduation()
    {
        return $this->hasOne(StudentGraduation::class);
    }

    public function deferred()
    {
        return $this->hasMany(StudentDeferral::class);
    }

    public function drop()
    {
        return $this->hasMany(StudentDrop::class);
    }

    public function admission()
    {
        return $this->hasMany(StudentAdmission::class);
    }

    public function alumni()
    {
        return $this->hasMany(Alumnus::class);
    }

    public function intakeperiod()
    {
        return $this->belongsTo(IntakePeriod::class, 'intake_period_id');
    }

    public function intakePeriodDates()
    {

        return AdmIntakeDate:: where('school_year_id', $this->school_year_id )->where('intake_period_id', $this->intake_period_id)->first();
    }



    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }

    public function admissionlevel()
    {

        return $this->belongsTo(Level::class, 'level_of_adm', 'id');
    }

    public function entrymode()
    {
        return $this->belongsTo(EntryMode::class, 'entry_mode_id', 'id');
    }

    public function maritalstatus()
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function registration()
    {
        return $this->hasMany(Registration::class,  'student_id', 'id');
    }

    public function semesterGPA($semester_id)
    {
        $regs = $this->hasMany(Registration::class,  'student_id', 'id')
                    ->where('semester_id', '=', $semester_id);
        $gpa = SUM($regs->credit);

        return $gpa;
    }

    public function CurrentRegistration()
    {

        return $this->hasMany(Registration::class)->where('school_year_id', '=', session('current_company_year'))->where('semester_id', '=', session('current_company_semester'));
    }



    public function financial()
    {
        return $this->hasMany(GeneralLedger::class)->orderBy('id', 'DESC');
    }

    public function getBalanceAttribute()
    {
        $bql = $this->financial()->sum('credit') - $this->financial()->sum('debit');
        if ($bql < 0){
            $bql = $bql * -1;
            $bql = number_format($bql,2);
            $newbql = '('. $bql.')';


            return $newbql;
        }

        elseif ($bql > 0){
            $newbql = $bql;

            return number_format($newbql,2);
        }

        else {
            return number_format($bql, 2);
        }

    }









    public function feesPayments()
    {

        return $this->hasMany(Payment::class, 'user_id', 'user_id')
                    ->where('school_year_id', '=', session('current_company_year'))
                    ->where('semester_id', '=', session('current_company_semester'))
                    ->orderBy('id', 'desc');
    }

    public function lastPayment()
    {

        return $this->hasMany(Payment::class, 'user_id', 'user_id')
            ->where('school_year_id', '=', session('current_company_year'))
            ->where('semester_id', '=', session('current_company_semester'))
            ->orderBy('id', 'desc');
    }


    public function feesStatus()
    {

        return $this->hasMany(Invoice::class, 'user_id', 'user_id')
            ->where('school_year_id', '=', session('current_company_year'))->where('semester_id', '=', session('current_company_semester'));
    }

    public function invoice()
    {

        return $this->hasMany(Invoice::class, 'user_id', 'user_id');
    }

    public function adminFeesComponents()
    {

        return $this->hasManyThrough(FeesStatus::class, Invoice::class, 'student_id', 'invoice_id')
            ->where('invoices.school_year_id', '=', $this->school_year_id)
            ->where('invoices.semester_id', '=', $this->semester_id);
    }

    public function adminFee()
    {

        return $this->hasManyThrough(FeeCategory::class, Department::class, 'id', 'section_id')
            ->where('fee_categories.section_id', '=', $this->section_id)
            ->where('fee_categories.level_id', '=', $this->level_id);
    }

    public function adminTuitionFees()
    {

        return $this->hasManyThrough(FeesStatus::class, Invoice::class, 'student_id', 'invoice_id')
            ->where('invoices.school_year_id', '=', $this->school_year_id)
            ->where('invoices.semester_id', '=', $this->semester_id)
            ->where('fees_status.title', '=', 'Tuition Fee');


    }

    public function adminSRCFees()
    {

        return $this->hasManyThrough(FeesStatus::class, Invoice::class, 'student_id', 'invoice_id')
            ->where('invoices.school_year_id', '=', $this->school_year_id)
            ->where('invoices.semester_id', '=', $this->semester_id)
            ->where('fees_status.title', '=', 'SRC');
    }


    public function SemesterFeesComponents()
    {

        return $this->hasManyThrough(FeesStatus::class, Invoice::class, 'student_id', 'invoice_id')
            ->where('invoices.school_year_id', '=', $this->school_year_id)
            ->where('invoices.semester_id', '=', $this->semester_id);
    }

    public function FeeStatement($company_year_id, $school_semester_id)
    {

        return $this->hasManyThrough(FeesStatus::class, Invoice::class, 'student_id', 'invoice_id')
            ->where('invoices.school_year_id', '=', $company_year_id)
            ->where('invoices.semester_id', '=', $school_semester_id);
    }


    public function InvoiceHistory($company_year_id, $school_semester_id)
    {

        return $this->hasMany(Invoice::class, 'user_id', 'user_id')
            ->where('school_year_id', '=', $company_year_id)
            ->where('semester_id', '=', $school_semester_id);
    }


    public function AllRegistration()
    {

        return $this->hasMany(Registration::class);
    }


    public function religion()
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }

    public function courses($level, $semester)
    {
        return $this->belongsToMany(Subject::class, 'registrations', 'student_id', 'subject_id')
            ->withPivot('user_id', 'level_id', 'school_year_id', 'semester_id', 'grade', 'mid_sem', 'credit', 'exams', 'exam_score')
            ->wherePivot('level_id', '=', $level)
            ->wherePivot('semester_id', '=', $semester);
    }

    public function Notes()
    {
        return $this->hasMany(StudentNote::class, 'student_id');
    }

 public function Notes2()
    {
        return $this->hasMany(StudentNote::class, 'student_id');
    }

}
