<?php

namespace App\Repositories;

use App\Models\ParentStudent;
use App\Models\User;
use Sentinel;

class ParentStudentRepositoryEloquent implements ParentStudentRepository
{
    /**
     * @var ParentStudent
     */
    private $model;

    /**
     * ParentStudentRepositoryEloquent constructor.
     * @param ParentStudent $model
     */
    public function __construct(ParentStudent $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function create(array $data, $activate = true)
    {
        $user_exists = User::where('email', $data['email'])->first();
        $is_user_with_role = false;
        if (isset($user_exists)) {
            $user = Sentinel::findById($user_exists->id);
            $is_user_with_role = $user->inRole('parent');
        }
        if (! isset($user_exists->id)) {
            $user_tem = Sentinel::registerAndActivate($data, $activate);
            $user = User::find($user_tem->id);
        } else {
            if ($is_user_with_role) {
                $user = $user_exists;
            }
        }

        $user->update(['birth_date'=>$data['birth_date'],
            'birth_city'=>isset($data['birth_city']) ? $data['birth_city'] : '-',
            'gender' => isset($data['gender']) ? $data['gender'] : 0,
            'address' => $data['address'],
            'mobile' => $data['mobile'], ]);

        try {
            $role = Sentinel::findRoleBySlug('parent');
            $role->users()->attach($user);
        } catch (\Exception $e) {
        }
        $student_user = User::where('email', $data['student_email'])->first();
        if (! is_null($student_user)) {
            $parent = new ParentStudent();
            $parent->user_id_student = $student_user->id;
            $parent->user_id_parent = $user->id;
            $parent->activate = 1;
            $parent->save();
        }

        return $user;
    }
}
