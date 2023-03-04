<div class="form-group required {{ $errors->has('sms_driver') ? 'has-error' : '' }}">
    {!! Form::label('sms_driver', trans('settings.sms_driver'), array('class' => 'control-label')) !!}
    <div class="controls">
        {!! Form::select('sms_driver', $sms_drivers, Settings::get('sms_driver'), array('id'=>'sms_driver', 'class' => 'form-control select2')) !!}
        <span class="help-block">{{ $errors->first('sms_driver', ':message') }}</span>
    </div>
</div>
<div id="none">
    <div class="form-group required {{ $errors->has('sms_from') ? 'has-error' : '' }}">
        {!! Form::label('sms_from', trans('settings.sms_from'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('sms_from', old('sms_from', Settings::get('sms_from')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('sms_from', ':message') }}</span>
        </div>
    </div>
</div>
{{-- CallFire --}}
<div id="callfire">
    <div class="form-group required {{ $errors->has('callfire_app_login') ? 'has-error' : '' }}">
        {!! Form::label('callfire_app_login', trans('settings.callfire_app_login'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('callfire_app_login', old('callfire_app_login', Settings::get('callfire_app_login')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('callfire_app_login', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('callfire_app_password') ? 'has-error' : '' }}">
        {!! Form::label('callfire_app_password', trans('settings.callfire_app_password'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('callfire_app_password', old('callfire_app_password', Settings::get('callfire_app_password')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('callfire_app_password', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Eztexting --}}
<div id="eztexting">
    <div class="form-group required {{ $errors->has('eztexting_username') ? 'has-error' : '' }}">
        {!! Form::label('eztexting_username', trans('settings.eztexting_username'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('eztexting_username', old('eztexting_username', Settings::get('eztexting_username')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('eztexting_username', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('eztexting_password') ? 'has-error' : '' }}">
        {!! Form::label('eztexting_password', trans('settings.eztexting_password'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('eztexting_password', old('eztexting_password', Settings::get('eztexting_password')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('eztexting_password', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Labsmobile --}}
<div id="labsmobile">
    <div class="form-group required {{ $errors->has('labsmobile_client') ? 'has-error' : '' }}">
        {!! Form::label('labsmobile_client', trans('settings.labsmobile_client'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('labsmobile_client', old('labsmobile_client', Settings::get('labsmobile_client')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('labsmobile_client', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('labsmobile_username') ? 'has-error' : '' }}">
        {!! Form::label('labsmobile_username', trans('settings.labsmobile_username'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('labsmobile_username', old('labsmobile_username', Settings::get('labsmobile_username')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('labsmobile_username', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('labsmobile_password') ? 'has-error' : '' }}">
        {!! Form::label('labsmobile_password', trans('settings.labsmobile_password'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('labsmobile_password', old('labsmobile_password', Settings::get('labsmobile_password')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('labsmobile_password', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Mozeo --}}
<div id="mozeo">
    <div class="form-group required {{ $errors->has('mozeo_company_key') ? 'has-error' : '' }}">
        {!! Form::label('mozeo_company_key', trans('settings.mozeo_company_key'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('mozeo_company_key', old('mozeo_company_key', Settings::get('mozeo_company_key')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('mozeo_company_key', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('mozeo_username') ? 'has-error' : '' }}">
        {!! Form::label('mozeo_username', trans('settings.mozeo_username'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('mozeo_username', old('mozeo_username', Settings::get('mozeo_username')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('mozeo_username', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('mozeo_password') ? 'has-error' : '' }}">
        {!! Form::label('mozeo_password', trans('settings.mozeo_password'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('mozeo_password', old('mozeo_password', Settings::get('mozeo_password')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('mozeo_password', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Nexmo --}}
<div id="nexmo">
    <div class="form-group required {{ $errors->has('nexmo_api_key') ? 'has-error' : '' }}">
        {!! Form::label('nexmo_api_key', trans('settings.nexmo_api_key'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('nexmo_api_key', old('nexmo_api_key', Settings::get('nexmo_api_key')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('nexmo_api_key', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('nexmo_api_secret') ? 'has-error' : '' }}">
        {!! Form::label('nexmo_api_secret', trans('settings.nexmo_api_secret'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('nexmo_api_secret', old('nexmo_api_secret', Settings::get('nexmo_api_secret')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('nexmo_api_secret', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Twilio --}}
<div id="twilio">
    <div class="form-group required {{ $errors->has('twilio_account_sid') ? 'has-error' : '' }}">
        {!! Form::label('twilio_account_sid', trans('settings.twilio_account_sid'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('twilio_account_sid', old('twilio_account_sid', Settings::get('twilio_account_sid')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('twilio_account_sid', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('twilio_auth_token') ? 'has-error' : '' }}">
        {!! Form::label('twilio_auth_token', trans('settings.twilio_auth_token'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('twilio_auth_token', old('twilio_auth_token', Settings::get('twilio_auth_token')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('twilio_auth_token', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Zenvia --}}
<div id="zenvia">
    <div class="form-group required {{ $errors->has('zenvia_account_key') ? 'has-error' : '' }}">
        {!! Form::label('zenvia_account_key', trans('settings.zenvia_account_key'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('zenvia_account_key', old('zenvia_account_key', Settings::get('zenvia_account_key')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('zenvia_account_key', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('zenvia_passcode') ? 'has-error' : '' }}">
        {!! Form::label('zenvia_passcode', trans('settings.zenvia_passcode'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('zenvia_passcode', old('zenvia_passcode', Settings::get('zenvia_passcode')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('zenvia_passcode', ':message') }}</span>
        </div>
    </div>
</div>

{{-- Bulk SMS --}}
<div id="bulk_sms">
    <div class="form-group required {{ $errors->has('bulk_sms') ? 'has-error' : '' }}">
        {!! Form::label('sms_bulk_username', trans('settings.sms_bulk_username'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('sms_bulk_username', old('sms_bulk_username', Settings::get('sms_bulk_username')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('sms_bulk_username', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('sms_bulk_password') ? 'has-error' : '' }}">
        {!! Form::label('sms_bulk_password', trans('settings.sms_bulk_password'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('sms_bulk_password', old('sms_bulk_password', Settings::get('sms_bulk_password')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('sms_bulk_password', ':message') }}</span>
        </div>
    </div>
</div>

{{-- MSG91 SMS --}}
<div id="msg91">
    <div class="form-group required {{ $errors->has('msg91_auth_key') ? 'has-error' : '' }}">
        {!! Form::label('msg91_auth_key', trans('settings.msg91_auth_key'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('msg91_auth_key', old('msg91_auth_key', Settings::get('msg91_auth_key')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('msg91_auth_key', ':message') }}</span>
        </div>
    </div>
    <div class="form-group required {{ $errors->has('msg91_sender_id') ? 'has-error' : '' }}">
        {!! Form::label('msg91_sender_id', trans('settings.msg91_sender_id'), array('class' => 'control-label')) !!}
        <div class="controls">
            {!! Form::text('msg91_sender_id', old('msg91_sender_id', Settings::get('msg91_sender_id')), array('class' => 'form-control')) !!}
            <span class="help-block">{{ $errors->first('msg91_sender_id', ':message') }}</span>
        </div>
    </div>
</div>

<div v-if="sms_driver!='none' && sms_driver!=''">
    <div class="form-group">
        {!! Form::label('automatic_sms_mark', trans('settings.automatic_sms_mark'), array('class' => 'control-label')) !!}
        <div class="controls">
            <div class="form-inline">
                <div class="radio">
                    {!! Form::radio('automatic_sms_mark', '1',(Settings::get('automatic_sms_mark')=='1')?true:false)  !!}
                    {!! Form::label('1', trans('settings.yes'))  !!}
                </div>
                <div class="radio">
                    {!! Form::radio('automatic_sms_mark', '0', (Settings::get('automatic_sms_mark')=='0')?true:false)  !!}
                    {!! Form::label('0', trans('settings.no')) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('automatic_sms_mark', trans('settings.automatic_sms_attendance'), array('class' => 'control-label')) !!}
        <div class="controls">
            <div class="form-inline">
                <div class="radio">
                    {!! Form::radio('automatic_sms_attendance', '1',(Settings::get('automatic_sms_attendance')=='1')?true:false)  !!}
                    {!! Form::label('1', trans('settings.yes'))  !!}
                </div>
                <div class="radio">
                    {!! Form::radio('automatic_sms_attendance', '0', (Settings::get('automatic_sms_attendance')=='0')?true:false)  !!}
                    {!! Form::label('0', trans('settings.no')) !!}
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    $('#sms_driver').on('change', function () {
        showSmsSettings($(this).val() );
    });
    $( document ).ready(function() {
        showSmsSettings('{{Settings::get('sms_driver')}}' );
    });

    function showSmsSettings(provider) {
        if (provider == '') {
            $('#none').show();
            $('#callfire').hide();
            $('#labsmobile').hide();
            $('#mozeo').hide();
            $('#nexmo').hide();
            $('#twilio').hide();
            $('#zenvia').hide();
            $('#bulk_sms').hide();
            $('#msg91').hide();
            $('#eztexting').hide();
        } else {
            $('#none').hide();
            $('#callfire').hide();
            $('#labsmobile').hide();
            $('#mozeo').hide();
            $('#nexmo').hide();
            $('#twilio').hide();
            $('#zenvia').hide();
            $('#bulk_sms').hide();
            $('#msg91').hide();
            $('#eztexting').hide();
            $('#' + provider).show();
        }
    }
</script>