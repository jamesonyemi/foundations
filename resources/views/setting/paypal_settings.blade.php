<div class="form-group required {{ $errors->has('paypal_testmode') ? 'has-error' : '' }}">
    {!! Form::label('paypal_testmode', trans('settings.paypal_testmode'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('paypal_testmode', 'true',(Settings::get('paypal_testmode')=='true')?true:false,array('class' => 'icheck'))  !!}
                {!! Form::label('true', trans('settings.true'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('paypal_testmode', 'false', (Settings::get('paypal_testmode')=='false')?true:false,array('class' => 'icheck'))  !!}
                {!! Form::label('false', trans('settings.false')) !!}
            </div>
        </div>
        <span class="help-block">{{ $errors->first('paypal_testmode', ':message') }}</span>
    </div>
</div>

<div class="form-group required {{ $errors->has('paypal_username') ? 'has-error' : '' }}">
    {!! Form::label('paypal_username', trans('settings.paypal_username'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','paypal_username', old('paypal_username', Settings::get('paypal_username')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('paypal_username', ':message') }}</span>
    </div>
</div>

<div class="form-group required {{ $errors->has('paypal_password') ? 'has-error' : '' }}">
    {!! Form::label('paypal_password', trans('settings.paypal_password'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('paypal_password', old('paypal_password', Settings::get('paypal_password')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('paypal_password', ':message') }}</span>
    </div>
</div>

<div class="form-group required {{ $errors->has('paypal_signature') ? 'has-error' : '' }}">
    {!! Form::label('paypal_signature', trans('settings.paypal_signature'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('paypal_signature', old('paypal_signature', Settings::get('paypal_signature')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('paypal_signature', ':message') }}</span>
    </div>
</div>