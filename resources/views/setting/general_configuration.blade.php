<div class="form-group required {{ $errors->has('logo_file') ? 'has-error' : '' }} ">
    {!! Form::label('logo_file', trans('settings.logo'), array('class' => 'control-label required')) !!}
    <div class="controls row" v-image-preview>
        <img src="{{ url('uploads/site').'/thumb_'.Settings::get('logo') }}"
             class="img-l col-sm-2">
        {!! Form::file('logo_file', null, array('id'=>'logo_file', 'class' => 'form-control')) !!}
        <img id="image-preview" width="300">
        <span class="help-block">{{ $errors->first('logo_file', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('login_file') ? 'has-error' : '' }} ">
    {!! Form::label('login_file', trans('settings.login'), array('class' => 'control-label required')) !!}
    <div class="controls row" v-image-preview>
        <img src="{{ url('uploads/site').'/thumb_'.Settings::get('login') }}"
             class="img-l col-sm-2">
        {!! Form::file('login_file', null, array('id'=>'login_file', 'class' => 'form-control')) !!}
        <img id="image-preview" width="300">
        <span class="help-block">{{ $errors->first('login_file', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('name') ? 'has-error' : '' }}">
    {!! Form::label('name', trans('settings.name'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::text('name', old('name', Settings::get('name')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('name', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
    {!! Form::label('email', trans('settings.email'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::text('email', old('email', Settings::get('email')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('email', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('allowed_extensions_avatar') ? 'has-error' : '' }}">
    {!! Form::label('allowed_extensions_avatar', trans('settings.allowed_extensions_avatar'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::text('allowed_extensions_avatar', old('allowed_extensions_avatar', Settings::get('allowed_extensions_avatar')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('max_upload_avatar_size', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('max_upload_avatar_size') ? 'has-error' : '' }}">
    {!! Form::label('max_upload_avatar_size', trans('settings.max_upload_avatar_size'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::select('max_upload_avatar_size', $max_upload_file_size, old('max_upload_avatar_size', Settings::get('max_upload_avatar_size')), array('id'=>'max_upload_file_size','class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('max_upload_avatar_size', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('minimum_characters') ? 'has-error' : '' }}">
    {!! Form::label('minimum_characters', trans('settings.minimum_characters'), array('class' => 'control-label required')) !!}
    <div class="controls">
        {!! Form::text('minimum_characters', old('minimum_characters', Settings::get('minimum_characters')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('minimum_characters', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('show_contact_page') ? 'has-error' : '' }}">
    {!! Form::label('show_contact_page', trans('settings.show_contact_page'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('show_contact_page', 'yes',(Settings::get('show_contact_page')=='yes')?true:false,array('class' => 'show_contact_page'))  !!}
                {!! Form::label('true', trans('settings.yes'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('show_contact_page', 'no', (Settings::get('show_contact_page')=='no')?true:false,array('class' => 'show_contact_page'))  !!}
                {!! Form::label('false', trans('settings.no')) !!}
            </div>
        </div>
    </div>
</div>

<div class="form-group required {{ $errors->has('rtl_support') ? 'has-error' : '' }}">
    {!! Form::label('rtl_support', trans('settings.rtl_support'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('rtl_support', 'yes',(Settings::get('rtl_support')=='yes')?true:false,array('class' => 'rtl_support'))  !!}
                {!! Form::label('true', trans('settings.yes'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('rtl_support', 'no', (Settings::get('rtl_support')=='no')?true:false,array('class' => 'rtl_support'))  !!}
                {!! Form::label('false', trans('settings.no')) !!}
            </div>
        </div>
    </div>
</div>
<div class="form-group required {{ $errors->has('upload_webcam') ? 'has-error' : '' }}">
    {!! Form::label('upload_webcam', trans('settings.upload_webcam'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('upload_webcam', 'upload',(Settings::get('upload_webcam')=='upload')?true:false,array('class' => 'upload_webcam'))  !!}
                {!! Form::label('true', trans('settings.upload'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('upload_webcam', 'webcam', (Settings::get('upload_webcam')=='webcam')?true:false,array('class' => 'upload_webcam'))  !!}
                {!! Form::label('false', trans('settings.webcam')) !!}
            </div>
        </div>
    </div>
</div>

<div class="form-group required {{ $errors->has('account_one_school') ? 'has-error' : '' }}">
    {!! Form::label('account_one_school', trans('settings.account_one_school'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('account_one_school', 'yes',(Settings::get('account_one_school')=='yes')?true:false,array('class' => 'account_one_school'))  !!}
                {!! Form::label('true', trans('settings.yes'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('account_one_school', 'no', (Settings::get('account_one_school')=='no')?true:false,array('class' => 'account_one_school'))  !!}
                {!! Form::label('false', trans('settings.no')) !!}
            </div>
        </div>
    </div>
</div>
<div class="form-group required {{ $errors->has('supplier_one_school') ? 'has-error' : '' }}">
    {!! Form::label('supplier_one_school', trans('settings.supplier_one_school'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('supplier_one_school', 'yes',(Settings::get('supplier_one_school')=='yes')?true:false,array('class' => 'supplier_one_school'))  !!}
                {!! Form::label('true', trans('settings.yes'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('supplier_one_school', 'no', (Settings::get('supplier_one_school')=='no')?true:false,array('class' => 'supplier_one_school'))  !!}
                {!! Form::label('false', trans('settings.no')) !!}
            </div>
        </div>
    </div>
</div>
<div class="form-group required {{ $errors->has('kitchen_admin_one_school') ? 'has-error' : '' }}">
    {!! Form::label('kitchen_admin_one_school', trans('settings.kitchen_admin_one_school'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('kitchen_admin_one_school', 'yes',(Settings::get('kitchen_admin_one_school')=='yes')?true:false,array('class' => 'kitchen_admin_one_school'))  !!}
                {!! Form::label('true', trans('settings.yes'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('kitchen_admin_one_school', 'no', (Settings::get('kitchen_admin_one_school')=='no')?true:false,array('class' => 'kitchen_admin_one_school'))  !!}
                {!! Form::label('false', trans('settings.no')) !!}
            </div>
        </div>
    </div>
</div>
<div class="form-group required {{ $errors->has('kitchen_staff_one_school') ? 'has-error' : '' }}">
    {!! Form::label('kitchen_staff_one_school', trans('settings.kitchen_staff_one_school'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('kitchen_staff_one_school', 'yes',(Settings::get('kitchen_staff_one_school')=='yes')?true:false,array('class' => 'kitchen_staff_one_school'))  !!}
                {!! Form::label('true', trans('settings.yes'))  !!}
            </div>
            <div class="radio">
                {!! Form::radio('kitchen_staff_one_school', 'no', (Settings::get('kitchen_staff_one_school')=='no')?true:false,array('class' => 'kitchen_staff_one_school'))  !!}
                {!! Form::label('false', trans('settings.no')) !!}
            </div>
        </div>
    </div>
</div>
<div class="form-group required {{ $errors->has('date_format') ? 'has-error' : '' }}">
    {!! Form::label('date_format', trans('settings.date_format'), array('class' => 'control-label required')) !!}
    <div class="controls">
        <div class="form-inline">
            {!! Form::radio('date_format', 'F j,Y',(Settings::get('date_format')=='F j,Y')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('true', date('F j,Y'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'Y-d-m',(Settings::get('date_format')=='Y-d-m')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('date_format', date('Y-d-m'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'Y-m-d',(Settings::get('date_format')=='Y-m-d')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('date_format', date('Y-m-d'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'd.m.Y.',(Settings::get('date_format')=='d.m.Y.')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('date_format', date('d.m.Y.'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'd.m.Y',(Settings::get('date_format')=='d.m.Y')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('date_format', date('d.m.Y'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'd/m/Y',(Settings::get('date_format')=='d/m/Y')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('date_format', date('d/m/Y'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'm/d/Y',(Settings::get('date_format')=='m/d/Y')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('date_format', date('m/d/Y'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('date_format', 'd.m.Y.',false,array('class' => 'icheck', 'id'=>'date_format_custom_radio'))  !!}
            {!! Form::label('custom_format', trans('settings.custom_format'))  !!}
            {!! Form::input('text','date_format_custom', Settings::get('date_format'), array('class' => 'form-control')) !!}
        </div>
    </div>
    <span class="help-block">{{ $errors->first('date_format', ':message') }}</span>
</div>
<a href="{{url('http://php.net/manual/en/function.date.php')}}">{!! trans('settings.date_time_format') !!}</a>
<div class="form-group required {{ $errors->has('time_format') ? 'has-error' : '' }}">
    {!! Form::label('time_format', trans('settings.time_format'), array('class' => 'control-label required')) !!}
    <div class="controls">
        <div class="form-inline">
            {!! Form::radio('time_format', 'g:i a',(Settings::get('time_format')=='g:i a')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('time_format', date('g:i a'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('time_format', 'g:i A',(Settings::get('time_format')=='g:i A')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('time_format', date('g:i A'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('time_format', 'H:i',(Settings::get('time_format')=='H:i')?true:false,array('class' => 'icheck'))  !!}
            {!! Form::label('time_format', date('H:i'))  !!}
        </div>
        <div class="form-inline">
            {!! Form::radio('time_format', 'H:i',false,array('class' => 'icheck', 'id'=>'time_format_custom_radio'))  !!}
            {!! Form::label('custom_format', trans('settings.custom_format'))  !!}
            {!! Form::input('text','time_format_custom', Settings::get('time_format'), array('class' => 'form-control')) !!}
        </div>
    </div>
    <span class="help-block">{{ $errors->first('date_format', ':message') }}</span>
</div>
<div class="form-group required {{ $errors->has('currency') ? 'has-error' : '' }}">
    {!! Form::label('currency', trans('settings.currency'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::select('currency', $options['currency'], old('currency', Settings::get('currency')), array('id'=>'currency','class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('currency', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('self_registration') ? 'has-error' : '' }}">
    {!! Form::label('self_registration', trans('settings.self_registration'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('self_registration', 'no',(Settings::get('self_registration')=='no')?true:false,array('id'=>'self_registration_no','class' => 'self_registration'))  !!}
                {!! Form::label('true', 'NO')  !!}
            </div>
            <div class="radio">
                {!! Form::radio('self_registration', 'yes', (Settings::get('self_registration')=='yes')?true:false,array('id'=>'self_registration_yes','class' => 'self_registration'))  !!}
                {!! Form::label('false', 'YES') !!}
            </div>
        </div>
        <span class="help-block">{{ $errors->first('self_registration', ':message') }}</span>
    </div>
</div>
<div class="form-group required self_registration_role {{ $errors->has('self_registration_role') ? 'has-error' : '' }}">
    {!! Form::label('self_registration_role', trans('settings.self_registration_role'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::select('self_registration_role', $self_registration_role, old('self_registration_role', Settings::get('self_registration_role')), array('id'=>'self_registration_role','class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('self_registration_role', ':message') }}</span>
    </div>
</div>
<div class="form-group required {{ $errors->has('generate_registration_code') ? 'has-error' : '' }}">
    {!! Form::label('generate_registration_code', trans('settings.generate_registration_code'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="form-inline">
            <div class="radio">
                {!! Form::radio('generate_registration_code', 'no',(Settings::get('generate_registration_code')=='no')?true:false,array('id'=>'generate_registration_code','class' => 'generate_registration_code'))  !!}
                {!! Form::label('true', 'NO')  !!}
            </div>
            <div class="radio">
                {!! Form::radio('generate_registration_code', 'yes', (Settings::get('generate_registration_code')=='yes')?true:false,array('id'=>'generate_registration_code','class' => 'generate_registration_code'))  !!}
                {!! Form::label('false', 'YES') !!}
            </div>
        </div>
        <span class="help-block">{{ $errors->first('generate_registration_code', ':message') }}</span>
    </div>
</div>

<div class="form-group {{ $errors->has('visitor_card_prefix') ? 'has-error' : '' }}">
    {!! Form::label('visitor_card_prefix', trans('settings.visitor_card_prefix'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::text('visitor_card_prefix', old('visitor_card_prefix', Settings::get('visitor_card_prefix')), array('class' => 'form-control')) !!}
        <span class="help-block">{{ $errors->first('visitor_card_prefix', ':message') }}</span>
    </div>
</div>
<div class="form-group {{ $errors->has('visitor_card_background') ? 'has-error' : '' }}">
    {!! Form::label('visitor_card_background', trans('settings.visitor_card_background'), array('class' => 'control-label')) !!}
    <div class="controls">
        <div class="controls row">
            <img src="{{ url('uploads/visitor_card/'.Settings::get('visitor_card_background')) }}"
                 alt="Visitor card background"
                 class="img-l col-sm-2">
            <div class="col-sm-9">
                <input type="file" name="visitor_card_background_file">
            </div>
        </div>
    </div>
</div>