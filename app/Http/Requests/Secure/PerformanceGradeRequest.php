<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class PerformanceGradeRequest extends BaseFormRequest
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
            'title' => 'required',
            'max_score' => 'required|numeric',
            'min_score' => 'required|numeric',
            'grade' => 'required',
        ];
    }
}
