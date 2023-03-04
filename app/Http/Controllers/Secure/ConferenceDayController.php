<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\ConferenceDayRequest;
use App\Models\Center;
use App\Models\ConferenceDay;
use App\Models\Option;
use App\Models\Section;
use App\Models\StudentRegistrationCode;
use App\Repositories\ConferenceDayRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use DB;
use Efriandika\LaravelSettings\Facades\Settings;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Sentinel;

class ConferenceDayController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $conferenceDayRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * SectionController constructor.
     * @param SectionRepository $sectionRepository
     * @param TeacherSchoolRepository $teacherSchoolRepository
     * @param StudentRepository $studentRepository
     * @param StudentGroupRepository $studentGroupRepository
     * @param SubjectRepository $subjectRepository
     */
    public function __construct(SectionRepository $sectionRepository,
                                ConferenceDayRepository $conferenceDayRepository,
                                StudentRepository $studentRepository)
    {
        parent::__construct();

        $this->sectionRepository = $sectionRepository;
        $this->conferenceDayRepository = $conferenceDayRepository;
        $this->studentRepository = $studentRepository;

        /* $this->middleware('authorized:section.show', ['only' => ['index', 'data']]);
         $this->middleware('authorized:section.create', ['only' => ['create', 'store']]);
         $this->middleware('authorized:section.edit', ['only' => ['update', 'edit']]);
         $this->middleware('authorized:section.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'conference_day');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('conference.days');
        $data = $this->conferenceDayRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get();

        return view('conference_day.index', compact('title', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('conference.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(ConferenceDayRequest $request)
    {
        $section = new ConferenceDay($request->all());
        $section->company_id = session('current_company');
        $section->school_year_id = session('current_company_year');
        $section->save();

        return redirect('/conference_day');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(ConferenceDay $conferenceDay)
    {
        $title = trans('section.details');
        $action = 'show';

        return view('layouts.show', compact('conferenceDay', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(ConferenceDay $conferenceDay)
    {
        $title = trans('conference.edit');

        return view('layouts.edit', compact('title', 'conferenceDay'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(ConferenceDayRequest $request, ConferenceDay $conferenceDay)
    {
        $conferenceDay->update($request->all());

        return redirect('/conference_day');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(ConferenceDay $conferenceDay)
    {
        $title = trans('section.delete');

        return view('/conference_day/delete', compact('conferenceDay', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(ConferenceDay $conferenceDay)
    {
        $conferenceDay->delete();

        return redirect('/conference_day');
    }

    private function generateUniqueCode()
    {
        $code = Str::random(8) ;
        $student_registration_code = StudentRegistrationCode::where('code', $code)->first();
        if (is_null($student_registration_code)) {
            return $code;
        } else {
            $this->generateUniqueCode();
        }
    }

    public function data()
    {
        $sections = $this->conferenceDayRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'day_name' => $section->day_name,
                    /*'sessions' => $section->students->count(),*/
                    /*'registrations' => $section->Registrations->count(),
                    'attended' => isset($section->attended) ? $section->attended->count() : "",*/
                ];
            });

        return Datatables::make($sections)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'section.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/conference_day/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/conference_day/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <!--<a href="{{ url(\'/section/\' . $id . \'/generate_csv\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-file-excel-o"></i>  {{ trans("section.generate_csv") }}</a>-->
                                    <!--@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.show\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/conference_day/\' . $id . \'/groups\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-list-ul"></i> {{ trans("section.groups") }}</a>
                                     @endif-->
                                     <!--<a href="{{ url(\'/conference_day/\' . $id . \'/students\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>-->
                                     <!--<a href="{{ url(\'/section/\' . $id . \'/make_invoices\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-money"></i> {{ trans("section.make_invoices") }}</a>-->
                                     <!--@if(Settings::get(\'generate_registration_code\')==true && Settings::get(\'self_registration_role\')==\'student\')
                                        <a target="_blank" href="{{ url(\'/section/\' . $id . \'/generate_code\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-list-alt"></i> {{ trans("section.generate_code") }}</a>
                                     @endif-->
                                     <!--@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'section.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/conference_day/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif-->')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function generateCsvStudentsSection(Center $center)
    {
        $students = $this->studentRepository->getAllForSchoolYearAndSection(session('current_company_year'), $section->id)
            ->orderBy('order')
            ->with('user')
            ->get()
            ->filter(function ($student) {
                return isset($student->user);
            })
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

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function groups(Section $section)
    {
        $title = trans('section.groups');
        $id = $section->id;

        $columns = ['title', 'direction', 'class', 'actions'];
        view()->share('columns', $columns);

        return view('section.groups', compact('title', 'id'));
    }

    public function groups_data(Section $section)
    {
        $studentGroups = $this->studentGroupRepository->getAllForSection($section->id)
            ->with('direction')
            ->get()
            ->map(function ($studentGroup) {
                return [
                    'id' => $studentGroup->id,
                    'title' => $studentGroup->title,
                    'direction' => isset($studentGroup->direction) ? $studentGroup->direction->title : '',
                    'class' => $studentGroup->class,
                ];
            });

        return Datatables::make($studentGroups)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/studentgroup/'.$section->id.'/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/studentgroup/'.$section->id.'/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/studentgroup/\' . $id . \'/generate_csv\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-file-excel-o"></i>  {{ trans("section.generate_csv") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/studentgroup/'.$section->id.'/\' . $id . \'/students\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>
                                     <a href="{{ url(\'/studentgroup/'.$section->id.'/\' . $id . \'/subjects\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-list-ol"></i> {{ trans("section.subjects") }}</a>
                                     <a href="{{ url(\'/studentgroup/'.$section->id.'/\' . $id . \'/timetable\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-calendar"></i>  {{ trans("studentgroup.timetable") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/studentgroup/'.$section->id.'/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function generateCsvStudentsGroup(StudentGroup $studentGroup)
    {
        $students = $this->studentRepository->getAllForStudentGroup($studentGroup->id)
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

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function students(Section $section)
    {
        $title = trans('section.students');
        $id = $section->id;

        $columns = ['full_name', 'email', 'mobile', 'actions'];
        view()->share('columns', $columns);

        return view('section.students', compact('title', 'id'));
    }

    public function students_data(Section $section)
    {
        $students = $this->studentRepository
            ->getAllForSchoolYearAndSection3(session('current_company_year'), $section->id)
            ->with('user')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'full_name' => isset($student->user) ? $student->user->full_name : '',
                    'email' => isset($student->user) ? $student->user->email : '',
                    'mobile' => isset($student->user) ? $student->user->mobile : '',
                ];
            });

        return Datatables::make($students)
            ->addColumn('actions', '<a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function get_groups(Section $section)
    {
        return $this->studentGroupRepository->getAllForSection($section->id)
                                                      ->get()
                                                      ->map(function ($studentGroup) {
                                                          return [
                                                              'id' => $studentGroup->id,
                                                              'title' => $studentGroup->title,
                                                          ];
                                                      });
    }
}
