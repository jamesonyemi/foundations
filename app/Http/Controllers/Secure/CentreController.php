<?php

namespace App\Http\Controllers\Secure;

use App\Exports\EmployeesAttendanceExport;
use App\Exports\SessionAbsentExport;
use App\Exports\SessionAttendanceExport;
use App\Helpers\GeneralHelper;
use App\Http\Requests\Secure\CentreRequest;
use App\Http\Requests\Secure\LevelRequest;
use App\Models\Center;
use App\Models\ConferenceSession;
use App\Models\Employee;
use App\Models\Level;
use App\Models\SessionAttendance;
use App\Models\StudentStatus;
use App\Models\SupplierDocument;
use App\Notifications\ConferenceInvitationNotification;
use App\Notifications\ConferenceRegistrationNotification;
use App\Repositories\EmployeeRepository;
use App\Repositories\LevelRepository;
use App\Repositories\SectionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class CentreController extends SecureController
{
    /**
     * @var LevelRepository
     */
    private $levelRepository;

    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var StudentRepository
     */
    private $employeeRepository;

    /**
     * DirectionController constructor.
     *
     * @param LevelRepository $levelRepository
     * @param SectionRepository $sectionRepository
     *
     * @internal param DirectionRepository $directionRepository
     */
    public function __construct(LevelRepository $levelRepository,
                                EmployeeRepository $employeeRepository,
                                SectionRepository $sectionRepository)
    {
        parent::__construct();

        $this->levelRepository = $levelRepository;
        $this->sectionRepository = $sectionRepository;
        $this->employeeRepository = $employeeRepository;

        view()->share('type', 'centre');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = 'Centers';

        $centers = Center::get();

        return view('centre.index', compact('title', 'centers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('level.new');

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Employee', 0)
            ->toArray();

        return view('layouts.create', compact('title', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|LevelRequest $request
     * @return Response
     */
    public function store(CentreRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $center = new Center($request->all());
                $center->company_id = session('current_company');

                if ($request->hasFile('file1') != '') {
                    $file = $request->file('file1');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $center->file1 = $document;
                }
                if ($request->hasFile('file2') != '') {
                    $file = $request->file('file2');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $center->file2 = $document;
                }
                if ($request->hasFile('file3') != '') {
                    $file = $request->file('file3');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $center->file3 = $document;
                }
                if ($request->hasFile('file4') != '') {
                    $file = $request->file('file4');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $center->file4 = $document;
                }
                $center->save();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Center Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param Center $centre
     * @return Response
     */
    public function show(Center $centre)
    {
        $title = trans('centre.details');
        $action = 'show';

        return view('layouts.show', compact('centre', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Center $centre
     * @return Response
     */
    public function edit(Center $centre)
    {
        $title = 'Edit '.$centre->title.'';

        $employees = $this->employeeRepository->getAllForSchoolAndGlobal(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->prepend('Select Employee', 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'centre', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request|LevelRequest $request
     * @param Center $centre
     * @return Response
     */
    public function update(CentreRequest $request, Center $centre)
    {
        try {
            DB::transaction(function () use ($request, $centre) {
                $centre->update($request->all());

                if ($request->hasFile('file1') != '') {
                    $file = $request->file('file1');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $centre->file1 = $document;
                }
                if ($request->hasFile('file2') != '') {
                    $file = $request->file('file2');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $centre->file2 = $document;
                }
                if ($request->hasFile('file3') != '') {
                    $file = $request->file('file3');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $centre->file3 = $document;
                }
                if ($request->hasFile('file4') != '') {
                    $file = $request->file('file4');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/centreImages/';
                    $file->move($destinationPath, $document);

                    $centre->file4 = $document;
                }

                $centre->save();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Center Updated Successfully</div>');
    }

    public function delete(Center $centre)
    {
        $title = trans('level.delete');
        try {
            DB::transaction(function () use ($centre) {
                $centre->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['exception'=>$e->getMessage()]);
        }

        return response('<div class="alert alert-success">Center Deleted Successfully</div>');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Level $level
     * @return Response
     */
    public function destroy(Center $centre)
    {
        $centre->delete();

        return redirect('/centre');
    }

    public function centerParticipants(Center $center)
    {
        $title = $center->title.' Participants';
        $id = $center->id;

        return view('centre.participants', compact('title', 'id', 'center'));
    }

    public function centerAttendance(Center $center)
    {
        $title = $center->title.' Session Attendance';
        $id = $center->id;

        return view('centre.attendance', compact('title', 'id', 'center'));
    }

    public function inviteCenterParticipants(Center $center)
    {
        foreach ($center->employees as $employee) {
            //send email to user
            if (GeneralHelper::validateEmail($employee->employee->user->email)) {
                @Notification::send($employee->employee->user, new ConferenceInvitationNotification($employee->employee->user, $center));
            }
        }

        return response('<div class="alert alert-success">Invitation Sent Successfully</div>');
    }

    public function generatePresent_csv(Request $request)
    {
        $employees = SessionAttendance::where('center_id', $request->center_id)
            ->where('conference_session_id', $request->session_id);

        /*$employees = Employee::whereHas('sessionAttendance', function ($q) use ($request){
            $q->where('session_attendances.conference_session_id', $request->session_id)
                ->where('session_attendances.center_id', $request->center_id);
        });*/

        /*dd($employees->count());*/
        /*$employees = $this->employeeRepository->getAllSessionAttendanceExport($request->center_id, $request->session_id)
            ->with('user');*/

        return Excel::download(new SessionAttendanceExport($employees), 'attendance.xlsx');
    }

    public function generateAbsent_csv(Request $request)
    {
        $center = Center::find($request->center_id);

        $ids = SessionAttendance::where('center_id', $request->center_id)
            ->where('conference_session_id', $request->session_id)->select('employee_id');

        $employees = StudentStatus::Where('center_id', $request->center_id)
            ->whereNotIn('employee_id', $ids);

        /*$centerParticipants = $center->employees->whereNotIn( 'student_id', $ids );*/

        /*dd($students->count());*/

        return Excel::download(new SessionAbsentExport($employees), 'attendance.xlsx');
    }
}
