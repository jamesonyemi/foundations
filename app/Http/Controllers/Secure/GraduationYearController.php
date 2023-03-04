<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\CreateAluminiRequest;
use App\Http\Requests\Secure\CreateNewSections;
use App\Http\Requests\Secure\SchoolYearRequest;
use App\Models\Alumini;
use App\Models\AluminiStudent;
use App\Models\School;
use App\Models\GraduationYear;
use App\Models\Section;
use App\Models\Student;
use App\Repositories\SchoolRepository;
use App\Repositories\GraduationYearRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

class GraduationYearController extends SecureController
{
    /**
     * @var GraduationYearRepository
     */
    private $graduationYearRepository;
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SchoolRepository
     */
    private $schoolRepository;

    /**
     * SchoolYearController constructor.
     *
     * @param GraduationYearRepository $schoolYearRepository
     * @param SectionRepository $sectionRepository
     * @param StudentRepository $studentRepository
     * @param SchoolRepository $schoolRepository
     */
    public function __construct(
        GraduationYearRepository $graduationYearRepository,
        SectionRepository $sectionRepository,
        StudentRepository $studentRepository,
        SchoolRepository $schoolRepository
    ) {
        parent::__construct();

        $this->graduationYearRepository = $graduationYearRepository;
        $this->sectionRepository    = $sectionRepository;
        $this->studentRepository    = $studentRepository;
        $this->schoolRepository     = $schoolRepository;

        view()->share('type', 'schoolyear');

        $columns = ['title','id_code', 'total_applicants', 'total_students_admitted', 'total_active_students', 'total_deferred_students', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('schoolyear.schoolyear');

        $data = $this->graduationYearRepository->getAllForSchool(session('current_company'))
            ->get();


        return view('schoolyear.index', compact('title', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('schoolyear.new');

        /*$schools = $this->schoolRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('schoolyear.select_school'), 0)
            ->toArray();*/

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SchoolYearRequest $request
     *
     * @return Response
     */
    public function store(SchoolYearRequest $request)
    {

        try
        {

            if (GraduationYear::where('title', '=', $request->title)->exists()) {
                return response('<div class="alert alert-warning">School Year Already Exists</div>') ;
            }
            elseif (GraduationYear::where('id_code', '=', $request->id_code)->exists()) {
                return response('<div class="alert alert-warning">ID Code Already Exists</div>') ;
            }
            else
                {
                    $schoolYear = new GraduationYear($request->all());
                    $schoolYear->company_id = session('current_company');
                    $schoolYear->save();
            }

        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }


        if ($schoolYear->save())
        {

            return response('<div class="alert alert-success">School Year Added Successfully</div>') ;
        }
        else
            {
                return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }



    }

    /**
     * Display the specified resource.
     *
     * @param  SchoolYear $schoolYear
     *
     * @return Response
     */
    public function show(GraduationYear $schoolYear)
    {
        $title  = trans('schoolyear.details');
        $action = 'show';
        $students = [];
        if (is_null($schoolYear->school)) {
            foreach ($this->schoolRepository->getAll()->get() as $item) {
                $students[ $item->title ] = $this->studentRepository->getCountStudentsForSchoolAndSchoolYear($item->id, $schoolYear->id);
            }
        } else {
            $students[ $schoolYear->school->title ] = $this->studentRepository->getCountStudentsForSchoolAndSchoolYear($schoolYear->school->id, $schoolYear->id);
        }
        return view('layouts.show', compact('schoolYear', 'title', 'action', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  SchoolYear $schoolYear
     *
     * @return Response
     */
    public function edit(GraduationYear $schoolYear)
    {
        $title = trans('schoolyear.edit');

        /*$schools = $this->schoolRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('schoolyear.select_school'), 0)
            ->toArray();*/

        return view('layouts.edit', compact('title', 'schoolYear'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SchoolYearRequest $request
     * @param  SchoolYear $schoolYear
     *
     * @return Response
     */
    public function update(SchoolYearRequest $request, GraduationYear $schoolYear)
    {
        $schoolYear->update($request->all());

        return redirect('/schoolyear');
    }

    /**
     * @param SchoolYear $schoolYear
     *
     * @return Response
     */
    public function delete(GraduationYear $schoolYear)
    {
        $title = trans('schoolyear.delete');

        return view('/schoolyear/delete', compact('schoolYear', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  SchoolYear $schoolYear
     *
     * @return Response
     */
    public function destroy(GraduationYear $schoolYear)
    {
        $schoolYear->delete();

        return redirect('/schoolyear');
    }

    public function data()
    {
        $schoolYears = $this->graduationYearRepository->getAllForSchool(session('current_company'))
                                                  ->get()
                                                  ->map(function ($schoolYear) {
                                                      return [
                                                          'id'    => $schoolYear->id,
                                                          'title' => $schoolYear->title,
                                                          'id_code' => $schoolYear->id_code,
                                                          'total_applicants' => $schoolYear->applicants->count(),
                                                          'total_students_admitted' => $schoolYear->admittedStudents->count(),
                                                          'total_active_students' => $schoolYear->activeStudents->count(),
                                                          'total_deferred_students' => $schoolYear->deferredStudents->count(),
                                                      ];
                                                  });

        return Datatables::make($schoolYears)
                          ->addColumn('actions', '<a href="{{ url(\'/schoolyear/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                  	<!--<a href="{{ url(\'/schoolyear/\' . $id . \'/copy_data\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-files-o"></i>  {{ trans("schoolyear.copy_sections_students") }}</a>-->
                                    <!--<a href="{{ url(\'/schoolyear/\' . $id . \'/make_alumini\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-history"></i>  {{ trans("schoolyear.make_alumini") }}</a>-->
                                    <!--<a href="{{ url(\'/schoolyear/\' . $id . \'/get_alumini\' ) }}" class="btn btn-info btn-sm" >
                                            <i class="fa fa-history"></i>  {{ trans("schoolyear.get_alumini") }}</a>-->
                                     <a href="{{ url(\'/schoolyear/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <!--<a href="{{ url(\'/schoolyear/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>-->')
                          ->removeColumn('id')
                          ->rawColumns(['actions'])
                          ->make();
    }

    public function copyData(GraduationYear $schoolYear)
    {
        $title            = trans('schoolyear.copy_sections_students_to') . $schoolYear->title;
        $school_year_list = $this->schoolYearRepository->getAll()->where('id', '<>', $schoolYear->id)
                                                       ->pluck('title', 'id')->prepend(trans('schoolyear.schoolyear_select'), 0)->toArray();

        $school_list = $this->schoolRepository->getAll()->pluck('title', 'id')
                                              ->prepend(trans('schoolyear.select_school'), 0)->toArray();

        return view('schoolyear/copy', compact('schoolYear', 'title', 'school_year_list', 'school_list'));
    }

    public function getSections(GraduationYear $schoolYear, School $school)
    {
        return $this->sectionRepository->getAllForSchoolYearSchool($schoolYear->id, $school->id)->get()
                                       ->pluck('title', 'id')->toArray();
    }

    public function getStudents(Section $section)
    {
        return $this->studentRepository->getAllForSection($section->id)
                                       ->map(function ($student) {
                                           return [
                                               'id'    => $student->user_id,
                                               'title' => $student->user->full_name,
                                           ];
                                       })
                                       ->pluck('title', 'id')->toArray();
    }

    public function postData(GraduationYear $schoolYear, CreateNewSections $request)
    {
        DB::beginTransaction();
        $section = Section::find($request->get('section_id'));
        if (isset($section)) {
            $section_new                     = new Section();
            $section_new->school_year_id     = $schoolYear->id;
            $section_new->section_teacher_id = $section->section_teacher_id;
            $section_new->company_id          = $request->get('select_company_id');
            $section_new->title              = $request->get('section_name');
            $section_new->save();

            if (! empty($request->get('students_list'))) {
                foreach ($request->get('students_list') as $student_user_id) {
                    $old_student = Student::where('user_id', $student_user_id)
                                          ->where('school_year_id', $request->get('select_school_year_id'))
                                          ->where('company_id', $request->get('select_company_id'))->first();
                    $student_new = Student::create([
                        'school_year_id' => $schoolYear->id,
                        'user_id'        => $student_user_id,
                        'section_id'     => $section_new->id,
                        'company_id'      => $old_student->company_id,
                        'order'          => $old_student->order
                    ]);

                    $student_new->student_no = $this->generateStudentNo($student_new->id, $student_new->company_id);
                    $student_new->save();
                }
            }
        }
        DB::commit();

        return redirect()->back();
    }


    public function makeAlumini(GraduationYear $schoolYear)
    {
        $title = trans('schoolyear.make_alumini') . $schoolYear->title;

        $school_list = $this->schoolRepository->getAll()->pluck('title', 'id');

        return view('schoolyear/make_alumini', compact('schoolYear', 'title', 'school_list'));
    }

    public function postAlumini(GraduationYear $schoolYear, CreateAluminiRequest $request)
    {
        DB::beginTransaction();
        $company_ids = $request->get('company_ids');
        if (is_null($company_ids)) {
            $company_ids = $this->schoolRepository->getAll()->pluck('id', 'id');
        } else {
            $company_ids = explode(',', $company_ids);
        }
        $aluminiStudents = [];
        foreach ($company_ids as $school) {
            $students = $this->schoolRepository->getAllAluministudents($school, $schoolYear->id)->get()
                                               ->map(function ($student) {
                                                   return [
                                                       'student_id' => $student->student_id,
                                                   ];
                                               })->toArray();
            if (count($students) > 0) {
                $aluminiStudents[ $school ] = $students;
            }
        }
        if (count($aluminiStudents) > 0) {
            $alumini = Alumini::create([ 'title' => $request->get('title'), 'school_year_id' => $schoolYear->id ]);
            foreach ($aluminiStudents as $school => $students) {
                foreach ($students as $student) {
                    AluminiStudent::create([
                        'alumini_id'     => $alumini->id,
                        'student_id'     => $student['student_id'],
                        'company_id'      => $school,
                        'school_year_id' => $schoolYear->id
                    ]);
                }
            }
        }
        DB::commit();

        return back();
    }

    public function getAlumini(GraduationYear $schoolYear)
    {
        $title = trans('schoolyear.get_alumini') . $schoolYear->title;

        $school_list = $this->schoolRepository->getAll()->pluck('title', 'id');
        $aluminis    = Alumini::where('school_year_id', $schoolYear->id)->pluck('title', 'id');

        return view('schoolyear/get_alumini', compact('schoolYear', 'title', 'school_list', 'aluminis'));
    }

    public function getAluminiStudents(GraduationYear $schoolYear, Alumini $alumini, Request $request)
    {
         $aluminiStudents = AluminiStudent::join('students', 'students.id', 'alumini_students.student_id')
                             ->join('users', 'users.id', 'students.user_id')
                             ->join('schools', 'schools.id', 'students.company_id')
                            ->where('alumini_students.school_year_id', $schoolYear->id)
                            ->where('alumini_students.alumini_id', $alumini->id);
        if (!is_null($request->get('company_ids'))) {
            $aluminiStudents->whereIn('schools.id', $request->get('company_ids'));
        }
        return $aluminiStudents->select('users.first_name', 'users.last_name', 'schools.title')->get();
    }
}
