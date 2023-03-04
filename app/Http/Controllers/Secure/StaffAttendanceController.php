<?php

namespace App\Http\Controllers\Secure;

use App\Classes\Reply;
use App\Exports\EmployeesAttendanceExport;
use App\Exports\StudentsExport;
use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Settings;
use App\Http\Requests\Secure\AddStaffAttendanceRequest;
use App\Http\Requests\Secure\AttendanceGetRequest;
use App\Http\Requests\Secure\DeleteRequest;
use App\Imports\AttendanceImport;
use App\Models\Attendance;
use App\Models\DailyAttendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Kpi;
use App\Models\Position;
use App\Models\StaffAttendance;
use App\Repositories\EmployeeRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StaffAttendanceRepository;
use App\Repositories\TeacherSchoolRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;

class StaffAttendanceController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var StaffAttendanceRepository
     */
    private $staffAttendanceRepository;

    /**
     * @var TeacherSchoolRepository
     */
    private $teacherSchoolRepository;

    /**
     * StaffAttendanceController constructor.
     *
     * @param OptionRepository $optionRepository
     * @param UserRepository $userRepository
     * @param StaffAttendanceRepository $staffAttendanceRepository
     * @param TeacherSchoolRepository $teacherSchoolRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        SectionRepository $sectionRepository,
        OptionRepository $optionRepository,
        UserRepository $userRepository,
        StaffAttendanceRepository $staffAttendanceRepository,
        TeacherSchoolRepository $teacherSchoolRepository
    ) {
        parent::__construct();

        view()->share('type', 'staff_attendance');
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;
        $this->optionRepository = $optionRepository;
        $this->userRepository = $userRepository;
        $this->staffAttendanceRepository = $staffAttendanceRepository;
        $this->teacherSchoolRepository = $teacherSchoolRepository;
    }

    public function index()
    {
        $title = trans('staff_attendance.attendance');
        /*$employees = $this->employeeRepository->getAllForSchool(session('current_company'))*/
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? @$item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('All Employees', 'all')
            ->toArray();

        $sections = Department::whereHas('employees', function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('staff_attendance.index', compact('title', 'employees', 'sections'));
    }

    public function monthlyAttendanceIndex()
    {
        $title = trans('staff_attendance.attendance');

        $sections = Department::whereHas('employees', function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('staff_attendance.monthly_attendance_index', compact('title', 'sections'));
    }

    public function weeklyAttendanceIndex()
    {
        $title = 'Weekly time attendance ';

        $sections = Department::whereHas('employees', function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();

        return view('staff_attendance.weekly_index', compact('title', 'sections'));
    }

    public function monthlyAttendance(Request $request)
    {
        $date = Carbon::create($request->date);
        $title = trans('staff_attendance.attendance');
        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))->where('status', 1)
            ->with('user', 'section');

        /*$employees = Employee::where('status', 1)->where('company_id', session('current_company'));;*/

        if ($request->department_id > 0) {
            $employees = $employees->where('department_id', $request->department_id)->get();
        } else {
            $employees = $employees->get();
        }

        $monthWorkingHours = GeneralHelper::monthlyWorkingHours($request->month, $request->year);
        $monthWorkingDays = GeneralHelper::workingdays($request->month, $request->year);

        return view('staff_attendance.monthly', compact('title', 'employees', 'monthWorkingDays', 'monthWorkingHours', 'request', 'date'));
    }

    public function weeklyAttendance(Request $request)
    {
        $date = Carbon::create($request->date);
        $title = 'Weekly time attendance for week '.$request->week;
        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))->where('status', 1)
            ->with('user', 'section');

        if ($request->department_id > 0) {
            $employees = $employees->where('department_id', $request->department_id)->get();
        } else {
            $employees = $employees->get();
        }

        /*$monthWorkingHours = GeneralHelper::monthlyWorkingHours($request->month, $request->year);
        $monthWorkingDays = GeneralHelper::workingdays($request->month, $request->year);*/

        return view('staff_attendance.weekly', compact('title', 'employees', 'request', 'date'));
    }

    public function filterAttendance(Request $request)
    {
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function ($query) use ($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        if ($request->department_id > 0) {
            $employees = $employees->where('department_id', $request->department_id)->get();
        } elseif ($request->employee_id == 'all') {
            $employees = $employees->get();
        } else {
            $employees = $employees->where('id', $request->employee_id)->get();
        }

        $final = [];

        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);

        foreach ($employees as $employee) {
            $final[$employee->id.'#'.@$employee->user->full_name] = array_fill(1, $this->daysInMonth, '-');

            foreach ($employee->attendance as $attendance) {
                $final[$employee->id.'#'.@$employee->user->full_name][Carbon::parse($attendance->date)->day] =
                    ($attendance->status == 'absent') ?
                        '<i class="fa fa-close text-danger"></i>' :
                        '<i class="fa fa-check text-success" onclick="getAttendanceDetails('.$employee->id.')" title='.Carbon::parse($attendance->date).'></i>';
            }
        }

        $this->employeeAttendence = $final;
        $data = $this->data;

        $daysInMonth = $this->daysInMonth;
        $employeeAttendence = $final;

        /*$view = View::make('staff_attendance.load', $this->data)->render();

        return Reply::successWithDataNew($view);*/
        /*dd($final);*/

        return view('staff_attendance.load', compact('final', 'data', 'daysInMonth', 'employeeAttendence', 'request'));
    }

    public function chartFilter(Request $request)
    {
        $date = Carbon::create($request->date);
        $attendances = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)->whereMonth('date', $date)->whereDay('date', $date)->where('device', 'ENTRANCE-IN')->get()->unique('employee_id');

        return view('staff_attendance.armchart1', compact('request', 'date', 'attendances'));
    }

    public function dailyExport(Request $request)
    {
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function ($query) use ($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        if ($request->department_id > 0) {
            $employees = $employees->where('department_id', $request->department_id)->get();
        } elseif ($request->employee_id == 'all') {
            $employees = $employees->get();
        } else {
            $employees = $employees->where('id', $request->employee_id)->get();
        }

        $final = [];

        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);

        foreach ($employees as $employee) {
            $final[$employee->id.'#'.@$employee->user->full_name] = array_fill(1, $this->daysInMonth, 0);

            foreach ($employee->attendance as $attendance) {
                $final[$employee->id.'#'.@$employee->user->full_name][Carbon::parse($attendance->date)->day] =
                    ($attendance->status == 'absent') ? 0 : 1;
            }
        }

        $this->employeeAttendence = $final;
        $data = $this->data;

        $daysInMonth = $this->daysInMonth;
        $employeeAttendence = $final;

        return view('staff_attendance.daily_export', compact('final', 'data', 'daysInMonth', 'employeeAttendence', 'request'));
    }

    public function erpSync(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $attendance = DailyAttendance::where('time_out', '')->orWhereNull('time_out')->get();

                foreach ($attendance as $key) {
                    $date = Carbon::create($key->date);
                    $data = Attendance::where('employee_id', $key->employee_id)->whereYear('date', $date)->whereMonth('date', $date)->whereDay('date', $date)->latest()->first();

                    if (isset($data->employee_id)) {
                        $key->time_out = date('H:i:s', strtotime($data->date));
                        $key->save();

                        /*
                                            array_push($savings, [
                                                "id" => $data->employee_id ?? '0',
                                                "time_out" => date('H:i:s', strtotime($data->date)),
                                            ]);*/
                    }
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Employees Synced Successfully</div>');
    }

    public function erpSync2(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $attendance = Attendance::where('employee_id', '!=', 0)/*->where('device', 'ENTRANCE-IN')*/->get();

                /*dd($dataCollection);*/
                foreach ($attendance as $key) {
                    DailyAttendance::firstOrCreate(
                        [
                            'employee_id' =>  $key->employee_id,
                            'date' => date('Y-m-d', strtotime($key->date)),
                        ],
                        [
                            'time_in' => date('H:i:s', strtotime($key->date)),

                            'status' => 'present',
                            'device' => $key->device,
                        ],

                    );
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Employees Synced Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $employee
     * @return Response
     */
    public function show(Employee $employee)
    {
        $title = $employee->user->full_name ?? '';
        $action = 'show';
        $attendances = Attendance::whereDay('date', now())->where('employee_id', $employee->id)->get();

        return view('staff_attendance._details', compact('attendances', 'title', 'action'));
    }

    public function showEmployee(Employee $employee, Request $request)
    {
        $date = Carbon::create($request->date);
        $title = $employee->user->full_name;
        $action = 'show';
        $attendances = Attendance::whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)->where('employee_id', $employee->id)->get();

        /*$attendances = Attendance::whereDay('date', now())->where('employee_id', $employee->id)->get();*/
        return view('staff_attendance._details', compact('attendances', 'title', 'action'));
    }

    public function showEmployeeDays(Employee $employee, Request $request)
    {
        $date = Carbon::create($request->date);
        $title = $employee->user->full_name;
        $action = 'show';
        $attendances = $employee->dailyAttendance($request->month, $request->year)->get();

        /*$attendances = Attendance::whereDay('date', now())->where('employee_id', $employee->id)->get();*/
        return view('staff_attendance.days', compact('attendances', 'title', 'action', 'request', 'employee'));
    }

    public function addAttendance(AddStaffAttendanceRequest $request)
    {
        if (isset($request['users'])) {
            foreach ($request['users'] as $user_id) {
                $attendance = new StaffAttendance($request->except('users'));
                $attendance->user_id = $user_id;
                $attendance->company_year_id = session('current_company_year');
                $attendance->company_id = session('current_company');
                $attendance->save();
            }
        }
    }

    public function attendanceForDate(AttendanceGetRequest $request)
    {
        $one_school = (Settings::get('account_one_school') == 'yes') ? true : false;
        if ($one_school && $this->user->inRole('accountant')) {
            $attendances = $this->staffAttendanceRepository->getAllForSchoolSchoolYear(session('current_company'), session('current_company_year'));
        } else {
            $attendances = $this->staffAttendanceRepository->getAllForSchoolYear(session('current_company_year'));
        }
        $attendances = $attendances->with('user', 'option')
            ->get()
            ->filter(function ($attendance) use ($request) {
                return Carbon::createFromFormat(Settings::get('date_format'), $attendance->date) ==
                    Carbon::createFromFormat(Settings::get('date_format'), $request->date);
            })
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'name' => $attendance->user->full_name,
                    'option' => $attendance->option->title,
                ];
            })->toArray();

        return json_encode($attendances);
    }

    public function deleteattendance(DeleteRequest $request)
    {
        $attendance = StaffAttendance::find($request['id']);
        $attendance->delete();
    }

    public function list_of_users()
    {
        $one_school = (Settings::get('account_one_school') == 'yes') ? true : false;
        if ($one_school && $this->user->inRole('accountant')) {
            $teachers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
                                             ->map(function ($user) {
                                                 return [
                                                     'id' => $user->id,
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

    public function getImport()
    {
        $title = trans('subject.import_subject');

        return view('staff_attendance.import', compact('title'));
    }

    public function postImport(Request $request)
    {
        $upload = Excel::import(new AttendanceImport(), $request->file('file'));

        Flash::success('Attendance Data Uploaded Successfully');

        return redirect('/subject');
    }
}
