<?php

namespace App\Http\Controllers\Secure;

use App\Models\Semester;
use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\FeesStatus;
use App\Models\Student;
use App\Models\StudentGraduation;
use App\Repositories\SchoolYearRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\StudentRepository;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Secure\SemesterRequest;
use App\Helpers\Flash;
use Session;
use DB;

class SemesterController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SchoolYearRepository
     */
    private $schoolYearRepository;
    /**
     * @var SemesterRepository
     */
    private $semesterRepository;
    /**
     * @var SchoolYearRepository
     */
    private $schoolRepository;

    /**
     * SemesterController constructor.
     *
     * @param SchoolYearRepository $schoolYearRepository
     * @param SemesterRepository $semesterRepository
     * @param SchoolYearRepository $schoolRepository
     */
    public function __construct(
        SchoolYearRepository $schoolYearRepository,
        SemesterRepository $semesterRepository,
        StudentRepository $studentRepository,
        SchoolYearRepository $schoolRepository
    ) {

        parent::__construct();
        view()->share('type', 'semester');

        $columns = ['title','start', 'end','year', 'actions'];
        view()->share('columns', $columns);

        $this->schoolYearRepository = $schoolYearRepository;
        $this->semesterRepository = $semesterRepository;
        $this->schoolRepository = $schoolRepository;
        $this->studentRepository = $studentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('semester.semesters');

        $schoolyears = $this->schoolYearRepository
              ->getAllForSchool(session('current_company'))
              ->pluck('title', 'id')
              ->toArray();

        $semesters = $this->semesterRepository->getAllForSchool(session('current_company'))
            ->with('school_year')
            ->get();
        return view('semester.index', compact('title', 'schoolyears', 'semesters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('semester.new');
        $schoolyears = $this->schoolYearRepository->getAllForSchool(session('current_company'))->pluck('title', 'id')->toArray();
        return view('layouts.create', compact('title', 'schoolyears'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|SemesterRequest $request
     * @return Response
     */
    public function store(SemesterRequest $request)
    {

        //check if current semester has ended
        $currentSemester = Semester::where('company_id', session('current_company'))
            ->where('school_year_id', session('current_company_year'))
            ->where('active', 'Yes')->first();

        if ($currentSemester->end < now()->toDateString()) {
            DB::beginTransaction();
        //Diactivate Current Semester
            $Csemester = Semester::find(session('current_company_semester'));
            $Csemester->active = 'No';
            $Csemester->save();


            //check if its a new academic year and create new academic year record and set active to yes

            $semester = new Semester($request->all());
            $semester->company_id = session('current_company');
            $semester->active = 'Yes';
            $semester->save();



        //get all active students

            $students = $this->studentRepository->getAllActive(session('current_company_year'), session('current_company_semester'), session('current_company'))
                ->with('user', 'section')
                ->get();


        //bill all active student with semester fees if its not a new academic year



            foreach ($students as $student) {
                $user_exists = Invoice::where('user_id', $student->user_id)
                    ->where('school_year_id', $semester->school_year_id)
                    ->where('semester_id', $semester->id)
                    ->where('company_id', '=', session('current_company'))
                    ->first();



                if (!isset($user_exists->id)) {
                    $currentFees = Invoice::where('user_id', $student->user_id)
                        ->where('school_year_id', session('current_company_year'))
                        ->where('semester_id', session('current_company_semester'))
                        ->where('company_id', '=', session('current_company'))
                        ->first();

                    //semester fees if not a new academic year
                    if ($semester->school_year_id == session('current_company_year')) {
                        $fees = FeeCategory::all()->whereIn('section_id', [$student->section_id, 7])
                            ->where('company_id', '=', session('current_company'))
                            ->where('currency_id', '=', $student->currency_id)
                            ->where('fees_period_id', '=', 1);


                        $invoice = new Invoice();
                        $invoice->student_id = $student->id;
                        $invoice->user_id = $student->user_id;
                        $invoice->company_id = session('current_company');
                        $invoice->school_year_id = $semester->school_year_id;
                        $invoice->semester_id = $semester->id;
                        $invoice->currency_id = $student->currency_id;
                        @$invoice->total_fees = @$fees->sum('amount') + @$currentFees->total_fees;
                        @$invoice->arrears = @$currentFees->total_fees;
                        $invoice->amount = $fees->sum('amount');
                        $invoice->save();


                        foreach ($fees as $fee) {
                            $feesStatus = new FeesStatus();
                            $feesStatus->invoice_id = $invoice->id;
                            $feesStatus->user_id = $student->user_id;
                            $feesStatus->company_id = session('current_company');
                            $feesStatus->school_year_id = $semester->school_year_id;
                            $feesStatus->semester_id = $semester->id;
                            ;
                            $feesStatus->title = $fee->title;
                            $feesStatus->currency_id = $student->currency_id;
                            $feesStatus->amount = $fee->amount;
                            $feesStatus->fee_category_id = $fee->id;
                            $feesStatus->save();
                        }
                    }


                    //Per years fees if  new academic year
                    if ($semester->school_year_id > session('current_company_year')) {
                        $fees = FeeCategory::all()->whereIn('section_id', [$student->section_id, 7])
                            ->where('company_id', '=', session('current_company'))
                            ->where('currency_id', '=', $student->currency_id)
                            ->where('fees_period_id', '=', 2);


                        $invoice = new Invoice();
                        $invoice->student_id = $student->id;
                        $invoice->user_id = $student->user_id;
                        $invoice->company_id = session('current_company');
                        $invoice->school_year_id = $semester->school_year_id;
                        $invoice->semester_id = $semester->id;
                        $invoice->currency_id = $student->currency_id;
                        @$invoice->total_fees = @$fees->sum('amount') + @$currentFees->total_fees;
                        @$invoice->arrears = @$currentFees->total_fees;
                        $invoice->amount = $fees->sum('amount');
                        $invoice->save();


                        foreach ($fees as $fee) {
                            $feesStatus = new FeesStatus();
                            $feesStatus->invoice_id = $invoice->id;
                            $feesStatus->user_id = $student->user_id;
                            $feesStatus->company_id = session('current_company');
                            $feesStatus->school_year_id = $semester->school_year_id;
                            $feesStatus->semester_id = $semester->id;
                            ;
                            $feesStatus->title = $fee->title;
                            $feesStatus->currency_id = $student->currency_id;
                            $feesStatus->amount = $fee->amount;
                            $feesStatus->fee_category_id = $fee->id;
                            $feesStatus->save();
                        }
                    }
                }



                //if its a new academic year
                if ($semester->school_year_id > session('current_company_year')) {
                    //check academic conditions to move students up their level a step, send them mail

                    if ($student->graduate != 'Yes') {
                        $student2 = Student::find($student->id);
                        $student2->level_id = $student2->next_level_id;
                        $student2->save();
                    }
                    //graduate final year level students if they meet the graduation requirements
                    if ($student->graduate == 'Yes') {
                        StudentGraduation::firstOrCreate(['company_id' => session('current_company'),
                            'school_year_id' => session('current_company_year'),
                            'semester_id' => session('current_company_semester'),
                            'student_id' => $student->id]);
                    }
                }

                //else maintain their current level and send them mail of the course they trailled
            }




        // Set the application variables to the new academic year and semester

            DB::commit();
        } else {
              Flash::error(trans('semester.current_not_ended'));
            //Flash::error(trans('semester.current_not_ended'$currentSemester->end_date));

            return back()->withInput();
            exit();
        }



        return redirect('/semester');
    }

    /**
     * Display the specified resource.
     *
     * @param Semester $semester
     * @return Response
     * @internal param int $id
     */
    public function show(Semester $semester)
    {
        $title = trans('semester.details');
        $action = 'show';
        return view('layouts.show', compact('semester', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Semester $semester
     * @return Response
     * @internal param int $id
     */
    public function edit(Semester $semester)
    {
        $title = trans('semester.edit');
        $schoolyears = $this->schoolYearRepository
            ->getAllForSchool(session('current_company'))
            ->pluck('title', 'id')
            ->toArray();
        /*$schools = $this->schoolRepository
		    ->getAll()
		    ->get()
		    ->pluck('title', 'id')
		    ->prepend(trans('schoolyear.select_school'), 0)
		    ->toArray();*/
        return view('layouts.edit', compact('title', 'schoolyears', 'semester'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SemesterRequest $request
     * @param Semester $semester
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(SemesterRequest $request, Semester $semester)
    {
        $semester->update($request->all());
        return redirect('/semester');
    }

    /**
     * @param Semester $semester
     * @return \BladeView|bool|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function delete(Semester $semester)
    {
        $title = trans('semester.delete');
        return view('/semester/delete', compact('semester', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Semester $semester
     * @return Response
     * @internal param int $id
     */
    public function destroy(Semester $semester)
    {
        $semester->delete();
        return redirect('/semester');
    }

    public function data()
    {
        $semesters = $this->semesterRepository->getAllForSchool(session('current_company'))
            ->with('school_year')
            ->get()
            ->map(function ($semester) {
                return [
                    'id' => $semester->id,
                    'title' => $semester->title,
                    'start' => $semester->start,
                    'end' => $semester->end,
                    'year' => isset($semester->school_year) ? $semester->school_year->title : "",
                    //'school' => isset($semester->school_year) ? $semester->school_year->school->title : "",
                ];
            });

        return Datatables::make($semesters)
            ->addColumn('actions', '<a href="{{ url(\'/semester/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    <a href="{{ url(\'/semester/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                     <a href="{{ url(\'/semester/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }
}
