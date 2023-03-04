<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\DormitoryRoomRequest;
use App\Models\DormitoryRoom;
use App\Repositories\DormitoryRepository;
use App\Repositories\DormitoryRoomRepository;
use Yajra\DataTables\Facades\DataTables;

class DormitoryRoomController extends SecureController
{
    /**
     * @var DormitoryRoomRepository
     */
    private $dormitoryRoomRepository;
    /**
     * @var DormitoryRepository
     */
    private $dormitoryRepository;

    /**
     * DormitoryRoomController constructor.
     * @param DormitoryRoomRepository $dormitoryRoomRepository
     * @param DormitoryRepository $dormitoryRepository
     */
    public function __construct(
        DormitoryRoomRepository $dormitoryRoomRepository,
        DormitoryRepository $dormitoryRepository
    ) {
    
        parent::__construct();

        $this->dormitoryRoomRepository = $dormitoryRoomRepository;
        $this->dormitoryRepository = $dormitoryRepository;

        $this->middleware('authorized:dormitoryroom.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:dormitoryroom.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:dormitoryroom.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:dormitoryroom.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'dormitoryroom');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('dormitoryroom.dormitoryrooms');
        return view('dormitoryroom.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('dormitoryroom.new');
        $dormitories = $this->dormitoryRepository->getAll()->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title
                ];
            })->pluck('title', 'id')->toArray();
        return view('layouts.create', compact('title', 'dormitories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(DormitoryRoomRequest $request)
    {
        $dormitoryRoom = new DormitoryRoom($request->all());
        $dormitoryRoom->save();
        return redirect('/dormitoryroom');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(DormitoryRoom $dormitoryRoom)
    {
        $title = trans('dormitoryroom.details');
        $action = 'show';
        return view('layouts.show', compact('dormitoryRoom', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(DormitoryRoom $dormitoryRoom)
    {
        $title = trans('dormitoryroom.edit');
        $dormitories = $this->dormitoryRepository->getAll()->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title
                ];
            })->pluck('title', 'id')->toArray();
        return view('layouts.edit', compact('title', 'dormitoryRoom', 'dormitories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     * @return Response
     */
    public function update(DormitoryRoomRequest $request, DormitoryRoom $dormitoryRoom)
    {
        $dormitoryRoom->update($request->all());
        return redirect('/dormitoryroom');
    }

    public function delete(DormitoryRoom $dormitoryRoom)
    {
        $title = trans('dormitoryroom.delete');
        return view('/dormitoryroom/delete', compact('dormitoryRoom', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(DormitoryRoom $dormitoryRoom)
    {
        $dormitoryRoom->delete();
        return redirect('/dormitoryroom');
    }

    public function data()
    {
        $rooms = $this->dormitoryRoomRepository->getAll()
            ->with('dormitory')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'title' => $room->title,
                    'dormitory' => isset($room->dormitory) ? $room->dormitory->title : "",
                ];
            });

        return Datatables::make($rooms)
            ->filterColumn('dormitory', 'whereRaw', "dormitories.title like ?", ["$1"])
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'dormitoryroom.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/dormitoryroom/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                        <a href="{{ url(\'/dormitoryroom/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'dormitoryroom.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/dormitoryroom/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make(false);
    }
}
