<?php

/**
 * Created by PhpStorm.
 * User: Tj
 * Date: 6/29/2016
 * Time: 3:11 PM
 */

namespace App\Helpers;

use App\Mail\StudentApproveMail;
use App\Models\Applicant;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\AuditTrail;
use App\Models\BscPerspective;
use App\Models\Center;
use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyYear;
use App\Models\Competency;
use App\Models\CompetencyFramework;
use App\Models\CompetencyGrade;
use App\Models\CompetencyLevel;
use App\Models\CompetencyType;
use App\Models\DailyAttendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCompetencyMatrix;
use App\Models\EmployeeKpiActivity;
use App\Models\EmployeeKpiScore;
use App\Models\EmployeeKpiTimeline;
use App\Models\EmployeeYearGrade;
use App\Models\Expense;
use App\Models\Gl_entry;
use App\Models\GlAccount;
use App\Models\GlJournalEntry;
use App\Models\Holiday;
use App\Models\JobDescription;
use App\Models\Kpi;
use App\Models\KpiObjective;
use App\Models\KpiPerformanceReview;
use App\Models\KpiResponsibility;
use App\Models\KpiTimeline;
use App\Models\Kra;
use App\Models\Loan;
use App\Models\LoanRepaymentSchedule;
use App\Models\LoanTransaction;
use App\Models\LoginHistory;
use App\Models\OtherIncome;
use App\Models\PayeSetUp;
use App\Models\Payroll;
use App\Models\PayrollMeta;
use App\Models\PayrollSetup;
use App\Models\Position;
use App\Models\QualificationFramework;
use App\Models\Savings;
use App\Models\SavingsProduct;
use App\Models\SavingsTransaction;
use App\Models\Sector;
use App\Models\Setting;
use App\Models\StaffLeave;
use App\Models\StaffLeavePlan;
use App\Models\SuccessionPlanning;
use App\Models\User;
use App\Models\VisitorLog;
use App\Notifications\KpiActivityApprovedEmail;
use App\Notifications\KpiActivityCreatedApproverEmail;
use App\Notifications\KpiActivityCreatedEmail;
use App\Notifications\KpiApprovedApproverEmail;
use App\Notifications\KpiApprovedEmail;
use App\Notifications\KpiCreatedApproverEmail;
use App\Notifications\KpiCreatedEmail;
use App\Notifications\LeaveApplicationNotification;
use App\Notifications\LeaveApproveNotification;
use App\Notifications\LoginNotification;
use App\Notifications\NewApplicant;
use App\Notifications\PostCommentCreatedEmail;
use App\Notifications\PostCommentCreatedOwnerEmail;
use App\Notifications\PostCreatedEmail;
use App\Notifications\SendEmail;
use App\Notifications\SendKpiCascadeEmail;
use App\Notifications\SendNewEmployeeEmail;
use App\Notifications\SendSMS;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Nexmo\Laravel\Facade\Nexmo;

class GeneralHelper
{
    //get active theme
    public static function get_active_theme_directory($sep = '.')
    {
        return 'themes'.$sep.Setting::where('setting_key', 'active_theme')->first()->setting_value;
    }

    /*
     * determine interest
     */

    public static function calculatePaye2($income)
    {
        if (!empty($amount)) {
            $paye = PayeSetUp::get();
            $payrollSetup = PayrollSetup::first();
            if ($income > $payrollSetup->ssf_maximum_limit)
            {
                $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $payrollSetup->ssf_maximum_limit);
            }
            else
            {
                $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $income);
            }
            $principal = $amount;
            $exceeding_income = 0;
            $tax = 0;
            foreach ($paye as $key) {
                if (($loop->first)) {
                    $tax = ($key->rate / 100) * $principal;
                    $principal = $principal-$tax;
                }
            }

