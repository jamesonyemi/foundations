<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Donate</title>
    <link rel="stylesheet" type="text/css" href="assets/css/_main.css?v=2.3">
    <link rel="stylesheet" type="text/css" href="assets/css/control.css?v=1.3">
</head>
<body>




<section class="left" style="background-image:url(assets/img/30072397147_803e6306db_b.jpeg);">herr</section>
<section class="right"><div class="control-box">
        <form method="POST" action="{{ url('signin') }}">
            {{ csrf_field() }}
            <div class="rads">
                <div class="login_auth">
                    @include('flash-message')
                    <h1>Members Login</h1></div>
                <label>User Name</label>
                <input type="text" name="mobile_email"  id="mobile_email" placeholder="Email">
                <p><label>Password</label>
                    <input type="password" name="password" placeholder="Password"></p>
                <p>
                    <button type="submit" name="submit">Login</button></p>
            </div>
        </form>
    </div></section>
<a href="" id="logo"></a>
</body>
</html>
