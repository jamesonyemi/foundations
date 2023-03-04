<?php

namespace App\Http\Controllers\Secure;

use App\Exports\ExportReport;
use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\Client;
use App\Models\GlAccount;
use App\Models\GlJournalEntry;
use App\Models\Grant;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanRepaymentSchedule;
use App\Models\LoanTransaction;
use App\Models\Office;
use App\Models\Savings;
use App\Models\SavingsTransaction;
use App\Models\Setting;
use App\Models\User;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use PDF;

class ReportController extends SecureController
{
    public function loan_product(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = LoanProduct::where('company_id', session('current_company'))->get();

        return view('report.loan_product',
            compact('data', 'start_date',
                'end_date'));
    }

    public function loan_projection(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $monthly_collections = [];
        $start_date1 = date('Y-m-d');
        for ($i = 1; $i < 14; $i++) {
            $d = explode('-', $start_date1);
            //get loans in that period
            $payments = 0;
            $payments_due = 0;
            foreach (LoanSchedule::where('branch_id', session('branch_id'))->where('year', $d[0])->where('month',
                $d[1])->get() as $key) {
                if (! empty($key->loan)) {
                    if ($key->loan->status == 'disbursed' || $key->loan->status == 'written_off' || $key->loan->status == 'closed') {
                        $payments_due = $payments_due + $key->principal + $key->fees + $key->interest + $key->penalty;
                    }
                }
            }
            $payments_due = round($payments_due, 2);
            $ext = ' '.$d[0];
            array_push($monthly_collections, [
                'month' => date_format(date_create($start_date1),
                    'M'.$ext),
                'due' => $payments_due,

            ]);
            //add 1 month to start date
            $start_date1 = date_format(date_add(date_create($start_date1),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }
        $monthly_collections = json_encode($monthly_collections);

        return view('report.loan_projection',
            compact('monthly_collections', 'start_date',
                'end_date'));
    }

    public function financial_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.financial_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('financial_report.data',
            compact('start_date',
                'end_date'));
    }

    public function loan_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.loan_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('loan_report.data',
            compact('start_date',
                'end_date'));
    }

