<?php

namespace App\Models;

use App\Helpers\GeneralHelper;
use App\Support\HasAdvancedFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Session;
use function PHPUnit\Framework\isNull;

class Employee extends Model
{
    use HasAdvancedFilter;
    use SoftDeletes;

    protected $dates = ['deleted_at', 'contract_end_date', 'join_date'];

    protected $guarded = ['id'];


    protected $orderable = [
        'id',
        'user.name',
        'eid',
        'company.title',
        'department.title',
        'social_security_number',
        'bank_account_number',
        'join_date',
        'contract_end_date',
        'tin_number',
        'bank.title',
        'residencial_address',
        'disability',
        'status',
        'global',
        'country.title',
        'position.title',
        'passport_number',
        'driver_license_number',
    ];

    protected $filterable = [
        'id',
        'user.name',
        'eid',
        'company.title',
        'department.title',
        'social_security_number',
        'bank_account_number',
        'join_date',
        'contract_end_date',
        'tin_number',
        'bank.title',
        'residencial_address',
        'disability',
        'status',
        'global',
        'country.title',
        'position.title',
        'passport_number',
        'driver_license_number',
    ];



    public function active()
    {
        return $this->hasMany(StudentStatus::class,'employee_id')
            ->where( 'student_statuses.company_year_id',
                session( 'current_company_year' ) );
    }


    public function sessionAttendance()
    {
        return $this->hasMany(SessionAttendance::class,'employee_id')
            ->where( 'session_attendances.company_year_id',
                session( 'current_company_year' ) );
    }



    public function attended()
    {
        return $this->hasMany(StudentStatus::class,'employee_id')
            ->where( 'student_statuses.company_year_id',
                session( 'current_company_year' ) )
            ->where( 'student_statuses.attended',
                1 );

    }

    public function attendedSession()
    {
        return $this->hasMany(StudentStatus::class,'employee_id')
            ->where( 'student_statuses.company_year_id',
                session( 'current_company_year' ) )
            ->where( 'student_statuses.attended',
                1 );

    }


    public function isActive()
    {
        return $this->hasMany(StudentStatus::class)
            ->where( 'company_year_id', '=',
                session( 'current_company_year' ) );
    }



    public function committee()
    {
        return $this->belongsTo( Committee::class, 'committee_id' );
    }


    public function confirmStatus(){
        return $this->hasMany(StudentStatus::class,'employee_id');
    }
    public function checkStatus(){
        return $this->hasMany(StudentStatus::class,'employee_id');
    }


    public function dormitory() {
        return $this->belongsToMany( Dormitory::class, 'registrations', 'employee_id', 'dormitory_id' )
            ->withPivot( 'user_id')
            /* ->wherePivot( 'level_id', '=', $level )
             ->wherePivot( 'semester_id', '=', $semester )*/;
    }


    public function center()
    {

        return $this->belongsTo(Center::class);

    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }


    public function getGrossPayAttribute()
    {
        return $this->basic_pay + $this->payrollComponents()->sum('amount');
    }



    public function getNetPayAttribute()
    {
        return $this->getTaxableIncomeAttribute() -  $this->getPayeAttribute();
    }



    public function getTaxableIncomeAttribute()
    {
        return GeneralHelper::calculateTaxableIncome($this->getGrossPayAttribute());
    }


    public function getPayeAttribute()
    {
        return GeneralHelper::calculatePaye($this->getGrossPayAttribute());
    }




