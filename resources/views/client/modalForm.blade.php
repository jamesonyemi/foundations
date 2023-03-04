<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> <strong>{{$title}}</strong></div>
    </div>
    <div class="card-body">
        @if (isset($project))
            {!! Form::model($project, array('url' => url($type) . '/' . $project->id. '/'.'update', 'method' => 'put', 'class' => 'pbf', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'pbf', 'files'=> true)) !!}
        @endif
        <div class="row col-12  g-9 mb-7">

            <div class="form-group col-md-6 fv-row {{ $errors->has('title') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <label><strong>Project Title</strong></label>
                <div class="controls">
                    {!! Form::text('title', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('title', ':message') }}</span>
                </div>
            </div>

            <div class="form-group  col-md-6 fv-row {{ $errors->has('legal_case_category_id') ? 'has-error' : '' }}">
               <i class="flaticon2-calendar"></i>  <label><strong>Project Category</strong></label>
               <div class="controls">
                  {!! Form::select('project_category_id', $projectCategories, null, array('id'=>'project_category_id', 'class' => 'form-select', 'data-control' => 'select2')) !!}
                  <span class="help-block">{{ $errors->first('legal_case_category_id', ':message') }}</span>
               </div>
            </div>

        </div>


        <div class="row col-12  g-9 mb-7">
            <div class="form-group col-md-6 fv-row {{ $errors->has('company_id') ? 'has-error' : '' }}">
               <i class="flaticon2-calendar"></i> <label><strong>Subsidiary</strong></label>
               <div class="controls">
                   {!! Form::select('company_id[]', $companies, @$company_legal_cases, array('id'=>'company_id', 'multiple'=>true, 'class' => 'form-select', 'data-control' => 'select2')) !!}
                   <span class="help-block">{{ $errors->first('company_id', ':message') }}</span>
               </div>
            </div>

            <div class="form-group col-md-6 fv-row {{ $errors->has('defendants') ? 'has-error' : '' }}">
                <i class="flaticon2-notification"></i> <label><strong>Defendants</strong></label>
                <div class="controls">
                    {!! Form::text('defendants', null, array('class' => 'form-control')) !!}
                    <span class="help-block">{{ $errors->first('defendants', ':message') }}</span>
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
        <div class="row col-12  g-9 mb-7">
            <div class="col-md-6 fv-row">
                <div class="form-group {{ $errors->has('project_status_id') ? 'has-error' : '' }}">
                    <i class="flaticon2-start-up"></i> <label><strong>Status</strong></label>
                    <div class="controls">
                        {!! Form::select('project_status_id', $projectStatuses, null, array('id'=>'project_status_id', 'class' => 'form-select')) !!}
                        <span class="help-block">{{ $errors->first('project_status_id', ':message') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 fv-row">
            <div class="form-group">
                <div class="form-group {{ $errors->has('employee_id') ? 'has-error' : '' }}">
                    <i class="flaticon2-calendar"></i> <label><strong>Stake Holders</strong></label>
                    <div class="controls">
                        {!! Form::select('employee_id[]', $employees, @$legal_case_stakeholders, array('id'=>'employee_id', 'multiple'=>true, 'class' => 'form-select', 'data-control' => 'select2')) !!}
                        <span class="help-block">{{ $errors->first('employee_id', ':message') }}</span>
                    </div>
                </div>
            </div>
            </div>
        </div>

            <div class="row col-12  g-9 mb-7">
                <!--begin::Repeater-->
                <div id="kt_docs_repeater_basic">
                    <!--begin::Form group-->
                    <i class="flaticon2-calendar"></i> <label><strong>Project Components</strong></label>
                    <div class="form-group">
                        <div data-repeater-list="project_components">
                            <div data-repeater-item class="form-group row g-9 mb-5">

                                <div class="col-md-3 fv-row">
                                    <label class="form-label">Artisan:</label>
                                    {!! Form::select('project_artisan_id', $artisans, null, array('id'=>'project_artisan_id', 'class' => 'form-select', 'data-control' => 'select2')) !!}
                                </div>
                                <div class="col-md-3 fv-row">
                                    <label class="form-label">Description:</label>
                                    <input name="description" type="text" class="form-control mb-2 mb-md-0" placeholder="Enter Description" />
                                </div>


                                <div class="col-md-2 fv-row">
                                    <label class="form-label">Cost:</label>
                                    <input name="cost" type="text" class="form-control mb-2 mb-md-0" placeholder="Enter Cost" />
                                </div>

                                <div class="col-md-2 fv-row">
                                    <label class="form-label">Contract:</label>
                                    <input name="file" type="file" class="form-control mb-2 mb-md-0" />
                                </div>


                                <div class="col-md-2 fv-row">
                                    <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-light-danger mt-1 mt-md-9">
                                        <i class="la la-trash-o"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!--end::Form group-->

                    <!--begin::Form group-->
                    <div class="form-group mt-5">
                        <a href="javascript:;" data-repeater-create class="btn btn-light-primary">
                            <i class="la la-plus"></i>Add More Components
                        </a>
                    </div>
                    <!--end::Form group-->
                </div>

                <!--end::Repeater-->
            </div>



        <div class="form-group">
            <div class="controls">
                @if (isset($project))
                    <a href="#" onclick="updateData({{$project->id}});return false;" class="btn btn-success btn-sm spinner-right spinner-white"  id="btnSubmit">{{'UPDATE'}}</a>
                @else
                    <a href="#" onclick="addData();return false;" class="btn btn-success btn-sm spinner-right spinner-white"  id="btnSubmit">{{'ADD'}}</a>
                @endif
            </div>
        </div>

        {!! Form::close() !!}
    </div>
</div>

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
        var get_url = "{{ url($type) . '/' . ':id' }}";
        var refresh_url = "{{ url($type)}}";
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
        var get_url = "{{ url($type)}}";
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
        var srcdiv="procurement_master_category_id";
        $(document.getElementById(srcdiv)).on
        ('change',function()
            {
                //console.log('Its have changed');

                var procurement_master_category_id=$(this).val();

                var div=$(this).parent().parent();
                //console.log(section_id);
                if (procurement_master_category_id > 0)
                {

                    $('#parent_id').html('');
                    $.ajax
                    (
                        {
                            type: "POST",
                            url: '{{ url('ajax/findlegalCaseCategory') }}',
                            data: {_token: '{{ csrf_token() }}', procurement_master_category_id: procurement_master_category_id},
                            success: function (result)
                            {
                                $.each(result, function (val, text)

                                    {
                                        $('#parent_id').append($('<option></option>').val(val).html(text))
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
