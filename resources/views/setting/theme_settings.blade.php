<div class="form-group">
    {!! Form::label('theme_name', trans('settings.theme_name'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::select('theme_name', $themes, null, array('id'=>'theme_name', 'class' => 'form-control select2')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('menu_bg_color', trans('settings.menu_bg_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('menu_bg_color', old('menu_bg_color', Settings::get('menu_bg_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('menu_active_bg_color', trans('settings.menu_active_bg_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('menu_active_bg_color', old('menu_active_bg_color', Settings::get('menu_active_bg_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('menu_active_border_right_color', trans('settings.menu_active_border_right_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('menu_active_border_right_color', old('menu_active_border_right_color', Settings::get('menu_active_border_right_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('menu_color', trans('settings.menu_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('menu_color', old('menu_color', Settings::get('menu_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('menu_active_color', trans('settings.menu_active_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('menu_active_color', old('menu_active_color', Settings::get('menu_active_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('frontend_menu_bg_color', trans('settings.frontend_menu_bg_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('frontend_menu_bg_color', old('frontend_menu_bg_color', Settings::get('frontend_menu_bg_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('frontend_bg_color', trans('settings.frontend_bg_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('frontend_bg_color', old('frontend_bg_color', Settings::get('frontend_bg_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('frontend_text_color', trans('settings.frontend_text_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('frontend_text_color', old('frontend_text_color', Settings::get('frontend_text_color')), array('class' => 'form-control')) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('frontend_link_color', trans('settings.frontend_link_color'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('frontend_link_color', old('frontend_link_color', Settings::get('frontend_link_color')), array('class' => 'form-control')) !!}
    </div>
</div>