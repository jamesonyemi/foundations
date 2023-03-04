<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests;
use App\Http\Requests\Secure\SchoolAdminRequest;
use App\Http\Requests\Secure\SchoolDirectionRequest;
use App\Models\SchoolDirection;
use App\Repositories\DirectionRepository;
use App\Repositories\SchoolDirectionRepository;
use App\Repositories\SchoolRepository;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class SchoolDirectionController extends SecureController
{
    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * @var DirectionRepository
     */
    private $directionRepository;

    /**
     * @var SchoolDirectionRepository
     */
    private $schoolDirectionRepository;

    /**
     * TeacherController constructor.
     * @param SchoolRepository $schoolRepository
     * @param DirectionRepository $directionRepository
     * @param SchoolDirectionRepository $schoolDirectionRepository
     * @internal param UserRepository $userRepository
     */
    public function __construct(
        SchoolRepository $schoolRepository,
        DirectionRepository $directionRepository,
        SchoolDirectionRepository $schoolDirectionRepository
    ) {
        parent::__construct();

        $this->schoolRepository = $schoolRepository;
        $this->directionRepository = $directionRepository;
        $this->schoolDirectionRepository = $schoolDirectionRepository;

        view()->share('type', 'school_direction');

        $columns = ['school_name', 'direction_name', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('school_direction.school_direction');

        return view('school_direction.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('school_direction.new');
        $company_ids = ['' => trans('school_direction.select_school')] + $this->schoolRepository->getAll()->pluck('title', 'id')->toArray();
        $direction_ids = ['' => trans('school_direction.select_direction')] + $this->directionRepository->getAll()->pluck('title', 'id')->toArray();

        return view('layouts.create', compact('title', 'company_ids', 'direction_ids'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SchoolDirectionRequest $request)
    {
        SchoolDirection::create($request->all());

        return redirect('/school_direction');
    }

    /**
     * Display the specified resource.
     *
     * @param  SchoolDirection $schoolDirection
     * @return Response
     */
    public function show(SchoolDirection $schoolDirection)
    {
        $title = trans('school_direction.details');
        $action = 'show';

        return view('layouts.show', compact('schoolDirection', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(SchoolDirection $schoolDirection)
    {
        $title = trans('school_direction.edit');
        $company_ids = ['' => trans('school_direction.select_school')] + $this->schoolRepository->getAll()->pluck('title', 'id')->toArray();
        $direction_ids = ['' => trans('school_direction.select_direction')] + $this->directionRepository->getAll()->pluck('title', 'id')->toArray();

        return view('layouts.edit', compact('title', 'schoolDirection', 'company_ids', 'direction_ids'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(SchoolDirectionRequest $request, SchoolDirection $schoolDirection)
    {
        $schoolDirection->update($request->all());

        return redirect('/school_direction');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(SchoolDirection $schoolDirection)
    {
        $title = trans('school_direction.delete');

        return view('/school_direction/delete', compact('schoolDirection', 'title', 'school'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(SchoolDirection $schoolDirection)
    {
        $schoolDirection->delete();

        return redirect('/school_direction');
    }

    public function data()
    {
        $school_directions = $this->schoolDirectionRepository->getAll()
            ->with('school', 'direction')
            ->get()
            ->map(function ($school_direction) {
                return [
                    'id' => $school_direction->id,
                    'school_name' => isset($school_direction->school->title) ? $school_direction->school->title : '',
                    'direction_name' => isset($school_direction->direction->title) ? $school_direction->direction->title : '',
                ];
            });

        return Datatables::make($school_directions)
            ->addColumn('actions', '<a href="{{ url(\'/school_direction/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/school_direction/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/school_direction/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
