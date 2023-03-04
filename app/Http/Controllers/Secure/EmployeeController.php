<?php

namespace App\Http\Controllers\Secure;

use App\Events\LoginEvent;
use App\Exports\EmployeesAttendanceExport;
use App\Exports\StudentsExport;
use App\Helpers\CompanySettings;
use App\Helpers\CustomFormUserFields;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\BlockLogin;
use App\Models\Kpi;
use App\Models\KpiTimeline;
use function App\Helpers\randomString;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\bscWizardRequest;
use App\Http\Requests\Secure\employeeDataWizardRequest;
use App\Http\Requests\Secure\EmployeePayrollComponentRequest;
use App\Http\Requests\Secure\EmployeeRequest;
use App\Http\Requests\Secure\ImportRequest;
use App\Http\Requests\Secure\LevelRequest;
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
use App\Models\Competency;
use App\Models\CompetencyGrade;
use App\Models\CompetencyMatrix;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCompetency;
use App\Models\EmployeeCompetencyMatrix;
use App\Models\EmployeeKpiTimeline;
use App\Models\EmployeePayrollComponent;
use App\Models\EmployeePostingGroup;
use App\Models\EmployeeQualification;
use App\Models\EmployeeSupervisor;
use App\Models\Level;
use App\Models\MarkValue;
use App\Models\MobileMoneyNetwork;
use App\Models\PayrollComponent;
use App\Models\PerformanceGrade;
use App\Models\PerformanceScoreGrade;
use App\Models\Permission;
use App\Models\Position;
use App\Models\Qualification;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\SmsMessage;
use App\Models\User;
use App\Models\UserDocument;
use App\Notifications\SendSMS;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use function PHPUnit\Framework\isEmpty;
use Sentinel;

