<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\StaffSalaryRequest;
use App\Models\Company;
use App\Models\SchoolAdmin;
use App\Models\StaffSalary;
use App\Models\User;
use App\Repositories\StaffSalaryRepository;
use Yajra\DataTables\Facades\DataTables;

class StaffSalaryController extends SecureController
{
    /**
     * @var StaffSalaryRepository
     */
    private $staffSalaryRepository;

    /**
     * StaffSalaryController constructor.
     * @param StaffSalaryRepository $staffSalaryRepository
     */
    public function __construct(StaffSalaryRepository $staffSalaryRepository)
    {
        parent::__construct();

        $this->staffSalaryRepository = $staffSalaryRepository;

        $this->middleware('authorized:salary.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:salary.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:salary.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:salary.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'staff_salary');

        $columns = ['full_name', 'school', 'price', 'date_start', 'date_end', 'actions'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(User $teacher)
    {
        $title = trans('staff_salary.staff_salary');

        return view('staff_salary.index', compact('title', 'teacher'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(User $teacher)
    {
        $title = trans('staff_salary.new');
        if ($this->user->inRole('human_resources') || $this->user->inRole('accountant') || $this->user->inRole('super_admin')) {
            $schools_list = Company::pluck('title', 'id')->toArray();
        } else {
            $schools_list = SchoolAdmin::join('schools', 'schools.id', '=', 'school_admins.company_id')
                ->where('user_id', $this->user->id)->select('schools.*')->pluck('title', 'id')->toArray();
        }

        return view('layouts.create', compact('title', 'schools_list', 'teacher'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StaffSalaryRequest $request, User $teacher)
    {
        $staffSalary = new StaffSalary($request->all());
        $staffSalary->user_id = $teacher->id;
        $staffSalary->save();

        return redirect('/staff_salary/'.$teacher->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show(User $teacher, StaffSalary $staffSalary)
    {
        $title = trans('staff_salary.details');
        $action = 'show';

        return view('layouts.show', compact('staffSalary', 'title', 'action', 'teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit(User $teacher, StaffSalary $staffSalary)
    {
        $title = trans('teacher.edit');
        if ($this->user->inRole('human_resources') || $this->user->inRole('accountant') || $this->user->inRole('super_admin')) {
            $schools_list = Company::pluck('title', 'id')->toArray();
        } else {
            $schools_list = SchoolAdmin::join('schools', 'schools.id', '=', 'school_admins.company_id')
                ->where('user_id', $this->user->id)->select('schools.*')->pluck('title', 'id')->toArray();
        }

        return view('layouts.edit', compact('title', 'staffSalary', 'schools_list', 'teacher'));
    }

    /**
     * Update the specified resource in storage.
     * @param  int $id
     * @return Response
     */
    public function update(StaffSalaryRequest $request, User $teacher, StaffSalary $staffSalary)
    {
        $staffSalary->update($request->all());

        return redirect('staff_salary/'.$teacher->id);
    }

    /**
     * @param $website
     * @return Response
     */
    public function delete(User $teacher, StaffSalary $staffSalary)
    {
        $title = trans('staff_salary.delete');

        return view('staff_salary/delete', compact('teacher', 'staffSalary', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(User $teacher, StaffSalary $staffSalary)
    {
        $staffSalary->delete();

        return redirect('staff_salary/'.$teacher->id);
    }

    public function data(User $teacher)
    {
        $staff_salaries = $this->staffSalaryRepository->getAllForSchoolAndStaff(session('current_company'), $teacher->id)
            ->get()
            ->map(function ($staff_salary) {
                return [
                    'id' => $staff_salary->id,
                    'user_id' => $staff_salary->user_id,
                    'full_name' => $staff_salary->user->full_name,
                    'school' => $staff_salary->school->title,
                    'price' => $staff_salary->price,
                    'date_start' => $staff_salary->date_start,
                    'date_end' => $staff_salary->date_end,
                ];
            });

        return Datatables::make($staff_salaries)
            ->addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'salary.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/staff_salary/\' . $user_id . \'/\'.$id.\'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/staff_salary/\' . $user_id . \'/\'.$id.\'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'salary.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/staff_salary/\' . $user_id . \'/\'.$id.\'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')
            ->removeColumn('id')
            ->removeColumn('user_id')
             ->rawColumns(['actions'])->make();
    }
}
