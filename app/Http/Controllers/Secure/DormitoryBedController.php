<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\DormitoryBedRequest;
use App\Models\DormitoryBed;
use App\Repositories\DormitoryBedRepository;
use App\Repositories\DormitoryRoomRepository;
use App\Repositories\StudentRepository;
use Yajra\DataTables\Facades\DataTables;
use DB;

class DormitoryBedController extends SecureController
{
    /**
     * @var DormitoryRoomRepository
     */
    private $dormitoryRoomRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var DormitoryBedRepository
     */
    private $dormitoryBedRepository;

    /**
     * DormitoryBedController constructor.
     * @param DormitoryRoomRepository $dormitoryRoomRepository
     * @param StudentRepository $studentRepository
     * @param DormitoryBedRepository $dormitoryBedRepository
     */
    public function __construct(
        DormitoryRoomRepository $dormitoryRoomRepository,
        StudentRepository $studentRepository,
        DormitoryBedRepository $dormitoryBedRepository
    ) {

        parent::__construct();

        $this->dormitoryRoomRepository = $dormitoryRoomRepository;
        $this->studentRepository = $studentRepository;
        $this->dormitoryBedRepository = $dormitoryBedRepository;

        $this->middleware('authorized:dormitorybed.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:dormitorybed.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:dormitorybed.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:dormitorybed.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'dormitorybed');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('dormitorybed.dormitorybeds');
        return view('dormitorybed.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('dormitorybed.new');
        $this->generateParams();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|DormitoryBedRequest $request
     * @return Response
     */
    public function store(DormitoryBedRequest $request)
    {
        $dormitoryBed = new DormitoryBed($request->all());
        $dormitoryBed->save();
        return redirect('/dormitorybed');
    }

    /**
     * Display the specified resource.
     *
     * @param DormitoryBed $bed
     * @return Response
     * @internal param int $id
     */
    public function show(DormitoryBed $dormitoryBed)
    {
        $title = trans('dormitorybed.details');
        $action = 'show';
        return view('layouts.show', compact('dormitoryBed', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param DormitoryBed $bed
     * @return Response
     * @internal param int $id
     */
    public function edit(DormitoryBed $dormitoryBed)
    {
        $title = trans('dormitorybed.edit');

        $this->generateParams();

        return view('layouts.edit', compact('title', 'dormitoryBed'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|DormitoryBedRequest $request
     * @param DormitoryBed $bed
     * @return Response
     * @internal param int $id
     */
    public function update(DormitoryBedRequest $request, DormitoryBed $dormitoryBed)
    {
        $dormitoryBed->update($request->all());
        return redirect('/dormitorybed');
    }

    public function delete(DormitoryBed $dormitoryBed)
    {
        $title = trans('dormitorybed.delete');
        return view('/dormitorybed/delete', compact('dormitoryBed', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DormitoryBed $bed
     * @return Response
     * @throws \Exception
     * @internal param int $id
     */
    public function destroy(DormitoryBed $dormitoryBed)
    {
        $dormitoryBed->delete();
        return redirect('/dormitorybed');
    }

    public function data()
    {
        $dormitoryBeds = $this->dormitoryBedRepository->getAll()
            ->with('dormitory_room', 'student')
            ->get()
            ->map(function ($item) {
                return [
                    "id" => $item->id,
                    "title" => $item->title,
                    "room" => isset($item->dormitory_room) ? $item->dormitory_room->title : "",
                ];
            });
        return Datatables::make($dormitoryBeds)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'dormitorybed.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/dormitorybed/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/dormitorybed/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'dormitorybed.delete\', Sentinel::getUser()->permissions)))
                                         <a href="{{ url(\'/dormitorybed/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make(false);
    }

    /**
     * @return array
     */
    private function generateParams()
    {
        $dormitory_rooms = $this->dormitoryRoomRepository->getAll()
            ->with('dormitory')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => (isset($item->dormitory) ? $item->dormitory->title : "") . ' ' . $item->title,
                ];
            })->pluck('title', 'id')
            ->toArray();
        view()->share('dormitory_rooms', $dormitory_rooms);

        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->with('user', 'section')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => (isset($student->section) ? $student->section->title : "") . " " .
                    isset($student->user) ? $student->user->full_name : "",
                ];
            })->pluck('name', 'id')->toArray();
        view()->share('students', $students);
    }
}
