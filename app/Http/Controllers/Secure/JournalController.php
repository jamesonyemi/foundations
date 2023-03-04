<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\Collateral;
use App\Models\CollateralType;
use App\Models\GlClosure;
use App\Models\GlJournalEntry;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\PaymentDetail;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class JournalController extends SecureController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*if (!Sentinel::hasAccess('accounting.journals.view')) {
            Flash::warning("Permission Denied");
            return redirect()->back();
        }*/
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $data = [];
        if (! empty($start_date)) {
            $data = GlJournalEntry::where('company_id', session('current_company'))->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($gl_account_id, function ($query) use ($gl_account_id) {
                if ($gl_account_id != 0) {
                    $query->where('gl_account_id', '=', $gl_account_id);
                }
            })->whereBetween('date',
                [$start_date, $end_date])->orderBy('date', 'asc')->get();
        }

        return view('journal.data',
            compact('start_date',
                'end_date', 'office_id', 'data', 'gl_account_id'));
    }

    public function create()
    {
        if (! Sentinel::hasAccess('accounting.journals.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }

        return view('journal.create');
    }

    public function store(Request $request)
    {
        if (! Sentinel::hasAccess('accounting.journals.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $payment_detail = new PaymentDetail();
        $payment_detail->company_id = session('company_id');
        $payment_detail->payment_type_id = $request->payment_type_id;
        $payment_detail->account_number = $request->account_number;
        $payment_detail->cheque_number = $request->cheque_number;
        $payment_detail->routing_code = $request->routing_code;
        $payment_detail->receipt_number = $request->receipt_number;
        $payment_detail->bank = $request->bank;
        $payment_detail->save();
        $journal = new GlJournalEntry();
        $journal->created_by_id = Sentinel::getUser()->id;
        $journal->company_id = session('company_id');
        $journal->gl_account_id = $request->credit;
        $journal->payment_detail_id = $payment_detail->id;
        $journal->manual_entry = 1;
        $date = explode('-', $request->date);
        $journal->date = $request->date;
        $journal->year = $date[0];
        $journal->month = $date[1];
        $journal->transaction_type = 'manual_entry';
        $journal->credit = $request->amount;
        $journal->name = $request->name;
        $journal->save();

        $journal = new GlJournalEntry();
        $journal->company_id = session('company_id');
        $journal->created_by_id = Sentinel::getUser()->id;
        $journal->gl_account_id = $request->debit;
        $journal->payment_detail_id = $payment_detail->id;
        $journal->manual_entry = 1;
        $date = explode('-', $request->date);
        $journal->date = $request->date;
        $journal->year = $date[0];
        $journal->month = $date[1];
        $journal->transaction_type = 'manual_entry';
        $journal->debit = $request->amount;
        $journal->name = $request->name;
        $journal->save();
        Flash::success(trans('general.successfully_saved'));
        GeneralHelper::audit_trail('Create', 'Journals', $journal->id);
        if (isset($request->return_url)) {
            return redirect($request->return_url);
        }

        return redirect('accounting/journal');
    }

    public function reconciliation(Request $request)
    {
        if (! Sentinel::hasAccess('accounting.journals.reconciliation.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $show = $request->show;
        $data = [];
        if (! empty($start_date)) {
            $data = GlJournalEntry::where('company_id', session('current_company'))->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($gl_account_id, function ($query) use ($gl_account_id) {
                if ($gl_account_id != 0) {
                    $query->where('gl_account_id', '=', $gl_account_id);
                }
            })->when($show, function ($query) use ($show) {
                if ($show == '1') {
                    $query->where('reconciled', '=', 0);
                }
                if ($show == '2') {
                    $query->where('reconciled', '=', 1);
                }
            })->whereBetween('date',
                [$start_date, $end_date])->orderBy('date', 'asc')->get();
        }

        return view('journal.reconciliation',
            compact('start_date',
                'end_date', 'office_id', 'data', 'gl_account_id', 'show'));
    }

    public function store_reconciliation(Request $request)
    {
        if (! Sentinel::hasAccess('accounting.journals.reconciliation.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        if (! empty($request->reconcile_ids)) {
            foreach ($request->reconcile_ids as $key) {
                $journal = GlJournalEntry::find($key);
                if (! empty($journal)) {
                    $journal->reconciled = 1;
                    $journal->save();
                }
            }
        }
        Flash::success(trans('general.successfully_saved'));
        GeneralHelper::audit_trail('Create Reconciliation', 'Journals', '');
        if (isset($request->return_url)) {
            return redirect($request->return_url);
        }

        return redirect()->back();
    }

    public function period()
    {
        if (! Sentinel::hasAccess('accounting.period.view')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $data = GlClosure::all();

        return view('journal.period',
            compact('data'));
    }

    public function store_period(Request $request)
    {
        if (! Sentinel::hasAccess('accounting.period.create')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $gl_closure = new GlClosure();
        $gl_closure->company_id = session('company_id');
        $gl_closure->created_by_id = Sentinel::getUser()->id;
        $gl_closure->created_by_id = Sentinel::getUser()->id;
        $gl_closure->office_id = $request->office_id;
        $gl_closure->closing_date = $request->closing_date;
        $gl_closure->notes = $request->notes;
        $gl_closure->save();
        Flash::success(trans('general.successfully_saved'));
        GeneralHelper::audit_trail('Create Period', 'Journals', '');
        if (isset($request->return_url)) {
            return redirect($request->return_url);
        }

        return redirect('accounting/period/data');
    }

    public function delete_period(Request $request, $id)
    {
        if (! Sentinel::hasAccess('accounting.period.delete')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        GlClosure::destroy($id);
        Flash::success(trans('general.successfully_saved'));
        GeneralHelper::audit_trail('Delete Period', 'Journals', '');
        if (isset($request->return_url)) {
            return redirect($request->return_url);
        }

        return redirect()->back();
    }
}
