@extends('layouts.secure')
@section('content')
    <section class="dashboard">
        <form action method="post">

            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>FSC Properties</h3>
                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="content">


                        <div class="uploads"><h3>Header Report</h3>

                            <a href="">Document Upload <i class="fa fa-download"></i></a>
                            <a href="">Financial Report <i class="fa fa-download"></i></a>
                            <a href="">Next Quarter Budget <i class="fa fa-download"></i></a>
                            <p><span><i class="fa fa-user"></i>Uploader: James Marcon</span></p>
                            <p><span><i class="fa fa-calendar"></i>Submission Date: 25-09-2022</span></p>


                        </div>
                    </div>
                </div>
                <div class="col-1-2">
                    <div class="content">
                        <div class="uploads"><h3>MEL Template</h3>

                            <a href="">Document Upload <i class="fa fa-download"></i></a>
                            <a href="">Next Quarter Work Plan <i class="fa fa-download"></i></a>
                            <a href="">Human Interest Areas/Impact Submission <i class="fa fa-download"></i></a>

                        </div>


                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="publication"><h3>Publication</h3>

                    <div class="col-1-2">
                        <div class="content">
                            <p><strong>Article Headlines</strong></p>
                            <a href="">KGL Foundation & GFA launch 2nd edition of KGL Foundation U-17 Champions League</a>
                            <a href="">KGL Foundation supports Achievers Ghana on International Day For A Girl Child
                            </a>
                        </div>
                    </div>

                </div></div>

            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="content">
                        <div class="form-group file-area">
                            <button type="submit" name="byname" id="byname" required="required" placeholder="Article URL" />Approve</button>
                        </div>
                    </div>
                </div>
                <div class="col-1-2">
                    <div class="content">
                        <div class="form-group file-area">
                            <button class="reject_btn" type="submit" name="byname" id="byname" required="required" placeholder="Article URL" />Reject</button>
                        </div>
                    </div>
                </div>
            </div>
        </form></section>


@stop
@section('styles')

@stop

@section('scripts')

@stop

