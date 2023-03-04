@extends('layouts.frontend')
@section('content')
    <h1>{!! $title !!}</h1>
    <div class="row">
        <div class="col-md-12">
            @if(Session::has('success'))
                <div class="alert alert-success">
                    <span>{!! session('success') !!}</span>
                </div>
            @endif
            @if(Session::has('error'))
                <div class="alert alert-danger">
                    <span>{!! session('error') !!}</span>
                </div>
            @endif
            {!! Form::open(array('url' => url('contact'), 'method' => 'post', 'files'=> true)) !!}
            <div class="control-group form-group">
                <div class="controls">
                    {!! Form::label('full_name', trans('frontend.full_name'), array('class' => 'control-label')) !!}
                    {!! Form::text('name', null, array('class' => 'form-control', 'required'=>'true')) !!}
                    <p class="help-block"></p>
                </div>
            </div>
            <div class="control-group form-group">
                <div class="controls">
                    {!! Form::label('email_address', trans('frontend.email_address'), array('class' => 'control-label')) !!}
                    {!! Form::text('email', null, array('class' => 'form-control', 'required'=>'true', 'type'=> 'email')) !!}
                </div>
            </div>
            <div class="control-group form-group">
                <div class="controls">
                    {!! Form::label('message', trans('frontend.message'), array('class' => 'control-label')) !!}
                    <textarea rows="10" cols="100" class="form-control" name="message" id="message" maxlength="999" style="resize:none"></textarea>
                </div>
            </div>
            <div class="control-group form-group">
                <div class="controls">
                    {!! captcha_img('flat') !!}
                    {!! Form::text('captcha', null, array('class' => 'form-control', 'required'=>'true', 'id'=> 'captcha')) !!}
                </div>
            </div>
            <div id="success"></div>
            <button type="submit" class="btn btn-success btn-sm">{!! trans('frontend.send_message') !!}</button>
            {!! Form::close() !!}
        </div>
    </div>
@stop