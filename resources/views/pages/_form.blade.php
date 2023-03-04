<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> {{$title}}</div>
    </div>
    <div class="card-body">
        @if (isset($page))
            {!! Form::model($page, array('url' => url($type) . '/' . $page->id, 'method' => 'put', 'class' => 'bf', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}
        @endif
        <div class="form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
            {!! Form::label('title', trans('pages.title'), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::text('title', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('title', ':message') }}</span>
            </div>
        </div>

        <div class="form-group  {{ $errors->has('content') ? 'has-error' : '' }}">
            {!! Form::label('content', trans('pages.content'), array('class' => 'control-label')) !!}
            <div class="controls">
                {!! Form::textarea('content', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('content', ':message') }}</span>
            </div>
        </div>
            <div class="form-group {{ $errors->has('have_slider') ? 'has-error' : '' }}">
                {!! Form::label('have_slider', trans('pages.have_slider'), array('class' => 'control-label')) !!}
                <div class="controls radiobutton">
                    {!! Form::label('have_slider', trans('pages.no'), array('class' => 'control-label')) !!}
                    {!! Form::radio('have_slider', '0',(isset($page->have_slider) && $page->have_slider==0)?true:false) !!}
                    {!! Form::label('have_slider', trans('pages.yes'), array('class' => 'control-label')) !!}
                    {!! Form::radio('have_slider', '1',(isset($page->have_slider) && $page->have_slider==1)?true:false) !!}
                </div>
            </div>

        <div class="form-group">
            <div class="controls">
                <a href="{{ url($type) }}" class="btn btn-primary btn-sm">{{trans('table.cancel')}}</a>
                <button type="submit" class="btn btn-success btn-sm">{{trans('table.ok')}}</button>
            </div>
        </div>


        {!! Form::close() !!}
    </div>
</div>
