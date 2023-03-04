<?php

namespace App\Http\Controllers\Secure;

use App\Events\LoginEvent;
use App\Exports\EmployeesAttendanceExport;
use App\Exports\StudentsExport;
use App\Helpers\CompanySettings;
use App\Helpers\CustomFormUserFields;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\Gl_entry;
use App\Models\Group;
use App\Models\KpiObjective;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Sector;
use App\Models\Student;
use App\Models\User;
use function App\Helpers\randomString;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\bscWizardRequest;
use App\Http\Requests\Secure\employeeDataWizardRequest;
use App\Http\Requests\Secure\EmployeeRequest;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\SmsMessageRequest;
use App\Http\Requests\Secure\StudentNoteRequest;
use App\Http\Resources\CompetencyEmployeeResource;
use App\Http\Resources\KpiPerformanceReviewResource;
use App\Http\Resources\PerformanceEmployeeResource;
use App\Imports\EmployeesImport;
use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\BscPerspective;
use App\Models\Center;
use App\Models\Company;
use App\Models\CompanyYear;
use App\Models\Competency;
use App\Models\CompetencyGrade;
use App\Models\CompetencyMatrix;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeKpiActivity;
use App\Models\Kpi;
use App\Repositories\ActivityLogRepository;
use App\Repositories\CampusRepository;
use App\Repositories\CountryRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\EntryModeRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\GraduationYearRepository;
use App\Repositories\IntakePeriodRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use App\Repositories\LevelRepository;
use App\Repositories\MaritalStatusRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ReligionRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\SessionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nexmo\Laravel\Facade\Nexmo;
use PDF;
use function PHPUnit\Framework\isEmpty;
use Sentinel;
use Str\Str;

class DashboardController extends SecureController
{
    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var KpiRepository
     */
    private $kpiRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var DirectionRepository
     */
    private $directionRepository;

    /**
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var CampusRepository
     */
    private $campusRepository;

    /**
     * @var CampusRepository
     */
    private $countryRepository;

    /**
     * @var CampusRepository
     */
    private $maritalStatusRepository;

    /**
     * @var CampusRepository
     */
    protected $activity;

    protected $module = 'Employee';

