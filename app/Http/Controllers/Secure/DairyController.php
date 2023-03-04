<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\DairyRequest;
use App\Models\Diary;
use App\Repositories\DiaryRepository;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\TimetableRepository;
use Maatwebsite\Excel\Facades\Excel;

class DairyController extends SecureController
{
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * @var DiaryRepository
     */
    private $diaryRepository;

    /**
     * @var TimetableRepository
     */
    private $timetableRepository;

    /**
     * DairyController constructor.
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param DiaryRepository $diaryRepository
     * @param TimetableRepository $timetableRepository
     */
    public function __construct(
        TeacherSubjectRepository $teacherSubjectRepository,
        DiaryRepository $diaryRepository,
        TimetableRepository $timetableRepository
    ) {
        parent::__construct();

        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->diaryRepository = $diaryRepository;
        $this->timetableRepository = $timetableRepository;

        $this->middleware('authorized:diary.show', ['only' => ['index', 'data']]);

        view()->share('type', 'diary');

        $columns = ['title', 'subject', 'hour', 'date', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('diary.diary');

        return view('diary.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('diary.new');
        $this->generateParams();

        return view('layouts.create', compact('title', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(DairyRequest $request)
    {
        $diary = new Diary($request->all());
        $diary->user_id = $this->user->id;
        $diary->company_year_id = session('current_company_year');
        $diary->company_id = session('current_company');
        $diary->save();

        return redirect('/diary');
    }

    /**
     * Display the specified resource.
     *
     * @param  Diary $diary
     * @return Response
     */
    public function show(Diary $diary)
    {
        $title = trans('diary.details');
        $action = 'show';

        return view('layouts.show', compact('diary', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Diary $diary)
    {
        $title = trans('diary.edit');
        $this->generateParams();

        $date = date_format(date_create_from_format(Settings::get('date_format'), $diary->date), 'd-m-Y');

        $hour_list = $this->timetableRepository->getAll()
            ->with('teacher_subject')
            ->get()
            ->filter(function ($timetable) use ($date) {
                return $timetable->teacher_subject->teacher_id == $this->user->id &&
                    $timetable->week_day == date('N', strtotime($date)) &&
                    $timetable->teacher_subject->student_group_id == session('current_student_group');
            })
            ->map(function ($timetable) {
                return [
                    'id' => $timetable->hour,
                    'hour' => $timetable->hour,
                ];
            })->pluck('hour', 'id')->toArray();

        return view('layouts.edit', compact('title', 'diary', 'subjects', 'hour_list'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(DairyRequest $request, Diary $diary)
    {
        $diary->update($request->all());

        return redirect('/diary');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Diary $diary)
    {
        $title = trans('diary.delete');

        return view('/diary/delete', compact('diary', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Diary $diary)
    {
        $diary->delete();

        return redirect('/diary');
    }

    public function data()
    {
        if ($this->user->inRole('teacher')) {
            $diaries = $this->diaryRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                ->with('subject')->get()
                ->filter(function ($diary) {
                    return $diary->user_id == $this->user->id;
                })
                ->map(function ($diary) {
                    return [
                        'id' => $diary->id,
                        'title' => $diary->title,
                        'subject' => isset($diary->subject) ? $diary->subject->title : '',
                        'hour' => $diary->hour,
                        'date' => $diary->date,
                    ];
                });

            return Datatables::make($diaries)
                ->addColumn('actions', '<a href="{{ url(\'/diary/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/diary/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/diary/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
                ->removeColumn('id')
                 ->rawColumns(['actions'])->make();
        } elseif ($this->user->inRole('student')) {
            $diaries = $this->diaryRepository->getAllForSchoolYearAndStudentUserId(session('current_company_year'), $this->user->id)
                ->with('subject')
                ->get()
                ->map(function ($diary) {
                    return [
                        'id' => $diary->id,
                        'title' => $diary->title,
                        'subject' => isset($diary->subject) ? $diary->subject->title : '',
                        'hour' => $diary->hour,
                        'date' => $diary->date,
                    ];
                });
        } elseif ($this->user->inRole('parent')) {
            $user = session('current_student_user_id');

            $diaries = $this->diaryRepository->getAllForSchoolYearAndStudentUserId(session('current_company_year'), $user)
                ->with('subject')
                ->get()
                ->map(function ($diary) {
                    return [
                        'id' => $diary->id,
                        'title' => $diary->title,
                        'subject' => isset($diary->subject) ? $diary->subject->title : '',
                        'hour' => $diary->hour,
                        'date' => $diary->date,
                    ];
                });
        } else {
            $diaries = $this->diaryRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                ->with('subject')->get()
                ->map(function ($diary) {
                    return [
                        'id' => $diary->id,
                        'title' => $diary->title,
                        'subject' => isset($diary->subject) ? $diary->subject->title : '',
                        'hour' => $diary->hour,
                        'date' => $diary->date,
                    ];
                });
        }

        return Datatables::make($diaries->toBase()->unique())
            ->addColumn('actions', '<a href="{{ url(\'/diary/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    /**
     * @return mixed
     */
    private function generateParams()
    {
        $subjects = $this->teacherSubjectRepository
                ->getAllForSchoolYearAndGroupAndTeacher(session('current_company_year'), session('current_student_group'), $this->user->id)
                ->with('subject')
                ->get()
                ->filter(function ($subject) {
                    return isset($subject->subject->title);
                })
                ->map(function ($subject) {
                    return [
                        'id' => $subject->subject_id,
                        'title' => $subject->subject->title,
                    ];
                })->pluck('title', 'id')->prepend(trans('mark.select_subject'), 0)->toArray();

        view()->share('subjects', $subjects);
    }

    public function export()
    {
        if ($this->user->inRole('teacher')) {
            $diaries = $this->diaryRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                                             ->with('subject')->get()
                                             ->filter(function ($diary) {
                                                 return $diary->user_id == $this->user->id;
                                             })
                                             ->map(function ($diary) {
                                                 return [
                                                     'Title' => $diary->title,
                                                     'Subject' => isset($diary->subject) ? $diary->subject->title : '',
                                                     'Hour' => $diary->hour,
                                                     'Date' => $diary->date,
                                                     'Description' => $diary->description,
                                                 ];
                                             })->toArray();
        } elseif ($this->user->inRole('student')) {
            $diaries = $this->diaryRepository->getAllForSchoolYearAndStudentUserId(session('current_company_year'), $this->user->id)
                                             ->with('subject')
                                             ->get()
                                             ->map(function ($diary) {
                                                 return [
                                                     'Title' => $diary->title,
                                                     'Subject' => isset($diary->subject) ? $diary->subject->title : '',
                                                     'Hour' => $diary->hour,
                                                     'Date' => $diary->date,
                                                     'Description' => $diary->description,
                                                 ];
                                             })->toArray();
        } elseif ($this->user->inRole('parent')) {
            $user = session('current_student_user_id');

            $diaries = $this->diaryRepository->getAllForSchoolYearAndStudentUserId(session('current_company_year'), $user)
                                             ->with('subject')
                                             ->get()
                                             ->map(function ($diary) {
                                                 return [
                                                     'Title' => $diary->title,
                                                     'Subject' => isset($diary->subject) ? $diary->subject->title : '',
                                                     'Hour' => $diary->hour,
                                                     'Date' => $diary->date,
                                                     'Description' => $diary->description,
                                                 ];
                                             })->toArray();
        } else {
            $diaries = $this->diaryRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                                             ->with('subject')->get()
                                             ->map(function ($diary) {
                                                 return [
                                                     'Title' => $diary->title,
                                                     'Subject' => isset($diary->subject) ? $diary->subject->title : '',
                                                     'Hour' => $diary->hour,
                                                     'Date' => $diary->date,
                                                     'Description' => $diary->description,
                                                 ];
                                             })->toArray();
        }

        Excel::create(trans('diary.diary'), function ($excel) use ($diaries) {
            $excel->sheet(trans('diary.diary'), function ($sheet) use ($diaries) {
                $sheet->fromArray($diaries, null, 'A1', true);
            });
        })->export('csv');
    }
}
