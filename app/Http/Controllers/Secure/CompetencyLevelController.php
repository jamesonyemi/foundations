<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyLevelRequest;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Models\Competency;
use App\Models\CompetencyLevel;
use App\Models\CompetencyType;
use App\Models\EmployeeCompetencyMatrix;
use App\Models\Level;
use App\Models\Position;
use App\Models\SchoolDirection;
use App\Models\StudentStatus;
use App\Repositories\EmployeeRepository;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Validator;

class CompetencyLevelController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

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
     * @param EmployeeRepository $employeeRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(
        LevelRepository $levelRepository,
        EmployeeRepository $employeeRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'competency_level');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Competency Levels';
        $competency_levels = CompetencyLevel::whereHas('competency', function ($q) {
            $q->where('competencies.company_id', session('current_company'));
        })->get();

        return view('competency_level.index', compact('title', 'competency_levels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Competency Level';

        $positions = Position::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Position', '')
            ->toArray();

        $competencies = Competency::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Competency', '')
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
            ->prepend('Select Employees', '')
            ->toArray();

        return view('layouts.create', compact('title', 'competencies', 'positions', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|CompetencyRequest $request
     * @return Response
     */
    public function store(CompetencyLevelRequest $request)
    {
        $competencyLevel = new CompetencyLevel($request->all());
        /*$competency->company_id = session('current_company');*/
        $competencyLevel->save();

        return 'Good';
    }

    /**
     * Display the specified resource.
     *
     * @param CompetencyLevel $competency_level
     * @return Response
     */
    public function show(CompetencyLevel $competency_level)
    {
        $title = 'Competency Level Details';
        $action = 'show';

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        $competency_level_employees = EmployeeCompetencyMatrix::where('competency_level_id', $competency_level->id)
        ->get()
        ->pluck('employee_id', 'employee_id')
        ->toArray();

        return view('layouts.show', compact('title', 'action', 'competency_level', 'employees', 'competency_level_employees'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyLevel $competency_level
     * @return Response
     */
    public function edit(CompetencyLevel $competency_level)
    {
        $title = 'Edit Competency Level';
        $positions = Position::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Position', '')
            ->toArray();

        $competencies = Competency::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select Competency', '')
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
            ->toArray();

        return view('layouts.edit', compact('title', 'competencies', 'positions', 'employees', 'competency_level'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param CompetencyLevel $competency_level
     * @return Response
     */
    public function update(CompetencyLevelRequest $request, CompetencyLevel $competency_level)
    {
        $competency_level->update($request->all());

        return 'Competency Level updated';
    }

    public function delete(CompetencyLevel $competency_level)
    {
        $title = 'Delete Competency Level';

        return view('competency_level.delete', compact('competency_level', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CompetencyLevel $competency_level
     * @return Response
     */
    public function destroy(CompetencyLevel $competency_level)
    {
        $competency_level->delete();

        return 'competency Level Deleted';
    }

    public function findSectionLevel(Request $request)
    {
        $directions = $this->levelRepository
            ->getAllForSection($request->section_id)
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_level'), 0)
            ->toArray();

        return $directions;
    }

    public function addEmployees(Request $request)
    {
        $competency_level_id = $request['competency_level_id'];
        $employee_ids = $request['employee_id'];

        try {
            foreach ($employee_ids as $index => $employee_id) {
                EmployeeCompetencyMatrix::firstOrCreate(
                        [
                            'competency_level_id' => $competency_level_id,
                            'employee_id' => $employee_id,
                            'company_year_id' => session('current_company_year'),
                        ]);
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employees Added Successfully</div>');

        /* END OF ONE*/
    }
}
