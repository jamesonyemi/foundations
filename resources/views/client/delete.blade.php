
    <div class="row">
        <div class="col-md-12">
            {!! Form::open(array('url' => url($type) . '/' . $legalCase->id, 'method' => 'delete', 'class' => 'bf')) !!}

            @include($type.'/_details')

            {!! Form::close() !!}
        </div>
    </div>

    <script>

        function deleteData() {
            var get_url = "{{ url($type) . '/' . $legalCase->id}}";
            var refresh_url = "{{ url($type)}}";
            $.ajax({
                url:get_url,
                method:"delete",
                data:$(".bf").serialize(),
                type:'json',
                success:function(data)
                {
                    if(data.error){
                        printErrorMsg(data.error);
                    }
                    else if(data.exception){
                        $(".print-error-msg").find("ul").append('<li>'+data.exception+'</li>');
                        showToastrMessage('<p>'+data.exception+'</p>', '{!! addslashes(__('error')) !!}', 'error');
                    }else{
                        i=1;
                        showToastrMessage(data, '{!! addslashes(__('Success')) !!}', 'success');
                        showDiv(refresh_url);
                        $(".print-success-msg").find("ul").html('');
                        $(".print-success-msg").css('display','block');
                        $(".print-error-msg").css('display','none');
                        $(".print-success-msg").find("ul").append('<li>data.</li>');
                        $('#delete_modal').modal('hide').slideDown();
                    }
                },

            });
        }



    </script>
