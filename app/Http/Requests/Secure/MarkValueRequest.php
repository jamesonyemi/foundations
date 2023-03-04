<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class MarkValueRequest extends Request
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
            'max_score' => 'required|numeric',
            'min_score' => 'required|numeric',
            'grade' => 'required',
            'mark_system_id' => 'integer',
        ];
    }
}
