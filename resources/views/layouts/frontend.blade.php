<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <title> {{$title or 'SMS'}} | {{ Settings::get('name') }}</title>
    @if(Settings::get('rtl_support') == 'yes')
        <link href="{{ asset('css/rtl_libs_frontend.css') }}" rel="stylesheet" type="text/css"/>
    @else
        <link href="{{ asset('css/libs_frontend.css') }}" rel="stylesheet" type="text/css"/>
    @endif
    <link href="{{ asset('css/frontend_sms.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{asset('js/libs_frontend.js')}}" type="text/javascript"></script>
    <link href="{{ asset('css/custom_frontend_colors.css') }}" rel="stylesheet" type="text/css"/>

</head>
<body>
<!--[if lt IE 10]>
<p class="browsehappy">{{trans('dashboard.outdated_browser')}}<a href="http://browsehappy.com/">{{trans('dashboard.new_one')}}</a>.</p>
<![endif]-->
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ url('uploads/site').'/'.Settings::get('logo') }}" alt="logo"
                     style="width:35px;height:35px;"/>
                {{ Settings::get('name') }}
            </a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                @if(isset($pages))
                    @foreach ($pages as $item)
                        <li><a href="{{url('page/'.$item->slug)}}">{{$item->title}}</a></li>
                    @endforeach
                @endif
                @if(Settings::get('about_school_page')=='yes' && Settings::get('about_school_page_title')!="")
                    <li><a href="{{url('about_school_page')}}">{{Settings::get('about_school_page_title')}}</a></li>
                @endif
                @if(Settings::get('about_teachers_page')=='yes' && Settings::get('about_teachers_page_title')!="")
                    <li><a href="{{url('about_teachers_page')}}">{{Settings::get('about_teachers_page_title')}}</a>
                    </li>
                @endif
                @if(isset($count_blogs) && $count_blogs>0)
                    <li><a href="{{url('blogs')}}">{{trans('frontend.blog')}}</a>
                    </li>
                @endif
                @if(isset($count_faq) && $count_faq>0)
                    <li><a href="{{url('faqs')}}">{{trans('frontend.faq')}}</a>
                    </li>
                @endif
                @if(Settings::get('show_contact_page')=='yes')
                    <li><a href="{{url('contact')}}">{{trans('frontend.contact')}}</a></li>
                @endif
                <li><a href="{{url('signin')}}">
                        @if(!Sentinel::check())
                            {{trans('auth.login')}}
                        @else
                            {{trans('auth.go_to_secure')}}
                        @endif
                    </a>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div>
<div class="container">
    @include('flash::message')
    @yield('content')
    @yield('scripts')
</div>
</body>
</html>