<div class="card card-custom gutter-b">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"> {{$title}}</div>
    </div>
    <div class="card-body">
        @if (isset($faq))
            {!! Form::model($faq, array('url' => url($type) . '/' . $faq->id, 'method' => 'put', 'class' => 'bf', 'files'=> true)) !!}
        @else
            {!! Form::open(array('url' => url($type), 'method' => 'post', 'class' => 'bf', 'files'=> true)) !!}
        @endif
        <div class="form-group required {{ $errors->has('title') ? 'has-error' : '' }}">
            {!! Form::label('title', trans('faq.title'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::text('title', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('title', ':message') }}</span>
            </div>
        </div>
        <div class="form-group  {{ $errors->has('faq_category_id') ? 'has-error' : '' }}">
            {!! Form::label('faq_category_id', trans('faq.faq_category'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::select('faq_category_id', $faq_categories, null, array('class' => 'form-control select2')) !!}
                <span class="help-block">{{ $errors->first('faq_category_id', ':message') }}</span>
            </div>
        </div>
        <div class="form-group required {{ $errors->has('content') ? 'has-error' : '' }}">
            {!! Form::label('title', trans('faq.content'), array('class' => 'control-label required')) !!}
            <div class="controls">
                {!! Form::textarea('content', null, array('class' => 'form-control')) !!}
                <span class="help-block">{{ $errors->first('content', ':message') }}</span>
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
