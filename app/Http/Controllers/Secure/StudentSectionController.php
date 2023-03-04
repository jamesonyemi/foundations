<?php

namespace App\Http\Controllers\Secure;

use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\TimetablePeriodRepository;
use App\Repositories\TimetableRepository;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class StudentSectionController extends SecureController
{
    /**
     * @var TimetableRepository
     */
    private $timetableRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;
    /**
     * @var PaymentRepository
     */
    private $paymentRepository;
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var TimetablePeriodRepository
     */
    private $timetablePeriodRepository;

    /**
     * StudentSectionController constructor.
     *
     * @param TimetableRepository $timetableRepository
     * @param StudentRepository $studentRepository
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param PaymentRepository $paymentRepository
     * @param InvoiceRepository $invoiceRepository
     * @param TimetablePeriodRepository $timetablePeriodRepository
     */
    public function __construct(
        TimetableRepository $timetableRepository,
        StudentRepository $studentRepository,
        TeacherSubjectRepository $teacherSubjectRepository,
        PaymentRepository $paymentRepository,
        InvoiceRepository $invoiceRepository,
        TimetablePeriodRepository $timetablePeriodRepository
    ) {

        parent::__construct();

        $this->timetableRepository = $timetableRepository;
        $this->studentRepository = $studentRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->paymentRepository = $paymentRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->timetablePeriodRepository = $timetablePeriodRepository;

        view()->share('type', 'studentsection');

        $columns = ['title','payment_method','amount', 'status'];
        view()->share('columns', $columns);
    }

    public function timetable()
    {
        $title = trans('teachergroup.timetable');

        if ($this->user->inRole('student')) {
            $student_user_id = $this->user->id;
        } else {
            $student_user_id = session('current_student_user_id');
        }
        $school_year_id = session('current_company_year');

        $studentgroups = $this->studentRepository
            ->getAllStudentGroupsForStudentUserAndSchoolYear($student_user_id, $school_year_id);

        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndGroups($school_year_id, $studentgroups)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) && isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => $teacherSubject->subject->title,
                    'name' => $teacherSubject->teacher->full_name,
                    'subject_id' => $teacherSubject->subject_id,
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);
        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();

        return view('studentsection.timetable', compact(
            'title',
            'action',
            'timetable',
            'timetablePeriods'
        ));
    }

    public function print_timetable()
    {
        $title = trans('teachergroup.timetable');

        if ($this->user->inRole('student')) {
            $student_user_id = $this->user->id;
        } else {
            $student_user_id = session('current_student_user_id');
        }
        $school_year_id = session('current_company_year');

        $studentgroups = $this->studentRepository
            ->getAllStudentGroupsForStudentUserAndSchoolYear($student_user_id, $school_year_id);

        $subject_list = $this->teacherSubjectRepository
            ->getAllForSchoolYearAndGroups($school_year_id, $studentgroups)
            ->with('teacher', 'subject')
            ->get()
            ->filter(function ($teacherSubject) {
                return (isset($teacherSubject->subject) && isset($teacherSubject->teacher));
            })
            ->map(function ($teacherSubject) {
                return [
                    'id' => $teacherSubject->id,
                    'title' => $teacherSubject->subject->title,
                    'name' => $teacherSubject->teacher->full_name,
                ];
            });
        $timetable = $this->timetableRepository
            ->getAllForTeacherSubject($subject_list);
        $timetablePeriods = $this->timetablePeriodRepository->getAll()->get();

        $data = '<h1>' . $title . '</h1>
				<table style="border: double" class="table-bordered">
					<tbody>
					<tr>
						<th>#</th>
						<th width="14%">' . trans('teachergroup.monday') . '</th>
						<th width="14%">' . trans('teachergroup.tuesday') . '</th>
						<th width="14%">' . trans('teachergroup.wednesday') . '</th>
						<th width="14%">' . trans('teachergroup.thursday') . '</th>
						<th width="14%">' . trans('teachergroup.friday') . '</th>
                        <th width="14%">' . trans('teachergroup.saturday') . '</th>
                        <th width="14%">' . trans('teachergroup.sunday') . '</th>
					</tr>';
        if ($timetablePeriods->count() >0) {
            for ($i=0; $i<$timetablePeriods->count(); $i++) {
                $data .= '<tr>
            <td>' . $timetablePeriods[$i]['start_at'].' - '. $timetablePeriods[$i]['end_at'] . '</td>';
                for ($j = 1; $j < 8; $j ++) {
                    $data .= '<td>';
                    if ($timetablePeriods[$i]['title']=="") {
                        foreach ($timetable as $item) {
                            if ($item['week_day'] == $j && $item['hour'] == $i) {
                                $data .= '<div>
                            <span>' . $item['title'] . '</span>
                            <br>
                            <span>' . $item['name'] . '</span></div>';
                            }
                        }
                    } else {
                        $data .=$timetablePeriods[$i]['title'];
                    }
                    $data .= '</td>';
                }
                $data .= '</tr>';
            }
        } else {
            for ($i = 1; $i < 8; $i ++) {
                $data .= '<tr>
            <td>' . $i . '</td>';
                for ($j = 1; $j < 8; $j ++) {
                    $data .= '<td>';
                    foreach ($timetable as $item) {
                        if ($item['week_day'] == $j && $item['hour'] == $i) {
                            $data .= '<div>
                            <span>' . $item['title'] . '</span>
                            <br>
                            <span>' . $item['name'] . '</span></div>';
                        }
                    }
                    $data .= '</td>';
                }
                $data .= '</tr>';
            }
        }
        $data .= '</tbody>
				</table>';
        $pdf = PDF::loadView('report.timetable', compact('data'));
        return $pdf->stream();
    }

    public function payment()
    {
        $title = trans('payment.payment');
        return view('studentsection.payment', compact('title'));
    }

    public function data()
    {
        if ($this->user->inRole('student')) {
            $student_user_id = $this->user->id;
        } else {
            $student_user_id = session('current_student_user_id');
        }
        $payments = $this->paymentRepository->getAll()
            ->get()
            ->filter(function ($payment) use ($student_user_id) {
                return $payment->user_id == $student_user_id;
            })
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'title' => $payment->title,
                    'payment_method' => $payment->payment_method,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                ];
            });

        return Datatables::make($payments)
            ->removeColumn('id')
             ->rawColumns([ 'actions' ])->make();
    }

    public function invoice()
    {
        if ($this->user->inRole('student')) {
            $student_user_id = $this->user->id;
        } else {
            $student_user_id = session('current_student_user_id');
        }

        $title = trans('invoice.invoice');

        $invoices = $this->invoiceRepository->getAll()
            ->get()
            ->filter(function ($invoice) use ($student_user_id) {
                return ($invoice->user_id == $student_user_id && $invoice->paid == 0);
            })
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'title' => $invoice->title,
                    'amount' => $invoice->amount,
                    'description' => $invoice->description,
                ];
            });

        return view('studentsection.invoice', compact('title', 'invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $pdf = PDF::loadView('report.invoice', compact('invoice'));
        return $pdf->stream();
    }
}
