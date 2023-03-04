<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class CentreRequest extends Request
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
            'title' => 'required|min:3',
            'seating_capacity' => 'required',
            'sound_system' => 'required',
            'internet' => 'required',
            'network' => 'required',
            'ready' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
    }
}
