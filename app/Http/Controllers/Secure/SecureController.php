<?php

namespace App\Http\Controllers\Secure;

use Session;
use Sentinel;
use Carbon\Carbon;
use App\Models\Faq;
use App\Models\Kpi;
use App\Models\Blog;
use App\Models\Book;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Page;
use App\Models\User;
use App\Models\Diary;
use App\Helpers\Flash;
use App\Models\Client;
use App\Models\Notice;
use App\Models\Option;
use App\Models\Salary;
use App\Models\School;
use App\Models\Company;
use App\Models\GetBook;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Subject;
use App\Models\Version;
use App\Models\BookUser;
use App\Models\Employee;
use App\Models\Semester;
use App\Helpers\Settings;
use App\Models\Applicant;
use App\Models\Direction;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\ReturnBook;
use App\Models\SchoolYear;
use App\Models\SmsMessage;
use App\Models\StaffLeave;
use App\Models\CompanyYear;
use App\Models\KpiTimeline;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\ApplyingLeave;
use App\Models\TeacherSchool;
use App\Helpers\GeneralHelper;
use App\Models\StaffLeaveType;
use App\Models\TeacherSubject;
use App\Models\Transportation;
use App\Models\SchoolDirection;
use App\Models\StaffAttendance;
use App\Models\KpiResponsibility;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeKpiActivity;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Traits\SharedValuesTrait;

class SecureController extends Controller
{
    use SharedValuesTrait;

    public $user;

    public $bscYear;

    public $school;

    public $quarter;

    public $currentEmployee;

    public $time;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Sentinel::check()) {
                $dt = Carbon::now();
                $this->user = User::find(Sentinel::getUser()->id);
                $this->time = date('H');
                $this->bscYear = CompanyYear::find(session('current_company_year'));
                $this->school = Company::find(session('current_company'));

                $this->currentEmployee = Employee::find(session('current_employee'));
                if (! isset($this->user)) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.account_deleted'));

                    return redirect()->guest('/');
                }
                view()->share('user', $this->user);
                view()->share('bscYear', $this->bscYear);
                view()->share('school', $this->school);
                view()->share('quarter', $this->quarter);
                view()->share('currentEmployee', $this->currentEmployee);
                view()->share('time', $this->time);

                $this->shareValues();
            } else {
                Sentinel::logout(null, true);
            }

            return $next($request);
        });
    }

    public function showHome()
    {
        if (Sentinel::check()) {
            $new_emails = Message::where('to', $this->user->id)->whereNull('deleted_at_receiver')->where('read', 0)->get();
            if ($this->user->inRole('super_admin')) {
                list($clients, $projects) = $this->super_admin_dashboard();

                return view(
                        'dashboard.super_admin',
                        compact('new_emails', 'clients', 'projects')
                    );
            } elseif ($this->user->inRole('admin_super_admin') || $this->user->inRole('admin')) {
                list($activeEmployees, $maleEmployees, $femaleEmployees, $departments,
                     $school) = $this->admin_dashboard();

                return view('dashboard.admin', compact(
                    'new_emails',
                    'activeEmployees',
                    'maleEmployees',
                    'femaleEmployees',
                    'departments',
                    'school'
                ));
            } elseif ($this->user->inRole('employee')) {
                list($school, $bscYear, $currentEmployee, $projects) = $this->admin_dashboard();

                return view('dashboard.employee', compact(
                    'new_emails',
                    'school', 'bscYear', 'currentEmployee', 'projects'
                ));
            }

            return view('dashboard.index');
        } else {
            if (Page::count() == 0) {
                if (Blog::count() > 0) {
                    return redirect('blogs')->send();
                } elseif (Settings::get('about_school_page') == 'yes') {
                    return redirect('about_us')->send();
                } elseif (Settings::get('about_teachers_page') == 'yes') {
                    return redirect('about_teachers_page')->send();
                } elseif (Settings::get('show_contact_page') == 'yes') {
                    return redirect('contact')->send();
                } elseif (Faq::count() > 0) {
                    return redirect('faqs')->send();
                } else {
                    /*return redirect('signin')->send();*/
                    return redirect('about_us')->send();
                }
            }
            $first = Page::first();

            return redirect('page/'.$first->slug)->send();
        }
    }








    private function admin_dashboard()
    {
        $school = Company::find(session('current_company'));
        if (is_null($school)) {
            Flash::error(trans('secure.no_schools'));
        }

        $bscYear = CompanyYear::find(session('current_company_year'));
        $currentEmployee = Employee::find(session('current_employee'));
        $projects = Project::where('company_id', session('current_company'))->get();

        return [$school, $bscYear, $currentEmployee, $projects];
    }

    private function super_admin_dashboard()
    {
        $clients = Client::get();
        $projects = Project::get();

        return [$clients, $projects];
    }

    public static function showConfirmDeletePage($id)
    {
        # code...
        $g = Project::with(['company'])->find($id);
        return view("dashboard.show_confirm_delete_page", compact('g'));
    }

    public static function destroy(Request $request)
    {
        # code...
        $get_project_id  =  Crypt::decrypt( $request->input("g") );
        $g               =  Project::find($get_project_id)->delete();

        if ($g) {
            # code...
            return Redirect::to('/')->with("success",
                "Project #" .$get_project_id ." deleted successfully");
        }else {
            # code...
            throw new Exception("Error Processing Request", 1);

        }
    }

}