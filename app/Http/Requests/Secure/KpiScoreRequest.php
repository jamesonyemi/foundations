<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class KpiScoreRequest extends Request
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
            'weight.*' => 'integer|min:0.1|max:5',
            'weight.*.*' => 'integer|min:0.1|max:5',

        ];
    }
    public function messages()
    {
        return [

        ];
    }

}
