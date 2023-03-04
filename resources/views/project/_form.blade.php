@extends('layouts.secure')
@section('content')
    <section class="dashboard">
        <form action="{{url('project')}}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="_program-wrap"><h1>Tackling Social Development Concerns in Ghana Program</h1></div>
            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>Client Dashboard</h3>
                        <p><span>Client ID: {{$currentEmployee->sID}}</span></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="content">
                        <!--<div class="submit_calendar_box">
                        <h3>Submission Date</h3>
                        <input type="text" name="">
                        <div class="form-container">
                <form>
                   <i class="fa fa-calendar"></i>
                   <input type="text" placeholder="Pick a Date" class="date-input">
                </form>
             </div></div>-->

                        <div class="uploads"><h3>NARRATIVE REPORT </h3>

                            <div class="form-group file-area">
                                <label for="images">Narrative Report</label>
                                <input type="file" name="header_report" id="header_report" required="required" />

                                <div class="file-dummy">
                                    <div class="success">Great, your files are selected.</div>
                                    <div class="default">Please select some files <i class="fa fa-upload"></i></div>
                                </div>

                            </div>
                            <span class="help-block">{{ $errors->first('header_report', ':message') }}</span>

                            <div class="form-group file-area">
                                <label for="images">Financial Report</label>
                                <input type="file" name="financial_report" id="financial_report" required="required" />

                                <div class="file-dummy">
                                    <div class="success">Great, your files are selected.</div>
                                    <div class="default">Please select some files <i class="fa fa-upload"></i></div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('financial_report', ':message') }}</span>

                            <div class="form-group file-area">
                                <label for="images">Next Quarter Budget</label>
                                <input type="file" name="nq_budget" id="nq_budget" required="required" />

                                <div class="file-dummy">
                                    <div class="success">Great, your files are selected.</div>
                                    <div class="default">Please select some files <i class="fa fa-upload"></i></div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('nq_budget', ':message') }}</span>


                            <div class="form-group file-area">
                                <label for="images">Upload By</label>
                                <input type="text" name="uploadByname" id="uploadByname" required="required" placeholder="Enter Full Name (Person upload the documents)" />

                            </div>
                            <span class="help-block">{{ $errors->first('uploadByname', ':message') }}</span>


                        </div>
                    </div>
                </div>
                <div class="col-1-2">
                    <div class="content">
                        <div class="uploads"><h3>MEL Template</h3>




                            <div class="form-group file-area">
                                <label for="images">MEL Template</label>
                                <input type="file" name="mel_template" id="mel_template" required="required" />

                                <div class="file-dummy">
                                    <div class="success">Great, your files are selected.</div>
                                    <div class="default">Please select some files  <i class="fa fa-upload"></i></div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('mel_template', ':message') }}</span>

                            <div class="form-group file-area">
                                <label for="images">Next Quarter Work Plan</label>
                                <input type="file" name="nq_work_plan" id="nq_work_plan" required="required" />

                                <div class="file-dummy">
                                    <div class="success">Great, your files are selected.</div>
                                    <div class="default">Please select some files <i class="fa fa-upload"></i></div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('nq_work_plan', ':message') }}</span>


                            <div class="form-group file-area">
                                <label for="images">Human Interest Areas/Impact Submission</label>
                                <input type="file" name="human_interest" id="human_interest" required="required" />

                                <div class="file-dummy">
                                    <div class="success">Great, your files are selected.</div>
                                    <div class="default">Please select some files <i class="fa fa-upload"></i></div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('human_interest', ':message') }}</span>



                        </div>


                    </div>
                </div>
            </div>

            <div class="grid grid-pad">
                <div class="publication"><h3>Publication</h3>

                    <div class="col-1-2">
                        <div class="content">
                            <div class="form-group file-area">
                                <input type="text" name="article_title" id="article_title" required="required" placeholder="Article Title" />

                            </div>
                            <span class="help-block">{{ $errors->first('article_title', ':message') }}</span>
                            <div class="form-group file-area">
                                <input type="text" name="article_url" id="article_url" required="required" placeholder="Article URL" />

                            </div>
                            <span class="help-block">{{ $errors->first('article_url', ':message') }}</span>
                        </div>
                    </div>
                    <div class="col-1-2">
                        <div class="content">
                            <ul class="media_upload"><li><div class="form-group file-area">
                                        <input type="file" name="file_file" id="file_file" required="required" placeholder="Picture" />

                                        <div class="file-dummy">
                                            <div class="success">Great, your files are selected.</div>
                                            <div class="default">Please select image <i class="fa fa-upload"></i></div>
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('file_file', ':message') }}</span>

                                </li>

<!--                                <li>
                                    <div class="form-group file-area">
                                        <input type="file" name="video" id="video" required="required" placeholder="Video" />

                                        <div class="file-dummy">
                                            <div class="success">Great, your files are selected.</div>
                                            <div class="default">Please select some files <i class="fa fa-upload"></i></div>
                                        </div>
                                    </div>
                                    <span class="help-block">{{ $errors->first('video', ':message') }}</span>
                                </li>-->
                            </ul>
                        </div>

                    </div>
                </div>

            </div>

            <div class="grid grid-pad">
                <div class="col-1-2">
                    <div class="content">
                        <div class="form-group file-area">

                            <div class="file-dummy checkbox">
                                Please check the box (Notify Me)
                                <input type="checkbox" name="notification" id="notification" required="required"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-1-2">
                    <div class="content">
                        <div class="form-group file-area">
                            <button type="submit" name="byname" id="byname" required="required" placeholder="Article URL" />Submit Reports</button>
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
