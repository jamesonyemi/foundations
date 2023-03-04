<div class="modal-header py-5 ">
    <h5 class="modal-title">{{$title}}
        {{--<span class="d-block text-muted font-size-sm">sub datatable for the selected row is loaded from remote data source</span>--}}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <i aria-hidden="true" class="ki ki-close"></i>
    </button>
</div>
<div class="card card-custom gutter-b">

    <div class="card-body">
        @if (isset($kpi))
            {!! Form::model($kpi, array('url' => url($type) . '/' . $kpi->id, 'method' => 'put', 'class' => 'bf', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}
        @endif

            @if (isset($kpi))

                <div class="form-group row">

                    <div class="form-group col-3 required  {{ $errors->has('kra_id') ? 'has-error' : '' }}">
                        {!! Form::label('kra_id', trans('kpi.section'), array('class' => 'control-label required')) !!}
                        <div class="controls">
                            {!! Form::select('kra_id', $kras, null, array('id'=>'kra_id', 'class' => 'form-control')) !!}
                            {{--<span class="help-block">{{ $errors->first('kra_id', ':message') }}</span>--}}
                        </div>
                    </div>

                    <div class="form-group col-3 required  {{ $errors->has('title') ? 'has-error' : '' }}">
                        {!! Form::label('title', trans('level.name'), array('class' => 'control-label required')) !!}
                        <div class="controls">
                            {!! Form::text('title', null, array('class' => 'form-control')) !!}
                            {{--<span class="help-block">{{ $errors->first('title', ':message') }}</span>--}}
                        </div>
                    </div>

                    <div class="form-group col-1 required  {{ $errors->has('weight') ? 'has-error' : '' }}">
                        {!! Form::label('weight', trans('kpi.weight'), array('class' => 'control-label required')) !!}
                        <div class="controls">
                            {!! Form::text('weight', null, array('class' => 'form-control')) !!}
                            {{--<span class="help-block">{{ $errors->first('weight', ':message') }}</span>--}}
                        </div>
                    </div>

                    <div class="form-group col-3 required {{ $errors->has('responsible_employee_id') ? 'has-error' : '' }}">
                        {!! Form::label('responsible_employee_id', trans('kpi.responsibility'), array('class' => 'control-label required')) !!}
                        <div class="controls">
                            {!! Form::select('responsible_employee_id', $employees, null, array('id'=>'responsible_employee_id', 'class' => 'form-control')) !!}
                            {{--<span class="help-block">{{ $errors->first('responsible_employee_id', ':message') }}</span>--}}
                        </div>
                    </div>

                    <div class="form-group col-2 required  {{ $errors->has('kpi_timeline_id') ? 'has-error' : '' }}">
                        {!! Form::label('kpi_timeline_id', trans('kpi.timeline'), array('class' => 'control-label required')) !!}
                        <div class="controls">
                            {!! Form::select('kpi_timeline_id', $kpitimelines, null, array('id'=>'kpi_timeline_id', 'class' => 'form-control')) !!}
                            {{--<span class="help-block">{{ $errors->first('kra_id', ':message') }}</span>--}}
                        </div>
                    </div>
                </div>

            @else

            {{--<div class="accordion accordion-light  accordion-toggle-arrow" id="accordionExample5">
                <div class="card">
                    <div class="card-header" id="headingOne5">
                        <div class="card-title" data-toggle="collapse" data-target="#collapseOne5">
                            <i class="fonticon-alignment-right  fs-2x me-3-1"></i> Financial
                        </div>
                    </div>
                    <div id="collapseOne5" class="collapse show" data-parent="#accordionExample5">

                        <div class="alert alert-danger print-error-msg" style="display:none">
                            <ul></ul>
                        </div>


                        <div class="alert alert-success print-success-msg" style="display:none">
                            <ul></ul>
                        </div>
                        <div id="kt_repeater_1">
                            <div class="form-group row">
                                <div data-repeater-list="" class="col-lg-10">
                                    <div data-repeater-item="" class="form-group row">
                                        <div class="form-group col-3 required  {{ $errors->has('kra_id') ? 'has-error' : '' }}">
                                            {!! Form::label('kra_id', trans('kpi.section'), array('class' => 'control-label required')) !!}
                                            <div class="controls">
                                                {!! Form::select('kra_id', $krasCustomer, null, array('id'=>'kra_id', 'class' => 'form-control')) !!}
                                                --}}{{--<span class="help-block">{{ $errors->first('kra_id', ':message') }}</span>--}}{{--
                                            </div>
                                        </div>

                                        <div class="form-group col-3 required  {{ $errors->has('title') ? 'has-error' : '' }}">
                                            {!! Form::label('title', trans('level.name'), array('class' => 'control-label required')) !!}
                                            <div class="controls">
                                                {!! Form::text('title', null, array('class' => 'form-control')) !!}
                                                --}}{{--<span class="help-block">{{ $errors->first('title', ':message') }}</span>--}}{{--
                                            </div>
                                        </div>



                                        <div class="form-group col-1 required  {{ $errors->has('weight') ? 'has-error' : '' }}">
                                            {!! Form::label('weight', trans('kpi.weight'), array('class' => 'control-label required')) !!}
                                            <div class="controls">
                                                {!! Form::text('weight[]', null, array('class' => 'form-control')) !!}
                                                --}}{{--<span class="help-block">{{ $errors->first('weight', ':message') }}</span>--}}{{--
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="javascript:;" data-repeater-delete="" class="btn btn-sm font-weight-bolder btn-light-danger">
                                                <i class="la la-trash-o"></i>Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-2 col-form-label text-right"></label>
                                <div class="col-lg-4">
                                    <a href="javascript:;" data-repeater-create="" class="btn btn-sm font-weight-bolder btn-light-primary">
                                        <i class="la la-plus"></i>Add</a>
                                </div>
                            </div>
                        </div>




                    </div>
                </div>



            </div>--}}

                <div class="container">
                    <h2 align="center">Laravel - Dynamically Add or Remove input fields using JQuery</h2>
                    <div class="form-group">


                            <div class="alert alert-danger print-error-msg" style="display:none">
                                <ul></ul>
                            </div>


                            <div class="alert alert-success print-success-msg" style="display:none">
                                <ul></ul>
                            </div>


                            <div class="table-responsive">
                                <table class="table table-bordered" id="dynamic_field">
                                    <div class="leaveWrapper">
                                    <tr>
                                        <td>
                                            <input type="text" name="name[]" placeholder="Enter your Name" class="form-control name_list" />
                                        </td>
                                        <td></td>
                                    </tr>
                                    </div>
                                </table>
                                <button type="button" name="add" id="add" class="btn btn-success">Add More</button>
                                <input type="button" name="submit" id="submit" class="btn btn-info" value="Submit" />
                            </div>


                    </div>
                </div>

            @endif

            <div class="form-group">
                <div class="controls">
                    @if (isset($kpi))
                        <a href="#" onclick="updateData({{@$kpi->id}});return false;" class="btn btn-success btn-sm">{{'UPDATE'}}</a>
                    @else
                        <a href="#" onclick="addData();return false;" class="btn btn-success btn-sm">{{'ADD'}}</a>
                    @endif
                </div>
                <div class="row" id="feeback"></div>
                <div class="alert alert-danger" style="display:none">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
</div>




<script src="{{ asset('assets/js/pages/crud/forms/widgets/select2.js') }}"></script>
<script src="{{ asset('assets/js/pages/custom/profile/profile.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        var postURL = "{{ url($type)}}";
        var i=1;


        $('#add').click(function(){
            var leaveWrapper    = $(".leaveWrapper"); //Input fields wrapper
            var leaveInput = leaveWrapper.html(); //Initial input field is set to 1
            i++;
            $('#dynamic_field').append(leaveInput);
        });


        $(document).on('click', '.btn_remove', function(){
            var button_id = $(this).attr("id");
            $('#row'+button_id+'').remove();
        });


        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('#submit').click(function(){
            $.ajax({
                url:postURL,
                method:"POST",
                data:$(".bf").serialize(),
                type:'json',
                success:function(data)
                {
                    if(data.error){
                        printErrorMsg(data.error);
                    }else{
                        i=1;
                        $('.dynamic-added').remove();
                        $('#add_name')[0].reset();
                        $(".print-success-msg").find("ul").html('');
                        $(".print-success-msg").css('display','block');
                        $(".print-error-msg").css('display','none');
                        $(".print-success-msg").find("ul").append('<li>Record Inserted Successfully.</li>');
                    }
                }
            });
        });


        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $(".print-success-msg").css('display','none');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }
    });
