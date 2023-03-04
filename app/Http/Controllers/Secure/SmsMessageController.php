<?php

namespace App\Http\Controllers\Secure;

use App\Http\Requests\Secure\SmsMessageRequest;
use App\Models\Company;
use App\Models\ParentStudent;
use App\Models\SmsMessage;
use App\Models\User;
use App\Notifications\SendSMS;
use App\Repositories\EmployeeRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\SectionRepository;
use App\Repositories\SmsMessageRepository;
use App\Repositories\TeacherSchoolRepository;
use App\Repositories\TeacherSubjectRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SMS;
use Yajra\DataTables\Facades\DataTables;

class SmsMessageController extends SecureController
{
    /**
     * @var SmsMessageRepository
     */
    private $smsMessageRepository;

    /**
     * @var TeacherSubjectRepository
     */
    private $teacherSubjectRepository;

    /**
     * @var StudentRepository
     */
    private $employeeRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var TeacherSchoolRepository
     */
    private $teacherSchoolRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param SmsMessageRepository $smsMessageRepository
     * @param TeacherSubjectRepository $teacherSubjectRepository
     * @param EmployeeRepository $employeeRepository
     * @param SectionRepository $sectionRepository
     * @param InvoiceRepository $invoiceRepository
     * @param TeacherSchoolRepository $teacherSchoolRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        SmsMessageRepository $smsMessageRepository,
        TeacherSubjectRepository $teacherSubjectRepository,
        EmployeeRepository $employeeRepository,
        SectionRepository $sectionRepository,
        InvoiceRepository $invoiceRepository,
        TeacherSchoolRepository $teacherSchoolRepository,
        UserRepository $userRepository
    ) {
        parent::__construct();

        $this->smsMessageRepository = $smsMessageRepository;
        $this->teacherSubjectRepository = $teacherSubjectRepository;
        $this->employeeRepository = $employeeRepository;
        $this->sectionRepository = $sectionRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->teacherSchoolRepository = $teacherSchoolRepository;
        $this->userRepository = $userRepository;

        $this->middleware('authorized:sms_message.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:sms_message.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:sms_message.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:sms_message.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'sms_message');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('sms_message.sms_messages');

        $messages = $this->smsMessageRepository->getAllForSender($this->user->id)
            ->get();

        return view('sms_message.index', compact('title', 'messages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('sms_message.new');

        $students = $this->employeeRepository->getAllForSchool(session('current_company'))
                                            ->with('user')
                                            ->get()
                                            ->filter(function ($student) {
                                                return  isset($student->user) &&
                                                         isset($student->user->mobile) &&
                                                         $student->user->mobile != '' &&
                                                         (! isset($student->user->get_sms) || $student->user->get_sms == 1);
                                            })
                                            ->map(function ($student) {
                                                return [
                                                    'user_id'   => $student->user_id,
                                                    'full_name' => $student->user->full_name,
                                                ];
                                            })->pluck('full_name', 'user_id')->toArray();
        $users = [];
        foreach ($students as $key => $item) {
            $users[$key] = $item;
        }

        return view('layouts.create', compact('title', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function store(SmsMessageRequest $request)
    {
        try {
            if (count($request->users_select) > 0) {
                foreach ($request->users_select as $user_id) {
                    $this->createMessageForUser($request, $user_id);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">SMS Message Successfully</div>');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function group_sms()
    {
        $title = trans('sms_message.group_sms');
        $groups = $this->generateGroups();

        return view('sms_message.for_group', compact('title', 'groups'));
    }

    public function store_group(SmsMessageRequest $request)
    {
        $users = [];
        if (count($request->groups) > 0) {
            foreach ($request->groups as $group_id) {
                switch ($group_id) {
                    case 0:
                        $students = $this->studentRepository->getAllForSchoolYearAndSchool(session('current_company_year'), session('current_company'))
                                                            ->get()->map(function ($student) {
                                                                return ['id' => $student->user_id];
                                                            });
                        foreach ($students as $item) {
                            if (! in_array($item['id'], $users)) {
                                $users[] = $item['id'];
                            }
                        }
                        break;
                    case 1:
                        $debtors = $this->invoiceRepository->getAllDebtorStudentsForSchool(session('current_company'))
                                                           ->get()->map(function ($debtor) {
                                                               return [
                                                                   'id' => $debtor->user_id,
                                                               ];
                                                           });
                        foreach ($debtors as $item) {
                            if (! in_array($item['id'], $users)) {
                                $users[] = $item['id'];
                            }
                        }
                        break;
                    case 2:
                        $teachers = $this->teacherSchoolRepository->getAllForSchool(session('current_company'))
                                                                  ->map(function ($teacher) {
                                                                      return [
                                                                          'id' => $teacher->id,
                                                                      ];
                                                                  });
                        foreach ($teachers as $item) {
                            if (! in_array($item['id'], $users)) {
                                $users[] = $item['id'];
                            }
                        }
                        break;
                    case 3:
                        $schoolAdmins = $this->userRepository->getUsersForRole('admin')->map(function ($schoolAdmin) {
                            return [
                                'id' => $schoolAdmin->id,
                            ];
                        });
                        foreach ($schoolAdmins as $item) {
                            if (! in_array($item['id'], $users)) {
                                $users[] = $item['id'];
                            }
                        }
                        break;
                    case 4:
                        $debtors = $this->invoiceRepository->getAllDebtorStudentsForSchool(session('current_company'))
                                                           ->get()->map(function ($debtor) {
                                                               return [
                                                                   'id' => $debtor->user_id,
                                                               ];
                                                           });
                        foreach ($debtors as $debtor) {
                            $parents = ParentStudent::where('user_id_student', $debtor['id'])->get();
                            foreach ($parents as $item) {
                                if (! in_array($item->id, $users)) {
                                    $users[] = $item->id;
                                }
                            }
                        }
                        break;
                    default:
                        $section_id = $group_id - 5;
                        $students = $this->studentRepository->getAllForSection($section_id);
                        foreach ($students as $item) {
                            if (! in_array($item->user_id, $users)) {
                                $users[] = $item->user_id;
                            }
                        }
                        break;
                }
            }
        }
        if (count($users) > 0) {
            foreach ($users as $user_id) {
                $this->createMessageForUser($request, $user_id);
            }
        }

        return redirect('/sms_message');
    }

    /**
     * Display the specified resource.
     *
     * @param  SmsMessage $smsMessage
     *
     * @return Response
     */
    public function show(SmsMessage $smsMessage)
    {
        $title = trans('sms_message.details');
        $action = 'show';

        return view('layouts.show', compact('smsMessage', 'title', 'action', 'receivers'));
    }

    public function data()
    {
        $messages = $this->smsMessageRepository->getAllForSender($this->user->id)
                                               ->get()
                                               ->map(function ($message) {
                                                   return [
                                                       'id'   => $message->id,
                                                       'text' => $message->text,
                                                   ];
                                               });

        return Datatables::make($messages)
                          ->addColumn('actions', '<a href="{{ url(\'/sms_message/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>')
                          ->removeColumn('id')
                          ->rawColumns(['actions'])->make();
    }

    /**
     * @return array
     */
    private function generateGroups()
    {
        $groups = [
            0 => trans('sms_message.all_students'),
            1 => trans('sms_message.all_students_with_debts'),
            2 => trans('sms_message.all_teachers'),
            3 => trans('sms_message.all_admins'),
            4 => trans('sms_message.all_parents'),
        ];
        $sections = $this->sectionRepository->getAllForSchoolYearSchool(session('current_company_year'), session('current_company'))
                                            ->get()->map(function ($section) {
                                                return [
                                                    'id'    => $section->id,
                                                    'title' => $section->title,
                                                ];
                                            });

        foreach ($sections as $item) {
            $groups[$item['id'] + 5] = trans('sms_message.all_students_from').$item['title'];
        }

        return $groups;
    }

    /**
     * @param SmsMessageRequest $request
     * @param $user_id
     */
    private function createMessageForUser(SmsMessageRequest $request, $user_id)
    {
        $school = Company::find(session('current_company'))->first();

        $user = User::find($user_id);
        if (! is_null($user) && $user->mobile != '') {
            $user->notify(new SendSMS($user, $request));

            $smsMessage = new SmsMessage();
            $smsMessage->text = $request->text;
            $smsMessage->number = $user->mobile;
            $smsMessage->user_id = $user_id;
            $smsMessage->user_id_sender = $this->user->id;
            $smsMessage->company_id = session('current_company');
            $smsMessage->save();
        }
    }
}
