<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KraRequest extends Request
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
            'bsc_perspective_id' => 'required',
            'title' => 'required|min:3',

        ];
    }
}
