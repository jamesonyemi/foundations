<?php

namespace App\Http\Controllers\Secure;

use App\Repositories\ParentStudentRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;

class ParentSectionController extends SecureController
{
    /**
     * @var ParentStudentRepository
     */
    private $parentStudentRepository;

    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * ParentSectionController constructor.
     * @param ParentStudentRepository $parentStudentRepository
     * @param StudentRepository $studentRepository
     */
    public function __construct(
        ParentStudentRepository $parentStudentRepository,
        StudentRepository $studentRepository
    ) {
        parent::__construct();

        $this->parentStudentRepository = $parentStudentRepository;
        $this->studentRepository = $studentRepository;

        view()->share('type', 'parentsection');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('parentsection.parentsection');

        return view('parentsection.index', compact('title'));
    }

    public function data()
    {
        $user = $this->user;
        $student_parents = new Collection([]);
        $this->parentStudentRepository->getAll()
            ->with('students')
            ->get()
            ->filter(function ($parent) use ($user, $student_parents) {
                if (isset($parent->students) && $parent->user_id_parent == $user->id) {
                    $student_parents->push($parent);
                }
            });
        $students = $this->studentRepository->getAllForSchoolYear(session('current_company_year'))
            ->get()
            ->filter(function ($student) use ($student_parents) {
                foreach ($student_parents as $student_parent) {
                    if ($student->user_id == $student_parent->user_id_student) {
                        return true;
                    }
                }
            })
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'name' => $parent->user->full_name,
                    'title' => $parent->section->title,
                ];
            });

        return Datatables::make($students)
            ->addColumn('actions', '<a href="{{ url(\'/setstudent/\' . $id . \'\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-eye "></i>  {{ trans("parentsection.show") }}</a>')
            ->removeColumn('id')
             ->rawColumns(['actions'])->make(false);
    }
}
