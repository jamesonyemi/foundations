<?php



namespace App\Http\Controllers\Traits;

use App\Models\AccountantSchool;
use App\Models\HumanResourceSchool;
use App\Models\LibrarianSchool;
use App\Models\Company;
use App\Models\SchoolAdmin;
use App\Models\CompanyYear;
use App\Models\Department;
use App\Models\Semester;
use App\Models\Employee;
use App\Models\StudentGroup;
use App\Models\TeacherSubject;
use DB;
use Sentinel;
use Session;

trait SchoolYearTrait
{
    /*    public function currentSchoolYear($current_company_year_id)
        {
            if (!isset($current_company_year_id) || $current_company_year_id == "") {
                $company_year = CompanyYear::where('active', '=', 1)->orderBy('id', 'DESC')->first();
            } else {
                $company_year = CompanyYear::where('active', '=', 1)->where('id', $current_company_year_id)->first();
                if (!isset($company_year->id)) {
                    $company_year = CompanyYear::where('active', '=', 1)->orderBy('id', 'DESC')->first();
                }
            }
            $value = isset($company_year) ? $company_year->title : "--";
            $id = isset($company_year) ? $company_year->id : 0;

            $company_years = CompanyYear::where('active', '=', 1)->orderBy('id', 'DESC')->select(['id', 'title'])->get();

            return [
                'current_company_value' => $value,
                'current_company_id' => $id,
                'other_company_years' => $company_years,
            ];
        }*/

    public function currentSchoolYear($current_company_year_id, $company_id)
    {
        $group_id = Company::find($company_id)->sector->group_id;
        if (!isset($current_company_year_id) || $current_company_year_id == "") {
            $company_year = CompanyYear::where('group_id', '=', $group_id)->orderBy('id', 'DESC')->first();
        } else {
            $company_year = CompanyYear::where('id', $current_company_year_id)->first();
            if (!isset($company_year->id)) {
                $company_year = CompanyYear::where('group_id', '=', $group_id)->orderBy('id', 'DESC')->first();
            }
        }
        $value = isset($company_year) ? $company_year->title : "--";
        $id = isset($company_year) ? $company_year->id : 0;

        $company_years = CompanyYear::where('group_id', '=', $group_id)->orderBy('id', 'DESC')
            ->select(array('id', 'title'))->get();

        return array(
            'current_company_value' => $value,
            'current_company_id' => $id,
            'other_company_years' => $company_years,
        );
    }



/*    public function previousSchoolYear($current_company_year_id, $company_id)
    {
        $group_id = Company::find($company_id)->sector->group_id;
        if (!isset($current_company_year_id) || $current_company_year_id == "") {
            $company_year = CompanyYear::where('group_id', '=', $group_id)->orderBy('id', 'DESC')->skip(1)->first();
        } else {
            $company_year = CompanyYear::where('id', $current_company_year_id)->skip(1)->first();
            if (!isset($company_year->id)) {
                $company_year = CompanyYear::where('group_id', '=', $group_id)->orderBy('id', 'DESC')->skip(1)->first();
            }
        }

        $id = isset($company_year) ? $company_year->id : 0;



        return array(
            'current_company_id' => $id,
        );
    }*/


