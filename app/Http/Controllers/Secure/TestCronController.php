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

class TestCronController extends SecureController
{
    public function index()
    {
        $data = [
            'email' => 'makoto@jospongroup.com',
            'name' => 'Mathew Akoto',
            'subject' => 'Test Cron Job on Jospong PMS',
            'code' => 0001,
            'id' => 11,
        ];
        Mail::send('emails.reminder', $data, function ($message) use ($data) {
            $message->to($data['email'], $data['name'])->subject($data['subject']);
        });
    }
}
