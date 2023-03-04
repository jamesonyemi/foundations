<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests;
use App\Http\Requests\Secure\SchoolRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Sector;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SectorController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;

        view()->share('type', 'sector');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Sectors';

        $sectors = Sector::withCount(['employees', 'companies', 'kpis'])->get();

        return view('sector.index', compact('title', 'sectors'));
    }

    public function groupSectors()
    {
        $title = 'Group Sectors';

        $sectors = Sector::where('group_id', $this->school->sector->group_id)->withCount(['employees', 'companies'])->get();

        return view('sector.index', compact('title', 'sectors'));
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
        /*$permissions = Module::where('parent_id', 0)->orderBy('name', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Permission::where('parent_id', $permission->id)->orderBy('name', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }*/

        /*$sectors = Sector::all()
            ->pluck('title', 'id')
            ->prepend('Select Sector', 0)
            ->toArray();*/

        return view('layouts.create', compact('title', ));
    }

    public function sector_company()
    {
        $title = 'Sector Companies';
        $data = Company::where('active', 'Yes')->where('sector_id', session('current_company_sector'))->withCount(['employees', 'kpis', 'kpi_activities', 'completed_kpi_activities'])->get();

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
    public function show(Sector $sector)
    {
        $companies = Company::where('sector_id', $sector->id)->withCount(['activeEmployees', 'kpis'])->get();
        $title = $sector->title;
        $action = 'show';
        /*$kpis = $sector->kpis();*/
        /*$employees = $sector->employees()->where('status', 1);*/
        /*$kpiActivities = $sector->kpi_activities;*/
        /*$completedActivities = $sector->completed_kpi_activities;*/
        /*$companyScore = $sector->kpi_score;*/
        /*$maleEmployees = $sector->male_employees->where('status', 1);*/
        /*$femaleEmployees = $sector->female_employees->where('status', 1);*/
        /*$companies = $sector->companies->where('active', 'Yes');*/

        return view('layouts.show', compact('sector', 'action', 'title', 'companies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Sector $sector)
    {
        $title = 'Update Sector '. $sector->title;

        $data = [];
        $permissions = Module::where('parent_id', 0)->orderBy('title', 'Asc')->get();
        foreach ($permissions as $permission) {
            array_push($data, $permission);
            $subs = Module::where('parent_id', $permission->id)->orderBy('title', 'Asc')->get();
            foreach ($subs as $sub) {
                array_push($data, $sub);
            }
        }

        return view('sector._form', compact('title', 'sector', 'data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, Sector $sector)
    {
        try {
            DB::transaction(function () use ($request, $sector) {
                if ($request->hasFile('student_card_background_file') != '') {
                    $file = $request->file('student_card_background_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/student_card/';
                    $file->move($destinationPath, $picture);
                    $sector->student_card_background = $picture;
                }
                if ($request->hasFile('photo_file') != '') {
                    $file = $request->file('photo_file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/school_photo/';
                    $file->move($destinationPath, $picture);
                    $sector->photo = $picture;
                }
                $sector->update($request->except('student_card_background_file', 'photo_file'));
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Sector Updated Successfully</div>');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Company $school)
    {
        $title = trans('schools.delete');

        return view('/sector/delete', compact('school', 'title'));
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

    public function employees(Sector $sector)
    {
        $title = $sector->title.' Employees';
        $employees = $sector->employees;

        return view('schools.employees', compact('title', 'employees'));
    }

    public function showSectorCompanies(Sector $sector)
    {
        $title = $sector->title.' Companies';
        $companies = Company::where('active', 'Yes')->where('sector_id', $sector->id)->withCount(['activeEmployees', 'kpis'])->get();

        return view('sector.companies', compact('title', 'companies'));
    }


    public function showSectorCompaniesBscReview(Sector $sector, KpiTimeline $kpiTimeline)
    {
        $title = $sector->title.' Companies ' .$kpiTimeline->title .' BSC Review';
        $companies = Company::where('active', 'Yes')->where('sector_id', $sector->id)->withCount(['activeEmployees', 'kpis'])->get();

        $timeline= $kpiTimeline;
        $activeEmployees = Employee::join('users', 'users.id', '=', 'employees.user_id')
            ->join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  $sector->id)
            ->where('employees.status', '=', 1)
            ->whereNull('employees.deleted_at')
            ->whereNull('users.deleted_at')
            ->get();


        $maleEmployees = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  $sector->id)
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();


        $bscSelfReview = Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.sector_id', '=',  $sector->id)
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

        return view('sector.companiesBscReview', compact('title', 'companies', 'kpiTimeline', 'activeEmployees', 'maleEmployees', 'bscSelfReview', 'bscSupervisorReview'));
    }

    public function kpis(Sector $sector)
    {
        $title = $sector->title.' Kpis';

        return view('sector.kpis', compact('title'));
    }



    public function KpiSignOff(Sector $sector)
    {
        $title = $sector->title . ' BSC Signed Off Employees';
        $employees= Employee::where('employees.status', '=', 1)
            ->where('employees.company_id', $sector->id)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 1);
            })->whereNull('employees.deleted_at')
            ->get();
        return view('schools.kpiSignOffemployees', compact('title', 'employees'));
    }


    public function pendingKpiSignOff(Sector $sector)
    {
        $title = $sector->title . ' BSC Signed Off Employees';
        $employees= Employee::where('employees.status', '=', 1)
            ->where('employees.company_id', $sector->id)
            ->whereHas('kpiSignOffs', function ($q) {
                $q->where('employee_kpi_sign_offs.company_year_id', session('current_company_year'))
                    ->where('employee_kpi_sign_offs.status', 0);
            })->whereNull('employees.deleted_at')
            ->get();
        return view('schools.kpiSignOffemployees', compact('title', 'employees'));
    }



    public function NoKpiSignOff(Sector $sector)
    {
        $title = $sector->title . ' No BSC Employees';
        $employees= Employee::join('companies', 'companies.id', '=', 'employees.company_id')
            ->where('companies.active', '=', 'Yes')
            ->where('employees.status', '=', 1)
            ->where('employees.company_id', $sector->id)
            ->whereNull('employees.deleted_at')
            ->doesntHave('kpiSignOffs')
            ->get();
        return view('schools.kpiSignOffemployees', compact('title', 'employees'));
    }



    public function sectorKpiPerspectiveBalance(Sector $sector)
    {
        $title = $sector->title . ' KPI Planning Perspective Balance';
        $kra_title = $sector->title . ' KRAs KPIs';
        $objective_title = $sector->title . ' Objectives KPIs';

        $kras = Kra::whereHas('kpis.employee.company', function ($q) use($sector) {
            $q->where('companies.sector_id', $sector->id);
        })->get()->sortByDesc(function($kra) use ($sector)
        {
            return $kra->sector_kpis($sector->id)->count();
        });




        $objectives = KpiObjective::whereHas('kpis.employee.company', function ($q) use($sector) {
            $q->where('companies.sector_id', $sector->id);
        })->get()->sortByDesc(function($objective) use ($sector)
        {
            return $objective->sector_kpis($sector->id)->count();
        });

        return view('sector.sectorKpiPerspectiveBalance', compact('title', 'sector', 'kras', 'objectives', 'kra_title', 'objective_title'));
    }



    public function sectorTopKras(Sector $sector)
    {
        $title = $sector->title . ' Top 20 KRAs';

        $kras = Kra::whereHas('kpis')->withCount('kpis')
            ->orderBy('kpis_count', 'desc')
            ->take(20)
            ->get();

        return view('dashboard.groupTopKras', compact('title', 'kras'));
    }


    public function sectorTopObjectives(Sector $sector)
    {
        $title = $sector->title . ' Top 20 Objectives';

        $objectives = KpiObjective::whereHas('kpis')->withCount('kpis')
            ->orderBy('kpis_count', 'desc')
            ->take(20)
            ->get();

        return view('dashboard.groupTopObjectives', compact('title', 'objectives'));
    }



}
