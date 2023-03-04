<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class CompetencyGapRequest extends Request
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
            'kpi_id' => 'required|int',

        ];
    }
    public function messages()
    {
        return [
            'kpi_timeline_id.required' => 'Timeline is required!',
            'kpi_objective_id.required' => 'Kpi Objective is required!',
            'password.required' => 'Password is required!'
        ];
    }

}
