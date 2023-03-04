<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class SettingRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'max_upload_avatar_size' => 'required',
            'allowed_extensions_avatar' => 'required',
            'email_driver' => 'required',
            'email_host' => 'required_if:email_driver,smtp',
            'email_port' => 'required_if:email_driver,smtp',
            'email_username' => 'required_if:email_driver,smtp',
            'email_password' => 'required_if:email_driver,smtp',
        ];
    }
}
