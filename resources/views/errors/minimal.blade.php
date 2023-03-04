<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<head><base href="../../">
    <title>Efficient Digital Workplace Error</title>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="The most advanced Web ERP System trusted by 94,000 institutions and professionals" />
    <meta name="keywords" content="ERP, exla erp, digital workplace, Human Resource Management, Financial Management, Payroll Management" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="Digital Workplace" />
    <meta property="og:title" content="The most advanced Web ERP System trusted by 94,000 institutions and professionals" />
    <meta property="og:url" content="https://exlaerp.com" />
    <meta property="og:site_name" content="Maksline | Exla" />
    <link rel="canonical" href="https://etranzact.exlaerp.com" />>
    <link rel="shortcut icon" href="assets/media/logos/favicon.ico" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/loader.css') }}" rel="stylesheet" type="text/css"
</head>
<!--end::Head-->
<!--begin::Body-->
<body id="kt_body" class="auth-bg">
<!--begin::Main-->
<!--begin::Root-->
<div class="d-flex flex-column flex-root">
    <!--begin::Authentication - Error 500 -->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div class="d-flex flex-column flex-column-fluid text-center p-10 py-lg-15">
            <!--begin::Logo-->
            <a href="/">
                @if(!isset($school->photo))
                    <img src="{{ url('uploads/site').'/thumb_'.Settings::get('logo') }}" alt="logo"  class="h-25px logo"
                @else
                    <img src="{{ url('uploads/school_photo').'/'.$school->photo}}"  class="h-25px logo"
                        @endif />
            </a>
            <!--end::Logo-->
            <!--begin::Wrapper-->
            <div class="pt-lg-10 mb-10">
                <!--begin::Logo-->
                <h1 class="fw-bolder fs-2qx text-gray-800 mb-10">System Error</h1>
            @include('flash-message')
            @yield('message')
                <!--begin::Message-->
                <div class="fw-bold fs-3 text-muted mb-15">Something went wrong!
                    <br />Please try again later.</div>
                <!--end::Message-->
                <!--begin::Action-->
                <div class="text-center">
                    <a href="{{ url('./') }}" class="btn btn-lg btn-primary fw-bolder">Go to homepage</a>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Wrapper-->
            <!--begin::Illustration-->
            <div class="d-flex flex-row-auto bgi-no-repeat bgi-position-x-center bgi-size-contain bgi-position-y-bottom min-h-100px min-h-lg-350px" style="background-image: url(assets/media/illustrations/sketchy-1/17.png"></div>
            <!--end::Illustration-->
        </div>
        <!--end::Content-->
        <!--begin::Footer-->
        <div class="d-flex flex-center flex-column-auto p-10">
            <!--begin::Links-->
            <div class="d-flex align-items-center fw-bold fs-6">
                <a href="#" class="text-muted text-hover-primary px-2">About</a>
                <a href="#" class="text-muted text-hover-primary px-2">Contact</a>
                <a href="#" class="text-muted text-hover-primary px-2">Contact Us</a>
            </div>
            <!--end::Links-->
        </div>
        <!--end::Footer-->
    </div>
    <!--end::Authentication - Error 500-->
</div>
<!--end::Root-->
<!--end::Main-->
<!--begin::Javascript-->
<script>var hostUrl = "assets/";</script>
<!--begin::Global Javascript Bundle(used by all pages)-->
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<!--end::Global Javascript Bundle-->
<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
