<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\Thumbnail;
use App\Http\Requests\Auth\ProfileChangeRequest;
use App\Models\BlockLogin;
use App\Models\CertificateUser;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSupervisor;
use App\Models\Position;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sentinel;
use Session;

class ProfileController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    public function __construct(EmployeeRepository $employeeRepository,
                                SectionRepository $sectionRepository)
    {
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->sectionRepository = $sectionRepository;
    }

    /**
     * Profile page.
     *
     * @return Redirect
     */
    public function getProfile()
    {
        if (! Sentinel::check()) {
            return redirect('/');
        }

        $title = trans('auth.user_profile');
        $user = User::find(Sentinel::getUser()->id);

        return view('profile', compact('title', 'user'));
    }

    public function getAccount()
    {
        if (! Sentinel::check()) {
            return redirect('/');
        }
        $title = trans('auth.edit_profile');
        $user = User::find(Sentinel::getUser()->id);
        $employee = Employee::find(session('current_employee'));


        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Supervisor', 0)
            ->toArray();


        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), '')
            ->toArray();

        return view('account', compact('title', 'user', 'employee', 'employees', 'sections'));
    }

    public function postAccount(ProfileChangeRequest $request)
    {
        if (! Sentinel::check()) {
            return redirect('/');
        }

        $user = User::find(Sentinel::getUser()->id);
        if ($request->hasFile('user_avatar_file') != '') {
            $file = $request->file('user_avatar_file');
            $extension = $file->getClientOriginalExtension();
            $picture = Str::random(8) .'.'.$extension;

            $destinationPath = public_path().'/uploads/avatar/';
            $file->move($destinationPath, $picture);
            Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
            $user->picture = $picture;
        }

        /*$employee = Employee::find((session('current_employee')));*/

        /*$employee->section_id = $request['section_id'];*/
        /*$employee->position_id = $request['position_id'];*/
        /*$employee->save();*/

        /*EmployeeSupervisor::where('employee_id', $employee->id)->delete();*/

        /*foreach ($request['employee_supervisor_id']  as $index => $supervisor_id)
        {
            EmployeeSupervisor::firstOrCreate
            (
                [
                    'employee_id' => $employee->id,
                    'employee_supervisor_id' => $supervisor_id
                ]
            );

        }*/

        $user->update($request->except('user_avatar_file', 'password', 'password_confirmation'));
        $user->password = bcrypt($request->password);
        $user->save();
        Flash::success(trans('auth.successfully_change_profile'));

        return redirect('/');
    }

    public function postWebcam(Request $request)
    {
        $user = User::find(Sentinel::getUser()->id);
        if (isset($request['photo_url'])) {
            $output_file = uniqid().'.jpg';
            $ifp = fopen(public_path().'/uploads/avatar/'.$output_file, 'wb');
            $data = explode(',', $request['photo_url']);
            fwrite($ifp, base64_decode($data[1]));
            fclose($ifp);
            $user->picture = $output_file;
        }
        $user->update($request->except('photo_url', 'password', 'password_confirmation'));
    }

    public function getCertificate()
    {
        if (! Sentinel::check()) {
            return redirect('/');
        }
        $user = User::find(Sentinel::getUser()->id);
        $certificates = CertificateUser::join('certificates', 'certificates.id', '=', 'certificate_user.certificate_id')
            ->whereNull('certificate_user.deleted_at')
            ->where('user_id', $user->id)
            ->select('certificates.*')->get();
        $title = trans('auth.my_certificate');

        return view('certificate', compact('title', 'certificates', 'user'));
    }

    public function loginAsUser(Request $request, User $user, Company $company)
    {
        /*if (Sentinel::getUser()->inRole('super_admin')) {
            session(['was_super_admin' => Sentinel::getUser()->id]);
        } elseif (Sentinel::getUser()->inRole('super_admin')) {
            session(['was_admin' => Sentinel::getUser()->id]);
        } else {
            return back();
        }*/

        $user = Sentinel::findById($user->id);
        Sentinel::login($user);

        session(['current_company' => $company->id]);

        $this->shareValues();

        return redirect('/');
    }

    public function backToSuperAdmin(Request $request)
    {
        if (! is_null(session('was_super_admin'))) {
            $user = Sentinel::findById(session('was_super_admin'));
            Sentinel::login($user);
        }

        return redirect('/');
    }

    public function backToAdmin(Request $request)
    {
        if (! is_null(session('was_admin'))) {
            $user = Sentinel::findById(session('was_admin'));
            Sentinel::login($user);
        }

        return redirect('/');
    }

    public function setYear($id)
    {
        session(['current_company_year' => $id]);

        return redirect()->back();
    }

    public function setSemester($id)
    {
        session(['current_company_semester' => $id]);

        return redirect('/');
    }

    public function setSchool($id)
    {
        session(['current_company' => $id]);

        return redirect('/');
    }

    public function setGroup($id)
    {
        session(['current_student_group' => $id]);

        return redirect('/');
    }

    public function setStudent($id)
    {
        session(['current_student_id' => $id]);

        return redirect('/');
    }
}
