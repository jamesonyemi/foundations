@extends('layouts.verify')
@section('content')
    {!! Form::open(array('url' =>  'verify', 'method' => 'post')) !!}
    <div class="step-content" style="padding-left: 15px; padding-top: 15px; padding-right: 15px">
        <h3>{{trans('verify.verification')}}</h3>
        <hr>
        <input type="hidden" id="envato" name="envato" value="yes">
        <!--div class="form-group">
            {!! Form::label('envato', trans('install.envato'), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::select('envato', ['yes'=>trans('install.yes'),'no'=>trans('install.no')], old('envato'),
                array('id'=>'envato', 'class' => 'form-control')) !!}
            </div>
        </div-->
        <div id="yes">
            <div class="form-group {{ $errors->has('purchase_code') ? 'has-error' : '' }}">
                <label for="purchase_code">{{trans('install.purchase_code')}}</label>
                <input type="text" class="form-control input-sm" id="purchase_code" name="purchase_code" value="{{ old('purchase_code') }}">
            </div>
            <div class="form-group {{ $errors->has('envato_username') ? 'has-error' : '' }}">
                <label for="envato_username">{{trans('install.envato_username')}}</label>
                <input type="text" class="form-control input-sm" id="envato_username" name="envato_username" value="{{ old('envato_username') }}">
            </div>
            <div class="form-group {{ $errors->has('envato_email') ? 'has-error' : '' }}">
                <label for="envato_email">{{trans('install.envato_email')}}</label>
                <input type="text" class="form-control input-sm" id="envato_email" name="envato_email" value="{{ old('envato_email') }}">
            </div>
        </div>
        <div id="no">
            <div class="form-group {{ $errors->has('secret') ? 'has-error' : '' }}">
                <label for="secret">{{trans('install.secret')}}</label>
                <input type="text" class="form-control input-sm" id="secret" name="secret" value="{{ old('secret') }}">
            </div>
            <div class="form-group {{ $errors->has('license') ? 'has-error' : '' }}">
                <label for="license">{{trans('install.license')}}</label>
                <input type="text" class="form-control input-sm" id="license" name="license" value="{{ old('license') }}">
            </div>
            <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">{{trans('install.email')}}</label>
                <input type="text" class="form-control input-sm" id="email" name="email" value="{{ old('email') }}">
            </div>
        </div>
        <button class="btn btn-success btn-sm pull-right">
            {{trans('verify.verify')}}
            <i class="fa fa-check-square" style="margin-left: 6px"></i>
        </button>
        <div class="clearfix"></div>
    </div>
    {!! Form::close() !!}
@stop
@section('scripts')
    <script>
        $(document).ready(function(){
            if($('#envato').val() == 'no'){
                $('#yes').hide();
            }else{
                $('#no').hide();
            }
            $('#envato').on('change', function() {
                if(this.value == 'no'){
                    $('#no').show();
                    $('#yes').hide();
                }else{
                    $('#no').hide();
                    $('#yes').show();
                }
            })
        });
    </script>
@stop