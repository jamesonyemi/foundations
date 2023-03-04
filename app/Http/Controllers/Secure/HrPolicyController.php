<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\HrPolicyRequest;
use App\Models\Employee;
use App\Models\EmployeeKpiActivity;
use App\Models\HrPolicy;
use App\Models\UserDocument;
use App\Notifications\SendSMS;
use App\Repositories\HrPolicyRepository;
use App\Repositories\SectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PDF;
use Sentinel;

class HrPolicyController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;

    /**
     * @var HrPolicyRepository
     */
    private $hrPolicyRepository;

    protected $module = 'hrPolicy';

    /**
     * EmployeeController constructor.
     * @param HrPolicyRepository $hrPolicyRepository
     */
    public function __construct(
        HrPolicyRepository $hrPolicyRepository,
        SectionRepository $sectionRepository
    ) {
        parent::__construct();
        $this->hrPolicyRepository = $hrPolicyRepository;
        $this->sectionRepository = $sectionRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'hrPolicy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('hrPolicy.hrPolicies');
        $hrPolicies = $this->hrPolicyRepository->getAll()
            ->get();

        return view('hrPolicy.index', compact('title', 'hrPolicies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('hrPolicy.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param HrPolicyRequest $request
     * @return Response
     */
    public function store(HrPolicyRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $hrPolicy = new HrPolicy();
                $hrPolicy->company_id = session('current_company');
                $hrPolicy->employee_id = session('current_employee');
                $hrPolicy->title = $request->title;
                $hrPolicy->description = $request->description;
                $hrPolicy->save();

                /*Send email to notify Supervisors*/
                /*@GeneralHelper::sendNewEmployee_email($user,$employee);*/

                if ($request->hasFile('file') != '') {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $document = Str::random(8) .'.'.$extension;

                    $destinationPath = public_path().'/uploads/documents/';
                    $file->move($destinationPath, $document);
                    $hrPolicy->file = $document;
                    $hrPolicy->save();
                }
            });
        } catch (\Exception $e) {
            return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
        }

        return response('<div class="alert alert-success">Daily Activity Created Successfully</div>');
    }

    /**
     * Display the specified resource.
     *
     * @param HrPolicy $hrPolicy
     * @return Response
     */
    public function show(HrPolicy $hrPolicy)
    {
        $title = $hrPolicy->title;
        $action = 'show';

        return view('layouts.show', compact('hrPolicy', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param hrPolicy $hrPolicy
     * @return Response
     */
    public function edit(HrPolicy $hrPolicy)
    {
        $title = 'Edit '.$hrPolicy->title.'';

        return view('layouts.edit', compact('title', 'hrPolicy'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param hrPolicyRequest $request
     * @param hrPolicy $hrPolicy
     * @return Response
     */
    public function update(HrPolicyRequest $request, HrPolicy $hrPolicy)
    {
        try {
            $hrPolicy->title = $request->title;
            $hrPolicy->description = $request->description;
            $hrPolicy->save();

            if ($request->hasFile('file') != '') {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $document = Str::random(8) .'.'.$extension;

                $destinationPath = public_path().'/uploads/documents/';
                $file->move($destinationPath, $document);
                $hrPolicy->file = $document;
                $hrPolicy->save();
            }

            $this->activity->record([
                'module'    => $this->module,
                'module_id' => $hrPolicy->id,
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
     * @param hrPolicy $hrPolicy
     * @return Response
     */
    public function delete(hrPolicy $hrPolicy)
    {
        $title = 'Delete '.$hrPolicy->title.'';

        return view('employee.delete', compact('hrPolicy', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param hrPolicy $hrPolicy
     * @return Response
     */
    public function destroy(HrPolicy $hrPolicy)
    {
        $hrPolicy->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $hrPolicy->id,
            'activity'  => 'Deleted',
        ]);
        Flash::success('Deleted successfully');

        return 'Deleted';
    }
}
