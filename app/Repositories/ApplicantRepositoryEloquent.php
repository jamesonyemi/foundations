<?php

namespace App\Repositories;

use App\Helpers\Settings;
use App\Models\Applicant;
use App\Models\Company;
use App\Models\CompanyYear;
use App\Models\Department;
use App\Models\Direction;
use App\Models\FeeCategory;
use App\Models\FeesStatus;
use App\Models\Invoice;
use App\Models\RoleUser;
use App\Models\Semester;
use App\Models\StudentAdmission;
use App\Models\StudentGroup;
use App\Models\StudentStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Sentinel;
use Session;

class ApplicantRepositoryEloquent implements ApplicantRepository
{
    /**
     * @var Student
     */
    private $model;

    /**
     * StudentRepositoryEloquent constructor.
     * @param Student $model
     */
    public function __construct(Applicant $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }

    public function getAllMale()
    {
        return $this->model->where('company_id', session('current_company'))
            ->where('company_year_id', session('current_company_year'))
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '1');
            })
            ->whereNull('deleted_at');
    }

    public function getAllFeMale()
    {
        return $this->model->where('company_id', session('current_company'))
            ->where('company_year_id', session('current_company_year'))
            ->whereHas('user', function ($query) {
                $query->where('gender', '=', '0');
            })
            ->whereNull('deleted_at');
    }

    public function getAllForSchoolYearAndSection($company_year_id, $section_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('section_id', $section_id)
            ->where('status', $section_id);
    }

    public function getAllForSchoolYear($company_year_id)
    {
        return $this->model->where('company_year_id', $company_year_id)->with('user');
    }

    public function getAllForSchoolYearAndSchool($company_year_id, $company_id, $school_semester_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('company_id', $company_id)
            ->where('semester_id', $school_semester_id)
            ->where('status', '!=', 0)
            ->orderBy('applicants.id', 'Desc');
    }

    public function getAllForSchoolYearSchoolAndSection($company_year_id, $company_id, $section_id)
    {
        return $this->model->where('company_year_id', $company_year_id)
            ->where('company_id', $company_id);
    }

    public function getSchoolForStudent($student_user_id, $company_year_id)
    {
        return $this->model->whereIn('user_id', $student_user_id)->where('company_year_id', $company_year_id);
    }

    public function getAllActiveFilter($request)
    {
        $studentItems = $this->model
            ->join('users', 'users.id', '=', 'applicants.user_id')
            ->join('sections', 'sections.id', '=', 'applicants.section_id')
            ->join('countries', 'countries.id', '=', 'applicants.country_id')
            ->leftJoin('levels', 'levels.id', '=', 'applicants.level_of_adm')
            ->leftJoin('directions', 'directions.id', '=', 'applicants.first_choice_prog_id')
            ->where('applicants.section_id', '>', 0)
            ->whereNull('users.deleted_at')
            ->where('applicants.company_id', session('current_company'))
            /*->where('status', '!=', 0)*/
            ->where(function ($w) use ($request) {
                if (! is_null($request['enroll']) && $request['enroll'] == 'enroll') {
                    $w->where('applicants.status', 0);
                }

                if (! is_null($request['enroll']) && $request['enroll'] == 'notenroll') {
                    $w->where('applicants.status', '!=', 0);
                }

                if (is_null($request['company_year_id'])) {
                    $w->where('applicants.company_year_id', session('current_company_year'));
                }

                if (is_null($request['semester_id'])) {
                    $w->where('applicants.semester_id', session('current_company_semester'));
                }

                if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
                    $w->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
                }
                if (! is_null($request['name_x']) && $request['name_x'] != '*') {
                    $w->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
                }

                if (! is_null($request['country_id'])) {
                    $w->where('applicants.country_id', $request['country_id']);
                }

                if (! is_null($request['section_id'])) {
                    $w->where('applicants.section_id', $request['section_id']);
                }

                if (! is_null($request['direction_id']) && $request['direction_id'] != '*') {
                    $w->where('applicants.first_choice_prog_id', $request['direction_id']);
                }

                if (! is_null($request['company_year_id']) && $request['company_year_id'] != '*') {
                    $w->where('applicants.company_year_id', $request['company_year_id']);
                }

                if (! is_null($request['semester_id']) && $request['semester_id'] != '*') {
                    $w->where('applicants.semester_id', $request['semester_id']);
                }

                if (! is_null($request['entry_mode_id']) && $request['entry_mode_id'] != '*') {
                    $w->where('applicants.entry_mode_id', $request['entry_mode_id']);
                }

                if (! is_null($request['level_id']) && $request['level_id'] != '*' && $request['level_id'] != 'null') {
                    $w->where('applicants.level_of_adm', $request['level_id']);
                }

                if (! is_null($request['religion_id']) && $request['religion_id'] != '*' && $request['religion_id'] != 'null') {
                    $w->where('applicants.religion_id', $request['religion_id']);
                }

                if (! is_null($request['marital_status_id']) && $request['marital_status_id'] != '*' && $request['marital_status_id'] != 'null') {
                    $w->where('applicants.marital_status_id', $request['marital_status_id']);
                }

                if (! is_null($request['session_id']) && $request['session_id'] != '*' && $request['session_id'] != 'null') {
                    $w->where('applicants.session_id', $request['session_id']);
                }

                if (! is_null($request['gender']) && $request['gender'] != '*' && $request['gender'] != 'null') {
                    $w->where('users.gender', $request['gender']);
                }
            })->orderBy('applicants.id', 'desc')
            ->select(
                'users.id as user_id',
                'applicants.id as applicant_id',
                'applicants.status as status',
                'applicants.created_at as created_at',
                DB::raw('CONCAT(users.first_name, " ", COALESCE(users.middle_name, ""), " ", users.last_name) as full_name'),
                'users.gender as gender',
                'sections.title as section',
                'directions.title as programme',
                'levels.name as level',
                'countries.nationality as nationality',
                'users.email as email',
                'users.mobile as mobile'
            );

        return $studentItems;
    }

    public function getAllActiveExport($request)
    {
        $query = Applicant::query()
            ->join('users', 'users.id', '=', 'applicants.user_id')
            ->join('sections', 'sections.id', '=', 'applicants.section_id')
            ->join('countries', 'countries.id', '=', 'applicants.country_id')
            ->leftJoin('levels', 'levels.id', '=', 'applicants.level_of_adm')
            ->leftJoin('directions', 'directions.id', '=', 'applicants.first_choice_prog_id')
            ->whereNull('users.deleted_at')
            ->where('applicants.company_id', '=', session('current_company'));

        if (! is_null($request['enroll']) && $request['enroll'] == 'enroll') {
            $query = $query->where('applicants.status', 0);
        }

        if (! is_null($request['enroll']) && $request['enroll'] == 'notenroll') {
            $query = $query->where('applicants.status', '!=', 0);
        }

        if (! is_null($request['fname_x']) && $request['fname_x'] != '*') {
            $query = $query->where('users.first_name', 'LIKE', '%'.$request['fname_x'].'%');
        }
        if (! is_null($request['name_x']) && $request['name_x'] != '*') {
            $query = $query->where('users.last_name', 'LIKE', '%'.$request['name_x'].'%');
        }

        if (! isset($request->company_year_id) || empty($request->company_year_id)) {
            $query = $query->where('applicants.company_year_id', session('current_company_year'));
        }
        if (! isset($request->semester_id) || empty($request->semester_id)) {
            $query = $query->where('applicants.semester_id', session('current_company_semester'));
        }

        if (isset($request->country_id) && ! empty($request->country_id)) {
            $query = $query->where('applicants.country_id', $request->country_id);
        }

        if (isset($request->section_id) && ! empty($request->section_id)) {
            $query = $query->where('applicants.section_id', $request->section_id);
        }

        if (isset($request->direction_id) && $request->direction_id != 0) {
            $query = $query->where('applicants.first_choice_prog_id', $request->direction_id);
        }

        if (isset($request->company_year_id) && ! empty($request->company_year_id)) {
            $query = $query->where('applicants.company_year_id', '=', $request->company_year_id);
        }

        if (isset($request->semester_id) && ! empty($request->semester_id)) {
            $query = $query->where('applicants.semester_id', '=', $request->semester_id);
        }

        if (isset($request->level_id) && ! empty($request->level_id)) {
            $query = $query->where('applicants.level_of_adm', '=', $request->level_id);
        }

        if (isset($request->entry_mode_id) && ! empty($request->entry_mode_id)) {
            $query = $query->where('applicants.entry_mode_id', '=', $request->entry_mode_id);
        }

        if (isset($request->session_id) && ! empty($request->session_id)) {
            $query = $query->where('applicants.session_id', '=', $request->session_id);
        }

        if (isset($request->marital_status_id) && ! empty($request->marital_status_id)) {
            $query = $query->where('applicants.marital_status_id', '=', $request->marital_status_id);
        }

        if (isset($request->religion_id) && ! empty($request->religion_id)) {
            $query = $query->where('applicants.religion_id', '=', $request->religion_id);
        }

        if (isset($request->gender) && ! empty($request->gender)) {
            $query = $query->where('users.gender', '=', $request->gender);
        }

        return $query;
    }

    public function deactivate($applicant_id)
    {
        $applicant = Applicant::find($applicant_id);
        $applicant->status = 0;
        $applicant->save();

        $roleUser = RoleUser::where('user_id', $applicant->user_id)->first();
        $roleUser->role_id = 7;
        $roleUser->save();
    }

    public function create(array $data, $activate = true)
    {
        $user_exists = User::where('email', $data['email'])->first();
        if (! isset($user_exists->id)) {
            $user_tem = Sentinel::registerAndActivate($data, $activate);
            $user = User::find($user_tem->id);
        } else {
            $user = $user_exists;
        }
        try {
            $role = Sentinel::findRoleBySlug('applicant');
            $role->users()->attach($user);
        } catch (\Exception $e) {
        }
        $user->update(['birth_date'=>$data['birth_date'],
            'birth_city'=>isset($data['birth_city']) ? $data['birth_city'] : '-',
            'gender' => isset($data['gender']) ? $data['gender'] : 0,
            'address' => isset($data['address']) ? $data['address'] : '-',
            'mobile' => isset($data['mobile']) ? $data['mobile'] : 0,
            'phone' => isset($data['phone']) ? $data['phone'] : 0, ]);

        if (is_null(session('current_company')) && Settings::get('multi_school') == 'no' && isset(Company::first()->id)) {
            session('current_company', Company::first()->id);
        }

        /*$student = new Student();
        $student->section_id = $data['section_id'];
        $student->order = $data['order'];
        $student->company_year_id = session('current_company_year');
        $student->company_id = session('current_company');
        $student->user_id = $user->id;
        $student->save();

        $school = Company::find(session('current_company'));;
        $student->student_no = $school->student_card_prefix . $student->id;
        $student->save();

        //if(!is_null($data['student_group_id'])){
            //$studentGroup = StudentGroup::find($data['student_group_id']);
            //$studentGroup->students()->attach($student->id);
        //}

        return $user;*/

        $student = new Applicant();
        $student->section_id = $data['section_id'];
        $student->country_id = $data['country_id'];
        $student->direction_id = $data['direction_id'];
        $student->first_choice_prog_id = $data['first_choice_prog_id'];
        $student->intake_period_id = $data['intake_period_id'];
        $student->entry_mode_id = $data['entry_mode_id'];
        $student->level_of_adm = $data['level_of_adm'];
        $student->marital_status_id = $data['marital_status_id'];
        $student->no_of_children = $data['no_of_children'];
        $student->order = $data['order'];
        $student->company_year_id = session('current_company_year');
        $student->semester_id = session('current_company_semester');
        $student->company_id = session('current_company');
        $student->campus_id = $data['campus_id'];
        $student->religion_id = $data['religion_id'];
        $student->denomination = $data['denomination'];
        $student->disability = $data['disability'];
        $student->contact_relation = $data['contact_relation'];
        $student->contact_name = $data['contact_name'];
        $student->contact_address = $data['contact_address'];
        $student->contact_phone = $data['contact_phone'];
        $student->contact_email = $data['contact_email'];
        $student->user_id = $user->id;
        $student->save();

        $school = Company::find(session('current_company'));
        $yearCode = CompanyYear::find(session('current_company_year'));
        $departmentCode = Department::find($data['section_id']);
        $programmeCode = Direction::find($data['direction_id']);
        $student->student_no = $school->student_card_prefix.$student->id;
        $student->sID = $school->student_card_prefix.'/'.$departmentCode->id_code.'/'.$programmeCode->id_code.'/'.$yearCode->id_code.$school->next_id_no;
        if ($data['country_id'] == '1') {
            $student->currency_id = '1';
        } else {
            $student->currency_id = '2';
        }

        $student->save();

        $school2 = Company::find(session('current_company'));
        $school2->next_id_no = $school2->next_id_no + $school2->id_interval;
        $school2->save();

        $fees = FeeCategory::all()->whereIn('section_id', [$data['section_id'], 7])
            ->where('company_id', '=', session('current_company'))
            ->where('currency_id', '=', $student->currency_id);

        $currentSemester = semester::where('company_year_id', '=', session('current_company_year'))->get()->last();

        $invoice = new Invoice();
        $invoice->student_id = $student->id;
        $invoice->user_id = $student->user_id;
        $invoice->company_id = session('current_company');
        $invoice->company_year_id = session('current_company_year');
        $invoice->semester_id = $currentSemester->id;
        $invoice->currency_id = $student->currency_id;
        $invoice->total_fees = $fees->sum('amount');
        $invoice->amount = $fees->sum('amount');
        $invoice->save();

        foreach ($fees as $fee) {
            $feesStatus = new FeesStatus();
            $feesStatus->invoice_id = $invoice->id;
            $feesStatus->user_id = $student->user_id;
            $feesStatus->company_id = session('current_company');
            $feesStatus->company_year_id = session('current_company_year');
            $feesStatus->semester_id = $currentSemester->id;
            $feesStatus->title = $fee->title;
            $feesStatus->currency_id = $student->currency_id;
            $feesStatus->amount = $fee->amount;
            $feesStatus->fee_category_id = $fee->id;
            $feesStatus->save();
        }

        return $user;
    }

    public function getAllForSection($section_id)
    {
        $studentitems = new Collection([]);
        $this->model->with('user')
            ->orderBy('order')
            ->get()
            ->each(function ($studentItem) use ($studentitems, $section_id) {
                if ($studentItem->section_id == $section_id && isset($studentItem->user)) {
                    $studentitems->push($studentItem);
                }
            });

        return $studentitems;
    }

    public function getAllForSection2($section_id)
    {
        return $this->model->where('section_id', $section_id);
    }

    public function getAllForSectionCurrency($section_id, $currency_id)
    {
        return $this->model->where('section_id', $section_id)
            ->where('currency_id', $currency_id);
    }

    public function getAllForDirection($direction_id)
    {
        return $this->model->where('direction_id', $direction_id);
    }

    public function getAllForDirectionCurrency($direction_id, $currency_id)
    {
        return $this->model->where('direction_id', $direction_id)
            ->where('currency_id', $currency_id);
    }

    public function getAllForSchoolYearStudents($company_year_id, $student_user_ids)
    {
        $studentItems = new Collection([]);
        $this->model->with('user', 'section')
                    ->orderBy('order')
                    ->get()
                    ->each(function ($studentItem) use ($studentItems, $student_user_ids, $company_year_id) {
                        if (in_array($studentItem->user_id, $student_user_ids) &&
                            $studentItem->company_year_id == $company_year_id) {
                            $studentItems->push($studentItem);
                        }
                    });

        return $studentItems;
    }

    public function getCountStudentsForSchoolAndSchoolYear($company_id, $schoolYearId)
    {
        return $this->model->where('company_id', $company_id)
                           ->where('company_year_id', $schoolYearId)
                           ->count();
    }
}
