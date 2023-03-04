<?php

namespace App\Http\Controllers\Secure;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SharedValuesTrait;
use App\Models\Blog;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Salary;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\SmsMessage;
use App\Models\Student;
use App\Models\TeacherSchool;
use App\Models\User;
use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sabberworm\CSS\Settings;
use Sentinel;

class AttendanceStatisticsController extends Controller
{
    use SharedValuesTrait;

    public $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Sentinel::check()) {
                $this->user = User::find(Sentinel::getUser()->id);
                if (! isset($this->user)) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.account_deleted'));

                    return redirect()->guest('/');
                }
                view()->share('user', $this->user);

                $version = isset(Version::first()->version) ? Version::first()->version : 1;
                view()->share('version', $version);

                $this->shareValues();
            } else {
                Sentinel::logout(null, true);
            }

            return $next($request);
        });
    }

    public function showCharts()
    {
        if (Sentinel::check()) {
            $new_emails = Message::where('to', $this->user->id)->whereNull('deleted_at_receiver')->where('read', 0)->get();
            if ($this->user->inRole('super_admin')) {
                list($sections, $teachers, $parents, $directions, $per_month, $per_school_year,$students_by_gender,
                    $teachers_by_gender, $school, $school_years, $countries) = $this->admin_dashboard();

                return view('charts.dashboardCharts', compact('new_emails','sections', 'teachers', 'parents', 'directions', 'per_month',
                    'per_school_year', 'students_by_gender', 'teachers_by_gender', 'school', 'school_years', 'countries'));
            } elseif ($this->user->inRole('admin_super_admin') ||
                $this->user->inRole('admin')) {
                list($sections, $teachers, $parents, $directions, $per_month, $per_school_year,$students_by_gender,
                    $teachers_by_gender, $school, $school_years, $countries) = $this->admin_dashboard();

                return view('charts.dashboardCharts', compact('new_emails','sections', 'teachers', 'parents', 'directions', 'per_month',
                    'per_school_year', 'students_by_gender', 'teachers_by_gender', 'school', 'school_years', 'countries'));
            }

            return view('charts.dashboardCharts');
        } else {
            if (Page::count() == 0) {
//                if(Blog::count()>0){
//                    return redirect('blogs')->send();
//                }elseif(Settings::get('about_school_page') == 'yes'){
//                    return redirect('about_school_page')->send();
//                }elseif(Settings::get('about_teachers_page') == 'yes'){
//                    return redirect('about_teachers_page')->send();
//                }elseif(Settings::get('show_contact_page') == 'yes'){
//                    return redirect('contact')->send();
//                }elseif(Faq::count()>0){
//                    return redirect('faqs')->send();
//                }else{
                return redirect('signin')->send();
            }
            $first = Page::first();

            return redirect('page/'.$first->slug)->send();
        }
    }

    private function admin_dashboard()
    {
        $sections = Section::where('company_id',
            session('current_company'))->get();

        $teachers = \App\Models\Student::whereHas('active', function ($q) {
            $q->where('students.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', session('current_company_year'));
        })
            ->whereNull('deleted_at')
            ->count();

        $parents = Student::join('users', 'users.id', 'students.user_id')
            ->join('sections', 'sections.id', 'students.section_id')
            ->join('levels', 'levels.id', 'students.level_id')
            ->join('school_years', 'school_years.id', 'students.school_year_id')
            ->whereHas('active', function ($q) {
                $q->where('students.company_id', session('current_company'))
                    ->where('student_statuses.school_year_id', session('current_company_year'))
                    ->where('student_statuses.confirm', 1);
            })
            ->count();

        /*$parents = Student::join( 'registrations', 'registrations.student_id', 'students.id' )
            ->join( 'users', 'users.id', 'registrations.user_id' )
            ->join( 'sections', 'sections.id', 'students.section_id' )
            ->join( 'levels', 'levels.id', 'students.level_id' )
            ->join( 'school_years', 'school_years.id', 'registrations.school_year_id' )
            ->join( 'dormitories', 'dormitories.id', 'registrations.dormitory_id' )
            ->join( 'dormitory_rooms', 'dormitory_rooms.id', 'registrations.dormitory_room_id' )
            ->where( 'students.company_id', session('current_company') )
            ->where( 'students.school_year_id', session('current_company_year') )
            ->where( 'students.confirm', '1' )
            ->count();*/

        $directions = Student::whereHas('active', function ($q) {
            $q->where('students.company_id', session('current_company'))
                ->where('student_statuses.school_year_id', session('current_company_year'))
                ->where('student_statuses.attended', 1);
        })
            ->whereNull('deleted_at')
            ->count();

        $school = School::find(session('current_company'));
        if (is_null($school)) {
            Flash::error(trans('secure.no_schools'));
        }

        if (! is_null($school) && $school->limit_sms_messages > 0 && $school->limit_sms_messages <= $school->sms_messages_year) {
            Flash::error(trans('dashboard.send_to_menu_sms'));
        }
        $per_month = [];
        for ($i = 11; $i >= 0; $i--) {
            $per_month[] =
                [
                    'month' => Carbon::now()->subMonth($i)->format('M'),
                    'year' => Carbon::now()->subMonth($i)->format('Y'),
                    'salary_by_month' => Salary::where(
                        'date',
                        'LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%'
                    )->sum('salary'),
                    'sum_of_payments' => Payment::where(
                        'created_at',
                        'LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%'
                    )->sum('amount'),
                    'sum_of_invoices' => Invoice::where(
                        'created_at',
                        'LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%'
                    )->sum('amount'),
                    'sent_sms_by_month' => SmsMessage::where('created_at', 'LIKE', Carbon::now()->subMonth($i)->format('Y-m').'%')
                        ->where('company_id', session('current_company'))->count('id'),
                ];
        }

        $per_school_year = [];
        $school_years = SchoolYear::all();
        foreach ($school_years as $school_year) {
            $per_school_year[] =
                [
                    'school_year' => $school_year->title,
                    'number_of_students' => Student::where('school_year_id', $school_year->id)
                        ->where('company_id', session('current_company'))->count(),
                    'number_of_sections' => Section::where('school_year_id', $school_year->id)
                        ->where('company_id', session('current_company'))->count(),
                ];
        }
        $students_by_gender = Student::join('users', 'users.id', '=', 'students.user_id')
            ->where('school_year_id', session('current_company_year'))
            ->where('company_id', session('current_company'))
            ->groupBy('users.gender')
            ->select(DB::raw(' count(users.id) as count'), 'users.gender')->get()->toArray();
        foreach ($students_by_gender as &$item) {
            $item['color'] = ($item['gender'] == 1) ? '#0f85ad' : '#c2185b';
            $item['gender'] = ($item['gender'] == 1) ? trans('profile.male') : trans('profile.female');
        }

        $teachers_gender = TeacherSchool::join('users', 'users.id', '=', 'teacher_schools.user_id')
            ->where('company_id', session('current_company'))
            ->groupBy('users.gender')
            ->select(DB::raw(' count(users.id) as count'), 'users.gender')->get()->toArray();
        foreach ($teachers_gender as &$item) {
            $item['color'] = ($item['gender'] == 1) ? '#155599' : '#7c0d18';
            $item['gender'] = ($item['gender'] == 1) ? trans('profile.male') : trans('profile.female');
        }
        $countries = Country::all();

        return [$sections, $teachers, $parents, $directions, $per_month, $per_school_year, $students_by_gender,
            $teachers_gender, $school, $school_years, $countries, ];
    }

    private function super_admin_dashboard()
    {
        $schools = School::where('schools.active', 1)->get();

        $teachers = User::join('role_users', 'role_users.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_users.role_id')
            ->where('roles.slug', 'teacher')->count();

        $parents = User::join('role_users', 'role_users.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_users.role_id')
            ->where('roles.slug', 'parent')->count();

        $directions = Direction::count();

        $per_month = [];
        foreach ($schools as $item) {
            for ($i = 11; $i >= 0; $i--) {
                $per_month[$item->id][] =
                    [
                        'month'           => Carbon::now()->subMonth($i)->format('M'),
                        'year'            => Carbon::now()->subMonth($i)->format('Y'),
                        'sent_sms_by_month' => SmsMessage::where('created_at', 'LIKE', Carbon::now()->subMonth($i)->format('Y-m').'%')
                            ->where('company_id', $item->id)->count('id'),
                    ];
            }
        }

        return [$schools, $teachers, $parents, $directions, $per_month];
    }
}
