<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class CustomUserFieldRequest extends Request
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
            'role_id' => 'required',
            'title' => 'required',
            'type' => 'required',
            'is_required' => 'required',
        ];
    }
}
