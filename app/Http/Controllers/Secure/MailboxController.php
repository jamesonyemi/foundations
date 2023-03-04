<?php

namespace App\Http\Controllers\Secure;

use App\Events\MessageCreated;
use App\Http\Requests\Secure\MessageRequest;
use App\Models\Message;
use App\Repositories\StudentRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sentinel;

class MailboxController extends SecureController
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /**
     * @var SubjectRepository
     */
    private $subjectRepository;

    /**
     * @param UserRepository $userRepository
     * @param StudentRepository $studentRepository
     * @param SubjectRepository $subjectRepository
     * @internal param CompanyRepository $
     */
    public function __construct(
        UserRepository $userRepository,
        StudentRepository $studentRepository,
        SubjectRepository $subjectRepository
    ) {

        parent::__construct();

        $this->userRepository = $userRepository;
        $this->studentRepository = $studentRepository;
        $this->subjectRepository = $subjectRepository;

        view()->share('type', 'mailbox');
    }

    public function index()
    {
        $this->generateData();
        $title = trans('mailbox.mailbox');
        return view('mailbox.index', compact('title'));
    }


    public function sent()
    {
        $this->generateData();
        $title = trans('mailbox.send_mail');
        return view('mailbox.sent', compact('title'));
    }

    public function delete(Message $message)
    {
        if ($message->to == $this->user->id) {
            $message->deleted_at_receiver = Carbon::now();
        } else {
            $message->deleted_at_sender = Carbon::now();
        }
        $message->save();
        return redirect()->back();
    }

    public function replay(Message $message)
    {
        $message->read = 1;
        $message->save();

        $this->generateData();
        $title = trans('mailbox.replay');
        return view('mailbox.replay', compact('title', 'message'));
    }

    public function send_replay(Message $message, MessageRequest $request)
    {

        $request->merge([
            'subject' => 'Re: ' . $message->subject,
        ]);

        $email = new Message($request->only('subject', 'message'));
        $email->to = $message->from;
        $email->from = Sentinel::getUser()->id;

        if ($request->hasFile('file_file') != "") {
            $file = $request->file('file_file');
            $extension = $file->getClientOriginalExtension();
            $attachment = str_random(10) . '.' . $extension;

            $destinationPath = public_path() . '/uploads/messages/';
            $file->move($destinationPath, $attachment);
            $email->attachment = $attachment;
        }
        $email->save();

        return redirect('mailbox');
    }

    public function compose()
    {

        $this->generateData();
        $title = trans('mailbox.compose');
        return view('mailbox.compose', compact('title'));
    }

    public function send_compose(MessageRequest $request)
    {

        foreach ($request->recipients as $item) {
            $email       = new Message($request->only('subject', 'message'));
            $email->to   = $item;
            $email->from = Sentinel::getUser()->id;

            if ($request->hasFile('file_file') != "") {
                $file = $request->file('file_file');
                $extension = $file->getClientOriginalExtension();
                $attachment = str_random(10) . '.' . $extension;

                $destinationPath = public_path() . '/uploads/messages/';
                $file->move($destinationPath, $attachment);
                $email->attachment = $attachment;
            }
            $email->save();
        }

        return redirect('mailbox');
    }


    public function download(Message $message)
    {
        return response()->download($message->file_url);
    }


    /**
     * @return array
     */
    private function super_admin_contact_users()
    {
        $users_list = $this->userRepository->getAll()
            ->where('id', '<>', $this->user->id)->get()
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'id' => $user->id,
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) &&
                        $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }
    /**
     * @return array
     */
    private function doorman_contact_users()
    {
        $users_list = $this->userRepository->getAll()
            ->where('id', '<>', $this->user->id)->get()
            ->filter(function ($user) {
                return ($user->inRole('admin') || $user->inRole('librarian')
                    || $user->inRole('human_resources') || $user->inRole('accountant')
                    || $user->inRole('doorman') || $user->inRole('visitor'));
            })
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'id' => $user->id,
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) &&
                        $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }

    /**
     * @return array
     */
    private function admin_contact_users()
    {
        $users_list = $this->userRepository->getAllUsersFromSchool(session('current_company'), session('current_company_year'))
            ->filter(function ($user) {
                return ($user->id != $this->user->id);
            })
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'user_avatar' => $user->user_avatar,
                    'id' => $user->id,
                    'active' => (isset($user->last_login) && $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }

    /**
     * @return array
     */
    private function teacher_contact_users()
    {
        $users_list = $this->userRepository->getAllStudentsParentsUsersFromSchool(session('current_company'), session('current_company_year'), session('current_student_group'))
            ->filter(function ($user) {
                return isset($user) && $user->id != $this->user->id;
            })
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'user_avatar' => $user->user_avatar,
                    'id' => $user->id,
                    'active' => (isset($user->last_login) && $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }

    /**
     * @return array
     */
    private function student_contact_users()
    {
        $users_list = $this->userRepository
            ->getAllStudentsAndTeachersForSchoolSchoolYearAndSection(session('current_company'), session('current_company_year'), session('current_student_section'))
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'id' => $user->id,
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) &&
                        $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }

    /**
     * @return array
     */
    private function parent_contact_users()
    {
        $users_list = $this->userRepository->getAllAdminAndTeachersForSchool(session('current_company'))
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'id' => $user->id,
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) &&
                        $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }

    private function generateData()
    {
        $email_list = Message::where('to', $this->user->id)->whereNull('deleted_at_receiver')->orderBy('id', 'desc')->get();
        $sent_email_list = Message::where('from', $this->user->id)->whereNull('deleted_at_sender')->orderBy('id', 'desc')->get();

        if ($this->user->inRole('super_admin') || $this->user->inRole('admin_super_admin') || $this->user->inRole('librarian')
            || $this->user->inRole('accountant')
        ) {
            $users_list = $this->super_admin_contact_users();
        } elseif ($this->user->inRole('admin') || $this->user->inRole('human_resources')) {
            $users_list = $this->admin_contact_users();
        } elseif ($this->user->inRole('teacher')) {
            $users_list = $this->teacher_contact_users();
        } elseif ($this->user->inRole('student')) {
            $users_list = $this->student_contact_users();
        } elseif ($this->user->inRole('doorman')) {
            $users_list = $this->doorman_contact_users();
        } elseif ($this->user->inRole('parent')) {
            $users_list = $this->parent_contact_users();
        } elseif ($this->user->inRole('applicant')) {
            $users_list = $this->applicant_contact_users();
        } else {
            $users_list = [];
        }
        view()->share('email_list', $email_list);
        view()->share('sent_email_list', $sent_email_list);
        view()->share('users_list', $users_list);

        $users_select = [];
        foreach ($users_list as $item) {
            $users_select[$item['id']] = $item['full_name'];
        }
        view()->share('users_select', $users_select);
    }

    private function applicant_contact_users()
    {
        $users_list = $this->userRepository->getAll()
            ->where('id', '<>', $this->user->id)->get()
            ->filter(function ($user) {
                return ($user->inRole('admin') || $user->inRole('admin_super_admin'));
            })
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name,
                    'id' => $user->id,
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) &&
                        $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
        return $users_list;
    }
}
