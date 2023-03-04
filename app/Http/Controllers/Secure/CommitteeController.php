<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\CommitteeRequest;
use App\Models\Committee;
use App\Repositories\CommitteeRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;
use Snowfire;
use Yajra\DataTables\Facades\DataTables;

class CommitteeController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $committeeRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(CommitteeRepository $committeeRepository,
                                StudentRepository $studentRepository,
                                SectionRepository $sectionRepository)
    {
        parent::__construct();

        $this->committeeRepository = $committeeRepository;
        $this->sectionRepository = $sectionRepository;
        $this->studentRepository = $studentRepository;

        view()->share('type', 'committee');

        $columns = ['id', 'name', 'total', 'registered', 'confirmations', 'attended', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('committee.title');
        $committees = $this->committeeRepository->getAll()
            ->get();

        return view('committee.index', compact('title', 'committees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('committee.new');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(CommitteeRequest $request)
    {
        $level = new Committee($request->all());
        $level->company_id = session('current_company');
        $level->school_year_id = session('current_company_year');
        $level->save();

        return redirect('/committee');
    }

    /**
     * Display the specified resource.
     *
     * @param Level $level
     * @return Response
     */
    public function show(Committee $committee)
    {
        $title = trans('committee.details');
        $action = 'show';

        return view('layouts.show', compact('committee',
            'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Level $level
     * @return Response
     */
    public function edit(Committee $committee)
    {
        $title = trans('committee.edit');

        $sections = $this->sectionRepository
            ->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'committee', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Level $level
     * @return Response
     */
    public function update(CommitteeRequest $request, Committee $level)
    {
        $level->update($request->all());

        return redirect('/committee');
    }

    public function delete(Committee $committee)
    {
        $title = trans('committee.delete');

        return view('committee.delete', compact('committee', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Level $level
     * @return Response
     */
    public function destroy(Committee $level)
    {
        $level->delete();

        return redirect('/committee');
    }

    public function data()
    {
        $data = $this->committeeRepository->getAllForSchool(session('current_company'))
            ->get()
            ->map(function ($level) {
                return [
                    'id' => $level->id,
                    'name' => $level->title,
                    'total' => $level->students->count(),
                    'registered' => $level->Registrations->count(),
                    'confirmations' => $level->confirmations->count(),
                    'attended' => isset($level->attended) ? $level->attended->count() : '',
                ];
            });

        return Datatables::make($data)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.edit\', Sentinel::getUser()->permissions)))
										<a href="{{ url(\'/committee/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.show\', Sentinel::getUser()->permissions)))
                                    	<a href="{{ url(\'/committee/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     @endif
                                     <a href="{{ url(\'/committee/\' . $id . \'/students\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>
                                     @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'level.delete\', Sentinel::getUser()->permissions)))
                                     	<a href="{{ url(\'/committee/\' . $id . \'/invite\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-trash"></i> Send Invitation</a>
                                     @endif')
             ->rawColumns(['actions'])->make();
    }

    public function students(Committee $committee)
    {
        $title = $committee->title.' '.trans('section.students');
        $id = $committee->id;

        $columns = ['No', 'full_name', 'gender', 'email', 'section', 'mobile', 'level', 'actions'];
        view()->share('columns', $columns);

        $data = $this->studentRepository
            ->getAllForSchoolYearAndCommittee(session('current_company_year'), $committee->id)
            ->with('user')
            ->orderBy('students.id')
            ->get();

        return view('committee.students', compact('title', 'id', 'data'));
    }

    public function students_data(Committee $committee)
    {
        $students = $this->studentRepository
            ->getAllForSchoolYearAndCommittee(session('current_company_year'), $committee->id)
            ->with('user')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'No' => str_pad($student->student_no, 4, '0', STR_PAD_LEFT),
                    'full_name' => isset($student->user) ? $student->user->full_name : '',
                    'gender'  =>     ($student->user->gender == '1') ? trans('student.male') : trans('student.female'),
                    'email' => isset($student->user) ? $student->user->email : '',
                    'section' => isset($student->section) ? $student->section->title : '',
                    'mobile' => isset($student->user) ? $student->user->mobile : '',
                    'level'    => $student->level->name,
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

    public function invite(Committee $committee)
    {
        $students = $this->studentRepository
            ->getAllForSchoolYearAndCommittee(session('current_company_year'), $committee->id)
            ->with('user')
            ->get();

        foreach ($students as $student) {
            if (! is_null($student) && $student->user->email != '') {
                $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
                $beautymail->send('emails.jlcc', ['thisUser' => $student], function ($message) use ($student) {
                    $email = $student->user->email;
                    $message
                        ->from('jlc@jospongroup.com', 'JLC 2019')
                        ->to($email, $student->user->full_name)
                        ->subject('Jospong Leadership Conference 2019');
                });

                /* $smsMessage                 = new SmsMessage();
                 $smsMessage->text           = $request->text;
                 $smsMessage->number         = $theUser->mobile;
                 $smsMessage->user_id        = $user_id;
                 $smsMessage->user_id_sender = $this->user->id;
                 $smsMessage->company_id      = session( 'current_company' );
                 $smsMessage->save();*/
            }
        }

        return redirect('/committee')->with('status', ''.$students->count().' Participants Invited Successfully!');
    }
}
