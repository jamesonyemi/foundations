<?php

namespace App\Http\Controllers\Secure;

use App\Helpers\Settings;
use App\Models\Employee;
use App\Models\User;
use PDF;
use Sentinel;

class VisitorStudentCardController extends SecureController
{
    private $begin_html = '';

    public function __construct()
    {
        parent::__construct();

        $this->begin_html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <html>
                    <head>
                        <style>
                        table {
                            border-collapse: collapse;
                             width:100%;
                        }

                        table, td, th {
                            border: 1px solid #000000;
                            text-align:center;
                            vertical-align:middle;
                        }
                        </style>
                    </head>
                ';
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     * @return Response
     * @internal param User $visitor
     */
    public function visitor(User $user)
    {
        if (isset($user->visitor)) {
            $data = $this->begin_html.'<title>'.trans('visitor_student_card.visitor_card').'</title>
        <body style="height: 0;
				    padding: 0;
				    padding-bottom: 75%;
				    background-image: url('.url('uploads/visitor_card/'.Settings::get('visitor_card_background')).');
				    background-position: top left;
				    background-size: 100%;
				    background-repeat: no-repeat;">';
            $data .= '<table><tr><td>
                <h1>'.trans('visitor_student_card.visitor_card').' - '.Settings::get('name').'</h1>';
            $data .= '<h2>'.$user->full_name.'</h2>';
            $data .= '<h2>'.$user->email.'</h2>';
            $data .= '<h2>'.trans('visitor_student_card.visitor_no').': '.$user->visitor->last()->visitor_no.'</h2>';
            $data .= '</td></tr></table></body></html>';
            $pdf = PDF::loadHTML($data);

            return $pdf->stream();
        } else {
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Employee $student
     *
     * @return Response
     */
    public function student(Employee $student)
    {
        $school = $student->school;
        $data = $this->begin_html.'<title>'.trans('visitor_student_card.student_card').'</title>
            <body style="height: 100%;
			    padding: 0;
			    padding-bottom: 75%;
			    background-image: url('.url($school->student_card_background_photo).');
			    background-position: top left;
			    background-size: 100%;
			    background-repeat: no-repeat;">';
        $data .= '<table><tr><td>
            <h1>'.trans('visitor_student_card.student_card').' - '.$school->title.'</h1>';
        $data .= '<h2>'.$student->user->full_name.'</h2>';
        $data .= '<h2>'.$student->user->email.'</h2>';
        $data .= '<h2>'.trans('visitor_student_card.student_no').': '.$student->student_no.'</h2></td><td>';
        $data .= '<img src="'.url($student->user->picture).'"></td></tr></table>';
        $data .= '</body></html>';
        $pdf = PDF::loadHTML($data);

        return $pdf->stream();
    }
}
