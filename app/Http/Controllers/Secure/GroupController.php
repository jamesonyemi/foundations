<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\Group;
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

class GroupController extends SecureController
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

        view()->share('type', 'group');
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

        return view('group.index', compact('title', 'schools'));
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
        $permissions = Module::where('parent_id', 0)->orderBy('name', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->orderBy('name', 'Asc')->get();
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
        $employees = $school->employees();
        $kpiActivities = $school->kpi_activities;
        $completedActivities = $school->completed_kpi_activities;
        $companyScore = $school->kpi_score;
        $maleEmployees = $school->male_employees;
        $femaleEmployees = $school->female_employees;
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
        $permissions = Module::where('parent_id', 0)->orderBy('name', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->orderBy('name', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        return view('group._form', compact('title', 'school', 'sectors', 'data'));
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

    public function employees(Group $group)
    {
        $title = $group->title.' Employees';
        $employees = $this->employeeRepository
            ->getAllForSchool($school->id)
            ->with('user')
            ->get();

        return view('schools.employees', compact('title', 'employees', 'school'));
    }

    public function kpis(Group $group)
    {
        $title = $group->title.' Kpis';

        return view('group.kpis', compact('title'));
    }
}
