<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\CompetencyRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Models\Competency;
use App\Models\CompetencyLevel;
use App\Models\CompetencyType;
use App\Models\Level;
use App\Models\Position;
use App\Models\SchoolDirection;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Validator;

class CompetencyController extends SecureController
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

        view()->share('type', 'competency');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Competencies';
        $competencies = Competency::where('company_id', session('current_company'))
            ->get();

        return view('competency.index', compact('title', 'competencies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = 'New Competency';

        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend('Select Position', 0)
            ->toArray();

        $competencyTypes = CompetencyType::get()
            ->pluck('title', 'id')
            ->prepend('Select Competency Type', '')
            ->toArray();

        return view('layouts.create', compact('title', 'competencyTypes', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|CompetencyRequest $request
     * @return Response
     */
    public function store(CompetencyRequest $request)
    {
        try {
            $competency = new Competency($request->all());
            $competency->company_id = session('current_company');
            $competency->save();
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Competency Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param Position $position
     * @return Response
     */
    public function show(Competency $competency)
    {
        $title = 'Competency Details';
        $action = 'show';
        $competency_levels = $competency->competency_levels;

        return view('layouts.show', compact('competency', 'title', 'action', 'competency_levels'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Competency $competency
     * @return Response
     */
    public function edit(Competency $competency)
    {
        $title = 'Edit Competency';
        $positions = Position::get()
            ->pluck('title', 'id')
            ->prepend('Select Position', '')
            ->toArray();

        $competencyTypes = CompetencyType::get()
            ->pluck('title', 'id')
            ->prepend('Select Competency Type', '')
            ->toArray();

        return view('layouts.edit', compact('title', 'competency', 'competencyTypes', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Competency $competency
     * @return Response
     */
    public function update(CompetencyRequest $request, Competency $competency)
    {
        $competency->update($request->all());

        return 'Competency updated';
    }

    public function delete(Competency $competency)
    {
        $title = 'Delete Competencies';

        return view('competency.delete', compact('competency', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Competency $competency
     * @return Response
     */
    public function destroy(Competency $competency)
    {
        $competency->delete();

        return 'competency Deleted';
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
             ->rawColumns(['actions'])->make();
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

    public function addCompetencyLevel(Request $request)
    {
        $competency_id = $request['competency_id'];
        $titles = $request['title'];
        $descriptions = $request['description'];

        $rules = [];

        foreach ($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            try {
                foreach ($titles as $index => $title) {
                    if (! empty($titles[$index])) {
                        $competency = new CompetencyLevel();
                        $competency->competency_id = $competency_id;
                        $competency->title = $title;
                        $competency->description = $descriptions[$index];
                        $competency->save();
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }

            /* return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;*/
            $competency_levels = Competency::find($request['competency_id'])->competency_levels;

            return view('competency.levels', compact('competency_levels'));
        }

        return response()->json(['error'=>$validator->errors()->all()]);

        /* END OF ONE*/
    }
}
