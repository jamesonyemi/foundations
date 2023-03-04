<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\LevelRequest;
use App\Http\Requests\Secure\PositionRequest;
use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\Company;
use App\Models\Competency;
use App\Models\CompetencyType;
use App\Models\Department;
use App\Models\Level;
use App\Models\Position;
use App\Models\PositionCompetency;
use App\Models\SchoolDirection;
use App\Models\ScoreCard;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Validator;

class PositionController extends SecureController
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

        view()->share('type', 'position');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('position.position');
        $positions = Position::with('employees')
            ->get();

        return view('position.index', compact('title', 'positions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('position.new');


        return view('position.modalForm', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(PositionRequest $request)
    {
        $position = new Position($request->all());
        $position->company_id = session('current_company');
        $position->save();

        return 'Position added successfully';
    }

    /**
     * Display the specified resource.
     *
     * @param Position $position
     * @return Response
     */
    public function show(Position $position)
    {
        $title = trans('position.details');
        /*$competencies = $position->competencies;*/
        $action = 'show';
        /*$competencyTypes = CompetencyType::where('company_id', session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend('Select' . trans('position.competency_type'), '')
            ->toArray();;*/
        return view('layouts.show', compact('position', 'title', 'action'));
    }

    public function addCompetency(Request $request)
    {
        $position_id = $request['position_id'];
        $competency_type_ids = $request['competency_type_id'];
        $titles = $request['title'];

        $rules = [];

        foreach ($request->input('title') as $key => $value) {
            $rules["title.{$key}"] = 'required|min:3';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            try {
                foreach ($titles as $index => $title) {
                    if (! empty($titles[$index])) {
                        $competency = new Competency();
                        $competency->position_id = $position_id;
                        $competency->competency_type_id = $competency_type_ids[$index];
                        $competency->company_id = session('current_company');
                        $competency->title = $title;
                        $competency->save();
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['exception'=>$e->getMessage()]);
            }

            /* return response('<div class="alert alert-success">KPI CREATED Successfully</div>') ;*/
            $competencies = Position::find($request['position_id'])->competencies;

            return view('position.competencies', compact('competencies'));
        }

        return response()->json(['error'=>$validator->errors()->all()]);

        /* END OF ONE*/
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Position $position
     * @return Response
     */
    public function edit(Position $position)
    {
        $title = trans('position.edit');

        return view('position.modalForm', compact('title', 'position'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Position $position
     * @return Response
     */
    public function update(PositionRequest $request, Position $position)
    {
        $position->update($request->all());

        return 'Position updated';
    }

    public function delete(Position $position)
    {
        $title = trans('level.delete');

        return view('position.delete', compact('position', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Position $position
     * @return Response
     */
    public function destroy(Position $position)
    {
        if ($position->employees->count() > 0) {
            $msg = $position->title.' Position has employees and cannot be deleted';

            return Response('<div class="alert alert-danger">'.$msg.'</div>');
        } else {
            $position->delete();

            return 'Position Deleted';
        }
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

    public function erpSync(Request $request)
    {
        $company = Company::find(session('current_company'));
        if ($company->erp_positions_endpoint === '') {
            return response('<div class="alert alert-danger">ERP EndPoint Not Set, Contact System Administrator!!!</div>');
        }
        try {
            DB::transaction(function () use ($request, $company) {
                $response = Http::withToken('BdUOYHXBApVGYmmriKENHrH90EE3wBf2kIUq3X9qIyeQgT3RThv9jUrfowB7DL89rkMnykyNmO1ElE3w')->get('http://api.jospong.com/erp/public/api/'.$company->erp_positions_endpoint)->json();

                $dataCollection = collect($response);

                foreach ($response as $key) {
                    Position::firstOrCreate(
                        [
                            'title' => $key['Job Description'],
                            'company_id' => session('current_company'),
                            'code' => $key['Job ID'],
                        ],
                    );
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Employees Synced Successfully</div>');
    }
}
