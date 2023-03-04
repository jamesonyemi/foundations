<?php

namespace App\Http\Controllers\Secure;

use App\Events\LoginEvent;
use App\Helpers\Flash;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\SharedValuesTrait;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordConfirmRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Secure\SchoolApplyRequest;
use App\Http\Requests\Secure\UserRequest;
use App\Mail\NewApplicantMail;
use App\Models\Admin;
use App\Models\Applicant;
use App\Models\BlockLogin;
use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\LoginHistory;
use App\Models\SchoolAdmin;
use App\Models\Semester;
use App\Models\StudentRegistrationCode;
use App\Models\User;
use App\Models\Visitor;
use App\Notifications\NewApplicant;
use App\Repositories\ApplicationTypeRepository;
use App\Repositories\CountryRepository;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Laravel\Facades\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Sentinel;
use Session;

class AuthController extends Controller
{
    use SharedValuesTrait;

    protected $redirectTo = '/';

    /**
     * @var CountryRepository
     */
    private $countryRepository;

    private $applicationTypeRepository;

    public function __construct(
        CountryRepository $countryRepository,
        ApplicationTypeRepository $applicationTypeRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->applicationTypeRepository = $applicationTypeRepository;
    }

    public function index(Request $request)
    {
        if (Sentinel::check()) {
            return redirect('/');
        }
        $url = $request->url();
        $school = Company::where('url', $url)->first();

        return view('login', compact('school'));
    }

    /**
     * Account sign in.
     *
     * @return View
     */
    public function getSignin(Request $request)
    {
        if (Sentinel::check()) {
            return redirect('/');
        }
        $url = $request->url();
        $company = Company::first();

        return view('login', compact('company'));
    }

    /**
     * Account sign up.
     *
     * @return View
     */
    public function getSignup()
    {
        if (Sentinel::check()) {
            return redirect('/');
        }

        return view('register');
    }

    public function getApply($school, $year)
    {
        if (Sentinel::check()) {
            return redirect('/');
        }

        $countries = $this->countryRepository
            ->getAll()
            ->orderBy('name', 'asc')
            ->get()
            ->pluck('name', 'id')
            ->prepend(trans('student.select_country'), '')
            ->toArray();

        $applicationTypes = $this->applicationTypeRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('applicant.select_application_type'), '')
            ->toArray();

