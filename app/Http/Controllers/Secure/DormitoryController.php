<?php
namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\DormitoryRequest;
use App\Models\Dormitory;
use App\Repositories\DormitoryRepository;
use Yajra\DataTables\Facades\DataTables;

class DormitoryController extends SecureController
{
    /**
     * @var DormitoryRepository
     */
    private $dormitoryRepository;

    /**
     * DormitoryController constructor.
     * @param DormitoryRepository $dormitoryRepository
     */
    public function __construct(DormitoryRepository $dormitoryRepository)
    {
        parent::__construct();

        $this->dormitoryRepository = $dormitoryRepository;

        $this->middleware('authorized:dormitory.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:dormitory.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:dormitory.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:dormitory.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'dormitory');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('dormitory.dormitories');
        return view('dormitory.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('dormitory.new');
        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|DormitoryRequest $request
     * @return Response
     */
    public function store(DormitoryRequest $request)
    {
        $dormitory = new Dormitory($request->all());
        $dormitory->save();
        return redirect('/dormitory');
    }

    /**
     * Display the specified resource.
     *
     * @param Dormitory $dormitory
     * @return Response
     * @internal param int $id
     */
    public function show(Dormitory $dormitory)
    {
        $title = trans('dormitory.details');
        $action = 'show';
        return view('layouts.show', compact('dormitory', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Dormitory $dormitory
     * @return Response
     * @internal param int $id
     */
    public function edit(Dormitory $dormitory)
    {
        $title = trans('dormitory.edit');
        return view('layouts.edit', compact('title', 'dormitory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param Dormitory $dormitory
     * @return Response
     * @internal param int $id
     */
    public function update(DormitoryRequest $request, Dormitory $dormitory)
    {
        $dormitory->update($request->all());
        return redirect('/dormitory');
    }

    public function delete(Dormitory $dormitory)
    {
        $title = trans('dormitory.delete');
        return view('/dormitory/delete', compact('dormitory', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Dormitory $dormitory
     * @return Response
     * @throws \Exception
     * @internal param int $id
     */
    public function destroy(Dormitory $dormitory)
    {
        $dormitory->delete();
        return redirect('/dormitory');
    }

    public function data()
    {
        $dormitories = $this->dormitoryRepository->getAll()
            ->get()
            ->map(function ($dormitory) {
                return [
                    'id' => $dormitory->id,
                    'title' => $dormitory->title,
                ];
            });

        return Datatables::make($dormitories)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'dormitory.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/dormitory/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                   @endif
                                    <a href="{{ url(\'/dormitory/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                   @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'dormitory.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/dormitory/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make(false);
    }
}
