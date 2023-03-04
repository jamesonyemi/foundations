<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\HelpDeskRequest;
use App\Models\Employee;
use App\Models\HelpDesk;
use App\Models\HelpDeskCategory;
use App\Models\HelpDeskPriority;
use App\Models\HelpDeskSubCategory;
use App\Models\UserDocument;
use App\Notifications\SendSMS;
use App\Repositories\EmployeeRepository;
use App\Repositories\HelpDeskRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Sentinel;

class TicketsController extends SecureController
{
    /**
     * @var EmployeeRepository
     */
    private $employeeRepository;

    /**
     * @var HelpdeskRepository
     */
    private $helpDeskRepository;

    protected $module = 'HelpDesk';

    /**
     * EmployeeController constructor.
     * @param HelpDeskRepository $helpDeskRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        HelpdeskRepository $helpDeskRepository
    ) {
        parent::__construct();
        $this->employeeRepository = $employeeRepository;
        $this->helpDeskRepository = $helpDeskRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'helpDesk');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('helpDesk.tickets');
        $helpDesks = $this->helpDeskRepository->getAllForSchool(session('current_company'))
            ->get();

        return view('tickets.index', compact('title', 'helpDesks'));
    }

    public function me()
    {
        $title = trans('helpDesk.me');
        $helpDesks = $this->helpDeskRepository->getAllMe(session('current_employee'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function mine()
    {
        $title = trans('helpDesk.mine');
        $helpDesks = $this->helpDeskRepository->getAllMine(session('current_employee'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function closed()
    {
        $title = trans('helpDesk.closed');
        $helpDesks = $this->helpDeskRepository->getAllClosed(session('current_company'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    public function open()
    {
        $title = trans('helpDesk.open');
        $helpDesks = $this->helpDeskRepository->getAllOpen(session('current_company'))
            ->get();

        return view('helpDesk.index', compact('title', 'helpDesks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('helpDesk.new');

        $helpDeskCategories = HelpDeskCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();

        $helpDeskPriorities = HelpDeskPriority::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_priority'), 0)
            ->toArray();

        return view('layouts.create', compact('title', 'helpDeskCategories', 'helpDeskPriorities'));
    }

    public function createFor()
    {
        $title = trans('helpDesk.new');
        $createFor = 1;

        $helpDeskCategories = HelpDeskCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();

        $helpDeskPriorities = HelpDeskPriority::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_priority'), 0)
            ->toArray();

        $employees = $this->employeeRepository->getAllForSchool(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.'  '.' |'.$item->sID.'| ' : '',
                ];
            })->pluck('name', 'id')
            ->toArray();

        return view('layouts.create', compact('title', 'helpDeskCategories', 'helpDeskPriorities', 'employees', 'createFor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param HelpDeskRequest $request
     * @return Response
     */
    public function store(HelpDeskRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $helpDesk = new HelpDesk();
                $helpDesk->company_id = session('current_company');
                $helpDesk->company_year_id = session('current_company_year');
                $helpDesk->employee_id = $request->employee_id ?? session('current_employee');
                $helpDesk->title = $request->title;
                $helpDesk->description = $request->description;
                $helpDesk->help_desk_category_id = $request->help_desk_category_id;
                $helpDesk->help_desk_subcategory_id = $request->help_desk_subcategory_id;
                $helpDesk->priority_id = $request->priority_id;
                $helpDesk->status = 0;
                $helpDesk->save();

                /*Send email to notify Supervisors*/
                /*@GeneralHelper::sendNewEmployee_email($user,$employee);*/

                if ($request->hasFile('file') != '') {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/avatar/';
                    $file->move($destinationPath, $picture);
                    Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                    $helpDesk->file = $picture;
                    $helpDesk->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Employee Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function show(HelpDesk $helpDesk)
    {
        $title = $helpDesk->title;
        $action = 'show';

        return view('layouts.show', compact('helpDesk', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function edit(HelpDesk $helpDesk)
    {
        $title = 'Edit '.$helpDesk->title.'';

        $helpDeskCategories = HelpDeskCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();

        $helpDeskPriorities = HelpDeskPriority::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_priority'), 0)
            ->toArray();

        return view('layouts.edit', compact('title', 'helpDesk', 'helpDeskPriorities', 'helpDeskCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param HelpDeskRequest $request
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function update(HelpDeskRequest $request, HelpDesk $helpDesk)
    {
        if (! Sentinel::hasAccess('employees_update')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        try {
            $employee->update($request->only('section_id', 'order', 'section_id', 'position_id', 'level_id', 'entry_mode_id', 'country_id', 'marital_status_id', 'no_of_children', 'religion_id', 'denomination', 'disability', 'contact_relation', 'contact_name', 'contact_address', 'contact_phone', 'contact_email', 'session_id'));
            $employee->save();
            EmployeeSupervisor::where('employee_id', $employee->id)->delete();
            if ($request->password != '') {
                $employee->user->password = bcrypt($request->password);
            }

            foreach ($request['employee_supervisor_id']  as $index => $supervisor_id) {
                EmployeeSupervisor::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'employee_supervisor_id' => $supervisor_id,
                    ]
                );
            }

            if ($request->hasFile('image_file') != '') {
                $file = $request->file('image_file');
                $extension = $file->getClientOriginalExtension();
                $picture = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/avatar/';
                $file->move($destinationPath, $picture);
                Thumbnail::generate_image_thumbnail($destinationPath.$picture, $destinationPath.'thumb_'.$picture);
                $employee->user->picture = $picture;
                $employee->user->save();
            }

            $employee->user->update($request->except('section_id', 'order', 'password', 'document', 'document_id', 'image_file', 'permission[]'));

            if ($request->hasFile('document') != '') {
                $file = $request->file('document');
                $user = $employee->user;
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);

                UserDocument::where('user_id', $user->id)->delete();

                $userDocument = new UserDocument;
                $userDocument->user_id = $user->id;
                $userDocument->document = $document;
                $userDocument->option_id = $request->document_id;
                $userDocument->save();
            }
            CustomFormUserFields::updateCustomUserField('employee', $employee->user->id, $request);
            $user = $employee->user;
            foreach ($user->getPermissions() as $key => $item) {
                $user->removePermission($key);
            }
            if (isset($request['permission'])) {
                foreach ($request['permission'] as $permission) {
                    $user->addPermission($permission);
                    $user->save();
                }
            }
            /*RoleUser::where('user_id', $user->id)->delete();;
            foreach ($request['roles'] as $key) {
                $role = Sentinel::findRoleBySlug($key);
                $role->users()->attach($user);

            }*/

            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $employee->id,
                'activity'  => 'updated',
            ]);
            /* Flash::success("Employee Information Updated Successfully");*/
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    /**
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function delete(HelpDesk $helpDesk)
    {
        if (! Sentinel::hasAccess('employees_delete')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        $title = 'Delete '.$employee->user->full_name.'';

        return view('employee.delete', compact('employee', 'title', 'helpDesk'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param HelpDesk $helpDesk
     * @return Response
     */
    public function destroy(HelpDesk $helpDesk)
    {
        if (! Sentinel::hasAccess('employees_delete')) {
            Flash::error('Permission Denied');

            return Response('<div class="alert alert-danger">Permission Denied</div>');
        }
        $helpDesk->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $helpDesk->id,
            'activity'  => 'Deleted',
        ]);
        Flash::success('Employee Deleted successfully');

        return 'Employee Deleted';
    }

    public function findHelpdeskSubCategory(Request $request)
    {
        $directions = HelpDeskSubCategory::where('help_desk_category_id', $request->section_id)->get()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_sub_category'), '')
            ->toArray();

        return $directions;
    }
}
