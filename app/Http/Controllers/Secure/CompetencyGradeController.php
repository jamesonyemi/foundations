<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyGradeRequest;
use App\Http\Requests\Secure\CompetencyLevelRequest;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Models\Competency;
use App\Models\CompetencyGrade;
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
use Illuminate\Support\Facades\DB;
use Validator;

class CompetencyGradeController extends SecureController
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

        view()->share('type', 'competency_grade');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Competency Grades';
        $competency_grades = CompetencyGrade::where('company_id', session('current_company'))
         ->get();

        return view('competency_grade.index', compact('title', 'competency_grades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Competency Grade';

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
    public function store(CompetencyGradeRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $competencyGrade = new CompetencyGrade($request->all());
                $competencyGrade->company_id = session('current_company');
                $competencyGrade->save();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">CompetencyGrade Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param CompetencyLevel $competency_grade
     * @return Response
     */
    public function show(CompetencyGrade $competency_grade)
    {
        $title = 'Competency Grade Details';
        $action = 'show';

        return view('layouts.show', compact('title', 'action', 'competency_grade'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CompetencyLevel $competency_grade
     * @return Response
     */
    public function edit(CompetencyGrade $competency_grade)
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

        return view('layouts.edit', compact('title', 'competencies', 'positions', 'employees', 'competency_grade'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param CompetencyLevel $competency_grade
     * @return Response
     */
    public function update(CompetencyGradeRequest $request, CompetencyGrade $competency_grade)
    {
        try {
            DB::transaction(function () use ($request, $competency_grade) {
                $competency_grade->update($request->all());
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Competency Level Updated Successfully</div>');
    }

    public function delete(CompetencyGrade $competency_grade)
    {
        $title = 'Delete Competency Level';

        return view('competency_grade.delete', compact('competency_grade', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CompetencyLevel $competency_grade
     * @return Response
     */
    public function destroy(CompetencyGrade $competency_grade)
    {
        try {
            DB::transaction(function () use ($competency_grade) {
                $competency_grade->delete();
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Competency Grade Deleted Successfully</div>');
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
        $competency_grade_id = $request['competency_grade_id'];
        $employee_ids = $request['employee_id'];

        try {
            foreach ($employee_ids as $index => $employee_id) {
                EmployeeCompetencyMatrix::firstOrCreate(
                        [
                            'competency_grade_id' => $competency_grade_id,
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
