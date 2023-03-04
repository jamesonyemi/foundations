<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\TransportationRequest;
use App\Models\Transportation;
use App\Models\TransportationLocation;
use App\Models\TransportationPassengers;
use App\Repositories\TransportationRepository;
use App\Repositories\UserRepository;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class TransportationController extends SecureController
{
    /**
     * @var TransportationRepository
     */
    private $transportationRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TransportationController constructor.
     *
     * @param TransportationRepository $transportationRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        TransportationRepository $transportationRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();

        $this->transportationRepository = $transportationRepository;
        $this->userRepository = $userRepository;

        $this->middleware('authorized:transportation.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:transportation.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:transportation.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:transportation.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'transportation');

        $columns = ['title', 'start', 'fee', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('transportation.transportation');

        return view('transportation.index', compact('title', 'hide_add'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('transportation.new');
        $users = $this->userRepository->getAllUsersFromSchool(session('current_company'), session('current_company_year'))
                                      ->map(function ($user) {
                                          return [
                                              'id'        => $user->id,
                                              'full_name' => $user->full_name.' - '.$user->email,
                                          ];
                                      })->pluck('full_name', 'id');

        return view('layouts.create', compact('title', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TransportationRequest $request
     *
     * @return Response
     */
    public function store(TransportationRequest $request)
    {
        $transportation = new Transportation($request->except('location', 'passengers'));
        $transportation->company_id = session('current_company');
        $transportation->save();

        foreach ($request->get('location') as $location) {
            TransportationLocation::create([
                'transportation_id' => $transportation->id,
                'name'              => $location,
                'lat'               => '',
                'lang'              => '',
            ]);
        }

        foreach ($request->get('passengers') as $passenger) {
            TransportationPassengers::create(['transportation_id' => $transportation->id, 'user_id' => $passenger]);
        }

        return redirect('/transportation');
    }

    /**
     * Display the specified resource.
     *
     * @param  Transportation $transportation
     *
     * @return Response
     */
    public function show(Transportation $transportation)
    {
        $title = trans('transportation.details');
        $action = 'show';

        return view('layouts.show', compact('transportation', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Transportation $transportation
     *
     * @return Response
     */
    public function edit(Transportation $transportation)
    {
        $title = trans('transportation.edit');
        $users = $this->userRepository->getAllUsersFromSchool(session('current_company'), session('current_company_year'))
                                      ->map(function ($user) {
                                          return [
                                              'id'        => $user->id,
                                              'full_name' => $user->full_name.' - '.$user->email,
                                          ];
                                      })->pluck('full_name', 'id');

        return view('layouts.edit', compact('title', 'transportation', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TransportationRequest $request
     * @param  Transportation $transportation
     *
     * @return Response
     */
    public function update(TransportationRequest $request, Transportation $transportation)
    {
        $transportation->update($request->except('location', 'passengers'));
        TransportationLocation::where('transportation_id', $transportation->id)->delete();

        foreach ($request->get('location') as $location) {
            TransportationLocation::create([
                'transportation_id' => $transportation->id,
                'name'              => $location,
                'lat'               => '',
                'lang'              => '',
            ]);
        }

        TransportationPassengers::where('transportation_id', $transportation->id)->delete();
        foreach ($request->get('passengers') as $passenger) {
            TransportationPassengers::create(['transportation_id' => $transportation->id, 'user_id' => $passenger]);
        }

        return redirect('/transportation');
    }

    /**
     * @param Transportation $transportation
     *
     * @return Response
     */
    public function delete(Transportation $transportation)
    {
        $title = trans('transportation.delete');

        return view('/transportation/delete', compact('transportation', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Transportation $transportation
     *
     * @return Response
     */
    public function destroy(Transportation $transportation)
    {
        $transportation->delete();

        return redirect('/transportation');
    }

    public function data()
    {
        if (Sentinel::getUser()->inRole('admin') || Sentinel::getUser()->inRole('admin_super_admin')) {
            $transportation = $this->transportationRepository->getAllForSchool(session('current_company'));
        } else {
            $transportation = $this->transportationRepository->getAllForUser(Sentinel::getUser()->id);
        }
        $transportation = $transportation->get()
                                         ->map(function ($item) {
                                             return [
                                                 'id'    => $item->id,
                                                 'title' => $item->title,
                                                 'start' => $item->start,
                                                 'fee'   => $item->fee,
                                             ];
                                         });

        return Datatables::make($transportation)
                          ->addColumn('actions', '@if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'transportation.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/transportation/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                   @endif
                                    <a href="{{ url(\'/transportation/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @if(Sentinel::getUser()->inRole(\'super_admin\') || Sentinel::getUser()->inRole(\'admin_super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'transportation.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/transportation/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
                          ->removeColumn('id')
                          ->rawColumns(['actions'])->make();
    }
}
