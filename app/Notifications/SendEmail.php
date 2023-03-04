<?php

namespace App\Notifications;

use App\Models\Company;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use PDF;

class SendEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var array
     */
    public $user;
    public $kpi;
    public $school;

    /**
     * Create a new message instance.
     *
     * @param array $user
     * @param array $kpi
     */
    public function __construct($user, $kpi)
    {
        $this->user = $user;
        $this->kpi = $kpi;
        $this->school = Company::find(session('current_company'));;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        /*$pdf = PDF::loadView('letters.duc4');*/
        return $this->view('emails.studentApproval')
            ->from($this->school->email, $this->school->title)
            ->subject('UHG Performance System ');
            /*->attachData($pdf->output(), "admissionLetter.pdf", [
                'mime' => 'application/pdf',
            ]);*/
    }
}
