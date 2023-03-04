<?php

namespace App\Http\Controllers\Secure;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Student;
use App\Http\Requests\Secure\SectionRequest;
use App\Models\Invoice;
use App\Models\Option;
use App\Models\Section;
use App\Models\StudentRegistrationCode;
use App\Repositories\DormitoryRepository;
use App\Repositories\SectionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubjectRepository;
use DB;
use Efriandika\LaravelSettings\Facades\Settings;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Sentinel;
use Snowfire;
use Yajra\DataTables\Facades\DataTables;

class LiveController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function live()
    {
        $title = 'JOSPONG LEADERSHIP CONFERENCE 2021';

        return view('live.index', compact('title'));
    }
}