    public function payrollComponents()
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }



    public function payrollPeriodTransactions()
    {
        return $this->hasMany(PayrollPeriodTransaction::class);
    }


    public function payroll_components()
    {
        return $this->hasMany(EmployeePayrollComponent::class);
    }



    public function mobileMoneyNetwork()
    {
        return $this->belongsTo(MobileMoneyNetwork::class);
    }


    public function salary_notch()
    {
        return $this->belongsTo(SalaryNotch::class, 'salary_notch_id');
    }

    public function bankBranch()
    {
        return $this->belongsTo(BankBranch::class, 'bank_branch_id');
    }

    public function postingGroup()
    {
        return $this->belongsTo(EmployeePostingGroup::class);
    }


    public function getDesignation()
    {
        return $this->belongsTo('App\Models\Designation', 'designation', 'id');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\Employee_document');
    }

    public function competencies()
    {
        return $this->hasMany(EmployeeCompetencyMatrix::class);
    }


    public function perspective_weights()
    {
        return $this->hasMany(PerspectiveWeight::class)->where('company_year_id', session('current_company_year'));
    }

    public function employee_competencies()
    {

        return $this->hasManyThrough(Competency::class, EmployeeCompetencyMatrix::class, 'employee_id', 'id', 'id', 'competency_id')->distinct();

    }


    public function getCompetencyScoreAttribute()
    {
        @$data = @GeneralHelper::employee_competency_score($this->id);

        return number_format(@$data,2);
    }



    public function getStandingAttribute()
    {
        @$competency = number_format(GeneralHelper::employee_competency_score($this->id),2);
        @$performance = number_format(GeneralHelper::employee_total_score($this->id, session('current_company_year')),2);

       if ($competency >= 90 && $performance >= 90){
           $standing = 'Top Talent';
       }
       elseif ($competency >= 90 && $performance >= 50 && $performance < 90){
           $standing = 'Rising Star';
       }
       elseif ($competency >= 70  && $competency < 90 && $performance >= 90 && $performance < 90){
           $standing = 'Valued Contributor';
       }
       elseif ($competency >= 70 && $performance <= 50 && $performance >= 50){
           $standing = 'Emerging Potential';
       }
       else
           $standing = 'None';
       return $standing;
    }



    public function getCompetencyScoreGradeAttribute()
    {
        @$data = GeneralHelper::employee_competency_score($this->id) ?? '';
        @$score = number_format(@$data,2);
        @$grade =  MarkValue::where('company_id', session('current_company'))
            ->where(function ($q) use ($score){
                $q->where('min_score', '<=', $score);
                $q->where('max_score', '>=', $score);
            })->first()->grade;
        return $grade;
    }

    public function getPerformanceScoreGradeAttribute()
    {
        $data = @GeneralHelper::employee_total_score($this->id, session('current_company_year'));
        $score = @number_format(@$data,2);
        $grade =  @PerformanceScoreGrade::where(function ($q) use ($score){
                $q->where('min_score', '<=', $score);
                $q->where('max_score', '>=', $score);
            })->first()->grade ?? '';
        return @$grade;

    }

    public function getMyCompetenciesAttribute()
    {
        return EmployeeCompetencyMatrix::where('employee_id', $this->id)
            ->where('company_year_id', session('current_company_year'))
            ->orderBy('competency_id')
            ->get();
    }

    public function getCompetencyIdsAttribute()
    {
        return $this->competencies()
            ->get()
            ->pluck('competency_matrix_id')
            ->toArray();
    }

    public function getCompetencyWeightsAttribute()
    {
        return $this->competencies()
            ->get()
            ->pluck('competency_matrix_id')
            ->toArray();
    }

    public function getCompetencyGapAttribute()
    {
        return $this->expected_competencies - $this->competencies()->count();
    }

    public function getFullNameTitleAttribute()
    {
        if (isset($this->position->title))
        {
        return "{$this->user->full_name} - {$this->position->title}" ?? "";
        }
        else
            return "{$this->user->full_name}";
    }

    public function performance_improvements()
    {
        return $this->hasMany(PerformanceImprovement::class, 'employee_id');
    }

    public function qualifications()
    {
        return $this->hasMany(EmployeeQualification::class);
    }



    public function getQualificationGapAttribute()
    {
        return $this->expected_qualifications - $this->qualifications()->count();
    }

    public function getQualificationIdsAttribute()
    {
        return $this->qualifications()
            ->get()
            ->pluck('qualification_id')
            ->toArray();
    }

    public function salaries()
    {
        return $this->hasMany('App\Models\Salary');
    }

    public function awards()
    {
        return $this->hasMany('App\Models\Award');
    }

    public function bank_details()
    {
        return $this->hasOne('App\Models\Bank_detail');
    }

    // get attendances
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }




    public function dailyActivities()
    {
        return $this->hasMany(DailyActivity::class, 'employee_id');
    }


    public function dailyAttendance($month, $year)
    {
        return $this->hasMany(DailyAttendance::class, 'employee_id')
            ->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function dailyAttendance2()
    {
        return $this->hasMany(DailyAttendance::class, 'employee_id');
    }

    public function weeklyAttendance($date)
    {
        return $this->hasMany(DailyAttendance::class, 'employee_id')
            ->whereBetween('date', [Carbon::parse($date)->startOfWeek(), Carbon::parse($date)->endOfWeek()]);

    }



    // get attendances
    public function leaveapplications()
    {
        return $this->hasMany(StaffLeave::class)->whereNotIn('staff_leave_type_id', [2,3,6,7])->where('approved', 1)->where('company_year_id', session('current_company_year'));
    }


    // get opending leave approval
    public function unapprovedLeave()
    {
        return $this->hasMany(StaffLeave::class)->where('approved', '!=', 1)->where('company_year_id', session('current_company_year'));
    }



    // get opending leave approval
    public function getLeaveApprovalPendingAttribute()
    {
        if ($this->unapprovedLeave()->count() > 0)
        {
            return true;
        }
        else
            return false;
    }


    // get pending leave approval
    public function is_supervisor($id)
    {
        $supervisors = EmployeeSupervisor::where('employee_id', $this->id)->whereIn('employee_supervisor_id', [$id])->first();
        if ($supervisors)
        {
            return true;
        }

        else return false;

    }



    // get attendances
    public function outstanding_leave_lastYear()
    {
        return $this->hasMany(StaffLeave::class)->where('approved', 1)->whereNotIn('staff_leave_type_id', [2,3,6,7])->whereYear('created_at', '=', date('Y') - 1);
    }


    public static function currentMonthBirthday($company_id)
    {
        $birthdays = Employee::where('company_id', $company_id)->select('full_name', 'date_of_birth', 'profile_image')
            ->whereRaw("MONTH(date_of_birth) = ?", [date('m')])->where('status', '=', 'active')
            ->orderBy('date_of_birth', 'asc')->get();

        return $birthdays;
    }

    public function getWorkDurationAttribute()
    {
        /** @var Carbon $joiningDate */
        $joiningDate = $this->joining_date;

        /** @var Carbon $exitDate */
        $exitDate = $this->exit_date;

        if ($exitDate == null) {
            $exitDate = Carbon::now();
        }

        if ($joiningDate == null) {
            return '-';
        }

        $diff = $exitDate->diff($joiningDate);

        $string = ($d = $diff->d) ? ' ' . $d . ' d' : '';
        $string = ($m = $diff->m) ? ($string ? ' ' : ' ') . $m . ' m' . $string : $string;
        $string = ($y = $diff->y) ? $y . ' y' . $string : $string;

        $string = ($diff->d == 0 && $diff->m == 0 && $diff->y == 0) ? __('core.joinedToday') : $string;

        return $string;
    }

    /**
     * Get the last absent days
     * If the user is not absent since joining then.Joining date is last absent date
     */
    public function lastAbsent($type = 'days')
    {
        $absent = Attendance::where('status', '=', 'absent')->where('employee_id', '=', $this->id)
            ->where(function ($query) {
                $query->where('application_status', '=', 'approved')
                    ->orWhere('application_status', '=', null);
            })->orderBy('date', 'desc')->first();

        $lastDate = date('Y-m-d');
        $old_date = isset($absent->date) ? $absent->date : $this->joining_date;
        $diff = date_diff(date_create($old_date), date_create($lastDate));

        if ($diff->d == 0 || !isset($absent->date)) {
            return '<span class="label label-danger">' . trans("core.never") . '</span>';
        }

        $difference = $diff->format('%a') . ' days ago';
        if ($type == 'days') {
            return $difference;
        } elseif ($type == 'date') {
            return date_create($old_date)->format('d-M-Y');
        }
    }

   /* public function leaveLeft()
    {

        $total_leave = Leavetype::get()
                ->sum('num_of_leave') + $this->annual_leave;

        $leaveLeft = array_sum(Attendance::absentEmployee(employee()->id)) . '/' . $total_leave;

        return $leaveLeft;
    }*/

    public function getOutstandingLeaveDaysAttribute($outstanding_leave_days)
    {
        if ($outstanding_leave_days == "") {
            return 0;
        } else {
            return $outstanding_leave_days;
        }
    }


    public function getLeaveLeftAttribute()
    {

        $total_Position_leave = $this->position->leave_days ?? 0;
        $leave_applications = $this->leaveapplications->sum('days');
        $outstanding_lastYear = $total_Position_leave - $this->outstanding_leave_lastYear->sum('days');
        @$outstanding_leave = (($total_Position_leave - $leave_applications) + $this->outstanding_leave_days);

        return @$outstanding_leave ;
    }


    public function getPositionLeaveDaysAttribute()
    {
        return  $this->position->leave_days ?? 0;
    }


    public function getLeaveDaysLeftAttribute()
    {
        return  (($this->position->leave_days-$this->leaveapplications->sum('days'))+($this->outstanding_leave_days));
    }



    public function worked_days($month, $year)
    {

        $total_Days = GeneralHelper::employeeMonthly_work_days($this->id,$month, $year);

        return $total_Days ;
    }


    public function getLastYearLeaveOutstandingAttribute()
    {

        $total_Position_leave = $this->position->leave_days;
        $outstanding_lastYear = $total_Position_leave - $this->outstanding_leave_lastYear->sum('days');

        return $outstanding_lastYear ;
    }




    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = date('Y-m-d', strtotime($value));
    }

    public function setJoiningDateAttribute($value)
    {
        $this->attributes['joining_date'] = date('Y-m-d', strtotime($value));
    }


    public $preventAttrSet = false;

    public function toPortableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }

    public function getEncrypted()
    {
        return $this->encrypted;
    }

    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if (in_array($key, $this->encrypted) &&
            !is_null($value)) {
            $gdpr = Setting::first()->gdpr;

            if (!$this->preventAttrSet && $gdpr === 1) {
                $value = decrypt($value);
            }
        }

        return $value;
    }



    /**
     * Return Model in array type, with all datas decrypted.
     * @return array
     */
    public function decryptToArray()
    {
        $model = [];
        foreach ($this->attributes as $attributeKey => $attributeValue) {
            $model[$attributeKey] = $this->$attributeKey;
        }

        return $model;
    }

    /**
     * Return Model in collection type, with all datas decrypted.
     * @return array
     */
    public function decryptToCollection()
    {
        $model = collect();
        foreach ($this->attributes as $attributeKey => $attributeValue) {
            $model->$attributeKey = $this->$attributeKey;
        }

        return $model;
    }

    protected $encrypted = [
        'full_name',
        'mobile_number',
        'father_name',
        'local_address',
        'permanent_address'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisors()
    {
        return $this->hasMany(EmployeeSupervisor::class);
    }

    public function children()
    {
        return $this->hasMany(EmployeeChildren::class);
    }


    public function jospongEmployments()
    {
        return $this->hasMany(EmployeeJospongEmployment::class);
    }



    public function jospongRelativeEmployments()
    {
        return $this->hasMany(EmployeeJospongRelativeEmployment::class);
    }

    public function supervisors2()
    {
        return $this->belongsToMany(Employee::class, 'employee_supervisors', 'employee_id', 'employee_supervisor_id')->where('status', 1);
    }

    public function subordinates2()
    {
        return $this->belongsToMany(Employee::class, 'employee_supervisors', 'employee_supervisor_id', 'employee_id')->where('status', 1);
    }

    public function section()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function behavior()
    {
        return $this->belongsToMany(Behavior::class)->withTimestamps();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }


    public function school()
    {
        return $this->belongsTo(Company::class, 'company_id');
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
        return $this->belongsTo(CompanyYear::class, 'company_year_id');
    }

    public function graduationYear()
    {
        return $this->belongsTo(GraduationYear::class, 'graduation_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }


    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_employee_id')->where('status', 1);
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id', 'id');
    }


    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id','id');
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
        return $this->hasMany(Registration::class,  'employee_id', 'id');
    }

    public function semesterGPA($semester_id)
    {
        $regs = $this->hasMany(Registration::class,  'employee_id', 'id')
                    ->where('semester_id', '=', $semester_id);
        $gpa = SUM($regs->credit);

        return $gpa;
    }

    public function CurrentRegistration()
    {

        return $this->hasMany(Registration::class)->where('company_year_id', '=', session('current_company_year'))->where('semester_id', '=', session('current_company_semester'));
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

    public function KpiPerformanceReview()
    {
        return $this->hasMany(KpiPerformanceReview::class);
    }

    public function kpiSelfRating($kpi_id, $timeline_id)
    {
            return KpiPerformanceReview::where('kpi_id',$kpi_id)->where('employee_id', $this->id)->where('kpi_timeline_id', $timeline_id)->first()->self_rating;
    }

    public function kpiYearSelfRating($kpi_id)
    {
            return round(KpiPerformanceReview::where('kpi_id',$kpi_id)->where('employee_id', $this->id)->average('self_rating'),2);
    }


    public function kpiSelfScore($kpi_id)
    {
        $score = EmployeeKpiScore::where('kpi_id', $kpi_id)->where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->get()->sum('self_score');

        return number_format($score, 2);
    }


    public function kpiManagerRating($kpi_id, $timeline_id)
    {
            return KpiPerformanceReview::where('kpi_id',$kpi_id)->where('employee_id', $this->id)->where('kpi_timeline_id', $timeline_id)->first()->agreed_rating;
    }

    public function kpiYearManagerRating($kpi_id)
    {
            return @round(@KpiPerformanceReview::where('kpi_id',$kpi_id)->where('employee_id', $this->id)->average('agreed_rating'),2);
    }



    public function kpiAgreedScore($kpi_id)
    {
        $score = EmployeeKpiScore::where('kpi_id', $kpi_id)->where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->get()->sum('score');

        return number_format($score, 2);
    }

    public function kpiWeight($kpi_id)
    {
            return KpiResponsibility::where('kpi_id', $kpi_id)->where('responsible_employee_id', $this->id)->first()->weight ?? '';
    }

    public function perspectiveWeight($perspective_id, $year_id)
    {
            return PerspectiveWeight::whereEmployeeId($this->id)->whereCompanyYearId($year_id)->whereBscPerspectiveId($perspective_id)->first()->weight ?? '';
    }



    public function totalYearWeight($year_id)
    {
            return KpiResponsibility::where('responsible_employee_id', $this->id)->whereHas('kpi.kpiObjective.kra', function ($q) use ($year_id) {
                $q->where('kpis.company_year_id', $year_id)->whereNull('kpi_responsibilities.deleted_at');
            })->sum('weight');
    }

    public function perspectiveWeightUsed($perspective_id, $year_id)
    {
            return KpiResponsibility::where('responsible_employee_id', $this->id)->whereHas('kpi.kpiObjective.kra', function ($q) use($perspective_id, $year_id) {
                $q->where('kras.bsc_perspective_id', $perspective_id)
                    ->where('kpis.company_year_id', $year_id)->whereNull('kpi_responsibilities.deleted_at');
            })->sum('weight');
    }


    public function totalYearKpiSelfReview($year_id)
    {
        $data =  EmployeeKpiScore::where('employee_id', $this->id)->where('company_year_id', $year_id)->get()->sum('self_score');

        return number_format($data, 2);
    }


    public function totalYearKpiManagerReview($year_id)
    {
        $data =  EmployeeKpiScore::where('employee_id', $this->id)->where('company_year_id', $year_id)->get()->sum('score');

        return number_format($data, 2);
    }


    public function getBscScoreAttribute()
    {
        return @GeneralHelper::employee_total_score($this->id, session('current_company_year'));
    }



    public function getLastYearBscScoreAttribute()
    {
        $company = Company::find(session('current_company'));
        $year = CompanyYear::where('id', '<', session('current_company_year'))->where('group_id', '=', $company->sector->group_id)->orderBy('id', 'DESC')->first('id');
        return @GeneralHelper::employee_total_score($this->id, $year->id);
    }




    public function totalYearPerspectiveKpiSelfReview($perspective_id, $year_id)
    {

        $data =  EmployeeKpiScore::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->whereHas('kpi.kpiObjective.kra', function ($q) use ($perspective_id, $year_id) {
            $q->where('kras.company_year_id', $year_id)
                ->where('kras.bsc_perspective_id', $perspective_id);
        })->get()->sum('self_score');

        return number_format($data, 2);
    }


    public function totalYearPerspectiveKpiManagerReview($perspective_id, $year_id)
    {
        $data =  EmployeeKpiScore::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->whereHas('kpi.kpiObjective.kra', function ($q) use ($perspective_id, $year_id) {
            $q->where('kras.company_year_id', $year_id)
                ->where('kras.bsc_perspective_id', $perspective_id);
        })->get()->sum('score');

        return number_format($data, 2);
    }









    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }


    public function yearKpis()
    {
        return $this->hasMany(KpiResponsibility::class, 'responsible_employee_id')->whereHas('kpi', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        });

    }

    public function lastYearKpis()
    {
        $company = Company::find(session('current_company'));
        $year = CompanyYear::where('id', '<', session('current_company_year'))->where('group_id', '=', $company->sector->group_id)->orderBy('id', 'DESC')->first('id');
        return $this->hasMany(KpiResponsibility::class, 'responsible_employee_id')->whereHas('kpi', function ($q) use($year) {
            $q->where('kpis.company_year_id', $year->id);
        });
    }

    public function timelineKpis($timeline)
    {
        return $this->hasMany(KpiResponsibility::class, 'responsible_employee_id')->whereHas('employee_kpi_timelines.kpi', function ($q) use ($timeline) {
            $q->where('employee_kpi_timelines.kpi_timeline_id', $timeline)->where('kpis.company_year_id', session('current_company_year'));
        });

    }

    public function yearKpis3($year_id)
    {
        return $this->hasMany(KpiResponsibility::class, 'responsible_employee_id')->whereHas('kpi')->whereHas('kpi.kpiObjective.kra', function ($q) use ($year_id) {
            $q->where('kpis.company_year_id', $year_id);
        });

    }

