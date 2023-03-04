<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\AddExamAttendanceRequest;
use App\Http\Requests\Secure\DeleteRequest;
use App\Http\Requests\Secure\ExamAttendanceGetRequest;
use App\Models\Exam;
use App\Models\ExamAttendance;
use App\Repositories\ExamAttendanceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\Collection;

class ExamAttendanceController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    /**
     * @var ExamAttendanceRepository
     */
    private $examAttendanceRepository;

    /**
     * AttendanceController constructor.
     * @param StudentRepository $studentRepository
     * @param OptionRepository $optionRepository
     * @param ExamAttendanceRepository $examAttendanceRepository
     */
    public function __construct(
        StudentRepository $studentRepository,
        OptionRepository $optionRepository,
        ExamAttendanceRepository $examAttendanceRepository
    ) {

        parent::__construct();

        $this->studentRepository = $studentRepository;
        $this->optionRepository = $optionRepository;
        $this->examAttendanceRepository = $examAttendanceRepository;

        view()->share('type', 'exam_attendance');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Exam $exam)
    {
        $title = trans('exam_attendance.exam_attendance');
        $students = $this->studentRepository->getAllRegistrationForSubject(session('current_company_year'), session('current_company_semester'), $exam->subject_id)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->full_name,
                ];
            })->pluck('name', 'id')->toArray();

        $attendance_type = $this->optionRepository->getAllForSchool(session('current_company'))
            ->where('category', 'attendance_type')->get()
            ->map(function ($option) {
                return [
                    "title" => $option->title,
                    "value" => $option->id,
                ];
            })->pluck('title', 'value')->toArray();

        return view('exam_attendance.index', compact('title', 'students', 'attendance_type', 'exam'));
    }

    public function addAttendance(AddExamAttendanceRequest $request)
    {
        foreach ($request['students'] as $student_id) {
            $attendance = new ExamAttendance($request->except('students'));
            $attendance->student_id = $student_id;
            $attendance->save();
        }
    }

    public function getAttendance(ExamAttendanceGetRequest $request)
    {
        $students = new Collection([]);
        $this->studentRepository->getAllRegistrationForSubject(session('current_company_year'), session('current_company_semester'), $request->subject_id)
            ->each(function ($student) use ($students) {
                $students->push($student->id);
            });
        $attendances = $this->examAttendanceRepository->getAllForStudents($students)
            ->with('student', 'student.user')
            ->get()
            ->filter(function ($attendance) use ($request) {
                return ($attendance->exam_id == $request->exam_id &&
                    isset($attendance->student->user->full_name));
            })
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'name' => $attendance->student->user->full_name,
                    'option' => isset($attendance->option) ? $attendance->option->title : "",
                ];
            })->toArray();
        return json_encode($attendances);
    }

    public function deleteAttendance(DeleteRequest $request)
    {
        $attendance = ExamAttendance::find($request['id']);
        $attendance->delete();
    }
}
