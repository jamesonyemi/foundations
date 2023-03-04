<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> {{$title}}</div>
    </div>
    <div class="card-body">
        @if (isset($permission))
            {!! Form::model($permission, array('url' => url($type) . '/' . $permission->id. '/'.'update', 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}
        @endif
        <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
            {!! Form::label('name', trans('schools.title'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('name', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('title', ':message') }}</span>
            </div>
        </div>
        <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
            {!! Form::label('description', trans('general.description'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('description', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('title', ':message') }}</span>
            </div>
        </div>


        <div class="form-group">
            <div class="controls">
                @if (isset($permission))
                <a href="#" onclick="updateData({{$permission->id}});return false;" class="btn btn-success btn-sm">{{'UPDATE'}}</a>
                @else
                    <a href="#" onclick="addData();return false;" class="btn btn-success btn-sm">{{'ADD'}}</a>
                @endif
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>

{{-- Scripts --}}


    <script>
        $('.date').datepicker({
            format: 'dd/mm/yyyy'
        });


        function updateData(id) {
            var get_url = "{{ url($type) . '/'.':id/update' }}";
            var refresh_url = "{{ url($type)}}";
            get_url = get_url.replace(':id', id);
            $.ajax({
                url: get_url,
                type: "post",
                data: $(".bf").serialize(),
                container: "#bf",
                success: function (response) {
                    showDiv(refresh_url);
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
                    showDiv(get_url);
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


        $(document).ready(function()
        {
            console.log('Mathew Akoto is Great');

            $(".pcheck").on('ifChecked', function()
                {
                    console.log('Mathew Akoto is Great');


                }
            )

        })
    </script>

