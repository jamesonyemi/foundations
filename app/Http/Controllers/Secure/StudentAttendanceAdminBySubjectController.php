<?php

namespace App\Http\Controllers\Secure;

use App\Repositories\AttendanceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentGroupRepository;
use App\Repositories\StudentRepository;
use Symfony\Component\HttpFoundation\Request;

class StudentAttendanceAdminBySubjectController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $sectionRepository;
    /**
     * @var AttendanceRepository
     */
    private $attendanceRepository;
    /**
     * @var StudentGroupRepository
     */
    private $studentGroupRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * StudentAttendanceAdminController constructor.
     * @param SectionRepository $sectionRepository
     * @param AttendanceRepository $attendanceRepository
     * @param StudentGroupRepository $studentGroupRepository
     * @param StudentRepository $studentRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        SectionRepository $sectionRepository,
        AttendanceRepository $attendanceRepository,
        StudentGroupRepository $studentGroupRepository,
        StudentRepository $studentRepository,
        OptionRepository $optionRepository
    ) {

        parent::__construct();

        $this->sectionRepository = $sectionRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->studentGroupRepository = $studentGroupRepository;
        $this->studentRepository = $studentRepository;
        $this->optionRepository = $optionRepository;

        $this->middleware('authorized:student_attendances_admin.show', ['only' => ['index', 'attendance','attendanceAjax']]);

        view()->share('type', 'attendances_by_subject');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('attendances_by_subject.sum_attendances_by_subject_and_type');
        $sections = $this->sectionRepository->getAllForSchool(session('current_company'))
            ->get()->pluck('title', 'id')->prepend(trans('attendances_by_subject.select_section'), 0)->toArray();

        return view('attendances_by_subject.index', compact('title', 'sections'));
    }

    public function getGroups(Request $request)
    {
        return $this->studentGroupRepository->getAllForSection($request->get('section_id'))
                                             ->pluck('title', 'id')->toArray();
    }
    public function getStudents(Request $request)
    {
        return $this->studentRepository->getAllForStudentGroup($request->get('group_id'))
                                        ->map(function ($student) {
                                            return [
                                                'id' => $student->id,
                                                'name' => $student->user->full_name,
                                            ];
                                        })->pluck('name', 'id')->toArray();
    }
    public function attendanceGraph(Request $request)
    {

        $attendance_types = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'attendance_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "id" => $option->id,
                ];
            })->pluck('title', 'id')->toArray();
        $attendance_graph = [];
        if ($request->get('student_id')>0) {
            foreach ($attendance_types as $key => $item) {
                $attendance_graph[$key] =
                    $this->attendanceRepository->getAllForStudentAndOptionAndBetweenDate(
                        $request->get('student_id'),
                        $key,
                        $request->get('start_date'),
                        $request->get('end_date')
                    );
            }
        } else if ($request->get('group_id')>0) {
            foreach ($attendance_types as $key => $item) {
                $attendance_graph[$key] =
                    $this->attendanceRepository->getAllForStudentGroupAndOptionAndBetweenDate(
                        $request->get('group_id'),
                        $key,
                        $request->get('start_date'),
                        $request->get('end_date')
                    );
            }
        } else {
            foreach ($attendance_types as $key => $item) {
                $attendance_graph[$key] =
                    $this->attendanceRepository->getAllForSectionAndOptionAndBetweenDate(
                        $request->get('section_id'),
                        $key,
                        $request->get('start_date'),
                        $request->get('end_date')
                    );
            }
        }
        return ['attendance_graph'=>$attendance_graph,'attendance_types'=>$attendance_types];
    }
}