/*
    public function yearKpis2()
    {
        return KpiResponsibility::whereHas('kpi')->where('responsible_employee_id', $this->id)->orWhere('owner_employee_id', $this->id)->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->with('kpi', 'responsibilities')->get()->unique('kpi_id');
    }
    */

    public function yearKpis2()
    {
        return KpiResponsibility::where('responsible_employee_id', $this->id)->whereHas('kpi.kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->with('kpi', 'responsibilities');
    }



    public function ownYearKpis()
    {
        return $this->hasMany(Kpi::class)->whereHas('kpiResponsibilities', function ($q) {
            $q->where('kpi_responsibilities.owner_employee_id', $this->id);
        })->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        });
    }


    public function cascadedYearKpis()
    {
        return $this->hasMany(Kpi::class)->whereHas('kpiResponsibilities', function ($q) {
            $q->where('kpi_responsibilities.owner_employee_id','!=', $this->id)
                ->Where('kpi_responsibilities.responsible_employee_id', $this->id);
        })->whereHas('kpiObjective.kra', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        });
    }


    public function kpiSignOffs()
    {
        return $this->hasMany(EmployeeKpiSignOff::class)->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'));
    }


    public function kpiSignOff()
    {
        return $this->hasMany(EmployeeKpiSignOff::class)->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'));
    }



    public function kpiActivities()
    {
        return $this->hasMany(EmployeeKpiActivity::class)
            ->whereHas('kpi', function ($q) {
                $q->where('kpis.company_year_id', '=', session('current_company_year'))
                  ->where('kpis.approved', '=', 1);
            });
    }


    public function lastYearkpiActivities()
    {
        $company = Company::find(session('current_company'));
        $year = CompanyYear::where('id', '<', session('current_company_year'))->where('group_id', '=', $company->sector->group_id)->orderBy('id', 'DESC')->first('id');
        return $this->hasMany(EmployeeKpiActivity::class)
            ->whereHas('kpi', function ($q) use ($year) {
                $q->where('kpis.company_year_id', '=', $year->id)
                    ->where('kpis.approved', '=', 1);
            });
    }

    public function getActivityPercentageAttribute()
    {
        return GeneralHelper::getPercentage($this->completedKpiActivities()->count(), $this->kpiActivities()->count());
    }

    public function completedKpiActivities()
    {
        /*return $this->hasManyThrough(EmployeeKpiActivity::class, Kpi::class)
            ->where('kpis.company_id', '=', session('current_company'))
            ->where('kpis.company_year_id', '=', session('current_company_year'))
            ->where('kpis.approved', '=', 1)
            ->where('employee_kpi_activities.kpi_activity_status_id', '=', 3);*/

        return $this->hasMany(EmployeeKpiActivity::class)->where('kpi_activity_status_id', '=', 3)
            ->whereHas('kpi', function ($q) {
                $q->where('kpis.company_year_id', '=', session('current_company_year'))
                    ->where('kpis.approved', '=', 1);
            });
    }




    public function feesStatus()
    {

        return $this->hasMany(Invoice::class, 'user_id', 'user_id')
            ->where('company_year_id', '=', session('current_company_year'))->where('semester_id', '=', session('current_company_semester'));
    }

    public function invoice()
    {

        return $this->hasMany(Invoice::class, 'user_id', 'user_id');
    }




    public function InvoiceHistory($company_year_id, $school_semester_id)
    {

        return $this->hasMany(Invoice::class, 'user_id', 'user_id')
            ->where('company_year_id', '=', $company_year_id)
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
        return $this->belongsToMany(Subject::class, 'registrations', 'employee_id', 'subject_id')
            ->withPivot('user_id', 'level_id', 'company_year_id', 'semester_id', 'grade', 'mid_sem', 'credit', 'exams', 'exam_score')
            ->wherePivot('level_id', '=', $level)
            ->wherePivot('semester_id', '=', $semester);
    }


 public function getTotalScoreAttribute()
    {
        $score = EmployeeKpiScore::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->get()->sum('score');

        return $score;
    }


 public function getTotalRatingAttribute()
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q)  {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpi_performance_reviews.employee_id', $this->id);
        })->get();
            $data = @$review->average('agreed_rating');
        return round($data);
    }


 public function getTotalSelfRatingAttribute()
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q)  {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpi_performance_reviews.employee_id', $this->id);
        })->get();
            $data = @$review->average('self_rating');
        return round($data);
    }



 public function getTotalSelfScoreAttribute()
    {
        $score = EmployeeKpiScore::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->get()->sum('self_score');

        return number_format($score, 2);
    }



    public function getExpectedCompetenciesNumberAttribute()

    { if (isset($this->position->competencyMatrix))
    {
        return Competency::whereHas('competency_framework', function ($q)  {
            $q->where('competency_frameworks.department_id', $this->department_id)
                ->where('competency_frameworks.position_id', $this->position_id);
        })->count();
    }
    else
        return 0;
    }

    public function getExpectedCompetenciesAttribute()
    { if (isset($this->position->competencyMatrix))
    {
        return "{$this->position->competencyMatrix()->whereDepartmentId($this->department_id)->count()}";
    }
    else
        return 0;
    }


    public function getGapCompetenciesAttribute()
    { if (isset($this->position->competencyMatrix))
    {
        return "{$this->position->competencyMatrix()->whereDepartmentId($this->department_id)}";
    }
    else
        return 0;
    }


    public function getExpectedQualificationsAttribute()
    { if (isset($this->position->qualificationMatrix))
    {
        return "{$this->position->qualificationMatrix()->whereDepartmentId($this->department_id)->count()}";
    }
    else
        return 0;
    }



    public function getCompleteAttribute()
    {
        if (
            $this->user->mobile == "" OR
            $this->user->email == "" OR
            $this->department_id == "" OR
            $this->position_id == ""

           )
        {
            return false;
        }
            return true;

    }





    public function getBscOpenAttribute()
    {

        if (@EmployeeKpiSignOff::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->first()->status != 1)
        {
            return true;
        }

    }

    public function getBscTimelineOpenAttribute()
    {
        if (@EmployeeKpiTimelineSignOff::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->where('kpi_timeline_id', 4)->first()->status != 1)
        {
            return true;
        }
    }


    public function getBscSignedAttribute()
    {

        if (@EmployeeKpiSignOff::where('employee_id', $this->id)->where('company_year_id', session('current_company_year'))->first()->status == 1)
        {
            return true;
        }
    }



    public function self_bsc_review($timelineID)
    {
        if
        (
            @EmployeeKpiTimelineSignOff::where('employee_id', $this->id)
                ->where('kpi_timeline_id', $timelineID)
                ->where('company_year_id', session('current_company_year'))
                ->where('self', 1)->first()
        )
        {
            return true;
        }
        else return false;

    }




    public function self_timeline_status()
    {
        return $this->hasMany(EmployeeKpiTimelineSignOff::class)
            ->where('company_year_id', session('current_company_year'))->where('self', 1);
    }

    public function supervisor_timeline_status()
    {
        return $this->hasMany(EmployeeKpiTimelineSignOff::class)
            ->where('company_year_id', session('current_company_year'))->where('supervisor', 1);
    }



}