    public function grant_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.grant_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('grant_report.data',
            compact('start_date',
                'end_date'));
    }

    public function client_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.client_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('client_report.data',
            compact('start_date',
                'end_date'));
    }

    public function company_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('company_report.data',
            compact('start_date',
                'end_date'));
    }

    public function savings_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('savings_report.data',
            compact('start_date',
                'end_date'));
    }

    public function trial_balance(Request $request)
    {
        if (! Sentinel::hasAccess('reports.trial_balance')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::orderBy('gl_code', 'asc')->get();
        }

        return view('financial_report.trial_balance',
            compact('start_date',
                'end_date', 'data', 'office_id'));
    }

    public function trial_balance_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.trial_balance')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
            $pdf = PDF::loadView('financial_report.trial_balance_pdf', compact('start_date',
                'end_date', 'data', 'office_id'));

            return $pdf->download(trans_choice('general.trial_balance', 1).' : '.$request->end_date.'.pdf');
        }
    }

    public function trial_balance_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.trial_balance')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('financial_report.trial_balance_pdf', $data), trans_choice('general.trial_balance', 1).'.xlsx');
        }
    }

    public function trial_balance_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.trial_balance')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('financial_report.trial_balance_pdf', $data), trans_choice('general.trial_balance', 1).'.csv');
        }
    }

    public function income_statement(Request $request)
    {
        if (! Sentinel::hasAccess('reports.income_statement')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
        }

        return view('financial_report.income_statement',
            compact('start_date',
                'end_date', 'data', 'office_id'));
    }

    public function income_statement_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.income_statement')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer

            $pdf = PDF::loadView('financial_report.income_statement_pdf', compact('start_date',
                'end_date', 'data', 'office_id'));

            return $pdf->download(trans_choice('general.income', 1).' '.trans_choice('general.statement',
                    1).' : '.$request->end_date.'.pdf');
        }
    }

    public function income_statement_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.income_statement')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer

            $data = [

                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('financial_report.income_statement_pdf', $data), trans_choice('general.statement',
                    1).' : '.$request->end_date.'.xlsx');
        }
    }

    public function income_statement_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.income_statement')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer

            $data = [

                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('financial_report.income_statement_pdf', $data), trans_choice('general.statement',
                    1).' : '.$request->end_date.'.csv');
        }
    }

    public function balance_sheet(Request $request)
    {
        if (! Sentinel::hasAccess('reports.balance_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];

        return view('financial_report.balance_sheet',
            compact('start_date',
                'end_date', 'office_id'));
    }

    public function balance_sheet_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.balance_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $pdf = PDF::loadView('financial_report.balance_sheet_pdf', compact('start_date',
            'end_date', 'office_id'));

        return $pdf->download(trans_choice('general.balance', 1).' '.trans_choice('general.sheet',
                1).' : '.$request->end_date.'.pdf');
    }

    public function balance_sheet_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.balance_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($end_date)) {
            $data = [

                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('financial_report.balance_sheet_pdf', $data), trans_choice('general.balance', 1).' '.trans_choice('general.sheet',
                    1).' as at '.$request->end_date.'.xlsx');
        }
    }

    public function balance_sheet_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.balance_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($end_date)) {
            $data = [

                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('financial_report.balance_sheet_pdf', $data), trans_choice('general.balance', 1).' '.trans_choice('general.sheet',
                    1).' as at '.$request->end_date.'.csv');
        }
    }

    public function expected_repayments(Request $request)
    {
        if (! Sentinel::hasAccess('reports.expected_repayment')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
        }

        return view('loan_report.expected_repayments',
            compact('start_date',
                'end_date', 'data', 'office_id'));
    }

    public function expected_repayments_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.expected_repayment')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
            $pdf = PDF::loadView('loan_report.expected_repayments_pdf', compact('start_date',
                'end_date', 'data', 'office_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.expected', 1).' '.trans_choice('general.repayment',
                    2).'.pdf');
        }
    }

    public function expected_repayments_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.expected_repayment')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,

            ];

            return Excel::download(new ExportReport('loan_report.expected_repayments_pdf', $data), trans_choice('general.expected', 1).' '.trans_choice('general.repayment',
                    2).'.xlsx');
        }
    }

    public function expected_repayments_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.expected_repayment')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,

            ];

            return Excel::download(new ExportReport('loan_report.expected_repayments_pdf', $data), trans_choice('general.expected', 1).' '.trans_choice('general.repayment',
                    2).'.csv');
        }
    }

    public function grant_disbursement_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.grant_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Grant::when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->get();
        }

        return view('grant_report.disbursement_report',
            compact('start_date',
                'end_date', 'data', 'office_id'));
    }

    public function grant_disbursement_report_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.grant_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Grant::when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->get();
            $pdf = PDF::loadView('grant_report.disbursement_report_pdf', compact('start_date',
                'end_date', 'data', 'office_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.disbursement', 1).' '.trans_choice('general.report',
                    2).'.pdf');
        }
    }

    public function grant_disbursement_report_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.grant_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            $data = Grant::when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,

            ];

            return Excel::download(new ExportReport('grant_report.disbursement_report_pdf', $data), trans_choice('general.disbursement', 1).' '.trans_choice('general.report',
                    2).'.xlsx');
        }
    }

    public function grant_disbursement_report_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.grant_reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($start_date)) {
            $data = Grant::when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,

            ];

            return Excel::download(new ExportReport('grant_report.disbursement_report_pdf', $data), trans_choice('general.disbursement', 1).' '.trans_choice('general.report',
                    2).'.csv');
        }
    }

    public function repayments_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.repayments_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = '';
        if (! empty($start_date)) {
            if ($office_id != 0) {
                $data = LoanTransaction::where('company_id', session('current_company'))->where('transaction_type',
                    'repayment')->where('reversed', 0)->where('office_id',
                    $office_id)->whereBetween('date',
                    [$start_date, $end_date])->with('loan')->with('office')->get();
            } else {
                $data = LoanTransaction::where('company_id', session('current_company'))->where('transaction_type',
                    'repayment')->where('reversed', 0)->whereBetween('date',
                    [$start_date, $end_date])->with('loan')->with('office')->get();
            }
        }

        return view('loan_report.repayments_report',
            compact('start_date',
                'end_date', 'data', 'office_id'));
    }

    public function repayments_report_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.repayments_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = LoanTransaction::where('company_id', session('current_company'))->where('transaction_type',
                'repayment')->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->whereBetween('date',
                [$start_date, $end_date])->with('loan')->with('office')->get();
            $pdf = PDF::loadView('loan_report.repayments_report_pdf', compact('start_date',
                'end_date', 'data'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.repayment', 2).' '.trans_choice('general.report',
                    1).'.pdf');
        }
    }

    public function repayments_report_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.repayments_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = LoanTransaction::where('company_id', session('current_company'))->where('transaction_type',
                'repayment')->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->whereBetween('date',
                [$start_date, $end_date])->with('loan')->with('office')->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];

            return Excel::download(new ExportReport('loan_report.repayments_report_pdf', $data), trans_choice('general.repayment', 2).' '.trans_choice('general.report',
                    1).'.xlsx');
        }
    }

    public function repayments_report_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.repayments_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = LoanTransaction::where('company_id', session('current_company'))->where('transaction_type',
                'repayment')->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->whereBetween('date',
                [$start_date, $end_date])->with('loan')->with('office')->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];

            return Excel::download(new ExportReport('loan_report.repayments_report_pdf', $data), trans_choice('general.repayment', 2).' '.trans_choice('general.report',
                    1).'.csv');
        }
    }

    public function collection_sheet(Request $request)
    {
        if (! Sentinel::hasAccess('reports.collection_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->join('loan_repayment_schedules', 'loans.id', '=', 'loan_repayment_schedules.loan_id')->whereBetween('loan_repayment_schedules.due_date', [$start_date, $end_date])->get();
        }

        return view('loan_report.collection_sheet',
            compact('start_date',
                'end_date', 'data', 'office_id', 'loan_officer_id'));
    }

    public function collection_sheet_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.collection_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->join('loan_repayment_schedules', 'loans.id', '=', 'loan_repayment_schedules.loan_id')->whereBetween('loan_repayment_schedules.due_date', [$start_date, $end_date])->get();
            $pdf = PDF::loadView('loan_report.collection_sheet_pdf', compact('start_date',
                'end_date', 'data', 'office_id', 'loan_officer_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.collection', 1).' '.trans_choice('general.sheet',
                    2).'.pdf');
        }
    }

    public function collection_sheet_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.collection_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->join('loan_repayment_schedules', 'loans.id', '=', 'loan_repayment_schedules.loan_id')->whereBetween('loan_repayment_schedules.due_date', [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_officer_id' => $loan_officer_id,
            ];

            return Excel::download(new ExportReport('loan_report.collection_sheet_pdf', $data), trans_choice('general.collection', 1).' '.trans_choice('general.sheet',
                    1).'.xlsx');
        }
    }

    public function collection_sheet_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.collection_sheet')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->join('loan_repayment_schedules', 'loans.id', '=', 'loan_repayment_schedules.loan_id')->whereBetween('loan_repayment_schedules.due_date', [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_officer_id' => $loan_officer_id,
            ];

            return Excel::download(new ExportReport('loan_report.collection_sheet_pdf', $data), trans_choice('general.collection', 1).' '.trans_choice('general.sheet',
                    1).'.csv');
        }
    }

    public function arrears_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.arrears_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();
        }

        return view('loan_report.arrears_report',
            compact('end_date', 'data', 'office_id'));
    }

    public function arrears_report_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.arrears_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();
            $pdf = PDF::loadView('loan_report.arrears_report_pdf', compact('office_id',
                'end_date', 'data'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.arrears', 1).' '.trans_choice('general.report',
                    2).'.pdf');
        }
    }

    public function arrears_report_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.arrears_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();
            $data = [
                'data' => $data,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('loan_report.arrears_report_pdf', $data), trans_choice('general.arrears', 1).' '.trans_choice('general.report',
                    1).'.xlsx');
        }
    }

    public function arrears_report_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.arrears_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();
            $data = [
                'data' => $data,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('loan_report.arrears_report_pdf', $data), trans_choice('general.arrears', 1).' '.trans_choice('general.report',
                    1).'.csv');
        }
    }

    public function loan_portfolio(Request $request)
    {
        if (! Sentinel::hasAccess('reports.loan_portfolio')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
        }

        return view('loan_report.loan_portfolio',
            compact('end_date', 'data', 'office_id', 'loan_product_id'));
    }

    public function loan_portfolio_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.loan_portfolio')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $data = [];
        if (! empty($end_date)) {
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
            $pdf = PDF::loadView('loan_report.loan_portfolio_pdf', compact('office_id',
                'end_date', 'data', 'loan_product_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.portfolio', 1).' '.trans_choice('general.report',
                    1).'.pdf');
        }
    }

    public function loan_portfolio_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.loan_portfolio')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $data = [];
        if (! empty($end_date)) {
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
            $data = [
                'data' => $data,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_product_id' => $loan_product_id,
            ];

            return Excel::download(new ExportReport('loan_report.loan_portfolio_pdf', $data), trans_choice('general.portfolio', 1).' '.trans_choice('general.report',
                    1).'.xlsx');
        }
    }

    public function loan_portfolio_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.loan_portfolio')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $data = [];
        if (! empty($end_date)) {
            $data = Office::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('id', '=', $office_id);
                }
            })->get();
            $data = [
                'data' => $data,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_product_id' => $loan_product_id,
            ];

            return Excel::download(new ExportReport('loan_report.loan_portfolio_pdf', $data), trans_choice('general.portfolio', 1).' '.trans_choice('general.report',
                    1).'.csv');
        }
    }

    public function disbursed_loans(Request $request)
    {
        if (! Sentinel::hasAccess('reports.disbursed_loans')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $loan_officer_id = $request->loan_officer_id;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $status = $request->status;
        $data = '';
        if (! empty($start_date)) {
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                    if ($loan_officer_id != 0) {
                        $query->where('loan_officer_id', '=', $loan_officer_id);
                    }
                })->when($loan_product_id, function ($query) use ($loan_product_id) {
                    if ($loan_product_id != 0) {
                        $query->where('loan_product_id', '=', $loan_product_id);
                    }
                })->when($status, function ($query) use ($status) {
                    if ($status != 0) {
                        $query->where('status', '=', $status);
                    } else {
                        $query->whereNotIn('status', ['pending', 'approved', 'declined', 'withdrawn', 'new']);
                    }
                })->with('loan_officer')->with('office')->with('fund')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();
        }

        return view('loan_report.disbursed_loans',
            compact('start_date',
                'end_date', 'data', 'office_id', 'loan_product_id', 'loan_officer_id', 'status'));
    }

    public function disbursed_loans_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.disbursed_loans')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $loan_officer_id = $request->loan_officer_id;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $status = $request->status;
        if (! empty($end_date)) {
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                    if ($loan_officer_id != 0) {
                        $query->where('loan_officer_id', '=', $loan_officer_id);
                    }
                })->when($loan_product_id, function ($query) use ($loan_product_id) {
                    if ($loan_product_id != 0) {
                        $query->where('loan_product_id', '=', $loan_product_id);
                    }
                })->when($status, function ($query) use ($status) {
                    if ($status != 0) {
                        $query->where('status', '=', $status);
                    } else {
                        $query->whereNotIn('status', ['pending', 'approved', 'declined', 'withdrawn', 'new']);
                    }
                })->with('loan_officer')->with('office')->with('fund')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();

            $pdf = PDF::loadView('loan_report.disbursed_loans_pdf',
                compact('start_date',
                    'end_date', 'data', 'office_id', 'loan_product_id', 'loan_officer_id', 'status'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.disbursed', 1).' '.trans_choice('general.report',
                    1).'.pdf');
        }
    }

    public function disbursed_loans_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.disbursed_loans')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $loan_officer_id = $request->loan_officer_id;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $status = $request->status;
        if (! empty($end_date)) {
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                    if ($loan_officer_id != 0) {
                        $query->where('loan_officer_id', '=', $loan_officer_id);
                    }
                })->when($loan_product_id, function ($query) use ($loan_product_id) {
                    if ($loan_product_id != 0) {
                        $query->where('loan_product_id', '=', $loan_product_id);
                    }
                })->when($status, function ($query) use ($status) {
                    if ($status != 0) {
                        $query->where('status', '=', $status);
                    } else {
                        $query->whereNotIn('status', ['pending', 'approved', 'declined', 'withdrawn', 'new']);
                    }
                })->with('loan_officer')->with('office')->with('fund')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();

            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_product_id' => $loan_product_id,
                'status' => $status,
                'loan_officer_id' => $loan_officer_id,
            ];

            return Excel::download(new ExportReport('loan_report.disbursed_loans_pdf', $data), trans_choice('general.disbursement', 1).' '.trans_choice('general.report',
                    1).'.xlsx');
        }
    }

    public function disbursed_loans_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.disbursed_loans')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $loan_officer_id = $request->loan_officer_id;
        $office_id = $request->office_id;
        $loan_product_id = $request->loan_product_id;
        $status = $request->status;
        if (! empty($end_date)) {
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->whereBetween('disbursement_date',
                [$start_date, $end_date])->when($office_id, function ($query) use ($office_id) {
                    if ($office_id != 0) {
                        $query->where('office_id', '=', $office_id);
                    }
                })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                    if ($loan_officer_id != 0) {
                        $query->where('loan_officer_id', '=', $loan_officer_id);
                    }
                })->when($loan_product_id, function ($query) use ($loan_product_id) {
                    if ($loan_product_id != 0) {
                        $query->where('loan_product_id', '=', $loan_product_id);
                    }
                })->when($status, function ($query) use ($status) {
                    if ($status != 0) {
                        $query->where('status', '=', $status);
                    } else {
                        $query->whereNotIn('status', ['pending', 'approved', 'declined', 'withdrawn', 'new']);
                    }
                })->with('loan_officer')->with('office')->with('fund')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();

            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_product_id' => $loan_product_id,
                'status' => $status,
                'loan_officer_id' => $loan_officer_id,
            ];

            return Excel::download(new ExportReport('loan_report.disbursed_loans_pdf', $data), trans_choice('general.disbursement', 1).' '.trans_choice('general.report',
                    1).'.csv');
        }
    }

    public function client_numbers(Request $request)
    {
        if (! Sentinel::hasAccess('reports.client_numbers_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('client_report.client_numbers',
            compact('start_date',
                'end_date'));
    }

    public function client_numbers_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.client_numbers_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (! empty($end_date)) {
            $pdf = PDF::loadView('client_report.client_numbers_pdf', compact('start_date',
                'end_date', 'data'));
            //$pdf->setPaper('A4', 'landscape');
            return $pdf->download(trans_choice('general.client', 1).' '.trans_choice('general.number',
                    2).'.pdf');
        }
    }

    public function client_numbers_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.client_numbers_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if (! empty($end_date)) {
            $data = [
                'end_date' => $end_date,
                'start_date' => $start_date,
            ];

            return Excel::download(new ExportReport('client_report.client_numbers_pdf', $data), trans_choice('general.client', 1).' '.trans_choice('general.number',
                    2).'.xlsx');
        }
    }

    public function client_numbers_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.client_numbers_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if (! empty($end_date)) {
            $data = [
                'end_date' => $end_date,
                'start_date' => $start_date,
            ];

            return Excel::download(new ExportReport('client_report.client_numbers_pdf', $data), trans_choice('general.client', 1).' '.trans_choice('general.number',
                    2).'.csv');
        }
    }

    public function provisioning(Request $request)
    {
        if (! Sentinel::hasAccess('reports.provisioning')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();
        }

        return view('financial_report.provisioning',
            compact('end_date', 'data', 'office_id', 'loan_officer_id'));
    }

    public function provisioning_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.provisioning')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();

            $pdf = PDF::loadView('financial_report.provisioning_pdf', compact('start_date',
                'end_date', 'data', 'office_id', 'loan_officer_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.provisioning', 1).' '.trans_choice('general.report',
                    2).'.pdf');
        }
    }

    public function provisioning_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.provisioning')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();

            $data = [
                'data' => $data,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_officer_id' => $loan_officer_id,
            ];

            return Excel::download(new ExportReport('financial_report.provisioning_pdf', $data), trans_choice('general.provisioning', 1).' '.trans_choice('general.report',
                    2).'.xlsx');
        }
    }

    public function provisioning_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.provisioning')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $loan_officer_id = $request->loan_officer_id;
        $data = [];
        if (! empty($end_date)) {
            //get disbursed loans within specified period and officer
            $data = Loan::where('company_id', session('current_company'))->where('status', 'disbursed')->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($loan_officer_id, function ($query) use ($loan_officer_id) {
                if ($loan_officer_id != 0) {
                    $query->where('loan_officer_id', '=', $loan_officer_id);
                }
            })->with('loan_officer')->with('office')->with('loan_product')->with('client')->with('group')->with('repayment_schedules')->get();

            $data = [
                'data' => $data,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'loan_officer_id' => $loan_officer_id,
            ];

            return Excel::download(new ExportReport('financial_report.provisioning_pdf', $data), trans_choice('general.provisioning', 1).' '.trans_choice('general.report',
                    2).'.csv');
        }
    }

    public function products_summary(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('company_report.products_summary',
            compact('start_date',
                'end_date', 'data'));
    }

    public function products_summary_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if (! empty($end_date)) {
            $pdf = PDF::loadView('company_report.products_summary_pdf', compact('start_date',
                'end_date', 'data'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.product', 2).' '.trans_choice('general.summary',
                    1).'.pdf');
        }
    }

    public function products_summary_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if (! empty($end_date)) {
            $data = [];
        }
    }

    public function products_summary_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if (! empty($end_date)) {
            $data = [];
        }
    }

    public function general_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if (isset($request->end_date)) {
            $date = $request->end_date;
        } else {
            $date = date('Y-m-d');
        }
        //loan product pie data
        $loan_product_data = [];
        foreach (LoanProduct::all() as $key) {
            if (empty($start_date)) {
                $count = Loan::where('company_id', session('current_company'))->where('loan_product_id', $key->id)->where('branch_id',
                    session('branch_id'))->whereIn('status',
                    ['disbursed', 'closed', 'written_off', 'rescheduled'])->count();
            } else {
                $count = Loan::where('loan_product_id', $key->id)->where('branch_id',
                    session('branch_id'))->whereIn('status',
                    ['disbursed', 'closed', 'written_off', 'rescheduled'])->whereBetween('release_date',
                    [$start_date, $end_date])->count();
            }
            array_push($loan_product_data, [
                'product' => $key->name,
                'value' => $count,

            ]);
        }
        $monthly_net_income_data = [];
        $loop_date = date_format(date_sub(date_create($date),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i < 14; $i++) {
            $d = explode('-', $loop_date);
            //get loans in that period
            $total_income = 0;
            foreach (GlAccount::where('company_id', session('current_company'))->where('account_type', 'income')->get() as $key) {
                $cr = GlJournalEntry::where('company_id', session('current_company'))->where('account_id', $key->id)->where('year',
                    $d[0])->where('month',
                    $d[1])->where('branch_id',
                    session('branch_id'))->sum('credit');
                $dr = GlJournalEntry::where('company_id', session('current_company'))->where('account_id', $key->id)->where('year',
                    $d[0])->where('month',
                    $d[1])->where('branch_id',
                    session('branch_id'))->sum('debit');
                $balance = $cr - $dr;
                $total_income = $total_income + $balance;
            }
            $total_expenses = 0;
            foreach (GlAccount::where('company_id', session('current_company'))->where('account_type', 'expense')->get() as $key) {
                $cr = GlJournalEntry::where('company_id', session('current_company'))->where('account_id', $key->id)->where('year',
                    $d[0])->where('month',
                    $d[1])->where('branch_id',
                    session('branch_id'))->sum('credit');
                $dr = GlJournalEntry::where('company_id', session('current_company'))->where('account_id', $key->id)->where('year',
                    $d[0])->where('month',
                    $d[1])->where('branch_id',
                    session('branch_id'))->sum('debit');
                $balance = $dr - $cr;
                $total_expenses = $total_expenses + $balance;
            }
            array_push($monthly_net_income_data, [
                'month' => date_format(date_create($loop_date),
                    'M'.' '.$d[0]),
                'income' => $total_income,
                'expenses' => $total_expenses,
            ]);
            //add 1 month to start date
            $loop_date = date_format(date_add(date_create($loop_date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }
        //user registrations
        $monthly_borrower_data = [];
        $loop_date = date_format(date_sub(date_create($date),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i < 14; $i++) {
            $d = explode('-', $loop_date);
            //get loans in that period
            $count = Client::where('year',
                $d[0])->where('month',
                $d[1])->where('branch_id',
                session('branch_id'))->count();
            array_push($monthly_borrower_data, [
                'month' => date_format(date_create($loop_date),
                    'M'.' '.$d[0]),
                'value' => $count,
            ]);
            //add 1 month to start date
            $loop_date = date_format(date_add(date_create($loop_date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }
        $monthly_repayments_data = [];
        $loop_date = date_format(date_sub(date_create($date),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i < 14; $i++) {
            $d = explode('-', $loop_date);
            //get loans in that period
            $amount = LoanTransaction::where('transaction_type',
                'repayment')->where('reversed', 0)->where('year',
                $d[0])->where('month',
                $d[1])->where('branch_id',
                session('branch_id'))->sum('credit');
            array_push($monthly_repayments_data, [
                'month' => date_format(date_create($loop_date),
                    'M'.' '.$d[0]),
                'value' => $amount,
            ]);
            //add 1 month to start date
            $loop_date = date_format(date_add(date_create($loop_date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }
        $monthly_actual_expected_data = [];
        $monthly_disbursed_loans_data = [];
        $loop_date = date_format(date_sub(date_create($date),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i < 14; $i++) {
            $d = explode('-', $loop_date);
            $actual = 0;
            $expected = 0;
            $principal = 0;
            $actual = $actual + LoanTransaction::where('transaction_type',
                    'repayment')->where('reversed', 0)->where('year',
                    $d[0])->where('month',
                    $d[1])->where('branch_id',
                    session('branch_id'))->sum('credit');
            foreach (Loan::where('company_id', session('current_company'))->select('loan_schedules.principal', 'loan_schedules.interest', 'loan_schedules.penalty',
                'loan_schedules.fees')->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_schedules', 'loans.id', '=',
                'loan_schedules.loan_id')->where('loan_schedules.deleted_at', null)->where('loan_schedules.year',
                $d[0])->where('loan_schedules.month',
                $d[1])->get() as $key) {
                $expected = $expected + $key->interest + $key->penalty + $key->fees + $key->principal;
                $principal = $principal + $key->principal;
            }
            array_push($monthly_actual_expected_data, [
                'month' => date_format(date_create($loop_date),
                    'M'.' '.$d[0]),
                'actual' => $actual,
                'expected' => $expected,
            ]);
            array_push($monthly_disbursed_loans_data, [
                'month' => date_format(date_create($loop_date),
                    'M'.' '.$d[0]),
                'value' => $principal,
            ]);
            //add 1 month to start date
            $loop_date = date_format(date_add(date_create($loop_date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        $loan_product_data = json_encode($loan_product_data);
        $monthly_net_income_data = json_encode($monthly_net_income_data);
        $monthly_borrower_data = json_encode($monthly_borrower_data);
        $monthly_repayments_data = json_encode($monthly_repayments_data);
        $monthly_actual_expected_data = json_encode($monthly_actual_expected_data);
        $monthly_disbursed_loans_data = json_encode($monthly_disbursed_loans_data);

        return view('company_report.general_report',
            compact('loan_product_data', 'monthly_net_income_data', 'monthly_borrower_data', 'monthly_repayments_data',
                'monthly_actual_expected_data', 'monthly_disbursed_loans_data', 'start_date', 'end_date'));
    }

    public function journal(Request $request)
    {
        if (! Sentinel::hasAccess('reports.journals_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('financial_report.journal',
            compact('start_date',
                'end_date'));
    }

    public function ledger(Request $request)
    {
        if (! Sentinel::hasAccess('reports.journals_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return view('financial_report.ledger',
            compact('start_date',
                'end_date'));
    }

    public function savings_transactions(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_transactions')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = SavingsTransaction::where('company_id', session('current_company'))->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->whereBetween('date',
                [$start_date, $end_date])->get();
        }

        return view('savings_report.savings_transactions',
            compact('start_date',
                'end_date', 'data', 'office_id'));
    }

    public function savings_transactions_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_transactions')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = SavingsTransaction::where('company_id', session('current_company'))->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->whereBetween('date',
                [$start_date, $end_date])->get();
            $pdf = PDF::loadView('savings_report.savings_transactions_pdf', compact('start_date',
                'end_date', 'data', 'office_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.savings', 2).' '.trans_choice('general.transaction',
                    2).'.pdf');
        }
    }

    public function savings_transactions_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_transactions')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = SavingsTransaction::where('company_id', session('current_company'))->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->whereBetween('date',
                [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('savings_report.savings_transactions_pdf', $data), trans_choice('general.savings', 2).' '.trans_choice('general.transaction',
                    2).'.xlsx');
        }
    }

    public function savings_transactions_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_transactions')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        if (! empty($start_date)) {
            $data = SavingsTransaction::where('company_id', session('current_company'))->where('reversed', 0)->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->whereBetween('date',
                [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
            ];

            return Excel::download(new ExportReport('savings_report.savings_transactions_pdf', $data), trans_choice('general.savings', 2).' '.trans_choice('general.transaction',
                    2).'.csv');
        }
    }

    public function savings_accounts(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_accounts_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $field_officer_id = $request->field_officer_id;
        $savings_product_id = $request->savings_product_id;
        if (! empty($start_date)) {
            $data = Savings::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($savings_product_id, function ($query) use ($savings_product_id) {
                if ($savings_product_id != 0) {
                    $query->where('savings_product_id', '=', $savings_product_id);
                }
            })->when($field_officer_id, function ($query) use ($field_officer_id) {
                if ($field_officer_id != 0) {
                    $query->where('field_officer_id', '=', $field_officer_id);
                }
            })->whereBetween('approved_date',
                [$start_date, $end_date])->get();
        }

        return view('savings_report.savings_accounts',
            compact('start_date',
                'end_date', 'data', 'office_id', 'savings_product_id', 'field_officer_id'));
    }

    public function savings_accounts_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $field_officer_id = $request->field_officer_id;
        $savings_product_id = $request->savings_product_id;
        if (! empty($start_date)) {
            $data = Savings::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($savings_product_id, function ($query) use ($savings_product_id) {
                if ($savings_product_id != 0) {
                    $query->where('savings_product_id', '=', $savings_product_id);
                }
            })->when($field_officer_id, function ($query) use ($field_officer_id) {
                if ($field_officer_id != 0) {
                    $query->where('field_officer_id', '=', $field_officer_id);
                }
            })->whereBetween('approved_date',
                [$start_date, $end_date])->get();
            $pdf = PDF::loadView('savings_report.savings_accounts_pdf', compact('start_date',
                'end_date', 'data', 'office_id', 'field_officer_id', 'savings_product_id'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download(trans_choice('general.savings', 2).' '.trans_choice('general.account',
                    2).'.pdf');
        }
    }

    public function savings_accounts_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_accounts_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $field_officer_id = $request->field_officer_id;
        $savings_product_id = $request->savings_product_id;
        if (! empty($start_date)) {
            $data = Savings::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($savings_product_id, function ($query) use ($savings_product_id) {
                if ($savings_product_id != 0) {
                    $query->where('savings_product_id', '=', $savings_product_id);
                }
            })->when($field_officer_id, function ($query) use ($field_officer_id) {
                if ($field_officer_id != 0) {
                    $query->where('field_officer_id', '=', $field_officer_id);
                }
            })->whereBetween('approved_date',
                [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'field_officer_id' => $field_officer_id,
                'savings_product_id' => $savings_product_id,
            ];

            return Excel::download(new ExportReport('savings_report.savings_accounts_pdf', $data), trans_choice('general.savings', 2).' '.trans_choice('general.account',
                    2).'.xlsx');
        }
    }

    public function savings_accounts_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.savings_accounts_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $field_officer_id = $request->field_officer_id;
        $savings_product_id = $request->savings_product_id;
        if (! empty($start_date)) {
            $data = Savings::where('company_id', session('current_company'))->when($office_id, function ($query) use ($office_id) {
                if ($office_id != 0) {
                    $query->where('office_id', '=', $office_id);
                }
            })->when($savings_product_id, function ($query) use ($savings_product_id) {
                if ($savings_product_id != 0) {
                    $query->where('savings_product_id', '=', $savings_product_id);
                }
            })->when($field_officer_id, function ($query) use ($field_officer_id) {
                if ($field_officer_id != 0) {
                    $query->where('field_officer_id', '=', $field_officer_id);
                }
            })->whereBetween('approved_date',
                [$start_date, $end_date])->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'field_officer_id' => $field_officer_id,
                'savings_product_id' => $savings_product_id,
            ];

            return Excel::download(new ExportReport('savings_report.savings_accounts_pdf', $data), trans_choice('general.savings', 2).' '.trans_choice('general.account',
                    2).'.csv');
        }
    }

    public function ledger_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
        }

        return view('financial_report.ledger_report',
            compact('start_date',
                'end_date', 'data', 'office_id', 'gl_account_id'));
    }

    public function ledger_report_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
            $pdf = PDF::loadView('financial_report.ledger_report_pdf', compact('start_date',
                'end_date', 'data', 'office_id', 'gl_account_id'));

            return $pdf->download(trans_choice('general.ledger', 1).' : '.$request->end_date.'.pdf');
        }
    }

    public function ledger_report_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'gl_account_id' => $gl_account_id,
            ];

            return Excel::download(new ExportReport('financial_report.ledger_report_pdf', $data), trans_choice('general.ledger', 1).'.xlsx');
        }
    }

    public function ledger_report_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
            $data = GlAccount::where('company_id', session('current_company'))->orderBy('gl_code', 'asc')->get();
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'gl_account_id' => $gl_account_id,
            ];

            return Excel::download(new ExportReport('financial_report.ledger_report_pdf', $data), trans_choice('general.ledger', 1).'.csv');
        }
    }

    public function journals_report(Request $request)
    {
        if (! Sentinel::hasAccess('reports.journals_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $office_id = $request->office_id;
        $gl_account_id = $request->gl_account_id;
        $data = [];
        if (! empty($start_date)) {
            //get disbursed loans within specified period and officer
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

        return view('financial_report.journals_report',
            compact('start_date',
                'end_date', 'data', 'office_id', 'gl_account_id'));
    }

    public function journals_report_pdf(Request $request)
    {
        if (! Sentinel::hasAccess('reports.journals_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
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
            $pdf = PDF::loadView('financial_report.journals_report_pdf', compact('start_date',
                'end_date', 'data', 'office_id', 'gl_account_id'));

            return $pdf->download(trans_choice('general.journal', 2).' : '.$request->end_date.'.pdf');
        }
    }

    public function journals_report_excel(Request $request)
    {
        if (! Sentinel::hasAccess('reports.journals_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
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
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'gl_account_id' => $gl_account_id,
            ];

            return Excel::download(new ExportReport('financial_report.journals_report_pdf', $data), trans_choice('general.journal', 1).'.xlsx');
        }
    }

    public function journals_report_csv(Request $request)
    {
        if (! Sentinel::hasAccess('reports.journals_report')) {
            Flash::warning('Permission Denied');

            return redirect()->back();
        }
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
            $data = [
                'data' => $data,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'office_id' => $office_id,
                'gl_account_id' => $gl_account_id,
            ];

            return Excel::download(new ExportReport('financial_report.journals_report_pdf', $data), trans_choice('general.journal', 1).'.csv');
        }
    }
}
