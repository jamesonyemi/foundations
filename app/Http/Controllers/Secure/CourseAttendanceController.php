<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\StudentAdminAttendanceRequest;
use App\Models\School;
use App\Repositories\AttendanceRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use Carbon\Carbon;
use App\Helpers\Settings;
use PDF;

class CourseAttendanceController extends SecureController
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
     * StudentAttendanceAdminController constructor.
     * @param SectionRepository $sectionRepository
     * @param AttendanceRepository $attendanceRepository
     */
    public function __construct(
        SectionRepository $sectionRepository,
        AttendanceRepository $attendanceRepository
    ) {

        parent::__construct();

        $this->sectionRepository = $sectionRepository;
        $this->attendanceRepository = $attendanceRepository;

        $this->middleware('authorized:student_attendances_admin.show', ['only' => ['index', 'attendance','attendanceAjax']]);

        view()->share('type', 'course_attendance');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('student_attendances_admin.student_attendances');
        $sections =  $this->sectionRepository->getAllForSchool(session('current_company'))
            ->get()->pluck('title', 'id')->prepend(trans('student_attendances_admin.select_section'), 0)->toArray();

        return view('student_attendances_admin.index', compact('title', 'sections'));
    }

    public function attendance(StudentAdminAttendanceRequest $request)
    {
        $result = ' <h1>' . trans('report.list_attendances') . '</h1>
                    ' . $request->get('start_date') . ' - ' . $request->get('end_date') . '<br>';
        $result .= $this->generateTable($request);
        $school =  School::where('id', session('current_company'))->get()
            ->map(function ($item) {
                return [
                    "title" => $item->title,
                    "address" => $item->address,
                    "email" => $item->email,
                    "phone" => $item->phone,
                ];
            });
        $pdf = PDF::loadView('course_attendance.attendance', compact('result', 'school'));
        return $pdf->stream();
    }
    public function attendanceAjax(StudentAdminAttendanceRequest $request)
    {
        $result = $this->generateTable($request);
        return $result;
    }

    /**
     * @param StudentAdminAttendanceRequest $request
     * @return string
     */
    private function generateTable(StudentAdminAttendanceRequest $request)
    {
        $result = '<table class="table">
                        <thead>
                        <tr>
                            <th>' . trans('student_attendances_admin.date') . '</th>
                            <th>' . trans('student_attendances_admin.attendance_type') . '</th>
                            <th>' . trans('student_attendances_admin.hours') . '</th>
                            <th>' . trans('student_attendances_admin.percent') . '</th>
                        </tr>
                        </thead>
                        <tbody>';

        $attendances = $this->attendanceRepository
            ->getAllForSectionIdAndBetweenDate(
                $request->get('section_id'),
                $request->get('start_date'),
                $request->get('end_date')
            )
            ->map(function ($attendance) {
                return
                    [
                        'date' => Carbon::createFromFormat(Settings::get('date_format'), $attendance->date)->toDateString(),
                        'attendance_type' => $attendance->title,
                        'hours' => $attendance->hours
                    ];
            });
        $attendances_sum = [];
        foreach ($attendances as $item) {
            $attendances_sum[$item['date']]['sum_hours'] =
                (isset($attendances_sum[$item['date']]['sum_hours']) ? $attendances_sum[$item['date']]['sum_hours'] : 0)
                + $item['hours'];
        }
        foreach ($attendances as $item) {
            $percent = 0;
            if (isset($attendances_sum[$item['date']]['sum_hours'])) {
                $percent = round(($item['hours'] / $attendances_sum[$item['date']]['sum_hours']) * 100);
            }
            $result .= '<tr>
                            <td>' . $item['date'] . '</td>
                            <td>' . $item['attendance_type'] . '</td>
                            <td>' . $item['hours'] . '</td>
                            <td>' . $percent . ' %</td>
                         </tr>';
        }
        $result .= '</tbody>
                    </table>
                </div>';
        return $result;
    }
}
