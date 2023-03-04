<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyFrameworkRequest;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\JobDescriptionRequest;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Resources\BscPerspectives;
use App\Http\Resources\CompetencyFrameworkResource;
use App\Http\Resources\KpiPerformanceResource;
use App\Models\BscPerspective;
use App\Models\Competency;
use App\Models\CompetencyFramework;
use App\Models\CompetencyLevel;
use App\Models\CompetencyMatrix;
use App\Models\CompetencyType;
use App\Models\Department;
use App\Models\JobDescription;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiPerformance;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Level;
use App\Models\Position;
use App\Models\Qualification;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;
use Validator;

class JobDescriptionController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var KpiRepository
     */
    private $kpiRepository;

    /**
     * @var KraRepository
     */
    private $kraRepository;

    /**
     * DirectionController constructor.
     *
     * @param KpiRepository $kpiRepository
     * @param EmployeeRepository $employeeRepository
     * @param KraRepository $kraRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        KpiRepository $kpiRepository,
        KraRepository $kraRepository
    ) {
        parent::__construct();

        $this->employeeRepository = $employeeRepository;
        $this->kpiRepository = $kpiRepository;
        $this->kraRepository = $kraRepository;

        view()->share('type', 'job_description');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Job Description';

        return view('job_description.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Job Description';

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Responsibility', 0)
            ->toArray();

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $qualifications = Qualification::get()
            ->pluck('title', 'id')
            ->toArray();

        $departments = Department::get()
            ->pluck('title', 'id')
            ->prepend('Select Department', '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'positions', 'qualifications', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|CompetencyFrameworkRequest $request
     * @return Response
     */
    public function store(JobDescriptionRequest $request)
    {
        $position_id = $request['position_id'];
        $department_id = $request['department_id'];
        try {
            $JobDescription = new JobDescription();
            $JobDescription->description = $request->description;
            $JobDescription->position_id = $position_id;
            $JobDescription->department_id = $department_id;
            $JobDescription->save();
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Job Description Created Successfully</div>');
    }

    public function addCompetency(Request $request)
    {
        $competency_type_id = $request['competency_type_id'];
        $position_ids = $request['position_id'];
        $titles = $request['title'];

        $rules = [];

        foreach ($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
            $rules["position_id.{$key}"] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            try {
                foreach ($titles as $index => $title) {
                    if (! empty($titles[$index])) {
                        $competency = new Competency();
                        $competency->position_id = $position_ids[$index];
                        $competency->competency_type_id = $competency_type_id;
                        $competency->company_id = session('current_company');
                        $competency->title = $title;
                        $competency->save();
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }

            /* return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;*/
            $competencies = CompetencyType::find($request['competency_type_id'])->competencies;

            return view('competency_type.competencies', compact('competencies'));
        }

        return response()->json(['error'=>$validator->errors()->all()]);

        /* END OF ONE*/
    }

    /**
     * Display the specified resource.
     *
     * @param JobDescription $job_description
     * @return Response
     */
    public function show(JobDescription $job_description)
    {
        $title = 'Competency Qualification Details';
        $action = 'show';

        return view('layouts.show', compact('job_description', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyFramework $job_description
     * @return Response
     */
    public function edit(JobDescription $job_description)
    {
        $title = 'Edit Job Description';

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Responsibility', '')
            ->toArray();

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $qualifications = Qualification::get()
            ->pluck('title', 'id')
            ->toArray();

        $departments = Department::get()
            ->pluck('title', 'id')
            ->prepend(trans('section.select_section'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'job_description', 'qualifications', 'departments', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|$request
     * @param JobDescription $job_description
     * @return Response
     */
    public function update(JobDescriptionRequest $request, JobDescription $job_description)
    {
        try {
            $job_description->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">FRAMEWORK UPDATED SUCCESSFULLY</div>');
    }

    public function delete(JobDescription $job_description)
    {
        $title = 'Delete Job Description';

        return view('job_description.delete', compact('job_description', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  JobDescription $job_description
     * @return Response
     */
    public function destroy(JobDescription $job_description)
    {
        $job_description->delete();

        return 'Deleted';
    }

    public static function data()
    {
        return new CompetencyFrameworkResource(CompetencyFramework::where('company_id', session('current_company')));
    }
}
