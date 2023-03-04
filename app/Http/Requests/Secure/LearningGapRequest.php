<?php

namespace App\Http\Requests\Secure;

use App\Helpers\Settings;
use App\Http\Requests\Request;

class LearningGapRequest extends Request
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
            'intervention' => 'required|min:3',
            'competency_gap_id' => 'required|int',
            'deadline' => 'required|date_format:"' . Settings::get('date_format') . '"',

        ];
    }
    public function messages()
    {
        return [
            'competency_gap_id.required' => 'Competency Gap field is required!',
            'intervention.required' => 'Intervention is required!',
        ];
    }

}
