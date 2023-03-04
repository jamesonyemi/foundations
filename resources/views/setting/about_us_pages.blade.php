<div class="form-group required {{ $errors->has('about_school_page') ? 'has-error' : '' }}">
    {!! Form::label('about_school_page', trans('settings.about_school_page'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('about_school_page', 'no',(Settings::get('about_school_page')=='no')?true:false,array('id'=>'about_school_page','class' => 'about_school_page'))  !!}
                {!! Form::label('true', trans('settings.no'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('about_school_page', 'yes', (Settings::get('about_school_page')=='yes')?true:false,array('id'=>'about_school_page','class' => 'about_school_page'))  !!}
                {!! Form::label('false', trans('settings.yes')) !!}
            </div>
        </div>
        <span class="help-block">{{ $errors->first('about_school_page', ':message') }}</span>
    </div>
</div>
<div class="form-group about_school_page_area required {{ $errors->has('about_school_page_title') ? 'has-error' : '' }}">
    {!! Form::label('about_school_page_title', trans('settings.about_school_page_title'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('about_school_page_title', old('about_school_page_title', Settings::get('about_school_page_title')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('about_school_page_title', ':message') }}</span>
    </div>
</div>
<div class="form-group about_school_page_area required {{ $errors->has('about_school_page_introduction') ? 'has-error' : '' }}">
    {!! Form::label('about_school_page_introduction', trans('settings.about_school_page_introduction'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::textarea('about_school_page_introduction', old('about_school_page_introduction', Settings::get('about_school_page_introduction')), array('class' => 'form-control no_wysiwyg')) !!}
        <span class="help-block">{{ $errors->first('about_school_page_introduction', ':message') }}</span>
    </div>
</div>
<div class="form-group {{ $errors->has('about_teachers_page') ? 'has-error' : '' }}">
    {!! Form::label('about_teachers_page', trans('settings.about_teachers_page'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('about_teachers_page', 'no',(Settings::get('about_teachers_page')=='no')?true:false,array('id'=>'about_teachers_page','class' => 'about_teachers_page'))  !!}
                {!! Form::label('true', trans('settings.no'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('about_teachers_page', 'yes', (Settings::get('about_teachers_page')=='yes')?true:false,array('id'=>'about_teachers_page','class' => 'about_teachers_page'))  !!}
                {!! Form::label('false', trans('settings.yes')) !!}
            </div>
        </div>
        <span class="help-block">{{ $errors->first('about_teachers_page', ':message') }}</span>
    </div>
</div>
<div class="form-group about_teachers_page_area required {{ $errors->has('about_teachers_page_title') ? 'has-error' : '' }}">
    {!! Form::label('about_teachers_page_title', trans('settings.about_teachers_page_title'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('about_teachers_page_title', old('about_teachers_page_title', Settings::get('about_teachers_page_title')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('about_teachers_page_title', ':message') }}</span>
    </div>
</div>
<div class="form-group about_teachers_page_area required {{ $errors->has('about_teachers_page_introduction') ? 'has-error' : '' }}">
    {!! Form::label('about_teachers_page_introduction', trans('settings.about_teachers_page_introduction'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::textarea('about_teachers_page_introduction', old('about_teachers_page_introduction', Settings::get('about_teachers_page_introduction')), array('class' => 'form-control no_wysiwyg')) !!}
        <span class="help-block">{{ $errors->first('about_teachers_page_introduction', ':message') }}</span>
    </div>
</div>