    public function currentSchool($current_company_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = Company::where('companies.active', 1)
                ->orderBy('id', 'DESC')->first();
        } else {
            $school = Company::where('companies.active', 1)
                ->where('id', $current_company_id)->first();
            if (!isset($school->id)) {
                $school = Company::where('active', 1)->orderBy('id', 'DESC')->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;

        $schools = Company::where('companies.active', 1)->orderBy('id', 'DESC')->select(['id', 'title'])->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }

    public function currentSchoolAccountant($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = AccountantSchool::join('schools', 'companies.id', '=', 'accountant_companies.company_id')
                ->where('user_id', $user_id)
                ->where('companies.active', 1)
                ->orderBy('companies.id', 'DESC')
                ->select('companies.*')->first();
        } else {
            $school = AccountantSchool::join('schools', 'companies.id', '=', 'accountant_companies.company_id')
                ->where('companies.id', $current_company_id)
                ->where('companies.active', 1)
                ->where('user_id', $user_id)
                ->select('companies.*')->first();
            if (!isset($school->id)) {
                $school = AccountantSchool::join('schools', 'companies.id', '=', 'accountant_companies.company_id')
                    ->where('user_id', $user_id)
                    ->where('companies.active', 1)
                    ->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;


        $schools = AccountantSchool::join('schools', 'companies.id', '=', 'accountant_companies.company_id')
            ->where('user_id', $user_id)
            ->where('companies.active', 1)
            ->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }



    public function currentSchoolHumanResource($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = HumanResourceSchool::join('schools', 'companies.id', '=', 'human_resource_companies.company_id')
                ->where('user_id', $user_id)
                ->where('companies.active', 1)
                ->orderBy('companies.id', 'DESC')
                ->select('companies.*')->first();
        } else {
            $school = HumanResourceSchool::join('schools', 'companies.id', '=', 'human_resource_companies.company_id')
                ->where('companies.id', $current_company_id)
                ->where('companies.active', 1)
                ->where('user_id', $user_id)
                ->select('companies.*')->first();
            if (!isset($school->id)) {
                $school = HumanResourceSchool::join('schools', 'companies.id', '=', 'human_resource_companies.company_id')
                    ->where('user_id', $user_id)
                    ->where('companies.active', 1)
                    ->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;


        $schools = HumanResourceSchool::join('schools', 'companies.id', '=', 'human_resource_companies.company_id')
            ->where('user_id', $user_id)
            ->where('companies.active', 1)
            ->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }



    public function currentSchoolLibrarian($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = LibrarianSchool::join('schools', 'companies.id', '=', 'librarian_companies.company_id')
                ->where('user_id', $user_id)
                ->where('companies.active', 1)
                ->orderBy('companies.id', 'DESC')
                ->select('companies.*')->first();
        } else {
            $school = LibrarianSchool::join('schools', 'companies.id', '=', 'librarian_companies.company_id')
                ->where('companies.id', $current_company_id)
                ->where('companies.active', 1)
                ->where('user_id', $user_id)
                ->select('companies.*')->first();
            if (!isset($school->id)) {
                $school = LibrarianSchool::join('schools', 'companies.id', '=', 'librarian_companies.company_id')
                    ->where('user_id', $user_id)
                    ->where('companies.active', 1)
                    ->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;


        $schools = LibrarianSchool::join('schools', 'companies.id', '=', 'librarian_companies.company_id')
            ->where('user_id', $user_id)
            ->where('companies.active', 1)
            ->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }





    public function currentSchoolAdmin($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = SchoolAdmin::join('schools', 'companies.id', '=', 'school_admins.company_id')
                ->where('user_id', $user_id)
                ->where('companies.active', 1)
                ->orderBy('companies.id', 'DESC')
                ->select('companies.*')->first();
        } else {
            $school = SchoolAdmin::join('schools', 'companies.id', '=', 'school_admins.company_id')
                ->where('companies.id', $current_company_id)
                ->where('companies.active', 1)
                ->where('user_id', $user_id)
                ->select('companies.*')->first();
            if (!isset($school->id)) {
                $school = SchoolAdmin::join('schools', 'companies.id', '=', 'school_admins.company_id')
                    ->where('user_id', $user_id)
                    ->where('companies.active', 1)
                    ->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;

        $schools = SchoolAdmin::join('schools', 'companies.id', '=', 'school_admins.company_id')
            ->where('user_id', $user_id)
            ->where('companies.active', 1)
            ->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }

    public function currentSchoolYearTeacher($current_company_year_id, $user_id)
    {
        $company_year_org = CompanyYear::join('teacher_subjects', 'teacher_subjects.company_year_id', '=', 'company_years.id')
            ->whereNull('teacher_subjects.deleted_at')
            ->where('teacher_subjects.teacher_id', $user_id)
            ->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct();
        if (!isset($current_company_year_id) || $current_company_year_id == "") {
            $company_year = $company_year_org->first();
        } else {
            $company_year = CompanyYear::where('id', $current_company_year_id)->first();
            if (!isset($company_year->id)) {
                $company_year = $company_year_org = CompanyYear::join('teacher_subjects', 'teacher_subjects.company_year_id', '=', 'company_years.id')
                    ->whereNull('teacher_subjects.deleted_at')
                    ->where('teacher_subjects.teacher_id', $user_id)
                    ->orderBy('company_years.id', 'DESC')
                    ->select('company_years.*')
                    ->distinct()->first();
            }
        }
        $value = isset($company_year) ? $company_year->title : "--";
        $id = isset($company_year) ? $company_year->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;

        $company_years = CompanyYear::join('teacher_subjects', 'teacher_subjects.company_year_id', '=', 'company_years.id')
            ->whereNull('teacher_subjects.deleted_at')
            ->where('teacher_subjects.teacher_id', $user_id)
            ->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct()->get();

        return [
            'current_company_value' => $value,
            'current_company_id' => $id,
            'current_company_type' => $type,
            'other_company_years' => $company_years,
        ];
    }

    public function currentSchoolTeacher($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = Company::leftJoin('teacher_schools', 'companies.id', '=', 'teacher_companies.company_id')
                ->leftJoin('teacher_subjects', 'companies.id', '=', 'teacher_subjects.company_id')
                ->where('teacher_id', $user_id)
                ->orWhere('user_id', $user_id)
                ->where('companies.active', 1)
                ->orderBy('companies.id', 'DESC')
                ->select('companies.*')->distinct()->first();
        } else {
            $school = Company::leftJoin('teacher_schools', 'companies.id', '=', 'teacher_companies.company_id')
                ->leftJoin('teacher_subjects', 'companies.id', '=', 'teacher_subjects.company_id')
                ->where('teacher_id', $user_id)
                ->orWhere('user_id', $user_id)
                ->where('companies.id', $current_company_id)
                ->where('companies.active', 1)
                ->select('companies.*')->distinct()->first();
            if (!isset($school->id)) {
                $school = Company::leftJoin('teacher_schools', 'companies.id', '=', 'teacher_companies.company_id')
                    ->leftJoin('teacher_subjects', 'companies.id', '=', 'teacher_subjects.company_id')
                    ->where('teacher_id', $user_id)
                    ->orWhere('user_id', $user_id)
                    ->where('teacher_id', $user_id)
                    ->where('companies.active', 1)
                    ->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->distinct()->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;

        $schools = $school = Company::leftJoin('teacher_schools', 'companies.id', '=', 'teacher_companies.company_id')
            ->leftJoin('teacher_subjects', 'companies.id', '=', 'teacher_subjects.company_id')
            ->where('teacher_id', $user_id)
            ->orWhere('user_id', $user_id)
            ->where('companies.active', 1)
            ->orderBy('id', 'DESC')->distinct()->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }

    /*    public function currentSchoolYearSchoolStudent($current_company_year_id, $user_id, $company_id)
        {

            $company_year = CompanyYear::where('company_years.active', '=', 1)->orderBy('id', 'DESC')->first();;


            $value = isset($company_year) ? $company_year->title : "--";
            $id = isset($company_year) ? $company_year->id : 0;
            $type = isset($school) ? $school->school_type_id : 0;

            $company_years = CompanyYear::orderBy('company_years.id', 'DESC')
                ->select('company_years.*')
                ->distinct()->get();

            return [
                'current_company_value' => $value,
                'current_company_id' => $id,
                'current_company_type' => $type,
                'other_company_years' => $company_years,
            ];
        }*/


    public function currentSchoolYearSchoolStudent($current_company_year_id, $user_id, $company_id)
    {
        $group_id = Company::find($company_id)->sector->group_id;
        $company_year = CompanyYear::where('group_id', group_id)->orderBy('id', 'DESC')->first();
        $value = isset($company_year) ? $company_year->title : "--";
        $id = isset($company_year) ? $company_year->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;
        $company_years = CompanyYear::where('group_id', group_id)->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct()->get();

        return [
            'current_company_value' => $value,
            'current_company_id' => $id,
            'current_company_type' => $type,
            'other_company_years' => $company_years,
        ];
    }

    public function currentSchoolYearEmployee($current_company_year_id, $user_id, $company_id)
    {
        $group_id = Company::find($company_id)->sector->group_id;
        $company_year_org = CompanyYear::where('group_id', $group_id)->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct()->first();
        if (!isset($current_company_year_id) || $current_company_year_id == "") {
            $company_year = $company_year_org;
        } else {
            $company_year = CompanyYear::where('id', $current_company_year_id)->first();
            if (!isset($company_year->id)) {
                $company_year = $company_year_org;
            }
        }
        $value = isset($company_year) ? $company_year->title : "--";
        $id = isset($company_year) ? $company_year->id : 0;

        $company_years = CompanyYear::where('group_id', $group_id)->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct()->get();

        return array(
            'current_company_value' => $value,
            'current_company_id' => $id,
            'other_company_years' => $company_years,
        );
    }




    public function currentSchoolYearSchoolApplicant($current_company_year_id, $user_id, $company_id)
    {
        $company_year_org = CompanyYear::join('applicants', 'applicants.company_year_id', '=', 'company_years.id')
            ->whereNull('applicants.deleted_at')
            ->where('applicants.user_id', $user_id)
            ->where('applicants.company_id', $company_id)
            /*->where('company_years.active', '=', 1)*/
            ->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct()->first();
        if (!isset($current_company_year_id) || $current_company_year_id == "") {
            $company_year = $company_year_org;
        } else {
            $company_year = CompanyYear::where('id', $current_company_year_id)->first();
            if (!isset($company_year->id)) {
                $company_year = $company_year_org;
            }
        }
        $value = isset($company_year) ? $company_year->title : "--";
        $id = isset($company_year) ? $company_year->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;

        $company_years = CompanyYear::join('applicants', 'applicants.company_year_id', '=', 'company_years.id')
            ->whereNull('applicants.deleted_at')
            ->where('applicants.user_id', $user_id)
            ->where('applicants.company_id', $company_id)
            ->orderBy('company_years.id', 'DESC')
            ->select('company_years.*')
            ->distinct()->get();

        return [
            'current_company_value' => $value,
            'current_company_id' => $id,
            'current_company_type' => $type,
            'other_company_years' => $company_years,
        ];
    }


    public function currentSchoolStudent($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = Company::join('employees', 'employees.company_id', '=', 'companies.id')
                ->orderBy('companies.id', 'DESC')
                ->select('companies.*')->distinct()->first();
        } else {
            $school = Company::join('employees', 'employees.company_id', '=', 'companies.id')
                ->where('companies.id', $current_company_id)
                ->select('companies.*')->first();
            $employee = Employee::where('user_id', $user_id)->first();
            if (!isset($school->id)) {
                $school = Company::join('employees', 'employees.company_id', '=', 'companies.id')
                    ->where('employees.user_id', $user_id)->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->distinct()->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;
        $employee_id = isset($employee) ? $employee->id : 0;

        $schools = Company::join('sections', 'companies.id', '=', 'sections.company_id')
            ->join('employees', 'employees.section_id', '=', 'sections.id')
            ->where('user_id', $user_id)->distinct()->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
            'current_employee' => $employee_id,
        ];
    }


    public function currentSchoolEmployee($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = Company::join('employees', 'employees.company_id', '=', 'companies.id')
                ->where('employees.user_id', $user_id)->orderBy('companies.id', 'DESC')
                ->select('companies.*')->distinct()->first();
        } else {
            $school = Company::where('companies.id', $current_company_id)->select('companies.*')->first();
            /*$employee = Employee::where('user_id', $user_id)->first();*/
            if (!isset($school->id)) {
                $school = Company::orderBy('companies.id', 'DESC')
                    ->select('companies.*')->distinct()->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;
        $sector = isset($school) ? $school->sector_id : 0;
        $employee_id = isset($employee) ? $employee->id : 0;

        $schools = Company::join('employees', 'employees.company_id', '=', 'companies.id')
            ->where('employees.user_id', $user_id)->distinct()->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
            'current_employee' => $employee_id,
            'current_company_sector' => $sector,
        ];
    }



    public function currentSchoolApplicant($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = Company::join('sections', 'companies.id', '=', 'sections.company_id')
                ->join('applicants', 'applicants.section_id', '=', 'sections.id')
                ->where('applicants.user_id', $user_id)->orderBy('companies.id', 'DESC')
                ->select('companies.*')->distinct()->first();
        } else {
            $school = Company::join('sections', 'companies.id', '=', 'sections.company_id')
                ->join('applicants', 'applicants.section_id', '=', 'sections.id')
                ->where('companies.id', $current_company_id)->where('user_id', $user_id)
                ->select('companies.*')->first();
            if (!isset($school->id)) {
                $school = Company::join('sections', 'companies.id', '=', 'sections.company_id')
                    ->where('applicants.user_id', $user_id)->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->distinct()->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0; $id = isset($school) ? $school->id : 0;
        $type = isset($school) ? $school->school_type_id : 0;

        $schools = Company::join('sections', 'companies.id', '=', 'sections.company_id')
            ->join('applicants', 'applicants.section_id', '=', 'sections.id')
            ->where('applicants.user_id', $user_id)->distinct()->orderBy('id', 'DESC')->select('companies.*')->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'current_company_type' => $type,
            'other_schools' => $schools,
        ];
    }


    public function semestersForSchoolYear($company_year)
    {
        $school_semesters = Semester::orderBy('start', 'DESC')
            ->where('company_year_id', $company_year)
            ->select(['id', 'title', 'from', 'start', 'end'])->get();

        return ['school_semesters' => $school_semesters];
    }

    public function setSessionSchoolYears($result)
    {
        $value = $result['current_company_value'];
        $id = $result['current_company_id'];
        $company_years = $result['other_company_years'];

        session(['current_company_year' => $id]);

        view()->share('current_company_year', $value);
        view()->share('current_company_year_id', $id);
        view()->share('company_years', $company_years);
    }

    public function setSessionSchool($result)
    {
        $value = $result['current_company_item'];
        $id = $result['current_company'];
        $schools = $result['other_schools'];
        $sector = $result['current_company_sector'];
        $type = $result['current_company_type'];

        session(['current_company' => $id]);
        session(['current_company_type' => $type]);
        session(['current_company_sector' => $sector]);


        view()->share('current_company_item', $value);
        view()->share('current_company', $id);
        view()->share('current_company_type', $type);
        view()->share('schools', $schools);
        view()->share('current_company_sector', $sector);
    }

    public function currentTeacherStudentGroupSchool($student_group, $current_company_year, $current_company)
    {
        $teacher_groups = TeacherSubject::where('teacher_id', '=', $this->user->id)
            ->where('company_year_id', '=', $current_company_year)
            ->where('company_id', '=', $current_company)
            ->orderBy('id', 'DESC')
            ->whereNull('deleted_at')
            ->distinct()->pluck('student_group_id')->toArray();

        if (!isset($student_group) || $student_group == "") {
            $student_groups = StudentGroup::join('directions', 'directions.id', '=', 'student_groups.direction_id')
                ->whereNull('directions.deleted_at')
                ->whereIn('student_groups.id', $teacher_groups)
                ->orderBy('student_groups.id', 'DESC')
                ->select('student_groups.id', 'student_groups.title')->first();
        } else {
            $student_groups = StudentGroup::join('directions', 'directions.id', '=', 'student_groups.direction_id')
                ->whereNull('directions.deleted_at')
                ->whereIn('student_groups.id', $teacher_groups)
                ->where('student_groups.id', $student_group)
                ->select('student_groups.id', 'student_groups.title')->first();
            if (!isset($student_groups->id)) {
                $student_groups = StudentGroup::join('directions', 'directions.id', '=', 'student_groups.direction_id')
                    ->whereNull('directions.deleted_at')
                    ->whereIn('student_groups.id', $teacher_groups)
                    ->orderBy('student_groups.id', 'DESC')
                    ->select('student_groups.id', 'student_groups.title')->first();
            }
        }
        $student_group = isset($student_groups) ? $student_groups->title : "--";
        $student_group_id = isset($student_groups) ? $student_groups->id : 0;
        $student_groups = StudentGroup::join('directions', 'directions.id', '=', 'student_groups.direction_id')
            ->whereNull('directions.deleted_at')
            ->whereIn('student_groups.id', $teacher_groups)
            ->orderBy('student_groups.id', 'DESC')
            ->select('student_groups.id', 'student_groups.title')->get();

        $head_teacher = Department::where('company_id', '=', $current_company)
            ->where('section_teacher_id', '=', Sentinel::getUser()->id)->count();
        return [
            'current_student_group' => $student_group,
            'current_student_group_id' => $student_group_id,
            'student_groups' => $student_groups,
            'head_teacher' => $head_teacher,
        ];
    }

    public function setSessionTeacherStudentGroups($result)
    {
        $current_student_group = $result['current_student_group'];
        $current_student_group_id = $result['current_student_group_id'];
        $student_groups = $result['student_groups'];
        $head_teacher = $result['head_teacher'];

        session(['current_student_group' => $current_student_group_id]);

        view()->share('current_student_group', $current_student_group);
        view()->share('current_student_group_id', $current_student_group_id);
        view()->share('student_groups', $student_groups);
        view()->share('head_teacher', $head_teacher);
    }

    public function currentStudentSectionSchool($student_section, $company_year_id, $company_id)
    {
        if (!isset($student_section) || $student_section == "") {
            $student_sections = Employee::join('sections', 'sections.id', '=', 'students.section_id')
                ->where('students.user_id', '=', $this->user->id)
                ->where('students.company_year_id', $company_year_id)
                ->where('students.company_id', $company_id)
                ->whereNull('sections.deleted_at')
                ->orderBy('students.company_year_id', 'DESC')
                ->select('sections.id', 'students.id as student', 'sections.title')->first();
            if (!isset($student_sections->id)) {
                $student_sections = Employee::join('sections', 'sections.id', '=', 'students.section_id')
                    ->where('students.user_id', '=', $this->user->id)
                    ->whereNull('sections.deleted_at')
                    ->orderBy('students.company_year_id', 'DESC')
                    ->select('sections.id', 'students.id as student', 'sections.title')->first();
            }
        } else {
            $student_sections = Employee::join('sections', 'sections.id', '=', 'students.section_id')
                ->where('students.user_id', '=', $this->user->id)
                ->where('students.company_year_id', $company_year_id)
                ->where('students.company_id', $company_id)
                ->whereNull('sections.deleted_at')
                ->where('sections.id', $student_section)
                ->orderBy('students.company_year_id', 'DESC')
                ->select('sections.id', 'students.id as student', 'sections.title')->first();
            if (!isset($student_sections->id)) {
                $student_sections = Employee::join('sections', 'sections.id', '=', 'students.section_id')
                    ->where('students.user_id', '=', $this->user->id)
                    ->where('students.company_year_id', $company_year_id)
                    ->where('students.company_id', $company_id)
                    ->orderBy('students.company_year_id', 'DESC')
                    ->select('sections.id', 'students.id as student', 'sections.title')->first();
            }
        }
        $student_section = isset($student_sections) ? $student_sections->title : "--";
        $student_section_id = isset($student_sections) ? $student_sections->id : 0;
        $student_id = isset($student_sections) ? $student_sections->student : 0;

        return [
            'student_section' => $student_section,
            'student_section_id' => $student_section_id,
            'student_id' => $student_id,
        ];
    }

    public function setSessionStudentSection($result)
    {
        $student_section = $result['student_section'];
        $student_section_id = $result['student_section_id'];
        $student_id = $result['student_id'];

        session(['current_student_section' => $student_section_id]);

        view()->share('current_student_section', $student_section);
        view()->share('current_student_section_id', $student_section_id);
        view()->share('current_student_id', $student_id);
    }

    public function currentParentStudents($student_id, $current_company_year, $company_id)
    {
        if (!isset($student_id) || $student_id == "") {
            $student_ids = CompanyYear::join('students', 'students.company_year_id', '=', 'company_years.id')
                ->join('users', 'users.id', '=', 'students.user_id')
                ->join('parent_students', 'parent_students.user_id_student', '=', 'students.user_id')
                ->whereNull('students.deleted_at');
            if ($current_company_year > 0) {
                $student_ids = $student_ids->where('students.company_year_id', $current_company_year);
            }
            $student_ids = $student_ids->where('parent_students.user_id_parent', $this->user->id)
                ->where('students.company_id', $company_id)
                ->orderBy('students.id', 'DESC')
                ->distinct()
                ->select('students.id', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
                ->first();
        } else {
            $student_ids = CompanyYear::join('students', 'students.company_year_id', '=', 'company_years.id')
                ->join('users', 'users.id', '=', 'students.user_id')
                ->join('parent_students', 'parent_students.user_id_student', '=', 'students.user_id')
                ->whereNull('students.deleted_at');
            if ($current_company_year > 0) {
                $student_ids = $student_ids->where('students.company_year_id', $current_company_year);
            }
            $student_ids = $student_ids->where('parent_students.user_id_parent', $this->user->id)
                ->where('students.company_id', $company_id)
                ->where('students.id', $student_id)
                ->orderBy('students.id', 'DESC')
                ->distinct()
                ->select('students.id', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
                ->first();

            if (!isset($student_ids->id)) {
                $student_ids = CompanyYear::join('students', 'students.company_year_id', '=', 'company_years.id')
                    ->join('users', 'users.id', '=', 'students.user_id')
                    ->join('parent_students', 'parent_students.user_id_student', '=', 'students.user_id')
                    ->whereNull('students.deleted_at');
                if ($current_company_year > 0) {
                    $student_ids = $student_ids->where('students.company_year_id', $current_company_year);
                }
                $student_ids = $student_ids->where('parent_students.user_id_parent', $this->user->id)
                    ->where('students.company_id', $company_id)
                    ->orderBy('students.id', 'DESC')
                    ->distinct()
                    ->select('students.id', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
                    ->get();
            }
        }
        $student_name = isset($student_ids) ? $student_ids->name : "--";
        $student_id = isset($student_ids) ? $student_ids->id : 0;

        $student_ids = Employee::join('users', 'users.id', '=', 'students.user_id')
            ->join('parent_students', 'users.id', '=', 'parent_students.user_id_student')
            ->orderBy('students.company_year_id', 'DESC')
            ->where('parent_students.user_id_parent', '=', $this->user->id)
            ->where('students.company_year_id', $current_company_year)
            ->where('students.company_id', $company_id)
            ->distinct()
            ->select('students.id', DB::raw('CONCAT(users.first_name, " ", users.last_name) as name'))
            ->get();
        return [
            'current_student_name' => $student_name,
            'current_student_id' => $student_id,
            'student_ids' => $student_ids,
        ];
    }

    public function setStudentParent($result)
    {
        $student_name = $result['current_student_name'];
        $student_id = $result['current_student_id'];
        $student_ids = $result['student_ids'];

        session(['current_student_id' => $student_id]);

        $student = Employee::find($student_id);
        session(['current_student_user_id' => isset($student) ? $student->user_id : 0]);

        view()->share('current_student_name', $student_name);
        view()->share('current_student_id', $student_id);
        view()->share('student_ids', $student_ids);
    }

    public function currentSchoolYearParent($current_company_year_id, $student_id, $company_id)
    {
        if (!isset($current_company_year_id) || $current_company_year_id == "") {
            $company_year = CompanyYear::join('students', 'students.company_year_id', '=', 'company_years.id')
                ->join('parent_students', 'parent_students.user_id_student', '=', 'students.user_id')
                ->whereNull('students.deleted_at');
            if ($student_id > 0) {
                $company_year = $company_year->where('students.id', $student_id);
            }
            $company_year = $company_year->where('parent_students.user_id_parent', $this->user->id)
                ->where('students.company_id', $company_id)
                ->orderBy('company_years.id', 'DESC')
                ->select('company_years.*')
                ->distinct()->first();
        } else {
            $company_year = CompanyYear::where('id', $current_company_year_id)->first();
            if (!isset($company_year->id)) {
                $company_year = CompanyYear::join('students', 'students.company_year_id', '=', 'company_years.id')
                    ->join('parent_students', 'parent_students.user_id_student', '=', 'students.user_id')
                    ->whereNull('students.deleted_at');
                if ($student_id > 0) {
                    $company_year = $company_year->where('students.id', $student_id);
                }
                $company_year = $company_year->where('parent_students.user_id_parent', $this->user->id)
                    ->where('students.company_id', $company_id)
                    ->orderBy('company_years.id', 'DESC')
                    ->select('company_years.*')
                    ->distinct()->first();
            }
        }
        $value = isset($company_year) ? $company_year->title : "--";
        $id = isset($company_year) ? $company_year->id : 0;

        $company_years = CompanyYear::join('students', 'students.company_year_id', '=', 'company_years.id')
            ->join('parent_students', 'parent_students.user_id_student', '=', 'students.user_id')
            ->whereNull('students.deleted_at')
            ->where('parent_students.user_id_parent', $this->user->id)
            ->where('students.company_id', $company_id)
            ->orderBy('company_years.id', 'DESC')
            ->distinct()
            ->select('company_years.*')->get();

        return [
            'current_company_value' => $value,
            'current_company_id' => $id,
            'other_company_years' => $company_years,
        ];
    }

    public function currentSchoolParent($current_company_id, $user_id)
    {
        if (!isset($current_company_id) || $current_company_id == "") {
            $school = Company::join('students', 'students.company_id', '=', 'companies.id')
                ->join('parent_students', 'students.user_id', '=', 'parent_students.user_id_student')
                ->where('parent_students.user_id_parent', $user_id)->orderBy('companies.id', 'DESC')
                ->select('companies.*')->distinct()->first();
        } else {
            $school = Company::join('students', 'students.company_id', '=', 'companies.id')
                ->join('parent_students', 'students.user_id', '=', 'parent_students.user_id_student')
                ->where('parent_students.user_id_parent', $user_id)
                ->where('companies.id', $current_company_id)
                ->select('companies.*')->distinct()->first();
            if (!isset($school->id)) {
                $school = Company::join('students', 'students.company_id', '=', 'companies.id')
                    ->join('parent_students', 'students.user_id', '=', 'parent_students.user_id_student')
                    ->where('parent_students.user_id_parent', $user_id)->orderBy('companies.id', 'DESC')
                    ->select('companies.*')->distinct()->first();
            }
        }
        $value = isset($school) ? $school->title : "--";
        $id = isset($school) ? $school->id : 0;

        $schools = Company::join('students', 'students.company_id', '=', 'companies.id')
            ->join('parent_students', 'students.user_id', '=', 'parent_students.user_id_student')
            ->where('parent_students.user_id_parent', $user_id)->orderBy('companies.id', 'DESC')
            ->select('companies.*')->distinct()->get();

        return [
            'current_company_item' => $value,
            'current_company' => $id,
            'other_schools' => $schools,
        ];
    }
}
