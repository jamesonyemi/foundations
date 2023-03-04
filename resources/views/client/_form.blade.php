<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> <strong>{{$title}}</strong></div>
    </div>
    <div class="card-body">
        @if (isset($client))
            {!! Form::model($client, array('url' => url($type) . '/' . $client->id. '/'.'update', 'method' => 'put', 'class' => 'pbf', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'pbf', 'files'=> true)) !!}
        @endif
        <div class="row col-12  g-9 mb-7">

            <div class="form-group col-md-6 fv-row {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <label><strong> Client Name</strong></label>
                <div class="controls">
                    {!! Form::text('title', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                </div>
            </div>

            <div class="form-group  col-md-6 fv-row {{ $errors->has('legal_case_category_id') ? 'has-error' : '' }}">
               <i class="flaticon2-calendar"></i>  <label><strong>Client Category</strong></label>
               <div class="controls">
                  {!! Form::select('client_category_id', $clientCategories, null, array('id'=>'project_category_id', 'class' => 'form-select', 'data-control' => 'select2')) !!}
                  <span class="help-block">{{ $errors->first('legal_case_category_id', ':message') }}</span>
               </div>
            </div>

        </div>

        <div class="row col-12  g-9 mb-7">

            <div class="form-group  col-md-4 fv-row {{ $errors->has('region_id') ? 'has-error' : '' }}">
                <i class="flaticon2-calendar"></i>  <label><strong>Region</strong></label>
                <div class="controls">
                    {!! Form::select('region_id', $regions, null, array('id'=>'region_id', 'class' => 'form-select', 'data-control' => 'select2')) !!}
                    <span class="help-block">{{ $errors->first('region_id', ':message') }}</span>
                </div>
            </div>

            <div class="form-group  col-md-4 fv-row {{ $errors->has('district_id') ? 'has-error' : '' }}">
                <i class="flaticon2-calendar"></i>  <label><strong>District</strong></label>
                <div class="controls">
                    {!! Form::select('district_id', @$districts, null, array('id'=>'district_id', 'class' => 'form-select', 'data-control' => 'select2')) !!}
                    <span class="help-block">{{ $errors->first('district_id', ':message') }}</span>
                </div>
            </div>

            <div class="form-group  col-md-4 fv-row {{ $errors->has('client_status_id') ? 'has-error' : '' }}">
               <i class="flaticon2-calendar"></i>  <label><strong>Client Status</strong></label>
               <div class="controls">
                  {!! Form::select('client_status_id', $clientStatuses, null, array('id'=>'client_status_id', 'class' => 'form-select', 'data-control' => 'select2')) !!}
                  <span class="help-block">{{ $errors->first('client_status_id', ':message') }}</span>
               </div>
            </div>

        </div>


        <div class="row col-12  g-9 mb-7">
            <div class="form-group col-md-4 fv-row {{ $errors->has('phone') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <label><strong>Phone Number</strong></label>
                <div class="controls">
                    {!! Form::text('phone', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                </div>
            </div>

            <div class="form-group col-md-4 fv-row {{ $errors->has('email') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <label><strong>Email Address</strong></label>
                <div class="controls">
                    {!! Form::text('email', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('email', ':message') }}</span>
                </div>
            </div>

            <div class="form-group col-md-4 fv-row {{ $errors->has('location') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <label><strong>Location</strong></label>
                <div class="controls">
                    {!! Form::text('location', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('location', ':message') }}</span>
                </div>
            </div>
        </div>


        <div class="row col-12  g-9 mb-7">
            <div class="form-group col-md-12 fv-row {{ $errors->has('description') ? 'has-error' : '' }}">
                <i class="flaticon2-document"></i> <label><strong>Description</strong></label>
                <div class="controls">
                    {!! Form::textArea('description', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('description', ':message') }}</span>
                </div>
            </div>

        </div>

        <div class="form-group">
            <div class="controls">
                @if (isset($client))
                    <a href="#" onclick="updateData({{$client->id}});return false;" class="btn btn-success btn-sm spinner-right spinner-white"  id="btnSubmit">{{'UPDATE'}}</a>
                @else
                    <a href="#" onclick="addData();return false;" class="btn btn-success btn-sm spinner-right spinner-white"  id="btnSubmit">{{'ADD'}}</a>
                @endif
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>
@section('scripts')
{{-- Scripts --}}
<script src="{{ asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>

<script>



    $('#kt_docs_repeater_basic').repeater({
        initEmpty: false,

        defaultValues: {
            'text-input': 'foo'
        },

        show: function () {
            $(this).slideDown();
        },

        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        }
    });

    var quill = new Quill('#reportEditor', {
        modules: {
            toolbar: [
                [{
                    header: [1, 2, false]
                }],
                ['bold','italic','underline','link','image'],
                [{ list: 'ordered' }, { list: 'bullet' }]
            ]
        },
        placeholder: 'Type your text here...',
        theme: 'snow' // or 'bubble'
    });




    function updateData(id) {
        var get_url = "{{ url($link) . '/' . ':id' }}";
        var refresh_url = "{{ url($link)}}";
        get_url = get_url.replace(':id', id);
        $('#btnSubmit').attr("disabled", true);
        $("#btnSubmit").html('Please Wait');
        $('#btnSubmit').addClass("spinner");
        $.ajax({
            url: get_url,
            type: "put",
            data: $(".pbf").serialize(),
            container: "#pbf",
            success:function(data)
            {
                $('#btnSubmit').attr("disabled", false);
                $("#btnSubmit").html('UPDATE');
                $('#btnSubmit').removeClass("spinner");
                if(data.error){
                    showToastrMessage('<p>'+data.error+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                }
                else if(data.exception){
                    showToastrMessage('<p>'+data.exception+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                }else{
                    i=1;
                    showDiv(refresh_url);
                    $('#add_modal').modal('hide').slideDown();
                    showToastrMessage(data, '{!! addslashes(__('Success')) !!}', 'success');
                    window.location.replace(refresh_url);
                }
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
        var get_url = "{{ url($link)}}";
        $('#btnSubmit').attr("disabled", true);
        $("#btnSubmit").html('Please Wait');
        $('#btnSubmit').addClass("spinner");
        $.ajax({
            url: get_url,
            type: "POST",
            data: $(".pbf").serialize(),
            container: "#pbf",
            success:function(data)
            {
                $('#btnSubmit').attr("disabled", false);
                $("#btnSubmit").html('UPDATE');
                $('#btnSubmit').removeClass("spinner");
                if(data.error){
                    showToastrMessage('<p>'+data.error+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                }
                else if(data.exception){
                    showToastrMessage('<p>'+data.exception+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                }else{
                    i=1;
                    showDiv(get_url);
                    $('#add_modal').modal('hide').slideDown();
                    showToastrMessage(data, '{!! addslashes(__('Success')) !!}', 'success');
                    window.location.replace(get_url);
                }
            },
            error: function (request, status, error) {

                json = $.parseJSON(request.responseText);
                $.each(json.errors, function(key, value){
                    $('.alert-danger').show();
                    $('.alert-danger').append('<p>'+value+'</p>');

                    showToastrMessage('<p>'+value+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                });
                $('#btnSubmit').attr("disabled", false);
                $("#btnSubmit").html('ADD');
                $('#btnSubmit').removeClass("spinner");
                $("#feeback").html('');
            }
        });
    }



    $(document).ready(function()
    {
        $('input').attr('autocomplete','off');
        var srcdiv="region_id";
        $(document.getElementById(srcdiv)).on
        ('change',function()
            {
                //console.log('Its have changed');

                var region_id=$(this).val();

                var div=$(this).parent().parent();
                //console.log(section_id);
                if (region_id > 0)
                {

                    $('#district_id').html('');
                    $.ajax
                    (
                        {
                            type: "POST",
                            url: '{{ url('ajax/findRegionDistricts') }}',
                            data: {_token: '{{ csrf_token() }}', region_id: region_id},
                            success: function (result)
                            {
                                $.each(result, function (val, text)

                                    {
                                        $('#district_id').append($('<option></option>').val(val).html(text))
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

@stop
