@extends('layouts.secure')
@section('content')
    <section class="dashboard">
        <form action = '{{  url('project/'. $project->id. '/approve' ) }}' method="GET">
            <div class="_program-wrap"><h1>Tackling Social Development Concerns in Ghana Program</h1></div>
            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>{{$project->company->title. ' - '.$project->id }}</h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="content">
                        <div class="uploads"><h3>Narrative Report</h3>

                            <a href="{{url('uploads/foundation/'.$project->header_report)}}" target="_blank"> Narrative Report <i class="fa fa-download"></i></a>
                            <a href="{{url('uploads/foundation/'.$project->financial_report)}}">Financial Report <i class="fa fa-download"></i></a>
                            <a href="{{url('uploads/foundation/'.$project->nq_budget)}}">Next Quarter Budget <i class="fa fa-download"></i></a>
                            <p><span><i class="fa fa-user"></i>Uploader: {{@$project->uploadByname}}</span></p>
                            <p><span><i class="fa fa-calendar"></i>Submission Date: {{@$project->created_at}}</span></p>


                        </div>
                    </div>
                </div>
                <div class="col-1-2">
                    <div class="content">
                        <div class="uploads"><h3>MEL Template</h3>

                            @if(@$project->mel_template)
                                <a href="{{url('uploads/foundation/'.$project->mel_template)}}" target="_blank"> MEL Template <i class="fa fa-download"></i></a>
                            @endif
                            @if(@$project->nq_work_plan)
                                <a href="{{url('uploads/foundation/'.$project->nq_work_plan)}}" target="_blank"> Next Quarter Work Plan <i class="fa fa-download"></i></a>
                            @endif
                            @if(@$project->human_interest)
                                <a href="{{url('uploads/foundation/'.$project->human_interest)}}" target="_blank"> Human Interest Areas/Impact Submission <i class="fa fa-download"></i></a>
                            @endif


                        </div>


                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="publication"><h3>Publication</h3>

                    <div class="col-1-2">
                        <div class="content">
                            <p><strong>Article Headlines</strong></p>
                            <a href="{{@$project->article_url}}" target="_blank">{{@$project->article_title}}</a>
<!--                            <a href="">KGL Foundation supports Achievers Ghana on International Day For A Girl Child</a>-->
                        </div>
                    </div>
                    <div class="col-1-2">
                        <div class="content">
                          @if(@$project->image)
                                <div class="bi-card-image">
                                    <img class="jKR4Ec" src="{{ asset('uploads/foundation/'.$project->image) }}">
                                </div>
                          @endif
                        </div>
                    </div>

                </div>
            </div>
@if($user->inRole('super_admin'))
            <div class="grid grid-pad">
                @if($project->approval == 0)
                <div class="col-1-2">
                    <div class="content">
                        <div class="form-group file-area">
                            <button type="submit" name="byname" id="byname" required="required" placeholder="Article URL" />Approve</button>
                        </div>
                    </div>
                </div>
                @endif


                <div class="col-1-2">
                    <div class="content">
                        <div class="form-group file-area">
                            <a href="{{  url('project/'. $project->id. '/reject' ) }}">
                            <button class="reject_btn" type="button">Reject</button>
                            </a>
                        </div>
                    </div>
                    @if($project->approval == 0)
                    <div class="content">
                        <div class="form-group file-area">
                            <a href="{{  url('project/'. $project->id. '/delete' ) }}">
                            <button class="reject_btn" type="button">Delete</button>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
    @endif
        </form></section>
@stop
@section('styles')


@stop

@section('scripts')


@stop



