<?php
namespace App\Http\Controllers\Secure;

use App\Helpers\CompanySettings;
use App\Helpers\GeneralHelper;
use App\Helpers\Thumbnail;
use App\Http\Requests\Secure\VisitorLogRequest;
use App\Models\DailyActivity;
use App\Models\Employee;

use App\Models\HelpDesk;
use App\Models\HelpDeskCategory;
use App\Models\HelpDeskPriority;
use App\Models\HelpDeskSubCategory;
use App\Models\EmployeeKpiActivity;
use App\Models\Procurement;
use App\Models\ProcurementCategory;
use App\Models\ProcurementItem;
use App\Models\ProcurementItemSupplier;
use App\Models\ProcurementRequest;
use App\Models\ProcurementSubCategory;
use App\Models\UserDocument;
use App\Models\VisitorLog;
use App\Repositories\VisitorLogRepository;
use App\Repositories\SectionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Flash;
use Illuminate\Http\Request;
use Sentinel;
use App\Notifications\SendSMS;
use Illuminate\Support\Facades\DB;
use PDF;

class ProcurementRequestController extends SecureController
{
    /**
     * @var SectionRepository
     */
    private $sectionRepository;
    /**
     * @var VisitorLogRepository
     */
    private $visitorLogRepository;

    protected $module = 'procurementRequest';



