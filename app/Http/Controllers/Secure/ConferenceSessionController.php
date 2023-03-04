<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\SectionRequest;
use App\Models\Center;
use App\Models\ConferenceDay;
use App\Models\ConferenceSession;
use App\Models\Section;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Repositories\ConferenceDayRepository;
use App\Repositories\ConferenceSessionRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SessionAttendanceRepository;
use App\Repositories\StudentRepository;
use DB;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Sentinel;

class ConferenceSessionController extends SecureController
{
    /**
     * @var SessionAttendanceRepository
     */
    private $sessionAttendanceRepository;

    /**
     * @var SectionRepository
     */
    private $conferenceDayRepository;

    /**
     * @var SectionRepository
     */
    private $conferenceSessionRepository;

    /**
     * @var SectionRepository
     */
    private $studentRepository;

    private $sectionRepository;

    /**
     * SectionController constructor.
     * @param SectionRepository $sectionRepository
     * @param StudentRepository $studentRepository
     */
    public function __construct(SectionRepository $sectionRepository,
                                SessionAttendanceRepository $sessionAttendanceRepository,
                                ConferenceSessionRepository $conferenceSessionRepository,
                                ConferenceDayRepository $conferenceDayRepository,
                                StudentRepository $studentRepository)
    {
        parent::__construct();

        $this->sectionRepository = $sectionRepository;
        $this->sessionAttendanceRepository = $sessionAttendanceRepository;
        $this->conferenceSessionRepository = $conferenceSessionRepository;
        $this->conferenceDayRepository = $conferenceDayRepository;
        $this->studentRepository = $studentRepository;

        /*  $this->middleware('authorized:section.show', ['only' => ['index', 'data']]);
          $this->middleware('authorized:section.create', ['only' => ['create', 'store']]);
          $this->middleware('authorized:section.edit', ['only' => ['update', 'edit']]);
          $this->middleware('authorized:section.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'conference_session');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('conference.sessions');

        $data = $this->conferenceSessionRepository->getAllForSchoolYear(session('current_company_year'))
            ->get();

        return view('conference_session.index', compact('title', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('section.new');
        $days = $this->conferenceDayRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($day) {
                return [
                    'id' => $day->id,
                    'name' => $day->title,
                ];
            })
            ->pluck('name', 'id')
            ->prepend(trans('conference.select_day'), '')
            ->toArray();

        return view('layouts.create', compact('title', 'days'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SectionRequest $request)
    {
        $section = new ConferenceSession($request->all());
        $section->company_id = session('current_company');
        $section->school_year_id = session('current_company_year');
        $section->save();

        return redirect('/conference_session');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(ConferenceSession $conferenceSession)
    {
        $title = $conferenceSession->day->title.' '.$conferenceSession->title;
        $action = 'show';

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get();
        $registrations = $this->sessionAttendanceRepository->getAllStudentsForSchoolSession(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->get();

        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->get();

        $attended = $this->studentRepository->getAllForSchoolYearAttended(session('current_company_year'), session('current_company'))
            ->get();

        return view('layouts.show', compact('conferenceSession', 'title', 'sections', 'registrations', 'students', 'attended', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(ConferenceSession $conferenceSession)
    {
        $title = trans('section.edit');
        $days = $this->conferenceDayRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($day) {
                return [
                    'id' => $day->id,
                    'name' => $day->title,
                ];
            })
            ->pluck('name', 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'conferenceSession', 'days'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(SectionRequest $request, ConferenceSession $conferenceSession)
    {
        $conferenceSession->update($request->all());

        return redirect('/conference_session');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(ConferenceSession $conferenceSession)
    {
        $title = trans('section.delete');

        return view('/conference_session/delete', compact('conferenceSession', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(ConferenceSession $conferenceSession)
    {
        $conferenceSession->delete();

        return redirect('/conference_session');
    }

    public function data()
    {
        $sections = $this->conferenceSessionRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'active' => $section->active,
                    'conference_day' => isset($section->day) ? $section->day->title : '',
                    'total' => Student::count(),
                    'attended' => isset($section->students) ? $section->students->count() : '',
                    'percentage' => round($section->students->count() / @Student::where('attended', 1)->count() * 100, 2),

                ];
            });

        return Datatables::make($sections)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'section.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/conference_session/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <!--<a href="{{ url(\'/conference_session/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>-->
                                    <!--<a href="{{ url(\'/section/\' . $id . \'/generate_csv\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-file-excel-o"></i>  {{ trans("section.generate_csv") }}</a>-->

                                     <a href="{{ url(\'/conference_session/\' . $id . \'/students\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>

                                    <!-- @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'section.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/conference_session/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif-->')

             ->rawColumns(['actions'])->make();
    }

    public function generateCsvStudentsSection(ConferenceSession $conferenceSession)
    {
        $session_name = $conferenceSession->title;

        $day_name = $conferenceSession->day->title;

        $data = SessionAttendance::Where('conference_session_id', $conferenceSession->id)
                                    ->where('school_year_id', session('current_company_year'))
                                    ->whereIn('student_id', $students);

        $students = $this->sessionAttendanceRepository->getAllStudentsForSchoolSession(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'ID.' => $student->student_no,
                    'Name' => $student->full_name,
                    'Gender' => ($student->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => $student->level,
                    'Subsidiary' => $student->section,
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    public function generateCsvStudentsSection2(Center $center, ConferenceSession $conferenceSession)
    {
        $ids = $this->employeeRepository->getAllForSchoolYearAndSchool(session('current_company_year'), $this->currentEmployee->center_id)->pluck('id');

        /*$ids = $this->studentRepository->getAllForSchoolYearAndSectionE(
            session('current_company_year'), session('current_company'), $center->id)
            ->pluck('id');*/

        /*$students =  SessionAttendance::Where('conference_session_id', $conferenceSession->id)
            ->whereIn( 'student_id', $ids )
            ->get() */

        $students = SessionAttendance::Where('conference_session_id', $conferenceSession->id)
            ->whereIn('student_id', $ids)
            ->get()

            ->map(function ($student) {
                return [
                    'ID.' => $student->student->sID,
                    'Name' => $student->student->user->full_name,
                    'Gender' => ($student->student->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => $student->student->position->title,
                    'Subsidiary' => $student->student->department->title,
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    public function generateCsvStudentsSection3(Section $section, ConferenceSession $conferenceSession)
    {
        $ids = $this->studentRepository->getAllForSchoolYearAndSectionE(
            session('current_company_year'), session('current_company'), $section->id)
            ->pluck('id');

        /*$students =  $this->studentRepository->getAllForSchoolYearAndSectionE(
            session('current_company_year'), session('current_company'), $section->id)
            ->get()*/
        $students = SessionAttendance::Where('conference_session_id', $conferenceSession->id)
            ->whereNotIn('student_id', $ids)
            ->get()
            ->map(function ($student) {
                return [
                    'ID.' => @$student->student->student_no,
                    'Name' => @$student->student->user->full_name,
                    'Gender' => (@$student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => @$student->student->level->name,
                    'Subsidiary' => @$student->student->section->title,
                    /*'Committee' => isset($student->committee) ? 'PRESENT' : "ABSENT",*/
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    public function CsvStudentsSectionInvited(Section $section)
    {
        /*
                $ids = $this->studentRepository->getAllForSchoolYearAndSectionE(
                    session('current_company_year'), session('current_company'), $section->id)
                    ->pluck('id');*/

        $students = $this->studentRepository->getAllForSchoolYearAndSectionE(
            session('current_company_year'), session('current_company'), $section->id)
            ->get()
            ->map(function ($student) {
                return [
                    'ID.' => @$student->student_no,
                    'Name' => @$student->user->full_name,
                    'Gender' => (@$student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => @$student->level->name,
                    'Subsidiary' => @$student->section->title,
                    /*'Committee' => isset($student->committee) ? 'PRESENT' : "ABSENT",*/
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    public function CsvStudentsSectionAttended(Section $section)
    {
        /*
                $ids = $this->studentRepository->getAllForSchoolYearAndSectionE(
                    session('current_company_year'), session('current_company'), $section->id)
                    ->pluck('id');*/

        $students = $this->studentRepository->getAllForSchoolYearAndSectionAttended(
            session('current_company_year'), session('current_company'), $section->id)
            ->get()
            ->map(function ($student) {
                return [
                    'ID.' => @$student->student_no,
                    'Name' => @$student->user->full_name,
                    'Gender' => (@$student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => @$student->level->name,
                    'Subsidiary' => @$student->section->title,
                    'Arrival Date' => @$student->active[0]->attended_date,
                    /*'Committee' => isset($student->committee) ? 'PRESENT' : "ABSENT",*/
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    /*NOT WORKING*/

    public function generateCsvStudentsSectionAbsent(ConferenceSession $conferenceSession)
    {
        $students = $this->studentRepository->getAllForSessionAbsent(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'ID.' => $student->student_no,
                    'Name' => $student->user->full_name,
                    'Gender' => ($student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'Level' => @$student->level->title,
                    'Subsidiary' => @$student->section->title,
                ];
            })->toArray();
        Excel::create(trans('section.students'), function ($excel) use ($students) {
            $excel->sheet(trans('section.students'), function ($sheet) use ($students) {
                $sheet->fromArray($students, null, 'A1', true);
            });
        })->export('csv');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function students(ConferenceSession $conferenceSession)
    {
        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get();
        $registrations = $this->sessionAttendanceRepository->getAllStudentsForSchoolSession(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->get();

        $maleStudents = $this->sessionAttendanceRepository->getAllStudentsForSchoolSessionMale(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->get();

        $femaleStudents = $this->sessionAttendanceRepository->getAllStudentsForSchoolSessionFemale(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->get();

        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
            ->get();

        $attended = $this->studentRepository->getAllForSchoolYearAttended(session('current_company_year'), session('current_company'))
            ->get();

        $session_name = $conferenceSession->title;

        $day_name = $conferenceSession->day->title;

        $title = trans('section.students');
        $id = $conferenceSession->id;

        $data = $this->sessionAttendanceRepository->getAllStudentsForSchoolSession(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->with('user')
            ->get();

        return view('conference_session.students', compact('title', 'conferenceSession', 'sections', 'registrations', 'students', 'maleStudents', 'femaleStudents', 'attended', 'id', 'session_name', 'day_name', 'data'));
    }

    public function students_data(ConferenceSession $conferenceSession)
    {
        $students = $this->sessionAttendanceRepository->getAllStudentsForSchoolSession(session('current_company'), session('current_company_year'), $conferenceSession->id)
            ->with('user')
            ->get()
            ->map(function ($student) {
                return [
                    'id'            => $student->id,
                    'student_no'    => str_pad($student->student_no, 4, '0', STR_PAD_LEFT),
                    'full_name'     => $student->full_name,
                    'gender'        => ($student->gender == '1') ? trans('student.male') : trans('student.female'),
                    'section'       => $student->section,
                    'level'         => $student->level,
                    'date'          => $student->created_at->toDateTimeString(),
                ];
            });

        /* return Datatables::make( $students)
             ->addColumn('actions', '<!--<a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                             <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>-->
                                     <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                             <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <!--<a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                             <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->')
             ->removeColumn('id')
              ->rawColumns( [ 'actions' ] )->make();*/
    }

    public function findConferenceDaySessions(Request $request)
    {
        $students = $this->conferenceSessionRepository->getAllForDay(session('current_company'), session('current_company_year'), $request->conference_day_id)
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item) ? $item->day->title.' '.$item->title : '',
                ];
            })->pluck('name', 'id')
            ->prepend(trans('student.select_conference_session'), 0)
            ->toArray();

        return $students;
    }
}
