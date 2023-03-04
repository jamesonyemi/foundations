<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> {{$title}}</div>
    </div>
    <div class="card-body">
        @if (isset($visitorLog))
            {!! Form::model($visitorLog, array('url' => url($type) . '/' . $visitorLog->id, 'method' => 'put', 'class' => 'bf', 'files'=> true, 'id' => 'dActivityUpdate')) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'bf', 'files'=> true, 'id' => 'dActivitySubmit')) !!}
        @endif
        <div class="row">
            <div class="col-lg-6 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <strong>{{'Name'}}</strong>
                <div class="controls">
                    {!! Form::text('name', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                </div>
            </div>

            <div class="col-lg-6  form-group required {{ $errors->has('description') ? 'has-error' : '' }}">
                <i class="flaticon2-open-text-book"></i> <strong>{{'Purpose'}}</strong>
                <div class="controls">
                    {!! Form::text('purpose', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('purpose', ':message') }}</span>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-lg-6 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <strong>{{'Organization'}}</strong>
                <div class="controls">
                    {!! Form::text('organization', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('organization', ':message') }}</span>
                </div>
            </div>

            <div class="form-group  col-6 {{ $errors->has('responsible_employee_id') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <strong>{{'Visiting Employee'}}</strong>
                {{--<span class="text-danger">*</span>--}}
                <div class="controls">
                    {!! Form::select('visited_employee_id', @$employees, null, array('id'=>'visited_employee_id', 'multiple'=>false, 'class' => 'form-control select2')) !!}
                    <span class="help-block">{{ $errors->first('visited_employee_id', ':message') }}</span>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-lg-4 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-email"></i> <strong>{{'Email Address'}}</strong>
                <div class="controls">
                    {!! Form::text('email', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                </div>
            </div>

            <div class="col-lg-4 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-phone"></i> <strong>{{'phone_number'}}</strong>
                <div class="controls">
                    {!! Form::text('phone_number', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('phone_number', ':message') }}</span>
                </div>
            </div>

            <div class="form-group  col-4 {{ $errors->has('responsible_employee_id') ? 'has-error' : '' }}">
                <i class="flaticon2-cardiogram"></i> <strong>{{'Car Number'}}</strong>
                {{--<span class="text-danger">*</span>--}}
                <div class="controls">
                    {!! Form::text('car_number', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('car_number', ':message') }}</span>
                </div>
            </div>
        </div>


        <div class="row"   >

            <div class="col-lg-4 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-lock"></i> <strong>{{'Access Card Number'}}</strong>
                <div class="controls">
                    {!! Form::text('access_card_number', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('access_card_number', ':message') }}</span>
                </div>
            </div>

            <div class="col-lg-4 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-lock"></i> <strong>{{'Observations'}}</strong>
                <div class="controls">
                    {!! Form::text('observations', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('observations', ':message') }}</span>
                </div>
            </div>

            <div class="col-lg-4 form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-lock"></i> <strong>{{'Time'}}</strong>
                <div class="controls">
                    {!! Form::text('check_in', now()->format('Y-m-d H:i:s'), array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('check_in', ':message') }}</span>
                </div>
            </div>
        </div>




            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="controls">
                            @if (isset($visitorLog))
                                <button type="submit" id="btnSubmit" class="btn btn-success btn-sm spinner-right spinner-white" style="margin-top:10px">UPDATE</button>
                            @else
                                <button type="submit" id="btnSubmit" class="btn btn-success btn-sm spinner-right spinner-white" style="margin-top:10px">SUBMIT</button>
                            @endif
                        </div>
                        <div class="row" id="feeback"></div>
                        <div class="alert alert-danger" id="errorback" style="display:none">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>




<script src="{{ asset('assets/js/pages/crud/forms/widgets/select2.js') }}"></script>
<script src="{{ asset('assets/js/pages/custom/profile/profile.js') }}"></script>

<script>

    function valueChanged()
    {
        if($('#showKpiActivity').is(":checked"))
            $("#kpi_activity_div").show();
        else
            $("#kpi_activity_div").hide();
    }





    $('.date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoApply: true,
            minYear: 1901,
            locale: {
                format: "YYYY-MM-DD"
            },
            maxYear: parseInt(moment().format("YYYY"),10)
        });


    $(document).ready(function (e) {





        $('#dActivitySubmit').on('submit',(function(e) {

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            e.preventDefault();

            var formData = new FormData(this);
            var get_url = "{{ url($type)}}";
            var refresh_url = "{{ url($type).'/indexAll'}}";
            $('#btnSubmit').attr("disabled", true);
            $("#btnSubmit").html('Please Wait');
            $('#btnSubmit').addClass("spinner");
            $("#errorback").html('');

            $.ajax({

                type:'POST',
                url: get_url,
                data:formData,
                cache:false,
                contentType: false,
                processData: false,
                success:function(data){
                    if(data.error){
                        showToastrMessage('<p>'+data.error+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                        $('.alert-danger').show();
                        $('#btnSubmit').attr("disabled", false);
                        $("#btnSubmit").html('SUBMIT');
                        $('#btnSubmit').removeClass("spinner");
                        $('.alert-danger').append('<p>'+data.error+'</p>');
                    }
                    else
                    {
                        showDiv(refresh_url);
                        $('#add_modal').modal('hide');
                        toastr.success(response);
                    }
                },

                error: function (request, status, error) {

                    json = $.parseJSON(request.responseText);
                    $.each(json.errors, function(key, value){
                        $('.alert-danger').show();
                        $('.alert-danger').append('<p>'+value+'</p>');
                        toastr.error('<p>'+value+'</p>');
                    });
                    $("#feeback").html('');
                    $('#btnSubmit').attr("disabled", false);
                    $("#btnSubmit").html('SUBMIT');
                    $('#btnSubmit').removeClass("spinner");
                }

            });

        }));


        $('#dActivityUpdate').on('submit',(function(e) {

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            e.preventDefault();

            var formData = new FormData(this);
            var get_url = "{{ url($type) . '/' . ':id' }}";
            var refresh_url = "{{ url($type).'/indexAll'}}";
            get_url = get_url.replace(':id', {{@$visitorLog->id }});
            $('#btnSubmit').attr("disabled", true);
            $("#btnSubmit").html('Please Wait');
            $('#btnSubmit').addClass("spinner");
            $("#errorback").html('');

            $.ajax({

                type:'POST',
                url: get_url,
                data:formData,
                cache:false,
                contentType: false,
                processData: false,
                success:function(data){
                    if(data.error){
                        showToastrMessage('<p>'+data.error+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                        $('.alert-danger').show();
                        $('#btnSubmit').attr("disabled", false);
                        $("#btnSubmit").html('SUBMIT');
                        $('#btnSubmit').removeClass("spinner");
                        $('.alert-danger').append('<p>'+data.error+'</p>');
                    }
                    else
                    {
                        showDiv(refresh_url);
                        $('#add_modal').modal('hide');
                        toastr.success(response);
                    }
                },

                error: function (request, status, error) {

                    json = $.parseJSON(request.responseText);
                    $.each(json.errors, function(key, value){
                        $('.alert-danger').show();
                        $('.alert-danger').append('<p>'+value+'</p>');
                        toastr.error('<p>'+value+'</p>');
                    });
                    $("#feeback").html('');
                    $('#btnSubmit').attr("disabled", false);
                    $("#btnSubmit").html('SUBMIT');
                    $('#btnSubmit').removeClass("spinner");
                }

            });

        }))

    })




    $(document).ready(function()
    {
        $('input').attr('autocomplete','off');
        var srcdiv="help_desk_category_id";
        $(document.getElementById(srcdiv)).on
        ('change',function()
            {
                //console.log('Its have changed');

                var help_desk_category_id=$(this).val();
                console.log(help_desk_category_id);
                if (help_desk_category_id > 0)
                {

                    $('#help_desk_subcategory_id').html('');
                    $.ajax
                    (
                        {
                            type: "POST",
                            url: '{{ url('ajax/findHelpdeskSubCategory') }}',
                            data: {_token: '{{ csrf_token() }}', section_id: help_desk_category_id},
                            success: function (result)
                            {
                                $.each(result, function (val, text)

                                    {
                                        $('#help_desk_subcategory_id').append($('<option></option>').val(val).html(text))
                                    }
                                );
                            }
                        }
                    );
                }
            }
        )

    })
</script>