    /**
     * EmployeeController constructor.
     * @param KpiRepository $kpiRepository
     * @param EmployeeRepository $employeeRepository
     * @param OptionRepository $optionRepository
     * @param SectionRepository $sectionRepository
     * @param LevelRepository $levelRepository
     * @param EntryModeRepository $EntryModeRepository
     * @param CampusRepository $CampusRepository
     * @param CountryRepository $CountryRepository
     * @param KraRepository $kraRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        SchoolRepository $schoolRepository,
        OptionRepository $optionRepository,
        LevelRepository $levelRepository,
        KpiRepository $kpiRepository,
        CampusRepository $campusRepository,
        CountryRepository $countryRepository,
        MaritalStatusRepository $maritalStatusRepository,
        ReligionRepository $religionRepository,
        DirectionRepository $directionRepository,
        SchoolYearRepository $schoolYearRepository,
        GraduationYearRepository $graduationYearRepository,
        SemesterRepository $semesterRepository,
        SessionRepository $sessionRepository,
        SectionRepository $sectionRepository,
        ActivityLogRepository $activity,
        KraRepository $kraRepository
    ) {
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->schoolRepository = $schoolRepository;
        $this->optionRepository = $optionRepository;
        $this->sectionRepository = $sectionRepository;
        $this->levelRepository = $levelRepository;
        $this->kpiRepository = $kpiRepository;
        $this->campusRepository = $campusRepository;
        $this->countryRepository = $countryRepository;
        $this->maritalStatusRepository = $maritalStatusRepository;
        $this->religionRepository = $religionRepository;
        $this->directionRepository = $directionRepository;
        $this->schoolYearRepository = $schoolYearRepository;
        $this->graduationYearRepository = $graduationYearRepository;
        $this->semesterRepository = $semesterRepository;
        $this->sessionRepository = $sessionRepository;
        $this->activity = $activity;
        $this->kraRepository = $kraRepository;

        $this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit', 'ajaxMakeGlobal']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'employee');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function employeeDashboard()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();


        $maleEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('employees.status', '=', 1)
            ->where('users.gender', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();

        $femaleEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('employees.status', '=', 1)
            ->where('users.gender', '=', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();



        $departments = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('employees.status', '=', 1)
            ->whereIn('employees.employee_posting_group_id', [5,6])
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();

        $sectors = Sector::where('group_id', $this->school->sector->group_id)
            ->where('active', 'yes')
            ->withCount(['employees', 'companies'])
            ->get();

        return view('dashboard.employeeDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'departments', 'sectors'));
    }


    public function getUsers()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::where('company_id', '=', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();

        $maleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();

        $femaleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();

        $departments = Department::whereCompanyId(session('current_company'))->get()->count();

        return response()->json(compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'departments'));
    }

    public function jlcDashboard()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::whereHas('active')
            ->whereNull('employees.deleted_at')
            ->get();

        $maleEmployees = Employee::whereHas('active')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $femaleEmployees = Employee::whereHas('active')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $departments = Center::all()->count();

        return view('dashboard.jlcDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'departments'));
    }

    public function bscDashboard()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->where('company_id', '=', session('current_company'))
            ->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->groupby('employees.id')
            ->get();

        $maleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $femaleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $departments = Department::whereCompanyId(session('current_company'))->get()->count();

        $kpis = Kpi::whereApproved(1)->whereHas('employee', function ($q) {
            $q->where('employees.status', '=', 1);
        })->where('company_id', '=', session('current_company'))
            ->where('company_year_id', '=', session('current_company_year'))
            ->get();

        $kpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employee_kpi_activities.employee_id', session('current_employee'))
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();

        $completedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $companyPercentage = GeneralHelper::getPercentage($completedActivities->count(), $kpiActivities->count());

        $companyScore = @GeneralHelper::company_total_score(session('current_company'));

        $departmentKpis = Kpi::whereHas('employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentKpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentCompletedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentPercentage = GeneralHelper::getPercentage($departmentCompletedActivities->count(), $departmentKpiActivities->count());

        $departmentScore = GeneralHelper::department_total_score($this->currentEmployee->department_id);

        return view('dashboard.bscDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'kpis', 'kpiActivities', 'completedActivities', 'departmentCompletedActivities', 'departmentKpiActivities', 'departmentKpis', 'departmentPercentage', 'departments', 'departmentScore', 'companyPercentage', 'companyScore'));
    }


    public function sectorKpiPlanningDashboard()
    {
        $title = 'Sector KPI Planning';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();

        $femaleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 0);
            })->whereNull('employees.deleted_at')
            ->get();



        $departments = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs')
            ->count();

        $sectorCompanies = Company::where('sector_id', session('current_company_sector'))
            ->where('active', 'yes')
            ->get();


        return view('dashboard.sectorKpiPlanningDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'departments', 'sectorCompanies'));
    }




    public function groupKpiPlanningDashboard()
    {
        $title = 'Group KPI Planning';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->count();

        $femaleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 0);
            })->whereNull('employees.deleted_at')
            ->count();



        $departments = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs')
            ->count();

        $sectors = Sector::where('group_id', $this->school->sector->group_id)
            ->where('active', 'yes')
            ->withCount(['employees', 'companies'])
            ->get();


        return view('dashboard.groupKpiPlanningDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'departments', 'sectors'));
    }

    public function groupKpiPerspectiveBalance()
    {
        $title = 'Group KPI Planning Perspectives Balance Bar';

        return view('dashboard.groupKpiPerspectiveBalance', compact('title'));
    }


    public function groupEmployeeAging()
    {
        $title = 'Group Employee Aging Analysis';

       /* $ranges = [ // the start of each age-range.
            '18-24' => 18,
            '25-35' => 25,
            '36-45' => 36,
            '46-55' => 46,
            '56+' => 56
        ];

        $output = User::get()
            ->map(function ($user) use ($ranges) {
                $age = Carbon::parse($user->birth_date)->age;
                foreach($ranges as $key => $breakpoint)
                {
                    if ($breakpoint >= $age)
                    {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) {
                return count($group);
            })
            ->sortKeys();*/



