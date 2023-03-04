<?php

namespace App\Http\Controllers\Traits;

use App\Models\Employee;
use App\Models\Message;
use App\Models\Company;
use App\Models\CompanyYear;
use App\Models\Semester;
use App\Models\Applicant;
use DB;
use App\Helpers\Settings;
use Sentinel;
use Session;
use App\Helpers\Flash;

trait SharedValuesTrait
{
    use SchoolYearTrait;

    public function shareValues()
    {
        if (isset($this->user->id)) {
            $new_emails = Message::where('to', $this->user->id)->whereNull('deleted_at_receiver')->where('read', 0)->get();
            view()->share('new_emails', $new_emails);

            if ($this->user->inRole('super_admin')
                || $this->user->inRole('human_resources')
                || $this->user->inRole('accountant')
                || $this->user->inRole('librarian')
                || $this->user->inRole('admin_super_admin')
            ) {
                /*
                * if current user is super admin , human resources or accountant
                */
                if ($this->user->inRole('super_admin')
                    || $this->user->inRole('human_resources')
                    || $this->user->inRole('accountant')
                    || $this->user->inRole('librarian')
                    /*|| $this->user->inRole('admin_super_admin')*/
                ) {
                    $current_company = session('current_company');
                    if ($this->user->inRole('accountant')) {
                        $result = $this->currentSchoolAccountant($current_company, $this->user->id);
                    } else if ($this->user->inRole('admin_super_admin')) {
                        $result = $this->currentSchoolAdmin($current_company, $this->user->id);
                    } else if ($this->user->inRole('human_resources')) {
                        $result = $this->currentSchoolHumanResource($current_company, $this->user->id);
                    } else if ($this->user->inRole('librarian')) {
                        $result = $this->currentSchoolLibrarian($current_company, $this->user->id);
                    } else {
                        $result = $this->currentSchool($current_company);
                    }



                    if ((!isset($result['other_schools']) || count($result['other_schools']) == 0) &&
                        ($this->user->inRole('human_resources') || $this->user->inRole('admin') || $this->user->inRole('accountant') || $this->user->inRole('employee'))
                    ) {
                        Sentinel::logout(null, true);
                        Session::flush();
                        Flash::error(trans('secure.no_schools'));

                        return redirect()->guest('/');
                    } else {
                        if ($this->user->inRole('super_admin') || $this->user->inRole('admin_super_admin')) {
                            if (Company::count() == 0) {
                                Flash::error(trans('secure.create_school'));
                            }
                            if (CompanyYear::where('group_id', 2)->count() == 0) {
                            }
                        } else {
                            $this->setSessionSchool($result);
                        }
                    }
                }
                /*
                 * if current user is admin or human_resources or librarian
                 */
                if ($this->user->inRole('human_resources')
                    || $this->user->inRole('accountant')
                    || $this->user->inRole('librarian')
                    || $this->user->inRole('admin')
                    /*|| $this->user->inRole('employee')*/
                ) {
                    @$current_company_year = session('current_company_year');

                    $result = @$this->currentSchoolYear($current_company_year, session('current_company'));
                    if (!isset($result['other_company_years'])) {
                        Sentinel::logout(null, true);
                        Session::flush();
                        Flash::error(trans('secure.no_company_year'));

                        return redirect()->guest('/');
                    } else {
                        $this->setSessionSchoolYears($result);
                    }

                    /*$semester = Semester::where('active', 'Yes')
                        ->where('company_id', session('current_company'))
                        //->where('company_year_id', $current_company_year)
                        ->first();

                    if (!isset($semester)) {
                        Sentinel::logout(null, true);
                        Session::flush();
                        Flash::error(trans('secure.no_school_semester'));
                        return redirect()->guest('/');
                    } else {
                        session(['current_company_semester' => @$semester->id]);
                        session(['current_company_semester_name' => @$semester->title]);
                    }*/
                }
            } /*
             * if current user is admin
             */
            else if ($this->user->inRole('admin')) {
                $current_company_year = session('current_company_year');

                $result = $this->currentSchoolYear($current_company_year);
                if (!isset($result['other_company_years'])) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchoolYears($result);
                }

                $current_company = session('current_company');

                $result = $this->currentSchoolAdmin($current_company, $this->user->id);

                if (!isset($result['other_schools']) || count($result['other_schools']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_schools'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchool($result);
                }
                /*
                                $semester = Semester::where('active', 'Yes')
                                    ->where('company_id', session('current_company'))
                                    //->where('company_year_id', $current_company_year)
                                    ->first();

                                if (!isset($semester)) {
                                    Sentinel::logout(null, true);
                                    Session::flush();
                                    Flash::error(trans('secure.no_school_semester'));
                                    return redirect()->guest('/');
                                } else {
                                    session(['current_company_semester' => @$semester->id]);
                                    session(['current_company_semester_name' => @$semester->title]);
                                }*/
            } /*
             * if current user is teacher
             */
            else if ($this->user->inRole('teacher')) {
                $current_company = session('current_company');

                $result = $this->currentSchoolTeacher($current_company, $this->user->id);

                if (!isset($result['other_schools']) || count($result['other_schools']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_schools'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchool($result);
                }

                $current_company_year = session('current_company_year');
                $result = $this->currentSchoolYearTeacher($current_company_year, $this->user->id);
                if (!isset($result['other_company_years']) || count($result['other_company_years']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchoolYears($result);
                }
                $student_group = session('current_student_group');
                $current_company_year = session('current_company_year');

                $result_groups = $this->currentTeacherStudentGroupSchool($student_group, $current_company_year, $current_company);
                if (empty($result_groups['student_groups'])) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionTeacherStudentGroups($result_groups);
                }
                /*
                                $semester = Semester::where('active', 'Yes')
                                    ->where('company_id', session('current_company'))
                                    //->where('company_year_id', $current_company_year)
                                    ->first();

                                if (!isset($semester)) {
                                    Sentinel::logout(null, true);
                                    Session::flush();
                                    Flash::error(trans('secure.no_school_semester'));
                                    return redirect()->guest('/');
                                } else {
                                    session(['current_company_semester' => @$semester->id]);
                                    session(['current_company_semester_name' => @$semester->title]);
                                }*/
            } /*
                * if current user is parent
                */
            else if ($this->user->inRole('parent')) {
                $current_company = session('current_company');

                $result = $this->currentSchoolParent($current_company, $this->user->id);

                if (!isset($result['other_schools']) || count($result['other_schools']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_schools'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchool($result);
                }

                $current_company_year = session('current_company_year');
                $student_id = session('current_student_id');
                $current_company = session('current_company');

                $result = $this->currentSchoolYearParent($current_company_year, $student_id, $current_company);
                if (!isset($result['other_company_years']) || count($result['other_company_years']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchoolYears($result);
                }

                $current_company_year = session('current_company_year');
                $student_id = session('current_student_id');

                $result = $this->currentParentStudents($student_id, $current_company_year, $current_company);

                if (!isset($result['student_ids'])) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_students_added'));
                    return redirect()->guest('/');
                } else {
                    $this->setStudentParent($result);
                }
                /*
                                $semester = Semester::where('active', 'Yes')
                                ->where('company_id', session('current_company'))
                                //->where('company_year_id', $current_company_year)
                                ->first();

                                if (!isset($semester)) {
                                    Sentinel::logout(null, true);
                                    Session::flush();
                                    Flash::error(trans('secure.no_school_semester'));
                                    return redirect()->guest('/');
                                } else {
                                    session(['current_company_semester' => @$semester->id]);
                                    session(['current_company_semester_name' => @$semester->title]);
                                }*/
            } /*
             * if current user is student
             */
            else if ($this->user->inRole('student')) {
                $current_company = session('current_company');

                $result = $this->currentSchoolStudent($current_company, $this->user->id);

                if (!isset($result['other_schools'])) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_schools'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchool($result);
                }
                $current_company_year = session('current_company_year');
                $result_company_year = $this->currentSchoolYearSchoolStudent($current_company_year, $this->user->id, session('current_company'));

                if (!isset($result_company_year['other_company_years']) || count($result_company_year['other_company_years']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchoolYears($result_company_year);
                }

                $student_section = session('current_student_section');
                $current_company_year = session('current_company_year');
                /*$result_section = $this->currentStudentSectionSchool($student_section, $current_company_year, session('current_company'));
                if (!isset($result_section['student_section_id']) || $result_section['student_section_id'] == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_sections_added'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionStudentSection($result_section);
                }*/

                session(['current_employee' => Employee::where('user_id', $this->user->id)->first()->id]);

                /*$semester = Semester::where('active', 'Yes')
                    ->where('company_id', session('current_company'))
                    ->first();

                if (!isset($semester) ) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_school_semester'));
                    return redirect()->guest('/');
                } else {
                    session(['current_company_semester' => @$semester->id]);
                    session(['current_company_semester_name' => @$semester->title]);
                }*/


            }

            /*
            * if current user is employee
            */
            else if ($this->user->inRole('employee')) {
                $current_company = session('current_company');

                $result = $this->currentSchoolEmployee($current_company, $this->user->id);

                if (!isset($result['other_schools'])) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_schools'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchool($result);
                }
                $current_company_year = session('current_company_year');
                $result_company_year = $this->currentSchoolYearEmployee($current_company_year, $this->user->id, session('current_company'));

                if (!isset($result_company_year['other_company_years']) || count($result_company_year['other_company_years']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchoolYears($result_company_year);
                }



                session(['current_employee' => Employee::where('user_id', $this->user->id)->first()->id]);


            }



            /*
                         * if current user is applicant
                         */
            else if ($this->user->inRole('applicant')) {
                //$current_company = session('current_company');

                $current_company = session('current_company');

                $result = $this->currentSchoolApplicant($current_company, $this->user->id);

                $applicant = Applicant::where('user_id', ($this->user->id))->get();
                session(['current_applicant' => @$applicant[0]->id]);

                if (!isset($result['other_schools'])) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_schools'));

                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchool($result);
                }
                $current_company_year = session('current_company_year');
                $result_company_year = $this->currentSchoolYearSchoolApplicant($current_company_year, $this->user->id, session('current_company'));

                if (!isset($result_company_year['other_company_years']) || count($result_company_year['other_company_years']) == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_company_year'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionSchoolYears($result_company_year);
                }


                /*$semester = Semester::where('active', 'Yes')
                    ->where('company_id', session('current_company'))
                    //->where('company_year_id', $current_company_year)
                    ->first();

                if (!isset($semester)) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_school_semester'));
                    return redirect()->guest('/');
                } else {
                    session(['current_company_semester' => @$semester->id]);
                    session(['current_company_semester_name' => @$semester->title]);
                }*/

                /*$student_section = session('current_student_section');
                $current_company_year = session('current_company_year');
                $result_section = $this->currentStudentSectionSchool($student_section, $current_company_year, session('current_company'));
                if (!isset($result_section['student_section_id']) || $result_section['student_section_id'] == 0) {
                    Sentinel::logout(null, true);
                    Session::flush();
                    Flash::error(trans('secure.no_sections_added'));
                    return redirect()->guest('/');
                } else {
                    $this->setSessionStudentSection($result_section);
                }*/
            }
        } else {
            return redirect('/signin');
        }
    }
}
