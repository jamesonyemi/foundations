<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Secure\PaymentRequest;
use App\Http\Requests\Secure\PayRequest;
use App\Models\CompanyYear;
use App\Models\Employee;
use App\Models\FeeCategory;
use App\Models\GeneralLedger;
use App\Models\Invoice;
use App\Models\Option;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\StudentStatus;
use App\Repositories\ActivityLogRepository;
use App\Repositories\FeeCategoryRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\StudentRepository;
use DB;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use Sentinel;
use Session;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends SecureController
{
    /**
     * @var StudentRepository
     */
    private $studentRepository;

    /**
     * @var PaymentRepository
     */
    private $paymentRepository;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var FeeCategoryRepository
     */
    private $feeCategoryRepository;

    /**
     * PaymentController constructor.
     * @param PaymentRepository $paymentRepository
     * @param InvoiceRepository $invoiceRepository
     */
    protected $activity;

    protected $module = 'Fees Payments';

    public function __construct(
        StudentRepository $studentRepository,
        PaymentRepository $paymentRepository,
        InvoiceRepository $invoiceRepository,
        FeeCategoryRepository $feeCategoryRepository,
        ActivityLogRepository $activity
    ) {
        parent::__construct();

        $this->studentRepository = $studentRepository;
        $this->paymentRepository = $paymentRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->feeCategoryRepository = $feeCategoryRepository;
        $this->activity = $activity;

        $this->middleware('authorized:payment.show', ['only' => ['index', 'data']]);
        $this->middleware('authorized:payment.create', ['only' => ['create', 'store']]);
        $this->middleware('authorized:payment.edit', ['only' => ['update', 'edit']]);
        $this->middleware('authorized:payment.delete', ['only' => ['delete', 'destroy']]);

        view()->share('type', 'payment');

        $columns = ['id', 'title', 'full_name', 'amount', 'date'];
        view()->share('columns', $columns);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $title = trans('payment.payment');

        /* $total_payment = Payment::sum('amount');
         $total_invoice = Invoice::sum('amount');*/
        $general_ledger = GeneralLedger::where('debit', '>', 0)
            ->where('semester_id', session('current_company_semester'))
            ->with('student.user', 'student.programme')->get();
        $count = 1;

        /*$payments = [['title' => trans('payment.total_payment'), 'items' => $total_payment, 'color' => "#0D47A1"],
            ['title' => trans('payment.total_invoice'), 'items' => $total_invoice, 'color' => "#00838F"]];*/

        $students = $this->studentRepository->getAllForSchool(session('current_company'))
            ->with('user', 'programme')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? $item->user->full_name.' '.$item->sID : '',
                ];
            })->pluck('name', 'id')
            ->prepend(trans('account.select_student'), '')
            ->toArray();

        return view('payment.index', compact('title', 'students', 'general_ledger', 'count'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->generateParam();
        $title = trans('payment.new');

        return view('layouts.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store2(PaymentRequest $request)
    {
        $invoice = Invoice::find($request['invoice_id']);

        $payment = new Payment($request->all());
        $payment->company_year_id = session('current_company_year');
        $payment->semester_id = session('current_company_semester');
        $payment->user_id = $invoice->user_id;
        $payment->currency_id = $invoice->currency_id;
        $payment->officer_user_id = Sentinel::getUser()->id;
        $payment->reciept_number = Str::random(8) ;
        $payment->save();

        if ($request->status == 'Payed') {
            /*$invoice->paid = $invoice->paid+$request->amount;
            $invoice->amount = $invoice->amount-$request->amount;
            $invoice->save();*/

            foreach ($invoice->items as $item) {
                PaymentItem::create(['fee_category_id'  => $item->fee_category_id,
                    'payment_id' => $payment->id,
                    'amount'   => $item->amount,
                    'title'   => $item->feeCategory->title,

                ]);

                /*CREDIT THE GENERAL LEDGER*/
                $generalLedger = new GeneralLedger();
                $generalLedger->student_id = $invoice->student_id;
                $generalLedger->user_id = $invoice->user_id;
                $generalLedger->company_id = session('current_company');
                $generalLedger->company_year_id = session('current_company_year');
                $generalLedger->semester_id = session('current_company_semester');
                $generalLedger->narration = $item->feeCategory->title;
                @$generalLedger->account_id = $item->feeCategory->credit_account_id;
                $generalLedger->amount = $item->amount;
                $generalLedger->fee_category_id = $item->fee_category_id;
                $generalLedger->transaction_date = now();
                $generalLedger->transaction_type = 'credit';
                $generalLedger->save();

                /*DEBIT THE GENERAL LEDGER*/
                $generalLedger = new GeneralLedger();
                $generalLedger->student_id = $invoice->student_id;
                $generalLedger->user_id = $invoice->user_id;
                $generalLedger->company_id = session('current_company');
                $generalLedger->company_year_id = session('current_company_year');
                $generalLedger->semester_id = session('current_company_semester');
                $generalLedger->narration = $item->feeCategory->title;
                @$generalLedger->account_id = $item->feeCategory->debit_account_id;
                $generalLedger->amount = -$item->amount;
                $generalLedger->fee_category_id = $item->fee_category_id;
                $generalLedger->transaction_date = now();
                $generalLedger->transaction_type = 'debit';
                $generalLedger->save();
            }
        }

        /*MAKE STUDENT ACTIVE*/
        StudentStatus::firstOrCreate(['company_id' => session('current_company'), 'company_year_id' => session('current_company_year'), 'semester_id' => session('current_company_semester'), 'student_id' => $invoice->student_id]);

        return redirect('/payment')->with('status', 'Payment Successfull!');
    }

    public function store(PaymentRequest $request)
    {
        if ($student = Employee::find($request->student_id)) {
            try {
                @$invoice = GeneralLedger::whereHas('student')->where('student_id', $student->id)
                ->where('semester_id', session('current_company_semester'))->first();

                if (is_null($invoice)) {
                    $fees = FeeCategory::all()->where('section_id', $student->section_id)
                    ->where('company_id', '=', session('current_company'));

                    foreach ($fees as $fee) {
                        $generalLedger = new GeneralLedger();
                        $generalLedger->student_id = $student->id;
                        $generalLedger->user_id = $student->user_id;
                        $generalLedger->company_id = session('current_company');
                        $generalLedger->company_year_id = session('current_company_year');
                        $generalLedger->semester_id = session('current_company_semester');
                        $generalLedger->narration = $fee->title;
                        $generalLedger->account_id = $fee->credit_account_id;
                        if ($student->country_id == 1) {
                            $generalLedger->credit = $fee->local_amount;
                        } else {
                            $generalLedger->credit = $fee->foreign_amount;
                        }
                        $generalLedger->fee_category_id = $fee->id;
                        $generalLedger->transaction_date = now();
                        $generalLedger->transaction_type = 'credit';
                        $generalLedger->save();
                    }
                }

                $generalLedger = new GeneralLedger();
                $generalLedger->student_id = $student->id;
                $generalLedger->user_id = Sentinel::getUser()->id;
                $generalLedger->company_id = session('current_company');
                $generalLedger->company_year_id = session('current_company_year');
                $generalLedger->semester_id = session('current_company_semester');
                $generalLedger->narration = $request->narration;
                @$generalLedger->account_id = $student->section->debit_account_id;
                $generalLedger->debit = $request->amount;
                $generalLedger->transaction_date = now();
                $generalLedger->save();
            } catch (\Exception $e) {
                return Response('<div class="alert alert-danger">'.$e->getMessage().'</div>');
            }

            if ($generalLedger->save()) {
                /*MAKE STUDENT ACTIVE*/
                StudentStatus::firstOrCreate(['company_id' => session('current_company'),
                    'company_year_id' => session('current_company_year'),
                    'semester_id' => session('current_company_semester'),
                    'student_id' => $student->id, ]);

                return response('<div class="alert alert-success">
                 Payment Recorded Successfully for <strong> '.$student->user->full_name.' </strong> <br>
                 Balance: <strong> '.$student->balance.' </strong>
                 </div>');
            } else {
                return response('<div class="alert alert-danger">Operation Not Successful!!!</div>');
            }
        } else {
            return response('<div class="alert alert-danger">Student ID Not found</div>');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Payment $payment
     * @return Response
     */
    public function show(Payment $payment)
    {
        $title = trans('payment.details');
        $action = 'show';

        return view('layouts.show', compact('payment', 'title', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Payment $payment
     * @return Response
     */
    public function edit(Payment $payment)
    {
        $title = trans('payment.edit');
        $this->generateParam();

        return view('layouts.edit', compact('title', 'payment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PaymentRequest $request
     * @param  Payment $payment
     *
     * @return Response
     */
    public function update(PaymentRequest $request, Payment $payment)
    {
        $invoice = Invoice::find($payment->invoice_id);

        $payment->update($request->all());

        if ($request->status == 'payed') {
            $invoice->paid = 1;
            $invoice->save();
        }
        PaymentItem::where('payment_id', $payment->id)->delete();

        foreach ($invoice->items as $item) {
            PaymentItem::create(['option_id'  => $item->option_id,
                'payment_id' => $payment->id,
                'quantity'   => $item->quantity,
            ]);
        }

        return redirect('/payment');
    }

    /**
     * @param Payment $payment
     * @return Response
     */
    public function delete(Payment $payment)
    {
        $title = trans('payment.delete');

        return view('/payment/delete', compact('payment', 'title'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Payment $payment
     * @return Response
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect('/payment');
    }

    public function data()
    {
        /*$one_school = (Settings::get('account_one_school')=='yes')?true:false;
        if($one_school &&  $this->user->inRole('accountant')){*/
        $payments = $this->paymentRepository->getAllStudentsForSchool(session('current_company'), session('current_company_year'), session('current_company_semester')); /*
        }else{
            $payments = $this->paymentRepository->getAll();
        }*/
        $payments = $payments->with('user')->get()
            ->map(function ($payment) {
                return [
                    'id' => @$payment->id,
                    'title' => @$payment->student->sID,
                    'full_name' => isset($payment->user) ? @$payment->user->full_name : '',
                    'amount' => @$payment->amount,
                    'date' => @$payment->created_at->formatLocalized('%A %d %B %Y'),
                    /*"payment_method" => @$payment->payment_method,*/
                ];
            });

        return Datatables::make($payments)
            ->/*addColumn('actions', '@if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'payment.edit\', Sentinel::getUser()->permissions)))
                                    <a href="{{ url(\'/payment/\' . $id . \'/edit\' ) }}" class="btn btn-success btn-sm" >
                                            <i class="fa fa-pencil-square-o "></i>  {{ trans("table.edit") }}</a>
                                    @endif
                                    <a href="{{ url(\'/payment/\' . $id . \'/show\' ) }}" class="btn btn-primary btn-sm" >
                                            <i class="fa fa-eye"></i>  {{ trans("table.details") }}</a>
                                    @if(!Sentinel::getUser()->inRole(\'admin\') || Sentinel::getUser()->inRole(\'super_admin\') || (Sentinel::getUser()->inRole(\'admin\') && Settings::get(\'multi_school\') == \'no\') || (Sentinel::getUser()->inRole(\'admin\') && in_array(\'payment.delete\', Sentinel::getUser()->permissions)))
                                     <a href="{{ url(\'/payment/\' . $id . \'/delete\' ) }}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> {{ trans("table.delete") }}</a>
                                    @endif')

             ->rawColumns( [ 'actions' ] )*/make();
    }

    private function generateParam()
    {
        $one_school = (Settings::get('account_one_school') == 'yes') ? true : false;
        if ($one_school && $this->user->inRole('accountant')) {
            $invoices = $this->invoiceRepository->getAllStudentsForSchool(session('current_company'));
        } else {
            $invoices = $this->invoiceRepository->getAll();
        }
        $invoices = $invoices->with('user')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'title' => $invoice->title.'('.$invoice->balance.') '.(isset($invoice->user) ? $invoice->user->full_name.' '.@$invoice->user->student[0]->sID : ''),
                ];
            })
            ->pluck('title', 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        $students = $this->studentRepository->getAllForSchoolFees(session('current_company'))
            ->with('user')
            ->get()
            ->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => isset($item->user) ? '('.$item->balance.')  '.$item->user->full_name.'  '.' '.$item->user->student[0]->sID.' ' : '',
                ];
            })
            ->pluck('name', 'id')
            ->prepend(trans('student.select_student'), 0)
            ->toArray();

        $payment_method = Option::where('category', 'payment_methods')->pluck('title', 'value')->toArray();
        $status_payment = Option::where('category', 'status_payment')->pluck('title', 'value')->toArray();

        view()->share('invoices', $invoices);
        view()->share('students', $students);
        view()->share('payment_method', $payment_method);
        view()->share('status_payment', $status_payment);

        $fee_categories = $this->feeCategoryRepository->getAll()
            ->get()
            ->map(function ($item) {
                return [
                    'id'    => $item->id,
                    'title' => $item->title,
                ];
            })->pluck('title', 'id')->toArray();
        view()->share('fee_categories', $fee_categories);
    }

    public function pay(Invoice $invoice)
    {
        $title = trans('payment.pay_invoice');

        return view('/payment.pay', compact('invoice', 'title'));
    }

    public function paypalPayment(Invoice $invoice, PayRequest $request)
    {
        $params = [
            'cancelUrl' => url('/payment/'.$invoice->id.'/paypal_cancel'),
            'returnUrl' => url('/payment/'.$invoice->id.'/paypal_success'),
            'name' => $invoice->title,
            'description' => $invoice->description,
            'amount' => $invoice->amount,
            'currency' => Settings::get('currency'),
        ];

        Session::put('params', $params);
        Session::save();

        $gateway = Omnipay::create('PayPal_Express');
        $gateway->setUsername(Settings::get('paypal_username'));
        $gateway->setPassword(Settings::get('paypal_password'));
        $gateway->setSignature(Settings::get('paypal_signature'));
        $gateway->setTestMode(Settings::get('paypal_testmode'));

        $response = $gateway->purchase($params)->send();

        if ($response->isSuccessful()) {
            // payment was successful: update database
        } elseif ($response->isRedirect()) {
            // redirect to offsite payment gateway
            $response->redirect();
        } else {
            // payment failed: display message to customer
            echo $response->getMessage();
        }
    }

    public function paypalSuccess(Invoice $invoice)
    {
        $gateway = Omnipay::create('PayPal_Express');
        $gateway->setUsername(Settings::get('paypal_username'));
        $gateway->setPassword(Settings::get('paypal_password'));
        $gateway->setSignature(Settings::get('paypal_signature'));
        $gateway->setTestMode(Settings::get('paypal_testmode'));

        $params = Session::get('params');

        $response = $gateway->completePurchase($params)->send();
        $paypalResponse = $response->getData();
        $title = '';
        if (isset($paypalResponse['PAYMENTINFO_0_ACK']) && $paypalResponse['PAYMENTINFO_0_ACK'] === 'Success') {
            $payment = new Payment();
            $payment->title = $invoice->title;
            $payment->description = $invoice->description;
            $payment->invoice_id = $invoice->id;
            $payment->amount = $paypalResponse['PAYMENTINFO_0_AMT'];
            $payment->status = $paypalResponse['PAYMENTINFO_0_PAYMENTSTATUS'];
            $payment->paykey = $paypalResponse['TOKEN'];
            $payment->timestamp = $paypalResponse['TIMESTAMP'];
            $payment->correlation_id = $paypalResponse['CORRELATIONID'];
            $payment->ack = $paypalResponse['ACK'];
            $payment->transaction_id = $paypalResponse['PAYMENTINFO_0_TRANSACTIONID'];
            $payment->status = $paypalResponse['ACK'];
            $payment->payment_method = 'Paypal';
            $payment->user_id = $invoice->user_id;
            $payment->save();

            $invoice->paid = ($paypalResponse['ACK'] == 'Success' || $paypalResponse['ACK'] == 'SuccessWithWarning') ? 1 : 0;
            $invoice->save();

            return redirect('/studentsection/payment');
        } else {
            $title = 'Error';
        }

        return view('result', compact('paypalResponse', 'title'));
    }

    public function stripe(Invoice $invoice, Request $request)
    {
        $creditCardToken = $request->stripeToken;
        $payment = new Payment();
        $payment->newSubscription('main', Settings::get('payment_plan'))->create($creditCardToken);
        $payment->title = $invoice->title;
        $payment->description = $invoice->description;
        $payment->invoice_id = $invoice->id;
        $payment->amount = $invoice->amount;
        $payment->status = 'Payed';
        $payment->payment_method = 'Stripe';
        $payment->user_id = $invoice->user_id;
        $payment->save();

        $invoice->paid = 1;
        $invoice->save();

        return redirect('/studentsection/payment');
    }
}