    /**
     * EmployeeController constructor.
     * @param VisitorLogRepository $visitorLogRepository
     */
    public function __construct(
        VisitorLogRepository $visitorLogRepository,
        SectionRepository $sectionRepository
    ) {

        parent::__construct();
        $this->visitorLogRepository = $visitorLogRepository;
        $this->sectionRepository = $sectionRepository;

        /*$this->middleware('authorized:view_employees', ['only' => ['index', 'data']]);
        $this->middleware('authorized:student.approval', ['only' => ['ajaxStudentApprove', 'data']]);
        $this->middleware('authorized:student.approveinfo', ['only' => ['pendingApproval', 'data']]);
        $this->middleware('authorized:student.create', ['only' => ['create', 'store', 'getImport', 'postImport', 'downloadTemplate']]);
        $this->middleware('authorized:student.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:student.delete', ['only' => ['delete', 'destroy']]);*/

        view()->share('type', 'procurementRequest');

    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('procurement.procurement');
        $procurements = Procurement::get();

       return view('procurementRequest.index', compact('title', 'procurements'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function filter(Request $request)
    {
        $date = Carbon::create($request->ddate);
        $title = trans('dailyActivity.visitorLogs');

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))->with(['attendance' => function($query) use($request) {
            $query->whereRaw('MONTH(date) = ?', [$request->month])->whereRaw('YEAR(date) = ?', [$request->year]);
        }]);

        $procurements = ProcurementRequest::all();


       return view('procurementRequest.load', compact('title', 'procurements', 'request', 'employees'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexAll()
    {
        $title = trans('procurement.procurements');
        $procurements = ProcurementRequest::get();

        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('All Employees', 'all')
            ->toArray();

        $sections = $this->sectionRepository
            ->getAll()
            ->get()
            ->pluck('title', 'id')
            ->prepend(trans('student.select_section'), 0)
            ->toArray();



       return view('procurementRequest.all', compact('title', 'procurements', 'employees', 'sections'));
    }







    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = trans('procurement.new');

        /*$procurement = Procurement::firstOrCreate
        (
            [
                'employee_id' => session('current_employee'),
                'company_year_id' => session('current_company_year'),
            ]
        );*/
        /*$employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->prepend('Select Employee', '')
            ->toArray();*/

        $procurementCategories = ProcurementCategory::all()
            ->pluck('title', 'id')
            ->prepend(trans('helpDesk.select_category'), 0)
            ->toArray();


        return view('layouts.create', compact('title', 'procurementCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VisitorLogRequest $request
     * @return Response
     */


    public function store(ProcurementRequest $request)
    {

        try
        {
            DB::transaction(function() use ($request) {

                $procurement = new Procurement();
                $procurement->company_id = session('current_company');
                $procurement->company_year_id = session('current_company_year');
                $procurement->employee_id = $request->employee_id ?? session('current_employee');
                $procurement->title = $request->title;
                $procurement->description = $request->description;
                $procurement->procurement_category_id = $request->procurement_category_id;
                $procurement->procurement_subcategory_id = $request->procurement_subcategory_id;
                $procurement->status = 0;
                $procurement->quantity = $request->quantity;
                $procurement->save();

                /*Send email to notify Supervisors*/
                /*@GeneralHelper::sendNewEmployee_email($user,$employee);*/


                if ($request->hasFile('file') != "") {
                    $file = $request->file('file');
                    $extension = $file->getClientOriginalExtension();
                    $picture = Str::random(8)  . '.' . $extension;

                    $destinationPath = public_path() . '/uploads/avatar/';
                    $file->move($destinationPath, $picture);
                    Thumbnail::generate_image_thumbnail($destinationPath . $picture, $destinationPath . 'thumb_' . $picture);
                    $procurement->file = $picture;
                    $procurement->save();
                }

            });
        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }

        return response('<div class="alert alert-success">Procurement Request Created Successfully</div>') ;

    }

    /**
     * Display the specified resource.
     *
     * @param ProcurementRequest $procurementRequest
     * @return Response
     */
    public function show(ProcurementRequest $procurementRequest)
    {
        $title = $procurementRequest->request_number;
        $action = 'show';

        return view('layouts.show', compact('procurementRequest', 'title', 'action'));
    }





    /**
     * Show the form for editing the specified resource.
     *
     * @param ProcurementRequest $procurementRequest
     * @return Response
     */
    public function edit(ProcurementRequest $procurementRequest)
    {
        $title = 'Edit '. $procurementRequest->title.'';
        $employees = Employee::where('status', 1)->where('company_id', session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    "id"   => $item->id,
                    "name" => isset($item->user) ? $item->user->full_name. '  '. ' |' .$item->sID . '| ' : "",
                ];
            })->pluck("name", 'id')
            ->toArray();

        return view('layouts.edit', compact('title', 'procurementRequest', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VisitorLogRequest $request
     * @param ProcurementRequest $procurementRequest
     * @return Response
     */
    public function update(ProcurementRequest $request, Procurement $procurement)
    {

        try
        {

            $procurement->visited_employee_id = $request->visited_employee_id;
            $procurement->name = $request->name;
            $procurement->purpose = $request->purpose;
            $procurement->organization = $request->organization;
            $procurement->email = $request->email;
            $procurement->phone_number = $request->phone_number;
            $procurement->car_number = $request->car_number;
            $procurement->access_card_number = $request->access_card_number;
            $procurement->check_in = $request->check_in;
            $procurement->check_out = $request->check_out;
            $procurement->observations = $request->observations;
            $procurement->save();



        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $procurement->id,
            'activity'  => 'updated'
        ]);
       /* Flash::success("Employee Information Updated Successfully");*/

        }

        catch (\Exception $e) {

            return Response ('<div class="alert alert-danger">'.$e->getMessage().'</div>') ;
        }

        {
            return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
        }
    }

    /**
     * @param DailyActivity $dailyActivity
     * @return Response
     */
    public function delete(ProcurementRequest $procurementRequest)
    {
        $title = 'Delete '. $visitorLog->name.'';


        return view('employee.delete', compact('visitorLog', 'title'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param VisitorLog $visitorLog
     * @return Response
     */
    public function destroy(ProcurementRequest $procurementRequest)
    {

        $procurementRequest->delete();

        $this->activity->record([
            'module'    => $this->module,
            'module_id' => $visitorLog->id,
            'activity'  => 'Deleted'
        ]);
        Flash::success("Deleted successfully");
        return 'Deleted';
    }



    public function findProcurementSubCategory(Request $request)
    {
        $items = ProcurementItem::where('procurement_category_id', $request->procurement_category_id)->get();

        return view('procurementRequest.procurementItems', compact('items'));
    }





}
