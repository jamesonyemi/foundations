<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\BlogRequest;
use App\Http\Requests\Secure\VisitorLogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Yajra\DataTables\Facades\DataTables;

class VisitorVisitController extends SecureController
{
    public function __construct()
    {
        parent::__construct();
        view()->share('type', 'visitor_visit');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('visitor_visit.visitor_visit');

        return view('visitor_visit.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('visitor_visit.new');
        $this->generateData();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorLogRequest|Request $request
     * @return Response
     */
    public function store(VisitorLogRequest $request)
    {
        VisitorLog::create($request->all());

        return redirect('/visitor_visit');
    }

    /**
     * Display the specified resource.
     *
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function show(VisitorLog $visitorLog)
    {
        $title = trans('visitor_visit.details');
        $action = 'show';

        return view('layouts.show', compact('title', 'visitorLog', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function edit(VisitorLog $visitorLog)
    {
        $title = trans('visitor_visit.edit');
        $this->generateData();

        return view('layouts.edit', compact('title', 'visitorLog'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VisitorLogRequest|Request $request
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function update(VisitorLogRequest $request, VisitorLog $visitorLog)
    {
        $visitorLog->update($request->all());

        return redirect('/visitor_visit');
    }

    public function data()
    {
        $visitorLogs = VisitorLog::get()
            ->filter(function ($visitorVisit) {
                return ! is_null($visitorVisit->user);
            })
            ->map(function ($visitorVisit) {
                return [
                    'id' => $visitorVisit->id,
                    'user' => $visitorVisit->user->full_name,
                    'check_in' => $visitorVisit->check_in,
                    'check_out' => $visitorVisit->check_out,
                    'visited_user' => isset($visitorVisit->visited_user) ? $visitorVisit->visited_user->full_name : '',
                ];
            });

        return Datatables::make($visitorLogs)
            ->addColumn('actions', '<a href="{{ url(\'/visitor_visit/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/visitor_visit/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }

    /**
     * @return array
     */
    private function generateData()
    {
        $visitors = Visitor::join('users', 'users.id', '=', 'visitors.user_id')
            ->select('users.id', DB::raw('CONCAT(users.first_name, " ", users.last_name, " - ", visitors.visitor_no) as title'))
                           ->pluck('title', 'id')->toArray();

        view()->share('visitors', $visitors);

        $visited_user = User::whereNotIn('id', Visitor::pluck('user_id'))
                        ->select('users.id', DB::raw('CONCAT(users.first_name, " ", users.last_name, " ", users.email) as title'))
                           ->pluck('title', 'id')->toArray();

        view()->share('visited_user', $visited_user);
    }
}
