<?php

namespace App\Http\Requests\Secure;

use App\Http\Requests\Request;

class PayrollSetupRequest extends Request
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
            'title' => 'sometimes|required',
            'kras.*' => 'nullable',
            'kras.*.*' => 'nullable',
            'weight' => 'sometimes|required|numeric|min:0|not_in:0',
            'weight.*' => 'required|numeric|min:0|not_in:0',
            'weight.*.*' => 'required|numeric|min:0|not_in:0',
            'objective_id.*' => 'required',
            'objective_id.*.*' => 'required',
            'kpis.*' => 'required',
            'kpis.*.*' => 'required',
            'supervisor_employee_id' => 'sometimes|required',
            'supervisor_employee_id.*' => 'required',
            'supervisor_employee_id.*.*' => 'required',
            'activities.*' => 'nullable',
            'activities.*.*' => 'nullable',

        ];
    }
    public function messages()
    {
        return [

        ];
    }

}