class EmployeeController extends SecureController
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
    private $semesterRepository;

    private $religionRepository;

    private $schoolYearRepository;

    private $graduationYearRepository;

    private $sessionRepository;

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
    public function index()
    {
        if (! Sentinel::hasAccess('employees_list')) {
            Flash::warning('Permission Denied');

            return view('flash-message');
        }
        $title = trans('employee.active_employees');
        $employees = $this->employeeRepository->getAllCompanyActive(session('current_company'))
            ->with(['user', 'section', 'position'])
            ->get();

        return view('employee.index', compact('title', 'employees'));
    }


    public function inActiveEmployees()
    {
        if (! Sentinel::hasAccess('employees_list')) {
            Flash::warning('Permission Denied');

            return view('flash-message');
        }
        $title = trans('employee.inActive_employees');
        $employees = $this->employeeRepository->getAllCompanyInActive(session('current_company'))
            ->with(['user', 'section', 'position'])
            ->get();

        return view('employee.index', compact('title', 'employees'));
    }

    public function employeeDirectory()
    {
        $title = trans('employee.all_students');

        return view('employee.directory', compact('title'));
    }

    public function searchEmployee(Request $request)
    {
        try {
            $employees = Employee::whereHas('user', function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->id.'%')->orWhere('middle_name', 'like', '%'.$request->id.'%')->orWhere('last_name', 'like', '%'.$request->id.'%');
            })->get()->take(100);

            if (! isset($employees)) {
                return response()->json(['exception'=>'No Employee Record found with the given name']);
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return view('employee.search_list', compact('employees', ));
    }

    public function allGroupEmployeesIndex()
    {
        $title = 'All Group Employees';
        $subsidiaries = $this->schoolRepository
            ->getAllForGroup($this->school->sector->group_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();

        return view('all_group_employees.index', compact('title', 'subsidiaries'));
    }

    public function allGroupEmployees(Request $request)
    {
        $title = Company::find($request->subsidiary_id)->title.' Employees';
        $employees = $this->employeeRepository->getAllForSchool($request->subsidiary_id)/*->where('status', 1)*/
            ->with('user', 'section', 'position')
            ->get();

        return view('all_group_employees.employees', compact('title', 'employees', 'request'));
    }


    public function employeeTransfer(Request $request)
    {
        $title = 'Employee Transfer';
        $employee = Employee::find($request->employee_id);
        $subsidiaries = $this->schoolRepository
            ->getAllForGroup($this->school->sector->group_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school'), 0)
            ->toArray();
        return view('all_group_employees.employee_transfer', compact('title', 'subsidiaries', 'employee'));

    }


    public function employeeTransferStore(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
        $employee = Employee::find($request->employee_id);
        $employee->company_id = $request->subsidiary_id;
        $employee->save();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employee Transfer Successful</div>');
    }



    public function pendingApproval()
    {
        /*if (!Sentinel::hasAccess('student.approveinfo')) {
            Flash::error("Permission Denied");
            return redirect()->back();
        }*/
        $title = trans('employee.pending_students');

        $this->filterParams();

        $employees = $this->employeeRepository->getAllPendingApproval(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'programme')
            ->get();

        $count = 1;

        return view('employee.pendingApproval', compact('title', 'employees', 'count'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('employee.new');
        $this->generateParams();
        $data = [];
        $permissions = Permission::where('parent_id', 0)->orderBy('name', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->orderBy('name', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        @$roles = @Role::get();
        $custom_fields = CustomFormUserFields::getCustomUserFields('employee');

        return view('layouts.create', compact(
            'title',
            'custom_fields',
            'data', 'roles'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EmployeeRequest $request
     * @return Response
     */
    public function store(EmployeeRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $user = @$this->employeeRepository->create($request->except('document', 'document_id', 'image_file', 'permission[]'));
                $employee = Employee::where('user_id', $user->id)->first();

                if ($request->employee_supervisor_id) {
                    foreach ($request['employee_supervisor_id']  as $index => $supervisor_id) {
                        $employeeSupervisor = new EmployeeSupervisor();
                        $employeeSupervisor->employee_id = $employee->id;
                        $employeeSupervisor->employee_supervisor_id = $supervisor_id;
                        $employeeSupervisor->save();
                    }
                }
                if ($request->hasFile('image_file') != '') {
                    $file = $request->file('image_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/avatar/';
                    $file->move($destinationPath, $picture);
                    Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                    $user->picture = $picture;
                    $user->save();
                }

                if (isset($request['permission'])) {
                    foreach ($request['permission'] as $permission) {
                        $user->addPermission($permission);
                        $user->save();
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employee Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $employee
     * @return Response
     */
    public function show(Employee $employee)
    {
        if (!Sentinel::hasAccess('employees_view')) {
            Flash::warning("Permission Denied");
            return Response ('<div class="alert alert-danger">Permission Denied</div>') ;
        }
        $title = $employee->user->full_name;
        $action = 'show';
        $schoolType = session('current_company_type');
        $custom_fields = CustomFormUserFields::getCustomUserFieldValues('employee', $employee->user_id);
        $count = 1;



        $competencies = Competency::whereHas('competency_framework', function ($q) use ($employee) {
            $q->where('competency_frameworks.department_id', $employee->department_id)
                ->where('competency_frameworks.position_id', $employee->position_id);
        })->get();

        $qualifications = Qualification::whereHas('qualification_framework', function ($q) use ($employee) {
            $q->where('qualification_frameworks.department_id', $employee->department_id)
                ->where('qualification_frameworks.position_id', $employee->position_id);
        })->get();

        $employeeCompetencies = $employee->competencies()
            ->get()
            ->pluck('competency_matrix_id', 'competency_matrix_id')
            ->toArray();

        $employeeQualifications = $employee->qualifications()
            ->get()
            ->pluck('qualification_id', 'qualification_id')
            ->toArray();

        /* $response = Http::get('https://www.thecocktaildb.com/api/json/v1/1/search.php?s=margarita')->json();

         $data = collect($response['drinks']);
         */

        return view('layouts.show', compact('employee', 'title', 'action', 'custom_fields', 'schoolType', 'count', 'competencies', 'employeeCompetencies', 'qualifications', 'employeeQualifications'));
    }



    public function kpis(Employee $employee)
    {
        $title = $employee->user->full_name.' Kpis';
        $kpis = $employee->yearKpis()->get();

        return view('employee.kpis', compact('title', 'kpis'));
    }


    public function timeLinekpis(Employee $employee, KpiTimeline $timeline)
    {
        $title = $employee->user->full_name.' '. $timeline->title . ' Kpis';
        $kpis = $employee->timelineKpis($timeline->id)->get();

        return view('employee.kpis', compact('title', 'kpis'));
    }


    public function showBarCode(Employee $employee)
    {
        return view('employee.barCode', compact('employee'));
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $employee
     * @return Response
     */
    public function showPayrollComponents(Employee $employee)
    {
        $title = $employee->user->full_name.' Payroll Components';
        $action = 'show';

        $payrollComponents = $employee->payrollComponents()
            ->get();

        return view('employee.payrollComponents', compact('employee', 'title', 'action', 'payrollComponents'));
    }

    public function addPayrollComponent(Employee $employee)
    {
        $title = $employee->user->full_name.' Payroll Components';
        $payrollComponents = PayrollComponent::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Transaction', '')
            ->toArray();

        return view('employee.payrollComponents_form', compact('employee', 'title', 'payrollComponents'));
    }

    public function storePayrollComponent(EmployeePayrollComponentRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                EmployeePayrollComponent::firstOrCreate(
            [
                'employee_id' => $request->employee_id,
                'payroll_component_id' => $request->payroll_component_id,
                'amount' => $request->amount,
                'balance_type' => $request->balance_type,
                'transaction_type' => $request->transaction_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'original_amount' => $request->original_amount,
            ]
        );
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">COMPONENT ADDED SUCCESSFULLY</div>');

    }

    public function editPayrollComponent(EmployeePayrollComponent $employeePayrollComponent)
    {
        $title = 'Edit Payroll Components';
        $action = 'show';

        $employee = Employee::find($employeePayrollComponent->employee_id);

        $payrollComponents = PayrollComponent::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        return view('employee.payrollComponents_form', compact('title', 'action', 'payrollComponents', 'employeePayrollComponent', 'employee'));
    }

    public function updatePayrollComponent(EmployeePayrollComponentRequest $request, EmployeePayrollComponent $employeePayrollComponent)
    {
        try {
            DB::transaction(function () use ($employeePayrollComponent, $request) {
                $employeePayrollComponent->update($request->all());
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">COMPONENT UPDATED SUCCESSFULLY</div>');
    }

    public function deletePayrollComponent(EmployeePayrollComponent $employeePayrollComponent)
    {
        try {
            DB::transaction(function () use ($employeePayrollComponent) {
                $employeePayrollComponent->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">COMPONENT DELETED SUCCESSFULLY</div>');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Employee $employee
     * @return Response
     */
    public function edit(Employee $employee)
    {
        if (! Sentinel::hasAccess('employees_update')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        $title = 'Edit '.$employee->user->full_name.'';
        $this->generateParams();
        $employee_supervisors = EmployeeSupervisor::where('employee_id', $employee->id)
            ->get()
            ->pluck('employee_supervisor_id', 'employee_supervisor_id')
            ->toArray();
        $data = [];
        $permissions = Permission::where('parent_id', 0)->orderBy('name', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->orderBy('name', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }
        @$roles = @Role::get();
        $documents = UserDocument::where('user_id', $employee->user->id)->first();
        $custom_fields = CustomFormUserFields::fetchCustomValues('student', $employee->user_id);

        return view('layouts.edit', compact('title', 'employee', 'documents', 'custom_fields', 'data', 'roles', 'employee_supervisors'));
    }

    public function allGroupEdit(Employee $employee)
    {
        if (! Sentinel::hasAccess('employees_update')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        $title = 'Edit '.$employee->user->full_name.'';
        $this->generateParamsAllEmployees($employee->company_id);
        $employee_supervisors = EmployeeSupervisor::where('employee_id', $employee->id)
            ->get()
            ->pluck('employee_supervisor_id', 'employee_supervisor_id')
            ->toArray();
        $data = [];
        $permissions = Permission::where('parent_id', 0)->orderBy('name', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->orderBy('name', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }
        @$roles = @Role::get();
        $documents = UserDocument::where('user_id', $employee->user->id)->first();
        $custom_fields = CustomFormUserFields::fetchCustomValues('student', $employee->user_id);

        return view('layouts.edit', compact('title', 'employee', 'documents', 'custom_fields', 'data', 'roles', 'employee_supervisors'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param StudentRequest $request
     * @param Employee $employee
     * @return Response
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        if (! Sentinel::hasAccess('employees_update')) {
            Flash::error('Permission Denied');

            return response()->json(['exception'=>'Permission Denied']);
        }
        try {
            $employee->update($request->only('department_id',
            'order',
            'employee_posting_group_id',
            'department_id',
            'position_id',
            'level_id',
            'social_security_number',
            'bank_account_number',
            'company_id',
            'tin_number',
            'passport_number',
            'driver_license',
            'driver_license_number',
            'driver_license_place_issue',
            'ghana_card_number',
            'bank_id',
            'bank_branch_id',
            'basic_pay',
            'payment_mode',
            'mobile_money_network_id',
            'mobile_money_number',
            'currency_id',
            'pays_pf',
            'probation_end_date',
            'pays_paye',
            'pays_ssf',
            'social_security_scheme',
            'entry_mode_id',
            'country_id',
            'marital_status_id',
            'no_of_children',
            'religion_id',
            'center_id',
            'denomination',
            'disability',
            'contact_relation',
            'contact_name',
            'contact_address',
            'contact_phone',
            'contact_email',
            'outstanding_leave_days',
            'session_id'));
            $employee->save();
            EmployeeSupervisor::where('employee_id', $employee->id)->delete();
            if ($request->password != '') {
                $employee->user->password = bcrypt($request->password);
            }

            foreach ($request['employee_supervisor_id']  as $index => $supervisor_id) {
                EmployeeSupervisor::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'employee_supervisor_id' => $supervisor_id,
                    ]
                );
            }

            if ($request->hasFile('image_file') != '') {
                $file = $request->file('image_file');
                $extension = $file->getClientOriginalExtension();
                $picture = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/avatar/';
                $file->move($destinationPath, $picture);
                Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                $employee->user->picture = $picture;
                $employee->user->save();
            }

            $employee->user->update($request->except('department_id', 'order', 'password', 'document', 'document_id', 'image_file', 'permission[]'));

            if ($request->hasFile('document') != '') {
                $file = $request->file('document');
                $user = $employee->user;
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);

                UserDocument::where('user_id', $user->id)->delete();

                $userDocument = new UserDocument;
                $userDocument->user_id = $user->id;
                $userDocument->document = $document;
                $userDocument->option_id = $request->document_id;
                $userDocument->save();
            }
            CustomFormUserFields::updateCustomUserField('employee', $employee->user->id, $request);
            $user = $employee->user;
            foreach ($user->getPermissions() as $key => $item) {
                $user->removePermission($key);
            }
            if (isset($request['permission'])) {
                foreach ($request['permission'] as $permission) {
                    $user->addPermission($permission);
                    $user->save();
                }
            }

            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $employee->id,
                'activity'  => 'updated',
            ]);
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employee Updated Successfully</div>');
    }

    /**
     * @param Employee $employee
     * @return Response
     */
    public function delete(Employee $employee)
    {
        if (! Sentinel::hasAccess('employees_delete')) {
            Flash::error('Permission Denied');

            return response()->json(['exception'=>'Permission Denied']);
        }

        try {
            $employee->delete();
            $employee->user()->delete();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">EMPLOYEE DELETED SUCCESSFULLY</div>');
    }

    public function ajaxCompetency(Employee $employee)
    {
        return view('employee.performance_stats', compact('employee'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Employee $employee
     * @return Response
     */
    public function destroy(Employee $employee)
    {
        if (! Sentinel::hasAccess('employees_delete')) {
            Flash::error('Permission Denied');

            return response()->json(['exception'=>'Permission Denied']);
        }

        try {
            DB::transaction(function () use ($employee) {
                $employee->delete();
                $employee->user()->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">EMPLOYEE DELETED SUCCESSFULLY</div>');
    }

    public function findDirectionName(Request $request)
    {
        $directions = $this->directionRepository
            ->getAllForSection($request->department_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();

        return $directions;
    }

    public function findBankBranches(Request $request)
    {
        $bankBranches = BankBranch::where('bank_id', $request->bank_id)
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_program'), 0)
            ->toArray();

        return $bankBranches;
    }

    public function findSectionStudents(Request $request)
    {
        $employees = $this->employeeRepository->getAllForSection2($request->department_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'sid'   => $item->sID,
                    'name' => isset($item->user) ? $item->user->full_name.' '.$item->sID : '',
                ];
            })->pluck('name', 'id')
               ->prepend('Select Employee', 0)
               ->toArray();

        return $employees;
    }

    public function findSectionStudents2(Request $request)
    {
        $employees = $this->employeeRepository->getAllForSection2($request->department_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'sid'   => $item->sID,
                    'name' => isset($item->user) ? $item->user->full_name.' '.$item->sID : '',
                ];
            })->pluck('name', 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        return $employees;
    }

    public function data()
    {
        return new CompetencyEmployeeResource($this->employeeRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section')
            ->get());
    }

    public function competencyMatrix()
    {
        return new CompetencyEmployeeResource($this->employeeRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section')
            ->get());
    }

    public function performanceMatrix()
    {
        return new PerformanceEmployeeResource($this->employeeRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
            ->with('user', 'section', 'position')
            ->get());
    }

    public function competencyMatrixIndex()
    {
        $title = 'Competency / Qualification Matrix';
        $grades = MarkValue::get();

        return view('employee_competency_matrix.index', compact('title', 'grades'));
    }

    public function performanceMatrixIndex()
    {
        $title = 'Performance Matrix';
        $grades = PerformanceScoreGrade::orderBy('id', 'asc')->get();

        return view('employee_performance_matrix.index', compact('title', 'grades'));
    }

    public function competencyGradeEmployeesIndex($grade)
    {
        $actualGrade = MarkValue::find($grade)->grade;
        $title = 'Competency Grade '.$actualGrade.' Employees';
        $employees = Employee::where('company_id', session('current_company'))
            ->get()
            ->filter(function ($item) use ($actualGrade) {
                return $item->CompetencyScoreGrade === $actualGrade;
            });

//        `return $employees->count();`
        return view('employee_competency_matrix.grade_employees', compact('title', 'employees', 'actualGrade'));
    }

    public function performanceGradeEmployeesIndex($grade)
    {
        $actualGrade = PerformanceScoreGrade::find($grade)->grade;
        $title = 'Performance Grade '.$actualGrade.' Employees';
        $employees = Employee::where('company_id', session('current_company'))->where('status', 1)
            ->get()
            ->filter(function ($item) use ($actualGrade) {
                return $item->PerformanceScoreGrade === $actualGrade;
            });

//        `return $employees->count();`
        return view('employee_performance_matrix.grade_employees', compact('title', 'employees', 'actualGrade'));
    }

    public function getImport()
    {
        $title = trans('student.import_student');

        return view('employee.import', compact('title'));
    }

    public function postImport(ImportRequest $request)
    {
        $title = trans('employee.import_student');

        Excel::import(new EmployeesImport(), $request->file('file'));

        Flash::success('Employees Imported Successfully');

        return redirect('/student');
    }

    public function finishImport(Request $request)
    {
        foreach ($request->import as $item) {
            $import_data = [
                'first_name' => $request->get('first_name')[$item],
                'last_name' => $request->get('last_name')[$item],
                'email' => $request->get('email')[$item],
                'password' => $request->get('password')[$item],
                'mobile' => $request->get('mobile')[$item],
                'department_id' => $request->get('department_id')[$item],
                'level_id' => $request->get('level_id')[$item],
                'gender' => $request->get('gender')[$item],
            ];
            $this->employeeRepository->create($import_data);
        }

        return redirect('/student');
    }

    public function downloadExcelTemplate()
    {
        return response()->download(base_path('resources/excel-templates/students.xlsx'));
    }

    public function studentFilter(Request $request)
    {
        $employees = $this->employeeRepository->getAllActiveFilter($request)->get();

        return view('student.allFilteredList', ['students' => $employees], ['count' => '1']); //
    }

    private function filterParams()
    {
        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $graduationyears = $this->graduationYearRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $semesters = $this->semesterRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $directions = $this->directionRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $levels = $this->levelRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $entrymodes = $this->entryModeRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $intakeperiods = $this->intakePeriodRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $campus = $this->campusRepository
            ->getAllForSchool(session('current_company'))
            ->get();

        $countries = $this->countryRepository
            ->getAll()
            ->get();

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get();

        $religions = $this->religionRepository
            ->getAll()
            ->get();

        view()->share('schoolyears', $schoolyears);
        view()->share('graduationyears', $graduationyears);
        view()->share('semesters', $semesters);
        view()->share('directions', $directions);
        view()->share('sections', $sections);
        view()->share('sessions', $sessions);
        view()->share('levels', $levels);
        view()->share('entrymodes', $entrymodes);
        view()->share('campus', $campus);
        view()->share('intakeperiods', $intakeperiods);
        view()->share('countries', $countries);
        view()->share('countries2', $countries);
        view()->share('maritalStatus', $maritalStatus);
        view()->share('religions', $religions);
    }

    private function generateParams()
    {
        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_session'), 0)
            ->toArray();

        $levels = $this->levelRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend(trans('employee.select_position'), 0)
            ->toArray();

        $centers = Center::get()
            ->pluck('title', 'id')
            ->prepend('Select JLC Center', 0)
            ->toArray();

        $countries = $this->countryRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_country'), 0)
            ->toArray();

        $banks = Bank::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();

        $currencies = Currency::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();

        $bankBranches = BankBranch::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank Branch', 0)
            ->toArray();

        $mobileMoneynetworks = MobileMoneyNetwork::get()
            ->pluck('title', 'id')
            ->prepend('Select Mobile Network', 0)
            ->toArray();

        $postingGroups = EmployeePostingGroup::get()
            ->pluck('title', 'id')
            ->prepend('Select Posting Group', 0)
            ->toArray();

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_marital_status'), 0)
            ->toArray();

        $religion = $this->religionRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_religion'), 0)
            ->toArray();

        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school_year'), 0)
            ->toArray();

        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'student_document_type')->get()
            ->map(function ($option) {
                return [
                    'title' => $option->title,
                    'value' => $option->id,
                ];
            });

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Supervisor', 0)
            ->toArray();

        $schoolType = session('current_company_type');

        $school = Company::find(session('current_company'));
        $subsidiaries = Company::get()
            ->pluck('title', 'id')
            ->prepend('Select Subsidiary', '')
            ->toArray();

        view()->share('schoolType', $schoolType);
        view()->share('schoolyears', $schoolyears);
        view()->share('school', $school);
        view()->share('subsidiaries', $subsidiaries);
        view()->share('employees', $employees);
        view()->share('sections', $sections);
        view()->share('sessions', $sessions);
        view()->share('levels', $levels);
        view()->share('positions', $positions);
        view()->share('centers', $centers);
        view()->share('countries', $countries);
        view()->share('countries2', $countries);
        view()->share('postingGroups', $postingGroups);
        view()->share('banks', $banks);
        view()->share('currencies', $currencies);
        view()->share('bankBranches', $bankBranches);
        view()->share('mobileMoneynetworks', $mobileMoneynetworks);
        view()->share('maritalStatus', $maritalStatus);
        view()->share('religion', $religion);
        view()->share('document_types', $document_types);
    }

    private function generateParamsAllEmployees($company_id)
    {
        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        $sessions = $this->sessionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_session'), 0)
            ->toArray();

        $levels = $this->levelRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend(trans('employee.select_position'), 0)
            ->toArray();

        $centers = Center::get()
            ->pluck('title', 'id')
            ->prepend('Select JLC Center', 0)
            ->toArray();

        $countries = $this->countryRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_country'), 0)
            ->toArray();

        $banks = Bank::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();

        $currencies = Currency::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank', 0)
            ->toArray();

        $bankBranches = BankBranch::get()
            ->pluck('title', 'id')
            ->prepend('Select Bank Branch', 0)
            ->toArray();

        $mobileMoneynetworks = MobileMoneyNetwork::get()
            ->pluck('title', 'id')
            ->prepend('Select Mobile Network', 0)
            ->toArray();

        $postingGroups = EmployeePostingGroup::get()
            ->pluck('title', 'id')
            ->prepend('Select Posting Group', 0)
            ->toArray();

        $maritalStatus = $this->maritalStatusRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_marital_status'), 0)
            ->toArray();

        $religion = $this->religionRepository
            ->getAll()
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_religion'), 0)
            ->toArray();

        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_school_year'), 0)
            ->toArray();

        $document_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'student_document_type')->get()
            ->map(function ($option) {
                return [
                    'title' => $option->title,
                    'value' => $option->id,
                ];
            });

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal($company_id)
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Supervisor', 0)
            ->toArray();

        $schoolType = session('current_company_type');

        $school = Company::find(session('current_company'));
        $subsidiaries = Company::get()
            ->pluck('title', 'id')
            ->prepend('Select Subsidiary', '')
            ->toArray();

        view()->share('schoolType', $schoolType);
        view()->share('schoolyears', $schoolyears);
        view()->share('school', $school);
        view()->share('subsidiaries', $subsidiaries);
        view()->share('employees', $employees);
        view()->share('sections', $sections);
        view()->share('sessions', $sessions);
        view()->share('levels', $levels);
        view()->share('positions', $positions);
        view()->share('centers', $centers);
        view()->share('countries', $countries);
        view()->share('countries2', $countries);
        view()->share('postingGroups', $postingGroups);
        view()->share('banks', $banks);
        view()->share('currencies', $currencies);
        view()->share('bankBranches', $bankBranches);
        view()->share('mobileMoneynetworks', $mobileMoneynetworks);
        view()->share('maritalStatus', $maritalStatus);
        view()->share('religion', $religion);
        view()->share('document_types', $document_types);
    }

    public function ajaxNote(StudentNoteRequest $request)
    {
        $employeeNote = new StudentNote();
        $employeeNote->user_id = Sentinel::getUser()->id;
        $employeeNote->note = $request->note;
        $employeeNote->student_id = $request->student_id;
        $employeeNote->save();

        $title = trans('student.details');
        $action = 'show';
        $count = 1;
        $thisUser = Sentinel::getUser()->id;
        $employee = Employee::find($request->student_id);

        return view('student.notes', compact('title', 'action', 'count', 'employee', 'thisUser'));
    }

    public function ajaxSMS(SmsMessageRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $school = Company::find(session('current_company'))->first();
                if ($school->limit_sms_messages == 0 ||
            $school->limit_sms_messages > $school->sms_messages_year) {
                    $user = User::find($request->muser_id);
                    if (! is_null($user) && $user->mobile != '') {
                        $user->notify(new SendSMS($user, $request));

                        $smsMessage = new SmsMessage();
                        $smsMessage->text = $request->text;
                        $smsMessage->number = $user->mobile;
                        $smsMessage->user_id = $request->muser_id;
                        $smsMessage->user_id_sender = $this->user->id;
                        $smsMessage->company_id = session('current_company');
                        $smsMessage->save();
                    }
                }
            });
        } catch (\Exception $e) {
            return $e;
        }

        $applicant = Applicant::where('user_id', $request->muser_id)->first();
        $count = 1;

        return view('applicant.messages', compact('count', 'applicant'));
    }

    public function ajaxMakeActive(Request $request)
    {
        $employee = Employee::find($request->employee_id);
        $employee->status = 1;
        $employee->save();

        BlockLogin::where('user_id', $employee->user_id)->delete();

        return 'Employee Activated';
    }

    public function ajaxMakeInActive(Request $request)
    {
        $employee = Employee::find($request->employee_id);
        $employee->status = 0;
        $employee->save();

        $blockLogin = BlockLogin::firstOrCreate(
            ['user_id' =>  $employee->user_id]
        );

        return 'Employee Deactivated';
    }

    public function ajaxAcceptAdmission(Request $request)
    {
        $employeeAdmission = StudentAdmission::where('student_id', $request->student_id)->first();
        $employeeAdmission->status = 1;
        $employeeAdmission->save();

        return 'Operation Successful';
    }

    public function ajaxMakeGlobal(Request $request)
    {
        $employee = Employee::find($request->employee_id);
        $employee->global = 1;
        $employee->save();

        return 'Employee Made Global';
    }

    public function ajaxRemoveGlobal(Request $request)
    {
        $employee = Employee::find($request->employee_id);
        $employee->global = 0;
        $employee->save();

        return 'Employee Global Revoked';
    }

    public function toggleQualification(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $employeeQualification = EmployeeQualification::where('employee_id', $request->employee_id)->where('qualification_id', $request->qualification_id)->first();
                if (! empty($employeeQualification)) {
                    $employeeQualification->delete();
                } else {
                    $qualification = new EmployeeQualification();
                    $qualification->employee_id = $request->employee_id;
                    $qualification->qualification_id = $request->qualification_id;
                    $qualification->company_year_id = session('current_company_year');
                    $qualification->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        $qualification = Qualification::find($request->qualification_id)->title;

        return response('<div class="alert alert-success">'.$qualification.'. Operation Successful</div>');
    }

    public function toggleCompetency(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                @$employeeCompetency = EmployeeCompetencyMatrix::where('employee_id', $request->employee_id)->where('competency_matrix_id', $request->competency_matrix_id)->first();
                @$competency_grade_id = CompetencyMatrix::find($request->competency_matrix_id)->competency_grade_id;
                @$competency_id = CompetencyMatrix::find($request->competency_matrix_id)->competency_id;

                if (! empty($employeeCompetency)) {
                    $employeeCompetency->delete();
                } else {
                    $competency = new EmployeeCompetencyMatrix();
                    $competency->employee_id = $request->employee_id;
                    $competency->competency_id = $competency_id;
                    $competency->competency_matrix_id = $request->competency_matrix_id;
                    $competency->company_year_id = session('current_company_year');
                    $competency->weight = CompetencyGrade::find($competency_grade_id)->weight;
                    $competency->save();
                }

                //AUTIMATIC LOWER SELECTION IF HIGHER MATRIX
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        /* $competency = Qualification::find($request->qualification_id)->title;*/
        /*return response('<div class="alert alert-success"> Operation Successful</div>') ;*/
        $employee = Employee::find($request->employee_id);

        return view('employee.performance_stats', compact('employee'));
    }

    public function generalExport(Request $request)
    {
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['dailyAttendance2' => function ($query) use ($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        if ($request->department_id > 0) {
            $employees = $employees->where('department_id', $request->department_id);
        } elseif ($request->employee_id > 0) {
            $employees = $employees->where('id', $request->employee_id);
        }

        /*dd($employees->count());*/

        return Excel::download(new EmployeesAttendanceExport($employees), 'attendance.xlsx');
    }

    public function admittedExport(Request $request)
    {
        $employees = $this->employeeRepository->getAllActiveExport($request);

        return Excel::download(new StudentsExport($employees), 'students.xlsx');
    }

    public function makeTicketAgent(Request $request)
    {
        return 'Operation Successful';
    }

    public function testSms(Request $request)
    {
        $user = User::find(1);
        event(new LoginEvent($user));
        echo 'Message Sent';
    }

    public function testEmail(Request $request)
    {
        $user = User::find(5);
        GeneralHelper::send_email($user);
    }

    public function ageRange(Request $request)
    {
        $ranges = [ // the start of each age-range.
            '0-18'  => 0,
            '18-24' => 18,
            '25-35' => 25,
            '36-45' => 36,
            '46-50' => 46,
            '51+' => 51,
        ];

        $output = User::get()
            ->map(function ($user) use ($ranges) {
                $age = Carbon::parse($user->birth_date)->age;
                foreach ($ranges as $key => $breakpoint) {
                    if ($breakpoint >= $age) {
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
            ->sortKeys();

        dd($output);
    }

    public function erpSync(Request $request)
    {
        $company = Company::find(session('current_company'));
        if ($company->erp_employees_endpoint === '') {
            return response('<div class="alert alert-danger">ERP EndPoint Not Set, Contact System Administrator!!!</div>');
        }
        try {
            DB::transaction(function () use ($request, $company) {
                $response = Http::withToken('BdUOYHXBApVGYmmriKENHrH90EE3wBf2kIUq3X9qIyeQgT3RThv9jUrfowB7DL89rkMnykyNmO1ElE3w')->get('http://api.jospong.com/erp/public/api/'.$company->erp_employees_endpoint)->json();

                $dataCollection = collect($response);

                foreach ($response as $key) {
                    $data = [];

                    $data['first_name'] = $key['First Name'];
                    $data['middle_name'] = $key['Middle Name'];
                    $data['last_name'] = $key['Last Name'];
                    $data['email'] = $key['No_'];
                    $data['companyMail'] = $key['Company E-Mail'];
                    $data['email2'] = $key['E-Mail'];
                    $data['birth_date'] = date('Y-m-d', strtotime($key['Date Of Birth']));
                    $data['join_date'] = date('Y-m-d', strtotime($key['Date Of Join']));
                    $data['contract_end_date'] = date('Y-m-d', strtotime($key['Contract End Date']));
                    $data['address'] = $key['Residential Address'];
                    $data['address_line2'] = $key['Postal Address'];
                    $data['mobile'] = $key['Cellular Phone Number'];
                    $data['phone'] = $key['Work Phone Number'];
                    $data['gender'] = $key['Gender'];
                    $data['sID'] = $key['No_'];
                    $data['social_security_number'] = $key['Social Security No_'];
                    $data['basic_pay'] = $key['Basic Pay'];
                    $data['salary_grade'] = $key['Salary Grade'];
                    $data['bank_account_number'] = $key['Bank Account Number'];
                    $data['job_title'] = $key['Job Title'];
                    $data['password'] = '123456';
                    $data['marital_status_id'] = $key['Marital Status'];
                    $data['tin_number'] = $key['TIN_'];
                    $data['bank_id'] = Bank::where('code', $key['Main Bank'])->first()->id ?? 0;
                    $data['bank_branch_id'] = BankBranch::where('code', $key['Branch Bank'])->first()->id ?? 0;
                    $section = Department::where('code', $key['Dimension 1 Code'])->first();
                    $position = Position::where('code', $key['Job ID'])->first();
                    $data['department_id'] = isset($section) ? $section->id : 0;
                    $data['position_id'] = isset($position) ? $position->id : 0;
                    $data['status'] = 1;
                    $data['company_id'] = session('current_company');

                    @$this->employeeRepository->erpSync($data);
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employees Synced Successfully</div>');
    }

    public function employeeDataWizard()
    {
        $title = trans('kpi.kpis');

        $employee = $this->currentEmployee;

        $supervisors = $this->currentEmployee->supervisors2
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select', '')
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select', '')
            ->toArray();

        $this->generateParams();

        return view('employee.wizard', compact('title', 'employee', 'employees', 'supervisors'));
    }

    public function processEmployeeDataWizard(employeeDataWizardRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        /*dd($validated['Echildrens']);*/
        try {
            $employee->update($request->only('social_security_number', 'bank_account_number', 'tin_number', 'bank_id', 'bank_branch_id', 'bank_branch', 'country_id', 'marital_status_id', 'disability', 'passport_number', 'driver_license', 'driver_license_number', 'driver_license_place_issue'));
            $employee->save();

            $employee->user->update($request->only('first_name', 'middle_name', 'last_name', 'maiden_name', 'height', 'weight', 'address', 'address_line2', 'mobile', 'mobile2', 'gender', 'birth_date', 'birth_city', 'home_town', 'spouse_name', 'mother_name', 'father_name'));
            $employee->user->save();

            /*EmployeeSupervisor::where('employee_id', $employee->id)->delete();*/
            if ($request->password != '') {
                $employee->user->password = bcrypt($request->password);
            }

            foreach ($validated['Echildrens'] as $child) {
                $employee->children()->firstOrCreate([
                    'child_name' => $child['child_name'],
                    'child_date_birth' => $child['child_date_birth'],
                    'child_gender' => $child['child_gender'],
                ]);
            }

            foreach ($validated['jospong_employs'] as $employment) {
                $employee->jospongEmployments()->firstOrCreate([
                    'subsidiary_name' => $employment['jospong_employ_subsidiary'],
                    'start_date' => $employment['jospong_employ_start_date'],
                    'end_date' => $employment['jospong_employ_end_date'],
                    'position' => $employment['jospong_employ_position'],
                ]);
            }

            foreach ($validated['jospong_relative_employs'] as $relativeEmployment) {
                $employee->jospongRelativeEmployments()->firstOrCreate([
                    'relative_name' => $relativeEmployment['jospong_relative_employ_name'],
                    'relation' => $relativeEmployment['jospong_relative_employ_relation'],
                    'subsidiary_name' => $relativeEmployment['jospong_relative_employ_subsidiary'],
                    'start_date' => $relativeEmployment['jospong_relative_employ_start_date'],
                    'end_date' => $relativeEmployment['jospong_relative_employ_end_date'],
                    'position' => $relativeEmployment['jospong_relative_employ_position'],
                ]);
            }

            /*foreach ($request['employee_supervisor_id']  as $index => $supervisor_id)
            {
                EmployeeSupervisor::firstOrCreate
                (
                    [
                        'employee_id' => $employee->id,
                        'employee_supervisor_id' => $supervisor_id
                    ]
                );

            }*/

            if ($request->hasFile('image_file') != '') {
                $file = $request->file('image_file');
                $extension = $file->getClientOriginalExtension();
                $picture = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/avatar/';
                $file->move($destinationPath, $picture);
                Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                $employee->user->picture = $picture;
                $employee->user->save();
            }

            if ($request->hasFile('document') != '') {
                $file = $request->file('document');
                $user = $employee->user;
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);

                UserDocument::where('user_id', $user->id)->delete();

                $userDocument = new UserDocument;
                $userDocument->user_id = $user->id;
                $userDocument->document = $document;
                $userDocument->option_id = $request->document_id;
                $userDocument->save();
            }
            CustomFormUserFields::updateCustomUserField('employee', $employee->user->id, $request);
            $user = $employee->user;
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        {
            return response('<div class="alert alert-danger">Operation Successful!!!</div>');
        }
    }
}
