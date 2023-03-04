<div class="form-group required {{ $errors->has('email_driver') ? 'has-error' : '' }}">
    {!! Form::label('email_driver', trans('settings.email_driver'), array('class' => 'control-label required')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('email_driver', 'mail',(Settings::get('email_driver')=='mail')?true:false,array('id'=>'mail','class' => 'email_driver'))  !!}
                {!! Form::label('true', 'MAIL')  !!}
            </div>
            <div class="radio">
                {!! Form::radio('email_driver', 'smtp', (Settings::get('email_driver')=='smtp')?true:false,array('id'=>'smtp','class' => 'email_driver'))  !!}
                {!! Form::label('false', 'SMTP') !!}
            </div>
        </div>
        <span class="help-block">{{ $errors->first('email_driver', ':message') }}</span>
    </div>
</div>
<div class="form-group smtp required {{ $errors->has('email_host') ? 'has-error' : '' }}">
    {!! Form::label('email_host', trans('settings.email_host'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','email_host', old('email_host', Settings::get('email_host')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('email_host', ':message') }}</span>
    </div>
</div>
<div class="form-group smtp required {{ $errors->has('email_port') ? 'has-error' : '' }}">
    {!! Form::label('email_port', trans('settings.email_port'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','email_port', old('email_port', Settings::get('email_port')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('email_port', ':message') }}</span>
    </div>
</div>
<div class="form-group smtp required {{ $errors->has('email_username') ? 'has-error' : '' }}">
    {!! Form::label('email_username', trans('settings.email_username'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','email_username', old('email_username', Settings::get('email_username')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('email_username', ':message') }}</span>
    </div>
</div>
<div class="form-group smtp required {{ $errors->has('email_password') ? 'has-error' : '' }}">
    {!! Form::label('email_password', trans('settings.email_password'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','email_password', old('email_password', Settings::get('email_password')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('email_password', ':message') }}</span>
    </div>
</div>
<div class="form-group smtp required {{ $errors->has('encryption') ? 'has-error' : '' }}">
    {!! Form::label('encryption', trans('settings.encryption'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','encryption', old('encryption', Settings::get('encryption','tls')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('encryption', ':message') }}</span>
    </div>
</div>