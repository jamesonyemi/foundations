<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Flash;
use App\Helpers\GeneralHelper;
use App\Models\Client;
use App\Models\Group;
use App\Models\Loan;
use App\Models\LoanProductCharge;
use App\Models\LoanRepaymentSchedule;
use App\Models\LoanTransaction;
use App\Models\PaymentType;
use App\Models\Savings;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CronController extends SecureController
{
    public function index()
    {
        if (Setting::where('setting_key', 'enable_cron')->first()->setting_value == 0) {
            return 0;
        }

        //check for charges
        foreach (Loan::where('status', 'disbursed')->with('repayment_schedules')->with('loan_product')->with('client')->with('group')->get() as $loan) {
            $loan_product = $loan->loan_product;
            if (! empty($loan_product) && count($loan_product->charges) > 0) {
                $overdue_schedule = '';
                $total_due = 0;
                $principal_due = 0;
                $interest_due = 0;
                $balance = 0;
                $overdue_balance = 0;
                $overdue_interest = 0;
                $overdue_principal = 0;
                $total_interest = 0;
                $total_principal = 0;
                foreach ($loan->repayment_schedules as $schedule) {
                    $total_principal = $total_principal + $schedule->principal - $schedule->principal_waived - $schedule->principal_written_off;
                    $total_interest = $total_interest + $schedule->interest - $schedule->interest_waived - $schedule->interest_written_off;
                    $balance = $balance + ($schedule->principal - $schedule->principal_paid - $schedule->principal_waived - $schedule->principal_written_off + $schedule->interest - $schedule->interest_paid - $schedule->interest_waived - $schedule->interest_written_off + $schedule->fees - $schedule->fees_paid - $schedule->fees_waived - $schedule->fees_written_off + $schedule->penalty - $schedule->penalty_paid - $schedule->penalty_waived - $schedule->penalty_written_off);
                    if ($schedule->due_date == date_format(date_sub(date_create(date('Y-m-d')),
                            date_interval_create_from_date_string($loan_product->arrears_grace_days.' days')),
                            'Y-m-d') && $balance > 0
                    ) {
                        $overdue_balance = $balance;
                        $overdue_schedule = $schedule;
                        $overdue_interest = $total_interest;
                        $overdue_principal = $total_principal;
                    }
                }
                foreach (LoanProductCharge::where('loan_product_id', $loan_product->id)->with('charge')->get() as $key) {
                    $charge = $key->charge;
                    if (! empty($charge)) {
                        $amount = 0;
                        if ($charge->charge_option == 'flat') {
                            $amount = $charge->amount;
                        }
                        if ($charge->charge_option == 'installment_principal_due') {
                            $amount = $charge->amount * $overdue_principal / 100;
                        }
                        if ($charge->charge_option == 'installment_principal_interest_due') {
                            $amount = $charge->amount * ($overdue_principal + $overdue_interest) / 100;
                        }
                        if ($charge->charge_option == 'installment_interest_due') {
                            $amount = $charge->amount * $overdue_principal / 100;
                        }
                        if ($charge->charge_option == 'installment_total_due') {
                            $amount = $charge->amount * $overdue_balance / 100;
                        }
                        if ($charge->charge_option == 'original_principal') {
                            $amount = $charge->amount * $loan->principal / 100;
                        }
                        if ($charge->charge_option == 'principal_due') {
                            $amount = $charge->amount * $total_principal / 100;
                        }
                        if ($charge->charge_option == 'interest_due') {
                            $amount = $charge->amount * $total_interest / 100;
                        }
                        if ($charge->charge_option == 'total_due') {
                            $amount = $charge->amount * $balance / 100;
                        }
                        //missed payment penalty
                        if ($charge->charge_type == 'overdue_installment_fee') {
                            if (! empty($overdue_schedule)) {
                                //check if cron has not been run before
                                if (LoanTransaction::where('charge_id', $charge->id)->where('loan_id', $loan->id)->where('loan_repayment_schedule_id', $overdue_schedule->id)->doesntExist()) {
                                    $schedule = LoanRepaymentSchedule::find($overdue_schedule->id);
                                    $schedule->penalty = $schedule->penalty + $amount;
                                    $schedule->save();
                                    //loan transaction
                                    $loan_transaction = new LoanTransaction();
                                    $loan_transaction->office_id = $loan->office_id;
                                    $loan_transaction->loan_id = $loan->id;
                                    $loan_transaction->charge_id = $charge->id;
                                    $loan_transaction->reversible = 1;
                                    $loan_transaction->loan_repayment_schedule_id = $schedule->id;
                                    $loan_transaction->transaction_type = 'overdue_installment_fee';
                                    $loan_transaction->date = $overdue_schedule->due_date;
                                    $date = explode('-', $overdue_schedule->due_date);
                                    $loan_transaction->year = $date[0];
                                    $loan_transaction->month = $date[1];
                                    $loan_transaction->debit = $amount;
                                    $loan_transaction->save();
                                }
                            }
                        }
                        //overdue maturity
                        if ($charge->charge_type == 'overdue_maturity' && strtotime($loan->expected_maturity_date) < strtotime(date('Y-m-d'))) {
                            //loan overdue maturity
                            //check if the charge has not been applied before
                            if (LoanTransaction::where('charge_id', $charge->id)->where('loan_id', $loan->id)->where('loan_repayment_schedule_id', $loan->repayment_schedules->last()->id)->doesntExist()) {
                                $schedule = LoanRepaymentSchedule::find($loan->repayment_schedules->last()->id);
                                $schedule->penalty = $schedule->penalty + $amount;
                                $schedule->save();
                                //loan transaction
                                $loan_transaction = new LoanTransaction();
                                $loan_transaction->office_id = $loan->office_id;
                                $loan_transaction->loan_id = $loan->id;
                                $loan_transaction->charge_id = $charge->id;
                                $loan_transaction->reversible = 1;
                                $loan_transaction->loan_repayment_schedule_id = $loan->repayment_schedules->last()->id;
                                $loan_transaction->transaction_type = 'overdue_maturity';
                                $loan_transaction->date = $loan->repayment_schedules->last()->due_date;
                                $date = explode('-', $loan->repayment_schedules->last()->due_date);
                                $loan_transaction->year = $date[0];
                                $loan_transaction->month = $date[1];
                                $loan_transaction->debit = $amount;
                                $loan_transaction->save();
                            }
                        }
                    }
                }
            }
        }
        //check for repayment reminder

        $repayment_days = Setting::where('setting_key',
            'auto_repayment_days')->first()->setting_value;
        foreach (DB::table('loan_repayment_schedules as lr')->selectRaw('lr.id,lr.loan_id,lr.due_date,l.client_id,l.group_id,l.principal as amount,l.client_type,lp.arrears_grace_days as grace,lr.principal,lr.principal_waived,lr.principal_paid,lr.principal_written_off,lr.interest,lr.interest_waived,lr.interest_paid,lr.interest_written_off,lr.fees,lr.fees_waived,lr.fees_paid,lr.fees_written_off,lr.penalty,lr.penalty_waived,lr.penalty_paid,lr.penalty_written_off')->join('loans as l', function ($join) {
            $join->on('lr.loan_id', '=', 'l.id');
            $join->where('l.status', 'disbursed');
        })->join('loan_products as lp', function ($join) {
            $join->on('l.loan_product_id', '=', 'lp.id');
        })->havingRaw("CURDATE()=date_sub(due_date,INTERVAL $repayment_days DAY)")->havingRaw('(COALESCE(principal,0)+COALESCE(interest,0)+COALESCE(fees,0)+COALESCE(penalty,0)-COALESCE(principal_waived,0)-COALESCE(principal_written_off,0)-COALESCE(principal_paid,0)-COALESCE(interest_waived,0)-COALESCE(interest_written_off,0)-COALESCE(interest_paid,0)-COALESCE(fees_waived,0)-COALESCE(fees_written_off,0)-COALESCE(fees_paid,0)-COALESCE(penalty_written_off,0)-COALESCE(penalty_paid,0))>0')->get() as $key) {
            //only schedules with due amount returned
            $mobile = '';
            $clientName = '';
            $email = '';
            if ($key->client_type == 'client') {
                $client = Client::find($key->client_id);
                if (! empty($client)) {
                    if ($client->client_type == 'individual') {
                        $clientName = $client->first_name.' '.$client->middle_name.' '.$client->last_name;
                    }
                    if ($client->client_type == 'business') {
                        $clientName = $client->full_name;
                    }
                    $mobile = $client->mobile;
                    $email = $client->email;
                }
            }
            if ($key->client_type == 'group') {
                $group = Group::find($key->group_id);
                if (! empty($group)) {
                    $mobile = $group->mobile;
                    $clientName = $group->name;
                    $email = $group->email;
                }
            }
            //send sms
            if (Setting::where('setting_key', 'auto_repayment_sms_reminder')->first()->setting_value == 1) {
                if (! empty($mobile) && Setting::where('setting_key',
                        'sms_enabled')->first()->setting_value == 1
                ) {
                    $body = Setting::where('setting_key',
                        'loan_payment_reminder_sms_template')->first()->setting_value;
                    $body = str_replace('{clientName}', $clientName, $body);
                    $body = str_replace('{clientMobile}', $mobile, $body);
                    $body = str_replace('{paymentDate}', $key->due_date, $body);
                    $body = str_replace('{loanNumber}', $key->loan_id, $body);
                    $body = str_replace('{paymentAmount}', ($key->principal - $key->principal_paid - $key->principal_waived - $key->principal_written_off + $key->interest - $key->interest_paid - $key->interest_waived - $key->interest_written_off + $key->fees - $key->fees_paid - $key->fees_waived - $key->fees_written_off + $key->penalty - $key->penalty_paid - $key->penalty_waived - $key->penalty_written_off), $body);
                    $body = strip_tags($body);
                    GeneralHelper::send_sms($mobile, $body);
                }
            }
            if (Setting::where('setting_key', 'auto_repayment_email_reminder')->first()->setting_value == 1) {
                if (! empty($email)) {
                    Mail::to($email)->send(new UpcomingRepaymentReminderEmail($key->loan_id, $key->id));
                }
            }
            //send email
        }
        //Calculate savings interest
        foreach (Savings::with('savings_product')->where('next_interest_calculation_date', Carbon::today()->format('Y-m-d'))->get() as $savings) {
            GeneralHelper::determine_savings_interest_earned($savings->id);
        }
        //post interest
        foreach (Savings::with('savings_product')->where('next_interest_posting_date', Carbon::today()->format('Y-m-d'))->get() as $savings) {
            GeneralHelper::post_savings_interest_earned($savings->id);
        }
        Setting::where('setting_key',
            'cron_last_run')->update(['setting_value' => date('Y-m-d H:i:s')]);

        return 'Cron job executed successfully';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
}
