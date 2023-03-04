@extends('layouts.secure')
@section('content')


    <section class="dashboard">
        <form action method="post">

            <div class="grid grid-pad">
                <div class="col-1-1">
                    <div class="content dashboard-header">
                        <h3>Basic Information</h3>
                    </div>
                </div>
            </div>


            <div class="grid grid-pad">
                <div class="publication">
                    <div class="col-7-12">
                        <div class="content">
                            {!! Form::model($employee, array('url' => url('account'), 'method' => 'post', 'files'=> true)) !!}
                            <div class="auth">
                                First Name<p><input type="text" name="first_name" value="{{$user->first_name}}"></p>
                                <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                                Last Name<p><input type="text" name="last_name" value="{{$user->last_name}}"></p>
                                <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                                Telephone<p><input type="tel" name="mobile" value="{{$user->mobile}}"></p>
                                <span class="help-block">{{ $errors->first('mobile', ':message') }}</span>
                                Email Address<p><input type="tel" name="email" value="{{$user->email}}"></p>
                                <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                                {!! Form::label('user_avatar_file', trans('profile.avatar'), array('class' => 'control-label')) !!}
                                <span class="btn btn-default btn-file">

                                <input type="file" name="user_avatar_file"></span>

                                <h1>Security</h1>
                                Password
                                <p><input type="password" name="password"></p>
                                <span class="help-block">{{ $errors->first('password', ':message') }}</span>


                                Confirm Password
                                <p><input type="password" name="password_confirmation"></p>
                                <span class="help-block">{{ $errors->first('password_confirmation', ':message') }}</span>

                                <button type="submit">Save</button>
<!--                                <button>Reset</button>-->

                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>

                </div></div>

        </form></section>
@stop
@section('scripts')

@stop
