<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\DirectionRequest;
use App\Http\Requests\Secure\DeleteRequest;
use App\Http\Requests\Secure\TimetableRequest;
use App\Models\Direction;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\Timetable;
use App\Repositories\SectionRepository;
use App\Models\SchoolDirection;
use App\Models\StudentGroup;
use App\Repositories\DirectionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\TimetablePeriodRepository;
use App\Repositories\TimetableRepository;
use App\Repositories\TeacherSchoolRepository;
use App\Helpers\Settings;
use Guzzle\Http\Message\Response;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class DirectionController extends SecureController
{
    /**
     * @var DirectionRepository
     */
    private $directionRepository;
    private $sectionRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;
    /**
     * @var TeacherSchoolRepository
     */
    private $teacherSchoolRepository;
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;
    /**
     * @var TimetableRepository
     */
    private $timetableRepository;
    /**
     * @var TimetablePeriodRepository
     */
    private $timetablePeriodRepository;

    /**
     * DirectionController constructor.
     * @param DirectionRepository $directionRepository
     */
    public function __construct(
        SubjectRepository $subjectRepository,
        TeacherSchoolRepository $teacherSchoolRepository,
        TeacherSubjectRepository $teacherSubjectRepository,
        TimetableRepository $timetableRepository,
        DirectionRepository $directionRepository,
        TimetablePeriodRepository $timetablePeriodRepository,
        StudentRepository $studentRepository,
        SectionRepository $sectionRepository
    ) {

        parent::__construct();

        $this->directionRepository = $directionRepository;
        $this->studentRepository = $studentRepository;
        $this->sectionRepository = $sectionRepository;
        $this->subjectRepository = $subjectRepository;
        $this->teacherSchoolRepository = $teacherSchoolRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->timetableRepository = $timetableRepository;
        $this->timetablePeriodRepository = $timetablePeriodRepository;

        view()->share('type', 'direction');

        $columns = ['title','code','id_code', 'section', 'duration',  'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('direction.directions');
        $directions = $this->directionRepository->getAllForSchool(session('current_company'))
            ->get();
        return view('direction.index', compact('title', 'directions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('direction.new');
        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|DirectionRequest $request
     * @return Response
     */
    public function store(DirectionRequest $request)
    {
        $direction = new Direction($request->all());
        $direction->company_id = session('current_company');
        $direction->save();

        SchoolDirection::create(['company_id'=>session('current_company'), 'direction_id'=>$direction->id]);

        StudentGroup::create(['section_id'=>$direction->section_id,
            'direction_id'=>$direction->id,
            'title'=>$direction->title,
            'class'=>2]);



        return redirect('/direction');
    }

    /**
     * Display the specified resource.
     *
     * @param Direction $direction
     * @return Response
     * @internal param int $id
     */
    public function show(Direction $direction)
    {
        $title = trans('direction.details');
        $action = 'show';
        return view('layouts.show', compact('direction', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Direction $direction
     * @return Response
     * @internal param int $id
     */
    public function edit(Direction $direction)
    {
        $title = trans('direction.edit');
        $sections = $this->sectionRepository
            ->getAllForSchool(session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'direction', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|DirectionRequest $request
     * @param Direction $direction
     * @return Response
     * @internal param int $id
     */
    public function update(DirectionRequest $request, Direction $direction)
    {
        $direction->update($request->all());
        return redirect('/direction');
    }

    public function delete(Direction $direction)
    {
        $title = trans('direction.delete');
        return view('/direction/delete', compact('direction', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Direction $direction)
    {
        $direction->delete();
        return redirect('/direction');
    }

    public function data()
    {
        $directions = $this->directionRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($direction) {
                return [
                    'id' => $direction->id,
                    'title' => $direction->title,
                    'code' => $direction->code,
                    'id_code' => $direction->id_code,
                    'section' => $direction->section->title,
                    'duration' => $direction->duration,
                ];
            });

        return Datatables::make($directions)
            ->addColumn('actions', '<a href="{{ url(\'/direction/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/direction/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/direction/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }


    public function students(Section $section, Direction $direction)
    {
        $title = trans('studentgroup.students');
        $students = $this->studentRepository
            ->getAllForSchoolYearAndDirection(
                session('current_company_year'),
                session('current_company'),
                $direction->id
            )
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->full_name,
                ];
            })->pluck('name', 'id')->toArray();

        return view('direction.students', compact('direction', 'title', 'section', 'students'));
    }

    public function subjects(Section $section, Direction $direction)
    {
        $title = trans('direction.courses');
        $subjects = $this->subjectRepository
            ->getAllForDirection($direction->id)
            ->orderBy('order')
            ->get();

        $teachers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->full_name,
                ];
            })->pluck('name', 'id')->toArray();

        $teacher_subject = [];
        foreach ($subjects as $item) {
            $teacher_subject[$item->id] =
                $this->teacherSubjectRepository->getAllForSubjectAndDirection($item->id, $direction->id, session('current_company_semester'))
                    ->get()
                    ->pluck('teacher_id', 'teacher_id');
        }

        return view('direction.subjects', compact('direction', 'title', 'subjects', 'section', 'teachers', 'teacher_subject'));
    }

    public function addeditsubject(Subject $subject, Direction $direction, Request $request)
    {
        $this->teacherSubjectRepository->getAllForSubjectAndDirection($subject->id, $direction->id, session('current_company_semester'))
            ->delete();

        if (!empty($request['teachers_select'])) {
            foreach ($request['teachers_select'] as $teacher) {
                $teacherSubject = new TeacherSubject;
                $teacherSubject->subject_id = $subject->id;
                $teacherSubject->school_year_id = session('current_company_year');
                $teacherSubject->company_id = session('current_company');
                $teacherSubject->semester_id = session('current_company_semester');
                /*$teacherSubject->student_group_id = $direction->id;*/
                $teacherSubject->teacher_id = $teacher;
                $teacherSubject->save();
            }
        }
    }

    public function timetable(Section $section, Direction $direction)
    {
        $title = trans('studentgroup.timetable');
        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndDirection(session('current_company_year'), $direction->id)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) && isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => isset($teacherSubject->subject) ? $teacherSubject->subject->title : "",
                    'name' => isset($teacherSubject->teacher) ? $teacherSubject->teacher->full_name : "",
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);
        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();
        return view('direction.timetable', compact(
            'direction',
            'timetablePeriods',
            'title',
            'action',
            'section',
            'subject_list',
            'timetable'
        ));
    }

    public function addtimetable(Section $section, Direction $direction, TimetableRequest $request)
    {
        $timetable = new Timetable($request->all());
        $timetable->save();

        return $timetable->id;
    }

    public function deletetimetable(Section $section, Direction $direction, DeleteRequest $request)
    {
        $timetable = Timetable::find($request['id']);
        $timetable->delete();
    }

    public function getDuration(Request $request)
    {
        $direction = Direction::find($request['direction']);
        return isset($direction->duration) ? $direction->duration : 1;
    }


    public function print_timetable(Section $section, Direction $direction)
    {
        $title = trans('studentgroup.timetable');
        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndGroup(session('current_company_year'), $direction->id)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) && isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => isset($teacherSubject->subject) ? $teacherSubject->subject->title : "",
                    'name' => isset($teacherSubject->teacher) ? $teacherSubject->teacher->full_name : "",
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);
        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();

        $data = '<h1>' . $title . '</h1>
					<table style="border: double" class="table-bordered">
					<tbody>
					<tr>
						<th>#</th>
						<th width="14%">' . trans('teachergroup.monday') . '</th>
						<th width="14%">' . trans('teachergroup.tuesday') . '</th>
						<th width="14%">' . trans('teachergroup.wednesday') . '</th>
						<th width="14%">' . trans('teachergroup.thursday') . '</th>
						<th width="14%">' . trans('teachergroup.friday') . '</th>
                        <th width="14%">' . trans('teachergroup.saturday') . '</th>
                        <th width="14%">' . trans('teachergroup.sunday') . '</th>
					</tr>';
        if ($timetablePeriods->count() >0) {
            for ($i=0; $i<$timetablePeriods->count(); $i++) {
                $data .= '<tr>
            <td>' . $timetablePeriods[$i]['start_at'].' - '. $timetablePeriods[$i]['end_at'] . '</td>';
                for ($j = 1; $j < 8; $j ++) {
                    $data .= '<td>';
                    if ($timetablePeriods[$i]['title']=="") {
                        foreach ($timetable as $item) {
                            if ($item['week_day'] == $j && $item['hour'] == $i) {
                                $data .= '<div>
                            <span>' . $item['title'] . '</span>
                            <br>
                            <span>' . $item['name'] . '</span></div>';
                            }
                        }
                    } else {
                        $data .=$timetablePeriods[$i]['title'];
                    }
                    $data .= '</td>';
                }
                $data .= '</tr>';
            }
        } else {
            for ($i = 1; $i < 8; $i ++) {
                $data .= '<tr>
            <td>' . $i . '</td>';
                for ($j = 1; $j < 8; $j ++) {
                    $data .= '<td>';
                    foreach ($timetable as $item) {
                        if ($item['week_day'] == $j && $item['hour'] == $i) {
                            $data .= '<div>
                            <span>' . $item['title'] . '</span>
                            <br>
                            <span>' . $item['name'] . '</span></div>';
                        }
                    }
                    $data .= '</td>';
                }
                $data .= '</tr>';
            }
        }
        $data .= '</tbody>
				</table>';
        $pdf = PDF::loadView('report.timetable', compact('data'));
        return $pdf->stream();
    }

    public function generateCsvStudentsDirection(Direction $direction)
    {

        $students = $this->studentRepository->getAllForStudentDirection($direction->id)
            ->map(function ($student) {
                return [
                    'Order No.' => $student->order,
                    'First name' => $student->user->first_name,
                    'Last name' => $student->user->last_name,
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }
}