        return view('dashboard.groupAgeAnalysis', compact('title'));
    }

    public function groupKpiPerspectiveBalancePie()
    {
        $title = 'Group KPI Planning Perspectives Balance Pie';

        return view('dashboard.groupKpiPerspectiveBalancePie', compact('title'));
    }



    public function groupTopKras()
    {
        $title = 'Group 20 Top KRAs';

        $kras = Kra::whereHas('kpis')->withCount('kpis')
        ->orderBy('kpis_count', 'desc')
        ->take(20)
        ->get();

        return view('dashboard.groupTopKras', compact('title', 'kras'));
    }


    public function groupTopObjectives()
    {
        $title = 'Group 20 Top Objectives';

        $objectives = KpiObjective::whereHas('kpis')->withCount('kpis')
            ->orderBy('kpis_count', 'desc')
            ->take(20)
            ->get();

        return view('dashboard.groupTopObjectives', compact('title', 'objectives'));
    }




    public function sectorBscReviewDashboard($timelineID=1)
    {
        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->first('id');



        $timeline= KpiTimeline::find($timelineID);
        $title = $timeline->title . 'Sector BSC Review';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();

        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();



        $bscSelfReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.self_rating');
            })
            ->get();

        $departments = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs')
            ->count();

        $sectorCompanies = Company::where('sector_id', session('current_company_sector'))
            ->where('active', 'yes')
            ->get();


        return view('dashboard.sectorBscreviewDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'departments', 'sectorCompanies', 'reviewQuarterId', 'kpitimelines'));
    }




    public function groupBscReviewDashboard()
    {
        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->first('id');



        $title = 'Group BSC Review';


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();



        return view('dashboard.groupBscreviewDashboard', compact('title',  'reviewQuarterId', 'kpitimelines'));
    }




    public function companyBscReviewDashboard($timelineID=1)
    {
        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->first('id');

        $title = 'Company BSC Review';


        $kpitimelines = KpiTimeline::get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => $item->title,
                ];
            })->pluck("name", 'id')
            ->prepend('Select Timeline', '')
            ->toArray();


        $sections = Department::whereHas('employees', function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();



        return view('dashboard.companyBscreviewDashboard', compact('title',  'reviewQuarterId', 'kpitimelines', 'sections'));
    }




    public function companyBscReviewDashboardFilter(Request $request)
    {
        $timeline= KpiTimeline::find($request->kpi_timeline_id);
        $title = $timeline->title . 'Company BSC Review';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();


        $bscSelfReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.self_rating');
            })
            ->get();


        $bscSupervisorReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.agreed_rating');
            })
            ->get();



        if($request->department_id > 0) {
            $section_list = Department::where('id', $request->department_id)->with('employees')->get();
        }

        else {
            $section_list = Department::whereHas('employees', function ($query) {
                $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
            })->with('employees.user', 'employees.position' )->get();
        }



        return view('dashboard.loadCompanyBscReview', compact('title', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'bscSupervisorReview', 'request', 'timeline', 'section_list'));
    }


    public function companyBscReviewDashboardFilterTable(Request $request)
    {
        $timeline= KpiTimeline::find($request->kpi_timeline_id);
        $title = $timeline->title . 'Company BSC Review';



        if($request->department_id > 0) {
            $employees = $this->employeeRepository->getAllForSchool(session('current_company'))->where('department_id', $request->department_id)->where('status', 1)
                ->with('user', 'section')
                ->get();
        }

        else {
            $employees = $this->employeeRepository->getAllForSchool(session('current_company'))->where('status', 1)
                ->with('user', 'section')
                ->get();
        }

        return view('dashboard.loadCompanyBscReviewTable', compact('title', 'request', 'employees', 'timeline'));
    }


    public function companyBscReviewDownload(Request $request)
    {
        $timeline= KpiTimeline::find($request->kpi_timeline_id);
        $title = $this->school->title. ' '. @$this->bscYear->title. ' '. $timeline->title . ' BSC Report';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();


        $bscSelfReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.self_rating');
            })
            ->get();


        $bscSupervisorReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('employees.company_id', session('current_company'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.agreed_rating');
            })
            ->get();




        if($request->department_id > 0) {
            $section_list = Department::where('id', $request->department_id)->with('employees')->get();
        }

        else {
            $section_list = Department::whereHas('employees', function ($query) {
                $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
            })->with('employees.user', 'employees.position' )->get();
        }


        /*$pdf = PDF::loadView('dashboard.loadCompanyBscReviewPdf', compact('title', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'bscSupervisorReview', 'request', 'employees', 'timeline'));*/

        /*return $pdf->stream($title.'.pdf', ['Attachment'=>0]);*/

        return view('dashboard.loadCompanyBscReviewPdf', compact('title', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'bscSupervisorReview', 'request', 'section_list', 'timeline'));
    }


    public function activeReviewQuarter()
    {
        $dt = Carbon::now();
        $reviewQuarterId = KpiTimeline::select("kpi_timelines.*")
            ->whereRaw('? between review_start_date and review_end_date', [$dt])
            ->first('id');
        return $reviewQuarterId;
    }


    public function sectorBscReviewDashboardFilter(Request $request)
    {
        $timeline= KpiTimeline::find($request->kpi_timeline_id);
        $title = $timeline->title . 'Sector BSC Review';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();


        $bscSelfReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.self_rating');
            })
            ->get();


        $bscSupervisorReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  session('current_company_sector'))
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.agreed_rating');
            })
            ->get();

        $sectorCompanies = Company::where('sector_id', session('current_company_sector'))
            ->where('active', 'yes')
            ->withCount(['activeEmployees', 'kpis'])
            ->get();


        return view('dashboard.loadSectorBscReview', compact('title', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'bscSupervisorReview', 'sectorCompanies', 'request'));
    }



    public function groupBscReviewDashboardFilter(Request $request)
    {
        $timeline= KpiTimeline::find($request->kpi_timeline_id);
        $title = $timeline->title . 'Group BSC Review';

        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->count();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->count();


        $sectors = Sector::where('group_id', $this->school->sector->group_id)
            ->where('active', 'yes')
            ->withCount(['employees', 'companies'])
            ->get();




        $bscSelfReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.self_rating');
            })
            ->get();


        $bscSupervisorReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $this->school->sector->group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereHas('KpiPerformanceReview.kpi',  function ($q) use($timeline)  {
                $q->where('kpi_performance_reviews.kpi_timeline_id', $timeline->id)
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->whereNotNull('kpi_performance_reviews.agreed_rating');
            })
            ->get();


        return view('dashboard.loadGroupBscReview', compact('title', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'bscSupervisorReview', 'sectors', 'request'));
    }



    public function leaveDashboard()
    {
        $title = 'Leave Dashboard';
        $sector = Sector::find(session('current_company_sector'));

        $total_outstanding_leave_days = $sector->total_outstanding_leave_days;
        $total_leave_days = $sector->total_leave_days;
        $total_leave_applications_days = $sector->leave_applications;
        $total_leave_days_left = $total_leave_days+$total_outstanding_leave_days-$total_leave_applications_days;

        $sectorCompanies = Company::where('sector_id', session('current_company_sector'))
            ->where('active', 'yes')
            ->get();
        return view('dashboard.leaveDashboard', compact('title', 'sectorCompanies', 'total_leave_days', 'total_leave_applications_days', 'total_outstanding_leave_days', 'total_leave_days_left'));
    }

    public function groupFinanceDashboardEfficincy()
    {
       $title = 'Group Finance Dashboard';
        $subsidiaries = $this->schoolRepository
            ->getAllForGroup($this->school->sector->group_id)
            ->where('active', '=', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();


        return view('dashboard.groupFinanceDashboard', compact('title', 'subsidiaries'));
    }

    public function groupFinanceDashboardFilter(Request $request)
    {
        $date = Carbon::create($request->date);
        $group_id = $this->school->sector->group_id;


        $sectors = Sector::where('group_id', $group_id)
            ->where('active', 'yes')
            ->withCount(['companies'])
            ->get();

        if ($request->subsidiary_id > 0) {
            $revenue = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->where('companies.id', $request->subsidiary_id)
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [40000, 49999])
                ->sum('amount');
        } else {
            $revenue = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [40000, 49999])
                ->sum('amount');
        }


        if ($request->subsidiary_id > 0) {
        $expense = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [50210, 81520])
            ->sum('amount');
        } else {
            $expense = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [50210, 81520])
                ->sum('amount');
        }

        $grossProfit = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $group_id)
            ->where('companies.active', '=', 'Yes')
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [10010, 20010])
            ->sum('amount');

        $netProfit = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $group_id)
            ->where('companies.active', '=', 'Yes')
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [10010, 20010])
            ->sum('amount');


        if ($request->subsidiary_id > 0) {
        $receivables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [10010, 20010])
            ->sum('amount');
        } else {
            $receivables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [10010, 20010])
                ->sum('amount');
        }


        if ($request->subsidiary_id > 0) {
        $inventory = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [13007, 13065])
            ->whereBetween('gl_account_no', [13091, 13093])
            ->sum('amount');
        } else {
            $inventory = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [13007, 13065])
                ->whereBetween('gl_account_no', [13091, 13093])
                ->sum('amount');
        }

        if ($request->subsidiary_id > 0) {
        $payables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
            ->where('sectors.group_id', '=',  $group_id)
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)

            //VENDORS
            ->whereBetween('gl_account_no', [20110, 20140])
            ->whereBetween('gl_account_no', [20210, 20240])
            ->whereBetween('gl_account_no', [20310, 20320])

            //STATUTORY
            ->whereBetween('gl_account_no', [12210, 12250])
            ->whereBetween('gl_account_no', [20410, 20430])
            ->whereBetween('gl_account_no', [21010, 21095])
            ->whereBetween('gl_account_no', [21105, 21170])
            ->whereBetween('gl_account_no', [22110, 22150])
            ->sum('amount');
        } else {
            $payables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)

                //VENDORS
                ->whereBetween('gl_account_no', [20110, 20140])
                ->whereBetween('gl_account_no', [20210, 20240])
                ->whereBetween('gl_account_no', [20310, 20320])

                //STATUTORY
                ->whereBetween('gl_account_no', [12210, 12250])
                ->whereBetween('gl_account_no', [20410, 20430])
                ->whereBetween('gl_account_no', [21010, 21095])
                ->whereBetween('gl_account_no', [21105, 21170])
                ->whereBetween('gl_account_no', [22110, 22150])
                ->sum('amount');
        }

        return view('dashboard.groupFinanceDashboardFilter', compact( 'sectors', 'revenue', 'expense', 'grossProfit', 'netProfit', 'receivables', 'payables', 'inventory', 'request', 'date', 'group_id'));
    }


    public function groupFinanceDashboardMonthlyTrend()
    {
       $title = 'Group Finance Dashboard';
        $subsidiaries = $this->schoolRepository
            ->getAllForGroup($this->school->sector->group_id)
            ->where('active', '=', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();


        return view('dashboard.groupFinanceDashboardMonthlyTrend', compact('title', 'subsidiaries'));
    }

    public function groupFinanceDashboardMonthlyTrendFilter(Request $request)
    {
        $date = Carbon::create($request->date);
        $group_id = $this->school->sector->group_id;

        return view('dashboard.groupFinanceDashboardMonthlyTrendFilter', compact(  'request', 'date', 'group_id'));
    }


    public function sectorFinanceDashboardEfficincy()
    {
       $title = 'Sector Finance Dashboard';
        $subsidiaries = $this->schoolRepository
            ->getAllForSector($this->school->sector_id)
            ->where('active', '=', 'Yes')
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();


        return view('dashboard.sectorFinanceDashboard', compact('title', 'subsidiaries'));
    }

    public function sectorFinanceDashboardFilter(Request $request)
    {
        $date = Carbon::create($request->date);
        $sector_id = $this->school->sector_id;


        $companies = Company::where('sector_id', $sector_id)
            ->where('active', 'yes')
            ->get();

        if ($request->subsidiary_id > 0) {
            $revenue = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.active', '=', 'Yes')
                ->where('companies.id', $request->subsidiary_id)
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [40000, 49999])
                ->sum('amount');
        } else {
            $revenue = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [40000, 49999])
                ->sum('amount');
        }


        if ($request->subsidiary_id > 0) {
        $expense = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [50210, 81520])
            ->sum('amount');
        } else {
            $expense = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.sector_id', $this->school->sector_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [50210, 81520])
                ->sum('amount');
        }

        $grossProfit = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->where('companies.sector_id', $this->school->sector_id)
            ->where('companies.active', '=', 'Yes')
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [10010, 20010])
            ->sum('amount');

        $netProfit = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->where('companies.sector_id', $this->school->sector_id)
            ->where('companies.active', '=', 'Yes')
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [10010, 20010])
            ->sum('amount');


        if ($request->subsidiary_id > 0) {
        $receivables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [10010, 20010])
            ->sum('amount');
        } else {
            $receivables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.sector_id', $this->school->sector_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [10010, 20010])
                ->sum('amount');
        }


        if ($request->subsidiary_id > 0) {
        $inventory = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)
            ->whereBetween('gl_account_no', [13007, 13065])
            ->whereBetween('gl_account_no', [13091, 13093])
            ->sum('amount');
        } else {
            $inventory = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.sector_id', $this->school->sector_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [13007, 13065])
                ->whereBetween('gl_account_no', [13091, 13093])
                ->sum('amount');
        }

        if ($request->subsidiary_id > 0) {
        $payables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
            ->where('companies.active', '=', 'Yes')
            ->where('companies.id', $request->subsidiary_id)
            ->whereYear('posting_date', $request->year)
            ->whereMonth('posting_date', $request->month)
            ->where('reversed', 0)

            //VENDORS
            ->whereBetween('gl_account_no', [20110, 20140])
            ->whereBetween('gl_account_no', [20210, 20240])
            ->whereBetween('gl_account_no', [20310, 20320])

            //STATUTORY
            ->whereBetween('gl_account_no', [12210, 12250])
            ->whereBetween('gl_account_no', [20410, 20430])
            ->whereBetween('gl_account_no', [21010, 21095])
            ->whereBetween('gl_account_no', [21105, 21170])
            ->whereBetween('gl_account_no', [22110, 22150])
            ->sum('amount');
        } else {
            $payables = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.sector_id', $this->school->sector_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)

                //VENDORS
                ->whereBetween('gl_account_no', [20110, 20140])
                ->whereBetween('gl_account_no', [20210, 20240])
                ->whereBetween('gl_account_no', [20310, 20320])

                //STATUTORY
                ->whereBetween('gl_account_no', [12210, 12250])
                ->whereBetween('gl_account_no', [20410, 20430])
                ->whereBetween('gl_account_no', [21010, 21095])
                ->whereBetween('gl_account_no', [21105, 21170])
                ->whereBetween('gl_account_no', [22110, 22150])
                ->sum('amount');
        }

        return view('dashboard.sectorFinanceDashboardFilter', compact( 'companies', 'revenue', 'expense', 'grossProfit', 'netProfit', 'receivables', 'payables', 'inventory', 'request', 'date', 'sector_id'));
    }


    public function companyFinanceDashboardEfficincy()
    {
       $title = 'Company Finance Dashboard';
        $departments = Department::whereCompanyId(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();


        return view('dashboard.companyFinanceDashboard', compact('title', 'departments'));
    }

    public function companyFinanceDashboardFilter(Request $request)
    {
        $date = Carbon::create($request->date);

            $revenue = Gl_entry::where('company_id', session('current_company'))
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [40000, 49999])
                ->sum('amount');


            $expense = Gl_entry::where('company_id', session('current_company'))
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [50210, 81520])
                ->sum('amount');

        $grossProfit = 0;

        $netProfit = 0;


            $receivables = Gl_entry::where('company_id', session('current_company'))
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [10010, 20010])
                ->sum('amount');



            $inventory = Gl_entry::where('company_id', session('current_company'))
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [13007, 13065])
                ->whereBetween('gl_account_no', [13091, 13093])
                ->sum('amount');


            $payables = Gl_entry::where('company_id', session('current_company'))
                ->whereYear('posting_date', $request->year)
                ->whereMonth('posting_date', $request->month)
                ->where('reversed', 0)

                //VENDORS
                ->whereBetween('gl_account_no', [20110, 20140])
                ->whereBetween('gl_account_no', [20210, 20240])
                ->whereBetween('gl_account_no', [20310, 20320])

                //STATUTORY
                ->whereBetween('gl_account_no', [12210, 12250])
                ->whereBetween('gl_account_no', [20410, 20430])
                ->whereBetween('gl_account_no', [21010, 21095])
                ->whereBetween('gl_account_no', [21105, 21170])
                ->whereBetween('gl_account_no', [22110, 22150])
                ->sum('amount');

        return view('dashboard.companyFinanceDashboardFilter', compact(  'revenue', 'expense', 'grossProfit', 'netProfit', 'receivables', 'payables', 'inventory', 'request', 'date'));
    }



    public function payrollDashboard()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->where('company_id', '=', session('current_company'))
            ->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->groupby('employees.id')
            ->get();

        $maleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $femaleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $departments = Department::whereCompanyId(session('current_company'))->get()->count();

        $kpis = Kpi::whereApproved(1)->whereHas('employee', function ($q) {
            $q->where('employees.status', '=', 1);
        })->where('company_id', '=', session('current_company'))
            ->where('company_year_id', '=', session('current_company_year'))
            ->get();

        $kpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();

        $completedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $companyPercentage = GeneralHelper::getPercentage($completedActivities->count(), $kpiActivities->count());

        $companyScore = @GeneralHelper::company_total_score(session('current_company'));

        $departmentKpis = Kpi::whereHas('employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentKpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentCompletedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentPercentage = GeneralHelper::getPercentage($departmentCompletedActivities->count(), $departmentKpiActivities->count());

        $departmentScore = GeneralHelper::department_total_score($this->currentEmployee->department_id);

        return view('dashboard.payrollDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'kpis', 'kpiActivities', 'completedActivities', 'departmentCompletedActivities', 'departmentKpiActivities', 'departmentKpis', 'departmentPercentage', 'departments', 'departmentScore', 'companyPercentage', 'companyScore'));
    }




    public function procurementDashboard()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::whereHas('section')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('company_id', '=', session('current_company'))
            ->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->groupby('employees.id')
            ->get();

        $maleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $femaleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $departments = Department::whereCompanyId(session('current_company'))->get()->count();

        $kpis = Kpi::whereApproved(1)->whereHas('employee', function ($q) {
            $q->where('employees.status', '=', 1);
        })->where('company_id', '=', session('current_company'))
            ->where('company_year_id', '=', session('current_company_year'))
            ->get();

        $kpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();

        $completedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $companyPercentage = GeneralHelper::getPercentage($completedActivities->count(), $kpiActivities->count());

        $companyScore = @GeneralHelper::company_total_score(session('current_company'));

        $departmentKpis = Kpi::whereHas('employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentKpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentCompletedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentPercentage = GeneralHelper::getPercentage($departmentCompletedActivities->count(), $departmentKpiActivities->count());

        $departmentScore = GeneralHelper::department_total_score($this->currentEmployee->department_id);

        return view('dashboard.payrollDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'kpis', 'kpiActivities', 'completedActivities', 'departmentCompletedActivities', 'departmentKpiActivities', 'departmentKpis', 'departmentPercentage', 'departments', 'departmentScore', 'companyPercentage', 'companyScore'));
    }


    public function attendanceDashboard()
    {
        $title = 'All Group Employees';
        $activeEmployees = Employee::whereHas('section')
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->where('company_id', '=', session('current_company'))
            ->where('status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->groupby('employees.id')
            ->get();

        $maleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $femaleEmployees = Employee::where('employees.company_id', session('current_company'))
            ->join('users', 'users.id', '=', 'employees.user_id')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->where('status', '=', 1)
            ->where('users.gender', 0)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at');

        $departments = Department::whereCompanyId(session('current_company'))->get()->count();

        $kpis = Kpi::whereApproved(1)->whereHas('employee', function ($q) {
            $q->where('employees.status', '=', 1);
        })->where('company_id', '=', session('current_company'))
            ->where('company_year_id', '=', session('current_company_year'))
            ->get();

        $kpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();

        $completedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $companyPercentage = GeneralHelper::getPercentage($completedActivities->count(), $kpiActivities->count());

        $companyScore = @GeneralHelper::company_total_score(session('current_company'));

        $departmentKpis = Kpi::whereHas('employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('employees.status', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentKpiActivities = EmployeeKpiActivity::whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentCompletedActivities = EmployeeKpiActivity::where('kpi_activity_status_id', 3)->whereHas('kpi.employee', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.approved', '=', 1)
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('employees.status', '=', 1)
                ->where('employees.department_id', $this->currentEmployee->department_id);
        })->get();

        $departmentPercentage = GeneralHelper::getPercentage($departmentCompletedActivities->count(), $departmentKpiActivities->count());

        $departmentScore = GeneralHelper::department_total_score($this->currentEmployee->department_id);

        return view('dashboard.attendanceDashboard', compact('title', 'activeEmployees', 'maleEmployees', 'femaleEmployees', 'kpis', 'kpiActivities', 'completedActivities', 'departmentCompletedActivities', 'departmentKpiActivities', 'departmentKpis', 'departmentPercentage', 'departments', 'departmentScore', 'companyPercentage', 'companyScore'));
    }
}
