<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\User;

class InstallSettingsEmailRequest extends Request
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
            'email_driver' => 'required',
            'email_host' => 'required_if:email_driver,no',
            'email_port' => 'required_if:email_driver,no',
            'email_username' => 'required_if:email_driver,no',
            'email_password' => 'required_if:email_driver,no',
        ];
    }
}
