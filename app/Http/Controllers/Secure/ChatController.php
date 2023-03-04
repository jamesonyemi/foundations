<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyFrameworkRequest;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\KpiPerformanceRequest;
use App\Http\Requests\Secure\KpiRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\QualificationFrameworkRequest;
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
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiPerformance;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Level;
use App\Models\Position;
use App\Models\Qualification;
use App\Models\QualificationFramework;
use App\Models\SchoolDirection;
use App\Repositories\EmployeeRepository;
use App\Repositories\KpiRepository;
use App\Repositories\KraRepository;
use Illuminate\Http\Request;
use Validator;

class ChatController extends SecureController
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

        view()->share('type', 'chat');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Qualification Framework';
        $employees = $this->employeeRepository->getAllForSchoolChat(session('current_company'))
            ->with('user', 'section')
            ->get();

        return view('chat.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Qualification Framework';

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

        $positions = Position::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $qualifications = Qualification::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        $departments = Department::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'employees', 'positions', 'qualifications', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|CompetencyFrameworkRequest $request
     * @return Response
     */
    public function store(QualificationFrameworkRequest $request)
    {
        $qualification_ids = $request['qualification_id'];
        $position_id = $request['position_id'];
        $section_id = $request['section_id'];
        try {
            if (isset($request['qualification_id'])) {
                foreach ($qualification_ids as $index => $qualification_id) {
                    $qualificationFramework = new QualificationFramework();
                    $qualificationFramework->qualification_id = $qualification_id;
                    $qualificationFramework->position_id = $position_id;
                    $qualificationFramework->section_id = $section_id;
                    $qualificationFramework->save();
                }
            }

            /* END OF ONE*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Framework Created Successfully</div>');
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
     * @param QualificationFramework $qualification_framework
     * @return Response
     */
    public function show(QualificationFramework $qualification_framework)
    {
        $title = 'Competency Qualification Details';
        $action = 'show';

        return view('layouts.show', compact('qualification_framework', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyFramework $qualification_framework
     * @return Response
     */
    public function edit(QualificationFramework $qualification_framework)
    {
        $title = 'Edit Qualification Framework';

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

        $positions = Position::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('position.select_position'), '')
            ->toArray();

        $qualifications = Qualification::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->toArray();

        $departments = Department::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('section.select_section'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'qualification_framework', 'qualifications', 'departments', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|$request
     * @param CompetencyFramework $qualification_framework
     * @return Response
     */
    public function update(QualificationFrameworkRequest $request, QualificationFramework $qualification_framework)
    {
        try {
            $qualification_framework->update($request->all());
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">FRAMEWORK UPDATED SUCCESSFULLY</div>');
    }

    public function delete(QualificationFramework $qualification_framework)
    {
        $title = 'Delete Qualification Framework';

        return view('qualification_framework.delete', compact('qualification_framework', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  QualificationFramework $qualification_framework
     * @return Response
     */
    public function destroy(QualificationFramework $qualification_framework)
    {
        $qualification_framework->delete();

        return 'Deleted';
    }

    public static function data()
    {
        return new CompetencyFrameworkResource(CompetencyFramework::where('company_id', session('current_company')));
    }
}
