<div class="form-group required {{ $errors->has('late_return_book_make_invoice') ? 'has-error' : '' }}">
    {!! Form::label('make_invoice', trans('settings.late_return_book_make_invoice'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('late_return_book_make_invoice', 'true',(Settings::get('late_return_book_make_invoice')=='true')?true:false,array('class' => 'icheck'))  !!}
                {!! Form::label('true', trans('settings.true'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('late_return_book_make_invoice', 'false', (Settings::get('late_return_book_make_invoice')=='false')?true:false,array('class' => 'icheck'))  !!}
                {!! Form::label('false', trans('settings.false')) !!}
            </div>
        </div>
    </div>
</div>
<div class="form-group required {{ $errors->has('price_by_date_late') ? 'has-error' : '' }}">
    {!! Form::label('price_by_date_late', trans('settings.price_by_date_late'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('price_by_date_late', old('price_by_date_late', Settings::get('price_by_date_late')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('price_by_date_late', ':message') }}</span>
    </div>
</div>