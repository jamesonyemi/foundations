<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signup</title>
    <link rel="stylesheet" type="text/css" href="assets/css/_main.css?v=2.3">
    <link rel="stylesheet" type="text/css" href="assets/css/control.css?v=1.3">
</head>
<body>




<section class="left" style="background-image:url(assets/img/30072397147_803e6306db_b.jpeg);">herr</section>
<section class="right"><div class="control-box">
        <form method="POST" action="{{ url('signup') }}">
            {{ csrf_field() }}

            <div class="rads">
                <div class="login_auth">
                    <h1>Create Account</h1>
                    @include('flash-message')
                </div>
                <label>First Name</label>
                <input type="text" name="first_name" placeholder="First Name">
                <span class="help-block">{{ $errors->first('first_name', ':message') }}</span>
                <p>
                <label>Last Name</label>
                <input type="text" name="last_name" placeholder="Last Name">
                <span class="help-block">{{ $errors->first('last_name', ':message') }}</span>
                <p>
                    <label>Organisation</label>
                    <input type="text" name="organization" placeholder="Organisation"></p>
                <span class="help-block">{{ $errors->first('organization', ':message') }}</span>
                <p>
                    <label>Email</label>
                    <input type="text" name="email" placeholder="Email"></p>
                <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                <p>
                    <label>Telephone</label>
                    <input type="text" name="mobile" placeholder="Telephone"></p>
                <span class="help-block">{{ $errors->first('mobile', ':message') }}</span>
                <p>
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password"></p>
                <span class="help-block">{{ $errors->first('password', ':message') }}</span>


                <p>
                    <button type="submit" name="submit">Create</button></p>
            </div>
        </form>
    </div></section>
<a href="" id="logo"></a>
</body>
</html>
