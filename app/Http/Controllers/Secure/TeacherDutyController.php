<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\TeacherDutyRequest;
use App\Models\TeacherDuty;
use App\Repositories\TeacherDutyRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class TeacherDutyController extends SecureController
{
    /**
     * @var TeacherDutyRepository
     */
    private $teacherDutyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * TeacherDutyController constructor.
     *
     * @param TeacherDutyRepository $teacherDutyRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        TeacherDutyRepository $teacherDutyRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();

        $this->teacherDutyRepository = $teacherDutyRepository;
        $this->userRepository        = $userRepository;

        view()->share('type', 'teacher_duty');

        $columns = ['teacher','start_date', 'end_date' ,'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('teacher_duty.teacherDutys');

        return view('teacher_duty.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title     = trans('teacher_duty.new');
        $day_night = [
            null => trans('teacher_duty.select_part'),
            0    => trans('teacher_duty.over_day'),
            1    => trans('teacher_duty.over_night')
        ];
        $users     = $this->userRepository->getUsersForRole('teacher')->pluck('full_name_email', 'id');

        return view('layouts.create', compact('title', 'day_night', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function store(TeacherDutyRequest $request)
    {
        $teacherDuty = new TeacherDuty($request->all());
        $teacherDuty->save();

        return redirect('/teacher_duty');
    }

    /**
     * Display the specified resource.
     *
     * @param  TeacherDuty $teacherDuty
     *
     * @return Response
     */
    public function show(TeacherDuty $teacherDuty)
    {
        $title  = trans('teacher_duty.details');
        $action = 'show';

        return view('layouts.show', compact('teacherDuty', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  TeacherDuty $teacherDuty
     *
     * @return Response
     */
    public function edit(TeacherDuty $teacherDuty)
    {
        $day_night = [
            null => trans('teacher_duty.select_part'),
            0    => trans('teacher_duty.over_day'),
            1    => trans('teacher_duty.over_night')
        ];
        $users     = $this->userRepository->getUsersForRole('teacher')->pluck('full_name_email', 'id');
        $title     = trans('teacher_duty.edit');

        return view('layouts.edit', compact('title', 'teacherDuty', 'users', 'day_night'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|TeacherDutyRequest $request
     * @param TeacherDuty $teacherDuty
     *
     * @return Response
     */
    public function update(TeacherDutyRequest $request, TeacherDuty $teacherDuty)
    {
        $teacherDuty->update($request->all());

        return redirect('/teacher_duty');
    }


    public function delete(TeacherDuty $teacherDuty)
    {
        $title = trans('teacher_duty.delete');

        return view('/teacher_duty/delete', compact('teacherDuty', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TeacherDuty $teacherDuty
     *
     * @return Response
     */
    public function destroy(TeacherDuty $teacherDuty)
    {
        $teacherDuty->delete();

        return redirect('/teacher_duty');
    }

    public function data()
    {
        $teacherDutys = $this->teacherDutyRepository
            ->getAll()
            ->orderBy('start_date', 'desc')
            ->orderBy('end_date', 'desc')
            ->get()
            ->map(function ($teacherDuty) {
                return [
                    'id'         => $teacherDuty->id,
                    'teacher'    => is_null($teacherDuty->user) ? "-" : $teacherDuty->user->full_name_email,
                    //'day_night'  => ($teacherDuty->day_night==0)?trans('teacher_duty.over_day'):trans('teacher_duty.over_night'),
                    'start_date' => $teacherDuty->start_date,
                    'end_date'   => $teacherDuty->end_date,
                ];
            });

        return Datatables::make($teacherDutys)
                          ->addColumn('actions', '<a href="{{ url(\'/teacher_duty/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/teacher_duty/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/teacher_duty/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
                          ->removeColumn('id')
                          ->rawColumns([ 'actions' ])->make();
    }

    public function teacherDutyTable()
    {
        $title                  = trans('teacher_duty.teacher_duty_table');
        $teacher_duty_table = $this->teacherDutyRepository
                                ->getAllForWeek(
                                    Carbon::now()->startOfMonth()->format('Y-m-d'),
                                    Carbon::now()->endOfMonth()->format('Y-m-d')
                                )->get();
        return view('teacher_duty.teacher_duty_table', compact('title', 'teacher_duty_table'));
    }
}
