@extends('layouts.secure')
@section('content')
    {{--<h1>{{trans('dashboard.calendar')}}</h1>
    <div id="calendar"></div>--}}

    <section id="data">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="btn btn-lg btn-block font-medium-1 btn-outline-success mb-1 block-element">WELCOME <STRONG>{{ Str::limit($user->full_name, 25) }}</STRONG></div>
                        @if($user->applicant[0]->applied != 1)

                            <div class="controls">
                                <a href="{{ url('/applicant_personal/' . session('current_applicant')  . '/edit' ) }}" class="btn btn-success col-md-12" >
                                    <i class="fa fa-pencil-square-o "></i>  CLICK HERE TO CONTINUE YOUR APPLICATION</a>
                            </div>

                        @endif
                        @if($user->applicant[0]->applied == 1)

                            <div class="controls">
                                <a href="{{ url('/' ) }}" class="btn btn-success col-md-12" >
                                    <i class="fa fa-pencil-square-o "></i>  YOUR APPLICATION IS BEING PROCESSED AND THE SCHOOL WILL GET BACK TO YOU SHORTLY</a>
                            </div>

                            <div class="controls">
                                <a href="{{ url('/applicant_personal/' . session('current_applicant')  . '/edit' ) }}" class="btn btn-success col-md-12" >
                                    <i class="fa fa-pencil-square-o "></i>  YOU CAN CLICK HERE TO MAKE CHANGES TO YOUR INFORMATION PROVIDED</a>
                            </div>
                        @endif

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



                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel panel-success">
                                            <div class="card-header flex-wrap border-0 pt-6 pb-0">
                                                {{--<h3 class="panel-title">
                                                    WELCOME <STRONG>{{ str_limit($user->full_name, 25) }}</STRONG>
                                                </h3>--}}
                                            </div>
                                            <div  class="panel-body">
                                                <h4>
                                                    <p>Congratulations for Choosing DOMINION UNIVERSITY COLLEGE.
                                                    </p>
                                                </h4>

                                                <p><b>Dominion University College’s</b> mission is to help our students master the competencies that drive career success
                                                    &nbsp; With programmes that are comprehensive, current and supported by qualified faculty and staff,
                                                    we are committed to keeping pace with the needs of an ever-changing market place.
                                                    In Dominion University College (DUC), all Degree,
                                                    Theology and HND programs share the following common goals:</p>
                                                <ul>
                                                    <li>To enable students acquire a body of knowledge in a specific discipline.</li>
                                                    <li>To think critically.</li>
                                                    <li>To improve student’s abilities to make significant contributions to the missions of their employers.</li>
                                                    <li>To ensure students use their knowledge to improve the functioning of the communities in which they live and work.</li>
                                                    <li>To enhance student’s personal satisfaction.</li>
                                                    <li>To provide a pathway for students to continue the pursuit of additional life-long learning experiences.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="col-xl-6 col-md-12">
                                        <div class="card">

                                            <div class="card-content">

                                                <div id="carousel-example" class="carousel slide" data-ride="carousel">
                                                    <ol class="carousel-indicators">
                                                        <li data-target="#carousel-example" data-slide-to="0" class=""></li>
                                                        <li data-target="#carousel-example" data-slide-to="1" class=""></li>
                                                        <li data-target="#carousel-example" data-slide-to="2" class="active"></li>
                                                        <li data-target="#carousel-example" data-slide-to="3" class=""></li>
                                                    </ol>
                                                    <div class="carousel-inner" role="listbox">
                                                        <div class="carousel-item">
                                                            <img src="../../../app-assets/images/carousel/08.jpg" class="d-block w-100" alt="First slide">
                                                        </div>

                                                        <div class="carousel-item">
                                                            <img src="../../../app-assets/images/carousel/02.jpg" class="d-block w-100" alt="Fourth slide">
                                                        </div>
                                                        <div class="carousel-item">
                                                            <img src="../../../app-assets/images/carousel/03.jpg" class="d-block w-100" alt="Second slide">
                                                        </div>
                                                        <div class="carousel-item active">
                                                            <img src="../../../app-assets/images/carousel/01.jpg" class="d-block w-100" alt="Third slide">
                                                        </div>
                                                    </div>
                                                    <a class="carousel-control-prev" href="#carousel-example" role="button" data-slide="prev">
                                                        <span class="la la-angle-left" aria-hidden="true"></span>
                                                        <span class="sr-only">Previous</span>
                                                    </a>
                                                    <a class="carousel-control-next" href="#carousel-example" role="button" data-slide="next">
                                                        <span class="la la-angle-right icon-next" aria-hidden="true"></span>
                                                        <span class="sr-only">Next</span>
                                                    </a>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                </div>

                          @if($user->applicant[0]->validated != 1)

                                {!! Form::open(array('url' => url('./applicant/'.$user->applicant[0]->id.'/makeValidate'), 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}

                                    <div class="row">
                                        <div class="col-md-12 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                            <div class="btn btn-lg btn-block font-medium-1 btn-success mb-1 block-element">Your Application pin Number was sent to your email address provided, Kindly enter your pin below</div>

                                            <div class="controls">
                                                {!! Form::text('pin', null , array('class' => 'form-control')) !!}
                                                <span class="help-block">{{ $errors->first('pin', ':message') }}</span>
                                            </div>
                                        </div>

                                    </div>

                                <div class="form-group">
                                    <div class="controls">
                                        {{--<a href="{{ url($type) }}" class="btn btn-primary btn-sm">{{trans('table.cancel')}}</a>--}}
                                        <button class="btn btn-primary">Validate your pin</button>
                                    </div>
                                </div>


                            {!! Form::close() !!}

                            @else

                                @if($user->applicant[0]->applied != 1)

                                <div class="controls">
                                    <a href="{{ url('/applicant_personal/' . session('current_applicant')  . '/edit' ) }}" class="btn btn-success col-md-12" >
                                        <i class="fa fa-pencil-square-o "></i>  CLICK HERE TO CONTINUE YOUR APPLICATION</a>
                                </div>

                                @elseif($user->applicant[0]->applied == 1)

                                        <div class="controls">
                                            <a href="{{ url('/' ) }}" class="btn btn-success col-md-12" >
                                                <i class="fa fa-pencil-square-o "></i>  YOUR APPLICATION IS BEING PROCESSED. WE WILL GET BACK TO YOU SHORTLY</a>
                                        </div>

                                    <div class="controls">
                                        <a href="{{ url('/applicant_personal/' . session('current_applicant')  . '/edit' ) }}" class="btn btn-success col-md-12" >
                                            <i class="fa fa-pencil-square-o "></i>  YOU CAN CLICK HERE TO MAKE CHANGES TO YOUR INFORMATION PROVIDED</a>
                                    </div>
                                @endif

                                @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>




@stop
@section('scripts')
    <script>
    $(document).ready(function() {
        $('#calendar').fullCalendar({
            "header": {
                "left": "prev,next today",
                "center": "title",
                "right": "month,agendaWeek,agendaDay"
            },
            "eventLimit": true,
            "firstDay": 1,
            "eventRender": function (event, element) {
                element.popover({
                    content: event.description,
                    template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                    title: event.title,
                    container: 'body',
                    trigger: 'click',
                    placement: 'auto'
                });
            },
            "eventSources": [
                {
                    url:"{{url('events')}}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    error: function() {
                        alert('there was an error while fetching events!');
                    }
                }
            ]
        });
    });
    </script>
    @stop
