<div class="form-group {{ $errors->has('section_id') ? 'has-error' : '' }}">
    {!! Form::label('section_id', trans('student.section'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('section_id',  $sections2,  null, array('id'=>'section_id', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('section_id', ':message') }}</span>
    </div>
</div>

<div class="form-group {{ $errors->has('company_year_id') ? 'has-error' : '' }}">
    {!! Form::label('company_year_id', trans('student.school_year'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('company_year_id', $schoolYears2, null, array('id'=>'company_year_id', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('company_year_id', ':message') }}</span>
    </div>
</div>


<div class="form-group {{ $errors->has('semester_id') ? 'has-error' : '' }}">
    {!! Form::label('semester_id', trans('student.semester'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('semester_id', $semesters2, null, array('id'=>'semester_id', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('semester_id', ':message') }}</span>
    </div>
</div>


<div class="form-group {{ $errors->has('direction_id') ? 'has-error' : '' }}">
    {!! Form::label('direction_id', trans('student.programme'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('direction_id', $directions2, null, array('id'=>'direction_id', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('direction_id', ':message') }}</span>
    </div>
</div>


<div class="form-group {{ $errors->has('level_id') ? 'has-error' : '' }}">
    {!! Form::label('level_id', trans('student.level'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('level_id', $levels2, null, array('id'=>'level_id', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('level_id', ':message') }}</span>
    </div>
</div>


<div class="form-group {{ $errors->has('session_id') ? 'has-error' : '' }}">
    {!! Form::label('session_id', trans('student.session'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('session_id', $sessions2, @$registration->student->session_id, array('id'=>'session_id', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('session_id', ':message') }}</span>
    </div>
</div>

<div class="form-group {{ $errors->has('student_id') ? 'has-error' : '' }}">
    {!! Form::label('student_id', trans('account.student'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::select('student_id[]', $students, null, array('id'=>'student_id', 'multiple'=>true, 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('student_id', ':message') }}</span>
    </div>
</div>
