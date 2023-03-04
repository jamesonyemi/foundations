{{--  Finance statistics 1--}}
<div class="row">
    <div class="col-xl-3 col-lg-6 col-12">
        <?php
        $target = 0;
        foreach (\App\Models\GeneralLedger::where('transaction_date', date("Y"))->where('transaction_date',
            date("m"))->get() as $key) {
            $target = $target + $key->credit;
        }
        $paid_this_year = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereYear('transaction_date', '=', date('Y'))->sum('debit');
        $paid_this_month = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereMonth('transaction_date', '=', date('m'))->sum('debit');
        $paid_last_month = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereMonth('transaction_date', '=', date('m') - 1)->sum('debit');
        if ($target > 0) {
            $percent = round(($paid_this_month / $target) * 100);
        } else {
            $percent = 0;
        }
        ?>
        <a href="{{ url('./student') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="info">{{ number_format(\App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->where('transaction_date',date("Y-m-d"))->sum('debit'),2) }}</h3>
                                <h6>Today</h6>
                            </div>
                            <div>
                                <i class="icon-basket-loaded info font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-info" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-3 col-lg-6 col-12">
        <a href="{{ url('./applicant') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="warning">{{ number_format(\App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereBetween('transaction_date', [Carbon\Carbon::now()->startOfWeek(), Carbon\Carbon::now()->endOfWeek()])->sum('debit'),2) }}</h3>
                                <h6>This Week</h6>
                            </div>
                            <div>
                                <i class="icon-pie-chart warning font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-warning" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-3 col-lg-6 col-12">
        <a href="{{ url('./student/admission') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="success">{{ number_format($paid_this_month,2) }}</h3>
                                <h6>This Month</h6>
                            </div>
                            <div>
                                <i class="icon-user-follow success font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-3 col-lg-6 col-12">

        <a href="{{ url('./registration') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="danger">{{ number_format($paid_this_year,2) }}</h3>
                                <h6>This Year</h6>
                            </div>
                            <div>
                                <i class="icon-heart danger font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-danger" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>


    </div>
</div>







{{-- Finance statistics 2--}}

<div class="row">
    <div class="col-xl-3 col-lg-6 col-12">
        <?php
        $target = 0;
        foreach (\App\Models\GeneralLedger::where('transaction_date', date("Y"))->where('transaction_date',
            date("m"))->get() as $key) {
            $target = $target + $key->credit;
        }
        $paid_last_year = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereYear('transaction_date', '=', date('Y')- 1)->sum('debit');
        $paid_this_month = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereMonth('transaction_date', '=', date('m'))->sum('debit');
        $paid_last_month = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereMonth('transaction_date', '=', date('m') - 1)->sum('debit');
        if ($target > 0) {
            $percent = round(($paid_this_month / $target) * 100);
        } else {
            $percent = 0;
        }
        ?>
        <a href="{{ url('./student') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="info">{{ number_format(\App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->where('transaction_date',date("Y-m-d" , strtotime("yesterday")) )->sum('debit'),2) }}</h3>
                                <h6>Yesterday</h6>
                            </div>
                            <div>
                                <i class="icon-basket-loaded info font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-info" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-3 col-lg-6 col-12">
        <a href="{{ url('./applicant') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="warning">{{ number_format(\App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereBetween('transaction_date', [Carbon\Carbon::now()->subWeek()->startOfWeek(), Carbon\Carbon::now()->subWeek()->endOfWeek()])->sum('debit'),2) }}</h3>
                                <h6>Last Week</h6>
                            </div>
                            <div>
                                <i class="icon-pie-chart warning font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-warning" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-3 col-lg-6 col-12">
        <a href="{{ url('./student/admission') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="success">{{ number_format($paid_last_month,2) }}</h3>
                                <h6>Last Month</h6>
                            </div>
                            <div>
                                <i class="icon-user-follow success font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>

    </div>
    <div class="col-xl-3 col-lg-6 col-12">

        <a href="{{ url('./registration') }}">
            <div class="card pull-up">
                <div class="card-content">
                    <div class="card-body">
                        <div class="media d-flex">
                            <div class="media-body text-left">
                                <h3 class="danger">{{ number_format($paid_last_year,2) }}</h3>
                                <h6>Last Year</h6>
                            </div>
                            <div>
                                <i class="icon-heart danger font-large-2 float-right"></i>
                            </div>
                        </div>
                        <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                            <div class="progress-bar bg-gradient-x-danger" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>


    </div>
