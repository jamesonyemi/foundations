<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\SalaryRequest;
use App\Models\Salary;
use App\Repositories\SalaryRepository;
use App\Repositories\StaffSalaryRepository;
use App\Repositories\TeacherSchoolRepository;
use App\Repositories\UserRepository;
use DB;
use PDF;

class SalaryController extends SecureController
{
    /**
     * @var SalaryRepository
     */
    private $salaryRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var StaffSalaryRepository
     */
    private $staffSalaryRepository;

    /**
     * @var TeacherSchoolRepository
     */
    private $teacherSchoolRepository;

    /**
     * SalaryController constructor.
     *
     * @param SalaryRepository $salaryRepository
     * @param UserRepository $userRepository
     * @param StaffSalaryRepository $staffSalaryRepository
     * @param TeacherSchoolRepository $teacherSchoolRepository
     */
    public function __construct(
        SalaryRepository $salaryRepository,
        UserRepository $userRepository,
        StaffSalaryRepository $staffSalaryRepository,
        TeacherSchoolRepository $teacherSchoolRepository
    ) {
        parent::__construct();

        $this->salaryRepository = $salaryRepository;
        $this->userRepository = $userRepository;
        $this->staffSalaryRepository = $staffSalaryRepository;
        $this->teacherSchoolRepository = $teacherSchoolRepository;

        $this->middleware('authorized:salary.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:salary.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:salary.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:salary.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'salary');
        $columns = ['salary', 'date', 'full_name', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $sum_salary = $this->salaryRepository->getAllForSchoolMonthAndYear(session('current_company'), date('m'), date('Y'))
            ->get()->sum('salary');
        $sum_need_payment_salary = $this->staffSalaryRepository->getAllForSchool(session('current_company'))
            ->get()->sum('price');

        $salary = [['title' => trans('salary.total_payment'), 'items' => $sum_salary, 'color' => '#fd9883'],
            ['title' => trans('salary.total_set_salary'), 'items' => $sum_need_payment_salary, 'color' => '#c2185b'], ];

        $title = trans('salary.salary');

        return view('salary.index', compact('title', 'salary'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('salary.new');
        $users = $this->list_of_users();

        return view('layouts.create', compact('title', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SalaryRequest $request
     *
     * @return Response
     */
    public function store(SalaryRequest $request)
    {
        $salary = new Salary($request->all());
        $salary->company_id = session('current_company');
        $salary->company_year_id = session('current_company_year');
        $salary->save();

        return redirect('/salary');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(Salary $salary)
    {
        $title = trans('salary.details');
        $action = 'show';

        return view('layouts.show', compact('salary', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Salary $salary
     * @return Response
     */
    public function edit(Salary $salary)
    {
        $title = trans('salary.edit');
        $users = $this->list_of_users();

        return view('layouts.edit', compact('title', 'salary', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(SalaryRequest $request, Salary $salary)
    {
        $salary->update($request->all());

        return redirect('/salary');
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(Salary $salary)
    {
        $title = trans('salary.delete');

        return view('/salary/delete', compact('salary', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     * @param  Salary $salary
     * @return Response
     */
    public function destroy(Salary $salary)
    {
        $salary->delete();

        return redirect('/salary');
    }

    public function print_salary(Salary $salary)
    {
        $data = '<h1>'.trans('salary.salary').'</h1><p>'.trans('salary.date').': '.$salary->date.'</p>
                <p>'.trans('salary.school').': '.$salary->school->title.'</p>
                <p>'.trans('salary.staff').': '.$salary->user->full_name.'</p>
                <p>'.trans('salary.salary').': '.$salary->salary.'</p>';
        $pdf = PDF::loadView('report.salary', compact('data'));

        return $pdf->stream();
    }

    public function data()
    {
        $salaries = $this->salaryRepository
            ->getAllForSchoolYearSchool(session('current_company'), session('current_company_year'))
            ->with('user')
            ->get()
            ->map(function ($salary) {
                return [
                    'id' => $salary->id,
                    'salary' => $salary->salary,
                    'date' => $salary->date,
                    'full_name' => isset($salary->user) ? $salary->user->full_name : '',
                ];
            });

        return Datatables::make($salaries)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'salary.edit\', Sentinel::getUser()->permissions)))
                                        <a href="{{ url(\'/salary/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/salary/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    <a href="{{ url(\'/salary/\' . $id . \'/print_salary\' ) }}" class="btn btn-warning btn-sm" >
                                            <i class="fa fa-print"></i>  {{ trans("salary.print_salary") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'salary.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/salary/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                     @endif')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make();
    }

    /**
     * @return mixed
     */
    public function list_of_users()
    {
        $one_school = (Settings::get('account_one_school') == 'yes') ? true : false;
        if (($one_school && $this->user->inRole('accountant')) || $this->user->inRole('admin')) {
            $teachers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
                                                      ->map(function ($user) {
                                                          return [
                                                              'id'   => $user->id,
                                                              'name' => $user->full_name,
                                                          ];
                                                      })
                                                      ->pluck('name', 'id')
                                                      ->toArray();

            return $teachers;
        } else {
            $teachers = $this->userRepository->getUsersForRole('teacher')
                                                    ->map(function ($user) {
                                                        return [
                                                            'id'   => $user->id,
                                                            'name' => $user->full_name,
                                                        ];
                                                    })
                                                    ->pluck('name', 'id')
                                                    ->toArray();
            $human_resources = $this->userRepository->getUsersForRole('human_resources')
                                                    ->map(function ($user) {
                                                        return [
                                                            'id'   => $user->id,
                                                            'name' => $user->full_name,
                                                        ];
                                                    })
                                                    ->pluck('name', 'id')
                                                    ->toArray();
            $admins = $this->userRepository->getUsersForRole('admin')
                                                    ->map(function ($user) {
                                                        return [
                                                            'id'   => $user->id,
                                                            'name' => $user->full_name,
                                                        ];
                                                    })
                                                    ->pluck('name', 'id')
                                                    ->toArray();
            $accountant = $this->userRepository->getUsersForRole('accountant')
                                                    ->map(function ($user) {
                                                        return [
                                                            'id'   => $user->id,
                                                            'name' => $user->full_name,
                                                        ];
                                                    })
                                                    ->pluck('name', 'id')
                                                    ->toArray();

            return $teachers + $human_resources + $admins + $accountant;
        }
    }
}