            return ($tax);
        } else {
            return 0;
        }
    }

    public static function calc_income_tax2($income) {
        $paye = PayeSetUp::get();
        $payrollSetup = PayrollSetup::first();
        if ($income > $payrollSetup->ssf_maximum_limit)
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $payrollSetup->ssf_maximum_limit);
        }
        else
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $income);
        }

        $exceeding_income1 = 0;
        $exceeding_income2 = 0;
        $exceeding_income3 = 0;
        $exceeding_income4 = 0;
        $exceeding_income5 = 0;
        $exceeding_income6 = 0;
        $tax1 = 0;
        $tax2 = 0;
        $tax3 = 0;
        $tax4 = 0;
        $tax5 = 0;
        $tax6 = 0;




        if ($taxableIncome > $paye[0]->paye_tier)
        {
            $exceeding_income = $taxableIncome - $paye[0]->paye_tier;
            $tax1 += ( $exceeding_income * ($paye[0]->rate / 100 ));
            $exceeding_income1 += $exceeding_income;
        }

        else
        {
            $tax1 += ( $taxableIncome * ($paye[0]->rate / 100 ));
        }

            if ($exceeding_income1 > $paye[1]->paye_tier)
            {
                $exceeding_income2 += $exceeding_income1 - $paye[1]->paye_tier;
                if ($exceeding_income2 > 0)
                {
                    $tax2 += ( $paye[1]->paye_tier * ($paye[1]->rate / 100 ));
                }
            }

            else
            {
                $tax2 += ( $exceeding_income1 * ($paye[1]->rate / 100 ));

            }


        if ($exceeding_income2 > $paye[2]->paye_tier)
        {
            $exceeding_income3 += $exceeding_income2 - $paye[2]->paye_tier;
            if ($exceeding_income3 > 0)
            {
                $tax3 += ( $paye[2]->paye_tier * ($paye[2]->rate / 100 ));
            }
        }

        else
        {
            $tax3 += ( $exceeding_income2 * ($paye[2]->rate / 100 ));
        }



        if ($exceeding_income3 > $paye[3]->paye_tier)
        {
            $exceeding_income4 += $exceeding_income3 - $paye[3]->paye_tier;
            if ($exceeding_income4 > 0)
            {
                $tax4 += ( $paye[3]->paye_tier * ($paye[3]->rate / 100 ));
            }

        }

        else
        {
            $tax4 += ( $exceeding_income3 * ($paye[3]->rate / 100 ));
        }


        if ($exceeding_income4 > $paye[4]->paye_tier)
        {
            $exceeding_income5 += $exceeding_income4 - $paye[4]->paye_tier;
            if ($exceeding_income5 > 0)
            {
                $tax5 += ( $paye[4]->paye_tier * ($paye[4]->rate / 100 ));
            }
        }

        else
        {
            $tax5 += ( $exceeding_income4 * ($paye[4]->rate / 100 ));
        }


        if ($exceeding_income5 > $paye[5]->paye_tier)
        {
            $exceeding_income6 += $exceeding_income5 - $paye[5]->paye_tier;
            if ($exceeding_income6 > 0)
            {
                $tax6 += ( $exceeding_income5 * ($paye[5]->rate / 100 ));
            }
        }

        else
        {
            $tax6 += ( $exceeding_income5 * ($paye[5]->rate / 100 ));
        }

        return (($tax1 + $tax2 + $tax3 + $tax4 + $tax5 + $tax6));
        /*return ($taxableIncome);*/
    }


    public static function calc_income_tax($income) {
        $paye = PayeSetUp::get();
        $payrollSetup = PayrollSetup::first();
        if ($income > $payrollSetup->ssf_maximum_limit)
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $payrollSetup->ssf_maximum_limit);
        }
        else
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $income);
        }

        $exceeding_income1 = 0;
        $exceeding_income2 = 0;
        $exceeding_income3 = 0;
        $exceeding_income4 = 0;
        $exceeding_income5 = 0;
        $exceeding_income6 = 0;
        $tax1 = 0;
        $tax2 = 0;
        $tax3 = 0;
        $tax4 = 0;
        $tax5 = 0;
        $tax6 = 0;



        if ($taxableIncome > $paye[0]->paye_tier)
        {
            $exceeding_income = $taxableIncome - $paye[0]->paye_tier;
            $tax1 += ( $exceeding_income * ($paye[0]->rate / 100 ));
            $exceeding_income1 += $exceeding_income;
        }

        else
        {
            $tax1 += ( $taxableIncome * ($paye[0]->rate / 100 ));
        }

            if ($exceeding_income1 > $paye[1]->paye_tier)
            {
                $exceeding_income2 += $exceeding_income1 - $paye[1]->paye_tier;
                if ($exceeding_income2 > 0)
                {
                    $tax2 += ( $paye[1]->paye_tier * ($paye[1]->rate / 100 ));
                }
            }

            else
            {
                $tax2 += ( $exceeding_income1 * ($paye[1]->rate / 100 ));

            }


        if ($exceeding_income2 > $paye[2]->paye_tier)
        {
            $exceeding_income3 += $exceeding_income2 - $paye[2]->paye_tier;
            if ($exceeding_income3 > 0)
            {
                $tax3 += ( $paye[2]->paye_tier * ($paye[2]->rate / 100 ));
            }
        }

        else
        {
            $tax3 += ( $exceeding_income2 * ($paye[2]->rate / 100 ));
        }



        if ($exceeding_income3 > $paye[3]->paye_tier)
        {
            $exceeding_income4 += $exceeding_income3 - $paye[3]->paye_tier;
            if ($exceeding_income4 > 0)
            {
                $tax4 += ( $paye[3]->paye_tier * ($paye[3]->rate / 100 ));
            }

        }

        else
        {
            $tax4 += ( $exceeding_income3 * ($paye[3]->rate / 100 ));
        }


        if ($exceeding_income4 > $paye[4]->paye_tier)
        {
            $exceeding_income5 += $exceeding_income4 - $paye[4]->paye_tier;
            if ($exceeding_income5 > 0)
            {
                $tax5 += ( $paye[4]->paye_tier * ($paye[4]->rate / 100 ));
            }
        }

        else
        {
            $tax5 += ( $exceeding_income4 * ($paye[4]->rate / 100 ));
        }


        if ($exceeding_income5 > $paye[5]->paye_tier)
        {
            $exceeding_income6 += $exceeding_income5 - $paye[5]->paye_tier;
            if ($exceeding_income6 > 0)
            {
                $tax6 += ( $exceeding_income5 * ($paye[5]->rate / 100 ));
            }
        }

        else
        {
            $tax6 += ( $exceeding_income5 * ($paye[5]->rate / 100 ));
        }

        return (($tax1 + $tax2 + $tax3 + $tax4 + $tax5 + $tax6));
        /*return ($taxableIncome);*/
    }



    public static function _calc_income_tax($income) {

        // see this page for the bands in Ghana:
        // https://home.kpmg/xx/en/home/insights/2020/02/flash-alert-2020-036.html
        // look for "Residents: Rates & Bands"

        // first  3,828 GHS ==> Nil tax
        // next   1,200 GHS ==> 5% tax
        // next   1,440 GHS ==> 10% tax
        // next  36,000 GHS ==> 17.5% tax
        // next 197,532 GHS ==> 25% tax
        // over 240,000 GHS ==> 30% tax

        // $band_arr has the top amounts of each band in Ghana currency in descending order
        // We have 5 thresholds to higher tax rates and 5 rates for each of the thresholds
        $band_arr = [240000, 42468, 6468, 5028, 365];
        $rate_arr = [30, 25, 17.5, 10, 5];
        $paye = PayeSetUp::get();
        $income_tax_amount = 0;

        foreach ($band_arr as $key => $threshold) {
            if ( $income > $threshold ) {
                $exceeding_income = $income - $threshold;
                $income_tax_amount += ( $exceeding_income * $rate_arr[$key] );
                $income = $threshold;
            }
        }

        return $income_tax_amount;
    }




    public static function calculatePaye($income)
    {
        $paye = PayeSetUp::get();
        $payrollSetup = PayrollSetup::first();
        if ($income > $payrollSetup->ssf_maximum_limit)
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $payrollSetup->ssf_maximum_limit);
        }
        else
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $income);
        }

        $exceeding_income1 = 0;
        $exceeding_income2 = 0;
        $exceeding_income3 = 0;
        $exceeding_income4 = 0;
        $exceeding_income5 = 0;
        $exceeding_income6 = 0;
        $tax1 = 0;
        $tax2 = 0;
        $tax3 = 0;
        $tax4 = 0;
        $tax5 = 0;
        $tax6 = 0;



        if ($taxableIncome > $paye[0]->paye_tier)
        {
            $exceeding_income = $taxableIncome - $paye[0]->paye_tier;
            $tax1 += ( $exceeding_income * ($paye[0]->rate / 100 ));
            $exceeding_income1 += $exceeding_income;
        }

        else
        {
            $tax1 += ( $taxableIncome * ($paye[0]->rate / 100 ));
        }

        if ($exceeding_income1 > $paye[1]->paye_tier)
        {
            $exceeding_income2 += $exceeding_income1 - $paye[1]->paye_tier;
            if ($exceeding_income2 > 0)
            {
                $tax2 += ( $paye[1]->paye_tier * ($paye[1]->rate / 100 ));
            }
        }

        else
        {
            $tax2 += ( $exceeding_income1 * ($paye[1]->rate / 100 ));

        }


        if ($exceeding_income2 > $paye[2]->paye_tier)
        {
            $exceeding_income3 += $exceeding_income2 - $paye[2]->paye_tier;
            if ($exceeding_income3 > 0)
            {
                $tax3 += ( $paye[2]->paye_tier * ($paye[2]->rate / 100 ));
            }
        }

        else
        {
            $tax3 += ( $exceeding_income2 * ($paye[2]->rate / 100 ));
        }



        if ($exceeding_income3 > $paye[3]->paye_tier)
        {
            $exceeding_income4 += $exceeding_income3 - $paye[3]->paye_tier;
            if ($exceeding_income4 > 0)
            {
                $tax4 += ( $paye[3]->paye_tier * ($paye[3]->rate / 100 ));
            }

        }

        else
        {
            $tax4 += ( $exceeding_income3 * ($paye[3]->rate / 100 ));
        }


        if ($exceeding_income4 > $paye[4]->paye_tier)
        {
            $exceeding_income5 += $exceeding_income4 - $paye[4]->paye_tier;
            if ($exceeding_income5 > 0)
            {
                $tax5 += ( $paye[4]->paye_tier * ($paye[4]->rate / 100 ));
            }
        }

        else
        {
            $tax5 += ( $exceeding_income4 * ($paye[4]->rate / 100 ));
        }


        if ($exceeding_income5 > $paye[5]->paye_tier)
        {
            $exceeding_income6 += $exceeding_income5 - $paye[5]->paye_tier;
            if ($exceeding_income6 > 0)
            {
                $tax6 += ( $exceeding_income5 * ($paye[5]->rate / 100 ));
            }
        }

        else
        {
            $tax6 += ( $exceeding_income5 * ($paye[5]->rate / 100 ));
        }

        return (($tax1 + $tax2 + $tax3 + $tax4 + $tax5 + $tax6));
        /*return ($taxableIncome);*/
    }


    public static function calculateTaxableIncome($income)
    {
        $payrollSetup = PayrollSetup::first();
        if ($income > $payrollSetup->ssf_maximum_limit)
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $payrollSetup->ssf_maximum_limit);
        }
        else
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $income);
        }

        return ($taxableIncome);
    }


    public static function calculateNetPay($income)
    {
        $payrollSetup = PayrollSetup::first();
        if ($income > $payrollSetup->ssf_maximum_limit)
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $payrollSetup->ssf_maximum_limit);
        }
        else
        {
            $taxableIncome = $income - (($payrollSetup->new_ssf_employee_percentage / 100) * $income);
        }

        return ($taxableIncome);
    }



    public static function bscTimelineScore($id, $timeline, $year)
    {
        $kpi_weights = KpiResponsibility::where('responsible_employee_id', $id)->whereHas('employee_kpi_timelines.kpi', function ($q) use ($timeline, $year) {
            $q->where('employee_kpi_timelines.kpi_timeline_id', $timeline)->where('kpis.company_year_id', $year);
        })->sum('weight');

        @$averageRating = @KpiPerformanceReview::where('kpi_timeline_id', $timeline)->where('employee_id', $id)->whereHas('kpi', function ($q)  use ($timeline, $year)  {
            $q->where('kpis.company_year_id', $year);
        })->average('agreed_rating');

        $score =  ((@$averageRating / 5) * $kpi_weights);
        if ($kpi_weights)
        {
            $finalScore =  $score * 100 / $kpi_weights;
        }
        else
            $finalScore = 0;

        return number_format($finalScore, 2);
    }


    public static function bscTimelineMarks($id, $timeline)
    {
        $kpi_weights = KpiResponsibility::where('responsible_employee_id', $id)->whereHas('employee_kpi_timelines.kpi', function ($q) use ($timeline) {
            $q->where('employee_kpi_timelines.kpi_timeline_id', $timeline)->where('kpis.company_year_id', session('current_company_year'));
        })->sum('weight');

        @$averageRating = @KpiPerformanceReview::where('kpi_timeline_id', $timeline)->where('employee_id', $id)->whereHas('kpi', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->average('agreed_rating');

        $score =  ((@$averageRating / 5) * $kpi_weights);


        return number_format($score, 2);
    }


    public static function bscTimelineWeight($id, $timeline)
    {
        $kpi_weights = KpiResponsibility::where('responsible_employee_id', $id)->whereHas('employee_kpi_timelines.kpi', function ($q) use ($timeline) {
            $q->where('employee_kpi_timelines.kpi_timeline_id', $timeline)->where('kpis.company_year_id', session('current_company_year'));
        })->sum('weight');



        return number_format($kpi_weights, 2);
    }



    public static function determine_interest_rate($id)
    {
        $loan = Loan::find($id);
        if ($loan->override_interest == 1) {
            return $loan->override_interest_rate / 100;
        }
        $interest = '';
        if ($loan->repayment_frequency_type == 'days') {
            //return the interest per year
            if ($loan->interest_rate_type == 'month') {
                $interest = $loan->interest_rate / 30;
            }
            if ($loan->interest_rate_type == 'year') {
                $interest = $loan->interest_rate / 365;
            }
        }
        if ($loan->repayment_frequency_type == 'weeks') {
            //return the interest per semi annually
            if ($loan->interest_rate_type == 'month') {
                $interest = $loan->interest_rate / 4;
            }
            if ($loan->interest_rate_type == 'year') {
                $interest = $loan->interest_rate / 52;
            }
        }
        if ($loan->repayment_frequency_type == 'months') {
            //return the interest per quaterly

            if ($loan->interest_rate_type == 'month') {
                $interest = $loan->interest_rate;
            }
            if ($loan->interest_rate_type == 'year') {
                $interest = $loan->interest_rate / 12;
            }
        }
        if ($loan->repayment_frequency_type == 'years') {
            //return the interest per bi-monthly
            if ($loan->interest_rate_type == 'month') {
                $interest = $loan->interest_rate * 12;
            }
            if ($loan->interest_rate_type == 'year') {
                $interest = $loan->interest_rate;
            }
        }

        return $interest * $loan->repayment_frequency / 100;
    }

    //determine monthly payment using amortization
    public static function amortized_payment($id, $balance, $period = '')
    {
        $loan = Loan::find($id);
        if (empty($period)) {
            $period = $loan->loan_term / $loan->repayment_frequency;
        }
        $interest_rate = self::determine_interest_rate($id);
        //calculate here
        $amount = ($interest_rate * $balance * pow((1 + $interest_rate), $period)) / (pow((1 + $interest_rate),
                    $period) - 1);

        return $amount;
    }

    public static function loan_period($id)
    {
        $loan = Loan::find($id);
        $period = 0;
        if ($loan->repayment_cycle == 'annually') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration * 12);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 52);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration * 365);
            }
        }
        if ($loan->repayment_cycle == 'semi_annually') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration * 2);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration * 6);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 26);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration * 182.5);
            }
        }
        if ($loan->repayment_cycle == 'quarterly') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration * 12);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 52);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration * 365);
            }
        }
        if ($loan->repayment_cycle == 'bi_monthly') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration * 6);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration / 2);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 8);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration * 60);
            }
        }

        if ($loan->repayment_cycle == 'monthly') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration * 12);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 4.3);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration * 30.4);
            }
        }
        if ($loan->repayment_cycle == 'weekly') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration * 52);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration * 4);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 1);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration * 7);
            }
        }
        if ($loan->repayment_cycle == 'daily') {
            if ($loan->loan_duration_type == 'year') {
                $period = ceil($loan->loan_duration * 365);
            }
            if ($loan->loan_duration_type == 'month') {
                $period = ceil($loan->loan_duration * 30.42);
            }
            if ($loan->loan_duration_type == 'week') {
                $period = ceil($loan->loan_duration * 7.02);
            }
            if ($loan->loan_duration_type == 'day') {
                $period = ceil($loan->loan_duration);
            }
        }

        return $period;
    }

    public static function time_ago($eventTime)
    {
        $totaldelay = time() - strtotime($eventTime);
        if ($totaldelay <= 0) {
            return '';
        } else {
            if ($days = floor($totaldelay / 86400)) {
                $totaldelay = $totaldelay % 86400;

                return $days.' days ago';
            }
            if ($hours = floor($totaldelay / 3600)) {
                $totaldelay = $totaldelay % 3600;

                return $hours.' hours ago';
            }
            if ($minutes = floor($totaldelay / 60)) {
                $totaldelay = $totaldelay % 60;

                return $minutes.' minutes ago';
            }
            if ($seconds = floor($totaldelay / 1)) {
                $totaldelay = $totaldelay % 1;

                return $seconds.' seconds ago';
            }
        }
    }

    public static function determine_due_date($id, $date)
    {
        $schedule = LoanRepaymentSchedule::where('due_date', ' >=', $date)->where('loan_id', $id)->orderBy('due_date',
            'asc')->first();
        if (! empty($schedule)) {
            return $schedule->due_date;
        } else {
            $schedule = LoanRepaymentSchedule::where('loan_id',
                $id)->orderBy('due_date',
                'desc')->first();
            if ($date > $schedule->due_date) {
                return $schedule->due_date;
            } else {
                $schedule = LoanRepaymentSchedule::where('due_date', '>', $date)->where('loan_id',
                    $id)->orderBy('due_date',
                    'asc')->first();

                return $schedule->due_date;
            }
        }
    }

    public static function loan_total_interest($id, $date = '')
    {
        if (empty($date)) {
            return LoanSchedule::where('loan_id', $id)->sum('interest');
        } else {
            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('interest');
        }
    }

    public static function loan_total_interest_waived($id, $date = '')
    {
        if (empty($date)) {
            return LoanSchedule::where('loan_id', $id)->sum('interest_waived');
        } else {
            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('interest_waived');
        }
    }

    public static function loan_total_principal($id, $date = '')
    {
        if (empty($date)) {
            return LoanSchedule::where('loan_id', $id)->sum('principal');
        } else {
            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('principal');
        }
    }

    public static function loan_total_fees($id, $date = '')
    {
        if (empty($date)) {
            return LoanSchedule::where('loan_id', $id)->sum('fees');
        } else {
            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=',
                $date)->sum('fees');
        }
    }

    public static function loan_total_penalty($id, $date = '')
    {
        if (empty($date)) {
            return LoanSchedule::where('loan_id', $id)->sum('penalty');
        } else {
            return LoanSchedule::where('loan_id', $id)->where('due_date', '<=', $date)->sum('penalty');
        }
    }

    public static function loan_total_paid($id, $date = '')
    {
        if (empty($date)) {
            return LoanTransaction::where('loan_id', $id)->where('transaction_type',
                'repayment')->where('reversed', 0)->sum('credit');
        } else {
            return LoanTransaction::where('loan_id', $id)->where('transaction_type',
                'repayment')->where('reversed', 0)->where('due_date', '<=', $date)->sum('credit');
        }
    }

    public static function loan_total_balance($id, $date = '')
    {
        if (empty($date)) {
            $loan = Loan::find($id);
            $principal = 0;
            $principal_paid = 0;
            $principal_written_off = 0;
            $fees = 0;
            $fees_paid = 0;
            $penalty = 0;
            $penalty_paid = 0;
            $penalty_written_off = 0;
            $interest_waived = 0;
            $penalty_waived = 0;
            $fees_waived = 0;
            $fees_written_off = 0;
            $principal_waived = 0;
            $interest = 0;
            $interest_paid = 0;
            $interest_written_off = 0;
            foreach ($loan->repayment_schedules as $schedule) {
                $principal = $principal + $schedule->principal;
                $interest = $interest + $schedule->interest;
                $penalty = $penalty + $schedule->penalty;
                $fees = $fees + $schedule->fees;
                $principal_paid = $principal_paid + $schedule->principal_paid;
                $interest_paid = $interest_paid + $schedule->interest_paid;
                $penalty_paid = $penalty_paid + $schedule->penalty_paid;
                $fees_paid = $fees_paid + $schedule->fees_paid;
                $principal_waived = $principal_waived + $schedule->principal_waived;
                $interest_waived = $interest_waived + $schedule->interest_waived;
                $penalty_waived = $penalty_waived + $schedule->penalty_waived;
                $fees_waived = $fees_waived + $schedule->fees_waived;
                $principal_written_off = $principal_written_off + $schedule->principal_written_off;
                $interest_written_off = $interest_written_off + $schedule->interest_written_off;
                $penalty_written_off = $penalty_written_off + $schedule->penalty_written_off;
                $fees_written_off = $fees_written_off + $schedule->fees_written_off;
            }

            return ($principal - $principal_paid - $principal_waived - $principal_written_off) + ($interest - $interest_paid - $interest_waived - $interest_written_off) + ($fees - $fees_paid - $fees_waived - $fees_written_off) + ($penalty - $penalty_paid - $penalty_waived - $penalty_written_off);
        } else {
            return 0;
        }
    }

    public static function loan_arrears($id, $date)
    {
        $allocation = [];
        $amount_in_arrears = 0;
        $timely_repayments = 0;
        $total_repayments = 0;
        $days_in_arrears = 0;
        foreach (LoanRepaymentSchedule::where('loan_id', $id)->where('due_date', '<', $date)->orderBy('due_date', 'asc')->get() as $schedule) {
            $total_repayments = $total_repayments + 1;
            $amount_in_arrears = $amount_in_arrears + (($schedule->principal - $schedule->principal_waived - $schedule->principal_written_off - $schedule->principal_paid) + ($schedule->interest - $schedule->interest_waived - $schedule->interest_written_off - $schedule->interest_paid) + ($schedule->fees - $schedule->fees_waived - $schedule->fees_written_off - $schedule->fees_paid) + ($schedule->penalty - $schedule->penalty_waived - $schedule->penalty_written_off - $schedule->penalty_paid));
            if (! empty($schedule->from_date)) {
                if (strtotime($schedule->due_date) > strtotime($schedule->from_date)) {
                    $timely_repayments = $timely_repayments + 1;
                }
            }
        }
        if ($amount_in_arrears > 0) {
            $date1 = new \DateTime(LoanRepaymentSchedule::where('loan_id', $id)->where('due_date', '<', date('Y-m-d'))->orderBy('due_date', 'desc')->first()->due_date);
            $date2 = new \DateTime($date);
            $days_in_arrears = $date2->diff($date1)->format('%a');
        }
        if ($total_repayments > 0) {
            $percentage = $timely_repayments * 100 / $total_repayments;
        } else {
            $percentage = 0;
        }
        $allocation['amount'] = $amount_in_arrears;
        $allocation['days'] = $days_in_arrears;
        $allocation['percentage'] = $percentage;

        return $allocation;
    }

    public static function loan_total_due_amount($id, $date = '')
    {
        if (empty($date)) {
            return self::loan_total_penalty($id) + self::loan_total_fees($id) + self::loan_total_interest($id) + self::loan_total_principal($id) - self::loan_total_interest_waived($id);
        } else {
            return self::loan_total_penalty($id, $date) + self::loan_total_fees($id,
                    $date) + self::loan_total_interest($id, $date) + self::loan_total_principal($id,
                    $date) - self::loan_total_interest_waived($id, $date);
        }
    }

    public static function loan_total_due_period($id, $date)
    {
        return LoanSchedule::where('loan_id', $id)->where('due_date',
                $date)->sum('penalty') + LoanSchedule::where('loan_id', $id)->where('due_date',
                $date)->sum('fees') + LoanSchedule::where('loan_id', $id)->where('due_date',
                $date)->sum('principal') + LoanSchedule::where('loan_id', $id)->where('due_date',
                $date)->sum('interest') + LoanSchedule::where('loan_id', $id)->where('due_date',
                $date)->sum('interest_waived');
    }

    public static function loan_total_paid_period($id, $date)
    {
        return LoanRepayment::where('loan_id', $id)->where('due_date', $date)->sum('amount');
    }

    public static function loans_total_paid($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $paid = 0;
            foreach (Loan::whereIn('status', ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $paid = $paid + LoanTransaction::where('loan_id',
                        $key->id)->where('transaction_type',
                        'repayment')->where('reversed', 0)->sum('credit');
            }

            return $paid;
        } else {
            $paid = 0;
            foreach (Loan::whereIn('status', ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $paid = $paid + LoanTransaction::where('loan_id',
                        $key->id)->where('transaction_type',
                        'repayment')->where('reversed', 0)->sum('credit');
            }

            return $paid;
        }
    }

    public static function diff_in_months(\DateTime $date1, \DateTime $date2)
    {
        $diff = $date1->diff($date2);

        $months = $diff->y * 12 + $diff->m + $diff->d / 30;

        return (int) round($months);
    }

    public static function addMonths($date, $months)
    {
        $orig_day = $date->format('d');
        $date->modify('+'.$months.' months');
        while ($date->format('d') < $orig_day && $date->format('d') < 5) {
            $date->modify('-1 day');
        }
    }

    public static function single_payroll_total_pay($id)
    {
        return PayrollMeta::where('payroll_id', $id)->where('position', 'bottom_left')->sum('value');
    }

    public static function single_payroll_total_deductions($id)
    {
        return PayrollMeta::where('payroll_id', $id)->where('position', 'bottom_right')->sum('value');
    }

    public static function single_payroll_pay($id)
    {
        return self::single_payroll_total_pay($id) - self::single_payroll_total_deductions($id);
    }

    public static function total_expenses($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            return Expense::where('branch_id', session('branch_id'))->sum('amount');
        } else {
            return Expense::where('branch_id', session('branch_id'))->whereBetween('date',
                [$start_date, $end_date])->sum('amount');
        }
    }

    public static function total_payroll($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $payroll = 0;
            foreach (Payroll::where('branch_id', session('branch_id'))->get() as $key) {
                $payroll = $payroll + self::single_payroll_total_pay($key->id);
            }

            return $payroll;
        } else {
            $payroll = 0;
            foreach (Payroll::where('branch_id', session('branch_id'))->whereBetween('date',
                [$start_date, $end_date])->get() as $key) {
                $payroll = $payroll + self::single_payroll_total_pay($key->id);
            }

            return $payroll;
        }
    }

    public static function loans_total_principal($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $principal = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $principal = $principal + LoanSchedule::where('loan_id', $key->id)->sum('principal');
            }

            return $principal;
        } else {
            $principal = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $principal = $principal + $key->principal;
            }

            return $principal;
        }
    }

    public static function total_other_income($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            return OtherIncome::where('branch_id', session('branch_id'))->sum('amount');
        } else {
            return OtherIncome::where('branch_id', session('branch_id'))->whereBetween('date',
                [$start_date, $end_date])->sum('amount');
        }
    }

    public static function total_savings_interest($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            return SavingTransaction::where('branch_id', session('branch_id'))->where('type',
                'interest')->where('reversed', 0)->sum('debit');
        } else {
            return SavingTransaction::where('branch_id', session('branch_id'))->where('type',
                'interest')->where('reversed', 0)->whereBetween('date',
                [$start_date, $end_date])->sum('debit');
        }
    }

    public static function total_savings_deposits($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            return SavingTransaction::where('branch_id', session('branch_id'))->where('type', 'deposit')->where('reversed', 0)->sum('credit');
        } else {
            return SavingTransaction::where('branch_id', session('branch_id'))->where('type',
                'deposit')->where('reversed', 0)->whereBetween('date',
                [$start_date, $end_date])->sum('credit');
        }
    }

    public static function total_savings_transactions($id, $start_date = '', $end_date = '')
    {
        $interest = 0;
        $deposits = 0;
        $withdrawals = 0;
        $fees = 0;
        $guarantee = 0;
        $allocation = [];
        if (empty($start_date)) {
            foreach (SavingTransaction::where('savings_id', $id)->where('reversed', 0)->get() as $key) {
                if ($key->type == 'interest') {
                    $interest = $interest + $key->credit;
                }
                if ($key->type == 'deposit') {
                    $deposits = $deposits + $key->credit;
                }
                if ($key->type == 'interest') {
                    $withdrawals = $withdrawals + $key->debit;
                }
                if ($key->type == 'bank_fees') {
                    $fees = $fees + $key->credit;
                }
                if ($key->type == 'guarantee') {
                    $guarantee = $guarantee + $key->credit;
                }
            }
        } else {
            foreach (SavingTransaction::where('savings_id', $id)->where('reversed', 0)->whereBetween('date',
                [$start_date, $end_date])->get() as $key) {
                if ($key->type == 'interest') {
                    $interest = $interest + $key->credit;
                }
                if ($key->type == 'deposit') {
                    $deposits = $deposits + $key->credit;
                }
                if ($key->type == 'interest') {
                    $withdrawals = $withdrawals + $key->debit;
                }
                if ($key->type == 'bank_fees') {
                    $fees = $fees + $key->credit;
                }
                if ($key->type == 'guarantee') {
                    $guarantee = $guarantee + $key->credit;
                }
            }
        }
        $allocation['interest'] = $interest;
        $allocation['deposits'] = $deposits;
        $allocation['withdrawals'] = $withdrawals;
        $allocation['fees'] = $fees;
        $allocation['guarantee'] = $guarantee;

        return $allocation;
    }

    public static function total_savings_withdrawals($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            return SavingTransaction::where('branch_id', session('branch_id'))->where('type',
                'withdrawal')->where('reversed', 0)->sum('credit');
        } else {
            return SavingTransaction::where('branch_id', session('branch_id'))->where('type',
                'withdrawal')->where('reversed', 0)->whereBetween('date',
                [$start_date, $end_date])->sum('credit');
        }
    }

    public static function total_capital($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            return Capital::where('branch_id', session('branch_id'))->where('type',
                    'deposit')->sum('amount') - Capital::where('branch_id', session('branch_id'))->where('type',
                    'withdrawal')->sum('amount');
        } else {
            return Capital::where('branch_id', session('branch_id'))->where('type',
                    'deposit')->sum('amount') - Capital::where('branch_id', session('branch_id'))->where('type',
                    'withdrawal')->sum('amount');
        }
    }

    public static function loans_total_paid_item($item, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $amount = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $amount = $amount + self::loan_terms_paid_item($key->id, $item);
            }

            return $amount;
        } else {
            $amount = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $amount = $amount + self::loan_terms_paid_item($key->id, $item);
            }

            return $amount;
        }
    }

    public static function loans_product_total_paid_item($id, $item, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $amount = 0;
            foreach (Loan::where('loan_product_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $amount = $amount + self::loan_terms_paid_item($key->id, $item);
            }

            return $amount;
        } else {
            $amount = 0;
            foreach (Loan::where('loan_product_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $amount = $amount + self::loan_terms_paid_item($key->id, $item);
            }

            return $amount;
        }
    }

    public static function loans_borrower_total_paid_item($id, $item, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $amount = 0;
            foreach (Loan::where('borrower_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $amount = $amount + self::loan_terms_paid_item($key->id, $item);
            }

            return $amount;
        } else {
            $amount = 0;
            foreach (Loan::where('borrower_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $amount = $amount + self::loan_terms_paid_item($key->id, $item);
            }

            return $amount;
        }
    }

    public static function loans_total_due_item($item, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $amount = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                if ($item == 'principal') {
                    $amount = $amount + self::loan_total_principal($key->id);
                }
                if ($item == 'interest') {
                    $amount = $amount + self::loan_total_interest($key->id);
                }
                if ($item == 'fees') {
                    $amount = $amount + self::loan_total_fees($key->id);
                }
                if ($item == 'penalty') {
                    $amount = $amount + self::loan_total_penalty($key->id);
                }
            }

            return $amount;
        } else {
            $amount = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                if ($item == 'principal') {
                    $amount = $amount + self::loan_total_principal($key->id);
                }
                if ($item == 'interest') {
                    $amount = $amount + self::loan_total_interest($key->id);
                }
                if ($item == 'fees') {
                    $amount = $amount + self::loan_total_fees($key->id);
                }
                if ($item == 'penalty') {
                    $amount = $amount + self::loan_total_penalty($key->id);
                }
            }

            return $amount;
        }
    }

    public static function loans_product_total_due_items($id, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $interest = 0;
            $penalty = 0;
            $fees = 0;
            $principal = 0;
            foreach (Loan::where('loans.loan_product_id', $id)->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_schedules', 'loans.id', '=',
                'loan_schedules.loan_id')->where('loan_schedules.deleted_at', null)->get() as $key) {
                $interest = $interest + $key->interest;
                $penalty = $penalty + $key->penalty;
                $fees = $fees + $key->fees;
                $principal = $principal + $key->principal;
            }

            return ['interest' => $interest, 'principal' => $principal, 'penalty' => $penalty, 'fees' => $fees];
        } else {
            $interest = 0;
            $penalty = 0;
            $fees = 0;
            $principal = 0;
            foreach (Loan::where('loans.loan_product_id', $id)->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_schedules', 'loans.id', '=',
                'loan_schedules.loan_id')->whereBetween('loan_schedules.due_date',
                [$start_date, $end_date])->where('loan_schedules.deleted_at', null)->get() as $key) {
                $interest = $interest + $key->interest;
                $penalty = $penalty + $key->penalty;
                $fees = $fees + $key->fees;
                $principal = $principal + $key->principal;
            }

            return ['interest' => $interest, 'principal' => $principal, 'penalty' => $penalty, 'fees' => $fees];
        }
    }

    public static function loans_product_total_paid_items($id, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $interest = 0;
            $penalty = 0;
            $fees = 0;
            $principal = 0;
            foreach (Loan::where('loans.loan_product_id', $id)->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_repayments', 'loans.id', '=',
                'loan_repayments.loan_id')->where('loan_repayments.deleted_at', null)->get() as $key) {
                $interest = $interest + $key->interest;
                $penalty = $penalty + $key->penalty;
                $fees = $fees + $key->fees;
                $principal = $principal + $key->principal;
            }

            return ['interest' => $interest, 'principal' => $principal, 'penalty' => $penalty, 'fees' => $fees];
        } else {
            $interest = 0;
            $penalty = 0;
            $fees = 0;
            $principal = 0;
            foreach (Loan::where('loans.loan_product_id', $id)->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_repayments', 'loans.id', '=',
                'loan_repayments.loan_id')->whereBetween('loan_repayments.collection_date',
                [$start_date, $end_date])->where('loan_repayments.deleted_at', null)->get() as $key) {
                $interest = $interest + $key->interest;
                $penalty = $penalty + $key->penalty;
                $fees = $fees + $key->fees;
                $principal = $principal + $key->principal;
            }

            return ['interest' => $interest, 'principal' => $principal, 'penalty' => $penalty, 'fees' => $fees];
        }
    }

    public static function loans_borrower_total_due_item($id, $item, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $amount = 0;
            foreach (Loan::where('borrower_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                if ($item == 'principal') {
                    $amount = $amount + self::loan_total_principal($key->id);
                }
                if ($item == 'interest') {
                    $amount = $amount + self::loan_total_interest($key->id);
                }
                if ($item == 'fees') {
                    $amount = $amount + self::loan_total_fees($key->id);
                }
                if ($item == 'penalty') {
                    $amount = $amount + self::loan_total_penalty($key->id);
                }
            }

            return $amount;
        } else {
            $amount = 0;
            foreach (Loan::where('borrower_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                if ($item == 'principal') {
                    $amount = $amount + self::loan_total_principal($key->id);
                }
                if ($item == 'interest') {
                    $amount = $amount + self::loan_total_interest($key->id);
                }
                if ($item == 'fees') {
                    $amount = $amount + self::loan_total_fees($key->id);
                }
                if ($item == 'penalty') {
                    $amount = $amount + self::loan_total_penalty($key->id);
                }
            }

            return $amount;
        }
    }

    public static function loans_total_default($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $principal = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->where('status', 'written_off')->get() as $key) {
                $principal = $principal + ($key->principal - self::loan_total_paid($key->id));
            }

            return $principal;
        } else {
            $principal = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->where('status',
                'written_off')->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $principal = $principal + ($key->principal - self::loan_total_paid($key->id));
            }

            return $principal;
        }
    }

    public static function loans_total_due($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $due = $due + self::loan_total_due_amount($key->id);
            }

            return $due;
        } else {
            $due = 0;
            foreach (Loan::where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $due = $due + self::loan_total_due_amount($key->id);
            }

            return $due;
        }
    }

    public static function loans_count($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            $due = $due + Loan::where('branch_id', session('branch_id'))->whereIn('status',
                    ['disbursed', 'closed', 'written_off'])->count();

            return $due;
        } else {
            $due = 0;
            $due = $due + Loan::where('branch_id', session('branch_id'))->whereIn('status',
                    ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                    [$start_date, $end_date])->count();

            return $due;
        }
    }

    public static function loans_product_count($id, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            $due = $due + Loan::where('loan_product_id', $id)->where('branch_id',
                    session('branch_id'))->whereIn('status',
                    ['disbursed', 'closed', 'written_off'])->count();

            return $due;
        } else {
            $due = 0;
            $due = $due + Loan::where('loan_product_id', $id)->where('branch_id',
                    session('branch_id'))->whereIn('status',
                    ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                    [$start_date, $end_date])->count();

            return $due;
        }
    }

    public static function client_loans_count($id, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            $client_ids = [];
            foreach (Sentinel::findUserById($id)->client_users as $key) {
                array_push($client_ids, $key->client_id);
            }
            $group_ids = [];
            foreach (Sentinel::findUserById($id)->group_users as $key) {
                array_push($group_ids, $key->group_id);
            }
            $due = Loan::where(function ($query) use ($client_ids, $group_ids) {
                $query->whereIn('client_id', $client_ids)
                    ->orWhereIn('group_id', $group_ids);
            })->count();

            return $due;
        } else {
            $due = 0;
            $client_ids = [];
            foreach (Sentinel::findUserById($id)->client_users as $key) {
                array_push($client_ids, $key->client_id);
            }
            $group_ids = [];
            foreach (Sentinel::findUserById($id)->group_users as $key) {
                array_push($group_ids, $key->group_id);
            }
            $due = Loan::where(function ($query) use ($client_ids, $group_ids) {
                $query->whereIn('client_id', $client_ids)
                    ->orWhereIn('group_id', $group_ids);
            })->whereBetween('created_at',
                [$start_date, $end_date])->count();

            return $due;
        }
    }

    public static function payments_product_count($id, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            foreach (Loan::where('loan_product_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $due = $due + LoanTransaction::where('loan_id',
                        $key->id)->where('transaction_type',
                        'repayment')->where('reversed', 0)->count();
            }

            return $due;
        } else {
            $due = 0;
            foreach (Loan::where('loan_product_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $due = $due + LoanTransaction::where('loan_id',
                        $key->id)->where('transaction_type',
                        'repayment')->where('reversed', 0)->count();
            }

            return $due;
        }
    }

    public static function payments_borrower_count($id, $start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            foreach (Loan::where('borrower_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->get() as $key) {
                $due = $due + LoanTransaction::where('loan_id',
                        $key->id)->where('transaction_type',
                        'repayment')->where('reversed', 0)->count();
            }

            return $due;
        } else {
            $due = 0;
            foreach (Loan::where('borrower_id', $id)->where('branch_id', session('branch_id'))->whereIn('status',
                ['disbursed', 'closed', 'written_off'])->whereBetween('release_date',
                [$start_date, $end_date])->get() as $key) {
                $due = $due + LoanTransaction::where('loan_id',
                        $key->id)->where('transaction_type',
                        'repayment')->where('reversed', 0)->count();
            }

            return $due;
        }
    }

    public static function borrower_loans_total_due($id)
    {
        $due = 0;
        foreach (Loan::whereIn('status',
            ['disbursed', 'closed', 'written_off'])->where('borrower_id', $id)->get() as $key) {
            $due = $due + self::loan_total_due_amount($key->id);
        }

        return $due;
    }

    public static function borrower_loans_total_paid($id)
    {
        $paid = 0;
        foreach (Loan::whereIn('status',
            ['disbursed', 'closed', 'written_off'])->where('borrower_id', $id)->get() as $key) {
            $paid = $paid + LoanTransaction::where('loan_id',
                    $key->id)->where('transaction_type',
                    'repayment')->where('reversed', 0)->sum('credit');
        }

        return $paid;
    }

    public static function audit_trail($action = '', $module = '', $notes = '')
    {
        $audit_trail = new AuditTrail();
        $audit_trail->user_id = Sentinel::getUser()->id;
        $audit_trail->company_id = session('company_id');
        $audit_trail->office_id = Sentinel::getUser()->office_id;
        $audit_trail->name = Sentinel::getUser()->first_name.' '.Sentinel::getUser()->last_name;
        $audit_trail->action = $action;
        $audit_trail->module = $module;
        $audit_trail->notes = $notes;
        $audit_trail->save();
    }

    public static function savings_account_balance($id, $end_date = '')
    {
        if (empty($end_date)) {
            $balance = SavingsTransaction::selectRaw(DB::raw('(COALESCE(SUM(credit),0)-COALESCE(SUM(debit),0)) as balance'))->where('savings_id', $id)->where('reversed', 0)->first();
        } else {
            $balance = SavingsTransaction::selectRaw(DB::raw('(COALESCE(SUM(credit),0)-COALESCE(SUM(debit),0)) as balance'))->where('savings_id', $id)->where('reversed', 0)->where('date', '<', $end_date)->first();
        }
        if (! empty($balance)) {
            return $balance->balance;
        } else {
            return 0;
        }
    }

    public static function client_savings_account_balance($id)
    {
        $balance = 0;
        foreach (Savings::where('client_id', $id)->get() as $key) {
            $balance = $balance + self::savings_account_balance($key->id);
        }

        return $balance;
    }

    public static function total_client_savings_account_balance($id)
    {
        $balance = 0;
        $client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }
        foreach (Savings::where(function ($query) use ($client_ids, $group_ids) {
            $query->whereIn('client_id', $client_ids)
                ->orWhereIn('group_id', $group_ids);
        })->get() as $key) {
            $balance = $balance + self::savings_account_balance($key->id);
        }

        return $balance;
    }

    public static function asset_valuation($id, $start_date = '')
    {
        if (empty($start_date)) {
            $value = 0;
            if (! empty(AssetValuation::where('asset_id', $id)->orderBy('date', 'desc')->first())) {
                $value = AssetValuation::where('asset_id', $id)->orderBy('date', 'desc')->first()->amount;
            }

            return $value;
        } else {
            $value = 0;
            if (! empty(AssetValuation::where('asset_id', $id)->where('date', '<=', $start_date)->orderBy('date',
                'desc')->first())
            ) {
                $value = AssetValuation::where('asset_id', $id)->where('date', '<=', $start_date)->orderBy('date',
                    'desc')->first()->amount;
            }

            return $value;
        }
    }

    public static function asset_type_valuation($id, $start_date = '')
    {
        if (empty($start_date)) {
            $value = 0;
            foreach (Asset::where('asset_type_id', $id)->get() as $key) {
                if (! empty(AssetValuation::where('asset_id', $key->id)->orderBy('date', 'desc')->first())) {
                    $value = AssetValuation::where('asset_id', $key->id)->orderBy('date', 'desc')->first()->amount;
                }
            }

            return $value;
        } else {
            $value = 0;
            foreach (Asset::where('asset_type_id', $id)->get() as $key) {
                if (! empty(AssetValuation::where('asset_id', $key->id)->where('date', '<=',
                    $start_date)->orderBy('date',
                    'desc')->first())
                ) {
                    $value = AssetValuation::where('asset_id', $key->id)->where('date', '<=',
                        $start_date)->orderBy('date',
                        'desc')->first()->amount;
                }
            }

            return $value;
        }
    }

    public static function bank_account_balance($id)
    {
        return Capital::where('bank_account_id', $id)->where('branch_id', session('branch_id'))->where('type',
                'deposit')->sum('amount') - Capital::where('bank_account_id', $id)->where('branch_id',
                session('branch_id'))->where('type',
                'withdrawal')->sum('amount');
    }

    public static function send_sms($user)
    {
        $user->notify(new SendSMS($user));

        /*$school = Company::find(session('current_company'));
        Nexmo::message()->send([
            'to'=> $number,
            'from'=> $school->sms_name?? 'Application',
            'text'=> $text
        ]);*/
    }

    public static function login_notification($user)
    {
        $user->notify(new LoginNotification($user));
    }

    public static function leave_approve($user)
    {
        $user->notify(new LeaveApproveNotification($user));
    }

    public static function leave_application($user)
    {
        $user->notify(new LeaveApplicationNotification($user));
    }

    public static function send_email($user)
    {
        $when = now()->addMinutes(1);
        Mail::to($user->email)
            ->later($when, new SendEmail($user));
    }

    public static function send_post_created_email($post)
    {
        Mail::to($post->employee->user->email)->send(new PostCreatedEmail($post));
    }

    public static function send_kpi_created_email($kpi)
    {
        Mail::to($kpi->employee->user->email)->send(new KpiCreatedEmail($kpi));
        Mail::to($kpi->supervisor->user->email)->send(new KpiCreatedApproverEmail($kpi));
    }

    public static function send_kpi_activity_created_email($activity)
    {
        Mail::to($activity->kpi->employee->user->email)->send(new KpiActivityCreatedEmail($activity));
        Mail::to($activity->kpi->supervisor->user->email)->send(new KpiActivityCreatedApproverEmail($activity));
    }

    public static function send_kpi_activity_updated_email($activity)
    {
        Mail::to($activity->kpi->employee->user->email)->send(new KpiCreatedEmail($activity));
        Mail::to($activity->supervisor->user->email)->send(new KpiCreatedApproverEmail($activity));
    }

    public static function send_kpi_approved_email($kpi)
    {
        Mail::to($kpi->employee->user->email)->send(new KpiApprovedEmail($kpi));
        Mail::to($kpi->supervisor->user->email)->send(new KpiApprovedApproverEmail($kpi));
    }

    public static function send_kpi_activity_approved_email($activity)
    {
        Mail::to($activity->kpi->employee->user->email)->send(new KpiActivityApprovedEmail($activity));
        /*Mail::to($activity->supervisor->user->email)->send(new KpiCreatedApproverEmail($activity));*/
    }

    public static function send_post_comment_created_email($comment)
    {
        Mail::to($comment->employee->user->email)->send(new PostCommentCreatedEmail($comment));
        Mail::to($comment->post->employee->user->email)->send(new PostCommentCreatedOwnerEmail($comment));
    }

    public static function sendKpiCascade_email($user, $kpiResponsibility)
    {
        if (self::validateEmail($user->email)) {
            $when = now()->addMinutes(1);
            Mail::to(str_replace(' ', '', $user->email))
                ->later($when, new SendKpiCascadeEmail($user, $kpiResponsibility));
        }
    }

    public static function sendNewEmployee_email($user, $employee)
    {
        $when = now()->addMinutes(1);
        Mail::to($user->email)
            ->later($when, new SendNewEmployeeEmail($user, $employee));
    }

    public static function buildTree($data, $parent = 0)
    {
        $tree = [];
        foreach ($data as $d) {
            if ($d['parent_id'] == $parent) {
                $children = self::buildTree($data, $d['id']);
                // set a trivial key
                if (! empty($children)) {
                    $d['_children'] = $children;
                }
                $tree[] = $d;
            }
        }

        return $tree;
    }

    public static function printTree($tree, $r = 0, $p = null)
    {
        foreach ($tree as $i => $t) {
            $dash = ($t['parent_id'] == 0) ? '' : str_repeat('-', $r).' ';
            printf("\t<option value='%d'>%s%s</option>\n", $t['id'], $dash, $t['name']);
            if (isset($t['_children'])) {
                self::printTree($t['_children'], $r + 1, $t['parent_id']);
            }
        }
    }

    public static function printTableTree($tree, $r = 0, $p = null)
    {
        $html = '';
        foreach ($tree as $i => $t) {
            $dash = ($t['parent_id'] == 0) ? '' : str_repeat('-', $r).' ';
            $html .= '<tr>';
            $html .= '<td>'.$dash.$t['name'].'</td>';
            $html .= '<td>'.$t['slug'].'</td>';
            if ($t['active'] == 1) {
                $html .= "<td><span class='label label-success'>".trans_choice('general.yes', 1).'</span></td>';
            } else {
                $html .= "<td><span class='label label-danger'>".trans_choice('general.no', 1).'</span></td>';
            }
            $html .= '<td>'.$t['notes'].'</td>';
            $html .= '<td>'.count($t['products']).'</td>';
            $html .= "<td> <div class='btn-group'>";
            $html .= '<button type="button" class="btn btn-info btn-xs dropdown-toggle"
                                        data-toggle="dropdown" aria-expanded="false">'.trans('general.choose');
            $html .= '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>';
            $html .= '<ul class="dropdown-menu" role="menu">';
            if (Sentinel::hasAccess('stock.update')) {
                $html .= '<li><a href="'.url('product/category/'.$t['id'].'/edit').'"><i
                                                        class="fa fa-edit"></i>'.trans('general.edit').'</a>
                                        </li>';
            }
            if (Sentinel::hasAccess('stock.delete')) {
                $html .= '<li><a href="'.url('product/category/'.$t['id'].'/delete').'" class="delete"><i
                                                        class="fa fa-trash"></i>'.trans('general.delete').'</a>
                                        </li>';
            }
            $html .= '</ul></div></td>';
            $html .= '</tr>';
            if (isset($t['_children'])) {
                $html .= self::printTableTree($t['_children'], $r + 1, $t['parent_id']);
            }
        }

        return $html;
    }

    public static function getUniqueSlug($model, $value)
    {
        $slug = Str::slug($value);
        $slugCount = count($model->whereRaw("slug REGEXP '^{$slug}(-[0-9]+)?$' and id != '{$model->id}'")->get());

        return ($slugCount > 0) ? "{$slug}-{$slugCount}" : $slug;
    }

    public static function limit_text($text, $limit)
    {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $text = substr($text, 0, $pos[$limit]).'...';
        }

        return $text;
    }

    public static function check_in_total_amount($id)
    {
        return ProductCheckinItem::where('product_check_in_id', $id)->sum('total_cost');
    }

    public static function check_in_total_paid_amount($id)
    {
        return ProductCheckinItem::where('product_check_in_id', $id)->sum('total_cost');
    }

    public static function check_out_total_amount($id)
    {
        return ProductCheckoutItem::where('product_check_out_id', $id)->sum('total_cost');
    }

    public static function check_ins_total_amount($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            foreach (ProductCheckin::where('branch_id', session('branch_id'))->get() as $key) {
                $due = $due + self::check_in_total_amount($key->id);
            }

            return $due;
        } else {
            $due = 0;
            foreach (ProductCheckin::where('branch_id', session('branch_id'))->whereBetween('date',
                [$start_date, $end_date])->get() as $key) {
                $due = $due + self::check_in_total_amount($key->id);
            }

            return $due;
        }
    }

    public static function check_outs_total_amount($start_date = '', $end_date = '')
    {
        if (empty($start_date)) {
            $due = 0;
            foreach (ProductCheckout::where('branch_id', session('branch_id'))->get() as $key) {
                $due = $due + self::check_in_total_amount($key->id);
            }

            return $due;
        } else {
            $due = 0;
            foreach (ProductCheckout::where('branch_id', session('branch_id'))->whereBetween('date',
                [$start_date, $end_date])->get() as $key) {
                if ($key->type == 'cash') {
                    $due = $due + self::check_in_total_amount($key->id);
                } else {
                    if (! empty($key->loan)) {
                        $due = $due + self::loan_total_due_amount($key->loan_id);
                    }
                }
            }

            return $due;
        }
    }

    public static function stock_total_cost_amount()
    {
        $due = 0;
        foreach (Product::get() as $key) {
            $due = $due + ($key->qty * $key->cost_price);
        }

        return $due;
    }

    public static function stock_total_selling_amount()
    {
        $due = 0;
        foreach (Product::get() as $key) {
            $due = $due + ($key->qty * $key->selling_price);
        }

        return $due;
    }

    public static function loan_schedule_dtermine_paid_by($id)
    {
        $schedule = LoanSchedule::find($id);
        $amount = $schedule->principal + $schedule->interest + $schedule->fees + $schedule->penalty;
        $payments = 0;
        foreach (LoanRepayment::where('loan_id', $schedule->loan_id)->orderBy('collection_date',
            'asc')->get() as $payment) {
            $payments = $payments + $payment->amount;
        }
    }

    public static function loan_items($id, $start_date = '', $end_date = '')
    {
        $allocation = [];
        $loan = Loan::find($id);
        $principal = 0;
        $principal_paid = 0;
        $principal_written_off = 0;
        $fees = 0;
        $fees_paid = 0;
        $penalty = 0;
        $penalty_paid = 0;
        $penalty_written_off = 0;
        $interest_waived = 0;
        $penalty_waived = 0;
        $fees_waived = 0;
        $fees_written_off = 0;
        $principal_waived = 0;
        $interest = 0;
        $interest_paid = 0;
        $interest_written_off = 0;
        foreach ($loan->repayment_schedules as $schedule) {
            $principal = $principal + $schedule->principal;
            $interest = $interest + $schedule->interest;
            $penalty = $penalty + $schedule->penalty;
            $fees = $fees + $schedule->fees;
            $principal_paid = $principal_paid + $schedule->principal_paid;
            $interest_paid = $interest_paid + $schedule->interest_paid;
            $penalty_paid = $penalty_paid + $schedule->penalty_paid;
            $fees_paid = $fees_paid + $schedule->fees_paid;
            $principal_waived = $principal_waived + $schedule->principal_waived;
            $interest_waived = $interest_waived + $schedule->interest_waived;
            $penalty_waived = $penalty_waived + $schedule->penalty_waived;
            $fees_waived = $fees_waived + $schedule->fees_waived;

            $principal_written_off = $principal_written_off + $schedule->principal_written_off;
            $interest_written_off = $interest_written_off + $schedule->interest_written_off;
            $penalty_written_off = $penalty_written_off + $schedule->penalty_written_off;
            $fees_written_off = $fees_written_off + $schedule->fees_written_off;
        }
        $allocation['principal'] = $principal;
        $allocation['interest'] = $interest;
        $allocation['fees'] = $fees;
        $allocation['penalty'] = $penalty;

        $allocation['interest_waived'] = $interest_waived;
        $allocation['principal_waived'] = $principal_waived;
        $allocation['penalty_waived'] = $penalty_waived;
        $allocation['fees_waived'] = $fees_waived;

        $allocation['interest_paid'] = $interest_paid;
        $allocation['principal_paid'] = $principal_paid;
        $allocation['penalty_paid'] = $penalty_paid;
        $allocation['fees_paid'] = $fees_paid;

        $allocation['interest_written_off'] = $interest_written_off;
        $allocation['principal_written_off'] = $principal_written_off;
        $allocation['penalty_written_off'] = $penalty_written_off;
        $allocation['fees_written_off'] = $fees_written_off;

        return $allocation;
    }

    public static function loan_due_items($id, $start_date = '', $end_date = '')
    {
        $allocation = [];
        $principal = 0;
        $fees = 0;
        $penalty = 0;
        $interest = 0;
        if (empty($start_date)) {
            $schedules = LoanSchedule::where('loan_id', $id)->get();
        } else {
            $schedules = LoanSchedule::where('loan_id', $id)->whereBetween('due_date',
                [$start_date, $end_date])->get();
        }
        foreach ($schedules as $schedule) {
            $interest = $interest + $schedule->interest;
            $penalty = $penalty + $schedule->penalty;
            $fees = $fees + $schedule->fees;
            $principal = $principal + $schedule->principal;
        }
        $allocation['principal'] = $principal;
        $allocation['interest'] = $interest;
        $allocation['fees'] = $fees;
        $allocation['penalty'] = $penalty;

        return $allocation;
    }

    public static function schedule_due_amount($id)
    {
        $schedule = LoanSchedule::find($id);
        $amount = 0;
        $payments = LoanRepayment::where('loan_id', $schedule->loan_id)->sum('amount');
        foreach (LoanSchedule::where('due_date', '<=', $schedule->due_date)->where('loan_id',
            $schedule->loan_id)->get() as $key) {
            if ($key->id != $id) {
                $payments = $payments - ($key->interest + $key->penalty + $key->fees + $key->principal);
            }
        }
        if ($payments > 0 && $payments > ($schedule->interest + $schedule->penalty + $schedule->fees + $schedule->principal)) {
            $amount = 0;
        } elseif ($payments > 0 && $payments < ($schedule->interest + $schedule->penalty + $schedule->fees + $schedule->principal)) {
            $amount = $schedule->interest + $schedule->penalty + $schedule->fees + $schedule->principal - $payments;
        } else {
            $amount = $schedule->interest + $schedule->penalty + $schedule->fees + $schedule->principal;
        }

        return $amount;
    }

    public static function loans_paid_items($start_date = '', $end_date = '')
    {
        $allocation = [];
        $principal = 0;
        $fees = 0;
        $penalty = 0;
        $interest = 0;
        $interest_waived = 0;
        $over_payments = 0;
        if (empty($start_date)) {
            $principal = $principal + JournalEntry::where('transaction_type',
                    'repayment')->where('transaction_sub_type', 'repayment_principal')->where('reversed',
                    0)->where('branch_id', session('branch_id'))->sum('credit');
            $interest = $interest + JournalEntry::where('transaction_type', 'repayment')->where('transaction_sub_type',
                    'repayment_interest')->where('reversed', 0)->where('branch_id',
                    session('branch_id'))->sum('credit');
            $fees = $fees + JournalEntry::where('transaction_type', 'repayment')->where('transaction_sub_type',
                    'repayment_fees')->where('reversed', 0)->where('branch_id', session('branch_id'))->sum('credit');
            $penalty = $penalty + JournalEntry::where('transaction_type', 'repayment')->where('transaction_sub_type',
                    'repayment_penalty')->where('reversed', 0)->where('branch_id', session('branch_id'))->sum('credit');
            $over_payments = $over_payments + JournalEntry::where('transaction_type',
                    'repayment')->where('transaction_sub_type',
                    'overpayment')->where('reversed', 0)->where('branch_id', session('branch_id'))->sum('credit');
        } else {
            $principal = $principal + JournalEntry::where('transaction_type',
                    'repayment')->where('transaction_sub_type', 'repayment_principal')->where('reversed',
                    0)->whereBetween('date',
                    [$start_date, $end_date])->where('branch_id', session('branch_id'))->sum('credit');
            $interest = $interest + JournalEntry::where('transaction_type', 'repayment')->where('transaction_sub_type',
                    'repayment_interest')->where('reversed', 0)->whereBetween('date',
                    [$start_date, $end_date])->where('branch_id', session('branch_id'))->sum('credit');
            $fees = $fees + JournalEntry::where('transaction_type', 'repayment')->where('transaction_sub_type',
                    'repayment_fees')->where('reversed', 0)->whereBetween('date',
                    [$start_date, $end_date])->where('branch_id', session('branch_id'))->sum('credit');
            $penalty = $penalty + JournalEntry::where('transaction_type', 'repayment')->where('transaction_sub_type',
                    'repayment_penalty')->where('reversed', 0)->whereBetween('date',
                    [$start_date, $end_date])->where('branch_id', session('branch_id'))->sum('credit');
            $over_payments = $over_payments + JournalEntry::where('transaction_type',
                    'repayment')->where('transaction_sub_type',
                    'overpayment')->where('reversed', 0)->whereBetween('date',
                    [$start_date, $end_date])->where('branch_id', session('branch_id'))->sum('credit');
        }

        $allocation['principal'] = $principal;
        $allocation['interest'] = $interest;
        $allocation['fees'] = $fees;
        $allocation['penalty'] = $penalty;
        $allocation['over_payments'] = $over_payments;

        return $allocation;
    }

    public static function loans_due_items($start_date = '', $end_date = '')
    {
        $allocation = [];
        $principal = 0;
        $fees = 0;
        $penalty = 0;
        $interest = 0;

        if (empty($start_date)) {
            foreach (Loan::select('loan_schedules.principal', 'loan_schedules.interest', 'loan_schedules.penalty',
                'loan_schedules.fees')->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_schedules', 'loans.id', '=',
                'loan_schedules.loan_id')->where('loan_schedules.deleted_at', null)->get() as $key) {
                $interest = $interest + $key->interest;
                $penalty = $penalty + $key->penalty;
                $fees = $fees + $key->fees;
                $principal = $principal + $key->principal;
            }
        } else {
            foreach (Loan::select('loan_schedules.principal', 'loan_schedules.interest', 'loan_schedules.penalty',
                'loan_schedules.fees')->where('loans.branch_id',
                session('branch_id'))->whereIn('loans.status',
                ['disbursed', 'closed', 'written_off'])->join('loan_schedules', 'loans.id', '=',
                'loan_schedules.loan_id')->whereBetween('loan_schedules.due_date',
                [$start_date, $end_date])->where('loan_schedules.deleted_at', null)->get() as $key) {
                $interest = $interest + $key->interest;
                $penalty = $penalty + $key->penalty;
                $fees = $fees + $key->fees;
                $principal = $principal + $key->principal;
            }
        }

        $allocation['principal'] = $principal;
        $allocation['interest'] = $interest;
        $allocation['fees'] = $fees;
        $allocation['penalty'] = $penalty;

        return $allocation;
    }

    public static function determine_posting_days($id)
    {
        $savings_product = SavingsProduct::find($id);
        $interest_posting_days = [];
        if ($savings_product->interest_posting_period == 'monthly') {
            array_push($interest_posting_days, Carbon::parse('last day of january')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of february')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of march')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of april')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of may')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of june')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of july')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of august')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of september')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of august')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of november')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of december')->format('Y-m-d'));
        }
        if ($savings_product->interest_posting_period == 'quarterly') {
            array_push($interest_posting_days, Carbon::parse('last day of march')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of june')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of september')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of december')->format('Y-m-d'));
        }
        if ($savings_product->interest_posting_period == 'biannual') {
            array_push($interest_posting_days, Carbon::parse('last day of june')->format('Y-m-d'));
            array_push($interest_posting_days, Carbon::parse('last day of december')->format('Y-m-d'));
        }
        if ($savings_product->interest_posting_period == 'annually') {
            array_push($interest_posting_days, Carbon::parse('last day of december')->format('Y-m-d'));
        }

        return $interest_posting_days;
    }

    public static function determine_next_interest_calculation_date($id)
    {
        $savings_product = SavingsProduct::find($id);
        $next_calculation_date = '';
        if ($savings_product->interest_compounding_period == 'daily') {
            $next_calculation_date = Carbon::tomorrow()->format('Y-m-d');
        }
        if ($savings_product->interest_compounding_period == 'monthly') {
            $next_calculation_date = Carbon::parse('last day of this month')->format('Y-m-d');
        }
        if ($savings_product->interest_compounding_period == 'quarterly') {
            if (Carbon::parse('last day of march')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of march')->format('Y-m-d');
            } elseif (Carbon::parse('last day of june')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of june')->format('Y-m-d');
            } elseif (Carbon::parse('last day of september')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of september')->format('Y-m-d');
            } elseif (Carbon::parse('last day of december')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of december')->format('Y-m-d');
            }
        }
        if ($savings_product->interest_compounding_period == 'biannual') {
            if (Carbon::parse('last day of june')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of june')->format('Y-m-d');
            } elseif (Carbon::parse('last day of december')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of december')->format('Y-m-d');
            }
        }
        if ($savings_product->interest_compounding_period == 'annually') {
            if (Carbon::parse('last day of december')->gt(Carbon::today())) {
                $next_calculation_date = Carbon::parse('last day of december')->format('Y-m-d');
            }
        }

        return $next_calculation_date;
    }

    public static function determine_next_interest_posting_date($id)
    {
        $savings_product = SavingsProduct::find($id);
        $next_posting_date = '';
        if ($savings_product->interest_posting_period == 'monthly') {
            $next_posting_date = Carbon::parse('last day of this month')->format('Y-m-d');
        }
        if ($savings_product->interest_posting_period == 'quarterly') {
            if (Carbon::parse('last day of march')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of march')->format('Y-m-d');
            } elseif (Carbon::parse('last day of june')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of june')->format('Y-m-d');
            } elseif (Carbon::parse('last day of september')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of september')->format('Y-m-d');
            } elseif (Carbon::parse('last day of december')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of december')->format('Y-m-d');
            }
        }
        if ($savings_product->interest_posting_period == 'biannual') {
            if (Carbon::parse('last day of june')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of june')->format('Y-m-d');
            } elseif (Carbon::parse('last day of december')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of december')->format('Y-m-d');
            }
        }
        if ($savings_product->interest_posting_period == 'annually') {
            if (Carbon::parse('last day of december')->gt(Carbon::today())) {
                $next_posting_date = Carbon::parse('last day of december')->format('Y-m-d');
            }
        }

        return $next_posting_date;
    }

    public static function gl_account_balance($id)
    {
        $transactions = GlJournalEntry::selectRaw(DB::raw('COALESCE(SUM(credit),0) credit, COALESCE(SUM(debit),0) debit'))->where('gl_account_id', $id)->where('reversed', 0)->groupBy('gl_account_id')->first();

        return $transactions;
    }

    public static function gl_account_unreconciled_balance($id)
    {
        $transactions = GlJournalEntry::selectRaw(DB::raw('COALESCE(SUM(credit),0) credit, COALESCE(SUM(debit),0) debit'))->where('gl_account_id', $id)->where('reversed', 0)->where('reconciled', 0)->groupBy('gl_account_id')->first();

        return $transactions;
    }

    public static function total_disbursed_loans_amount($start_date = '', $end_date = '')
    {
        $amount = Loan::selectRaw(DB::raw('COALESCE(SUM(principal),0) principal'))->whereIn('status', ['disbursed', 'closed', 'written_off'])->when($start_date, function ($query) use ($start_date, $end_date) {
            $query->whereBetween('disbursement_date', [$start_date, $end_date]);
        })->first();

        if (! empty($amount)) {
            return $amount->principal;
        } else {
            return 0;
        }
    }

    public static function client_total_disbursed_loans_amount($id, $start_date = '', $end_date = '')
    {
        $client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }
        $amount = Loan::selectRaw(DB::raw('COALESCE(SUM(principal),0) principal'))->whereIn('status', ['disbursed', 'closed', 'written_off'])->when($start_date, function ($query) use ($start_date, $end_date) {
            $query->whereBetween('disbursement_date', [$start_date, $end_date]);
        })->where(function ($query) use ($client_ids, $group_ids) {
            $query->whereIn('client_id', $client_ids)
                ->orWhereIn('group_id', $group_ids);
        })->first();

        if (! empty($amount)) {
            return $amount->principal;
        } else {
            return 0;
        }
    }

    public static function total_loans_repayments_amount($start_date = '', $end_date = '')
    {
        $amount = LoanTransaction::selectRaw(DB::raw('COALESCE(SUM(credit),0) amount'))->where('reversed', 0)->where('transaction_type', 'repayment')->when($start_date, function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
        })->first();

        if (! empty($amount)) {
            return $amount->amount;
        } else {
            return 0;
        }
    }

    public static function client_total_loans_repayments_amount($id, $start_date = '', $end_date = '')
    {
        $client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }
        $amount = LoanTransaction::selectRaw(DB::raw('COALESCE(SUM(credit),0) amount'))->where('reversed', 0)->where('transaction_type', 'repayment')->when($start_date, function ($query) use ($start_date, $end_date) {
            $query->whereBetween('date', [$start_date, $end_date]);
        })->where(function ($query) use ($client_ids, $group_ids) {
            $query->whereIn('client_id', $client_ids)
                ->orWhereIn('group_id', $group_ids);
        })->first();

        if (! empty($amount)) {
            return $amount->amount;
        } else {
            return 0;
        }
    }

    public static function total_loans_outstanding_amount($start_date = '', $end_date = '')
    {
        $amount = DB::table('loan_repayment_schedules as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.interest),0)-COALESCE(SUM(lr.interest_waived),0)-COALESCE(SUM(lr.interest_written_off),0)-COALESCE(SUM(lr.interest_paid),0)+COALESCE(SUM(lr.principal),0)-COALESCE(SUM(lr.principal_waived),0)-COALESCE(SUM(lr.principal_written_off),0)-COALESCE(SUM(lr.principal_paid),0)+COALESCE(SUM(lr.fees),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)-COALESCE(SUM(lr.fees_paid),0)+COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.penalty_waived),0)-COALESCE(SUM(lr.penalty_written_off),0)-COALESCE(SUM(lr.penalty_paid),0)) balance'))->join('loans as l', 'l.id', '=', 'lr.loan_id')->where('l.status', 'disbursed')->first();

        if (! empty($amount)) {
            return $amount->balance;
        } else {
            return 0;
        }
    }

    public static function client_total_loans_outstanding_amount($id, $start_date = '', $end_date = '')
    {
        $client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }

        $amount = DB::table('loan_repayment_schedules as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.interest),0)-COALESCE(SUM(lr.interest_waived),0)-COALESCE(SUM(lr.interest_written_off),0)-COALESCE(SUM(lr.interest_paid),0)+COALESCE(SUM(lr.principal),0)-COALESCE(SUM(lr.principal_waived),0)-COALESCE(SUM(lr.principal_written_off),0)-COALESCE(SUM(lr.principal_paid),0)+COALESCE(SUM(lr.fees),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)-COALESCE(SUM(lr.fees_paid),0)+COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.penalty_waived),0)-COALESCE(SUM(lr.penalty_written_off),0)-COALESCE(SUM(lr.penalty_paid),0)) balance'))->join('loans as l', 'l.id', '=', 'lr.loan_id')->where('l.status', 'disbursed')->where(function ($query) use ($client_ids, $group_ids) {
            $query->whereIn('l.client_id', $client_ids)
                ->orWhereIn('l.group_id', $group_ids);
        })->first();

        if (! empty($amount)) {
            return $amount->balance;
        } else {
            return 0;
        }
    }

    public static function total_loans_overdue_amount($start_date = '')
    {
        $amount = DB::table('loan_repayment_schedules as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.interest),0)-COALESCE(SUM(lr.interest_waived),0)-COALESCE(SUM(lr.interest_written_off),0)-COALESCE(SUM(lr.interest_paid),0)+COALESCE(SUM(lr.principal),0)-COALESCE(SUM(lr.principal_waived),0)-COALESCE(SUM(lr.principal_written_off),0)-COALESCE(SUM(lr.principal_paid),0)+COALESCE(SUM(lr.fees),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)-COALESCE(SUM(lr.fees_paid),0)+COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.penalty_waived),0)-COALESCE(SUM(lr.penalty_written_off),0)-COALESCE(SUM(lr.penalty_paid),0)) balance'))->join('loans as l', 'l.id', '=', 'lr.loan_id')->where('l.status', 'disbursed')->when($start_date, function ($query) use ($start_date) {
            $query->where('lr.due_date', '<', $start_date);
        }, function ($query) {
            $query->where('lr.due_date', '<', date('Y-m-d'));
        })->first();

        if (! empty($amount)) {
            return $amount->balance;
        } else {
            return 0;
        }
    }

    public static function client_total_loans_overdue_amount($id, $start_date = '')
    {
        $client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }
        $amount = DB::table('loan_repayment_schedules as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.interest),0)-COALESCE(SUM(lr.interest_waived),0)-COALESCE(SUM(lr.interest_written_off),0)-COALESCE(SUM(lr.interest_paid),0)+COALESCE(SUM(lr.principal),0)-COALESCE(SUM(lr.principal_waived),0)-COALESCE(SUM(lr.principal_written_off),0)-COALESCE(SUM(lr.principal_paid),0)+COALESCE(SUM(lr.fees),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)-COALESCE(SUM(lr.fees_paid),0)+COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.penalty_waived),0)-COALESCE(SUM(lr.penalty_written_off),0)-COALESCE(SUM(lr.penalty_paid),0)) balance'))->join('loans as l', 'l.id', '=', 'lr.loan_id')->where('l.status', 'disbursed')->when($start_date, function ($query) use ($start_date) {
            $query->where('lr.due_date', '<', $start_date);
        }, function ($query) {
            $query->where('lr.due_date', '<', date('Y-m-d'));
        })->where(function ($query) use ($client_ids, $group_ids) {
            $query->whereIn('l.client_id', $client_ids)
                ->orWhereIn('l.group_id', $group_ids);
        })->first();

        if (! empty($amount)) {
            return $amount->balance;
        } else {
            return 0;
        }
    }

    public static function client_numbers_graph()
    {
        $clients = [];
        $registered_prospects = Client::where('status', 'active')->whereNOTIn('id', function ($query) {
            $query->select('client_id')->from('loans');
        })->count();
        $total_clients = Client::where('status', 'active')->count();

        $funded_clients = Client::where('status', 'active')->whereIn('id', function ($query) {
            $query->select('l.client_id')->from('loans as l')->join('loan_repayment_schedules as lr', 'l.id', '=', 'lr.loan_id')->where('status', 'disbursed')->groupBy('l.id')->havingRaw('(COALESCE(SUM(lr.principal),0)+COALESCE(SUM(lr.interest),0)+COALESCE(SUM(lr.fees),0)+COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.principal_waived),0)-COALESCE(SUM(lr.principal_written_off),0)-COALESCE(SUM(lr.principal_paid),0)-COALESCE(SUM(lr.interest_waived),0)-COALESCE(SUM(lr.interest_written_off),0)-COALESCE(SUM(lr.interest_paid),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)-COALESCE(SUM(lr.fees_paid),0)-COALESCE(SUM(lr.penalty_written_off),0)-COALESCE(SUM(lr.penalty_paid),0)) >0')->distinct();
        })->count();
        array_push($clients, [
            'title' => 'Registered Prospects',
            'value' => $registered_prospects,
        ]);
        array_push($clients, [
            'title' => 'Funded Clients',
            'value' => $funded_clients,
        ]);
        array_push($clients, [
            'title' => 'Total Clients',
            'value' => $total_clients,
        ]);

        return json_encode($clients, JSON_UNESCAPED_SLASHES);
    }

    public static function savings_balance_graph()
    {
        $savings = [];
        $transactions = DB::table('savings_transactions as st')->selectRaw(DB::raw('o.name name,(COALESCE(SUM(st.credit),0)- COALESCE(SUM(st.debit),0)) balance'))->join('offices as o', 'o.id', '=', 'st.office_id')->where('st.reversed', 0)->groupBy('st.office_id')->get();
        foreach ($transactions as $transaction) {
            array_push($savings, [
                'title' => $transaction->name,
                'value' => $transaction->balance,
            ]);
        }

        return json_encode($savings, JSON_UNESCAPED_SLASHES);
    }

    public static function loans_status_graph()
    {
        $loans = [];

        array_push($loans, [
            'title' => 'Pending',
            'value' => Loan::where('status', 'pending')->count(),
        ]);

        array_push($loans, [
            'title' => 'Approved',
            'value' => Loan::where('status', 'approved')->count(),
        ]);
        array_push($loans, [
            'title' => 'Disbursed',
            'value' => Loan::where('status', 'disbursed')->count(),
        ]);
        array_push($loans, [
            'title' => 'Declined',
            'value' => Loan::where('status', 'declined')->count(),
        ]);
        array_push($loans, [
            'title' => 'Written Off',
            'value' => Loan::where('status', 'written_off')->count(),
        ]);
        array_push($loans, [
            'title' => 'Withdrawn',
            'value' => Loan::where('status', 'withdrawn')->count(),
        ]);

        return json_encode($loans, JSON_UNESCAPED_SLASHES);
    }

    public static function grants_status_graph()
    {
        $grants = [];

        array_push($grants, [
            'title' => 'Pending',
            'value' => Grant::where('status', 'pending')->count(),
        ]);

        array_push($grants, [
            'title' => 'Approved',
            'value' => Grant::where('status', 'approved')->count(),
        ]);
        array_push($grants, [
            'title' => 'Disbursed',
            'value' => Grant::where('status', 'disbursed')->count(),
        ]);
        array_push($grants, [
            'title' => 'Declined',
            'value' => Grant::where('status', 'declined')->count(),
        ]);
        /*array_push($grants, [
            "title" => "Withdrawn",
            "value" => Grant::where('status', 'withdrawn')->count(),
        ]);*/

        return json_encode($grants, JSON_UNESCAPED_SLASHES);
    }

    public static function collection_overview_graph()
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);
            $actual = 0;
            $expected = 0;
            $actual = $actual + LoanTransaction::where('transaction_type',
                    'repayment')->where('reversed', 0)->where('year',
                    $d[0])->where('month',
                    $d[1])->sum('credit');
            $repayment_schedules = DB::table('loan_repayment_schedules as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.interest),0)-COALESCE(SUM(lr.interest_waived),0)-COALESCE(SUM(lr.interest_written_off),0)+COALESCE(SUM(lr.principal),0)-COALESCE(SUM(lr.principal_waived),0)-COALESCE(SUM(lr.principal_written_off),0)+COALESCE(SUM(lr.fees),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)+COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.penalty_waived),0)-COALESCE(SUM(lr.penalty_written_off),0)) balance'))->where('year',
                $d[0])->where('month',
                $d[1])->first();
            if (! empty($repayment_schedules)) {
                $expected = $repayment_schedules->balance;
            }
            array_push($collection_overview, [
                'month' => date_format(date_create($date),
                    'M'.' '.$d[0]),
                'actual' => $actual,
                'expected' => $expected,
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }

    public static function fumigation_overview_graph()
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);
            $actual = 0;
            $expected = 0;

            if (Sentinel::inRole('admin')) {
                $actual = $actual + Fumigation::whereStatus('approved')->whereCompanyId(session('company_id'))->whereHas('school')->whereYear('opening_date',
                        $d[0])->whereMonth('opening_date',
                        $d[1])->count();
            } else {
                $actual = $actual + Fumigation::whereStatus('approved')->whereHas('school')->whereYear('opening_date',
                        $d[0])->whereMonth('opening_date',
                        $d[1])->count();
            }

            if (Sentinel::inRole('admin')) {
                $repayment_schedules = DB::table('targets as lr')->whereRegionId(session('region_id'))->selectRaw(DB::raw('(COALESCE(SUM(lr.total_due),0)) balance'))->whereYear('due_date',
                    $d[0])->whereMonth('due_date',
                    $d[1])->first();
            } else {
                $repayment_schedules = DB::table('targets as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.total_due),0)) balance'))->whereYear('due_date',
                    $d[0])->whereMonth('due_date',
                    $d[1])->first();
            }

            if (! empty($repayment_schedules)) {
                $expected = $repayment_schedules->balance;
            }
            array_push($collection_overview, [
                'month' => date_format(date_create($date),
                    'M'.' '.$d[0]),
                'actual' => $actual,
                'expected' => $expected,
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }

    public static function fees_penalty_earned_paid()
    {
        $schedules = [];
        $fees = 0;
        $fees_paid = 0;
        $penalty = 0;
        $penalty_paid = 0;
        $transactions = DB::table('loan_repayment_schedules as lr')->selectRaw(DB::raw('(COALESCE(SUM(lr.fees),0)-COALESCE(SUM(lr.fees_waived),0)-COALESCE(SUM(lr.fees_written_off),0)) fees,(COALESCE(SUM(lr.fees_paid),0)) fees_paid ,(COALESCE(SUM(lr.penalty),0)-COALESCE(SUM(lr.penalty_waived),0)-COALESCE(SUM(lr.penalty_written_off),0)) penalty,(COALESCE(SUM(lr.penalty_paid),0)) penalty_paid'))->first();
        if (! empty($transactions)) {
            $fees = $transactions->fees;
            $fees_paid = $transactions->fees_paid;
            $penalty = $transactions->penalty;
            $penalty_paid = $transactions->penalty_paid;
        }
        $schedules['fees'] = $fees;
        $schedules['fees_paid'] = $fees_paid;
        $schedules['penalty'] = $penalty;
        $schedules['penalty_paid'] = $penalty_paid;

        return $schedules;
    }

    public static function determine_savings_interest_earned($id)
    {
        $savings = Savings::find($id);
        $savings_product = $savings->savings_product;
        $total_balance = 0;
        if (Carbon::parse($savings->next_interest_calculation_date)->eq(Carbon::today())) {
            $previous_balance = self::savings_account_balance($id, Carbon::today()->format('Y-m-d')) + $savings->interest_earned;
            $total_balance = $total_balance + $previous_balance;
            if ($savings_product->interest_compounding_period == 'daily') {
                $today_balance = SavingsTransaction::selectRaw(DB::raw('(COALESCE(SUM(credit),0)-COALESCE(SUM(debit),0)) as balance'))->where('savings_id', $id)->where('reversed', 0)->where('date', Carbon::today()->format('Y-m-d'))->first();
                if (! empty($today_balance)) {
                    $total_balance = $today_balance->balance + $total_balance;
                }
                if ($total_balance >= $savings_product->minimum_balance) {
                    //calculate interest
                    $interest = $total_balance * ($savings_product->interest_rate / (100 * 365));
                    $savings->interest_earned = $savings->interest_earned + $interest;
                    $savings->next_interest_calculation_date = Carbon::tomorrow()->format('Y-m-d');
                    $savings->last_interest_calculation_date = Carbon::today()->format('Y-m-d');
                    $savings->save();
                } else {
                    $savings->next_interest_calculation_date = Carbon::tomorrow()->format('Y-m-d');
                    $savings->last_interest_calculation_date = Carbon::today()->format('Y-m-d');
                    $savings->save();
                }
            }
            if ($savings_product->interest_compounding_period == 'monthly') {
                if (Carbon::parse($savings->start_interest_calculation_date)->gt(Carbon::parse('first day of '.date('M')))) {
                    $start_date = $savings->start_interest_calculation_date;
                } else {
                    $start_date = Carbon::parse('first day of '.date('M'))->format('Y-m-d');
                }
                $next_interest_calculation_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(1)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_compounding_period == 'quarterly') {
                if (Carbon::parse($savings->start_interest_calculation_date)->gt(Carbon::parse('first day of '.Carbon::today()->subMonths(2)->format('M')))) {
                    $start_date = $savings->start_interest_calculation_date;
                } else {
                    $start_date = Carbon::parse('first day of '.Carbon::today()->subMonths(2))->format('Y-m-d');
                }
                $next_interest_calculation_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(3)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_compounding_period == 'biannual') {
                if (Carbon::parse($savings->start_interest_calculation_date)->gt(Carbon::parse('first day of '.Carbon::today()->subMonths(5)->format('M')))) {
                    $start_date = $savings->start_interest_calculation_date;
                } else {
                    $start_date = Carbon::parse('first day of '.Carbon::today()->subMonths(5))->format('Y-m-d');
                }
                $next_interest_calculation_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(6)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_compounding_period == 'annually') {
                if (Carbon::parse($savings->start_interest_calculation_date)->gt(Carbon::parse('first day of '.Carbon::today()->subMonths(11)->format('M')))) {
                    $start_date = $savings->start_interest_calculation_date;
                } else {
                    $start_date = Carbon::parse('first day of '.Carbon::today()->subMonths(11))->format('Y-m-d');
                }
                $next_interest_calculation_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(12)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_compounding_period == 'monthly') {
                if ($savings_product->interest_calculation_type == 'daily') {
                    $transactions = SavingsTransaction::selectRaw(DB::raw('(COALESCE(SUM(credit),0)-COALESCE(SUM(debit),0)) as balance, date'))->where('savings_id', $savings->id)->where('reversed', 0)->whereBetween('date', [$start_date, Carbon::today()->format('Y-m-d')])->groupBy('date')->get();
                    $balance = self::savings_account_balance($savings->id, $start_date);
                    $interest = 0;
                    $total_days = 0;
                    if (empty($transactions)) {
                        if ($balance >= $savings_product->minimum_balance) {
                            $days = Carbon::parse($start_date)->diffInDays(Carbon::today()->format('Y-m-d')) + 1;
                            $interest = $interest + ($balance * $days * $savings_product->interest_rate / (100 * 365));
                        }
                    } else {
                        foreach ($transactions as $transaction) {
                            if (Carbon::parse($start_date)->eq(Carbon::parse($transaction->date))) {
                                $days = 1;
                            } else {
                                $days = Carbon::parse($start_date)->diffInDays($transaction->date);
                            }
                            if ($balance >= $savings_product->minimum_balance) {
                                $interest = $interest + ($balance * $days * $savings_product->interest_rate / (100 * 365));
                            }
                            $start_date = Carbon::parse($start_date)->addDays($days)->format('Y-m-d');
                            $balance = $balance + $transaction->balance;
                            $total_days = $total_days + $days;
                        }
                        if (Carbon::parse($start_date)->notEqualTo(Carbon::today())) {
                            $days = Carbon::parse($start_date)->diffInDays(Carbon::today()) + 1;
                            if ($balance >= $savings_product->minimum_balance) {
                                $interest = $interest + ($balance * $days * $savings_product->interest_rate / (100 * 365));
                            }
                            $total_days = $total_days + $days;
                        } else {
                            if ($balance >= $savings_product->minimum_balance) {
                                $interest = $interest + ($balance * $savings_product->interest_rate / (100 * 365));
                            }
                            $total_days = $total_days + 1;
                        }
                    }
                    $savings->interest_earned = $savings->interest_earned + $interest;
                    $savings->next_interest_calculation_date = $next_interest_calculation_date;
                    $savings->last_interest_calculation_date = Carbon::today()->format('Y-m-d');
                    $savings->save();
                }
                if ($savings_product->interest_calculation_type == 'average') {
                    $transactions = SavingsTransaction::selectRaw(DB::raw('(COALESCE(SUM(credit),0)-COALESCE(SUM(debit),0)) as balance, date'))->where('savings_id', $savings->id)->where('reversed', 0)->whereBetween('date', [$start_date, Carbon::today()->format('Y-m-d')])->groupBy('date')->get();
                    $balance = self::savings_account_balance($savings->id, $start_date);
                    $interest = 0;
                    $total_days = 0;
                    if (empty($transactions)) {
                        if ($balance >= $savings_product->minimum_balance) {
                            $days = Carbon::parse($start_date)->diffInDays(Carbon::today()->format('Y-m-d')) + 1;
                            $interest = $interest + ($balance * $days * $savings_product->interest_rate / (100 * 365));
                        }
                    } else {
                        $average_balance = 0;
                        foreach ($transactions as $transaction) {
                            if (Carbon::parse($start_date)->eq(Carbon::parse($transaction->date))) {
                                $days = 1;
                            } else {
                                $days = Carbon::parse($start_date)->diffInDays($transaction->date);
                            }
                            $interest = $interest + ($balance * $days * $savings_product->interest_rate / (100 * 365));
                            $average_balance = $average_balance + ($balance * $days);
                            $start_date = Carbon::parse($start_date)->addDays($days)->format('Y-m-d');
                            $balance = $balance + $transaction->balance;
                            $total_days = $total_days + $days;
                        }
                        if (Carbon::parse($start_date)->notEqualTo(Carbon::today())) {
                            $days = Carbon::parse($start_date)->diffInDays(Carbon::today()) + 1;
                            $average_balance = $average_balance + ($balance * $days);
                            if ($balance >= $savings_product->minimum_balance) {
                                $interest = $interest + ($balance * $days * $savings_product->interest_rate / (100 * 365));
                            }
                            $total_days = $total_days + $days;
                        } else {
                            $average_balance = $average_balance + ($balance * 1);
                            if ($balance >= $savings_product->minimum_balance) {
                                $interest = $interest + ($balance * $savings_product->interest_rate / (100 * 365));
                            }
                            $total_days = $total_days + 1;
                        }
                        $average_balance = $average_balance / $total_days;
                        if ($average_balance > $savings_product->minimum_balance) {
                            $interest = $interest + ($average_balance * $total_days * $savings_product->interest_rate / (100 * 365));
                        }
                    }
                    $savings->interest_earned = $savings->interest_earned + $interest;
                    $savings->next_interest_calculation_date = $next_interest_calculation_date;
                    $savings->last_interest_calculation_date = Carbon::today()->format('Y-m-d');
                    $savings->save();
                }
            }
        }
    }

    public static function post_savings_interest_earned($id)
    {
        $savings = Savings::find($id);
        $savings_product = $savings->savings_product;
        if (Carbon::parse($savings->next_interest_posting_date)->eq(Carbon::today())) {
            if ($savings->interest_earned > 0) {
                $date = date('Y-m-d');
                $savings_transaction = new SavingsTransaction();
                //$savings_transaction->created_by_id = Sentinel::getUser()->id;
                $savings_transaction->office_id = $savings->office_id;
                $savings_transaction->savings_id = $savings->id;
                $savings_transaction->transaction_type = 'interest';
                $savings_transaction->reversible = 1;
                $savings_transaction->date = date('Y-m-d');
                $savings_transaction->time = date('H:i');
                $date = explode('-', date('Y-m-d'));
                $savings_transaction->year = $date[0];
                $savings_transaction->month = $date[1];
                $savings_transaction->credit = $savings->interest_earned;
                $savings_transaction->save();
                if (! empty($savings_product->gl_account_interest_on_savings)) {
                    $journal = new GlJournalEntry();
                    //$journal->created_by_id = Sentinel::getUser()->id;
                    $journal->gl_account_id = $savings_product->gl_account_interest_on_savings->id;
                    $journal->office_id = $savings->office_id;
                    $journal->date = date('Y-m-d');
                    $journal->year = $date[0];
                    $journal->month = $date[1];
                    $journal->transaction_type = 'savings';
                    $journal->name = 'Savings interest';
                    $journal->savings_transaction_id = $savings_transaction->id;
                    $journal->savings_id = $savings->id;
                    $journal->debit = $savings->interest_earned;
                    $journal->reference = $savings_transaction->id;
                    $journal->save();
                }
                if (! empty($savings_product->gl_account_savings_reference)) {
                    $journal = new GlJournalEntry();
                    //$journal->created_by_id = Sentinel::getUser()->id;
                    $journal->gl_account_id = $savings_product->gl_account_savings_reference->id;
                    $journal->office_id = $savings->office_id;
                    $journal->date = date('Y-m-d');
                    $journal->year = $date[0];
                    $journal->month = $date[1];
                    $journal->transaction_type = 'savings';
                    $journal->name = 'Savings interest';
                    $journal->savings_transaction_id = $savings_transaction->id;
                    $journal->savings_id = $savings->id;
                    $journal->credit = $savings->interest_earned;
                    $journal->reference = $savings_transaction->id;
                    $journal->save();
                }
            }
            if ($savings_product->interest_posting_period == 'monthly') {
                $savings->next_interest_posting_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(1)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_posting_period == 'quarterly') {
                $savings->next_interest_posting_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(3)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_posting_period == 'biannual') {
                $savings->next_interest_posting_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(6)->format('M'))->format('Y-m-d');
            }
            if ($savings_product->interest_posting_period == 'annually') {
                $savings->next_interest_posting_date = Carbon::parse('last day of '.Carbon::today()->addMonthsNoOverflow(12)->format('M'))->format('Y-m-d');
            }
            $savings->interest_earned = 0;
            $savings->last_interest_posting_date = Carbon::today()->format('Y-m-d');
            $savings->save();
        }
    }

    public static function total_grants_disbursed($start_date = '', $end_date = '')
    {
        $amount = 0;
        if (empty($start_date)) {
            $amount = Grant::where('status', 'disbursed')->sum('amount');
        } else {
            $amount = Grant::where('status', 'disbursed')->whereBetween('disbursement_date', [$start_date, $end_date])->sum('amount');
        }

        return $amount;
    }

    public static function total_number_of_schools_for_region($id, $start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereRegionId($id)->count();

        return $amount;
    }

    public static function total_number_of_schools_for_district($id, $start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereDistrictId($id)->count();

        return $amount;
    }

    public static function total_number_of_schools_fumigated_for_region($id, $start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereHas('fumigation')->whereRegionId($id)->count();

        return $amount;
    }

    public static function total_number_of_schools_fumigated_for_district($id, $start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereHas('fumigation')->whereDistrictId($id)->count();

        return $amount;
    }

    public static function total_loans_outstanding_for_region($id, $start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereDoesntHave('fumigation')->whereRegionId($id)->count();

        return $amount;
    }

    public static function total_loans_outstanding_for_district($id, $start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereDoesntHave('fumigation')->whereDistrictId($id)->count();

        return $amount;
    }

    public static function percentage_fumigated_for_region($id, $start_date = '', $end_date = '')
    {
        $percentage = Company::whereHas('fumigation')->whereRegionId($id)->count() * 100 / Company::whereRegionId($id)->count();

        return $percentage;
    }

    public static function getPercentage($portion, $total)
    {
        if ($total > 0) {
            $percentage = number_format(($portion * 100 / $total),2).'%';
        } else {
            $percentage = 0;
        }

        return @$percentage;
    }

    public static function percentage_fumigated_for_district($id, $start_date = '', $end_date = '')
    {
        @$percentage = Company::whereHas('fumigation')->whereDistrictId($id)->count() * 100 / Company::whereDistrictId($id)->count();

        return @$percentage;
    }

    public static function total_number_of_schools($start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::count();

        return $amount;
    }

    public static function total_number_of_schools_fumigated($start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereHas('fumigation')->count();

        return $amount;
    }

    public static function total_loans_outstanding($start_date = '', $end_date = '')
    {
        /*$client_ids = [];
        foreach (Sentinel::findUserById($id)->client_users as $key) {
            array_push($client_ids, $key->client_id);
        }
        $group_ids = [];
        foreach (Sentinel::findUserById($id)->group_users as $key) {
            array_push($group_ids, $key->group_id);
        }*/
        $amount = Company::whereDoesntHave('fumigation')->count();

        return $amount;
    }

    public static function percentage_fumigated($start_date = '', $end_date = '')
    {
        $percentage = Company::whereHas('fumigation')->count() * 100 / Company::count();

        return $percentage;
    }

    public static function school_type_graph()
    {
        $savings = [];
        $transactions = DB::table('schools as st')->selectRaw(DB::raw('o.name name,(COALESCE(COUNT(st.id),0)) balance'))->join('school_types as o', 'o.id', '=', 'st.school_type_id')->groupBy('st.school_type_id')->get();
        foreach ($transactions as $transaction) {
            array_push($savings, [
                'title' => $transaction->name,
                'value' => $transaction->balance,
            ]);
        }

        return json_encode($savings, JSON_UNESCAPED_SLASHES);
    }

    public static function all_school_map()
    {
        $t = 1;
        $savings = [];
        $transactions = Center::where('latitude', '!=', '')->get();
        foreach ($transactions as $transaction) {
            array_push($savings, [
                $transaction->title,
                $transaction->latitude,
                $transaction->longitude,
                $t++,
                $transaction->marker_icon,
            ]);
        }

        return json_encode($savings, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_armchar_bar1()
    {
        $t = 1;
        $data = [];
        $departments = Sector::whereHas('employees')->get();
        foreach ($departments as $key) {
            array_push($data, [
                'country' => $key->title,
                'visits' => $key->employees->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function department_armchar_bar1()
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $data = [];

        $departments = Department::withCount(['employees' => function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        }])->get();

        foreach ($departments as $key) {
            array_push($data, [
                'country' => Str::limit($key->title, 35),
                'visits' => $key->employees_count,
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/'.$key->id.'/show',
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function department_Attendance_armchar_bar1($month, $year)
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $data = [];
        $departments = Department::with(['attendance' => function ($query) use ($month, $year) {
            $query->whereRaw('MONTH(date) = ?', [$month])->whereRaw('YEAR(date) = ?', [$year]);
        }])->whereHas('employees', function ($q) use ($month, $year) {
            $q->where('status', 1)->where('employees.status', 1);
        })->get();

        foreach ($departments as $key) {
            array_push($data, [
                'country' => Str::limit($key->title, 35),
                'visits' => $key->attendance->count(),
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/'.$key->id.'/show',
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function department_daily_attendance_armchar_bar1($month, $year)
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $data = [];
        $departments = Department::with(['dailyAttendance' => function ($query) use ($month, $year) {
            $query->whereRaw('MONTH(date) = ?', [$month])->whereRaw('YEAR(date) = ?', [$year]);
        }])->whereHas('employees', function ($q) use ($month, $year) {
            $q->where('status', 1)->where('employees.status', 1);
        })->get();

        foreach ($departments as $key) {
            array_push($data, [
                'country' => Str::limit($key->title, 35),
                'visits' => $key->dailyAttendance->count(),
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/'.$key->id.'/show',
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function department_dailyActivity_armchar_bar1($date)
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $data = [];
        $departments = Department::with(['dailyActivities' => function ($query) use ($date) {
            $query->whereYear('daily_activities.created_at', $date)->whereMonth('daily_activities.created_at', $date)->whereDay('daily_activities.created_at', $date);
        }])->whereHas('employees', function ($q) {
            $q->where('status', 1)->where('employees.status', 1);
        })->get();

        foreach ($departments as $key) {
            array_push($data, [
                'country' => Str::limit($key->title, 35),
                'visits' => $key->dailyActivities->count(),
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/'.$key->id.'/show',
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function weekly_visitor_armchar_bar1($date)
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $data = [];

        for ($i = 1; $i < 7; $i++) {
            $day_name = Carbon::now()->subDays($i)->format('D'); // Trim day name to 3 chars
            if ($day_name != 'Sun' && $day_name != 'Sat') {
                $day[] = Carbon::now()->subDays($i);
            }
        }

        foreach ($day as $key) {
            array_push($data, [
                'country' => Str::limit($key->format('D').' ('.$key->format('m-d').')', 35),
                'visits' => VisitorLog::where('company_id', session('current_company'))->whereYear('created_at', $key)->whereMonth('created_at', $key)->whereDay('created_at', $key)->count(),
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/1/show',
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function weekly_staff_attendance_armchar_bar1($date)
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $data = [];

        for ($i = 1; $i < 8; $i++) {
            $day[] = Carbon::now()->subDays($i);
        }

        foreach ($day as $key) {
            array_push($data, [
                'country' => Str::limit($key->format('D').' ('.$key->format('m-d').')', 35),
                'visits' => Attendance::whereHas('employee', function ($q) use ($date) {
                    $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
                })->whereYear('created_at', $key)->whereMonth('created_at', $key)->whereDay('created_at', $key)->where('device', 'ENTRANCE-IN')->distinct('employee_id')->count(),
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/1/show',
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_company_employee_armchar_bar1($id)
    {
        $t = 1;
        $data = [];
        $schools = Company::whereHas('employees')->where('sector_id', $id)->get();
        foreach ($schools as $key) {
            array_push($data, [
                'country' => $key->title,
                'visits' => $key->employees->where('status', 1)->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_kpi_performance()
    {
        $t = 1;
        $data = [];
        $departments = Sector::whereHas('employees')->get();
        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'staff' => $key->employees->count(),
                'kpis' => $key->kpis->count(),
                'kpiActivities' => $key->kpi_activities->count(),
                'completedKpis' => $key->completed_kpi_activities->count(),
                'score' => $key->kpi_score,
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function department_kpi_performance()
    {
        $t = 1;
        $data = [];

        /*$departments = Department::withCount(['employees','kpis' => function ($query) {
            $query->where('employees.company_id', session('current_company'));
        }])->get();*/

        $departments = Department::whereHas('employees', function ($q) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->get();

        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'staff' => $key->employees_count,
                'kpis' => $key->kpis_count,
                'score' => $key->kpi_score,
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_companies_kpi_performance($sector_id)
    {
        $t = 1;
        $data = [];

        /*$departments = Department::withCount(['employees','kpis' => function ($query) {
            $query->where('employees.company_id', session('current_company'));
        }])->get();*/

        $companies = Company::withCount(['employees', 'kpis'])->where('sector_id', $sector_id)->get();

        foreach ($companies as $key) {
            array_push($data, [
                'department' => $key->title,
                'staff' => $key->employees_count,
                'kpis' => $key->kpis_count,
                'score' => $key->kpi_score,
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function quaters_kpi_performance()
    {
        $t = 1;
        $data = [];
        $departments = KpiTimeline::get();
        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'kpis' => $key->timeline_kpi->count(),
                'kpiActivities' => $key->timeline_activities->count(),
                'completedKpis' => $key->timeline_completed_activities->count(),
                'score' => $key->timeline_score,
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function employee_quaters_kpi_performance($id)
    {
        $t = 1;
        $data = [];
        $departments = KpiTimeline::get();
        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'kpis' => $key->employee_timeline_kpi($id)->count(),
                'kpiActivities' => $key->employee_timeline_activities($id)->count(),
                'completedKpis' => $key->employee_timeline_completed_activities($id)->count(),
                'score' => $key->employee_timeline_score($id),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function employee_perspectiveKpis($id)
    {
        $t = 1;
        $data = [];
        $perspectives = BscPerspective::get();
        foreach ($perspectives as $key) {
            array_push($data, [
                'perspective' => $key->title,
                'kpis' => $key->getKpisAttribute($id)->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function group_perspectiveKpis()
    {
        $data = [];
        $perspectives = BscPerspective::get()->sortByDesc(function($perspective)
        {
            return $perspective->group_kpis->count();
        });
        foreach ($perspectives as $key) {
            array_push($data, [
                'perspective' => $key->title,
                'kpis' => $key->group_kpis->count(),
                'weight' => $key->group_weight,
                'activities' => $key->group_activities->count(),
            ]);
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }


    public static function group_perspectiveKpisPie()
    {
        $data = [];
        $perspectives = BscPerspective::get()->sortByDesc(function($perspective)
        {
            return $perspective->group_kpis->count();
        });
        foreach ($perspectives as $key) {
            array_push($data, [
                'perspective' => $key->title,
                'kpis' => $key->group_kpis->count(),
                /*'weight' => $key->group_weight,*/
                /*'activities' => $key->group_activities->count(),*/
            ]);
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }



    public static function company_perspectiveKpis($id)
    {
        $t = 1;
        $data = [];
        $perspectives = BscPerspective::get();
        foreach ($perspectives as $key) {
            array_push($data, [
                'perspective' => $key->title,
                'kpis' => $key->getCompanyKpisAttribute($id)->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_perspectiveKpis($sector_id)
    {
        $data = [];
        $perspectives = BscPerspective::get()->sortByDesc(function($perspective) use ($sector_id)
        {
            return $perspective->sector_kpis($sector_id)->count();
        });
        foreach ($perspectives as $key) {
            array_push($data, [
                'country' => $key->title,
                'value' => $key->sector_kpis($sector_id)->count(),
            ]);
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }


    public static function department_quaters_kpi_performance($id)
    {
        $t = 1;
        $data = [];
        $departments = KpiTimeline::get();
        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'kpis' => $key->department_timeline_kpi($id)->count(),
                'kpiActivities' => $key->department_timeline_activities($id)->count(),
                'completedKpis' => $key->department_timeline_completed_activities($id)->count(),
                'score' => $key->department_timeline_score($id),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function company_armchar_pie1()
    {
        $t = 1;
        $data = [];
        $departments = Company::where('sector_id', session('current_company_sector'))->get();

        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'employees' => $key->employees->where('status', 1)->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_armchar_pie1()
    {
        $t = 1;
        $data = [];
        $departments = Sector::whereHas('employees')->get();
        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'employees' => $key->employees->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function department_armchar_pie1()
    {
        $t = 1;
        $data = [];
        $departments = Department::withCount(['employees' => function ($query) {
            $query->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        }])->get();

        foreach ($departments as $key) {
            array_push($data, [
                'department' => $key->title,
                'employees' => $key->employees_count,
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function conference_centre_registration_pie1()
    {
        $data = [];
        $centers = Center::get();
        foreach ($centers as $key) {
            array_push($data, [
                'department' => $key->title,
                'employees' => $key->employees->count(),
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function total_leave_days()
    {
        /*$data = Employee::whereHas('position', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->sum('positions.leave_days'); */

        $data = @Employee::join('positions', 'positions.id', '=', 'employees.position_id')->where('employees.company_id', session('current_company'))->sum('positions.leave_days');

        return json_encode(@$data, JSON_UNESCAPED_SLASHES);
    }

    public static function total_leave_days_taken()
    {
        @$data = StaffLeave::whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'))
              ->where('staff_leaves.approved', 1)->where('staff_leaves.company_year_id', session('current_company_year'));
        })->sum('days');

        return json_encode(@$data, JSON_UNESCAPED_SLASHES);
    }

    public static function employee_total_leave_days($id)
    {
        @$data = Employee::find($id)->leaveLeft ?? 0;

        return json_encode(@$data, JSON_UNESCAPED_SLASHES);
    }

    public static function employee_total_leave_days_taken($id)
    {
        @$data = StaffLeave::whereHas('employee', function ($q) use ($id) {
            $q->where('employees.company_id', session('current_company'))->where('staff_leaves.employee_id', $id)
              ->where('staff_leaves.approved', 1)->where('staff_leaves.company_year_id', session('current_company_year'));
        })->sum('days');

        return json_encode(@$data, JSON_UNESCAPED_SLASHES);
    }

    public static function monthly_account_overview_graph()
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);
            $income = 0;
            $expense = 0;

            $income = GlJournalEntry::where('reversed', 0)
                    ->whereYear('date',
                    $d[0])->whereMonth('date',
                    $d[1])->sum('credit');

            $expense = $expense + Expense::whereStatus('approved')->whereYear('date',
                    $d[0])->whereMonth('date',
                    $d[1])->sum('amount');

            array_push($collection_overview, [
                'year' => date_format(date_create($date),
                    'M'.' '.$d[0]),
                'income' => $income,
                'expenses' => $expense,
                'daysOfMonth' => self::workingdays($d[1], $d[0]),
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }

    public static function calender()
    {
        $t = 1;
        $data = [];
        $transactions = DB::table('employees as st')->selectRaw(DB::raw('o.title title,(COALESCE(COUNT(st.id),0)) balance'))->join('sections as o', 'o.id', '=', 'st.department_id')->groupBy('st.department_id')->get();
        foreach ($transactions as $transaction) {
            array_push($data, [
                'country' => $transaction->title,
                'litres' => $transaction->balance,
            ]);
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function ajax_load_calender()
    {
        $holidays = [];
        $data = Holiday::/*where('user_id', Sentinel::getUser()->id )->*/select('holidays.title as title', 'holidays.date as start')->get();
        foreach ($data as $holiday) {
            array_push($holidays, [
                'title' => $holiday->title,
                'start' => $holiday->start,
                'description' => $holiday->title,
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_start = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_start, [
                'title' => $key->title.' Review Start date',
                'start' => $key->review_start_date,
                'description' => $key->title.' Review Start date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_end = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_end, [
                'title' => $key->title.' Review End date',
                'start' => $key->review_end_date,
                'description' => $key->title.' Review End date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $yearBscOpenDate = [];
        $data = CompanyYear::get();
        foreach ($data as $key) {
            array_push($yearBscOpenDate, [
                'title' => $key->title.' Bsc Start date',
                'start' => $key->bsc_open_date,
                'description' => $key->title.' Bsc Start date',
                'className' => 'fc-event-danger fc-event-solid-success',
            ]);
        }

        $yearBscCloseDate = [];
        $data = CompanyYear::get();
        foreach ($data as $key) {
            array_push($yearBscCloseDate, [
                'title' => $key->title.' Bsc End date',
                'start' => $key->bsc_close_date,
                'description' => $key->title.' Bsc End date',
                'className' => 'fc-event-danger fc-event-solid-danger',
            ]);
        }

        $kpiActivities = [];
        $data = EmployeeKpiActivity::where('employee_id', session('current_employee'))->whereHas('kpi', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        foreach ($data as $key) {
            array_push($kpiActivities, [
                'title' => $key->title.' due date',
                'start' => $key->due_date,
                'description' => $key->title.' due date',
                'className' => 'fc-event-danger fc-event-solid-primary',
            ]);
        }

        /*$data = User::join('employees', 'users.id', '=', 'employees.user_id')->where('employees.company_id', session('current_company'))->whereMonth('birth_date', '=', date('m'))
                ->get();*/

        /*$employees = [];*/
        /*foreach ($data as $key) {
            $temp_date = strtotime($key->birth_date);
            $formate_1 = date('d', $temp_date);
            $ym = \Carbon\Carbon::now()->format('Y-m-');
            $new_date = $ym.''.$formate_1;
            array_push($employees, [
                'title' => $key->full_name,
                'start' => $new_date,
                'description' => $key->full_name.',s Birthday ',
                'className' => 'fc-event-solid-info fc-event-light',
            ]);
        }*/

        $calender = array_merge($holidays, $bscReview_start, $bscReview_end, $kpiActivities, $yearBscOpenDate, $yearBscCloseDate);

        return json_encode($calender);
    }

    public static function ajax_load_employee_attendance_calender($id, $month, $year)
    {

        $attendance = [];
        $data = DailyAttendance::where('employee_id', $id)->whereRaw('MONTH(date) = ?', [$month])->whereRaw('YEAR(date) = ?', [$year])->get();
        foreach ($data as $key) {
            array_push($attendance, [
                'title' => 'Present',
                'start' => \Carbon\Carbon::parse($key->date)->format('Y-m-d'),
                'description' => 'Present',
                'className' => 'fc-event-success fc-event-solid-success',
            ]);
        }

        $kpiActivities = [];
        /*    $data = EmployeeKpiActivity::whereHas('kpi', function ($q) use ($id) {
                $q->where('kpis.company_id', session('current_company'))
                    ->where('kpis.company_year_id', session('current_company_year'))
                    ->where('kpis.employee_id', $id);
            })->get();
            foreach ($data as $key) {
                array_push($kpiActivities, [
                    "title" => $key->title. ' due date',
                    "start" => $key->due_date,
                    "description" => $key->title. ' due date',
                    "className" => 'fc-event-danger fc-event-solid-primary',
                ]);
            }*/

        $calender = array_merge($attendance, $kpiActivities);

        return json_encode($calender);
    }

    public static function all_leave_calender()
    {
        $holidays = [];
        $data = Holiday::select('holidays.title as title', 'holidays.date as start')->get();
        foreach ($data as $holiday) {
            array_push($holidays, [
                'title' => $holiday->title,
                'start' => $holiday->start,
                'description' => $holiday->title,
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_start = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_start, [
                'title' => $key->title.' Review Start date',
                'start' => $key->review_start_date,
                'description' => $key->title.' Review Start date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_end = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_end, [
                'title' => $key->title.' Review End date',
                'start' => $key->review_end_date,
                'description' => $key->title.' Review End date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $staffLeave = [];
        $data = StaffLeave::get();
        foreach ($data as $key) {
            array_push($staffLeave, [
                'title' => $key->employee->user->full_name.' Leave',
                'start' => $key->start_date,
                'end' => $key->end_date,
                'description' => $key->employee->user->full_name.' Leave',
                'className' => 'fc-event-danger fc-event-solid-success',
            ]);
        }

        $yearBscCloseDate = [];
        $data = CompanyYear::get();
        foreach ($data as $key) {
            array_push($yearBscCloseDate, [
                'title' => $key->title.' Bsc End date',
                'start' => $key->bsc_close_date,
                'description' => $key->title.' Bsc End date',
                'className' => 'fc-event-danger fc-event-solid-danger',
            ]);
        }

        $kpiActivities = [];
        $data = EmployeeKpiActivity::whereHas('kpi', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.employee_id', session('current_employee'));
        })->get();
        foreach ($data as $key) {
            array_push($kpiActivities, [
                'title' => $key->title.' due date',
                'start' => $key->due_date,
                'description' => $key->title.' due date',
                'className' => 'fc-event-danger fc-event-solid-primary',
            ]);
        }

        $month = strtotime(request()->get('start'));
        $start_month = date('m');
        $month_end = strtotime(request()->get('end'));
        $end_month = date('m');
        $data = User::join('employees', 'users.id', '=', 'employees.user_id')->where('employees.company_id', session('current_company'))->whereMonth('birth_date', '=', date('m'))
                ->get();

        $employees = [];
        foreach ($data as $key) {
            $temp_date = strtotime($key->birth_date);
            $formate_1 = date('d', $temp_date);
            $ym = \Carbon\Carbon::now()->format('Y-m-');
            $new_date = $ym.''.$formate_1;
            array_push($employees, [
                'title' => $key->full_name,
                'start' => $new_date,
                'description' => $key->full_name.',s Birthday ',
                'className' => 'fc-event-solid-info fc-event-light',
            ]);
        }

        $calender = array_merge($holidays, $bscReview_start, $bscReview_end, $kpiActivities, $employees, $staffLeave, $yearBscCloseDate);

        return json_encode($calender);
    }

    public static function subsidiary_leave_calender()
    {
        $bscReview_start = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_start, [
                'title' => $key->title.' Review Start date',
                'start' => $key->review_start_date,
                'description' => $key->title.' Review Start date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_end = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_end, [
                'title' => $key->title.' Review End date',
                'start' => $key->review_end_date,
                'description' => $key->title.' Review End date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $staffLeave = [];
        $data = StaffLeave::where('company_year_id', session('current_company_year'))->whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->get();
        foreach ($data as $key) {
            array_push($staffLeave, [
                'title' => $key->employee->user->full_name.' Leave',
                'start' => $key->start_date,
                'end' => $key->end_date,
                'description' => $key->employee->user->full_name.' Leave',
                'className' => 'fc-event-danger fc-event-solid-success',
            ]);
        }

        $yearBscCloseDate = [];
        $data = CompanyYear::get();
        foreach ($data as $key) {
            array_push($yearBscCloseDate, [
                'title' => $key->title.' Bsc End date',
                'start' => $key->bsc_close_date,
                'description' => $key->title.' Bsc End date',
                'className' => 'fc-event-danger fc-event-solid-danger',
            ]);
        }

        $kpiActivities = [];
        $data = EmployeeKpiActivity::whereHas('kpi', function ($q) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.employee_id', session('current_employee'));
        })->get();
        foreach ($data as $key) {
            array_push($kpiActivities, [
                'title' => $key->title.' due date',
                'start' => $key->due_date,
                'description' => $key->title.' due date',
                'className' => 'fc-event-danger fc-event-solid-primary',
            ]);
        }

        $month = strtotime(request()->get('start'));
        $start_month = date('m');
        $month_end = strtotime(request()->get('end'));
        $end_month = date('m');
        $data = User::join('employees', 'users.id', '=', 'employees.user_id')->where('employees.company_id', session('current_company'))->whereMonth('birth_date', '=', date('m'))
            ->get();

        $employees = [];
        foreach ($data as $key) {
            $temp_date = strtotime($key->birth_date);
            $formate_1 = date('d', $temp_date);
            $ym = \Carbon\Carbon::now()->format('Y-m-');
            $new_date = $ym.''.$formate_1;
            array_push($employees, [
                'title' => $key->full_name,
                'start' => $new_date,
                'description' => $key->full_name.',s Birthday ',
                'className' => 'fc-event-solid-info fc-event-light',
            ]);
        }

        $calender = array_merge($bscReview_start, $bscReview_end, $kpiActivities, $employees, $staffLeave, $yearBscCloseDate);

        return json_encode($calender);
    }

    public static function employee_leave_calender($id)
    {
        $staffLeave = [];
        $data = StaffLeave::where('employee_id', $id)->where('company_year_id', session('current_company_year'))->get();
        foreach ($data as $key) {
            array_push($staffLeave, [
                'title' => $key->employee->user->full_name,
                'start' => $key->start_date,
                'end' => $key->end_date,
                'description' => $key->description,
                'className' => 'fc-event-danger fc-event-solid-success',
            ]);
        }

        $calender = array_merge($staffLeave);

        return json_encode($calender);
    }

    public static function employee_calender($id)
    {
        $attendances = [];
        $data = Attendance::whereYear('date', now())
            ->whereMonth('date', now())->where('employee_id', $id)->get();

        foreach ($data as $attendance) {
            array_push($attendances, [
                'title' => 'Present',
                'start' => $attendance->date,
                'className' => 'fc-event-success fc-event-solid-success',
            ]);
        }

        $holidays = [];
        $data = Holiday::select('holidays.title as title', 'holidays.date as start')->get();
        foreach ($data as $holiday) {
            array_push($holidays, [
                'title' => $holiday->title,
                'start' => $holiday->start,
                'description' => $holiday->title,
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_start = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_start, [
                'title' => $key->title.' Review Start date',
                'start' => $key->review_start_date,
                'description' => $key->title.' Review Start date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $bscReview_end = [];
        $data = KpiTimeline::get();
        foreach ($data as $key) {
            array_push($bscReview_end, [
                'title' => $key->title.' Review End date',
                'start' => $key->review_end_date,
                'description' => $key->title.' Review End date',
                'className' => 'fc-event-danger fc-event-solid-warning',
            ]);
        }

        $staffLeave = [];
        $data = StaffLeavePlan::where('employee_id', $id)->where('company_year_id', session('current_company_year'))->get();
        foreach ($data as $key) {
            array_push($staffLeave, [
                'title' => $key->title,
                'start' => $key->start_date,
                'end' => $key->end_date,
                'description' => $key->description,
                'className' => 'fc-event-danger fc-event-solid-success',
            ]);
        }

        $yearBscCloseDate = [];
        $data = CompanyYear::get();
        foreach ($data as $key) {
            array_push($yearBscCloseDate, [
                'title' => $key->title.' Bsc End date',
                'start' => $key->bsc_close_date,
                'description' => $key->title.' Bsc End date',
                'className' => 'fc-event-danger fc-event-solid-danger',
            ]);
        }

        $kpiActivities = [];
        $data = EmployeeKpiActivity::whereHas('kpi', function ($q) use ($id) {
            $q->where('kpis.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.employee_id', $id);
        })->get();
        foreach ($data as $key) {
            array_push($kpiActivities, [
                'title' => $key->title.' due date',
                'start' => $key->due_date,
                'description' => $key->title.' due date',
                'className' => 'fc-event-danger fc-event-solid-primary',
            ]);
        }

        $month = strtotime(request()->get('start'));
        $start_month = date('m');
        $month_end = strtotime(request()->get('end'));
        $end_month = date('m');
        $data = User::join('employees', 'users.id', '=', 'employees.user_id')->where('employees.company_id', session('current_company'))->where('employees.id', $id)->whereMonth('birth_date', '=', date('m'))
            ->get();

        $employees = [];
        foreach ($data as $key) {
            $temp_date = strtotime($key->birth_date);
            $formate_1 = date('d', $temp_date);
            $ym = \Carbon\Carbon::now()->format('Y-m-');
            $new_date = $ym.''.$formate_1;
            array_push($employees, [
                'title' => $key->full_name,
                'start' => $new_date,
                'description' => $key->full_name.',s Birthday ',
                'className' => 'fc-event-solid-info fc-event-light',
            ]);
        }

        $calender = array_merge($holidays, $bscReview_start, $bscReview_end, $kpiActivities, $employees, $staffLeave, $yearBscCloseDate, $attendances);

        return json_encode($calender);
    }

    public static function ajax_load_calender2()
    {
        $attendance = Attendance::select('*', 'attendance.status as a_status')
            /* ->where('date', '>=', request()->get('start'))->where('date', '<', request()->get('end'))*/
            ->where(function ($query) {
                $query->where('application_status', '=', 'approved')
                    ->orwhere('application_status', '=', null)
                    ->orwhere('attendance.status', '=', 'present');
            })->get();

        $at = [];
        $final = [];
        foreach ($attendance as $attend) {
            $date = $attend->date->format('Y-m-d');
            $at[$date]['status'][] = $attend->a_status;
            $at[$date]['employee'][] = $attend->employee->full_name;
        }
        $i = 0;
        foreach ($at as $index => $att) {
            if (in_array('absent', $att['status'])) {
                foreach ($att['employee'] as $index_emp => $employee) {
                    if ($att['status'][$index_emp] == 'absent') {
                        $final[$i]['title'] = $employee;
                        $final[$i]['start'] = $index;
                        $final[$i]['description'] = $employee;
                        $final[$i]['className'] = 'fc-event-danger fc-event-solid-warning';
                        $i++;
                    }
                }
            } else {
                $final[$i]['title'] = 'all present';
                $final[$i]['start'] = $index;
                $final[$i]['description'] = 'all present';
                $final[$i]['className'] = 'fc-event-danger fc-event-solid-warning';
                $i++;
            }
        }
        $holidays = Holiday::select('holidays.id', 'holidays.occassion as title', 'holidays.date as start')
            /*->where('date', '>=', request()->get('start'))->where('date', '<', request()->get('end'))*/->get()
            ->toArray();

        $month = strtotime(request()->get('start'));
        $start_month = date('m');
        $month_end = strtotime(request()->get('end'));
        $end_month = date('m');
        if ($start_month == 12) {
            $employees = User::select('users.first_name as title', 'users.birth_date as start', DB::raw('\'birthday\' as description'))
                ->where(DB::raw('month(birth_date)'), '<=', $start_month)
                ->where(DB::raw('month(birth_date)'), '<', $end_month)->get()->toArray();
        } elseif ($start_month == 11) {
            $employees = User::select('users.first_name as title', 'users.birth_date as start', DB::raw('\'birthday\' as description'))
                ->where(DB::raw('month(birth_date)'), '>=', $start_month)
                ->where(DB::raw('month(birth_date)'), '>', $end_month)->get()->toArray();
        } else {
            $employees = User::select('users.first_name as title', 'users.birth_date as start', DB::raw('\'birthday\' as description'))
                ->where(DB::raw('month(birth_date)'), '>=', $start_month)
                ->where(DB::raw('month(birth_date)'), '<', $end_month)->get()->toArray();
        }
        /*if ($employees) {
            foreach ($employees as $key => $emp_bday) {
                $temp_date = strtotime($emp_bday['start']);
                $formate_1 = date('m-d', $temp_date);
                $y = \Carbon\Carbon::now();
                $year = $y->year;
                $new_date = $year . '-' . $formate_1;
                $employees[$key]['start'] = $new_date;
            }
        }*/

        $calender = array_merge($final, $holidays, $employees);

        return json_encode($calender);
    }

    /*DATA TABLES*/

    public static function perspectives()
    {
        $data = [];
        $bscPerspectives = BscPerspective::where('company_id', session('current_company'))->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->kras as $kra) {
                array_push($orders, [
                    'OrderID' => @$kra->id,
                    'Title' => @$kra->title,
                    /*"Kpi" => isset($kra->kpis) ? @$kra->kpis->count() : 0,*/
                    /*"ShipName" => $kra->title,*/

                ]

                );
            }

            array_push($data, [
                'RecordID' => @$key->id,
                'Title' => @$key->title,
                /* "LastName" => $key->title,
                    "Company" => $key->title,*/
                /*"Kra" => isset($key->kras) ? @$key->kras->count() : 0,*/
                'Orders' => @$orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function kras()
    {
        $data = [];
        $bscPerspectives = Kra::where('company_id', session('current_company'))->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->kpiObjectives as $Objective) {
                array_push($orders, [
                    'OrderID' => @$Objective->id,
                    'Title' => @$Objective->title,
                    'Kpi' => isset($Objective->kpis) ? @$Objective->kpis->count() : 0,
                    'Timeline' => 1,
                    'Status' => @$Objective->approved,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => $key->bscPerspective->title,
                'Title' => $key->full_title,
                'Kra' => isset($key->kpiObjectives) ? $key->kpiObjectives->count() : 0,
                'Status' => @$key->approved,
                'Orders' => @$orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function competencyMatrix()
    {
        $data = [];

        $bscPerspectives = Competency::where('competencies.company_id', session('current_company'))
        ->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->competency_matrix as $matrix) {
                array_push($orders, [
                    'OrderID' => $matrix->id,
                    'Grade' => @$matrix->competencyGrade->title,
                    'Weight' => isset($matrix->competencyGrade->weight) ? @$matrix->competencyGrade->weight : '',
                    'Description' => @$matrix->description,
                    'Status' => 0,
                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'CompetencyType' => isset($key->competency_type) ? @$key->competency_type->title : '',
                'Competency' => $key->title,
                'Matrix' => $key->competency_matrix->count(),
                'Status' => 1,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function kpiObjectives()
    {
        $data = [];

        $bscPerspectives = KpiObjective::whereHas('kra', function ($q) {
            $q->where('kras.company_id', session('current_company'));
        })->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->kpis->where('employee_id', session('current_employee')) as $kpi) {
                array_push($orders, [
                    'OrderID' => $kpi->id,
                    'Title' => $kpi->title,
                    'Owner' => isset($kpi->employee->user) ? $kpi->employee->user->full_name : '',
                    'Responsibility' => isset($kpi->employeeResponsible->user) ? $kpi->employeeResponsible->user->full_name : '',
                    'Timeline' => @$kpi->time_lines,
                    'Status' => @$kpi->approved,
                    'Cascade' => isset($kpi->employeeResponsible->user) ? 1 : 0,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->kra) ? $key->kra->full_title : '',
                'Title' => $key->title,
                'Kra' => @$key->kpis->where('employee_id', session('current_employee'))->count(),
                'Status' => @$key->approved,
                'Orders' => @$orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function unapprovedKpis()
    {
        $data = [];

        $kpis = Kpi::whereHas('employee', function ($q) {
            $q->where('employees.company_id', session('current_company'))
                ->where('kpis.company_year_id', session('current_company_year'))
                ->where('kpis.supervisor_employee_id', session('current_employee'))
                ->where('kpis.approved', 0);
        })->get();
        foreach ($kpis as $key) {
            $orders = [];
            foreach ($key->kpiActivities as $activity) {
                array_push($orders, [
                    'OrderID' => $activity->id,
                    'Title' => $activity->title,
                    'DueDate' => $activity->due_date,
                    'Status' => $activity->approved,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->full_title) ? $key->full_title : '',
                'Employee' => isset($key->employee->user) ? $key->employee->user->full_name : '',
                'Activities' => isset($key->kpiActivities) ? $key->kpiActivities->count() : '',
                'Status' => $key->approved,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function competencyFramework()
    {
        $data = [];

        $competencyTypes = CompetencyFramework::whereHas('position', function ($q) {
            $q->where('positions.company_id', session('current_company'));
        })->get();
        foreach ($competencyTypes as $key) {
            $orders = [];
            /*foreach ($key->positions as $competency_framework) {

                array_push($orders, [
                        "OrderID" => $competency_framework->id,
                        "Title" => $competency_framework->title,
                        "Department" => $competency_framework->pivot->id,
                        "level1" => $competency_framework->id,
                        "level2" => $competency_framework->id,
                        "level3" => $competency_framework->id,

                    ]

                );
            }*/

            array_push($data, [
                'RecordID' => $key->id,
                'Position' => @$key->position->title,
                'Department' => @$key->section->title,
                'CompetencyLevel' => @$key->competencyMatrix->full_title,
                'Frameworks' => isset($key->positions) ? $key->positions->count() : '0',
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function succession_planning()
    {
        $data = [];

        $competencyTypes = SuccessionPlanning::whereHas('position', function ($q) {
            $q->where('positions.company_id', session('current_company'));
        })->get();
        foreach ($competencyTypes as $key) {
            array_push($data, [
                'RecordID' => $key->id,
                'Position' => $key->position->title,
                'Department' => $key->section->title,
                'Successor' => isset($key->employee->user) ? $key->employee->user->full_name : '',
                /*$current = Employee::wherePositionId($key->position_id)->whereSectionId($key->department_id)->first(),
                    "Current" => isset($current->user) ? $current->user->full_name : '',*/
                /*"Orders" => $orders,*/

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function qualificationFramework()
    {
        $data = [];

        $competencyTypes = QualificationFramework::whereHas('position', function ($q) {
            $q->where('positions.company_id', session('current_company'));
        })->get();
        foreach ($competencyTypes as $key) {
            $orders = [];
            /*foreach ($key->positions as $competency_framework) {

                array_push($orders, [
                        "OrderID" => $competency_framework->id,
                        "Title" => $competency_framework->title,
                        "Department" => $competency_framework->pivot->id,
                        "level1" => $competency_framework->id,
                        "level2" => $competency_framework->id,
                        "level3" => $competency_framework->id,

                    ]

                );
            }*/

            array_push($data, [
                'RecordID' => $key->id,
                'Position' => $key->position->title,
                'Department' => $key->section->title,
                'CompetencyLevel' => isset($key->qualification) ? $key->qualification->title : '',
                'Frameworks' => isset($key->positions) ? $key->positions->count() : '0',
                'Status' => 1,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function jobDescription()
    {
        $data = [];

        $competencyTypes = JobDescription::whereHas('position', function ($q) {
            $q->where('positions.company_id', session('current_company'));
        })->get();
        foreach ($competencyTypes as $key) {
            $orders = [];
            /*foreach ($key->positions as $competency_framework) {

                array_push($orders, [
                        "OrderID" => $competency_framework->id,
                        "Title" => $competency_framework->title,
                        "Department" => $competency_framework->pivot->id,
                        "level1" => $competency_framework->id,
                        "level2" => $competency_framework->id,
                        "level3" => $competency_framework->id,

                    ]

                );
            }*/

            array_push($data, [
                'RecordID' => $key->id,
                'Position' => isset($key->position) ? $key->position->title : '',
                'Department' => isset($key->section) ? $key->section->title : '',
                'Status' => 1,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function kpiActivities()
    {
        $data = [];

        $bscPerspectives = Kpi::where('employee_id', session('current_employee'))
            ->where('company_year_id', session('current_company_year'))
            ->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->kpiActivities as $kpiActivity) {
                array_push(@$orders, [
                    'OrderID' => @$kpiActivity->id,
                    'ShipCountry' => @$kpiActivity->title,
                    'Weight' => @$kpiActivity->weight,
                    'ShipName' => @$kpiActivity->title,
                    'Timeline' => @$kpiActivity->title,
                    'Status' => @$kpiActivity->status_id,
                    'DueDate' => date('d-m-Y', strtotime(@$kpiActivity->due_date)),

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->kpiObjective->full_title) ? @$key->kpiObjective->full_title : '',
                'Title' => $key->title,
                'Kra' => isset($key->kpiActivities) ? @$key->kpiActivities->count() : 0,
                'Status' => @$key->approved,
                'Orders' => @$orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function competencyGaps()
    {
        $data = [];

        $bscPerspectives = Employee::where('company_id', session('current_company'))
            ->with('user', 'section', 'position')
            ->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->position->competencyFrameworks->where('department_id', $key->department_id)->whereNotIn('competency_matrix_id', $key->competency_ids) as $competency) {
                array_push($orders, [
                    'OrderID' => @$competency->id,
                    'ShipCountry' => @$competency->competencyMatrix->full_title,
                    'ShipAddress' => @$competency->id,
                    'ShipName' => @$competency->id,
                    'Timeline' => @$competency->id,
                    'Status' => @$competency->id,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->section) ? $key->section->title : '',
                'Title' => $key->user->full_name,
                'Kra' => isset($key->competencyGap) ? $key->competencyGap : 0,
                'Status' => $key->status,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function qualificationGaps()
    {
        $data = [];

        $bscPerspectives = Employee::where('company_id', session('current_company'))
            ->with('user', 'section', 'position')
            ->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->position->qualificationMatrix->where('department_id', $key->department_id)->whereNotIn('qualification_id', $key->qualification_ids) as $competency) {
                array_push($orders, [
                    'OrderID' => @$competency->id,
                    'ShipCountry' => @$competency->qualification->title,
                    'ShipAddress' => @$competency->id,
                    'ShipName' => @$competency->id,
                    'Timeline' => @$competency->id,
                    'Status' => @$competency->id,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->section) ? $key->section->title : '',
                'Title' => $key->user->full_name,
                'Kra' => isset($key->qualificationGap) ? $key->qualificationGap : 0,
                'Status' => $key->status,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function learningGaps()
    {
        $data = [];

        $bscPerspectives = Kpi::where('employee_id', session('current_employee'))
            ->where('company_year_id', session('current_company_year'))
            ->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->competencyGaps as $competencyGap) {
                array_push($orders, [
                    'OrderID' => $competencyGap->id,
                    'ShipCountry' => $competencyGap->title,
                    'ShipAddress' => $competencyGap->title,
                    'ShipName' => $competencyGap->title,
                    'Timeline' => $competencyGap->title,
                    'Status' => $competencyGap->status,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->kpiObjective) ? $key->kpiObjective->full_title : '',
                'Title' => $key->title,
                'Kra' => isset($key->competencyGaps) ? $key->competencyGaps->count() : 0,
                'Status' => $key->approved,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function perspectiveKras($id)
    {
        $data = [];
        $bscPerspectives = Kra::where('bsc_perspective_id', $id)->where('company_id', session('current_company'))->get();
        foreach ($bscPerspectives as $key) {
            $orders = [];
            foreach ($key->kpiObjectives as $kpi) {
                array_push($orders, [
                    'OrderID' => $kpi->id,
                    'ShipCountry' => $kpi->title,
                    'ShipAddress' => $kpi->title,
                    'ShipName' => $kpi->title,

                ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => $key->bscPerspective->title,
                'Title' => $key->title,
                'Kra' => isset($key->kpiObjectives) ? $key->kpiObjectives->count() : 0,
                'Orders' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function login_overview_graph()
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);

            $value = LoginHistory::whereYear('created_at', $d[0])
                        ->whereMonth('created_at', $d[1])
                        ->count();

            array_push($collection_overview, [
                'date' => date_format(date_create($date),
                    $d[0].''.$d[1]),
                'value' => $value,
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }

    public static function income_expenditure($group_id)
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        $IncomeFrom = 40001;
        $IncomeTo = 49999;
        $ExoenseFrom = 50210;
        $ExpenseTo = 81520;
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);

            $income = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $d[0])
                ->whereMonth('posting_date', $d[1])
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [$IncomeFrom, $IncomeTo])
                ->sum('amount');

            $expense = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->join('sectors', 'sectors.id', '=', 'companies.sector_id')
                ->where('sectors.group_id', '=',  $group_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $d[0])
                ->whereMonth('posting_date', $d[1])
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [$ExoenseFrom, $ExpenseTo])
                ->sum('amount');

            array_push($collection_overview, [
                'year' => date_format(date_create($date),
                    'M'.' '.$d[0]),
                'income' => str_replace('-', '', $income),
                'expenses' => $expense,
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }

    public static function sector_monthly_income_expenditure($sector_id)
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        $IncomeFrom = 40001;
        $IncomeTo = 49999;
        $ExoenseFrom = 50210;
        $ExpenseTo = 81520;
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);

            $income = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.sector_id', $sector_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $d[0])
                ->whereMonth('posting_date', $d[1])
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [$IncomeFrom, $IncomeTo])
                ->sum('amount');

            $expense = Gl_entry::join('companies', 'companies.id', '=', 'gl_entries.company_id')
                ->where('companies.sector_id', $sector_id)
                ->where('companies.active', '=', 'Yes')
                ->whereYear('posting_date', $d[0])
                ->whereMonth('posting_date', $d[1])
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [$ExoenseFrom, $ExpenseTo])
                ->sum('amount');

            array_push($collection_overview, [
                'year' => date_format(date_create($date),
                    'M'.' '.$d[0]),
                'income' => str_replace('-', '', $income),
                'expenses' => $expense,
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }


    public static function subsidiary_income_expenditure($id)
    {
        $collection_overview = [];
        $date = date_format(date_sub(date_create(date('Y-m-d')),
            date_interval_create_from_date_string('1 years')),
            'Y-m-d');
        $IncomeFrom = 40001;
        $IncomeTo = 49999;
        $ExoenseFrom = 50210;
        $ExpenseTo = 81520;
        for ($i = 1; $i <= 13; $i++) {
            $d = explode('-', $date);

            $income = Gl_entry::where('company_id', $id)
                ->whereYear('posting_date', $d[0])
                ->whereMonth('posting_date', $d[1])
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [$IncomeFrom, $IncomeTo])
                ->sum('amount');

            $expense = Gl_entry::where('company_id', $id)
                ->whereYear('posting_date', $d[0])
                ->whereMonth('posting_date', $d[1])
                ->where('reversed', 0)
                ->whereBetween('gl_account_no', [$ExoenseFrom, $ExpenseTo])
                ->sum('amount');

            array_push($collection_overview, [
                'year' => date_format(date_create($date),
                    'M'.' '.$d[0]),
                'income' => str_replace('-', '', $income),
                'expenses' => $expense,
            ]);
            $date = date_format(date_add(date_create($date),
                date_interval_create_from_date_string('1 months')),
                'Y-m-d');
        }

        return json_encode($collection_overview, JSON_UNESCAPED_SLASHES);
    }

    public static function employee_total_score($employeeId, $year)
    {
        $score = EmployeeKpiScore::where('employee_id', $employeeId)->where('company_year_id', $year)->get()->sum('score') ?? 0;

        return number_format($score, 2);
    }

    public static function employee_total_score2($employeeId)
    {
        $data = [];
        $employee = Employee::find($employeeId);
        foreach ($employee->yearKpis as  $kpi) {
            $averageRating = KpiPerformanceReview::where('kpi_id', $kpi->id)->where('employee_id', $employeeId)->first()->average('agreed_rating');
            $score = ($averageRating / 5) * $employee->kpiWeight($kpi->id);
            array_push($data, [$score]
            );
        }

        return $data;
    }

    public static function sector_total_score($sectorId)
    {

        $review = EmployeeYearGrade::whereHas('employee.company', function ($q) use ($sectorId) {
            $q->where('companies.sector_id', $sectorId)
                ->whereNull('employees.deleted_at')
                ->where('employees.status', '=', 1)
                ->where('employee_year_grades.company_year_id', session('current_company_year'));
        })->get();
        if ($review->sum('performance_score') > 0) {
            $data = @$review->average('performance_score');
        } else {
            $data = 0;
        }

        return round(@$data);
    }

    public static function group_total_score()
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee.school', function ($q) {
            $q->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $data = @$review->average('score');

        return round($data);
    }

    public static function _employee_competency_score($employeeId)
    {
        $review = EmployeeCompetencyMatrix::where('employee_id', $employeeId)
            ->where('company_year_id', session('current_company_year'))
            ->get();
        if ($review->sum('weight') > 0) {
            $data = @$review->average('weight');
        } else {
            $data = 0;
        }

        return round($data).'%';
    }

    public static function employees_competency_score()
    {
        $data = [];

        $employees = Employee::where('company_id', session('current_company'))
            ->with('user', 'section', 'position')
            ->get();
        foreach ($employees as $key) {
            $orders = [];
            foreach ($key->employee_competencies as $competency) {
                array_push($orders,

                    [
                        'Score' => @$competency->EmployeeCompetencyMatrix->max('weight'),
                    ]

                );
            }

            array_push($data, [
                'RecordID' => $key->id,
                'Department' => isset($key->section) ? $key->section->title : '',
                'Title' => $key->user->full_name,
                'Score' => $orders,

            ]
            );
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public static function employee_competency_score($id)
    {
        $employee = Employee::find($id);

        $maxWeight = [];
        foreach ($employee->employee_competencies as $competency) {
            array_push($maxWeight,

                        @$competency->employeeCompetencyMatrix->where('employee_id', $id)->max('weight')

                );
        }

        @$count = count($maxWeight);
        @$sum = array_sum($maxWeight);
        /*@$average = number_format(@$sum/@$count, 2);*/

        if ($employee->expected_competencies_number > 0) {
            $score = (($sum / $employee->expected_competencies_number) / 4) * 100;
        } else {
            $score = 0;
        }

        return json_encode($score, JSON_UNESCAPED_SLASHES);
    }

    public static function company_total_score($company_id)
    {
        $review = EmployeeYearGrade::whereHas('employee', function ($q) use ($company_id) {
            $q->where('employees.company_id', $company_id)
              ->whereNull('employees.deleted_at')
              ->where('employees.status', '=', 1)
              ->where('employee_year_grades.company_year_id', session('current_company_year'));
        })->get();
        if ($review->sum('performance_score') > 0) {
            $data = @$review->average('performance_score');
        } else {
            $data = 0;
        }

        return round(@$data);
    }

    public static function department_total_score($department)
    {
        $review = KpiPerformanceReview::whereHas('kpi.employee', function ($q) use ($department) {
            $q->where('kpis.company_id', session('current_company'))
              ->where('kpis.approved', '=', 1)
              ->where('employees.status', '=', 1)
              ->where('employees.department_id', '=', $department)
              ->where('kpis.company_year_id', session('current_company_year'));
        })->get();
        $data = @$review->average('score');

        return round($data).'%';
    }

    public static function getDayAttendanceCharts2($date)
    {
        $beforeSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '05:00:00')
            ->whereTime('date', '<=', '07:30:00')->unique('employee_id')->count();

        $afterSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('created_at', '>', '07:30:00')
            ->whereTime('created_at', '<=', '08:00:00')->count();

        $sevenTandEightT = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('created_at', '>', '08:00:00')
            ->whereTime('created_at', '<=', '08:30:00')->count();

        $eightThirtyToNine = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('created_at', '>', '08:30:00')
            ->whereTime('created_at', '<=', '09:00:00')->count();

        $afterNine = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('created_at', '>', '09:00:00')
            ->whereTime('created_at', '<=', '13:30:00')
            ->count();

        return [
            0,
            isset($beforeSevenThirty) ? $beforeSevenThirty : 0,
            isset($afterSevenThirty) ? $afterSevenThirty : 0,
            isset($sevenTandEightT) ? $sevenTandEightT : 0,
            isset($eightThirtyToNine) ? $eightThirtyToNine : 0,
            isset($afterNine) ? $afterNine : 0,
        ];

//    'before_Eight'=>
//    'after_sevenT'=>
//    'btn_sevenThirtyAndEight'=>
//    'eightTtoNinge'=>
//    'afterNine'=>
//    'totalNumber'=> $beforeSevenThirty + $afterSevenThirty + $sevenTandEightT + $eightThirtyToNine + $afterNine,
    }

    public static function getDayAttendanceCharts($date)
    {
        $t = 1;
        $colors = ['#FF0F00', '#FF6600', '#FF9E01"', '#FCD202', '#F8FF01', '#B0DE09', '#04D215', '#0D8ECF', '#0D52D1', '#2A0CD0', '#8A0CCF', '#CD0D74'];
        $departments = ['7am-7:30am', '7:30am-8:00am', '8:01am-8:30am', '8:31am-9:00am', ' After 9:00am'];
        $pets = ['Morie', 'Miki', 'Halo', 'lab' => 'Winnie'];
        $data = [];
        $data2 = [];
        /*$departments = Department::whereHas('employees', function ($q) {
            $q->where('employees.company_id', session('current_company'));
        })->get();*/

        $beforeSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>=', '05:00:00')
            ->whereTime('date', '<=', '07:30:00')->count();

        $afterSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '07:30:00')
            ->whereTime('date', '<=', '08:00:00')->count();

        $sevenTandEightT = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '08:00:00')
            ->whereTime('date', '<=', '08:30:00')->count();

        $eightThirtyToNine = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '08:30:00')
            ->whereTime('date', '<=', '09:00:00')->count();

        $afterNine = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '09:00:00')
            ->whereTime('date', '<=', '13:30:00')
            ->count();

        $numbers2 = [
            isset($beforeSevenThirty) ? $beforeSevenThirty : 0,
            isset($afterSevenThirty) ? $afterSevenThirty : 0,
            isset($sevenTandEightT) ? $sevenTandEightT : 0,
            isset($eightThirtyToNine) ? $eightThirtyToNine : 0,
            isset($afterNine) ? $afterNine : 0,

        ];

        foreach ($departments as $key) {
            array_push($data, [
                'country' => $key,
                'visits' => $numbers2[array_rand($colors)],
                'color' => $colors[array_rand($colors)],
                'link' => 'schoolyear/show',
            ]);
        }

        /*$final = array_merge($data, $data2);*/
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    public function getMorningSessionStats($date)
    {
        $beforeSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>=', '05:00:00')
            ->whereTime('date', '<=', '07:30:00')->count();

        $afterSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '07:30:00')
            ->whereTime('date', '<=', '08:00:00')->count();

        $sevenTandEightT = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '08:00:00')
            ->whereTime('date', '<=', '08:30:00')->count();

        $eightThirtyToNine = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '08:30:00')
            ->whereTime('date', '<=', '09:00:00')->count();

        $afterNine = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '09:00:00')
            ->whereTime('date', '<=', '13:30:00')
            ->count();

        return [
            '7am-7:30am' => isset($beforeSevenThirty) ? $beforeSevenThirty : 0,
            '7:30-8:00am' => isset($afterSevenThirty) ? $afterSevenThirty : 0,
            '8:00am-8:30am' => isset($sevenTandEightT) ? $sevenTandEightT : 0,
            '8:30am-9:00am' => isset($eightThirtyToNine) ? $eightThirtyToNine : 0,
            'afterNine' => isset($afterNine) ? $afterNine : 0,
        ];

//    'before_Eight'=>
//    'after_sevenT'=>
//    'btn_sevenThirtyAndEight'=>
//    'eightTtoNinge'=>
//    'afterNine'=>
//    'totalNumber'=> $beforeSevenThirty + $afterSevenThirty + $sevenTandEightT + $eightThirtyToNine + $afterNine,
    }

    public static function get730($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '05:00:00')
            ->whereTime('date', '<=', '07:29:00')->distinct('employee_id')->count();
    }

    public static function getAllPresent($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')->distinct('employee_id')->count();
    }

    public static function get800($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '07:30:00')
            ->whereTime('date', '<=', '08:00:00')->distinct('attendance.employee_id')->count();
    }

    public static function get830($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '08:01:00')
            ->whereTime('date', '<=', '08:29:00')->distinct('attendance.employee_id')->count();
    }

    public static function get900($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '08:30:00')
            ->whereTime('date', '<=', '08:59:00')->distinct('attendance.employee_id')->count();
    }

    public static function get1000($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '09:00:00')
            ->whereTime('date', '<=', '09:59:00')->distinct('attendance.employee_id')->count();
    }

    public static function get1200($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '10:00:00')
            ->whereTime('date', '<=', '12:00:00')->distinct('attendance.employee_id')->count();
    }

    public static function getAfter1200($date)
    {
        return  Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'))->where('employees.status', 1);
        })->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date)
            ->where('device', 'ENTRANCE-IN')
            ->whereTime('date', '>=', '12:01:00')->distinct('attendance.employee_id')->count();
    }

    public static function absent($date)
    {
        return  Employee::where('company_id', session('current_company'))->where('status', 1)->whereDoesntHave('attendance', function ($q) use ($date) {
            $q->whereYear('date', $date)
            ->whereMonth('date', $date)
            ->whereDay('date', $date);
        })->count();
    }

    public static function afterSevenThirty($date)
    {
        $afterSevenThirty = Attendance::whereHas('employee', function ($q) use ($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('date', '>', '07:30:00')
            ->whereTime('date', '<=', '08:00:00')->count();

        return  isset($afterSevenThirty) ? $afterSevenThirty : 10;
    }

    public static function workingdays($month, $year)
    {
        $workdays = [];
        $type = CAL_GREGORIAN;
        /*$month = date('n'); // Month ID, 1 through to 12.
        $year = date('Y'); // Year in 4 digit 2009 format.*/
        $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days

        //loop through all days
        for ($i = 1; $i <= $day_count; $i++) {
            $date = $year.'/'.$month.'/'.$i; //format date
            $get_name = date('l', strtotime($date)); //get week day
            $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

            //if not a weekend add day to array
            if ($day_name != 'Sun' && $day_name != 'Sat') {
                $workdays[] = $i;
            }
        }

        return count($workdays);
    }

    public static function monthlyWorkingHours($month, $year)
    {
        $workdays = [];
        $type = CAL_GREGORIAN;
        /*$month = date('n'); // Month ID, 1 through to 12.
        $year = date('Y'); // Year in 4 digit 2009 format.*/
        $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days

        //loop through all days
        for ($i = 1; $i <= $day_count; $i++) {
            $date = $year.'/'.$month.'/'.$i; //format date
            $get_name = date('l', strtotime($date)); //get week day
            $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

            //if not a weekend add day to array
            if ($day_name != 'Sun' && $day_name != 'Sat') {
                $workdays[] = $i;
            }
        }

        return count($workdays) * 8;
    }

    public static function employeeMonthlyWorkHours($employee, $month, $year)
    {
        $workdays = [];
        $type = CAL_GREGORIAN;
        /*$month = date('n'); // Month ID, 1 through to 12.
        $year = date('Y'); // Year in 4 digit 2009 format.*/
        $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days

        /*$attendance = Attendance::whereEmployeeId($employee)->whereYear('date', $year)
            ->whereMonth('date', $month)->distinct('date')->count('date');*/

        /*
        $attendance = Attendance::whereEmployeeId($employee)->whereYear('date', $year)
        ->whereMonth('date', $month)->distinct('date')->count('date');

        *//*
        $attendance = Attendance::whereHas('employee', function ($q) use($date) {
            $q->where('employees.company_id', session('current_company'));
        })->whereDay('date', $date)
            ->whereTime('created_at', '>', '07:30:00')
            ->whereTime('created_at', '<=', '08:00:00')->count()*/

        //loop through all days
        $b = 0;
        for ($i = 1; $i <= $day_count; $i++) {
            $date = $year.'-'.$month.'-'.$i; //format date
            $date = Carbon::create($date);

            /* $attendance = Attendance::whereHas('employee', function ($q) use($date, $employee) {
                 $q->where('employees.id', $employee)->where('employees.status', 1);
             })->whereYear('date', $date)->whereMonth('date', $date)->whereDay('date', $date)->where('device', 'ENTRANCE-IN')->get();

                 foreach ($attendance as $k)
                 {
                     $b++;
                 }*/
        }

        return $b;
    }

    public static function employeeMonthly_work_days($id, $month, $year)
    {
        $employee = Attendance::whereHas('employee', function ($q) use ($id, $month, $year) {
            $q->where('employees.id', $id)->where('employees.status', 1);
        })->whereYear('date', $year)->whereMonth('date', $month)->where('device', 'ENTRANCE-IN')->first();
        $final = [];
        $i = 0;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workdays = [];

        foreach ($employee->attendance as $attendance) {
            $days = [Carbon::parse($attendance->date)->day];
            $days = count($days);
            $days++;
        }

        /*dd($days);*/
        return @$days;
    }

    public function formatPhoneNo($no)
    {
        switch (true) {
            case preg_match('#^7\d{8}$#', $no):
                $no = '+233'.$no;
                break;
            case preg_match('#^07\d{8}$#', $no):
                $no = '+233'.substr($no, 1);
                break;
            case preg_match('#^233\d{8}$#', $no):
                $no = '+'.$no;
                break;
            case preg_match('#^00233\d{8}$#', $no):
                $no = '+'.substr($no, 2);
                break;
            case preg_match('#^\+233\d{8}$#', $no):
                break;
            default:
                throw new InvalidArgumentException('Invalid format supplied');
                break;
        }

        return $no;
    }

    /**
     * Check if the @param is formatted as an e-mail address.
     *
     * @param string $emailToCkeck
     * @return bool
     */
    public static function validateEmail($emailToCkeck)
    {
        $my_data = [
            'email' => $emailToCkeck,
        ];
        $validator = \Illuminate\Support\Facades\Validator::make($my_data, [
            'email' => 'email',
        ]);
        if ($validator->fails()) {
            return false;
        } else {
            return true;
        }
    }
}
