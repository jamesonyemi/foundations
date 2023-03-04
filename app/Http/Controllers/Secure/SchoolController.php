<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\Module;
use App\Models\Permission;
use App\Models\PerspectiveWeight;
use App\Models\Position;
use App\Models\Sector;
use App\Repositories\EmployeeRepository;
use App\Repositories\SchoolRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SchoolController extends SecureController
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
     * SchoolController constructor.
     * @param SchoolRepository $schoolRepository
     * @param EmployeeRepository $employeeRepository
     */
    public function __construct(
        SchoolRepository $schoolRepository,
        EmployeeRepository $employeeRepository
    ) {
        parent::__construct();

        $this->schoolRepository = $schoolRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'schools');

        $columns = ['title', 'address', 'phone', 'email', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('schools.school');
        if (Settings::get('multi_school') == 'yes') {
            if ($this->user->inRole('super_admin')) {
                $schools = $this->schoolRepository->getAll();
            } elseif ($this->user->inRole('admin') ||
                $this->user->inRole('human_resources') ||
                $this->user->inRole('librarian')) {
                $schools = $this->schoolRepository->getAllAdmin();
            } elseif ($this->user->inRole('teacher')) {
                $schools = $this->schoolRepository->getAllTeacher();
            } elseif ($this->user->inRole('applicant')) {
                $schools = $this->schoolRepository->getAllApplicant();
            } else {
                $schools = $this->schoolRepository->getAllStudent();
            }
        } else {
            if ($this->user->inRole('admin') || $this->user->inRole('super_admin')
                || $this->user->inRole('admin_super_admin')
                || $this->user->inRole('human_resources') || $this->user->inRole('librarian')) {
                $schools = $this->schoolRepository->getAll();
            } elseif ($this->user->inRole('teacher')) {
                $schools = $this->schoolRepository->getAllTeacher();
            } elseif ($this->user->inRole('applicant')) {
                $schools = $this->schoolRepository->getAllApplicant();
            } else {
                $schools = $this->schoolRepository->getAllStudent();
            }
        }
        $schools = $schools->get();

        return view('schools.index', compact('title', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('schools.new');
        $data = [];
        $permissions = Module::where('parent_id', 0)->orderBy('title', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Module::where('parent_id', $permission->id)->orderBy('title', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        $sectors = Sector::all()
            ->pluck('title', 'id')
            ->prepend('Select Sector', 0)
            ->toArray();

        return view('layouts.create', compact('title', 'data', 'sectors'));
    }

    public function sector_company()
    {
        $title = 'Sector Companies';
        $data = Company::where('sector_id', session('current_company_sector'))->get();

        return view('sector_company.index', compact('title', 'data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SchoolRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if (Settings::get('multi_school') == 'yes') {
                    $school = new Company($request->except('student_card_background_file', 'photo_file'));
                    if ($request->hasFile('student_card_background_file') != '') {
                        $file = $request->file('student_card_background_file');
                        $extension = $file->getClientOriginalExtension();
                        $picture = Str::random(8) .'.'.$extension;

                        $destinationPath = public_path().'/uploads/student_card/';
                        $file->move($destinationPath, $picture);
                        $school->student_card_background = $picture;
                    }
                    if ($request->hasFile('photo_file') != '') {
                        $file = $request->file('photo_file');
                        $extension = $file->getClientOriginalExtension();
                        $picture = Str::random(8) .'.'.$extension;

                        $destinationPath = public_path().'/uploads/school_photo/';
                        $file->move($destinationPath, $picture);
                        $school->photo = $picture;
                    }
                    $school->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Company Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Company $school)
    {
        $title = trans('schools.details');
        $action = 'show';
        $kpis = $school->kpis();
        $employees = $school->employees()->where('status', 1);
        $kpiActivities = $school->kpi_activities;
        $completedActivities = $school->completed_kpi_activities;
        $companyScore = $school->kpi_score;
        $maleEmployees = $school->male_employees->where('status', 1);
        $femaleEmployees = $school->female_employees->where('status', 1);
        $departments = $school->departments;

        return view('layouts.show', compact('school', 'title', 'action', 'kpis', 'employees', 'kpiActivities', 'completedActivities', 'companyScore', 'maleEmployees', 'femaleEmployees', 'departments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Company $school)
    {
        $title = trans('schools.edit');
        $sectors = Sector::all()
            ->pluck('title', 'id')
            ->prepend('Select Sector', 0)
            ->toArray();

        $data = [];
        $permissions = Module::where('parent_id', 0)->orderBy('title', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Module::where('parent_id', $permission->id)->orderBy('title', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        return view('schools._form', compact('title', 'school', 'sectors', 'data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, Company $school)
    {
        try {
            DB::transaction(function () use ($request, $school) {
                if ($request->hasFile('student_card_background_file') != '') {
                    $file = $request->file('student_card_background_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/student_card/';
                    $file->move($destinationPath, $picture);
                    $school->student_card_background = $picture;
                }
                if ($request->hasFile('photo_file') != '') {
                    $file = $request->file('photo_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/school_photo/';
                    $file->move($destinationPath, $picture);
                    $school->photo = $picture;
                }
                $school->update($request->except('student_card_background_file', 'photo_file'));
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Company Updated Successfully</div>');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Company $school)
    {
        $title = trans('schools.delete');

        return view('/schools/delete', compact('school', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Company $school
     * @return Response
     */
    public function destroy(Company $school)
    {
        $school->delete();

        return redirect('/schools');
    }

    public function activate(Company $school)
    {
        $school->active = ($school->active + 1) % 2;
        $school->save();

        return redirect('/schools');
    }

    public function data()
    {
        if (Settings::get('multi_school') == 'yes') {
            if ($this->user->inRole('super_admin')) {
                $schools = $this->schoolRepository->getAll();
            } elseif ($this->user->inRole('admin') ||
                $this->user->inRole('human_resources') ||
                $this->user->inRole('librarian')) {
                $schools = $this->schoolRepository->getAllAdmin();
            } elseif ($this->user->inRole('teacher')) {
                $schools = $this->schoolRepository->getAllTeacher();
            } elseif ($this->user->inRole('applicant')) {
                $schools = $this->schoolRepository->getAllApplicant();
            } else {
                $schools = $this->schoolRepository->getAllStudent();
            }
        } else {
            if ($this->user->inRole('admin') || $this->user->inRole('super_admin')
                || $this->user->inRole('admin_super_admin')
                || $this->user->inRole('human_resources') || $this->user->inRole('librarian')) {
                $schools = $this->schoolRepository->getAll();
            } elseif ($this->user->inRole('teacher')) {
                $schools = $this->schoolRepository->getAllTeacher();
            } elseif ($this->user->inRole('applicant')) {
                $schools = $this->schoolRepository->getAllApplicant();
            } else {
                $schools = $this->schoolRepository->getAllStudent();
            }
        }
        $schools = $schools->get()
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'title' => $school->title,
                    'address' => $school->address,
                    'phone' => $school->phone,
                    'email' => $school->email,
                    'active' => $school->active,
                ];
            });
        if (Settings::get('multi_school') == 'yes') {
            if ($this->user->inRole('super_admin')) {
                return Datatables::make($schools)
                    ->addColumn('actions', '<a href="{{ url(\'/schools/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                     <a href="{{ url(\'/schools/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                             @if($active==0)
                                     <a href="{{ url(\'/schools/\' . $id . \'/activate\' ) }}" class="btn btn-warning btn-sm" >
                                           <i class="fa fa-square-o" aria-hidden="true"></i> {{ trans("schools.activate") }}
                                           @else
                                     <a href="{{ url(\'/schools/\' . $id . \'/activate\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-check-square-o" aria-hidden="true"></i> {{trans("schools.deactivate") }}
                                           @endif
                                    </a>
                                    <a href="{{ url(\'/schools/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
                    ->removeColumn('id')
                    ->removeColumn('active')
                    ->rawColumns(['actions'])->make();
            } elseif ($this->user->inRole('admin')) {
                return Datatables::make($schools)
                    ->addColumn('actions', '<a href="{{ url(\'/schools/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                     <a href="{{ url(\'/schools/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                    ->removeColumn('id')
                    ->removeColumn('active')
                    ->rawColumns(['actions'])->make();
            } else {
                return Datatables::make($schools)
                    ->addColumn('actions', '<a href="{{ url(\'/schools/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                    ->removeColumn('id')
                    ->removeColumn('active')
                    ->rawColumns(['actions'])->make();
            }
        } else {
            if ($this->user->inRole('admin') || $this->user->inRole('super_admin')) {
                return Datatables::make($schools)
                    ->addColumn('actions', '<a href="{{ url(\'/schools/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                     <a href="{{ url(\'/schools/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                    ->removeColumn('id')
                    ->removeColumn('active')
                    ->rawColumns(['actions'])->make();
            } else {
                return Datatables::make($schools)
                    ->addColumn('actions', '<a href="{{ url(\'/schools/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                    ->removeColumn('id')
                    ->removeColumn('active')
                    ->rawColumns(['actions'])->make();
            }
        }
    }

    public function webService(Request $request)
    {
        $response = Http::withToken('BdUOYHXBApVGYmmriKENHrH90EE3wBf2kIUq3X9qIyeQgT3RThv9jUrfowB7DL89rkMnykyNmO1ElE3w')->get('http://api.jospong.com/erp/public/api/company')->json();

        $dataCollection = collect($response);
        foreach ($response as $data) {
            Company::updateOrCreate(
                ['title' => $data['Name']]
            );
        }

        return $dataCollection->count();
    }

    public function employees(Company $school)
    {
        $title = $school->title.' Employees';
        $employees = $this->employeeRepository
            ->getAllForSchool($school->id)->where('status', 1)
            ->with('user')
            ->get();

        return view('schools.employees', compact('title', 'employees', 'school'));
    }

    public function kpis(Company $school)
    {
        $title = $school->title.' Kpis';
        $kpis = Kpi::whereHas('employee', function ($q) use ($school) {
            $q->where('employees.company_id', $school->id)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();

        return view('schools.kpis', compact('title', 'kpis'));
    }


    public function KpiSignOff(Company $school)
    {
        $title = $school->title . ' BSC Signed Off Employees';
        $employees= Employee::where('employees.status', '=', 1)
            ->where('employees.company_id', $school->id)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();
        return view('schools.kpiSignOffemployees', compact('title', 'employees'));
    }


    public function pendingKpiSignOff(Company $school)
    {
        $title = $school->title . ' BSC Signed Off Employees';
        $employees= Employee::where('employees.status', '=', 1)
            ->where('employees.company_id', $school->id)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 0);
            })->whereNull('employees.deleted_at')
            ->get();
        return view('schools.kpiSignOffemployees', compact('title', 'employees'));
    }



    public function NoKpiSignOff(Company $school)
    {
        $title = $school->title . ' No BSC Employees';
        $employees= Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->where('employees.company_id', $school->id)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs')
            ->get();
        return view('schools.kpiSignOffemployees', compact('title', 'employees'));
    }




    public function erpSync(Request $request)
    {
        $company = Company::find($request->company_id);

        if (empty($company->erp_employees_endpoint)) {
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
                    $data['bank_id'] = $key['Main Bank'];
                    $data['bank_branch_id'] = $key['Branch Bank'];
                    $section = Department::where('code', $key['Dimension 1 Code'])->first();
                    $position = Position::where('code', $key['Job ID'])->first();
                    $data['section_id'] = isset($section) ? $section->id : 0;
                    $data['position_id'] = isset($position) ? $position->id : 0;
                    $data['status'] = 1;
                    $data['company_id'] = $company->id;
                    $data['company_year_id'] = 18;

                    @$this->employeeRepository->erpSync($data);
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Employees Synced Successfully</div>');
    }
}