        return view('apply', compact('school', 'year', 'countries', 'applicationTypes'));
    }

    public function getSchoolApply()
    {
        if (Sentinel::check()) {
            return redirect('/');
        }

        return view('schoolApply');
    }

    /**
     * Account sign in form processing.
     *
     * @return Redirect
     */
    public function postSignin(LoginRequest $request)
    {
        try {
            if ($user = $this->tryAuthenticate($request)) {
                Sentinel::login($user);
                if (! is_null(BlockLogin::where('user_id', $user->id)->first())) {
                    Flash::error(trans('auth.account_suspended'));
                    Sentinel::logout(null, true);

                    return back()->withInput();
                }
                /*Flash::success(trans('auth.signin_success'));*/

                $this->shareValues();

                $userLogin = new LoginHistory();
                $userLogin->user_id = $user->id;
                $userLogin->ip_address = $request->ip();
                $userLogin->user_agent = \Request::header('User-Agent');
                $userLogin->company_id = session('current_company');
                $userLogin->save();

                /*event(new LoginEvent($user));*/

                return redirect()->intended('/');
            }
            Flash::error(trans('auth.login_params_not_valid'));
        } catch (NotActivatedException $e) {
            Flash::error(trans('auth.account_not_activated'));
        } catch (ThrottlingException $e) {
            $delay = $e->getDelay();
            Flash::error(trans('auth.account_suspended').$delay.trans('auth.second'));
        }

        return back()->withInput();
    }

    private function tryAuthenticate($request)
    {
        $user = User::where('email', $request->get('mobile_email'))
                    ->orWhere('mobile', $request->get('mobile_email'))->first();
        if (! is_null($user)) {
            if (Hash::check($request->get('password'), $user->password)) {
                return $user;
            }

            return null;
        }

        return null;
    }

    /**
     * Account sign up form processing.
     *
     * @return Redirect
     */
    public function postSignup(UserRequest $request)
    {

            try {
                $user = Sentinel::registerAndActivate([
                    'first_name' => $request['first_name'],
                    'last_name' => $request['last_name'],
                    'email' => $request['email'],
                    'mobile' => $request['mobile'],
                    'password' => $request['password'],
                ]);
                $role = Sentinel::findRoleBySlug('employee');
                if (isset($role)) {
                    $role->users()->attach($user);

                        $company = Company::firstOrCreate
                        (
                            [
                            'title'=>$request['organization'],
                            'active'=>'Yes',
                            'sector_id'=>1,
                            ]
                        );

                        Employee::create
                        (
                            [
                            'user_id'=>$user->id,
                            'sID'=>1,
                            'company_id'=> $company->id,
                            ]
                        );
                    }


                Sentinel::loginAndRemember($user);

                Flash::success(trans('auth.signup_success'));

                return redirect()->intended('/');
            } catch (UserExistsException $e) {
                Flash::warning(trans('auth.account_already_exists'));
            }

            return back()->withInput();

    }



    public function reminders()
    {
        return view('reminders.create');
    }

    public function remindersStore(PasswordResetRequest $request)
    {
        $userFind = User::where('email', $request->email)->first();
        if (isset($userFind->id)) {
            $user = Sentinel::findById($userFind->id);
            $reminder = Reminder::create($user);

            $data = [
                'email' => $user->email,
                'name' => $userFind->full_name,
                'subject' => trans('auth.reset_your_password'),
                'code' => $reminder->code,
                'id' => $user->id,
            ];
            Mail::send('emails.reminder', $data, function ($message) use ($data) {
                $message->to($data['email'], $data['name'])->subject($data['subject']);
            });

            Session::flash('email_message_success', trans('auth.reset_password_link_send'));

            return back();
        }
        Session::flash('email_message_warning', trans('auth.user_dont_exists'));

        return back();
    }

    public function edit($id, $code)
    {
        $user = Sentinel::findById($id);
        if (Reminder::exists($user, $code)) {
            return view('reminders.edit', ['id' => $id, 'code' => $code]);
        } else {
            return redirect('/signin');
        }
    }

    public function update($id, $code, PasswordConfirmRequest $request)
    {
        $user = Sentinel::findById($id);
        $reminder = Reminder::exists($user, $code);
        //incorrect info was passed.
        if ($reminder == false) {
            Flash::error(trans('auth.reset_password_failed'));

            return redirect('/');
        }
        Reminder::complete($user, $code, $request->password);
        if ($user->moodleUser) {
            $user->moodleUser->password = bcrypt($request->password);
            $user->moodleUser->save();
        }
        Flash::success(trans('auth.reset_password_success'));

        return redirect('/signin');
    }

    /**
     * Logout page.
     *
     * @return Redirect
     */
    public function getLogout()
    {
        Sentinel::logout(null, true);
        Flash::success(trans('auth.successfully_logout'));
        Session::flush();

        return redirect('about_us');
    }

    public function screenlock()
    {
        $cookie = \Cookie::forever('lock', '1');

        return \Response::view('screen_lock', Sentinel::getUser()->id)->withCookie($cookie);
    }

    public function resend_verify_email()
    {
        $input = Admin::where('email', admin()->email)->first();
        if ($input) {
            $code = Str::random(60);
            $input->email_token = $code;
            $input->save();

            \Illuminate\Support\Facades\Session::flash('success', trans('messages.passwordReset'));
            //---- RESET EMAIL SENDING-----

            $emailInfo = ['from_email' => $this->setting->email,
                'from_name' => $this->setting->name,
                'to' => $input['email'],
                'active_company' => admin()->company, ];
            $fieldValues = ['NAME' => $input->name, 'VERIFY_LINK' => \HTML::link('admin/verify_email/'.$code)];

            EmailTemplate::prepareAndSendEmail('NEW_ADMIN_EMAIL_VERIFICATION', $emailInfo, $fieldValues);
        } else {
            Session::flash('error', trans('messages.emailNotFound'));
        }

        return Redirect::route('admin.dashboard.index');
    }

    public function support()
    {
        $this->pageTitle = trans('core.support');

        return view('admin.support', $this->data);
    }

    public function screenlockModal()
    {
        $cookie = \Cookie::forever('lock', '1');
        \Session::put('back_url_'.Sentinel::getUser()->id, \Request::server('HTTP_REFERER'));

        return \Response::json([
            'status' => 'success',
            'back' => \Session::get('back_url'),
        ])->withCookie($cookie);
    }
}
