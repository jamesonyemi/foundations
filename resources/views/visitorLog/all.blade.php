@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{--{{$fees->count()}}--}}
{{-- Content --}}
@section('content')
    <div class="card card-custom gutter-b">
        <div class="card-header flex-wrap border-0 pt-6 pb-0">
            <div class="card-title">
                <h3 class="card-label">{{ $title }}
                    {{--<span class="d-block text-muted pt-2 font-size-sm">row selection and group actions</span>--}}
                </h3>
            </div>
            <div class="card-toolbar">

                <!--begin::Button-->

                <a href="#" class="btn btn-primary font-weight-bolder"  onclick="showAdd()">
                    <i class="la la-plus" ></i>New Visitor Record
                </a>


                <!--end::Button-->
            </div>
        </div>
        <div class="card-body">
            <!--begin: Datatable-->
            <div class="row col-12">

                <div class="form-group col-3  {{ $errors->has('employee_id') ? 'has-error' : '' }}">
                    {!! Form::label('employee_id', 'Employee', array('class' => 'control-label required')) !!}
                    <div class="controls">
                        {!! Form::select('employee_id', $employees, null, array('id'=>'employee_id', 'class' => 'form-control select2', 'disabled')) !!}
                        <span class="help-block">{{ $errors->first('employee_id', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group col-3  {{ $errors->has('department_id') ? 'has-error' : '' }}">
                    {!! Form::label('department_id', trans('employee.section'), array('class' => 'control-label required')) !!}
                    <div class="controls">
                        {!! Form::select('department_id', $sections, null, array('id'=>'department_id', 'class' => 'form-control select2', 'disabled')) !!}
                        <span class="help-block">{{ $errors->first('department_id', ':message') }}</span>
                    </div>
                </div>
                <div class="form-group col-2  {{ $errors->has('month') ? 'has-error' : '' }}">
                    {!! Form::label('month', 'Month', array('class' => 'control-label required', )) !!}
                    <div class="controls">
                        <select class="form-control select2 floating" name="month" disabled>
                            <option value="1"
                                    @if(strtolower(date('F'))=='january')selected='selected'@endif >January
                            </option>
                            <option value="2"
                                    @if(strtolower(date('F'))=='february')selected='selected'@endif>February
                            </option>
                            <option value="3"
                                    @if(strtolower(date('F'))=='march')selected='selected'@endif>March
                            </option>
                            <option value="4"
                                    @if(strtolower(date('F'))=='april')selected='selected'@endif>April
                            </option>
                            <option value="5" @if(strtolower(date('F'))=='may')selected='selected'@endif>
                                May
                            </option>
                            <option value="6"
                                    @if(strtolower(date('F'))=='june')selected='selected'@endif>June
                            </option>
                            <option value="7"
                                    @if(strtolower(date('F'))=='july')selected='selected'@endif>July
                            </option>
                            <option value="8"
                                    @if(strtolower(date('F'))=='august')selected='selected'@endif>August
                            </option>
                            <option value="9"
                                    @if(strtolower(date('F'))=='september')selected='selected'@endif>
                                September
                            </option>
                            <option value="10"
                                    @if(strtolower(date('F'))=='october')selected='selected'@endif>October
                            </option>
                            <option value="11"
                                    @if(strtolower(date('F'))=='november')selected='selected'@endif>November
                            </option>
                            <option value="12"
                                    @if(strtolower(date('F'))=='december')selected='selected'@endif>December
                            </option>
                        </select>
                        <span class="help-block">{{ $errors->first('month', ':message') }}</span>
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-group form-focus">
                        {!! Form::label('ddate', 'Date', array('class' => 'control-label required')) !!}
                        {!! Form::text('ddate', now(), array('class' => 'form-control date', 'id' => 'ddate')) !!}
                    </div>
                </div>

                <div class="col-2">
                    <div class="form-group form-focus">
                        <label class="control-label">&nbsp;</label>
                        <a href="javascript:;" class="btn btn-success btn-block" onclick="filter(); return false;"> Search </a>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-lg-12">
                    <div class="table-responsive" id="attendance-sheet">
                    </div>
                </div>
            </div>
        </div>
    </div>


    @stop

    @section('styles')


    @stop

    @section('scripts')

    <script src="{{ asset('js/amchart/amcharts.js') }}"></script>
    <script src="{{ asset('js/amchart/light.js') }}"></script>
    <script src="{{ asset('js/amchart/serial.js') }}"></script>
    <script src="{{ asset('js/amchart/pie.js') }}"></script>
    <script src="{{ asset('js/amchart/radar.js') }}"></script>

    <script>
        "use strict";
        $('#ddate').datepicker({
            format: 'yyyy-mm-dd',
        })

    </script>

    <script >

        function showAdd() {
            $('#add_modal').modal('show');
            $("body").addClass("modal-open");

            var get_url = "{{ url($type.'/create') }}";

            $("#add_modal_body").html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

            $.ajax({
                type: "GET",
                url: get_url,
                data: {}
            }).done(function (response) {
                $("#add_modal_body").html(response);
            });
        }


        function filter() {
            var get_url = "{{ url($type.'/filter') }}";
            var data = {
                employee_id: $("select[name='employee_id']").val(),
                month: $("select[name='month']").val(),
                year: $("select[name='year']").val(),
                ddate: $('#ddate').val(),
                department_id: $("select[name='department_id']").val(),
                _token: '{{ csrf_token() }}'
            };

            $('#attendance-sheet').html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

            $.ajax({
                type: "post",
                url: get_url,
                container: '#attendance-sheet',
                data: data
            }).done(function (response) {
                $('#attendance-sheet').html(response);
            });
        }

        function chartFilter() {
            var get_url = "{{ url($type.'/chartFilter') }}";
            var data = {
                date: $('#ddate').val(),
                department_id: $("select[name='department_id']").val(),
                _token: '{{ csrf_token() }}'
            };

            $('#chartFilter').html('<div class="text-center">{!!  HTML::image('assets/loader.gif') !!} Loading.....</div>');

            $.ajax({
                type: "post",
                url: get_url,
                container: '#attendance-sheet',
                data: data
            }).done(function (response) {
                $('#chartFilter').html(response);
            });
        }




        jQuery(document).ready(function() {
            KTamChartsChartsDemo.init();
            filter();
        });


    </script>

@stop
