<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Http\Requests\Secure\SectionRequest;
use App\Models\Company;
use App\Models\Department;
use App\Models\DepartmentHead;
use App\Models\Invoice;
use App\Models\Kpi;
use App\Models\PerspectiveWeight;
use App\Models\Position;
use App\Repositories\DirectionRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherSchoolRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Sentinel;

class SectionController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var DirectionRepository
     */
    private $directionRepository;

    /**
     * @var SectionRepository
     */
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
     * SectionController constructor.
     * @param EmployeeRepository $employeeRepository
     * @param SectionRepository $sectionRepository
     * @param TeacherSchoolRepository $teacherSchoolRepository
     * @param StudentRepository $studentRepository
     * @param SubjectRepository $subjectRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        DirectionRepository $directionRepository,
        SectionRepository $sectionRepository,
        TeacherSchoolRepository $teacherSchoolRepository,
        StudentRepository $studentRepository,
        SubjectRepository $subjectRepository
    ) {
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->directionRepository = $directionRepository;
        $this->sectionRepository = $sectionRepository;
        $this->teacherSchoolRepository = $teacherSchoolRepository;
        $this->studentRepository = $studentRepository;
        $this->subjectRepository = $subjectRepository;

        $this->middleware('authorized:section.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:section.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:section.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:section.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'section');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (! Sentinel::hasAccess('department')) {
            Flash::warning('Permission Denied');

            return view('flash-message');
        }
        $title = trans('section.section');
        $sections = $this->sectionRepository->getAllForSchool(session('current_company'))
            ->with('employees')
            ->get();

        return view('section.index', compact('title', 'sections'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('section.new');

        return view('section.modalForm', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(SectionRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $section = new Department($request->except('section_teacher_id'));
                $section->company_id = session('current_company');
                $section->save();

            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Department Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Department $section)
    {
        $title = trans('section.details');
        $action = 'show';

        return view('layouts.show', compact('section', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(Department $section)
    {
        $title = trans('section.edit');

        return view('section.modalForm', compact('title', 'section'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(SectionRequest $request, Department $section)
    {
        try {
            DB::transaction(function () use ($request, $section) {
                $section->update($request->except('section_teacher_id'));

                /*DepartmentHead::updateOrCreate(
                    ['section_id' => $section->id, 'company_id' => session('current_company')],
                    ['employee_id' => $request->section_teacher_id]
                );*/
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Department Updated Successfully</div>');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Department $section)
    {
        $title = trans('section.delete');

        return view('section.delete', compact('section', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Department $section)
    {
        if ($section->employees->count() > 0) {
            $msg = $section->title.' Department has employees and cannot be deleted';

            return Response('<div class="alert alert-danger">'.$msg.'</div>');
        } else {
            $section->delete();

            return 'Deleted';
        }
    }

    public function make_invoices(Department $section)
    {
        $student_users = $this->subjectRepository->getAllStudentsSubjectAndDirection()
            ->where('students.section_id', $section->id)
            ->where('students.company_year_id', session('current_company_year'))
            ->where('students.company_id', session('current_company'))
            ->where('subjects.fee', '>', 0)
            ->distinct('students.user_id')->select('students.user_id', 'subjects.title as subject', 'subjects.fee')
            ->get();
        foreach ($student_users as $user) {
            $invoice = new Invoice();
            $invoice->title = trans('subject.fee');
            $invoice->description = trans('subject.subject_fee').$user->subject;
            $invoice->amount = $user->fee;
            $invoice->user_id = $user->user_id;
            $invoice->save();
        }

        return redirect('/section');
    }

    public function generate_code(Department $section)
    {
        $quantity = $section->quantity;
        if ($quantity > 0 && ($quantity - $section->total->count()) > 0) {
            $count = $quantity - $section->total->count();
            StudentRegistrationCode::where('company_year_id', $section->company_year_id)
                ->where('company_id', $section->company_id)
                ->where('section_id', $section->id)->delete();
            $code_lists = [];
            for ($i = 0; $i < $count; $i++) {
                $code = $this->generateUniqueCode();
                $code_lists[] = $code;
                StudentRegistrationCode::create(['company_year_id' => $section->company_year_id,
                    'company_id' => $section->company_id,
                    'section_id' => $section->id,
                    'code' => $code, ]);
            }

            $content = '<table>';
            foreach (array_chunk($code_lists, 4) as $codeRow) {
                $content .= '<tr>';
                foreach ($codeRow as $code) {
                    $content .= '<td>'.$code.'</td>';
                }
                $content .= '</tr>';
            }
            $content .= '</table>';

            $pdf = PDF::loadView('report.code_lists', compact('content'));

            return $pdf->stream();
        }

        return redirect('/section');
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
        $sections = $this->sectionRepository->getAllForSchool(session('current_company'))
            ->with('teacher')
            ->get()
            ->map(function ($section) {
                return [
                    'id' => $section->id,
                    'title' => $section->title,
                    'id_code' => $section->id_code,
                    'total' => $section->total->count(),
                    'full_name' => isset($section->teacher) ? $section->teacher->full_name : '',
                ];
            });

        return Datatables::make($sections)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'section.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/section/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/section/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <!--<a href="{{ url(\'/section/\' . $id . \'/generate_csv\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-file-excel-o"></i>  {{ trans("section.generate_csv") }}</a>-->
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.show\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/section/\' . $id . \'/groups\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-list-ul"></i> {{ trans("section.programs") }}</a>
                                     @endif
                                     <a href="{{ url(\'/section/\' . $id . \'/students\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>
                                     <!--<a href="{{ url(\'/section/\' . $id . \'/make_invoices\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-money"></i> {{ trans("section.make_invoices") }}</a>-->
                                     <!--@if(Settings::get(\'generate_registration_code\')==true && Settings::get(\'self_registration_role\')==\'student\')
                                        <a target="_blank" href="{{ url(\'/section/\' . $id . \'/generate_code\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-list-alt"></i> {{ trans("section.generate_code") }}</a>
                                     @endif-->
                                     @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'section.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/section/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function generateCsvStudentsSection(Department $section)
    {
        $students = $this->studentRepository
            ->getAllForSchoolYearAndSection(
                session('current_company_year'),
                session('current_company'),
                $section->id
            )
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
    public function groups(Department $section)
    {
        $title = trans('section.groups');
        $id = $section->id;

        return view('section.groups', compact('title', 'id'));
    }

    public function groups_data(Department $section)
    {
        $studentGroups = $this->directionRepository->getAllForSection($section->id)
            ->with('total')
            ->get()
            ->map(function ($studentGroup) {
                return [
                    'id' => @$studentGroup->id,
                    'title' => @$studentGroup->title,
                    /*'direction' => isset($studentGroup->direction) ? @$studentGroup->direction->title : "",*/
                    /*'class' => @$studentGroup->class*/
                ];
            });

        return Datatables::make($studentGroups)
            ->addColumn('actions', '<!--
                                    <a href="{{ url(\'/direction/\' . $id . \'/generate_csv\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-file-excel-o"></i>  {{ trans("section.generate_csv") }}</a>-->
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.edit\', Sentinel::getUser()->permissions)))
                                    <!--<a href="{{ url(\'/direction/'.$section->id.'/\' . $id . \'/students\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>-->
                                     <a href="{{ url(\'/direction/'.$section->id.'/\' . $id . \'/subjects\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-list-ol"></i> {{ trans("section.courses") }}</a>
                                     <a href="{{ url(\'/direction/'.$section->id.'/\' . $id . \'/timetable\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-calendar"></i>  {{ trans("studentgroup.timetable") }}</a>
                                    @endif
                                    ')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    /*public function groups(Department $section)
    {
        $title = trans('section.groups');
        $id = $section->id;
        return view('section.groups', compact('title', 'id'));
    }

    public function groups_data(Department $section)
    {
        $studentGroups = $this->studentGroupRepository->getAllForSection($section->id)
            ->with('direction')
            ->get()
            ->map(function ($studentGroup) {
                return [
                    'id' => $studentGroup->id,
                    'title' => $studentGroup->title,
                    'direction' => isset($studentGroup->direction) ? $studentGroup->direction->title : "",
                    'class' => $studentGroup->class
                ];
            });


        return Datatables::make($studentGroups)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/studentgroup/' . $section->id . '/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/studentgroup/' . $section->id . '/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/studentgroup/\' . $id . \'/generate_csv\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-file-excel-o"></i>  {{ trans("section.generate_csv") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/studentgroup/' . $section->id . '/\' . $id . \'/students\' ) }}" class="btn btn-success btn-sm">
                                            <i class="fa fa-users"></i> {{ trans("section.students") }}</a>
                                     <a href="{{ url(\'/studentgroup/' . $section->id . '/\' . $id . \'/subjects\' ) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-list-ol"></i> {{ trans("section.subjects") }}</a>
                                     <a href="{{ url(\'/studentgroup/' . $section->id . '/\' . $id . \'/timetable\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-calendar"></i>  {{ trans("studentgroup.timetable") }}</a>
                                    @endif
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'student_group.delete\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/studentgroup/' . $section->id . '/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }*/
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
    public function students(Department $section)
    {
        $title = trans('section.students');
        $students = $this->employeeRepository
            ->getAllForSchoolYearAndSection(
                session('current_company_year'),
                session('current_company'),
                $section->id
            )
            ->with('user')
            ->get();

        return view('section.students', compact('title', 'students'));
    }

    public function students_data(Department $section)
    {
        $students = $this->studentRepository
            ->getAllForSchoolYearAndSection(
                session('current_company_year'),
                session('current_company'),
                $section->id
            )
            ->with('user')
            ->orderBy('students.order')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'full_name' => isset($student->user) ? $student->user->full_name : '',
                    'order' => @$student->order,
                ];
            });

        return Datatables::make($students)
            ->addColumn('actions', '<!--<a href="{{ url(\'/student/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>-->
                                    <a href="{{ url(\'/student/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <!--<a href="{{ url(\'/student/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    public function get_groups(Department $section)
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

    public function kpis(Department $section)
    {
        $title = $section->title.' Kpis';
        /*$kpis= Kpi::whereHas('employee', function ($q) use ($section) {
            $q->where('employees.company_id', $school->id)
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();*/

        $kpis = $section->kpis;

        return view('section.kpis', compact('title', 'kpis'));
    }

    public function erpSync(Request $request)
    {
        $company = Company::find(session('current_company'));
        if ($company->erp_departments_endpoint === '') {
            return response('<div class="alert alert-danger">ERP EndPoint Not Set, Contact System Administrator!!!</div>');
        }
        try {
            DB::transaction(function () use ($request, $company) {
                $response = Http::withToken('BdUOYHXBApVGYmmriKENHrH90EE3wBf2kIUq3X9qIyeQgT3RThv9jUrfowB7DL89rkMnykyNmO1ElE3w')->get('http://api.jospong.com/erp/public/api/'.$company->erp_departments_endpoint)->json();

                $dataCollection = collect($response);

                foreach ($response as $key) {
                    Department::firstOrCreate(
                        [
                            'title' => $key['Name'],
                            'company_id' => session('current_company'),
                            'code' => $key['Code'],
                        ],
                    );
                }
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Departments Synced Successfully</div>');
    }
}
