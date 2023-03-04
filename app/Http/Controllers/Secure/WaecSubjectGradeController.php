<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ApplicationTypeRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\WaecSubjectGradeRequest;
use App\Models\WaecSubject;
use App\Models\WaecSubjectGrade;
use App\Models\Level;
use App\Repositories\SectionRepository;
use App\Models\SchoolDirection;
use App\Repositories\LevelRepository;

use App\Repositories\ApplicationTypeRepository;
use App\Repositories\WaecSubjectRepository;
use App\Repositories\WaecSubjectGradeRepository;
use App\Helpers\Settings;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class WaecSubjectGradeController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    private $applicationTypeRepository;
    private $waecSubjectRepository;
    private $waecSubjectGradeRepository;

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
        WaecSubjectRepository $waecSubjectRepository,
        WaecSubjectGradeRepository $waecSubjectGradeRepository,
        SectionRepository $sectionRepository,
        ApplicationTypeRepository $applicationTypeRepository
    ) {

        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->waecSubjectRepository = $waecSubjectRepository;
        $this->waecSubjectGradeRepository = $waecSubjectGradeRepository;
        $this->sectionRepository = $sectionRepository;
        $this->applicationTypeRepository = $applicationTypeRepository;

        view()->share('type', 'waec_subject_grade');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Application Subjects Grades';
        $waecSubjectGrades = $this->waecSubjectGradeRepository->getAllForSchool(session('current_company'))
            ->get();
        return view('waec_subject_grade.index', compact('title', 'waecSubjectGrades'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('level.new');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        $applicationTypes = $this->applicationTypeRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('applicant.select_application_type'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'sections', 'applicationTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(WaecSubjectGradeRequest $request)
    {
        $waecSubjectgrade = new WaecSubjectGrade($request->all());
        $waecSubjectgrade->company_id = session('current_company');
        $waecSubjectgrade->save();

        return redirect('/waec_subject_grade');
    }

    /**
     * Display the specified resource.
     *
     * @param Level $level
     * @return Response
     */
    public function show(WaecSubjectGrade $waecSubjectGrade)
    {
        $title = trans('level.details');
        $action = 'show';
        return view('layouts.show', compact('waecSubjectGrade', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Level $level
     * @return Response
     */
    public function edit(WaecSubjectGrade $waecSubjectGrade)
    {
        $title = trans('level.edit');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        $applicationTypes = $this->applicationTypeRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('applicant.select_application_type'), '')
            ->toArray();

        return view('layouts.edit', compact('title', 'waecSubjectGrade', 'sections', 'applicationTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Level $level
     * @return Response
     */
    public function update(WaecSubjectGradeRequest $request, WaecSubjectGrade $waecSubjectGrade)
    {
        $waecSubjectGrade->update($request->all());
        return redirect('/waec_subject_grade');
    }

    public function delete(WaecSubjectGrade $waecSubjectGrade)
    {
        $title = trans('level.delete');
        return view('waec_subject_grade.delete', compact('waecSubjectGrade', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Level $level
     * @return Response
     */
    public function destroy(WaecSubjectGrade $waecSubjectGrade)
    {
        $waecSubjectGrade->delete();
        return redirect('/waec_subject_grade');
    }

    public function data()
    {
        $levels = $this->levelRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'section' => $level->section->title,
                ];
            });

        return Datatables::make($levels)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.edit\', Sentinel::getUser()->permissions)))
										<a href="{{ url(\'/levels/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.show\', Sentinel::getUser()->permissions)))
                                    	<a href="{{ url(\'/levels/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @endif
                                     @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.delete\', Sentinel::getUser()->permissions)))
                                     	<a href="{{ url(\'/levels/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
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
}
