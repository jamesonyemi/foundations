<label class="required">&nbsp;</label> <label>{{trans('settings.stripe_info')}}</label>
<div class="form-group required {{ $errors->has('stripe_secret') ? 'has-error' : '' }}">
    {!! Form::label('stripe_secret', trans('settings.stripe_publishable'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','stripe_secret', old('stripe_secret', Settings::get('stripe_secret')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('stripe_secret', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('stripe_publishable') ? 'has-error' : '' }}">
    {!! Form::label('stripe_publishable', trans('settings.stripe_secret'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','stripe_publishable', old('stripe_publishable', Settings::get('stripe_publishable')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('stripe_publishable', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('payment_plan') ? 'has-error' : '' }}">
    {!! Form::label('payment_plan', trans('settings.payment_plan'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::input('text','payment_plan', old('payment_plan', Settings::get('payment_plan')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('payment_plan', ':message') }}</span>
    </div>
</div>