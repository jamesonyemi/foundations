<?php

namespace App\Repositories;

use App\Models\Company;
use Sentinel;

class SchoolRepositoryEloquent implements SchoolRepository
{
    /**
     * @var Company
     */
    private $model;

    /**
     * SchoolRepositoryEloquent constructor.
     *
     * @param Company $model
     */
    public function __construct(Company $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model
            ->select('companies.*');
    }

    public function getAllForGroup($group_id)
    {
        return $this->model->whereHas('sector', function ($query) use ($group_id) {
            $query->where('group_id', $group_id);
        });
    }
    public function getAllForSector($sector_id)
    {
        return $this->model->where('sector_id', $sector_id);

    }

    public function getAllAdmin()
    {
        return $this->model->join('school_admins', 'companies.id', '=', 'school_admins.company_id')
                           ->where('school_admins.user_id', Sentinel::getUser()->id)
                           ->where('companies.active', 1)
                           ->distinct()
                           ->select('companies.*');
    }

    public function getAllTeacher()
    {
        return $this->model->join('teacher_subjects', 'companies.id', '=', 'teacher_subjects.company_id')
                           ->where('teacher_subjects.teacher_id', Sentinel::getUser()->id)
                           ->where('companies.active', 1)
                           ->distinct()
                           ->select('companies.*');
    }

    public function getAllStudent()
    {
        return $this->model->join('departments', 'companies.id', '=', 'departments.company_id')
                           ->join('students', 'students.section_id', '=', 'departments.id')
                           ->where('companies.active', 1)
                           ->where('students.user_id', Sentinel::getUser()->id)
                           ->distinct()
                           ->select('companies.*');
    }

    public function getAllAluministudents($company_id, $schoolYearId)
    {
        return $this->model->join('departments', 'companies.id', '=', 'departments.company_id')
                           ->join('student_groups', 'student_groups.section_id', '=', 'departments.id')
                           ->join('directions', function ($join) {
                               $join->on('student_groups.direction_id', '=', 'directions.id');
                               $join->on('directions.duration', '=', 'student_groups.class');
                           })
                           ->join('student_student_group', 'student_student_group.student_group_id', '=', 'student_groups.id')
                           ->where('companies.active', 1)
                           ->where('companies.id', $company_id)
                           /*->where( 'departments.company_year_id', $schoolYearId )*/
                           ->distinct()
                           ->select('student_student_group.student_id');
    }

    public function getAllCanApply()
    {
        return $this->model->where('can_apply', 1)
            ->select('companies.*');
    }

    public function getAllApplicant()
    {
        return $this->model->join('applicants', 'companies.id', '=', 'applicants.company_id')
            ->where('applicants.user_id', Sentinel::getUser()->id)
            ->where('companies.active', 1)
            ->distinct()
            ->select('companies.*');
    }
}
