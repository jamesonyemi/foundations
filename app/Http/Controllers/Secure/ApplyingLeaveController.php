<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ApplyingLeaveRequest;
use App\Models\ApplyingLeave;
use App\Repositories\ApplyingLeaveRepository;
use App\Repositories\TeacherSubjectRepository;
use Yajra\DataTables\Facades\DataTables;

class ApplyingLeaveController extends SecureController
{
    /**
     * @var ApplyingLeaveRepository
     */
    private $applyingLeaveRepository;

    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * ApplyingLeaveController constructor.
     * @param ApplyingLeaveRepository $applyingLeaveRepository
     * @param TeacherSubjectRepository $teacherSubjectRepository
     */
    public function __construct(
        ApplyingLeaveRepository $applyingLeaveRepository,
        TeacherSubjectRepository $teacherSubjectRepository
    ) {
        parent::__construct();

        $this->applyingLeaveRepository = $applyingLeaveRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;

        view()->share('type', 'applyingleave');

        $columns = ['title', 'name', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('applyingleave.applyingleaves');

        return view('applyingleave.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('applyingleave.new');

        $this->generateParam();

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|ApplyingLeaveRequest $request
     * @return Response
     */
    public function store(ApplyingLeaveRequest $request)
    {
        $applyingLeave = new ApplyingLeave($request->all());
        $applyingLeave->parent_id = $this->user->id;
        $applyingLeave->company_year_id = session('current_company_year');
        $applyingLeave->save();

        return redirect('/applyingleave');
    }

    /**
     * Display the specified resource.
     *
     * @param ApplyingLeave $applyingleave
     * @return Response
     * @internal param int $id
     */
    public function show(ApplyingLeave $applyingLeave)
    {
        $title = trans('applyingleave.details');
        $action = 'show';

        return view('layouts.show', compact('applyingLeave', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ApplyingLeave $applyingleave
     * @return Response
     * @internal param int $id
     */
    public function edit(ApplyingLeave $applyingLeave)
    {
        $title = trans('applyingleave.edit');

        $this->generateParam();

        return view('layouts.edit', compact('title', 'applyingLeave'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|ApplyingLeaveRequest $request
     * @param ApplyingLeave $applyingleave
     * @return Response
     * @internal param int $id
     */
    public function update(ApplyingLeaveRequest $request, ApplyingLeave $applyingLeave)
    {
        $applyingLeave->update($request->all());

        return redirect('/applyingleave');
    }

    public function delete(ApplyingLeave $applyingLeave)
    {
        $title = trans('applyingleave.delete');

        return view('/applyingleave/delete', compact('applyingLeave', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ApplyingLeave $applyingleave
     * @return Response
     * @internal param int $id
     */
    public function destroy(ApplyingLeave $applyingLeave)
    {
        $applyingLeave->delete();

        return redirect('/applyingleave');
    }

    public function data()
    {
        if ($this->user->inRole('parent')) {
            $applyingLeave = $this->applyingLeaveRepository
                ->getAllForStudentAndSchoolYear(session('current_student_id'), session('current_company_year'))
                ->with('student.user')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'name' => isset($item->student->user) ? $item->student->user->full_name : '',
                        'date' => $item->date,
                    ];
                });

            return Datatables::make($applyingLeave)
                ->addColumn('actions', '<a href="{{ url(\'/applyingleave/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/applyingleave/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/applyingleave/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
                ->removeColumn('id')
                 ->rawColumns(['actions'])->make(false);
        } else {
            $teacherSection = $this->teacherSubjectRepository
                ->getAllForSchoolYearAndGroup(session('current_company_year'), session('current_student_group'))
                ->with('student_group')
                ->get()
                ->filter(function ($teacherSubject) {
                    return isset($teacherSubject->student_group);
                })
                ->map(function ($teacherSubject) {
                    return [
                        'section_id' => $teacherSubject->student_group->section_id,
                    ];
                })->pluck('section_id')->toArray();

            $applyingLeave = $this->applyingLeaveRepository->getAll()
                ->with('student')
                ->get()
                ->filter(function ($item) use ($teacherSection) {
                    foreach ($teacherSection as $section) {
                        if (isset($item->student->section_id) &&
                            $item->student->section_id == $section
                        ) {
                            return $item;
                        }
                    }
                })
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'student' => $item->student->user->full_name,
                        'date' => $item->date,
                    ];
                });

            return Datatables::make($applyingLeave)
                ->addColumn('actions', '<a href="{{ url(\'/applyingleave/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                ->removeColumn('id')
                 ->rawColumns(['actions'])->make();
        }
    }

    private function generateParam()
    {
        $student_id = session('current_student_id');
        view()->share('student_id', $student_id);
    }
}
