@extends('layouts.secure')

{{-- Web site Title --}}
@section('title')
	{{ $title }}
@stop

{{-- Content --}}
@section('content')
	{!! Form::open(array('url' => url($type), 'method' => 'post', 'target'=> '_blank', 'class' => 'bf', 'files'=> true)) !!}

	<div class="form-group {{ $errors->has('report_type') ? 'has-error' : '' }}">
		{!! Form::label('report_type', trans('report.report_type'), array('class' => 'control-label')) !!}
		<div class="controls">
			{!! Form::select('report_type', $report_type, null, array('id'=>'report_type', 'class' => 'form-control select2')) !!}
			<span class="help-block">{{ $errors->first('report_type', ':message') }}</span>
		</div>
	</div>
	<div id="exams" class="form-group {{ $errors->has('exam_id') ? 'has-error' : '' }}">
		{!! Form::label('exam_id', trans('report.exam'), array('class' => 'control-label')) !!}
		<div class="controls">
			{!! Form::select('exam_id', $exams, null, array('class' => 'form-control','id'=>'exam_id select2')) !!}
			<span class="help-block">{{ $errors->first('exam_id', ':message') }}</span>
		</div>
	</div>
	<div class="form-group {{ $errors->has('student_id') ? 'has-error' : '' }}">
		{!! Form::label('student_id', trans('report.student'), array('class' => 'control-label')) !!}
		<div class="controls">
			{!! Form::select('student_id[]', $students, null, array('class' => 'form-control select2', 'multiple'=>true)) !!}
			<span class="help-block">{{ $errors->first('student_id', ':message') }}</span>
		</div>
	</div>
    <div id="start_date" class="form-group {{ $errors->has('start_date') ? 'has-error' : '' }}">
        {!! Form::label('start_date', trans('report.start_date'), array('class' => 'control-label')) !!}
        <div class="controls" style="position: relative">
            {!! Form::text('start_date', $start_date, array('class' => 'form-control date')) !!}
            <span class="help-block">{{ $errors->first('start_date', ':message') }}</span>
        </div>
    </div>
    <div id="end_date" class="form-group {{ $errors->has('end_date') ? 'has-error' : '' }}">
        {!! Form::label('end_date', trans('report.end_date'), array('class' => 'control-label')) !!}
        <div class="controls" style="position: relative">
            {!! Form::text('end_date', $end_date, array('class' => 'form-control date')) !!}
            <span class="help-block">{{ $errors->first('end_date', ':message') }}</span>
        </div>
    </div>
	<div class="form-group">
		<div class="controls">
			<button type="submit" class="btn btn-success btn-sm">{{trans('table.ok')}}</button>
		</div>
	</div>
	{!! Form::close() !!}
@stop
@section('scripts')
<script>
    $('#exams').hide();
    $('#report_type').on('change', function() {
        if($(this).val() == 'list_exam_marks'){
            $('#exams').show();
            $('#start_date').hide();
            $('#end_date').hide();
        }
        else if($(this).val() == 'list_behaviors'){
            $('#exams').hide();
            $('#start_date').hide();
            $('#end_date').hide();
        }
        else if($(this).val() == 'student_cards'){
            $('#exams').hide();
            $('#start_date').hide();
            $('#end_date').hide();
        }
        else{
            $('#exams').hide();
            $('#start_date').show();
            $('#end_date').show();
        }
    });
</script>
@stop