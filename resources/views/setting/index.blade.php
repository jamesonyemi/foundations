
    <link rel="stylesheet" href="{{ asset('css/icheck.css') }}" type="text/css">

    <link rel="stylesheet" href="{{ asset('css/icheck.css') }}" type="text/css">
    <div class="card card-custom gutter-b">
        <div class="card-body">
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}
            {!! Form::hidden('purchase_code', Settings::get('purchase_code')) !!}
            {!! Form::hidden('envato_username', Settings::get('envato_username')) !!}
            {!! Form::hidden('envato_email', Settings::get('envato_email')) !!}
            <div class="nav-tabs-custom" id="setting_tabs">
                @if($errors->any())
                    <div class="form-group has-error">
                        @foreach($errors->all() as $error)
                            <span class="help-block">{{ $error }}</span>
                        @endforeach
                    </div>
                @endif
                <ul class="nav nav-tabs" style="display:list-item;">
                    <li class="active">
                        <a href="#general_configuration" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.general_configuration') }}"><i
                                    class="material-icons md-24">build</i></a>
                    </li>
                    <li>
                        <a href="#email_configuration" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.email_configuration') }}"><i
                                    class="material-icons md-24">email</i></a>
                    </li>
                    <li>
                        <a href="#about_us_pages" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.about_us_pages') }}"><i
                                    class="material-icons md-24">assignment</i></a>
                    </li>
                    <li>
                        <a href="#paypal_settings" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.paypal_settings') }}"><i
                                    class="material-icons md-24">payment</i></a>
                    </li>
                    <li>
                        <a href="#stripe_settings" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.stripe_settings') }}"><i
                                    class="material-icons md-24">vpn_key</i></a>
                    </li>
                    <li>
                        <a href="#book_late_settings" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.book_late_settings') }}"><i
                                    class="material-icons md-24">book</i></a>
                    </li>
                    <li>
                        <a href="#sms_settings" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.sms_settings') }}"><i
                                    class="material-icons md-24">phone</i></a>
                    </li>
                    <li>
                        <a href="#theme_settings" class="tabs_settings"
                           data-toggle="tab" title="{{ trans('settings.theme_settings') }}"><i
                                    class="material-icons md-24">photo</i></a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="general_configuration">
                        @include('setting.general_configuration')
                    </div>
                    <div class="tab-pane" id="email_configuration">
                        @include('setting.email_configuration')
                    </div>
                    <div class="tab-pane" id="about_us_pages">
                        @include('setting.about_us_pages')
                    </div>
                    <div class="tab-pane" id="paypal_settings">
                        @include('setting.paypal_settings')
                    </div>
                    <div class="tab-pane" id="stripe_settings">
                        @include('setting.stripe_settings')
                    </div>
                    <div class="tab-pane" id="book_late_settings">
                        @include('setting.book_late_settings')
                    </div>
                    <div class="tab-pane" id="sms_settings">
                        @include('setting.sms_settings')
                    </div>
                    <div class="tab-pane" id="theme_settings">
                        @include('setting.theme_settings')
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="controls">
                    <button type="submit" class="btn btn-success btn-sm"><i
                                class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                </div>
            </div>


            {!! Form::close() !!}
        </div>
    </div>

    <script type="text/javascript" src="{{ asset('js/icheck.min.js') }}"></script>
    <script>

        $( document ).ready(function() {

            $('#menu_bg_color').colorpicker();
            $('#menu_active_bg_color').colorpicker();
            $('#menu_active_border_right_color').colorpicker();
            $('#menu_color').colorpicker();
            $('#menu_active_color').colorpicker();
            $('#frontend_bg_color').colorpicker();
            $('#frontend_text_color').colorpicker();
            $('#frontend_link_color').colorpicker();
            $('#frontend_menu_bg_color').colorpicker();

            $('#theme_name').change(function () {
                $('#menu_bg_color').val("");
                $('#menu_active_bg_color').val("");
                $('#menu_active_border_right_color').val("");
                $('#menu_color').val("");
                $('#menu_active_color').val("");
                $('#frontend_bg_color').val("");
                $('#frontend_text_color').val("");
                $('#frontend_link_color').val("");
                $('#frontend_menu_bg_color').val("");
                if ($(this).val() != "") {
                    $.ajax({
                        type: "GET",
                        url: '{{ url('/setting/get_theme_colors') }}/' + $(this).val(),
                        success: function (result) {
                            $('#menu_bg_color').val(result.menu_bg_color);
                            $('#menu_active_bg_color').val(result.menu_active_bg_color);
                            $('#menu_active_border_right_color').val(result.menu_active_border_right_color);
                            $('#menu_color').val(result.menu_color);
                            $('#menu_active_color').val(result.menu_active_color);
                            $('#frontend_bg_color').val(result.frontend_bg_color);
                            $('#frontend_text_color').val(result.frontend_text_color);
                            $('#frontend_link_color').val(result.frontend_link_color);
                            $('#frontend_menu_bg_color').val(result.frontend_menu_bg_color);
                        }
                    });
                }
            });
        });

        $(document).ready(function () {
            $('.icheck').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
            $("input[name='date_format']").on('ifChecked', function () {
                if ("date_format_custom_radio" != $(this).attr("id"))
                    $("input[name='date_format_custom']").val($(this).val()).siblings('.example').text($(this).siblings('span').text());
            });
            $("input[name='date_format_custom']").focus(function () {
                $("#date_format_custom_radio").attr("checked", "checked");
            });

            $("input[name='time_format']").on('ifChecked', function () {
                if ("time_format_custom_radio" != $(this).attr("id"))
                    $("input[name='time_format_custom']").val($(this).val()).siblings('.example').text($(this).siblings('span').text());
            });
            $("input[name='time_format_custom']").focus(function () {
                $("#time_format_custom_radio").attr("checked", "checked");
            });
            $("input[name='date_format_custom'], input[name='time_format_custom']").on('ifChecked', function () {
                var format = $(this);
                format.siblings('img').css('visibility', 'visible');
                $.post(ajaxurl, {
                    action: 'date_format_custom' == format.attr('name') ? 'date_format' : 'time_format',
                    date: format.val()
                }, function (d) {
                    format.siblings('img').css('visibility', 'hidden');
                    format.siblings('.example').text(d);
                });
            });
        });
        jQuery(document).ready(function ($) {
            $('.smtp').hide();
            $('.email_driver').change(function () {
                if ($(this).filter(':checked').val() == 'smtp') {
                    $('.smtp').show();
                }
                else {
                    $('.smtp').hide();
                }
            });
            if ($('.email_driver').filter(':checked').val() == 'smtp') {
                $('.smtp').show();
            }

            $('.self_registration_role').hide();
            $('.self_registration').change(function () {
                if ($(this).filter(':checked').val() == 'yes') {
                    $('.self_registration_role').show();
                    $('.generate_registration_code').show();
                }
                else {
                    $('.self_registration_role').hide();
                    $('.generate_registration_code').hide();
                }
            });
            if ($('.self_registration').filter(':checked').val() == 'yes') {
                $('.self_registration_role').show();
                $('.generate_registration_code').show();
            }

            $('.about_school_page_area').hide();
            $('.about_school_page').change(function () {
                if ($(this).filter(':checked').val() == 'yes') {
                    $('.about_school_page_area').show();
                }
                else {
                    $('.about_school_page_area').hide();
                }
            });
            if ($('.about_school_page').filter(':checked').val() == 'yes') {
                $('.about_school_page_area').show();
            }

            $('.about_teachers_page_area').hide();
            $('.about_teachers_page').change(function () {
                if ($(this).filter(':checked').val() == 'yes') {
                    $('.about_teachers_page_area').show();
                }
                else {
                    $('.about_teachers_page_area').hide();
                }
            });
            if ($('.about_teachers_page').filter(':checked').val() == 'yes') {
                $('.about_teachers_page_area').show();
            }
        })
    </script>
