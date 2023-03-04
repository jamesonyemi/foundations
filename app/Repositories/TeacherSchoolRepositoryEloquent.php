<?php

namespace App\Repositories;

use App\Helpers\Settings;
use App\Models\Company;
use App\Models\TeacherSchool;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sentinel;
use Session;

class TeacherSchoolRepositoryEloquent implements TeacherSchoolRepository
{
    /**
     * @var TeacherSchool
     */
    private $model;

    /**
     * TimetableRepositoryEloquent constructor.
     * @param TeacherSchool $model
     */
    public function __construct(TeacherSchool $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllForSchool($company_id)
    {
        if (is_null($company_id) && Settings::get('multi_school') == 'no' && isset(Company::first()->id)) {
            $company_id = Company::first()->id;
        }
        $users = new Collection([]);
        $this->model->with('user')
                    ->get()
                    ->each(function ($teacher) use ($users, $company_id) {
                        if ($teacher->company_id == $company_id) {
                            if (isset($teacher->user)) {
                                $users->push($teacher->user);
                            }
                        }
                    });

        return $users;
    }

    public function create(array $data, $activate = true)
    {
        $user_exists = User::where('email', $data['email'])->first();
        $is_user_with_role = false;
        if (isset($user_exists)) {
            $user = Sentinel::findById($user_exists->id);
            $is_user_with_role = $user->inRole('teacher');
        }
        if (! isset($user_exists->id)) {
            $user_tem = Sentinel::registerAndActivate($data, $activate);
            $user = User::find($user_tem->id);
        } else {
            if ($is_user_with_role) {
                $user = $user_exists;
            }
        }

        $user->update([/*'birth_date'=>$data['birth_date'],
                       'birth_city'=>isset($data['birth_city'])?$data['birth_city']:"-",*/
            'gender' => isset($data['gender']) ? $data['gender'] : 0,
            'address' => $data['address'],
            'mobile' => $data['mobile'], ]);

        try {
            $role = Sentinel::findRoleBySlug('teacher');
            $role->users()->attach($user);
        } catch (\Exception $e) {
        }
        if (is_null(session('current_company')) && Settings::get('multi_school') == 'no' && isset(Company::first()->id)) {
            session(['current_company' => Company::first()->id]);
        }
        TeacherSchool::firstOrCreate(['user_id' => $user->id, 'company_id' => session('current_company')]);

        return $user;
    }
}