</div>





<div class="row">

</div>

<section id="data">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Fees Collection Info</h4>
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-content collapse show">
                    <div class="card-body card-dashboard dataTables_wrapper dt-bootstrap">
                        {{--<p class="card-text">Exporting data from a table can often be a key part of a complex
                            application. The Buttons extension for DataTables provides three plug-ins that provide
                            overlapping functionality for data export.</p>--}}

                        <div class="box-body" id="">
                            <div class="row text-center">
                                <?php
                                /*                                    $target = 200;
                                                                    foreach (\App\Models\GeneralLedger::where('transaction_date', date("Y"))->where('transaction_date',
                                                                        date("m"))->where('reversed', 0)->get() as $key) {
                                                                        $target = $target + $key->credit;
                                                                    }
                                                                    $paid_this_month = \App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->whereMonth('transaction_date', '=', date('m'))->sum('debit');
                                                                    if ($target > 0) {
                                                                        $percent = round(($paid_this_month / $target) * 100);
                                                                    } else {
                                                                        $percent = 0;
                                                                    }
                                                                    */?>
                                <div class="col-md-4">
                                    <div class="content-group">

                                        <h5 class="text-semibold no-margin">
                                            {{ number_format(\App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->where('transaction_date',date("Y-m-d"))->sum('debit'),2) }}
                                        </h5>
                                        <span class="text-muted text-size-small">{{'Today'}}</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="content-group">

                                        <h5 class="text-semibold no-margin">
                                            {{--{{ number_format(\App\Models\LoanTransaction::where('transaction_type',
                                'repayment')->where('reversed', 0)->whereBetween('date',array('date_sub(now(),INTERVAL 1 WEEK)','now()'))->sum('credit'),2) }}--}}

                                            {{ number_format(\App\Models\GeneralLedger::whereHas('student')->where('reversed', 0)->where('account_id', '!=', 11)->whereBetween('transaction_date', [Carbon\Carbon::now()->subWeek()->startOfWeek(), Carbon\Carbon::now()->subWeek()->endOfWeek()])->sum('debit'),2) }}
                                        </h5>
                                        <span class="text-muted text-size-small">{{'Last Week'}}</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="content-group">

                                        <h5 class="text-semibold no-margin">{{ number_format($paid_this_month,2) }}</h5>
                                        <span class="text-muted text-size-small">{{'This Month'}}</span>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <h5 class=" text-semibold">{{'Monthly Target'}}</h5>
                                    </div>
                                    <div class="progress" data-toggle="tooltip"
                                         title="{{ trans_choice('general.target',1) }} : {{number_format($target,2)}}">

                                        <div class="progress-bar progress-bar-success progress-bar-striped active"
                                             style="width: {{$percent}}%">
                                            <span>{{$percent}}% {{ trans_choice('general.complete',1) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h3 class="text-center">{{'Fees Payment Overview'}}</h3>
                                    <div id="collection_statistics_graph" style="height: 300px;"></div>
                                </div>
                            </div>

                        </div>



                    </div>
                </div>

            </div>
        </div>




        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 20 Debtors</h4>
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-content collapse show">
                    <div class="card-body card-dashboard dataTables_wrapper dt-bootstrap">
                        {{--<p class="card-text">Exporting data from a table can often be a key part of a complex
                            application. The Buttons extension for DataTables provides three plug-ins that provide
                            overlapping functionality for data export.</p>--}}

                        {{-- @include('partials.studentFilter')--}}


                        <div id="result_container" class="row">
                            <div class="table-responsive">
                                @include('partials.topStudentDebtors')
                            </div>
                        </div>



                    </div>
                </div>

            </div>
        </div>

    </div>
</section>


{{--<section id="data">
    <div class="row">





        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    --}}{{--<h4 class="card-title">{{ $title }}</h4>--}}{{--
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-content collapse show">
                    <div class="card-body card-dashboard dataTables_wrapper dt-bootstrap">
                        --}}{{--<p class="card-text">Exporting data from a table can often be a key part of a complex
                            application. The Buttons extension for DataTables provides three plug-ins that provide
                            overlapping functionality for data export.</p>--}}{{--

                        <div id="loans_status_graph" style="height: 300px;"></div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</section>--}}

