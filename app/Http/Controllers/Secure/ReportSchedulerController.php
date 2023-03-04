<?php

namespace App\Http\Controllers\Secure;

use Aloha\Twilio\Twilio;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Http\Requests;
use App\Models\Country;
use App\Models\CustomField;
use App\Models\CustomFieldMeta;
use App\Models\Document;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Note;
use App\Models\Office;
use App\Models\ReportScheduler;
use App\Models\ReportSchedulerClient;
use App\Models\ReportSchedulerIdentification;
use App\Models\ReportSchedulerNextOfKin;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Clickatell\Api\ClickatellHttp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class ReportSchedulerController extends Controller
{
    public function __construct()
    {
        $this->middleware('sentinel');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Sentinel::hasAccess('borrowers')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $data = ReportScheduler::get();

        return view('report_scheduler.data', compact('data'));
    }

    public function pending()
    {
        if (! Sentinel::hasAccess('borrowers')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $data = ReportScheduler::where('status', 'pending')->get();

        return view('borrower.pending', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Sentinel::hasAccess('borrowers.create')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }

        return view('report_scheduler.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Sentinel::hasAccess('borrowers.create')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = new ReportScheduler();

        $report_scheduler->created_by_id = Sentinel::getUser()->id;
        $report_scheduler->staff_id = $request->staff_id;
        $report_scheduler->office_id = $request->office_id;
        $report_scheduler->external_id = $request->external_id;
        $report_scheduler->mobile = $request->mobile;
        $report_scheduler->phone = $request->phone;
        $report_scheduler->email = $request->email;
        $report_scheduler->name = $request->name;
        $report_scheduler->street = $request->street;
        $report_scheduler->address = $request->address;
        $report_scheduler->joined_date = $request->joined_date;
        $report_scheduler->notes = $request->notes;
        $report_scheduler->save();
        $office = Office::find($request->office_id);
        $report_scheduler->account_no = $office->external_id.'-'.$report_scheduler->id;
        $report_scheduler->save();
        foreach ($request->clients as $key) {
            $report_scheduler_client = new ReportSchedulerClient();
            $report_scheduler_client->report_scheduler_id = $report_scheduler->id;
            $report_scheduler_client->client_id = $key;
            $report_scheduler->save();
        }
        //GeneralHelper::audit_trail("Added report_scheduler  with id:" . $report_scheduler->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect('report_scheduler/'.$report_scheduler->id.'/show');
    }

    public function show($report_scheduler)
    {
        if (! Sentinel::hasAccess('borrowers.view')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $users = User::all();
        $user = [];
        foreach ($users as $key) {
            $user[$key->id] = $key->first_name.' '.$key->last_name;
        }
        //get custom fields
        //$custom_fields = CustomFieldMeta::where('category', 'borrowers')->where('parent_id', $report_scheduler->id)->get();
        return view('report_scheduler.show', compact('report_scheduler', 'user', 'custom_fields'));
    }

    public function edit($report_scheduler)
    {
        if (! Sentinel::hasAccess('borrowers.update')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $users = User::all();
        $user = [];
        foreach ($users as $key) {
            $user[$key->id] = $key->first_name.' '.$key->last_name;
        }
        $countries = [];
        foreach (Country::all() as $key) {
            $countries[$key->id] = $key->name;
        }

        return view('report_scheduler.edit', compact('report_scheduler', 'user', 'custom_fields', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.update')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = ReportScheduler::find($id);
        $report_scheduler->staff_id = $request->staff_id;
        $report_scheduler->mobile = $request->mobile;
        $report_scheduler->phone = $request->phone;
        $report_scheduler->email = $request->email;
        $report_scheduler->name = $request->name;
        $report_scheduler->street = $request->street;
        $report_scheduler->address = $request->address;
        $report_scheduler->joined_date = $request->joined_date;
        $report_scheduler->notes = $request->notes;
        $report_scheduler->save();
        Flash::success(trans('general.successfully_saved'));

        return redirect('report_scheduler/data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        ReportScheduler::destroy($id);
        Loan::where('borrower_id', $id)->delete();
        LoanRepayment::where('borrower_id', $id)->delete();
        GeneralHelper::audit_trail('Deleted borrower  with id:'.$id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect('borrower/data');
    }

    public function picture(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = ReportScheduler::find($id);
        if (! empty($report_scheduler->picture)) {
            @unlink(public_path().'/uploads/'.$report_scheduler->picture);
        }
        if ($request->hasFile('picture')) {
            $file = ['picture' => Input::file('picture')];
            $rules = ['picture' => 'required|mimes:jpeg,jpg,bmp,png'];
            $validator = Validator::make($file, $rules);
            if ($validator->fails()) {
                Flash::warning(trans('general.validation_error'));

                return redirect()->back()->withInput()->withErrors($validator);
            } else {
                $fname = str_slug($report_scheduler->account_no, '_').''.uniqid().'.'.$request->file('picture')->guessExtension();
                $report_scheduler->picture = $fname;
                $request->file('picture')->move(public_path().'/uploads',
                    $fname);
            }
        }
        $report_scheduler->save();
        Flash::success(trans('general.successfully_saved'));

        return redirect()->back();
    }

    public function approve(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.approve')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = ReportScheduler::find($id);
        $report_scheduler->status = 'active';
        $report_scheduler->activated_date = $request->activated_date;
        $report_scheduler->activated_by_id = Sentinel::getUser()->id;
        $report_scheduler->save();
        //GeneralHelper::audit_trail("Approved borrower  with id:" . $report_scheduler->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect()->back();
    }

    public function decline(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.approve')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = ReportScheduler::find($id);
        $report_scheduler->status = 'declined';
        $report_scheduler->declined_date = $request->declined_date;
        $report_scheduler->declined_by_id = Sentinel::getUser()->id;
        $report_scheduler->save();
        //GeneralHelper::audit_trail("Declined borrower  with id:" . $report_scheduler->id);
        Flash::success(trans('general.successfully_saved'));

        return redirect()->back();
    }

    public function blacklist(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.blacklist')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = ReportScheduler::find($id);
        $report_scheduler->blacklisted = 1;
        $report_scheduler->save();
        GeneralHelper::audit_trail('Blacklisted borrower  with id:'.$id);
        Flash::success(trans('general.successfully_saved'));

        return redirect()->back();
    }

    public function unBlacklist(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.blacklist')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler = ReportScheduler::find($id);
        $report_scheduler->blacklisted = 0;
        $report_scheduler->save();
        GeneralHelper::audit_trail('Undo Blacklist for borrower  with id:'.$id);
        Flash::success(trans('general.successfully_saved'));

        return redirect()->back();
    }

    //report_scheduler identification
    public function store_report_scheduler_client(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.create')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $rules = [
            'client_id' => 'required',
        ];
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        } else {
            if (ReportSchedulerClient::where('report_scheduler_id', $id)->where('client_id', $request->client_id)->count() > 0) {
                Flash::warning('Client already added');

                return redirect()->back()->withInput()->withErrors($validator);
            }
            $report_scheduler_client = new ReportSchedulerClient();
            $report_scheduler_client->report_scheduler_id = $id;
            $report_scheduler_client->client_id = $request->client_id;
            $report_scheduler_client->save();
            //GeneralHelper::audit_trail("Added report_scheduler  with id:" . $report_scheduler->id);
            Flash::success(trans('general.successfully_saved'));

            return redirect()->back();
        }
    }

    public function delete_report_scheduler_client(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }

        ReportSchedulerClient::destroy($id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect()->back();
    }

    //report_scheduler documents
    public function store_report_scheduler_document(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.create')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $rules = [
            'name' => 'required',
            'attachment' => 'required',
        ];
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        } else {
            $report_scheduler_document = new Document();
            $report_scheduler_document->record_id = $id;
            $report_scheduler_document->type = 'report_scheduler';
            $report_scheduler_document->name = $request->name;
            $report_scheduler_document->notes = $request->notes;
            if ($request->hasFile('attachment')) {
                $file = ['attachment' => Input::file('attachment')];
                $rules = ['attachment' => 'required|mimes:jpeg,jpg,bmp,png,pdf,docx,doc,xlsx,pptx,xls'];
                $validator = Validator::make($file, $rules);
                if ($validator->fails()) {
                    Flash::warning(trans('general.validation_error'));

                    return redirect()->back()->withInput()->withErrors($validator);
                } else {
                    $fname = str_slug($request->name, '_').''.uniqid().'.'.$request->file('attachment')->guessExtension();
                    $report_scheduler_document->location = $fname;
                    $request->file('attachment')->move(public_path().'/uploads',
                        $fname);
                }
            }
            $report_scheduler_document->save();
            //GeneralHelper::audit_trail("Added report_scheduler  with id:" . $report_scheduler->id);
            Flash::success(trans('general.successfully_saved'));

            return redirect()->back();
        }
    }

    public function delete_report_scheduler_document(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $report_scheduler_document = Document::find($id);
        if (! empty($report_scheduler_document->location)) {
            @unlink(public_path().'/uploads/'.$report_scheduler_document->location);
        }
        Document::destroy($id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect()->back();
    }

    //report_scheduler notes
    public function store_note(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.create')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $rules = [
            'notes' => 'required',
        ];
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        } else {
            $note = new Note();
            $note->reference_id = $id;
            $note->created_by_id = Sentinel::getUser()->id;
            $note->type = 'report_scheduler';
            $note->notes = $request->notes;
            $note->save();
            //GeneralHelper::audit_trail("Added report_scheduler  with id:" . $report_scheduler->id);
            Flash::success(trans('general.successfully_saved'));

            return redirect()->back();
        }
    }

    public function delete_note(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }

        Note::destroy($id);
        Flash::success(trans('general.successfully_deleted'));

        return redirect()->back();
    }

    public function show_note($note)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }

        return View::make('report_scheduler.show_note', compact('note'))->render();
    }

    public function edit_note($note)
    {
        if (! Sentinel::hasAccess('borrowers.delete')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }

        return View::make('report_scheduler.edit_note', compact('note'))->render();
    }

    public function update_note(Request $request, $id)
    {
        if (! Sentinel::hasAccess('borrowers.create')) {
            Flash::warning('Permission Denied');

            return redirect('/');
        }
        $rules = [
            'notes' => 'required',
        ];
        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        } else {
            $note = Note::find($id);
            $note->notes = $request->notes;

            $note->save();
            //GeneralHelper::audit_trail("Added report_scheduler  with id:" . $report_scheduler->id);
            Flash::success(trans('general.successfully_saved'));

            return redirect()->back();
        }
    }
}
