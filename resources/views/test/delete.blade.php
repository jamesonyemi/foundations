
    <div class="row">
        <div class="col-md-12">
            {!! Form::open(array('url' => url($type) . '/' . $kpi->id, 'method' => 'delete', 'class' => 'bf')) !!}

            @include($type.'/_details')

            {!! Form::close() !!}
        </div>
    </div>
