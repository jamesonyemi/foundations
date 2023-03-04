<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\User;

class InstallSettingsRequest extends Request
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
            'first_name' => 'required|min:3|max:50',
            'last_name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'multi_school' => 'required',
            'title' => 'required_if:multi_school,no',
            'school_email' => 'required_if:multi_school,no',
            'phone' => 'required_if:multi_school,no',
            'address' => 'required_if:multi_school,no',
        ];
    }
}
