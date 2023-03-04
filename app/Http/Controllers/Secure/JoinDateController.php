<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests;
use App\Http\Requests\Secure\JoinDateRequest;
use App\Models\JoinDate;
use App\Models\School;
use App\Models\SchoolAdmin;
use App\Models\User;
use App\Repositories\JoinDateRepository;
use Yajra\DataTables\Facades\DataTables;

class JoinDateController extends SecureController
{
    /**
     * @var JoinDateRepository
     */
    private $joinDateRepository;

    /**
     * JoinDateController constructor.
     * @param JoinDateRepository $joinDateRepository
     */
    public function __construct(JoinDateRepository $joinDateRepository)
    {
        parent::__construct();

        $this->joinDateRepository = $joinDateRepository;

        view()->share('type', 'join_date');

        $columns = ['full_name', 'school', 'date_start', 'date_end', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(User $teacher)
    {
        $title = trans('join_date.join_date');
        return view('join_date.index', compact('title', 'teacher'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(User $teacher)
    {
        $title = trans('join_date.new');
        if ($this->user->inRole('human_resources') || $this->user->inRole('accountant') || $this->user->inRole('super_admin')) {
            $schools_list = School::pluck('title', 'id')->toArray();
        } else {
            $schools_list = SchoolAdmin::join('schools', 'schools.id', '=', 'school_admins.company_id')
                ->where('user_id', $this->user->id)->select('schools.*')->pluck('title', 'id')->toArray();
        }

        return view('layouts.create', compact('title', 'schools_list', 'teacher'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(JoinDateRequest $request, User $teacher)
    {
        $joinDate = new JoinDate($request->all());
        $joinDate->user_id = $teacher->id;
        $joinDate->save();

        return redirect('/join_date/' . $teacher->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(User $teacher, JoinDate $joinDate)
    {
        $title = trans('join_date.details');
        $action = 'show';
        return view('layouts.show', compact('joinDate', 'title', 'action', 'teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(User $teacher, JoinDate $joinDate)
    {
        $title = trans('teacher.edit');
        if ($this->user->inRole('human_resources') || $this->user->inRole('accountant') || $this->user->inRole('super_admin')) {
            $schools_list = School::pluck('title', 'id')->toArray();
        } else {
            $schools_list = SchoolAdmin::join('schools', 'schools.id', '=', 'school_admins.company_id')
                ->where('user_id', $this->user->id)->select('schools.*')->pluck('title', 'id')->toArray();
        }
        return view('layouts.edit', compact('title', 'joinDate', 'schools_list', 'teacher'));
    }

    /**
     * Update the specified resource in storage.
     * @param  int $id
     * @return Response
     */
    public function update(JoinDateRequest $request, User $teacher, JoinDate $joinDate)
    {
        $joinDate->update($request->all());
        return redirect('join_date/' . $teacher->id);
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(User $teacher, JoinDate $joinDate)
    {
        $title = trans('join_date.delete');
        return view('join_date/delete', compact('teacher', 'joinDate', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(User $teacher, JoinDate $joinDate)
    {
        $joinDate->delete();

        return redirect('join_date/' . $teacher->id);
    }

    public function data(User $teacher)
    {
        $join_dates = $this->joinDateRepository->getAllForSchoolAndStaff(session('current_company'), $teacher->id)
            ->get()
            ->map(function ($join_date) {
                return [
                    'id' => $join_date->id,
                    'user_id' => $join_date->user_id,
                    'full_name' => $join_date->user->full_name,
                    'school' => $join_date->school->title,
                    'date_start' => $join_date->date_start,
                    'date_end' => $join_date->date_end,
                ];
            });
        return Datatables::make($join_dates)
            ->addColumn('actions', '<a href="{{ url(\'/join_date/\' . $user_id . \'/\'.$id.\'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/join_date/\' . $user_id . \'/\'.$id.\'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/join_date/\' . $user_id . \'/\'.$id.\'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->removeColumn('user_id')
             ->rawColumns([ 'actions' ])->make();
    }
}
