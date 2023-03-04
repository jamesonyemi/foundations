@if ($message = Session::get('success'))
<!--    <div class="alert alert-success alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>{{ $message }}</strong>
    </div>-->

    <div class="alert alert-dismissible bg-success d-flex flex-column flex-sm-row w-100 p-5 mb-10">
        <!--begin::Icon-->

        <!--end::Svg Icon-->
        <!--end::Icon-->
        <!--begin::Content-->
        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
            <h4 class="mb-2 text-light">{{ $message }}</h4>
<!--            <span>The alert component can be used to highlight certain parts of your page for higher content visibility.</span>-->
        </div>
        <!--end::Content-->

    </div>
@endif





@if ($message = Session::get('error'))
    <div class="alert alert-dismissible bg-danger d-flex flex-column flex-sm-row w-100 p-5 mb-10">
        <!--begin::Icon-->
        <!--begin::Svg Icon | path: icons/duotune/communication/com003.svg-->

        <!--end::Svg Icon-->
        <!--end::Icon-->
        <!--begin::Content-->
        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
            <h4 class="mb-2 text-light">{{ $message }}</h4>
<!--            <span>The alert component can be used to highlight certain parts of your page for higher content visibility.</span>-->
        </div>
        <!--end::Content-->
        <!--begin::Close-->

    </div>
@endif

@if ($message = Session::get('warning'))
    <div class="alert alert-dismissible bg-warning d-flex flex-column flex-sm-row w-100 p-5 mb-10">

        <!--begin::Content-->
        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
            <h4 class="mb-2 text-light">{{ $message }}</h4>
            <!--            <span>The alert component can be used to highlight certain parts of your page for higher content visibility.</span>-->
        </div>
        <!--end::Content-->

    </div>
@endif

@if ($message = Session::get('info'))
    <div class="alert alert-dismissible bg-primary d-flex flex-column flex-sm-row w-100 p-5 mb-10">
        <!--begin::Icon-->

        <!--begin::Content-->
        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
            <h4 class="mb-2 text-light">{{ $message }}</h4>
            <!--            <span>The alert component can be used to highlight certain parts of your page for higher content visibility.</span>-->
        </div>
        <!--end::Content-->

    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        Please check the form below for errors
    </div>
@endif





{{--<script class="text/javascript">
    @if(Session::get('success'))
    showToastrMessage('{!!   addslashes(Session::get('success')) !!}', '{!! addslashes(__('messages.success')) !!}', 'success');
    <?php \Session::remove("success"); ?>
    @endif
    @if(Session::get('warning'))
    showToastrMessage('{!!   addslashes(Session::get('warning')) !!}', '{!! addslashes(__('messages.warning')) !!}', 'warning');
    <?php \Session::remove("warning"); ?>
    @endif
    @if(Session::get('error'))
    showToastrMessage('{!! addslashes(__('messages.errorTitle')) !!}', '{!! addslashes(__('messages.error')) !!}', 'error');
    <?php \Session::remove("error"); ?>
    @endif
    @if (count( $errors ) > 0)
    showToastrMessage('{!! addslashes(__('messages.errorTitle')) !!}', '{!! addslashes(__('messages.error')) !!}', 'error');
    @endif

</script>--}}