</script>
<script>
    // Class definition
    var KTFormRepeater = function() {

        // Private functions
        var demo1 = function() {
            $('#kt_repeater_1').repeater({
                initEmpty: false,

                /*defaultValues: {
                    'text-input': 'foo'
                },*/

                show: function () {
                    $(this).slideDown();
                },

                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                }
            });
        }

        return {
            // public functions
            init: function() {
                demo1();
            }
        };
    }();

    jQuery(document).ready(function() {
        KTFormRepeater.init();
    });

    /*FINANCIAL PERSPECTIVE*/
    var max_fields = 10; //Maximum allowed input fields
    var leaveWrapper    = $(".leaveWrapper"); //Input fields wrapper
    var add_leavebutton = $(".add_leave"); //Add button class or ID
    var x = 1; //Initial input field is set to 1
    var leaveInput = leaveWrapper.html(); //Initial input field is set to 1


    //When user click on add school input button
    $(add_leavebutton).click(function(e){
        e.preventDefault();
        //Check maximum allowed input fields
        if(x < max_fields){
            x++; //input field increment
            //add input field
            $(leaveWrapper).append(leaveInput).slideDown();


        }
    });

/*CUSTOMER PERSPECTIVE*/

    var max_fields = 10; //Maximum allowed input fields
    var financeWrapper    = $(".financeWrapper"); //Input fields wrapper
    var add_financebutton = $(".add_finance"); //Add button class or ID
    var x = 1; //Initial input field is set to 1
    var financeInput = financeWrapper.html(); //Initial input field is set to 1


    //When user click on add school input button
    $(add_financebutton).click(function(e){
        e.preventDefault();
        //Check maximum allowed input fields
        if(x < max_fields){
            x++; //input field increment
            //add input field
            $(financeWrapper).append(financeInput).slideDown();


        }
    });


    /*INTERNAL PROCESSES*/

    var max_fields = 10; //Maximum allowed input fields
    var internalWrapper    = $(".internalWrapper"); //Input fields wrapper
    var add_internalbutton = $(".add_internal"); //Add button class or ID
    var x = 1; //Initial input field is set to 1
    var internalInput = internalWrapper.html(); //Initial input field is set to 1


    //When user click on add school input button
    $(add_internalbutton).click(function(e){
        e.preventDefault();
        //Check maximum allowed input fields
        if(x < max_fields){
            x++; //input field increment
            //add input field
            $(internalWrapper).append(internalInput).slideDown();


        }
    });



    /*LEARNING AND GROWTH*/

    var max_fields = 10; //Maximum allowed input fields
    var learningWrapper    = $(".learningWrapper"); //Input fields wrapper
    var add_learningbutton = $(".add_learning"); //Add button class or ID
    var x = 1; //Initial input field is set to 1
    var learningInput = learningWrapper.html(); //Initial input field is set to 1


    //When user click on add school input button
    $(add_learningbutton).click(function(e){
        e.preventDefault();
        //Check maximum allowed input fields
        if(x < max_fields){
            x++; //input field increment
            //add input field
            $(learningWrapper).append(learningInput).slideDown();


        }
    });



    /*LEAVING THE VALUES*/

    var max_fields = 10; //Maximum allowed input fields
    var leavingWrapper    = $(".leavingWrapper"); //Input fields wrapper
    var add_leavingbutton = $(".add_leaving"); //Add button class or ID
    var x = 1; //Initial input field is set to 1
    var leavingInput = leavingWrapper.html(); //Initial input field is set to 1


    //When user click on add school input button
    $(add_leavingbutton).click(function(e){
        e.preventDefault();
        //Check maximum allowed input fields
        if(x < max_fields){
            x++; //input field increment
            //add input field
            $(leavingWrapper).append(leavingInput).slideDown();


        }
    });




    /*PERSONAL DEVELOPMENT*/

    var max_fields = 10; //Maximum allowed input fields
    var personalWrapper    = $(".personalWrapper"); //Input fields wrapper
    var add_personalbutton = $(".add_personal"); //Add button class or ID
    var x = 1; //Initial input field is set to 1
    var personalInput = personalWrapper.html(); //Initial input field is set to 1


    //When user click on add school input button
    $(add_personalbutton).click(function(e){
        e.preventDefault();
        //Check maximum allowed input fields
        if(x < max_fields){
            x++; //input field increment
            //add input field
            $(personalWrapper).append(personalInput).slideDown();


        }
    });





    $('.remove1').click(function (deleteElement) {

        $(this).parent().slideUp(deleteElement);
    });
    $('.date').datepicker({
        format: 'dd/mm/yyyy'
    });


    function updateData(id) {
        var get_url = "{{ url($type) . '/' . ':id' }}";
        get_url = get_url.replace(':id', id);
        $.ajax({
            url: get_url,
            type: "put",
            data: $(".bf").serialize(),
            container: "#bf",
            success: function (response) {
                $('#add_modal').modal('hide');
                $('.alert-danger').hide();
                $("#feeback").html(response);
                showToastrMessage(response, '{!! addslashes(__('Success')) !!}', 'success');
            },
            error: function (request, status, error) {

                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('.alert-danger').show();
                    $('.alert-danger').append('<p>'+value+'</p>');

                    showToastrMessage('<p>'+value+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                });
                $("#feeback").html('');
            }
        });
    }


    function addData() {
        var get_url = "{{ url($type)}}";
        $.ajax({
            url: get_url,
            type: "POST",
            data: $(".bf").serialize(),
            container: "#bf",
            success: function (response) {
                /* $('#add_modal').modal('hide');*/
                $('.alert-danger').hide();
                $("#feeback").html(response);
                showToastrMessage(response, '{!! addslashes(__('Success')) !!}', 'success');
                $(".bf").resetForm();
            },
            error: function (request, status, error) {

                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('.alert-danger').show();
                    $('.alert-danger').append('<p>'+value+'</p>');

                    showToastrMessage('<p>'+value+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                });
                $("#feeback").html('');
            }
        });
    }



    $(document).ready(function()
    {
        $('input').attr('autocomplete','off');
        var srcdiv="section_id";
        $(document.getElementById(srcdiv)).on
        ('change',function()
            {
                //console.log('Its have changed');

                var section_id=$(this).val();

                var div=$(this).parent().parent();
                //console.log(section_id);
                if (section_id > 0)
                {

                    $('#direction_id').html('');
                    $.ajax
                    (
                        {
                            type: "POST",
                            url: '{{ url('ajax/findDirectionName') }}',
                            data: {_token: '{{ csrf_token() }}', section_id: section_id},
                            success: function (result)
                            {
                                $.each(result, function (val, text)

                                    {
                                        $('#direction_id').append($('<option></option>').val(val).html(text))
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

